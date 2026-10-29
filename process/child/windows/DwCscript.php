<?php
/**
 * Copyright 2011-2026 Nicolas "DarkHack" Chapuis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace bakuba\kernel\process\child\windows;
//use bakuba\kernel\process\child\windows\DwCscript as DwCscript;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwCscript extends DwWinExe {
	static private $kernelShellDir="file://bakuba/kernel/process/child/windows/JScript";
	
	public static function getScript( string $scriptPath ) : ?string {
		return \bakuba\kernel\autoload\DwAutoLoad::getFile( self::$kernelShellDir."/{$scriptPath}" );
	}
	
	private $dwProcCscript = null;
	public function __construct( 
		\bakuba\kernel\process\child\windows\DwWinAgent $dwWinAgent
	) {
		parent::__construct( $dwWinAgent );
	}
	protected function createDwProcCscript() : DwProcCscript {
		$dwProcCscript = new DwProcCscript( $this );
		$dwProcCscript->onTaskEnded( new DwCallBack( array( &$this ,'tasksEnded' ) ) );
		$dwProcCscript->onTimeoutCmd( new DwCallBack( array( &$this ,'taskTimeOut' ) ) );
		$logPath = \bakuba\kernel\agartha\DwInstall::getLocalDirectory( $this->log->getLevelLog() );
		$dwProcCscript->execWithoutPrepare( "var jsLogFile=\"{$logPath}/jsLog.log\";" );
		$dwProcCscript->execWithoutPrepare( self::getScript("lib-js/jsLog.js") );
		$dwProcCscript->execWithoutPrepare( self::getScript("lib-js/jsObject.js") );
//		$dwProcCscript->execWithoutPrepare( self::getScript("lib-js/jsArray.js") );
		$dwProcCscript->execWithoutPrepare( self::getScript("lib-js/jsString.js") );
		$dwProcCscript->execWithoutPrepare( self::getScript("lib-js/jsBase.js") );
		$dwProcCscript->execWithoutPrepare( self::getScript("lib-js/jsJson.js") );
		return $dwProcCscript;
	}
	protected function getDwProcCscript() : DwProcCscript {
		if ( !isset( $this->dwProcCscript ) || \is_null( $this->dwProcCscript ) )
			$this->dwProcCscript = $this->createDwProcCscript();
		return $this->dwProcCscript;
	}
	public function taskTimeOut() : void {
		$this->log->error( "." );
		if ( isset( $this->dwProcCscript ) && \is_object( $this->dwProcCscript ) ) {
			$crashedCmd = $this->dwProcCscript;
			$this->dwProcCscript = null;
			$newCmd = $this->getDwProcCscript();
			$newCmd->emergencySetQueue( $crashedCmd->emergencyGetQueue() );
			$this->getDwProcCscript()->exec( "WScript.Echo('DwCscript::proc restarted');" );
		}
	}
	private $cptTaskLunch=0;
	public function tasksEnded() : void {
		$this->log->trace( "." );
		$this->cptTaskLunch++;
		$logPath = \bakuba\kernel\agartha\DwInstall::getLocalDirectory( $this->log->getLevelLog() );
		$this->getDwProcCscript()->execWithoutPrepare( "var jsLogFile=".\json_encode( "{$logPath}/jsLog_{$this->cptTaskLunch}.log" ).";" );
		
		
		if ( \is_object( $this->dwProcCscript ) ) {
			$dwProcCscript = $this->dwProcCscript;
			$this->dwProcCscript = null;
			$dwProcCscript->shutdown();
			$this->log->trace( "proc cmd ended" );
		} else
			$this->log->error( "tasksEnded without attribute 'dwProcCscript' defined ?" );
	}
	public function exec( string $cmdToExec ,DwCallBack $dwCallBack = null ) : void {
		$this->getDwProcCscript()->exec( $cmdToExec ,$dwCallBack );
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

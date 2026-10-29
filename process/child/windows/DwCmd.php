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
//use bakuba\kernel\process\child\windows\DwCmd as DwCmd;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwCmd extends DwWinExe {
	static private $kernelShellDir="file://bakuba/kernel/process/child/windows/cmd";
	
	public static function getScript( string $scriptPath ) : ?string {
		return \bakuba\kernel\autoload\DwAutoLoad::getFile( self::$kernelShellDir."/{$scriptPath}" );
	}
	
	public function __construct( 
		\bakuba\kernel\process\child\windows\DwWinAgent $dwWinAgent
	) {
		parent::__construct( $dwWinAgent );
	}
	
	private $dwProcCmd = null;
	protected function createDwProcCmd() : DwProcCmd {
		$this->log->trace( "." );
		$dwProcCmd = new DwProcCmd( $this );
		$dwProcCmd->onTaskEnded( new DwCallBack( array( &$this ,'tasksEnded' ) ) );
		$dwProcCmd->onTimeoutCmd( new DwCallBack( array( &$this ,'taskTimeOut' ) ) );
		//$dwProcCmd->execWithoutPrepare( self::getScript("string/lib.cmd") );
		return $dwProcCmd;
	}
	protected function getDwProcCmd() : DwProcCmd {
		$this->log->trace( "." );
		if ( ! isset( $this->dwProcCmd ) || \is_null( $this->dwProcCmd ) )
			$this->dwProcCmd = $this->createDwProcCmd();
		return $this->dwProcCmd;
	}
	public function taskTimeOut() : void {
		$this->log->error( "." );
		$crashedCmd = $this->dwProcCmd;
		$this->dwProcCmd = null;
		$newCmd = $this->getDwProcCmd();
		$newCmd->emergencySetQueue( $crashedCmd->emergencyGetQueue() );
		$this->getDwProcCmd()->exec( "echo oki" );
	}
	public function tasksEnded() : void {
		$dwProcCmd = $this->dwProcCmd;
		$this->dwProcCmd = null;
		$dwProcCmd->shutdown();
		$this->log->trace( "proc cmd ended" );
	}
	public function exec( string $cmdToExec ,DwCallBack $dwCallBack = null ) : void {
		$this->getDwProcCmd()->exec( $cmdToExec ,$dwCallBack );
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

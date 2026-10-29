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
//use bakuba\kernel\process\child\windows\DwProcCmd as DwProcCmd;

use \Exception as Exception;
use \Error as Error;
use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

use bakuba\kernel\process\DwProcess as DwProcess;
use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;

use bakuba\kernel\io\stream\DwStream as DwStream;
use \bakuba\kernel\io\socket\DwSocket as DwSocket;

class DwProcCmd extends \bakuba\kernel\process\child\aDwProcFifo {
	
	private $cbCheck = null;
	private $cmdBoundary = null;
	public function __construct( 
		\bakuba\kernel\process\DwResources $dwResources
	) {
		parent::__construct( $dwResources ,"cmd /q /v:on 2>&1", false );
		$this->cbCheck = new DwCallBack( array ( &$this, 'checkTaskActivity' ) );
		//parent::__construct( $dwResources ,"cmd /q /v:on" );
		//$this->dwResources->isEverSchudeled( $this->cbInit )
		//if ( $this->dwResources->cancelCallBack( $this->cbInit ) )
		//	$this->read( $this->cbInit );
		$this->execWithoutPrepare( "echo off" );
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
	}
	//*/
	
	private $onTasksEnded = null;
	public function onTaskEnded( DwCallBack $dwCallBack ) {
		$this->onTasksEnded = $dwCallBack;
	}
	public function checkTaskActivity() {
		//$this->log->warn("no more task should stop process.");
		if ( ! $this->taskRunning && $this->totalTasks() == 0 ) {
			$this->log->warn("no more task should stop process.");
			if ( \is_object( $this->onTasksEnded ) )
				$this->onTasksEnded->callback();
		}
	}
	public function receiveTask( string $data ) : void {
		parent::receiveTask( $data );
		if ( ! $this->taskRunning ) {
			if ( $this->dwResources->isEverSchudeled( $this->cbCheck ) )
				$this->dwResources->cancelCallBack( $this->cbCheck );
			$this->dwResources->addTimer( 3 ,$this->cbCheck );// let 3s for shutdown if needed
		}
		//	$this->dwResources->addTick( $this->cbCheck );
	}
	/*
	public function myInit( string $init ) : void {
		parent::myInit( $init );
	}
	//*/
	protected function prepareCmdBoundary( string $boundary ) : string {
		return "echo {$boundary}\r\n";
	}
	public function execWithoutPrepare( string $strExecute ,DwCallBack $dwCallBack = null ) : void {
		$cmdToExec = \preg_replace( '/\/\*([\s\S]*?)\*|\:\:.*|rem.*/i', '', $strExecute);
		if ( \is_null( $cmdToExec ) ) $cmdToExec = $strExecute;
		$cmdToExec.= "\n";
		
		$cmdToExec = \preg_replace("/\r/", "", $cmdToExec);
		$cmdToExec = \preg_replace("/[\n]+/", "\n", $cmdToExec);
		$cmdToExec = \preg_replace("/\n/", "\r\n", $cmdToExec);
		parent::execWithoutPrepare( $cmdToExec ,$dwCallBack );
	}
	protected function prepareExec( string $cmdToPrepare, string $boundary ) : string {
		$cmdToExec = \preg_replace( '/\/\*([\s\S]*?)\*|\:\:.*|rem.*/i', '', $cmdToPrepare);
		if ( \is_null( $cmdToExec ) ) $cmdToExec = $cmdToPrepare;
		$cmdToExec="( {$cmdToExec} && echo ".$this->getBundarySucess( $boundary ).") || echo ".$this->getBundaryEchec( $boundary )."\n";
		$cmdToExec = \preg_replace("/\r/", "", $cmdToExec);
		$cmdToExec = \preg_replace("/[\n]+/", "\n", $cmdToExec);
		$cmdToExec = \preg_replace("/\n/", "\r\n", $cmdToExec);
		return $cmdToExec;
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

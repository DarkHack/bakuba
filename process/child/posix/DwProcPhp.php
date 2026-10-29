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

namespace bakuba\kernel\process\child\posix;
//use bakuba\kernel\process\child\posix\DwProcShell as DwProcShell;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwProcShell extends \bakuba\kernel\process\child\aDwProcFifo {
	
	private $cbCheck = null;
	private $cmdBoundary = null;
	public function __construct( 
		\bakuba\kernel\process\DwResources $dwResources
	) {
		parent::__construct( $dwResources ,"php" );
		$this->cbCheck = new DwCallBack( array ( &$this, 'checkTaskActivity' ) );
		//$this->execWithoutPrepare( "echo off" );
		$this->readErr( new DwCallBack( array( &$this->log, 'fatal' ) ) );
	}
	
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
	/*
	public function toString( $withParent = false ) : string {
		if ( \is_null( $this->procPid ) )
			$objStr="";
		else
			$objStr="procPid:{$this->procPid}/";
		$objStr.= parent::toString();
		return $objStr;
	}
	//*/
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

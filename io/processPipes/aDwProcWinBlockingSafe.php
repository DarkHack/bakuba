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

namespace bakuba\kernel\io\processPipes;
//use bakuba\kernel\io\processPipes\aDwProcWinBlockingSafe as aDwProcWinBlockingSafe;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

abstract class aDwProcWinBlockingSafe extends \bakuba\kernel\io\processPipes\aDwProcFifo {
	
	protected function getNewStreamIn( $resource ) : DwPipeInStream {
		$dwPipeInStream=new DwPipeInStream( $this ,$resource );
		//$dwPipeInStream->enableSafe();
		return $dwPipeInStream;
	}
	protected function getNewStreamOutStd( $resource ) : DwPipeOutStdStream {
		$dwPipeOutStdStream=new DwPipeOutStdStream( $this ,$resource );
		$dwPipeOutStdStream->enableSafe();
		return $dwPipeOutStdStream;
	}
	protected function getNewStreamOutErr( $resource ) : DwPipeOutErrStream {
		$dwPipeOutErrStream=new DwPipeOutErrStream( $this ,$resource );
		//$dwPipeOutErrStream->enableSafe();
		return $dwPipeOutErrStream;
	}
	
	private $cbCheck = null;
	
	public function __construct(
		\bakuba\kernel\process\DwResources $dwResources
		,string $cmdPath
		,bool $withErrPipe = true
	) {
		parent::__construct( $dwResources ,$cmdPath ,$withErrPipe );
		$this->cbCheck = new DwCallBack( array ( &$this, 'checkTaskActivity' ) );
	}
	
	private $onTasksEnded = null;
	public function onTaskEnded( DwCallBack $dwCallBack ) {
		$this->onTasksEnded = $dwCallBack;
	}
	private $cbTimeoutCmd = null;
	public function onTimeoutCmd( DwCallBack $dwCallBack ) {
		$this->cbTimeoutCmd = $dwCallBack;
	}
	public function checkTaskActivity() {
		if ( ! $this->taskRunning && $this->totalTasks() == 0 ) {
			$this->log->warn("no more task should stop process.");
			if ( \is_object( $this->onTasksEnded ) )
				$this->onTasksEnded->callback();
		}
	}
	public function taskTimeOut() : void {
		parent::taskTimeOut();
		! is_null( $this->cbTimeoutCmd ) && $this->getDwResources()->addTick( $this->cbTimeoutCmd );
		$this->log->trace( "ECHEC TASK TIMEOUT" );
	}
	public function receiveTask( string $data ) : void {
		$this->log->trace( $data );
		parent::receiveTask( $data );
		if ( ! $this->taskRunning ) {
			$this->log->trace( "taskRunning is false, shutdown." );
			$this->getDwResources()->addTick( $this->cbCheck );
			/*
			if ( $this->getDwResources()->isEverSchudeled( $this->cbCheck ) )
				$this->getDwResources()->cancelCallBack( $this->cbCheck );
			$this->getDwResources()->addTimer( 3 ,$this->cbCheck );// let 3s for shutdown if needed
			//*/
		}
		//	$this->getDwResources()->addTick( $this->cbCheck );
	}
	
	public function pipeOutReaded( string $data ,bool $withoutRelunch = false ) : void {
		$this->log->trace( "." );
		parent::pipeOutReaded( $data ,true );
	}
	/*
	public function __destruct() {
		$this->log->trace( "." );
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

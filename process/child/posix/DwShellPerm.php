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

namespace bakuba\kernel\shell;
//use bakuba\kernel\shell\DwShellPerm as DwShellPerm;

use \Exception as Exception;
use \Error as Error;
use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

use bakuba\kernel\process\DwProcess as DwProcess;
use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;

use bakuba\kernel\io\stream\DwStream as DwStream;

class DwShellPerm extends DwObject {
	private $resourceProc = null;
	private $cwd=null;
	private $env=null;
	private $myProcess = null;
	private $dwCallBack = null;
	private $dwCallBackCheckProcess = null;
	private $shellProcessPid = null;
	private $dwInPipeProcess = null;
	private $dwOutStdPipeProcess = null;
	private $strBuffOutStd = "";
	private $dwOutErrPipeProcess = null;
	
	private $isRunning = false;
	private $queue = null;
	
	private $logFolder=null;
	private $shellboundary=null;
	public function __construct( DwProcessResources $myProcess ) {
		//, DwCallBack $dwCBClose ) {
		//global $dwKernel;
		parent::__construct();
		$this->logFolder = \bakuba\kernel\autoLoad\DwAutoLoad::getLogDir();
		$this->queue = new \SplQueue();
		$this->shellboundary=\sha1( $this->getClassName() )."-".\bakuba\kernel\randomizer\DwRandomizer::randomize( 10 );
		$this->myProcess = $myProcess;
		$this->dwCallBackCheckProcess = new DwCallBack( array( &$this, "checkProcess" ) );
		//$processEndCallBack = new DwCallBack( array( &$this, "ready"), $dwCBClose );
		//$dwKernel->addSignal(\SIGUSR2, new DwCallBack( array( &$myProcess, "futureTick" ), $processEndCallBack ) );
		$this->init();
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
	public function onExec( DwCallBack $dwCallBack ) : void {
			$this->dwCallBack = $dwCallBack;
	}
	
	public function init( ) : void {
		
		$this->cptInactivity=1;
		$this->cptActivity=1;
		$this->activity=true;
		$this->shellProcessPid = null;
		$this->logFolder = \bakuba\kernel\autoLoad\DwAutoLoad::getLogDir();
		if ( ! is_dir( $this->logFolder ) )
			mkdir( $this->logFolder );
		
		$descriptorspec = array(
			0 => array( "pipe", "r") // stdin is a pipe that the child will read from
			,1 => array( "pipe" ,"w") // stdout is a pipe that the child will write to
			,2 => array( "pipe" ,"w") // stderr is a file to write to
		);
		$pipes=array();
		$options = array(
			"suppress_errors" => false
			,"bypass_shell" => true
			,"blocking_pipes" => false
			,"create_process_group" => false
			,"create_new_console" => false
		);
		$this->resourceProc = proc_open( 
//			$contentToStart//." && kill -".\SIGUSR2." ".$this->myProcess->getPid()."\n"
			"\$(which sh)"
//			"\$(which cat) <&0 | \$(which sh)"
//			"\$(which cat) <&0 | \$(which tee) /tmp/shellProcess_\$(date +\"%s\").cat | \$(which sh) | \$(which tee) /tmp/shellProcess_\$(date +\"%s\").run"
			//"\$(which sh)"
			,$descriptorspec
			,$pipes
			,$this->cwd
			,$this->env
			,$options
		);
			
		if ( is_resource( $this->resourceProc ) ) {
			if ( $this->logEnable )
				$this->log->trace("process started");
			
			$this->isRunning = true;
			$this->dwInPipeProcess = new DwStream( $pipes[0] );
			//$this->dwInPipeProcess->withoutFlush();
			$this->dwInPipeProcess->withoutClose();
			//$this->dwInPipeProcess->setDebug();
			//$this->myProcess->addTick( new DwCallBack( array( $this, "streamWrite" ), $this->dwInPipeProcess, new DwCallBack( array( $this, "streamWrited" ) ) ) );
			$this->dwInPipeProcess->setOnWrited( new DwCallBack( array( &$this ,"streamWrited" ) ) );
			$this->myProcess->streamWrite( $this->dwInPipeProcess );
			
			$this->dwOutStdPipeProcess = new DwStream( $pipes[1] );
			//$this->dwOutStdPipeProcess->withoutFlush();
			$this->dwOutStdPipeProcess->withoutClose();
			$this->dwOutStdPipeProcess->setOnReaded( new DwCallBack( array( &$this ,"streamReaded" ) ) );
			$this->myProcess->streamRead( $this->dwOutStdPipeProcess );
			
			$this->dwOutErrPipeProcess = new DwStream( $pipes[2] );
			//$this->dwOutErrPipeProcess->withoutFlush();
			$this->dwOutErrPipeProcess->withoutClose();
			$this->dwOutErrPipeProcess->setOnReaded( new DwCallBack( array( &$this, "streamErrorReaded" ) ) );
			$this->myProcess->streamRead( $this->dwOutErrPipeProcess );
			
			$this->dwCallBackCheckProcess->callback();
			//if ( $this->logEnable )
			//	$this->log->trace( $contentToStart );
		}  elseif ( $this->logEnable )
			$this->log->trace("echec process start".$this->scriptData);
	
	}
	private $activity = false;
	private $cptActivity = 1;
	private $cptInactivity = 1;
	public function checkProcess( ) {
		//if ( $this->logEnable )
		//	$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
		
		$wasActive = true;
		if ( $this->activity ) {
			$this->cptInactivity=1;
			$this->cptActivity++;
			$this->activity=false;
		} else {
			$this->cptActivity=1;
			$this->cptInactivity++;
			$wasActive=false;
		}
		
		if ( ! is_resource( $this->resourceProc ) ) {
			if ( $this->logEnable )
				$this->log->warn(" resourceProc not an resource!!!");
		} else {
	/*
	command 	string 	The command string that was passed to proc_open().
	pid 		int 	process id
	running 	bool 	true if the process is still running, false if it has terminated.
	signaled 	bool 	true if the child process has been terminated by an uncaught signal. Always set to false on Windows.
	stopped 	bool 	true if the child process has been stopped by a signal. Always set to false on Windows.
	exitcode 	int 	The exit code returned by the process (which is only meaningful if running is false). Only first call of this function return real value, next calls return -1.
	termsig 	int 	The number of the signal that caused the child process to terminate its execution (only meaningful if signaled is true).
	stopsig 	int 	The number of the signal that caused the child process to stop its execution (only meaningful if stopped is true). 
	//*/
			$processStatus = proc_get_status( $this->resourceProc );
			
			if ( is_null($this->shellProcessPid) && $processStatus[ "pid" ]>0 ) {
				$this->shellProcessPid = $processStatus[ "pid" ];
				$this->logFolder.="/{$this->shellProcessPid}";
				if ( ! is_dir( $this->logFolder ) )
					mkdir( $this->logFolder );
				if ( $this->logEnable )
					$this->log->warn("[{$this->shellProcessPid}] shell pid set.");
			}
			if ( ! $processStatus[ "running" ] && $processStatus[ "exitcode" ] != -1 ) {
				if ( $this->logEnable )
					$this->log->warn("[{$this->shellProcessPid}] exitcode:".$processStatus[ "exitcode" ] );
				$wasActive=true;
			}
			if ( $this->logEnable ) {
				if ( isset( $processStatus[ "stopsig" ] ) && $processStatus[ "stopsig" ] != 0 )
					$this->log->warn( "[{$this->shellProcessPid}] stopsig:".$processStatus[ "stopsig" ] );
				if ( isset( $processStatus[ "termsig" ] ) && $processStatus[ "termsig" ] != 0  )
					$this->log->warn("[{$this->shellProcessPid}] termsig:".$processStatus[ "termsig" ] );
			}
		}
		if ( $wasActive )
			$this->myProcess->addTimer( 0.01 ,$this->dwCallBackCheckProcess );
		else {
			if ( 
				( ! isset($processStatus) || ! $processStatus[ "running" ] )
				&& ! is_null($this->shellProcessPid)
				&&  $this->cptInactivity >= 100 //1000
			) {
				if ( $this->logEnable )
					$this->log->trace("[{$this->shellProcessPid}] stop process, cptInactivity:".$this->cptInactivity );
				$this->stop();
			} else
				$this->myProcess->addTimer(
					0.01 * $this->cptInactivity
					,$this->dwCallBackCheckProcess
				);
		}
		//if (  )
		//	$this->stop();
	}
	private $taskRunning =false;
	private $taskCurrent = null;
	private $cptTask = 0;
	private $cptTaskEnd = 0;
	public function start( string $contentToStart, DwCallBack $dwCallBack = null ) {
		$strExecute = $contentToStart."\necho {$this->shellboundary}\n";
		$this->queue->enqueue( array(
			"content" => $strExecute
			,"dwCallBack" => $dwCallBack
		) );
		
		$this->cptTask++;
		
		//if ( ! \is_dir( $this->logFolder ) )
		//	\mkdir( $this->logFolder );
		\file_put_contents(
			"{$this->logFolder}/{$this->cptTask}.sh"
			,$strExecute
			,\FILE_APPEND | \LOCK_EX
		);
		if ( ! $this->taskRunning )
			$this->nextTask();
	}
	private function nextTask() {
		if ( $this->logEnable )
			$this->log->warn( "[{$this->shellProcessPid}]:{$this->cptTaskEnd}" );
		if ( is_object( $this->dwInPipeProcess ) && $this->queue->count() > 0 ) {
			$this->taskRunning=true;
			$currentTask = $this->queue->dequeue();
			$this->taskCurrent=$currentTask;
			$strExecute=$this->taskCurrent["content"];
			if ( $this->dwInPipeProcess->writeEnd() ) {
				if ( $this->logEnable )
					$this->log->warn( "create new write stream :{$this->shellProcessPid}" );
				
				$resource = $this->dwInPipeProcess->getResource();
				$this->dwInPipeProcess = new DwStream( $resource );
				//$this->dwInPipeProcess->withoutFlush();
				$this->dwInPipeProcess->withoutClose();
				$this->dwInPipeProcess->setOnWrited( new DwCallBack( array( $this, "streamWrited" ) ) );
				$this->myProcess->streamWrite( $this->dwInPipeProcess );
			}
			$this->dwInPipeProcess->write( $strExecute );
		}
	}
	public function streamWrited( DwStream $dwStream ) : void {
		if ( $this->logEnable )
			$this->log->trace("[shell:".$this->shellProcessPid."]");
		
		$this->activity=true;
		fflush( $dwStream->getResource() );
	}
	public function result( $result ) {
		$this->cptTaskEnd++;
		\file_put_contents(
			"{$this->logFolder}/{$this->cptTaskEnd}.log"
			,$result
			,\FILE_APPEND | \LOCK_EX
		);
		
		$dwCallBack = $this->taskCurrent["dwCallBack"];
		$this->taskRunning=false;
		$this->nextTask();
		
		if ( is_object( $dwCallBack ) )
			$dwCallBack->callback( $result );
	}
	public function stop( ) {
		if ( $this->logEnable )
			$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
				
		$this->dwInPipeProcess->withFlush();
		$this->dwOutStdPipeProcess->withFlush();
		$this->dwOutErrPipeProcess->withFlush();
		
		if ( $this->dwInPipeProcess->isResource() )
			fflush( $this->dwInPipeProcess->getResource() );
		if ( $this->dwOutStdPipeProcess->isResource() )
			fflush( $this->dwOutStdPipeProcess->getResource() );
		if ( $this->dwOutErrPipeProcess->isResource() )
			fflush( $this->dwOutErrPipeProcess->getResource() );
		
		$this->dwInPipeProcess->withClose();
		$this->dwOutStdPipeProcess->withClose();
		$this->dwOutErrPipeProcess->withClose();
		
		$this->dwInPipeProcess->disconnect();
		$this->dwOutStdPipeProcess->disconnect();
		$this->dwOutErrPipeProcess->disconnect();
		
		$reported=false;
		if ( is_resource( $this->resourceProc ) ) {
			$processStatus = proc_get_status( $this->resourceProc );
			if ( $processStatus[ "running" ] ) {
				if ( $this->logEnable )
					$this->log->trace( "kill:".$this->shellProcessPid );
				\posix_kill( $this->shellProcessPid , \SIGKILL );
				$this->myProcess->addTimer( 0.001 ,new DwCallBack( array( &$this, "stop" ) ) );
				$reported=true;
			} else {
				$this->myProcess->cancelCallBack( $this->dwCallBackCheckProcess );
				if ( $this->logEnable )
					$this->log->trace( "proc_close:".$this->shellProcessPid );
				proc_close( $this->resourceProc );
			}
		}
		if ( $reported ) {
			if ( $this->logEnable )
			$this->log->trace( "reported" );
		} else {
			$this->isRunning = false;
			
			try {
				$lostBuf=$this->strBuffOutStd;
				if ( $this->logEnable )
					$this->log->trace( "lost buf:{$lostBuf}" );
				$this->init();
				$this->result( $lostBuf );
			} catch(Exception $exception_cb) {
				if ( $this->logEnable )
					$this->log->throwable($exception_cb);
				//throw $exception_cb;
			} catch(Error $error_cb) {
				if ( $this->logEnable )
					$this->log->throwable($error_cb);
				//throw $error_cb;
			}
		}
	}
	private $cpt=0;
	
	private $cptEmpty=0;
	public function streamReaded( DwStream $dwStream ) : void {
		if ( $this->logEnable )
			$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
		if ( $dwStream->readEnd() ) {
			if ( $this->logEnable )
				$this->log->warn( "create new read stream :{$this->shellProcessPid}" );
			$readDwStream = new DwStream( $dwStream->getResource() );
			//$readDwStream->withoutFlush();
			$readDwStream->setOnReaded( new DwCallBack( array( &$this ,"streamReaded" ) ) );
			$readDwStream->withoutClose();
			$this->myProcess->streamRead( $readDwStream );
		}
		$dataStream = $dwStream->read();
		
		$dataEmpty = empty( $dataStream );
		
		if ( $dataEmpty ) {
			$this->cptEmpty++;
			if ( is_resource( $this->resourceProc ) ) {
				$processStatus = proc_get_status( $this->resourceProc );
				if ( $processStatus[ "running" ] ) {
					/*
					if ( $this->cptEmpty>20 ) {
						if ( $this->logEnable )
							$this->log->error( "force process stop:".$this->shellProcessPid );
						$this->stop();
					}
					//*/
					if ( ! empty( $this->strBuffOutStd ) && $this->cptEmpty>10 ) {
						if ( $this->logEnable )
							$this->log->warn( "process stop empty read for :".$this->shellProcessPid );
						$this->stop();
					}
				} else {
					if ( $this->logEnable )
						$this->log->warn( "is resource stop pid:".$this->shellProcessPid." inactivity:".$this->cptInactivity );
					$this->cptInactivity=101;
					$this->activity=false;
					$this->stop();
				}
			} else {
				if ( $this->logEnable )
					$this->log->warn( "no resource stop pid:".$this->shellProcessPid." inactivity:".$this->cptInactivity );
				$this->cptInactivity=101;
				$this->activity=false;
				$this->stop();
			}
		} else {
			$this->strBuffOutStd.= $dataStream;
			$this->cptEmpty=0;
			$this->activity=true;
			if ( $this->logEnable )
				$this->log->trace( "[".$this->getInstanceKey()."] DATA:\r\n$dataStream" );
		}
		$posBundary = strpos( $this->strBuffOutStd ,$this->shellboundary );
		$resultTask=null;
		if ( $posBundary>0 || $posBundary!==false ) {
			$posbegin = $posBundary+strlen( $this->shellboundary )+1; 
			$nextBuf=substr(
				$this->strBuffOutStd
				,$posbegin
				,strlen($this->strBuffOutStd)-($posbegin)
			);
			$resultTask=substr(
				$this->strBuffOutStd
				,0
				,$posBundary
			);
			if ( $this->logEnable )
				$this->log->trace("result[{$posBundary}]\r\n{$resultTask}");
			$this->strBuffOutStd = $nextBuf;
		}
		
		if ( ! is_null($resultTask ) )
			$this->result($resultTask);
		//$this->dwCallBackCheckProcess->callback();
		
		/*
		//*/
		//$dwStream->clearRecvBuf();
		//$dwStream->setOnReaded( new DwCallBack( array( $this, "streamReaded" ) ) );
		//$this->myProcess->streamRead( $dwStream );
	}
	public function streamErrorReaded( DwStream $dwStream ) : void {
		$dataStream = $dwStream->read();
		if ( ! empty( $dataStream ) ) {
			if ( $this->logEnable )
				$this->log->trace( "[".$dwStream->getInstanceKey()."] ERROR:\r\n$dataStream" );
			/*
			$currentTask=$this->cptTaskEnd+1;
			\file_put_contents(
				"{$this->logFolder}/{$currentTask}.log"
				,"STD ERR:\r\n".$dataStream."\r\n"
				,\FILE_APPEND | \LOCK_EX
			);
			//*/
			$this->activity=true;
		}
		if ( $dwStream->readEnd() ) {
			if ( $this->logEnable )
				$this->log->warn( "create new read err stream :{$this->shellProcessPid}" );
			$readErrDwStream = new DwStream( $dwStream->getResource() );
			//$readErrDwStream->withoutFlush();
			$readErrDwStream->withoutClose();
			$readErrDwStream->setOnReaded( new DwCallBack( array( $this, "streamErrorReaded" ) ) );
			$this->myProcess->streamRead( $readErrDwStream );
		}
		// else if ( $this->logEnable )
		//	$this->log->trace("empty [{$this->getInstanceKey()}]");
		//$this->stop();
		//if ( $this->logEnable )
		//	$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
		/*
		$readDwStream = new DwStream( $dwStream->getResource() );
		$readDwStream->withoutFlush();
		$readDwStream->withoutClose();
		$readDwStream->setOnReaded( new DwCallBack( array( $this, "streamErrorReaded" ) ) );
		$this->myProcess->streamRead( $readDwStream );
		//*/
		//$dwStream->clearRecvBuf();
		//$dwStream->setOnReaded( new DwCallBack( array( $this, "streamReaded" ) ) );
		//$this->myProcess->streamRead( $dwStream );
	}
	public function __destruct() {
		$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

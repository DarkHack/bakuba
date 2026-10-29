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
//use bakuba\kernel\io\processPipes\DwProcPipe as DwProcPipe;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwProcPipe extends \darkphp\DwObject {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className=$this->getClassName();
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToTopName( $className )
				,time()."-".\darkphp\source\DwSource::classNameToTopName( $className )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	
	private $resourceProc = null;
	private $cwd=null;
	private $env=null;
	private $procPid = null;
	
	private $isRunning = false;
	private $isStop = false;
	private $exitCode = null;
	
	private $dwTimerCheck = null;
	private $cbCheck = null;
		
	public function getDwResources() : \bakuba\kernel\process\DwResources {
		return $this->parent;
	}
	public function __construct(
		\bakuba\kernel\process\DwResources $dwResources
		,string $cmdPath
		,bool $withErrPipe = true
	) {
		parent::__construct( $dwResources );
		$this->cbCheck = new DwCallBack( array( &$this, "checkProcess" ) );
		
		$this->env = $_ENV;
		$this->myCmdPath=$cmdPath;
		$this->myWithErrPipe=$withErrPipe;
  		$this->startProcess();
		$this->log->trace( "." );
	}
	private $myCmdPath = null;
	private $myWithErrPipe = null;
	//private $resetCount=0;
	
	// With Perplexity production
	public function procReset( ) {
		$this->log->warn("ProcReset called: restarting process on CMD path {$this->myCmdPath}");
		//if ($this->resetCount < 3) { // Limite pour éviter boucle infinie
		//	$this->resetCount++;
			$this->startProcess();
		//}
	}
	protected function getNewStreamIn( $resource ) : DwPipeInStream {
		return new DwPipeInStream( $this ,$resource );
	}
	protected function getNewStreamOutStd( $resource ) : DwPipeOutStdStream {
		return new DwPipeOutStdStream( $this ,$resource );
	}
	protected function getNewStreamOutErr( $resource ) : DwPipeOutErrStream {
		return new DwPipeOutErrStream( $this ,$resource );
	}
	protected function startProcess() : void {
		$this->cptInactivity=1;
		$this->cptActivity=1;
		$this->activity=true;
		$this->procPid=null;
		$this->isStop=false;
		$this->exitCode=null;
		
		/* On Windows we need to use AF_INET */
		//$domain = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? STREAM_PF_INET :  STREAM_PF_UNIX);
		//not work :STREAM_SOCK_RAW ,STREAM_SOCK_RDM ,STREAM_SOCK_DGRAM ,STREAM_SOCK_SEQPACKET
		//yes work :STREAM_SOCK_STREAM
		//$type= STREAM_SOCK_STREAM;
		/* Setup socket pair */
		//$socketsWrite = stream_socket_pair($domain, $type,  STREAM_IPPROTO_TCP );
		//$socketsRead= stream_socket_pair($domain, $type,  STREAM_IPPROTO_TCP );
		//$socketsReadErr = stream_socket_pair($domain, $type,  STREAM_IPPROTO_TCP );
		//*/
		$descriptorspec = array(
			// $socketsWrite[0]// array( "pipe", "r")
			// stdin is a pipe that the child will read from
			0 => array( "pipe", "r")
			// $socketsRead[0]// array( "pipe" ,"w")
			// stdout is a pipe that the child will write to
			,1 => array( "pipe" ,"w")
			// $socketsReadErr[0]// array( "pipe" ,"w")
			// stderr is a file to write to
//			,2 => array( "pipe" ,"w")
		);
		if ( $this->myWithErrPipe )
			$descriptorspec[2] = array( "pipe" ,"w");
		
		$pipes=array();
		$options = array(
			"suppress_errors" => false
			,"bypass_shell" => true
			,"blocking_pipes" => false
			,"create_process_group" => false
			,"create_new_console" => false
		);
		//$this->log->trace( $this->env );
		$this->resourceProc = \proc_open( 
			$this->myCmdPath
			,$descriptorspec
			,$pipes
			,$this->cwd
			,$this->env
			,$options
		);
			
		if ( \is_resource( $this->resourceProc ) ) {
			$this->log->debug( "process started cmd:{$this->myCmdPath}" );
			$this->dwPipesPeer = new \bakuba\kernel\io\processPipes\DwPipesPeer(
				$this
				,$this->getNewStreamIn($pipes[0])
				,$this->getNewStreamOutStd($pipes[1])
				,isset ( $pipes[2] ) ? $this->getNewStreamOutErr($pipes[2]) : null
			);
			$this->dwPipesPeer->setOnPipeInWrited( new DwCallBack( array( &$this, 'pipeInWrited' ) ) );
			$this->dwPipesPeer->setOnPipeOutReaded( new DwCallBack( array( &$this, 'pipeOutReaded' ) ) );
			//$this->lunchRead();
			if ( isset ( $pipes[2] ) ) {
				$this->dwPipesPeer->setOnPipeErrReaded( new DwCallBack( array( &$this, 'pipeErrReaded' ) ) );
				//$this->lunchReadErr();
			}
			
			$this->dwTimerCheck = $this->getDwResources()->addTimer( 1/10 ,$this->cbCheck );// let 100ms for start
		}  else
			throw new \Exception( "echec process start resourceProc return by proc_open is not an ressource." );
	}
	private $activity = false;
	private $cptActivity = 1;
	private $cptInactivity = 1;
	private $procIsStarted = false;
	public function checkProcess( ) {
		$this->log->trace(".");
		
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
		
		if ( ! \is_resource( $this->resourceProc ) )
			throw new \Error( "resourceProc not an resource !!!" );
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
		$processStatus = \proc_get_status( $this->resourceProc );
		$this->isRunning = $processStatus[ "running" ];
		if ( ! $this->isRunning )/*
			$this->procCheckStream(); 
		else//*/
			$this->isStop=true;
		
		if ( \is_null( $this->procPid ) && $processStatus[ "pid" ] > 0 ) {
			$this->procPid = $processStatus[ "pid" ];
			//$this->log->debug("procPid:{$this->procPid}");
		}
		if ( isset( $processStatus[ "exitcode" ] ) && $processStatus[ "exitcode" ] != -1 ) {
			$this->exitCode = $processStatus[ "exitcode" ];
			$this->log->warn("exitcode:{$this->exitCode} ".( $this->isRunning?"running":"not running" ) );
			$wasActive=true;
		}
		if ( isset( $processStatus[ "stopsig" ] ) && $processStatus[ "stopsig" ] != 0 )
			$this->log->warn( "stopsig:".$processStatus[ "stopsig" ] );
		if ( isset( $processStatus[ "termsig" ] ) && $processStatus[ "termsig" ] != 0  )
			$this->log->warn("termsig:".$processStatus[ "termsig" ] );
		
		if ( ! $this->isRunning && ! is_null($this->procPid) &&  $this->cptInactivity >= 100 ) { //1000
			$this->log->trace("stop process, cptInactivity:".$this->cptInactivity );
			$this->isStop=true;
		}
		if ( $this->isStop ) {
			$this->shutdown();
		} else {
			if ( $wasActive )
				$this->dwTimerCheck = $this->getDwResources()->addTimer( 0.2 ,$this->cbCheck );// next check in 200 ms
			else
				$this->dwTimerCheck = $this->getDwResources()->addTimer(
					0.1 * $this->cptInactivity // cptInactivity * 100 ms ( 0.1 s )
					,$this->cbCheck
				);
		}
	}
	/*
	private function procCheckStream() : void {
		$this->log->trace( "." );
		//if ( ! $this->dwPipesPeer->stdWritting() )
		//	$this->dwPipesPeer->lunchWrite();
		if ( ! $this->stdReading() )
			$this->lunchRead();
		if ( \is_object( $this->dwOutErrPipeProcess ) 
			&& ! $this->stdErrReading()
		)
			$this->lunchReadErr();
	}
	//*/
	protected function writeToStd( string $data ) : void {
		$this->dwPipesPeer->writeToStd( $data );
		$this->dwPipesPeer->lunchWrite();
	}
	public function pipeInWrited() : void {
		$this->log->trace( "." );
		$this->activity = true;
	}
	
	public function asyncLunchRead() : void {
		$this->log->trace( "." );
		$this->lunchRead();
	}
	protected function lunchRead() : void {
		$this->log->trace( "." );
		$this->dwPipesPeer->lunchRead();
	}
	protected function stdReading() : bool {
		return $this->dwPipesPeer->stdReading();
	}
	public function pipeOutReaded( string $data ,bool $withoutRelunch = false ) : void {
		if ( ! empty( $data ) )
			$this->activity=true;
		/*
		if ( $withoutRelunch )
			$this->log->warn( "no relunch read" );
		else
			$this->lunchRead();
		//*/
	}
	protected function lunchReadErr() : void {
		$this->log->trace( "." );
		$this->dwPipesPeer->lunchReadErr();
	}
	protected function stdErrReading() : bool {
		return $this->dwPipesPeer->stdErrReading();
	}
	public function pipeErrReaded( string $data ) : void {
		if ( empty( $data ) )
			$this->log->warn("Empty read");
		else {
			$this->log->error( $data );
			$this->activity = true;
		}
		/*
		$processStatus = \proc_get_status( $this->resourceProc );
		if ( $processStatus[ "running" ] )
			$this->lunchReadErr();
		//*/
	}
	public function stop( ) {
		$this->dwPipesPeer->stop();
	}
	public function shutdown() : void {
		$this->dwPipesPeer->shutdown();
		
		$processStatus = \proc_get_status( $this->resourceProc );
		$this->isRunning = $processStatus[ "running" ];
		
		if ( $this->getDwResources()->isEverSchudeled( $this->cbCheck ) )
			$this->getDwResources()->cancelTimer( $this->dwTimerCheck );
		if ( $this->isRunning ) {
			$this->log->trace("proc_terminate" );
			\proc_terminate( $this->resourceProc );
			$wasActive=true;
			$this->dwTimerCheck = $this->getDwResources()->addTimer( 0.2 ,$this->cbCheck );// next check in 200 ms
		} else {
			$this->log->trace("proc_close" );
			\proc_close( $this->resourceProc );
			// close in 100 ms
			$this->dwTimerCheck = $this->getDwResources()->addTimer( 0.1, new DwCallBack( array( &$this, "stop" ) ) );
		}
	}
	public function toString( $withParent = false ) : string {
		$objStr=parent::toString( $withParent );
		if ( isset( $this->procPid ) )
			$objStr.="/childProcPid:{$this->procPid}";
		return $objStr;
	}
	///*
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

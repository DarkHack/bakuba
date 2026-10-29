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

namespace bakuba\kernel\process;
//use bakuba\kernel\process\DwProcess as DwProcess;

use \Exception as Exception;
use \Error as Error;

//use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\objects\timer\DwTimer as DwTimer;
abstract class DwProcess extends DwProcessManager implements DwIRunnable  {
	protected $parentPid = null;
	public function setParentPid( $parentPid ) {
		$this->parentPid = $parentPid;
	}
	public function getParentPid( ) {
		return $this->parentPid;
	}
	protected $ableToFork = true;
	public function disableFork() {
		$this->ableToFork = false;
	}
	protected $isForked = false;
	public function isFork() : bool {
		return $this->isForked;
	}
	
	private $dwTimerCheckChildProcess = null;
	protected function __construct(	DwProcessIpc $parentProcess ) {
		parent::__construct( $parentProcess );
		//$this->log->trace("end");
	}
	
	public function DwSerializableInit() {
		parent::DwSerializableInit();
			
		$this->addtoSerialize("parentPid");
		$this->addtoSerialize("ableToFork");
		$this->addtoSerialize("isForked");
	}
	
	protected function init(): void {
		parent::init( );
		$this->log->trace(".");
		
		if ( $this->isFork() ) {
			$processClassName =  str_replace ( '\\' ,'.' ,$this->getClassName() );
			$processTitle = "php-{$processClassName}";
			/*
			$firstPos = strpos( $processClassName ,'.' );
			$headName = substr( $processClassName ,0 ,$firstPos );
			if ( $headName == "bakuba" )
				$headName .= "-".substr(
					$processClassName
					,$firstPos+1
					,strpos( $processClassName ,'.' ,$firstPos+1 )
				);
			$footName = substr(
				$processClassName
				,strrpos( $processClassName ,'.' ) + 1
			);
			$processTitle = "php-{$headName}_{$footName}-".$this->cptProcRun;
			//*/
			$this->setProcessTitle( $processTitle );
		}
	}
	
	public function checkChildProcess( ) {
		$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		$debugKey=$this->getClassName()."/pid:{$this->getPid()}";
		$this->log->trace( "[{$debugKey}]" );
		
		if ( ! self::checkPid( $this->getPid() ) ) {
			$this->log->error("[{$debugKey}] stop");
			$this->stop();
		}
	}
	public function isForkEnable() : bool {
		return $this->ableToFork && \function_exists("pcntl_fork");
	}
	public function stopTimer( ) : void {
		$debugKey=$this->getClassName()."/pid:{$this->getPid()}";
		$this->log->info( "[{$debugKey}]" );
		if ( is_null( $this->dwTimerCheckChildProcess ) ) {
			//if ( $this->logEnable )
				$this->log->error( "[{$debugKey}] dwTimerCheckChildProcess is null!!!" );
		} else
			$this->cancelTimer( $this->dwTimerCheckChildProcess );
	}
	
	/*
	// true if parent process and should continue
	// false if child process and all will stop
	public function start( ) : bool {
		//global $dwKernel;
		$return = true;
		//$dwKernel->eventLog( str_replace ( '\\', '.',$this->getClassName() ) );
		$init = false;
		$executeForked = false;
		
		$this->log->trace($this->isForkEnable() ? "isForkEnable":"no fork enable  (disable in code)");
		if ( $this->isForkEnable() && false ) {
			if ( $this->fork() ) {
				$init = true;$executeForked = true;
			} else {
				$this->log->trace("add check child process");
				$this->dwTimerCheckChildProcess = $this->addPeriodicTimer(
					3 // each 3s
					,new DwCallBack( array( &$this ,"checkChildProcess" ) )
				);
				$this->onClose( new DwCallBack( array( &$this ,'stopTimer' ) ) );
			}
		} else
			$init = true;
		
		//if ( $init ) {
			try {
				$this->log->trace("this.init");
				$this->init();
				$this->log->trace("this.init end");
			} catch(\Exception $exception_pinit) {
				$this->log->throwable( $exception_pinit );
				//throw $exception_pinit;
			} catch(\Error $error_pinit) {
				$this->log->throwable( $error_pinit );
				//throw $error_pinit;
			}
		//}
		if ( $executeForked ) {
			try {
				$this->log->trace("forked go to DwProcessManager::run");
				DwProcessManager::run( $this );
			} catch( \Exception $exception_run ) {
				$this->log->throwable( $exception_run );
				//throw $exception_run;
			} catch( \Error $error_run ) {
				$this->log->throwable( $error_run );
				//throw $error_run;
			}
			$return = false;
		}
		
		return $return;
	}
	//*/
	// return false if forked and parent process or echec fork
	private function fork() {
		$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		$return = false;
		$dbgMessage="PPid:{$this->getParentProcess()->getPid()}";
		$this->setParentPid( $this->getParentProcess()->getPid() );
		if ( $this->isForkEnable() ) {
		    $dbgMessage.=" fork";
			$pid = \pcntl_fork();
			//if ( $this->logEnable )
			//	$this->log->info("{$dbgMessage} :pcntl_fork");
			
			if ($pid == -1) {
				$this->isForked = false;
				//$return = false;
				throw new Exception("{$dbgMessage}:pcntl_fork echec");
			} else if ($pid) {
				$this->isForked = true;
				$this->setPid( $pid );
				//$return = false;
			} else {
				//echo "DwProcess:Forked for {$dbgMessage}\r\n";
				$return = true;
				$this->isForked = true;
				
				$this->setPid( \getmypid() );
				\bakuba\kernel\objects\DwKernel::setActiveProcess( $this );
			}
		} else {
		    $dbgMessage.="[monoprocess]";
			$this->isForked = false;
			$this->setPid( $this->getParentProcess()->getPid() );
			//$return = false;
		}
		//$this->log->info( $this->getPid()." started by ".$this->getParentPid() );
		
		return $return;
	}
	
	private static function w32CheckPid( int $pid ) : bool {
		$found = false;
	    $wmi=new COM("winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\cimv2");
		$procs=$wmi->ExecQuery("SELECT * FROM Win32_Process WHERE ProcessId='".$pid."'");
		foreach( $procs as $proc ) 
			if ( ( $found = ( $pid == $proc->ProcessId ) )== true )
				break;
		return $found;
	}
	// DwProcess::checkPid
	public static function checkPid( int $pid ) : bool {
		$pidIsAlive = false;
		//$resource_usage = array();
		$resultWait = \pcntl_waitpid( $pid ,$status ,\WNOHANG );//, $resource_usage);
		if ( $resultWait == 0 ) 
			$pidIsAlive = true;
		elseif ( $resultWait==-1 ) {
			if ( \function_exists('posix_getpgid'))
				$pidIsAlive = \posix_getpgid( $pid ) !== false;
			else 
				$pidIsAlive = self::w32CheckPid( $pid );
		} elseif ( $resultWait == $pid ) {
			$pidIsAlive = false;
			/*
			if ( \pcntl_wifexited( $status ) )
				$childReturnCode = \pcntl_wexitstatus( $status );
			if ( \pcntl_wifstopped( $status ) )
				$childCauseStopCode = \pcntl_wexitstatus( $status );
			if ( \pcntl_wifsignaled( $status ) )
				$childSignalStopCode = \pcntl_wtermsig( $status );
			//*/
		} else
			throw new \Exception(
				"[{$debugKey}] pcntl_waitpid result : $resultWait (value not ['0','-1','$pid'])"
				."---- status:".\darkphp\DwObject::print_r( $status )
				//, $resource_usage
			);
		return $pidIsAlive;
	}
	public function toString( $withParent = false ) : string {
		$objStr = parent::toString( $withParent );
		if ( ! is_null( $this->parentPid ) )
			$objStr.="/Ppid:{$this->getParentPid()}";
		return $objStr;
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

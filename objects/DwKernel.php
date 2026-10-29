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

namespace bakuba\kernel\objects;
//use bakuba\kernel\objects\DwKernel as DwKernel;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;

//use bakuba\kernel\objects\DwCallBack as DwCallBack;
//use darkphp\DwObject as DwObject;
use bakuba\kernel\time\DwObjectTick as DwObjectTick;
//use bakuba\kernel\objects\DwSignalsHandler as DwSignalsHandler;

class DwKernel extends DwObject {
	static private $sLog = null;
	// singleton class pattern:
	private static $instance = null;
	public static function singleton( ) {
		global $dwKernel;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			null === self::$instance and self::$instance = new self( );	
			$dwKernel = self::$instance;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".self::getStaticClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".self::getStaticClassName()."]");
			throw $error;
		}
		return self::$instance;
	}
	
	private $dwSignalsHandler = null;
	private function __construct(  ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::__construct();
			self::$sLog->trace("[".$this->getClassName()."]");
			
			$this->dwSignalsHandler = new DwSignalsHandler();
			$this->endRequest();
			
			//register_tick_function( array(&$this ,'tick') );
			
			//$this->myNode->ping();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}

	private static $activeProcess = null;
	public static function setActiveProcess(
		\bakuba\kernel\process\DwProcessBase $dwProcessBase
	) : void {
		global $dwBakuba;
		if ( is_null( self::$activeProcess ) )
			self::$activeProcess = $dwProcessBase;
		elseif ( \method_exists( $dwProcessBase, 'isFork' )
		&& $dwProcessBase->isFork() ) {
			self::$activeProcess = $dwProcessBase;
			if ( is_object( $dwBakuba->getDwRandomizerProcess() ) )
				new \bakuba\kernel\randomizer\DwRandomizer( $dwProcessBase );
		}
	}
	// \bakuba\kernel\objects\DwKernel::getActiveProcess();
	public static function getActiveProcess() : ?\bakuba\kernel\process\DwProcessBase {
		return self::$activeProcess;
	}
	public function __destruct( ) {
		try {	
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			unset( $this->dwSignalsHandler );
			parent::__destruct();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
    public function addSignal( int $signo ,DwObjectTick $dwObjectTick ,DwCallBack $dwCallBack ) : DwCallBack {
		$theDwCallBack = null;
		try {
			$theDwCallBack = new DwCallBack( array( &$this ,'recvSignal' ) ,$dwObjectTick ,$dwCallBack );
			$return = $this->dwSignalsHandler->add( $signo ,$theDwCallBack );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $theDwCallBack;
    }
    public function recvSignal( DwObjectTick $dwObjectTick, DwCallBack $dwCallBack ) : void {
		try {
			//self::$sLog->error("signal received...........................................");
			$dwObjectTick->futureTick( $dwCallBack );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
    }
	
    public function clearSignal( DwCallBack $dwCallBack ) : void {
		try {
			//self::$sLog->error("signal received...........................................");
			$this->dwSignalsHandler->clear( $dwCallBack );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
    }
    
    public function removeSignal( int $signo ,DwCallBack $dwCallBack ) : bool {
		$return = false;
		try {
			$return = $this->dwSignalsHandler->remove( $signo ,$dwCallBack );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $return;
    }
	// tick signal
	private $cptTick = 0;
	public function tick() {
		try {
			$this->cptTick++;
			
			//$this->myNode->ping();
			if ( $this->cptTick % 10 == 0 ) {
				self::$sLog->debug("tick[".$this->cptTick."]");
				
				self::$sLog->debug(DwObject::printStats());
				
				$debugBackTrace = debug_backtrace();
				$printBackTrace = array();
				foreach($debugBackTrace as $backtrace) {
					$printBackTrace[]=DwLogger::formatBackTrace( $backtrace );
				}
				echo DwObject::print_r( $printBackTrace )."\r\n";
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	private $strLogSession = null;
	public function setLogSession( string $logSession ) : void {
		try {
			self::$sLog->trace("[".$this->getClassName()."]");
			$this->strLogSession = $logSession;
			$this->refreshDirLog();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	private $strLogtime = null;
	public function setLogTime( string $logTime ) : void {
		try {
			self::$sLog->trace("[".$this->getClassName()."]");
			$this->strLogtime = $logTime;
			$this->refreshDirLog();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	private $strLogAppUrl = null;
	public function setLogAppUrl( string $strLogAppUrl ) : void {
		try {
			self::$sLog->trace("[".$this->getClassName()."]");
			$this->strLogAppUrl =  str_replace( array("/", ".","\\"), "-", $strLogAppUrl );
			$this->refreshDirLog();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	private $strLogCtrl = null;
	public function setLogCtrl( string $strLogCtrl ) : void {
		try {
			self::$sLog->trace("[".$this->getClassName()."]");
			$this->strLogCtrl =  str_replace( array("/", ".","\\"), "-", $strLogCtrl );
			$this->refreshDirLog();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function getFolderInit(  ) : string {
		return DwLogger::getFolderInit();
	}
	public function refreshDirLog(  ) : void {
		//global $dwBakuba;
		try {
			//self::$sLog->trace("[".$this->getClassName()."]");
			$dirLog = $this->getFolderInit();
			
			//$runProcess = $dwBakuba->getProcessRunning();
			//$dirLog .= "/$runProcess/".getmypid();
			if ( ! is_null( $this->strLogAppUrl ) )
				$dirLog.= "/".$this->strLogAppUrl;
			$dirLog .= "/".getmypid();
			if ( ! is_null( $this->strLogCtrl ) )
				$dirLog.= "/".$this->strLogCtrl;
			if ( ! is_null( $this->strLogSession ) )
				$dirLog.= "/".$this->strLogSession;
			if ( ! is_null( $this->strLogtime ) )
				$dirLog.= "/".$this->strLogtime;
			$this->rootLogDir( $dirLog );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	public function getDirLog( ) : string {
		$dirLog = "";
		try {
			self::$sLog->trace("[".$this->getClassName()."]");
			$dirLog = DwLogger::getDirLog();
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $dirLog;
	}
	public function rootLogDir( $dirToRoot ) {
		try {
			//self::$sLog->trace("$dirToRoot [".$this->getClassName()."]");
			DwLogger::rootLogDir($dirToRoot);
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	public function upLogDir( $upToLevel ) {
		try {
			self::$sLog->trace(". $upToLevel");
			DwLogger::upLogDir($upToLevel);
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	
	
	
	public function startRequest( $rootDir, $className ) {
		try {
			self::$sLog->trace(". $rootDir, $className");
			$this->upLogDir($className);
			$this->logToRootDir( $rootDir );
			$this->logToController( $className );
			$this->logToRequest();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function endRequest() {
		try {
			self::$sLog->trace(". ".self::$keyRootDirDefault.", ".self::$keyControllerDefault);
			$this->logToRootDir( self::$keyRootDirDefault );
			$this->logToController( self::$keyControllerDefault );
			$this->logToRequest();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	private static $keyRootDirDefault = "default";
	private $keyRootDir = null;
	public function logToRootDir( $keyRootDir ) {
		try {
			self::$sLog->trace(". $keyRootDir");
			$this->keyRootDir = $keyRootDir;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	private static $keyControllerDefault = "default";
	private $keyController = null;
	public function logToController( $keyController ) {
		try {
			self::$sLog->trace(". $keyController");
			$this->keyController = $keyController;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	private $keysRequest = array();
	public function logToRequest(  ) {
		try {
			self::$sLog->trace(".");
			
			
			/*
			if (! isset( $this->logs[ $this->keyRootDir ] ) )
				$this->logs[ $this->keyRootDir ] = array();
			
			if (! isset( $this->logs[ $this->keyRootDir ][ $this->keyController ] ) )
				$this->logs[ $this->keyRootDir ][ $this->keyController ] = array();
			
			if (!isset($this->keysRequest[ $this->keyRootDir.$this->keyController ]))
				$this->keysRequest[ $this->keyRootDir.$this->keyController ] = 0;
			
			$keyRequest = $this->keysRequest[ $this->keyRootDir.$this->keyController ];
			if (! isset( $this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ] ) ) {
				$this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ] = array();
				$this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "logs" ] = array();
				$this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "tstart" ] = microtime();
				$this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "mstart" ] = ($this->myNode==null ?"???" : $this->myNode->getMemoryState() );
			} else {
				$this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "tstop" ] = microtime();
				$this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "mstop" ] = ($this->myNode==null ?"???" : $this->myNode->getMemoryState() );
				
				// COMPUTE DELTA time/memory
				$mstart = $this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "mstart" ];
				$mstop = $this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "mstop" ];
				$this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "mdelta" ] = ($this->myNode==null ?"???" : $this->myNode->getMemoryDelta($mstart, $mstop) );
				
				$tstart = $this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "tstart" ];
				$tstop = $this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "tstop" ];
				$this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "tdelta" ] = $tstop - $tstart;
			}
			//*/
			
			//$this->keysRequest[ $this->keyRootDir.$this->keyController ]++;
				
			
			//DwLogger::logSwitchTo( $this->keyController."/".( $this->keysRequest[ $this->keyRootDir.$this->keyController ]++ ).".log", $this->keyRootDir );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	private $eventName = "boot";
	public function eventLog( string $eventName ) {
		if ( $eventName != $this->eventName ) {
			$pid=getmypid();
			if ( ! isset( $this->logs[$pid] ) )
				$this->logs[$pid] = array();
			
			/*
			//date('Ymd-his-').
			$strEventMv = date('Ymd-his-')."- FIN --------- ".$this->eventName." => ".$eventName."\r\n";
			if ( isset( $this->logs[$pid][$this->eventName] ) )
				$this->logs[$pid][$this->eventName][]= $strEventMv;
			else
				$this->logs[$pid][$this->eventName]= array($strEventMv);
			
			$strEventMv = date('Ymd-his-')."- DEBUT ------- ".$eventName." <= ".$this->eventName."\r\n";
			if ( ! isset( $this->logs[$pid][$eventName] ) )
				$this->logs[$pid][$eventName] = array($strEventMv);
			else
				$this->logs[$pid][$eventName][]= $strEventMv;
			//*/
			$this->eventName = $eventName;
		}
	}
	
	public $logs = array();
	public function addLog( $eventLog, $layoutLog ) {
		//return ;
		try {
			$pid = getmypid();
			if ( ! isset( $this->logs[$pid] ) )
				$this->logs[$pid] = array();
			if ( ! isset( $this->logs[$pid][$this->eventName] ) )
				$this->logs[$pid][$this->eventName] = array();
			// 
			//self::$sLog->trace("."); // this is the add log, that is finish by recursive call
			//$keyRequest = $this->keysRequest[ $this->keyRootDir.$this->keyController ];
			//$this->logs[ $this->keyRootDir ][ $this->keyController ][ $keyRequest ][ "logs" ][] = $layoutLog->format( $eventLog );
			$this->logs[$pid][$this->eventName][] = urlencode( $layoutLog->format( $eventLog ) );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function addLogs( array $pidsEventsLogs ) : void {
		try {
			if (is_array($pidsEventsLogs) ) {
				foreach($pidsEventsLogs as $pid => $eventsLogs) {
					if ( ! isset( $this->logs[$pid] ) )
						$this->logs[$pid] = $eventsLogs;
					else {
						foreach($eventsLogs as $event => $logs) {
							if ( ! isset( $this->logs[$pid][$event] ) )
								$this->logs[$pid][$event] = $logs;
							else {
								foreach($logs as $log)
									$this->logs[$pid][$event][] = $log;
							}
						}
					}
				}
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function getLogs( ) : array {
		$return = array();
		try {
			$return = $this->logs;
			$this->logs = array();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $return;
	}
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

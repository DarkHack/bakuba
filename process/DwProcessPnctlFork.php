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
//use bakuba\kernel\process\DwProcessPnctlFork as DwProcessPnctlFork;
use \Exception as Exception;
use \Error as Error;
use \DateTime as DateTime;

use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use darkphp\logger\DwLoggerFolder as DwLoggerFolder;
use darkphp\logger\DwLoggerFile as DwLoggerFile;
use darkphp\logger\DwLogger as DwLogger;

abstract class DwProcessPnctlFork extends \bakuba\kernel\time\timer\DwObjectTimer implements iDwProcessBase {
	public function getLogDir() : string {
		return \bakuba\kernel\autoload\DwAutoLoad::getLogDir()."/{$this->pid}";
	}
	/*
	private $log = null;
	private $logFile = null;
	private $logFolder = null;
	protected function initLogFile( string $fileName  ) : void {
		if ( is_null( $this->logFolder ) ) 
			$this->logFolder  = DwLoggerFolder::singleton( self::getLogDir() );
		$this->logFile = $this->logFolder->getDwLoggerFile(
			$fileName
		);
	}
	protected function getDwLoggerFolder( string $folderUp ) : DwLoggerFolder {
		return $this->logFolder->upToFolder( $folderUp );
	}
	protected function getLogger(
		string $className
	) : \darkphp\logger\DwLogger {
		return $this->logFile->getLogger( $className );
	}
	
		$this->initLogFile( $this->getClassName() );
		if (is_null($this->log) ) $this->log = $this->getLogger(__CLASS__);
	
	//*/
	
	protected $pid = null;
	public function getPid( ) : int {
		return $this->pid;
	}
	protected function setPid( int $myPid ) : void {
		$this->pid = $myPid;
	}
	///*
	public function getParentProcess() : ?self {
		return $this->parent;
	}
	protected function __construct( self $parentProcess ) {
		parent::__construct( $parentProcess );
	}
	/*
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		$this->addtoSerialize("pid");
	}
	/*
	public function stop() : void {
		$this->log->trace( "[".$this->getClassName()."/".$this->getInstanceKey()."]" );
		//$this->log->trace(debug_backtrace()[0]);
		parent::stop();
	}
	public function sendTerm( ) : void {
		$this->sendSignal( \SIGTERM );
	}
	public function sendKill( ) : void {
		$this->sendSignal( \SIGKILL );
	}
	public function sendSignal( int $signo ) {
		\posix_kill( $this->pid , $signo );
		if ( \function_exists("pcntl_signal_dispatch") ) \pcntl_signal_dispatch();
	}
	//*/
	public function isMyProcess() : bool {
		return $this->pid == \getmypid();
	}
	/* -------------------------------------------------------------- */
	protected function init( ) : void {
		//parent::init();
		$this->setPid( \getmypid() );
	}
	
	private $dwCallBackCheckTitle = null;
	public function setProcessTitle( string $processTitle ) {
		if ( \function_exists( 'cli_set_process_title' ) ) {
			if ( \cli_set_process_title( $processTitle ) )
				$this->checkProcessTitle( $processTitle );
			else {
				if ( \is_object( $this->dwCallBackCheckTitle ) )
					$this->cancelCallBack( $this->dwCallBackCheckTitle );
				$this->dwCallBackCheckTitle = new DwCallBack(
					array( &$this ,"checkProcessTitle" )
					,$processTitle
				);
				$this->addTick( $this->dwCallBackCheckTitle );
			}
		}
	}
	public function checkProcessTitle( string $processTitle ) {
		if ( \function_exists( 'cli_get_process_title' ) ) {
			if ( \cli_get_process_title() != $processTitle ) 
				$this->setProcessTitle( $processTitle );
		}
	}
	public function toString( $withParent = false ) : string {
		$objStr = parent::toString( $withParent );
		if ( isset( $this->pid ) && ! \is_null( $this->pid ) )
			$objStr.="/pid:{$this->getPid()}";
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
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ 
			: __CLASS__
		;
	}
}

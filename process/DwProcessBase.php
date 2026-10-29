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
//use bakuba\kernel\process\DwProcessBase as DwProcessBase;
use \Exception as Exception;
use \Error as Error;
use \DateTime as DateTime;

use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use darkphp\logger\DwLoggerFolder as DwLoggerFolder;
use darkphp\logger\DwLoggerFile as DwLoggerFile;
use darkphp\logger\DwLogger as DwLogger;

interface iDwProcessBase {
//	public function setPid( int $myPid );
//	public function getPid() : int;
//	public function isMyProcess() : bool;
	
//	protected function initLogFile( string $fileName  ) : void;
//	protected function getDwLoggerFolder( string $folderUp ) : DwLoggerFolder;
}

abstract class DwProcessBase extends \bakuba\kernel\time\timer\DwObjectTimer implements iDwProcessBase,DwIRunnable {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		//echo "\r\n devrais:|{$className}|\r\n";
		
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		else
			parent::setDebug( $iDwLogger );
		
		//$this->log->trace( "." );
	}
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
	///*
	public function getParentProcess() : ?self {
		return $this->parent;
	}
	protected function __construct( self $parentProcess ) {
		$newTimer = false;
		///*
		if ( $parentProcess->isDwTimersReady() )
			$dwTimers = $parentProcess->getDwTimersChild( $this );
		else {
			$newTimer=true;
			$dwTimers = new \bakuba\kernel\time\timer\DwTimers( $this );
		}
		//*/
		parent::__construct( $parentProcess ,$dwTimers );
		if ( ! $newTimer )
			$dwTimers->setDebug( $this->log );
		$this->log->trace( $newTimer ? "is new timer" : "use child timer" );
	}
	/* -------------------------------------------------------------- */
	protected function init( ) : void {
		//parent::init();
	}
	private $onAliveCb = array();
	private $onCloseCb = array();
	public function onAlive( DwCallBack $dwCallBack ) : void {
		if ( $this->alive_state )
			$dwCallBack->callback();
		else 
			$this->onAliveCb[] = $dwCallBack;
	}
	public function setAlive( bool $alive_state ) : void {
		$this->log->info(
			"alive_state:{$this->alive_state} => {$alive_state}"
		);
		if ( $this->alive_state == true )
			$this->log->warn("already done");
		
		if ( ! empty($this->onAliveCb) ) {
			foreach( $this->onAliveCb as $index => $dwCallBack ) {
				$this->addTick( $dwCallBack );
				unset($this->onAliveCb[$index]);
			}
		}
		$this->alive_state = $alive_state;
		
	}
	
	public function onClose( DwCallBack $dwCallBack ) : void {
		$this->onCloseCb[] = $dwCallBack;
	}
	public function stop() : void {
		$this->log->trace( "." );
	}
	public function stopProcess() : void {
		$debugKey ="{$this->getClassName()}/{$this->getInstanceKey()}";
		$this->log->trace( "[{$debugKey}]" );
		//$this->log->trace(debug_backtrace()[0]);
		
		if ( ! empty($this->onCloseCb) ) {
			$this->log->trace( "[{$debugKey}] onCloseEvent" );
			foreach( $this->onCloseCb as $index => $dwCallBack ) {
				try {
					$dwCallBack->callback();
				} catch(\Exception $exception_cb) {
					$this->log->error("[{$debugKey}] exception_cb" );
					$this->log->error( $exception_cb );
					//throw $exception_cb;
				} catch(\Error $error_cb) {
					$this->log->error("[{$debugKey}] error_cb" );
					$this->log->error( $error_cb );
					//throw $error_cb;
				}
				unset($this->onCloseCb[$index]);
			}
			foreach( $this->listProcess as $keyProcess => $dwProcess )
				$dwProcess->sendKill();
		}
		
		$this->stop();
		//$this->isFinish=true;
		
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

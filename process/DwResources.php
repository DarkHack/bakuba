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
//use bakuba\kernel\process\DwResources as DwResources;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwResources extends \darkphp\DwObject implements \bakuba\kernel\time\iDwTickQueue,\bakuba\kernel\time\timer\iDwObjectTimer {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToFirstSectionName( $this->getClassName() )
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	private function getDwObjectTimer() : \bakuba\kernel\time\timer\DwObjectTimer {
		return $this->getDwProcessResources();
	}
	public function getDwProcessResources() : \bakuba\kernel\process\DwProcessResources {
		return $this->parent;
	}
	public function __construct( \bakuba\kernel\process\DwProcessResources $parent ) {
		parent::__construct( $parent );
		$this->init();
	}
	protected function init(): void {
		//parent::init();
		//$this->log->trace(".");
	}
	// return true, if parent or no fork
	// return false, if child forked and finish or exception
	public function startProcess( \bakuba\kernel\process\DwIRunnable $dwProcess ) : void {
		$this->log->trace( $dwProcess );
		
		//$this->setProcessRun( $dwProcess );
		$dwProcess->setDwResources( $this );
		if ( $dwProcess->start() ) {
			// $dwProcess->start() : true if parent process and should continue
			if ( \method_exists( $dwProcess ,'isFork' ) && $dwProcess->isFork() )
				$this->log->trace( "start:true isFork:true end?" .( $dwProcess->isEnd()?"true":"false" ) );
		} else {
			// $dwProcess->start()
			// => return false if child process and all will stop
			die("child process end");
			/*
			self::$isChildInterrupt = true;
			$return=false;
			if ( $dwProcess->isFork() ) {
				$this->log->trace(
					"[{$debugKey}] start:false isFork:true, end?"
					.( $dwProcess->isEnd()?"true":"false" )
				);
			} else
				throw new \Exception("[{$debugKey}] start:false, isFork:false, fork echec ?");
			//*/
		}
		//$this->log->trace( $this->getProcessState() );
		//$this->log->trace( $dwProcess->getProcessState() );
		
		//return $return;
	}
	private $ipcRouting = null;
	public function getDwIpcRouting() : \bakuba\kernel\io\ipc\DwIpcRouting {
		if ( is_null( $this->ipcRouting ) )
			$this->ipcRouting = new \bakuba\kernel\io\ipc\DwIpcRouting( $this );
		return $this->ipcRouting;
	}
	private $dwIpcSelect = null;
	public function getIpcSelect( \darkphp\logger\iDwLogger $iDwLogger = null ) : \bakuba\kernel\io\ipc\DwIpcSelect {
		if ( \is_null( $this->dwIpcSelect ) ) {
			if ( \is_null( $iDwLogger ) ) $iDwLogger = $this->log;
			//$dwResources = $this->getNewResources( $iDwLogger );
			//$dwResources->enableDebug( true );
			//$this->dwIpcSelect =  new DwIpcSelect( $dwResources );
			$this->log->trace(" create ipc select." );
			$this->dwIpcSelect =  new \bakuba\kernel\io\ipc\DwIpcSelect( $this );
			//$this->dwIpcSelect->enableDebug( true );
		}
		return $this->dwIpcSelect;
	}
	private $dwSocketSelect = null;
	public function getSocketSelect( \darkphp\logger\iDwLogger $iDwLogger = null ) : \bakuba\kernel\io\socket\DwSocketSelect {
		if ( \is_null( $this->dwSocketSelect ) ) {
			if ( \is_null( $iDwLogger ) ) $iDwLogger = $this->log;
			//$dwResources = $this->getNewResources( $iDwLogger );
			//$dwResources->enableDebug( true );
			//$this->dwSocketSelect =  new DwSocketSelect( $dwResources );
			$this->log->trace(" create socket select." );
			$this->dwSocketSelect =  new \bakuba\kernel\io\socket\DwSocketSelect( $this );
			$this->refSelectForCheckActivity( "dwSocketSelect" ,$this->dwSocketSelect );
			//$this->dwSocketSelect->enableDebug( true );
		}
		return $this->dwSocketSelect;
	}
	private $dwStreamSelect = null;
	public function getStreamSelect( \darkphp\logger\iDwLogger $iDwLogger = null ) : \bakuba\kernel\io\stream\DwStreamSelect {
		if ( \is_null( $this->dwStreamSelect ) ) {
			$this->log->trace(" create stream select." );
			$this->dwStreamSelect = new \bakuba\kernel\io\stream\DwStreamSelect( $this );
			$this->refSelectForCheckActivity( "dwStreamSelect" ,$this->dwStreamSelect );
		}
		return $this->dwStreamSelect;
	}
	private $dwLocalAgent = null;
	public function getDwLocalAgent() : child\DwLocalAgent {
		if ( \is_null( $this->dwLocalAgent ) )
			$this->dwLocalAgent = child\DwLocalAgent::getLocalAgent( $this );
		return $this->dwLocalAgent;
	}
	private $dwResourcesSelectToCheck = array();
	public function refSelectForCheckActivity(
		string $resourceSelectName
		,\bakuba\kernel\io\DwResourceSelect $dwResourcesSelect
	) : void {
		$this->dwResourcesSelectToCheck[ $resourceSelectName ] = $dwResourcesSelect;
	}
	protected function checkActivity() : bool {
		$activity=false;
		foreach( $this->dwResourcesSelectToCheck as $resourceSelectName => $dwResourcesSelect ) {
			if ( ! $activity && ! is_null( $dwResourcesSelect )
			&& $dwResourcesSelect->checkActivity() < 5 ) {
				$this->log->warn(
					"{$resourceSelectName} activity "
					."{$dwResourcesSelect->checkActivity()}"
				);
				$activity=true;
			}
		}
		return $activity;
	}
	
	///*
	public function fileRead(
		string $filePath
		,DwCallBack $onReaded = null
	) {
		$fp = \fopen($filePath, "r");
		if ( $fp === false )
			throw new Exception(" echec on fopen:$filePath");
		else {
			$dwStream = new \bakuba\kernel\io\stream\DwStream( $this ,$fp );
			if ( \is_object( $onReaded ) )
				$dwStream->setOnReaded( new DwCallBack( array( &$this, 'fileReaded' ) , $onReaded ,$dwStream ) );
			$this->getStreamSelect()->streamRead( $dwStream );
		}
	}
	public function fileReaded(
		DwCallBack $onReaded
		,\bakuba\kernel\io\stream\DwStream $dwStream
	) {
		$onReaded->callback( $dwStream->read() );
	}
	public function fileWrite(
		string $filePath
		,string $data
		,DwCallBack $onWrited = null
	) : void {
		$fp = \fopen($filePath, "w");
		if ( $fp === false )
			throw new \Exception(" echec on fopen:$filePath");
		else {
			$dwStream = new DwStream( $this ,$fp );
			if ( \is_object( $onWrited ) ) $dwStream->setOnWrited( $onWrited );
			$dwStream->write( $data );
			$this->getStreamSelect()->streamWrite( $dwStream );
		}
	}
	//*/
	
	public function cancelTimer( \bakuba\kernel\time\timer\iDwTimer $dwTimer ) : bool {
		return $this->getDwObjectTimer()->cancelTimer( $dwTimer );
	}
	public function addPeriodicTimer( float $sInterval ,DwCallBack $dwCallBack ) : \bakuba\kernel\time\timer\DwTimer {
		return $this->getDwObjectTimer()->addPeriodicTimer( $sInterval ,$dwCallBack );
	}
	public function addTimer( float $sInterval ,DwCallBack $dwCallBack ) : \bakuba\kernel\time\timer\DwTimer {
		return $this->getDwObjectTimer()->addTimer( $sInterval ,$dwCallBack );
	}
    public function addTick( DwCallBack $dwCallBack ) : void {
		$this->getDwObjectTimer()->addTick( $dwCallBack );
	}
    public function cancelCallBack( DwCallBack $dwCallBack ) : bool {
		return $this->getDwObjectTimer()->cancelCallBack( $dwCallBack );
	}
	public function isEverSchudeled( DwCallBack $dwCallBack ) : bool {
		return $this->getDwObjectTimer()->isEverSchudeled( $dwCallBack );
	}
	public function count() : int {
		return $this->getDwObjectTimer()->count();
	}
	public function hasTick() : bool {
		return $this->getDwObjectTimer()->hasTick();
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

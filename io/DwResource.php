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

namespace bakuba\kernel\io;
//use bakuba\kernel\io\DwResource as DwResource;

use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
//use bakuba\kernel\time\DwChrono as DwChrono;

abstract class DwResource extends DwObject implements iDwResource {
	
	private $fullTraceEnabled = false;
	protected function enableFullTrace() : void {
		$this->fullTraceEnabled = true;
	}
	protected function isFullTrace() : bool {
		return $this->fullTraceEnabled;
	}
	public function __construct( DwObject $parent ,$resource = null ) {
		parent::__construct( $parent );
		
		//$this->resource = $resource;
		if ( ! is_null( $resource ) )
			$this->connect( $resource );
		
		//$this->log->trace(".");
	}
	private $resource = null;
	public function isResource() : bool {
		return ( ! is_null($this->resource) ) && is_resource($this->resource);
	}
	public function isResourceDefined() : bool {
		return ! is_null( $this->resource );
	}
	public function getResource() {
		return $this->resource;
	}
	public function connect( $resource = null ) : bool {
		//$this->log->trace( $resource );
		$this->resource = $resource;
		
		return $this->isResource();
	}
	public function disconnect( ) : bool {
		//$this->log->trace(".");
		unset( $this->resource );
		$this->resource = null;
		
		return ! $this->isResource();
	}
	private $timerWriteTimeout = null;
	public function writeLunch( DwResourceSelect $dwResourceSelect ) : void {
		$this->timerWriteTimeout = $dwResourceSelect->getDwObjectTimer()->addTimer(
			120
			,new DwCallBack( array( &$this ,"writeTimeout" ) ,$this )
		);
		
		$dwResourceSelect->resourceWrite( $this );
	}
	public function writeCancel( DwResourceSelect $dwResourceSelect ) : void {
		$dwResourceSelect->resourceWriteCancel( $this ,$this->timerWriteTimeout );
		$this->timerWriteTimeout = null;
	}
	public function writeTimeout( DwResourceSelect $dwResourceSelect ,DwCallBack $onTimeout = null ) {
		if ( $this->checkActivity() )
			$this->timerWriteTimeout = $dwResourceSelect->getDwObjectTimer()->addTimer(
				120
				,new DwCallBack( array( &$this, "writeTimeout" ) ,$dwResourceSelect ,$onTimeout )
			);
		else {
			$this->log->warn( "no more activity, timeout!!!" );
			$this->writeCancel( $dwResourceSelect );
			if ( \is_object( $onTimeout ) ) {
				//$this->getDwObjectTimer()->addTick( $onTimeout-> );
				$this->log->trace( $onTimeout->printHandle() );
				$onTimeout->callback( $this );
			}
		}
	}
	public function writed( DwResourceSelect $dwResourceSelect ) : void {
		$this->writeCancel( $dwResourceSelect );
		
		$onWrited = $this->getOnWrited();
		if ( is_object ( $onWrited ) ) 
			$dwResourceSelect->getDwObjectTimer()->addTick( $onWrited );
		
		//$this->log->trace( $this->getClassName() );
		if ( \is_object( $this->timerWriteTimeout ) ) {
			$dwResourceSelect->getDwObjectTimer()->cancelTimer( $this->timerWriteTimeout );
			$this->timerWriteTimeout=null;
		}
	}
	/************************************/
	private $timerReadTimeout = null;
	protected function readAction( \bakuba\kernel\io\DwResourceSelect $dwResourceSelect ) : void {
		$packetReceived=0;$totalReceived=0;
		if ( ( $packetReceived = $packeteceived=$this->receive() ) >= $this->getRcvSize() && $packetReceived > 0 ) {
			$totalReceived+=$packetReceived;
			$this->log->trace( "pipe exceded read:{$packeteceived}/{$totalReceived}" );
			//$this->log->trace( $this->read() );
			$packetReceived=0;
			$dwResourceSelect->resourceRead( $this );
		} elseif ( $packetReceived > 0 ) {
			$totalReceived+=$packetReceived;
			//$this->log->trace( "[[$readHandleKey}]pipe ended total read:{$totalReceived}" );
			//$this->log->trace( $dwResource->read() );
			$this->readed( $dwResourceSelect );
		} else {
			//$this->log->trace( "[[$readHandleKey}] resourceRead" );
			$dwResourceSelect->resourceRead( $this );
		}
	}
	public function readLunch( DwResourceSelect $dwResourceSelect ) : void {
		//$this->log->trace( "." );
		//$this->readsHandle[ $readHandleKey ] = $onReaded;
		if ( $this->isListen() )
			$dwResourceSelect->resourceRead( $this );
		else {
			$this->timerReadTimeout = $dwResourceSelect->getDwObjectTimer()->addTimer(
				120
				,new DwCallBack( array( &$this ,"readTimeout" ) ,$dwResourceSelect )
			);
			$this->readAction( $dwResourceSelect );
		}
	}
	public function readCancel( DwResourceSelect $dwResourceSelect ) : void {
		$dwResourceSelect->resourceReadCancel( $this, $this->timerReadTimeout );
		$this->timerReadTimeout = null;
	}
	public function readTimeout( DwResourceSelect $dwResourceSelect ) {
		if ( $this->isListen() ) 
			$this->log->warn("timeout on listen ressource, ignored!!!");
		else {
			if ( $this->checkActivity() )
				$this->timerReadTimeout = $dwResourceSelect->getDwObjectTimer()->addTimer(
					120
					,new DwCallBack( array( &$this ,"readTimeout" ) ,$dwResourceSelect )
				);
			else {
				$onTimeout = $this->getOnTimeout();
				if ( \is_object( $onTimeout ) ) {
					$dwResourceSelect->getDwObjectTimer()->addTick( $onTimeout );
					$this->log->trace( "timeout tick add" );
				}
				$this->log->warn("no more activity, timeout!!!");
				$this->log->error( $dwResource );
				$dwResourceSelect->readCancel( $dwResource );
				
			}
		}
	}
	public function readed( DwResourceSelect $dwResourceSelect ) : void {
		//$this->log->trace( $this->isListen() ? "isListen" : "noListen" );
		$onReaded = $this->getOnReaded();
		if ( is_object( $onReaded ) ) {
			//$this->log->trace( $onReaded->printHandle() );
			$dwResourceSelect->getDwObjectTimer()->addTick( $onReaded );
		} else {
			$this->log->error( "no callback in read" );
			$this->log->error( $this );
		}
		if ( ! $this->isListen() ) {
			$this->readCancel( $dwResourceSelect );
			//self::$sLog->trace( "." );
		}
		/*
		else
			$this->log->info( " is Listen no readCancel" );
		//*/
	}
	/************************************/
	private $listening = false;
	public function isListen() : bool {
		return $this->listening;
	}
	public function enableListen() {
		$this->listening = true;
	}
	public function disableListen() {
		$this->listening = true;
	}
	/***********************************/
	private $cbOnReaded=null;
	public function setOnReaded( DwCallBack $cbOnReaded ) : void {
		if ( is_null( $this->cbOnReaded ) )
			$this->cbOnReaded = $cbOnReaded;
		else
			throw new \Exception( " on readed callback ever defined. " );
	}
	public function getOnReaded( ) : ?DwCallBack {
		return $this->cbOnReaded;
	}
	/***********************************/
	private $cbOnTimeout=null;
	public function setOnTimeout( DwCallBack $cbOnTimeout ) : void {
		if ( is_null( $this->cbOnTimeout ) )
			$this->cbOnTimeout = $cbOnTimeout;
		else
			throw new \Exception( " on timeout callback ever defined. " );
	}
	public function getOnTimeout( ) : ?DwCallBack {
		return $this->cbOnTimeout;
	}
	/***********************************/
	private $cbOnWrited=null;
	public function setOnWrited( DwCallBack $cbOnWrited ) : void {
		if ( is_null( $this->cbOnWrited ) )
			$this->cbOnWrited = $cbOnWrited;
		else
			throw new \Exception( " on readed callback ever defined. " );
	}
	public function getOnWrited( ) : ?DwCallBack {
		return $this->cbOnWrited;
	}
	/***********************************/
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

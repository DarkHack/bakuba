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

namespace bakuba\kernel\time;
//use bakuba\kernel\time\DwObjectTick as DwObjectTick;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwObjectTick extends \darkphp\DwObject implements iDwTickQueue {
	private $dwTickQueue = null;
	private function getDwTickQueue() : ?iDwTickQueue {
		return $this->dwTickQueue;
	}
	protected function getDwTickQueueChild( \darkphp\DwObject $parent ) : iDwTickQueue {
		return $this->dwTickQueue->getDwTickQueueChild( $parent );
	}
	/*
	protected function setDwTickQueue( iDwTickQueue $dwTickQueue ) : void {
		$this->dwTickQueue = $dwTickQueue;
	}
	//*/
	
	private $dwChrono = null;
	private $dwChronoEnable = false;
	public function enableStats( DwChrono $dwChrono = null ) {
		$this->log->info(".");
		$this->dwChronoEnable=true;
		if ( \is_null( $dwChrono ) ) $dwChrono = new DwChrono( $this );
		$this->dwChrono = $dwChrono;
	}
	public function __construct( \darkphp\DwObject $parent ,iDwTickQueue $dwTickQueue ) {
		$this->dwTickQueue = $dwTickQueue;
		parent::__construct( $parent );
		$this->enableStats();
	}
	protected $isFinish = false;
	public function isEnd() : bool {
		return $this->isFinish;
	}
	public function stop() : void {
		$this->isFinish=true;
		$this->log->trace( "stop" );
		//$this->log->trace(debug_backtrace()[0]);
	}
	
	public function hasTick() : bool {
		return $this->dwTickQueue->hasTick();
	}
	public function cancelCallBack( DwCallBack $dwCallBack ) : bool {
		return $this->dwTickQueue->cancelCallBack( $dwCallBack );
	}
	public function isEverSchudeled( DwCallBack $dwCallBack ) : bool {
		return $this->dwTickQueue->isEverSchudeled( $dwCallBack );
	}
	public function addTick( DwCallBack $dwCallBack ) : void {
		$this->dwTickQueue->addTick($dwCallBack);
	}
	public function count() : int {
		return $this->dwTickQueue->count();
	}
	private $statCpt=0;
	public function tick() : void {
		if ( $this->dwChronoEnable ) {
			$this->dwChrono->tick();
			$this->statCpt++;
			if ( $this->statCpt % 1000 == 0 ) {
				$this->log->trace( "total:".$this->statCpt.\PHP_EOL.$this->dwChrono->printStats() );
			}
		}
		$this->dwTickQueue->tick();
	}
	///*
	public function __destruct() {
		if ( isset( $this->log ) && \is_object( $this->log ) )
			if ( $this->dwChronoEnable )
				$this->log->trace( $this->dwChrono->printStats() );
			else
				$this->log->trace( "destruct" );
		
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
	public static $maxTicks=-1;
	//\bakuba\kernel\time\DwObjectTick::run( $dwObjectTick );
	public static function run( self $dwObjectTick ) : void {
		/*
		$dwObjectTick->log->trace( "set error/exception handle" );
		\set_error_handler( array( &$dwObjectTick->log ,'error_handle' ) );
		\set_exception_handler( array( &$dwObjectTick->log ,'throwable' ) );
		//*/
//		declare(ticks=10000) {
		$dwObjectTick->log->trace( $dwObjectTick );
		$cpt=0;
		while ( $dwObjectTick->hasTick() && ( self::$maxTicks == -1 || ( $cpt++ < self::$maxTicks ) ) )
			$dwObjectTick->tick();
//		}	
		$dwObjectTick->isEnd() ? $dwObjectTick->log->trace( "ended" ) : $dwObjectTick->log->error( "not ended ( but no more tick )" );
		
		/*
		\restore_error_handler();
		\restore_exception_handler();
		$dwObjectTick->log->trace( "remove error/exception handle" );
		//*/
	}
	public static function test(
		\darkphp\logger\iDwLogger $iDwLogger
		,\darkphp\serializable\iDwNoSerializable $dwToTest = null
	) : \darkphp\serializable\iDwNoSerializable {
		$nbLoop = 5000;
		if ( is_null( $dwToTest ) ) {
			$dwToTest = new self( $iDwLogger );
			//$dwToTest->setDebug( $iDwLogger );
		}
		$dwCallBack = null;
		$dwChronoStartStop = new DwChronoStartStop( $dwToTest );
		///*
		$cpt=0;
		$dwCallBack = new DwCallBack(
			function( DwChronoStartStop $dwChronoStartStop )
				use ( $dwToTest ,&$dwCallBack ,&$cpt, &$nbLoop, &$iDwLogger ) {
				// 
				if ( $cpt < $nbLoop )
					$dwToTest->addTick( $dwCallBack );
				else 
					$iDwLogger->trace(
						"dwCallBack1 stats:\r\n"
						.$dwChronoStartStop->printStats()
						."==============================\r\n"
					);
				$cpt++;
			}
			,$dwChronoStartStop
		);
		$dwCallBack->enableStats( $dwChronoStartStop );
		$dwToTest->addTick( $dwCallBack );
		//*/
		///*
		$dwChronoStartStop2 = new DwChronoStartStop( $dwToTest );
		$dwCallBack2 = new DwCallBack( function( ) { } );
		$dwCallBack2->enableStats( $dwChronoStartStop2 );
		///*
		for ( $anCpt=0;$anCpt<$nbLoop;$anCpt++ )
			$dwToTest->addTick( $dwCallBack2 );
		
		$dwToTest->addTick( new DwCallBack(
			function( ) use ( $dwChronoStartStop2, $iDwLogger ) {
				$iDwLogger->trace(
					"dwCallBack2 stats:\r\n"
					.$dwChronoStartStop2->printStats()
					."==============================\r\n"
				);
			}
		) );
		$dwToTest->addTick( new DwCallBack( array( &$dwToTest,'stop' ) ) );
		self::run( $dwToTest );
		//*/
		return $dwToTest;
	}
}

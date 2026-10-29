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

namespace bakuba\kernel\time\timer;
//use bakuba\kernel\time\timer\DwObjectTimer as DwObjectTimer;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\time\timer\DwTimer as DwTimer;
use bakuba\kernel\time\timer\DwTimers as DwTimers;
use bakuba\kernel\time\timer\iDwTimer as iDwTimer;

//use bakuba\kernel\process\DwTickQueue as DwTickQueue;
interface iDwObjectTimer {
//	public function nextTimeout() : ?int;
	public function cancelTimer(
		iDwTimer $dwTimer
	) : bool;
	public function addPeriodicTimer(
		float $sInterval
		,DwCallBack $dwCallBack
	) : DwTimer ;
	public function addTimer(
		float $sInterval
		,DwCallBack $dwCallBack
	) : DwTimer;
}

class DwObjectTimer extends \bakuba\kernel\time\DwObjectTick implements iDwObjectTimer {
	//private $dwCallBackTimerTick = null;
	private $dwTimers = null;
	private function getDwTimers( ) : iDwTimers {
		return $this->dwTimers;
	}
	public function isDwTimersReady( ) : bool {
		return ! \is_null( $this->dwTimers ) && \is_object( $this->dwTimers );
	}
	public function getDwTimersChild( \darkphp\DwObject $parent ) : iDwTimers {
		return $this->dwTimers->getDwTimersChild( $parent );
	}
	public function states() : array {
		return $this->dwTimers->states();
	}
	/*
	private function getDwTickQueue() : ?\bakuba\kernel\time\iDwTickQueue {
		return $this->dwTimers;
	}
	protected function getDwTickQueueChild( \darkphp\DwObject $parent ) : ?\bakuba\kernel\time\iDwTickQueue {
		return $this->dwTimers->getDwTickQueueChild( $parent );
	}
	//*/
	public function __construct( \darkphp\DwObject $parent ,DwTimers $dwTimers ) {
		$this->dwTimers = $dwTimers;
		/*
		$this->dwTimers = new DwTimers( $this );
		$isNewTimer = \is_null( $parent ) || ! $parent->isDwTimersReady();
		if ( $isNewTimer )
			$this->dwTimers = new DwTimers( $this );
		else
			$this->dwTimers = $parent->getDwTimersChild( $this );
		//*/
		parent::__construct( $parent ,$this->dwTimers );
		
		
		//$tickeu=is_null($this->dwTimers);
		//$this->log->trace( $isNewTimer ? "new timer (".($tickeu?"isnull":"isnotnull").")":"use depend timer");
	}
	//*/
	public function addTimer( float $fSecondeInterval ,DwCallBack $dwCallBack ) : DwTimer {
		$dwTimer =null;
		//if ( $this->isEverSchudeled( $dwCallBack ) )
		//	$this->cancelCallBack( $dwCallBack );
		/*
		$this->log->trace(
			"in $fSecondeInterval s\r\n"
			.$dwCallBack->printHandle()
		);
		//*/
		$dwTimer = new DwTimer( $fSecondeInterval, $dwCallBack, false );
		$this->dwTimers->addTimer( $dwTimer );
		
		return $dwTimer;
	}
	public function addPeriodicTimer( float $fSecondeInterval ,DwCallBack $dwCallBack ) : DwTimer {
		$dwTimer =null;
		$this->log->trace(
			"every $fSecondeInterval s\r\n"
			.$dwCallBack->printHandle()
		);
		$dwTimer = new DwTimer( $fSecondeInterval ,$dwCallBack ,true );
		$this->dwTimers->addTimer($dwTimer);
		return $dwTimer;
	}
	
	public function cancelTimer( iDwTimer $iDwTimer ) : bool {
		return $this->dwTimers->cancel( $iDwTimer );
	}
	
	public function updateTime() : int {
		return $this->dwTimers->updateTime();
	}
	public function printHandleFirst() : string {
		$strHandleFirst = $this->dwTimers->printHandleFirst();
		if ( \is_null( $strHandleFirst ) )
			$strHandleFirst="empty handle";
		return $strHandleFirst;
	}
	
	public function sleep() : void {
		$this->dwTimers->sleep();
	}
	//*/
	///*
	public function __destruct() {
		if ( isset( $this->log ) && \is_object( $this->log ) )
			$this->log->trace( "\r\n\tDESTRUCT [{$this->toString()}] count:{$this->count()}" );
		if ( isset( $this->queue ) && \is_object( $this->queue ) ) {
			$count = $this->count();
			while ( $count-- )
				$this->queue->dequeue();
			unset( $this->queue );
		}
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

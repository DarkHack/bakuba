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
//use bakuba\kernel\time\timer\DwTimersChild as DwTimersChild;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

/**
 * A scheduler implementation that can hold multiple timer instances
 *
 * This class should only be used internally, see iDwTimer instead.
 *
 * @see iDwTimer
 * @internal
 */
final class DwTimersChild extends DwTimers /* implements iDwTimers */ {
	/*
	public function getParentClassName() : string {
		return $this->parent->getClassName();
	}
	public function getDwTimersChild() : iDwTimers {
		return new DwTimersChild( $this );
	}
	private $childsTimer = array();
	//*/
	private $parentDwTimers = null;
	public function __construct( \darkphp\DwObject $parent ,DwTimers $parentDwTimers ) {
		$this->parentDwTimers = $parentDwTimers;
		$this->setDebug( $this->parentDwTimers->log );
		parent::__construct( $parent ,$parentDwTimers );
		$this->registerChild();
		//$this->reset();
	}
	/*
    public function addTimer( iDwTimer $iDwTimer ) : bool {
		$dwCallBack = $iDwTimer->getDwCallBack();
		$key = $dwCallBack->getInstanceKey();
		if ( $this->isEverSchudeled( $dwCallBack ) )
			$this->cancelCallBack( $dwCallBack );
		$this->childsTimer[ $key ] = $iDwTimer;
		return $this->parent->addTimer( $iDwTimer );
    }
	public function isEverSchudeled( DwCallBack $dwCallBack ) : bool {
		return isset( $this->childsTimer[ $dwCallBack->getInstanceKey() ] ) || parent::isEverSchudeled( $dwCallBack );
	}
	
    public function cancelCallBack( DwCallBack $dwCallBack ) : bool {
		$found = false;
		$key = $dwCallBack->getInstanceKey();
		if ( isset( $this->childsTimer[ $key ] ) ) {
			$this->parent->cancel( $this->childsTimer[ $key ] );
			unset( $this->childsTimer[ $key ] );
			$found=true;
		}
		if ( parent::isEverSchudeled( $dwCallBack ) )
			$found=parent::cancelCallBack( $dwCallBack );
		//if ( \is_null( $this->log ) )
		//	echo "\r\nNo Log for this timeChild\r\n{$this->toString()}\r\n";
		//else
		//	$this->log->trace( $dwCallBack );
		
		return $found;
    }
    
    public function cancel( iDwTimer $iDwTimer ) : bool {
		//$this->log->trace( $iDwTimer );
		$found=false;
		$key = $iDwTimer->getDwCallBack()->getInstanceKey();
		if ( ! isset( $this->childsTimer[ $key ] ) )
			throw new \Exception('timer not found');
		else {
			$found=true;
			$this->parent->cancel( $this->childsTimer[ $key ] );
			unset( $this->childsTimer[ $key ] );
		}
		return $found;
    }
	//*/
	
    public function tick() : void {
		// Only invoke as many callbacks as were on the queue when tick() was called.
		///*
		if ( isset( $this->log ) && \is_object( $this->log) ) {
			$count = $this->count();
			$this->log->trace( "count:{$count}" );
		} else
			echo "NoLog For DwTimers-TICK:[{$this->toString()}]".\PHP_EOL;
		//*/
		//parent::tick();
		
		parent::tick();
		//$this->dwTimersChildsTick();
	}
	public function sleep() {
		if ( \is_object( $this->log ) )
			$this->log->trace( "no sleep in time child" );
		else
			echo \PHP_EOL."no sleep in time child".\PHP_EOL;
	}
	private $registered=false;
	private function registerChild() : void {
		$this->parentDwTimers->addDwTimersChild( $this );
		$this->registered=true;
	}
	protected function unregisterChild() : void {
		if ( $this->registered ) {
			$this->parentDwTimers->removeDwTimersChild( $this );
			$this->registered=false;
		}
	}
	public function __destruct() {
		//$this->log->trace( "." );
		$this->unregisterChild();
		//print_r( debug_backtrace( false ) );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function toString( $withParent = false ) : string {
		return parent::toString( $withParent )."[".$this->getParentClassName()."]";
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

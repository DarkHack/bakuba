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
//use bakuba\kernel\time\timer\DwTimers as DwTimers;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

/**
 * A scheduler implementation that can hold multiple timer instances
 *
 * This class should only be used internally, see iDwTimer instead.
 *
 * @see iDwTimer
 * @internal
 */
class DwTimers extends \bakuba\kernel\time\DwTickQueue implements iDwTimers {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className = $this->getClassName();
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
//				,\darkphp\source\DwSource::classNameToFirstSectionName( $className )
				,\darkphp\source\DwSource::classNameToTopName( $className )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	
	private $dwTimersChilds = array();
	public function getDwTimersChild( \darkphp\DwObject $parent ) : iDwTimers {
		$dwTimersChild = new DwTimersChild( $parent ,$this );
		return $dwTimersChild;
	}
	public function states() : array {
		$myStates = array( $this->parent->getClassName() => $this->count() );
		foreach( $this->dwTimersChilds as $dwTimerChild )
			$myStates[ $dwTimerChild->getParentClassName() ] = $dwTimerChild->states();
		
		return $myStates;
	}
	public function addDwTimersChild( DwTimersChild $dwToAdd ) : void {
		$key = $dwToAdd->getInstanceKey();
		if ( isset( $this->log ) )
			$this->log->info( "\r\n\t key:{$key}");
		else
			echo "\r\n\Add [{$key}] :No Log for:{$this->toString()}";
		$this->dwTimersChilds[ $key ] = $dwToAdd; 
	}
	public function removeDwTimersChild( DwTimersChild $dwToRemove ) : void {
		$key = $dwToRemove->getInstanceKey();
		if ( isset( $this->log ) )
			$this->log->info( "\r\n\t key:{$key}");
		else
			echo "\r\n\tRemove [{$key}] :No Log for:{$this->toString()}";
		
		if ( isset( $this->dwTimersChilds[ $key ] ) )
			unset( $this->dwTimersChilds[ $key ] );
		else
			throw new \Exception( "child no found [key:'{$key}'] timer:{$dwToRemove->toString()}" );
	}
	
    private $dwTime = null;
    private $timers = null;
    private $schedule = null;
    private $sorted = null;
    private $handleSleep = null;
    
	public function __construct( \darkphp\DwObject $parent ) {
		parent::__construct( $parent );
		$this->handleSleep = new DwCallBack( array( &$this, 'sleep' ) );
		$this->addTick( $this->handleSleep );
		
		$this->dwTime = new \bakuba\kernel\time\DwTime( );
		$this->timers = array();
		$this->schedule = array();
		$this->sorted = true;
		//$this->log->trace( "." );
		//$this->reset();
		//$this->log->trace( "." );
		
		if ( \is_object( $this->log) )
			$this->log->trace( "\r\n\t count:{$this->count()} [{$this->toString()}]" );
		//else
		//	echo "NoLog For DwTimers:[{$this->toString()}]".\PHP_EOL;
	
		//$this->log->trace( "\r\n\t[{$this->toString()}] count:{$this->count()}" );
    }
	
	protected function dwTimersChildsHasTick() : bool {
		$found = false;
		foreach( $this->dwTimersChilds as $dwTimersChild )
			if ( $found |= $dwTimersChild->hasTick() )
				break;
		return $found;
	}
	public function hasTick() : bool {
		return parent::hasTick() || $this->dwTimersChildsHasTick();
	}
	protected function dwTimersChildsTick() : void {
		foreach( $this->dwTimersChilds as $dwTimersChild ) {
			if ( $dwTimersChild->hasTick() ) {
				//if ( isset( $this->log ) && \is_object( $this->log) )
				//	$this->log->trace( $dwTimersChild->getParentClassName() );
				$dwTimersChild->tick();
			}
		}
	}
    public function tick() : void {
		// Only invoke as many callbacks as were on the queue when tick() was called.
		/*
		if ( isset( $this->log ) && \is_object( $this->log) ) {
			$count = $this->count();
			$this->log->trace( "count:{$count}" );
		} else
			echo "NoLog For DwTimers-TICK:[{$this->toString()}]".\PHP_EOL;
		//*/
		parent::tick();
		$this->dwTimersChildsTick();
	}
    public function updateTime() : int {
    	return $this->dwTime->updateTime();
    }
    public function getFirst() : ?int {
		//if ( \is_object( $this->log ) )
		//	$this->log->trace( "." );
		
    	if ( \count( $this->schedule) == 0 )
    		$first=null;
    	else {
			$first=0;
			// ensure timers are sorted to simply accessing next (first) one
			if (!$this->sorted) {
				$this->sorted = true;
				\asort($this->schedule);
			}
			$first = \reset($this->schedule);
		}
		foreach( $this->dwTimersChilds as $dwTimersChild ) {
			$childFirst = $dwTimersChild->getFirst();
			if ( ! \is_null( $childFirst ) && $first > $childFirst )
				$first = $childFirst;
		}
		return $first;
    }
    public function sleep() {
		if ( $this->hasTick() )
			$this->addTick( $this->handleSleep );
		else {
			$childHasTick = false;
			foreach( $this->dwTimersChilds as $dwTimersChild )
				if ( ( $childHasTick|=$dwTimersChild->hasTick() ) == true )
					break;
			if ( $childHasTick ) {
				$this->log->trace( "child has tick" );
				$this->addTick( $this->handleSleep );
			} else {
				$this->time();
				$first = $this->getFirst();
				if ( \is_int( $first ) ) {
					// if first not null, timer is active
					$this->addTick( $this->handleSleep );
					$iSecondes = 0;
					$iMilliSecondes = 0;
					$iMicroSecondes =  $first - $this->updateTime();
					if ( $iMicroSecondes < 0 )
						$iMicroSecondes = 0;
					
					if ( $iMicroSecondes > 1000 ) //{
						$iMilliSecondes = \intval( $iMicroSecondes / 1000 );
						
					if ( $iMilliSecondes > 1000 )
						$iSecondes = \intval( $iMilliSecondes / 1000 );
					//	if ( $iSecondes == 0 ) $iSecondes=1;
					//}
					//$this->log->trace( $iSecondes > 0 ? "{$iSecondes} s" : ( $iMilliSecondes > 0 ? "{$iMilliSecondes} ms" : "{$iMicroSecondes} us" ) );
					
					if ( $iSecondes > 0 )
						\sleep( $iSecondes );
					// if 1 ms
					elseif ( $iMicroSecondes > 200  )
						\usleep( $iMicroSecondes - 100 );
				} else
					$this->log->warn( "No more tick and child tick, no more timer and child timer..." );
			}
		}
    	/*
		$this->log->trace(
			"nextTimeout: ".( is_null($nextTimeout) ? "null" : print_r($nextTimeout,true) )."\r\n"
		);
		//*/
    }
    
	// n'est jamais programmed
	// callback ever sheduled ?
	public function isEverSchudeled( DwCallBack $dwCallBack ) : bool {
		return isset( $this->timers[ $dwCallBack->getInstanceKey() ] )
		|| parent::isEverSchudeled( $dwCallBack );
	}
	
    public function addTimer( iDwTimer $dwTimer ) : bool {
		//$this->log->trace( $dwTimer );
    	$return=false;
		// callback ever sheduled ?
		$dwCallBack = $dwTimer->getDwCallBack();
		$key = $dwCallBack->getInstanceKey();
		if ( isset( $this->timers[$key] ) ) {
			throw new \Exception('callback ever scheduler');
		} else {
			if ( $this->isEverSchudeled( $dwCallBack ) )
				$this->cancelCallBack( $dwCallBack );
			$this->timers[$key] = $dwTimer;
			// updateTime() : int microseconde
			// getInterval() : int microseconde
			$time = $this->updateTime();
			$this->schedule[$key] = $dwTimer->getInterval() + $time;
			$this->sorted = false;
			$return=true;
		}
		return $return;
    }

    public function cancelCallBack( DwCallBack $dwCallBack ) : bool {
		//$this->log->trace( $dwCallBack );
    	$found = false;
		$key = $dwCallBack->getInstanceKey();
		if (  isset( $this->timers[$key] ) ) {
			unset( $this->timers[$key] ,$this->schedule[$key] );
    		$found = true;
		} else $found = parent::cancelCallBack( $dwCallBack );
		//if ( ! $found ) $this->log->trace( "cb not found" );
		return $found;
    }
    
    public function cancel( iDwTimer $timer ) : bool {
		//$this->log->trace( $timer );
		return $this->cancelCallBack( $timer->getDwCallBack() );
    }

    public function time() : int {
    	//if ( count($this->schedule) == 0 )
    	//	return -1;
    	$cpt = 0;
    	// ensure timers are sorted so we can execute in order
		if (!$this->sorted) {
			$this->sorted = true;
			\asort($this->schedule);
		}
		$time = $this->updateTime();
		$scheduled = \reset( $this->schedule );
		//$newSchedule = array();
		while ( $scheduled <= $time) {
			$key = \key($this->schedule );
			if ( is_null( $key ) )
				break;
			$timer = $this->timers[$key];
			$this->addTick( $timer->getDwCallBack() );
			// re-schedule if this is a periodic timer
			// and it has not been cancelled explicitly already
			if ( $timer->isPeriodic() ) {
				$nextTime=$timer->getInterval() + $scheduled;
				$tooLateCpt=0;
				while ( $nextTime <= $time ) {
					$this->addTick( $timer->getDwCallBack() );
					$tooLateCpt++;
					if ( $tooLateCpt > 100 ) {
						$timer->setInterval( $timer->getInterval() + 100 );
						$this->log->warn( "!!! too late reduice freque [{$timer->getInterval()}]:{$nextTime}" );
					}
					$scheduled = $nextTime;
					$nextTime=$timer->getInterval() + $scheduled;
				}
				//$this->log->trace( "next[{$timer->getInterval()}]:{$nextTime}" );
				$this->schedule[$key] = $nextTime;
				//$this->sorted = false;
				\asort( $this->schedule );
			} else
				unset( $this->schedule[$key] ,$this->timers[$key] );
			$cpt++;
			$scheduled=\next( $this->schedule );
			$time = $this->updateTime();
		}
		foreach( $this->dwTimersChilds as $dwTimersChild )
			$cpt += $dwTimersChild->time();
		return $cpt;
    }
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this->dwTimersChilds as $key => $dwTimerChild ) {
			$dwTimerChild->unregisterChild();
			unset( $this->dwTimersChilds[ $key ] );
		}
		
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

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
//use bakuba\kernel\time\DwTick as DwTick;

use  as DwCallBack;

/**
 * @internal
 */
class DwTick extends \darkphp\DwObject {
	private $dwCallBack = null;
	
	public function __construct( \bakuba\kernel\objects\DwCallBack $dwCallBack = null ) {
		parent::__construct( );
		$this->dwCallBack = $dwCallBack;
	}
	
	public function reset() {
		if ( \is_object( $this->queue ) )
			unset( $this->queue );
		$this->queue = new \SplQueue();
	}
    /**
     * Add a callback to be invoked on a future tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued.
     *
     * @param callable $listener The callback to invoke.
     */
    public function addTick( DwCallBack $dwCallBack ) : void {
		$this->log->trace( $dwCallBack );
		$this->queue->enqueue( $dwCallBack );
    }
    public function cancelCallBack( DwCallBack $dwCallBack ) : bool {
		$this->log->trace( $dwCallBack );
    	$found = false;
    	$oldQueue = $this->queue;
    	$this->queue = new \SplQueue();
    	foreach( \array_reverse( $oldQueue ) as $anDwCallBack ) {
    		if ( $dwCallBack->getInstanceKey() !== $anDwCallBack->getInstanceKey() )
    			$this->queue->enqueue( $anDwCallBack );
    		else
    			$found = true;
		}
		$this->log->trace( $found ? "found" : "not found" );
		return $found;
    }
	// n'est jamais programmed
	public function isEverSchudeled( DwCallBack $dwCallBack ) : bool {
		$found = false;
		// callback ever sheduled ?
    	foreach( $this->queue as $anDwCallBack )
    		if ( $dwCallBack->getInstanceKey() !== $anDwCallBack->getInstanceKey() )
    			continue;
    		else {
				$found = true;
				break;
			}
		$this->log->trace( $found ? "found" : "not found" );
		return $found;
	}
	public function count() : int {
		return $this->queue->count();
	}
	public function dequeue() : DwCallBack {
		return $this->queue->dequeue();
	}
	
    /**
     * Flush the callback queue.
     */
    public function tick()
    {
		// Only invoke as many callbacks as were on the queue when tick() was called.
		$count = $this->count();
		while ($count--) {
			$dwCallBack = $this->queue->dequeue();
			$dwCallBack->callback();
			$this->log->trace( $dwCallBack );
		}
		$this->log->trace( "count:{$count}" );
    }
    /**
     * Check if the next tick queue is empty.
     *
     * @return boolean
     */
	public function hasTick() : bool {
		return ! $this->queue->isEmpty();
	}
	public static function run( self $dwTickQueue ) : void {
		$dwTickQueue->log->trace( "." );
//		declare(ticks=10000) {
		while ( $dwTickQueue->hasTick() )
			$dwTickQueue->tick();
//		}
		$dwTickQueue->log->trace( "end" );
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

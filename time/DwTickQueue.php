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
//use bakuba\kernel\time\DwTickQueue as DwTickQueue;

use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

/**
 * @internal
 */
class DwTickQueue extends DwObject implements iDwTickQueue {
	private $dwTickQueueChilds = array();
	public function getDwTickQueueChild( DwObject $dwParent ) : DwTickQueue {
		$dwTickQueueChild = new DwTickQueueChild( $dwParent ,$this );
		$key = $dwTickQueueChild->getInstanceKey();
		$this->dwTickQueueChilds[ $key ] = $dwTickQueueChild; 
		$this->log->info( \PHP_EOL."\t new child [k:{$key}] Parent:{$dwParent->toString()}" );
		return $dwTickQueueChild;
	}
	public function removeDwTickQueueChild( DwTickQueueChild $dwToRemove ) : void {
		$key = $dwToRemove->getInstanceKey();
		if ( isset( $this->dwTickQueueChilds[ $key ] ) )
			unset( $this->dwTickQueueChilds[ $key ] );
		else
			throw new \Exception( "no found key:{$key} [{$dwToRemove->toString()}]" );
	}
	private $queue = null;
	public function __construct( \darkphp\DwObject $parent ) {
		parent::__construct( $parent );
		$this->queue = new \SplQueue();
		if ( \is_object( $this->log ) )
			$this->log->trace( "\r\n\t count:{$this->count()} [{$this->toString()}]" );
		else
			echo "NoLog For DwTickQueue:[{$this->toString()}]".\PHP_EOL;
	}
	/**
	 * Add a callback to be invoked on a future tick of the event loop.
	 *
	 * Callbacks are guaranteed to be executed in the order they are enqueued.
	 *
	 * @param DwCallBack $listener The callback to invoke.
	 */
	public function addTick( DwCallBack $dwCallBack ) : void {
		$this->queue->enqueue( $dwCallBack );
		//$this->log->trace( "\r\n\t[{$this->toString()}]\r\n\tcount:{$this->count()}\r\n" );
		//$this->log->trace( "\r\n\t[{$this->toString()}]\r\n\tcount:{$this->count()}\r\n{$dwCallBack->toString()}" );
	}
	public function cancelCallBack( DwCallBack $dwCallBack ) : bool {
		//$this->log->trace( $dwCallBack );
		$found = false;
		$oldQueue = $this->queue;
		$this->queue = new \SplQueue();
		foreach ($oldQueue as $anDwCallBack)
			if ($dwCallBack->getInstanceKey() !== $anDwCallBack->getInstanceKey())
				$this->queue->enqueue($anDwCallBack); //$this->queue->add( 0 ,$anDwCallBack );
			else
				$found = true;
		//$this->log->trace( $found ? "found" : "not found" );
		return $found;
	}
	// n'est jamais programmed
	public function isEverSchudeled( DwCallBack $dwCallBack ) : bool {
		$found = false;

		// callback ever sheduled ?
		$oldQueue = $this->queue;
		$this->queue = new \SplQueue();
		foreach( $oldQueue as $anDwCallBack ) {
			$this->queue->enqueue($anDwCallBack); //$this->queue->add( 0 ,$anDwCallBack );
			if ( $dwCallBack->getInstanceKey() == $anDwCallBack->getInstanceKey() )
				$found = true;
		}
		//$this->log->trace( "[{$this}] ".( $found ? "found" : "not found" ) );
		return $found;
	}
	public function count() : int {
		return ( ! isset( $this->queue ) || \is_null( $this->queue ) ) ? -1 : $this->queue->count();
	}
	public function dequeue() : ?DwCallBack {
		return $this->count() == 0 ? null : $this->queue->dequeue();
	}
	protected function tickChilds() : void {
		foreach( $this->dwTickQueueChilds as $dwTickQueuChild )
			if ( $dwTickQueuChild->hasTick() ) {
				if ( isset( $this->log ) && \is_object( $this->log) )
					$this->log->trace( $dwTickQueuChild );
				$dwTickQueuChild->tick();
			}
	}
	/**
	 * Flush the callback queue.
	 */
	public function tick() : void {
		$this->tickChilds();
		// Only invoke as many callbacks as were on the queue when tick() was called.
		$count = $this->count();
		//$this->log->trace( "count:{$count}" );
		while ( $count-- ) {
			//if ($this->queue->isEmpty()) break; // safety first
			$dwCallBack = $this->queue->dequeue();
			try {
				$dwCallBack->callback();
				//$this->log->trace( $dwCallBack );
			} catch( \Exception $exception ) {
				if ( \is_object( $this->log ) ) {
					$this->log->error( $this );
					$this->log->throwable( $exception );
				} else
					echo \PHP_EOL."\t {$error} ".\PHP_EOL."\t [{$this->toString()}]".\PHP_EOL;
				//throw $exceptionAppHttpRequest;
			} catch( \Error $error ) {
				if ( \is_object( $this->log ) ) {
					$this->log->error( $this );
					$this->log->throwable( $error );
				} else
					echo \PHP_EOL."\t {$error} ".\PHP_EOL."\t [{$this->toString()}]".\PHP_EOL;
				//throw $errorAppHttpRequest;
			}
		}
	}
	/**
	 * Check if the next tick queue is empty.
	 *
	 * @return boolean
	 */
	public function hasTick() : bool {
		$hasTick = ! $this->queue->isEmpty();
		foreach( $this->dwTickQueueChilds as $dwTickQueuChild )
			if ( $hasTick|=$dwTickQueuChild->tick() )
				break;
		return $hasTick;
	}
	///*
	public function __destruct() {
		if ( isset( $this->log ) && \is_object( $this->log ) )
			$this->log->trace( "count:{$this->count()} [{$this->toString()}]" );
		if ( \is_object( $this->queue ) ) {
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
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	public static function run( iDwTickQueue $iDwTickQueue ) : void {
		if ( \is_object( $iDwTickQueue->log ) )
			$iDwTickQueue->log->info( \PHP_EOL."\t run [{$iDwTickQueue->toString()}]" );
//		declare(ticks=10000) {
		while ( $iDwTickQueue->hasTick() )
			$iDwTickQueue->tick();
//		}
		if ( \is_object( $iDwTickQueue->log ) )
			$iDwTickQueue->log->info( \PHP_EOL."\t end run [{$iDwTickQueue->toString()}]" );
	}
	
	public static function test(
		\darkphp\logger\iDwLogger $iDwLogger
		,\darkphp\serializable\iDwNoSerializable $dwToTest = null
	) : \darkphp\serializable\iDwNoSerializable {
		try {
			$nbLoop = 5000;
			if ( \is_null( $dwToTest ) ) {
				$dwToTest = new self();
				$dwToTest->setDebug( $iDwLogger );
			}
			$dwChrono = new DwChrono( $dwToTest );
			$cpt=0;
			$dwCallBack = null;
			///*
			// la fonction la plus longue
			$dwCallBack = new DwCallBack(
				function( ) use
					( $dwToTest ,&$dwCallBack ,&$cpt ,&$dwChrono ,&$nbLoop ,&$iDwLogger )
				{
					$dwChrono->tick();
					if ( $cpt < $nbLoop )
						$dwToTest->addTick( $dwCallBack );
					else {
						$iDwLogger->trace( $dwCallBack->printHandle(false) );
						$iDwLogger->trace( $dwChrono->printStats() );
					}
					$cpt++;
				}
			);
			$dwToTest->addTick( $dwCallBack );
			//*/
			///*
			$dwChrono2 = new DwChrono( $dwToTest );
			$dwCallBack2 = new DwCallBack( array( &$dwChrono2,'tick' ) );
			for ( $anCpt=0;$anCpt<=$nbLoop;$anCpt++ )
				$dwToTest->addTick( $dwCallBack2 );
			
			$dwToTest->addTick( new DwCallBack(
				function( $dwChrono ) use ( &$iDwLogger)  {
					$iDwLogger->trace( $dwChrono->printStats() );
				}
				,$dwChrono2
			) );
		} catch( \Exception $exception ) {
			$iDwLogger->error( $exception );
		} catch( \Error $error ) {
			$iDwLogger->error( $error );
		}
		return $dwToTest;
	}
}
?>

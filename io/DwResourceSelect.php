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
//use bakuba\kernel\io\DwResourceSelect as DwResourceSelect;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\time\DwChrono as DwChrono;

//use bakuba\kernel\io\IDwResources as IDwResources;
//use bakuba\kernel\io\IDwResource as IDwResource;

abstract class DwResourceSelect extends \darkphp\DwObject implements iDwResourceSelect {
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
	public function getProcDwResources() : \bakuba\kernel\process\DwResources {
		return $this->parent;
	}
	public function getDwObjectTimer() : \bakuba\kernel\time\timer\DwObjectTimer {
		return $this->getProcDwResources()->getDwProcessResources();
	}
	private $dwChrono = null;
	
	protected $dwCallBackCheck = null;
	private $dwObjectTimer = null;
	public function __construct( \bakuba\kernel\process\DwResources $parent ) {
		parent::__construct( $parent );
		$this->dwChrono = new DwChrono( $this );
		$this->init( );
		
		$this->log->trace(".");
	}
	
	public function init( ) : void {
		$this->log->trace( "." );
		$this->dwCallBackCheck = new DwCallBack( array( &$this, "resourcesCheck" ) );
	}
	
	private $inactivityCpt = 0;
	public function checkActivity() {
		$this->inactivity();
		return $this->inactivityCpt;
	}
	public function inactivity() : void {
		$this->inactivityCpt++;
	}
	public function activity() : void {
		//self::$sLog->trace( $this->getClassName() );
		$this->inactivityCpt=0;
	}
	///*
	protected $lastState="no one state";
	public function getState() :string {
		return $this->lastState;
	}
	private $littletick=-1;
	private static $periodLogWriteCall = 10;
	//*/
	public function check() : bool {
		$return = false;
		$nbMessageReady = $this->select();
		if ( $nbMessageReady < 0  || $nbMessageReady === false )
			throw new \Exception("failed to select");
		elseif ( $nbMessageReady === 0 ) {
			$return = false;
			$this->inactivity();
		} else { 
			//$this->log->trace( "nbMessageReady:{$nbMessageReady}" );
			$return = true;
			$this->activity();
		}
			
		if ( $this->littletick >= self::$periodLogWriteCall 
		|| $this->littletick == -1 
		) {
			/*
			$this->log->trace(
				"littletick:{$this->littletick} inactivity:{$this->inactivityCpt}\r\n"
				.$this->getState()
			);
			//*/
			$this->littletick=0;
			$this->dwChrono->tick();
			//$this->log->trace( $this->dwChrono->printStats() );
		} else 
			$this->littletick++;
		
		return $return;
	}
	
	private $resourcesToRead = array();
	public function countRead() : int {
		return count( $this->resourcesToRead );
	}
	private $resourcesToWrite = array();
	public function countWrite() : int {
		return count( $this->resourcesToWrite );
	}
	
	public function resourceWrite( iDwResource $dwResource ) : void {
		$this->resourcesToWrite[ $dwResource->getInstanceKey() ] = $dwResource;
	}
	public function resourceIsWriting(iDwResource $dwResource ) : bool {
		return isset( $this->resourcesToWrite[ $dwResource->getInstanceKey() ] );
	}
	public function resourceWriteCancel( iDwResource $dwResource ,\bakuba\kernel\time\timer\iDwTimer $iDwTimer = null ) : void {
		if ( \is_object( $iDwTimer ) )
			$this->getDwObjectTimer()->cancelTimer( $iDwTimer );
		
		if ( isset( $this->resourcesToWrite[ $dwResource->getInstanceKey() ] ) )
			unset( $this->resourcesToWrite[ $dwResource->getInstanceKey() ] );
		else
			$this->log->error( "no key to cancel [{$dwResource->getInstanceKey()}]" );
		
		//	throw new \Exception( "no key to cancel [{$dwResource->getInstanceKey()}]" );
	}
	
	public function writeLunch( iDwResource $dwResource ) : void {
		//$this->log->trace( "." );
		if ( \is_object( $this->timercheck ) )
			$this->getDwObjectTimer()->cancelTimer( $this->timercheck ) && $this->timercheck = null;
		if ( ! $this->getDwObjectTimer()->isEverSchudeled( $this->dwCallBackCheck ) )
			$this->getDwObjectTimer()->addTick( $this->dwCallBackCheck );
		
		$dwResource->writeLunch( $this );
	}
	
	public function readLunch( iDwResource $dwResource ) : void {
		//$this->log->trace( "." );
		if ( \is_object( $this->timercheck ) )
			$this->getDwObjectTimer()->cancelTimer( $this->timercheck ) && $this->timercheck = null;
		if ( ! $this->getDwObjectTimer()->isEverSchudeled( $this->dwCallBackCheck ) )
			$this->getDwObjectTimer()->addTick( $this->dwCallBackCheck );
		
		$dwResource->readLunch( $this );
	}
	private $deltaTimerMax = 3.0;
	public function setLimiteDeltaTimer( float $fSeconde ) {
		$this->deltaTimerMax = $fSeconde;
	}
	
	private $cptCheck = 0;
	private $lastStateCheck = false;
	private $timercheck = null;
	//DwResources->resourcesCheck
	public function resourcesCheck() {
		//$this->log->trace( "." );
		if ( \is_object( $this->timercheck ) )
			$this->getDwObjectTimer()->cancelTimer( $this->timercheck );
		$this->timercheck = null;
		$this->cptCheck++;
		if ( $this->check() ) {
			if ( ! $this->lastStateCheck ) {
				$this->cptCheck=0;
				$this->lastStateCheck=true;
			}
			//$this->log->trace( "[".$this->getInstanceKey()."] futur tick" );
			if ( ! $this->getDwObjectTimer()->isEnd() ) {
				// 0.01 => 10 ms => 100 hz
				// 0.001 => 1ms => 1 Khz
				// 0.0001 => 0.1ms => 10 Khz
				//$this->getDwObjectTimer()->addTimer( 0.0001 , $this->dwCallBackCheck );
				if ( \is_object( $this->timercheck ) )
					$this->getDwObjectTimer()->cancelTimer( $this->timercheck ) && $this->timercheck = null;
				if ( ! $this->getDwObjectTimer()->isEverSchudeled( $this->dwCallBackCheck ) )
					$this->getDwObjectTimer()->addTick( $this->dwCallBackCheck );
				
				/*
				if ( $this->cptCheck % 5000 == 0 ) {
					$this->log->trace( "SPEED!!! [".$this->getClassName()."]" );
					//$this->cptCheck=0;
				}
				//*/
			}
			//*/
		} elseif ( $this->countRead() > 0 || $this->countWrite() > 0 ) {
			if ( $this->lastStateCheck ) {
				$this->lastStateCheck=false;
				$this->cptCheck=1;
			}
			$deltaTimer = 0.01 * $this->cptCheck;
			if ( $deltaTimer > $this->deltaTimerMax ) {
				$deltaTimer = $this->deltaTimerMax;
				//$this->log->trace( "max delta timer:".$this->deltaTimerMax."[".$this->getClassName()."]" );
			}
			
			if ( ! $this->getDwObjectTimer()->isEnd() ) {
				$this->timercheck = $this->getDwObjectTimer()->addTimer( $deltaTimer ,$this->dwCallBackCheck );
				/*
				if ( is_null( $this->timercheck ) )
					$this->log->trace( "[".$this->getInstanceKey()."] echec already in futur timer.................." );
				//*/
				/*
				if ( $this->cptCheck % 10 == 0 ) {
					//$this->cptCheck+=100;
					$this->log->trace("[".$this->getClassName()."] {$this->cptCheck} INACTIVITY!!!");
				}
				//*/
			}
		}
		else $this->log->trace("cpt:".$this->cptCheck." STOP!!!!![".$this->getClassName()."]");
		
	}
	public function resourceRead( iDwResource $dwResource ) : void {
		$this->resourcesToRead[ $dwResource->getInstanceKey() ] = $dwResource;
	}
	public function resourceIsReading(iDwResource $dwResource ) : bool {
		return isset( $this->resourcesToRead[ $dwResource->getInstanceKey() ] );
	}
	public function resourceReadCancel( iDwResource $dwResource ,\bakuba\kernel\time\timer\iDwTimer $iDwTimer = null ) : void {
		if ( \is_object( $iDwTimer ) )
			$this->getDwObjectTimer()->cancelTimer( $iDwTimer );
		
		if ( isset( $this->resourcesToRead[ $dwResource->getInstanceKey() ] ) )
			unset( $this->resourcesToRead[ $dwResource->getInstanceKey() ] );
		else
			$this->log->error( "no key to cancel [{$dwResource->getInstanceKey()}]" );
		
		/*
		//*/
		//	throw new \Exception( "no key to cancel [{$dwResource->getInstanceKey()}]" );
	}
	
	private function select() {
		$return = -1;
		//$this->log->trace("."); // !! too much write
		$dwResourcesExcept = array();
		$dwResourcesRead = $this->resourcesToRead;
		$dwResourcesWrite = $this->resourcesToWrite;
		/*
		$dwResourcesRead = array();
		foreach($this->resourcesToRead as $index => $dwResource) {
			if ( $dwResource->isResource() || $dwResource->isListen() )
				$dwResourcesRead[] = $dwResource;
			else  {
				//unset($this->resourcesToRead[$index]);
				$dbgMsg="[select:".$this->getClassName()."]";
				$dbgMsg.="\r\n";
				$dbgMsg.=\darkphp\DwObject::print_r( $dwResource ,2 )."\r\n================= NO READ MORE ==============\r\n";
				$this->log->trace( $dbgMsg );
			}
		}
		
		$dwResourcesWrite = array();
		foreach($this->resourcesToWrite as $index => $dwResource) {
			if ( $dwResource->isResource() ) 
				$dwResourcesWrite[] = $dwResource;
			else {
				//unset($this->resourcesToWrite[$index]);
				
				$dbgMsg="[select:".$this->getClassName()."]";
				$dbgMsg.="\r\n";
				$dbgMsg.=\darkphp\DwObject::print_r( $dwResource ,2 )."\r\n================= NO WRITE MORE ==============\r\n";
				$this->log->trace( $dbgMsg );
			}
			
		}
		//*/
		
		$return = 0;
		if ( ! empty( $dwResourcesRead ) || ! empty( $dwResourcesWrite ) ) {
			$nbMessageReady = $this->messaging_select(
				$dwResourcesRead
				,$dwResourcesWrite
				,$dwResourcesExcept
			);
			if ( $nbMessageReady === false ) {
				throw new \Exception("failed to messaging_select[xx]");
			} else {
				$return = $nbMessageReady;
				if ( ! empty( $dwResourcesWrite ) )
					$this->writeEnd( $this->write( $dwResourcesWrite ,$dwResourcesExcept ) );
				if ( ! empty( $dwResourcesRead ) )
					$this->readEnd( $this->read( $dwResourcesRead ,$dwResourcesExcept ) );
			}
		}
		if ( ! empty($dwResourcesExcept) ) {
			foreach( $dwResourcesExcept as $dwResourceExcept ) {
				//$this->log->trace(".");
				$found = false;
				if ( isset( $this->resourcesToWrite[ $dwResourceExcept->getInstanceKey() ] ) ) {
					$this->log->error( $dwResourceExcept );
					$dwResourceExcept->writeCancel( $this );
					$found =true;
				}
				if ( ! $found ) {
					if ( isset( $this->resourcesToRead[ $dwResourceExcept->getInstanceKey() ] ) ) {
						$dwResourceExcept->readCancel( $this );
						$this->log->error( $dwResourceExcept );
						$found =true;
					}
				}
				if ( ! $found ) 
					$this->log->throwable( new \Exception( $dwResourceExcept ) );
			}
		}
		
		//if ( $this->getClassName() == "bakuba\\kernel\\io\\stream\\DwStreamSelect" )
		//$this->log->trace("."); // !! too much write ( little less )
		return $return;
	}
	
	public function read( array &$dwResourcesRead, array &$dwResourcesExcept ) : array {
		$dwResourcesReaded = array();
		$dwResource = null;
		//$this->log->trace(".");
		foreach( $dwResourcesRead as $dwResource ) {
			try {
				$dwResource->receive();
				if ( $dwResource->readEnd() ) {
					// end transmition, no more receive
					$dwResourcesReaded[] = $dwResource;
				}
			} catch( \Exception $exception_read ) {
				$dwResourcesExcept[] = $dwResource;
				$this->log->throwable( $exception_read );
				//throw $exception_read;
			} catch( \Error $error_read ) {
				$dwResourcesExcept[] = $dwResource;
				$this->log->throwable( $error_read );
				//throw $error_read;
			}
		}
		
		return $dwResourcesReaded;
	}
	
	//private $cptTickWrite = 0;
	public function write( array &$dwResourcesWrite, array &$dwResourcesExcept ) : array {
		/*
		if ( $this->cptTickWrite % 10 )
			$this->log->trace(".");
		$this->cptTickWrite++;
		*/
		//$this->log->trace(".");	
		$dwResourcesWrited = array();
		$dwResource = null;
		
		foreach($dwResourcesWrite as $dwResource ) {
			try {
				! $dwResource->writeEnd() && $dwResource->send();
				
				if ( $dwResource->writeEnd() ) {
					// end transmition, no more receive
					$dwResourcesWrited[] = $dwResource;
				}
			} catch( \Exception $exception_write ) {
				$dwResourcesExcept[] = $dwResource;
				$this->log->throwable( $exception_write );
				//throw $exception_write;
			} catch( \Error $error_write ) {
				$dwResourcesExcept[] = $dwResource;
				$this->log->throwable( $error_write );
				//throw $error_write;
			}
		}
		return $dwResourcesWrited;
	}
	
	public function readEnd( array $dwResourcesReaded ) : void {
		//$this->log->trace("."); // too much write
		if ( ! empty( $dwResourcesReaded ) ) {
			//$this->log->trace("count :".count($dwResourcesReaded) ); // too much write (little less)
			foreach( $dwResourcesReaded as $dwResource ) {
				try {
					//if ( ! $dwResource->isListen() ) {
					if ( ! isset( $this->resourcesToRead[ $dwResource->getInstanceKey() ] ) )
						$this->log->throwable( new \Error( "no presente in property array resourcesToRead the ended dwResource. !!!") );
					else {
						unset( $this->resourcesToRead[ $dwResource->getInstanceKey() ] );
						$dwResource->readed( $this );
					}
					
					//}
				} catch( \Exception $exception_handle) {
					$this->log->throwable( $exception_handle );
					//throw $exception_handle;
				} catch( \Error $error_handle) {
					$this->log->throwable( $error_handle );
					//throw $error_handle;
				}
			}
		}
	}
	
	public function writeEnd( array $dwResourcesWrited ) : void {
		//$this->log->trace("."); // too much write
		if ( ! empty( $dwResourcesWrited ) ){
			//$this->log->trace("count :".count($dwResourcesWrited) ); // too much write (little less)
			foreach( $dwResourcesWrited as $dwResource ) {
				try {
					if ( ! isset( $this->resourcesToWrite[ $dwResource->getInstanceKey() ] ) ) 
						$this->log->throwable( new \Error( "no presente in property array resourcesToRead the ended dwResource. !!!") );
					else{
						unset( $this->resourcesToWrite[ $dwResource->getInstanceKey() ] );
						$dwResource->writed( $this );
					}
					/*
					if ( ( $key = array_search( $dwResource, $this->resourcesToWrite ) ) === false ) 
						throw new \Error( "not in property array resourcesToWrite the ended dwResource. !!!") ;
					else {
						unset($this->resourcesToWrite[$key]);
						$this->writed( $dwResource );
					}
					//*/
				} catch( \Exception $exception_handle) {
					$this->log->throwable( $exception_handle );
					//throw $exception_handle;
				} catch( \Error $error_handle ) {
					$this->log->throwable( $error_handle );
					//throw $error_handle;
				}
			}
		}
		
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

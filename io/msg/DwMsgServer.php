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

namespace bakuba\kernel\io\msg;
//use bakuba\kernel\io\msg\DwMessageQueueServer as DwMessageQueueServer;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;
 
//use bakuba\kernel\io\messageQueue\DwMessageQueueSelect as DwMessageQueueSelect;
//use bakuba\kernel\io\messageQueue\DwMessageQueueKey as DwMessageQueueKey;
//use bakuba\kernel\io\messageQueue\DwMessageQueue as DwMessageQueue;

class DwMessageQueueServer extends DwObject {
	private static $sLog = null;
	private $debugEnable = false;
	public function enableDebug( bool $withParent = false ) : void {
		$this->debugEnable = true;
		self::$sLog->trace( $withParent ? "with parent":"this only" );
		if ( $withParent )
			parent::initDebug( self::$sLog );
	}
	
	protected $isListen = false;
	protected $dwMessageQueueKey = null;
	public function getQueueKey() : DwMessageQueueKey {
		return $this->dwMessageQueueKey;
	}
	
	public function __construct( DwMessageQueueKey $dwMessageQueueKey ) {
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			
			parent::__construct( );
			
			$this->dwMessageQueueKey = $dwMessageQueueKey;
			
			if ( $this->debugEnable )
				self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function DwSerializableInit() {
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::DwSerializableInit();
			
			//$this->addtoSerialize("isListen");
			$this->addtoSerialize("dwMessageQueueKey");
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function refreshPid( int $pid ) : void {
		try {
			if ( $this->debugEnable )
				self::$sLog->trace("($pid)[".$this->getClassName()."/".$this->getInstanceKey()."]");
			$this->dwMessageQueueKey->setProcessId( $pid );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	public function connect() : DwMessageQueue {
			if ( $this->debugEnable )
				self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
		// dst key = is us.
		$dwMessageQueue = new DwMessageQueue( $this->dwMessageQueueKey );
		return $dwMessageQueue;
	}
	/*
	public function acceptConnection( DwMessageQueue $dwMessageQueueAccept ) : void {
		
	}
	//*/
	/*
	public function readed( $dwMessageQueue ) : void {
		try {
			//self::$sLog->trace( $dwMessageQueue->getId() );
			//self::$sLog->trace( "[".$dwMessageQueue->getInstanceKey()."/".$this->getInstanceKey()."] " );
			
			// lunch new waiting connection
			//if ( $dwMessageQueue->isListen() ) {
			//	self::$sLog->trace( $dwMessageQueue );
			//	$dwMessageQueueListen = $this->connect();
			//	$dwMessageQueueListen->enableListen(); // create channel ! no more cause only in first
			//	//$this->isListen = true;
			//	$dwMessageQueueListen->connect();
			//	$this->dwProcessResources->messageQueueRead( $dwMessageQueueListen, new DwCallBack( array(&$this, "readed") ) );
			//}
			
			$this->dwProcessResources->messagingHandle( $dwMessageQueue );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	//*/
	public function writed( DwMessageQueue $dwMessageQueue ) : void {
		try {
			if ( $this->debugEnable )
				self::$sLog->trace( " mqId:".$dwMessageQueue->getId() );
			// {
			//$dstKey = $dwMessageQueue->getDstKey();
			//self::$sLog->trace( $dstKey->getProcessId()."/".$dstKey->getClassNameProcess()."=>".$dwMessageQueue->getId()." r:".$dwMessageQueue->getRid()  );
			// }
			$dwMessageQueue->disconnect();
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	private $dwCallBackMessageReceived = null;
	public function getDwCallBackMessageReceived() : DwCallBack {
		return $this->dwCallBackMessageReceived;
	}
	private $dwResourcesMessageQueue = null;
	public function start(
		DwProcessResources $dwProcessResouces
		,DwCallBack $dwCallBackMessageReceived ) {
		try {
			if ( $this->debugEnable )
				self::$sLog->trace( $this->dwMessageQueueKey->getClassNameProcess()."/".$this->dwMessageQueueKey->getKey() );
			if ( $this->isListen )
				self::$sLog->warn("is ever in listen");
			else {
				$dwResources = $dwProcessResouces->getNewResources();
				$dwResources->addSignal( \SIGUSR1 );
				$this->dwResourcesMessageQueue = new DwMessageQueueSelect(
					$dwResources
					,$this
				);
				$dwProcessResouces->refSelectForCheckActivity( "dwResourcesMessageQueue" ,$this->dwResourcesMessageQueue );
				
				$this->isListen = true;
				$dwMessageQueueListen = $this->connect();
				$dwMessageQueueListen->enableListen(); // create channel
				$dwMessageQueueListen->connect();
				$this->dwCallBackMessageReceived = $dwCallBackMessageReceived;
				$dwProcessResouces->messageQueueRead( $dwMessageQueueListen, $this->dwCallBackMessageReceived );
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	public function startInObject( DwMessageQueueSelect $dwMessageQueueSelect ,DwCallBack $dwCallBackMessageReceived ) {
		try {
			if ( $this->debugEnable )
				self::$sLog->trace( $this->dwMessageQueueKey->getClassNameProcess()."/".$this->dwMessageQueueKey->getKey() );
			if ( $this->isListen )
				self::$sLog->warn("is ever in listen");
			else {
				$this->isListen = true;
				$dwMessageQueueListen = $this->connect();
				$dwMessageQueueListen->enableListen(); // create channel
				$dwMessageQueueListen->connect();
				$this->dwCallBackMessageReceived = $dwCallBackMessageReceived;
				$dwMessageQueueSelect->messageQueueRead( $dwMessageQueueListen, $this->dwCallBackMessageReceived );
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	/*
	public function stop() {
		$this->dwMessageQueueSelect->disconnect();
	}
	/*
	private $dwMessageQueueListen = null;
	public function getDwMessageQueueListen() : DwMessageQueue {
		return $this->dwMessageQueueListen;
	}
	*/
	
	public function disconnect() {
		try {
			if ( $this->debugEnable )
				self::$sLog->trace( $this->dwMessageQueueKey->getClassNameProcess()."/".$this->dwMessageQueueKey->getKey() );
			/*
			if ( is_object( $this->dwMessageQueueListen ) )
				$this->dwMessageQueueListen->disconnect();
			*/
			$this->isListen = false;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	
	public function messageQueueRead(
		DwMessageQueue $dwMessageQueue
		,DwCallBack $onReaded
	) : void {
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		try {
			$debugKey.="/dwMessageQueue {$dwMessageQueue->getInstanceKey()}";
			//$this->log->trace( "[{$debugKey}]" );
			$this->dwResourcesMessageQueue->messageQueueRead(
				$dwMessageQueue
				,$onReaded
			);
		} catch(\Exception $exception) {
			$this->log->error("[{$debugKey}] exception");
			throw $exception;
		} catch(\Error $error) {
			$this->log->error("[{$debugKey}] error");
			throw $error;
		}
	}
	
	public function messageQueueWrite(
		DwMessageQueue $dwMessageQueue
		,DwCallBack $onWrited
	) : void {
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		try {
			$debugKey.="/dwMessageQueue {$dwMessageQueue->getInstanceKey()}";
			//$this->log->trace( "[{$debugKey}]" );
			$dwMessageQueue->source( $this->getQueueKey() );
			$this->dwResourcesMessageQueue->messageQueueWrite(
				$dwMessageQueue
				,$onWrited
			);
		} catch(\Exception $exception) {
			$this->log->error("[{$debugKey}] exception");
			throw $exception;
		} catch(\Error $error) {
			$this->log->error("[{$debugKey}] error");
			throw $error;
		}
	}
	public function __destruct() {
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
}

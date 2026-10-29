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

namespace bakuba\kernel\io\messageQueue;
//use bakuba\kernel\io\messageQueue\DwMessageQueueServer as DwMessageQueueServer;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;
 
//use bakuba\kernel\io\messageQueue\DwMessageQueueSelect as DwMessageQueueSelect;
//use bakuba\kernel\io\messageQueue\DwMessageQueueKey as DwMessageQueueKey;
//use bakuba\kernel\io\messageQueue\DwMessageQueue as DwMessageQueue;

class DwMessageQueueServer extends \darkphp\DwObject {
	protected $isListen = false;
	protected $dwMessageQueueKey = null;
	public function getQueueKey() : DwMessageQueueKey {
		return $this->dwMessageQueueKey;
	}
	private $sendRouting = array();
	private $receiveRouting = array();
	private function addRoute( DwMessageQueueKey $dwMessageQueueKey ,DwCallBack $dwCallBackOnReceive ,DwCallBack $dwCallBackToSend ) {
		$this->sendRouting[ $dwMessageQueueKey->getKey() ] = $dwCallBackToSend;
		$this->receiveRouting[ $dwMessageQueueKey->getKey() ] = $dwCallBackOnReceive;
	}
	public function routing( DwMessageQueue $dwMessageQueue ,DwCallBack $onWrited = null ) {
		$this->log->trace( "{$dwMessageQueue->getSrcKey()->getClassNameProcess()} => {$dwMessageQueue->getDstKey()->getClassNameProcess()}" );
		if ( isset( $this->sendRouting[ $dwMessageQueue->getDstKey()->getKey() ] ) )
			$this->sendRouting[ $dwMessageQueue->getDstKey()->getKey() ]->callback( $dwMessageQueue );
		elseif ( isset( $this->receiveRouting[ $dwMessageQueue->getDstKey()->getKey() ] ) )
			$this->receiveRouting[ $dwMessageQueue->getDstKey()->getKey() ]->callback( $dwMessageQueue );
		else
			$this->log->warn("not route receiveRouting for :{$dwMessageQueue->getDstKey()->getProcessId()}:{$dwMessageQueue->getDstKey()->getClassNameProcess()}=>{$dwMessageQueue->getDstKey()->getKey()}");
		if ( \is_object( $onWrited ) )
			$onWrited->callback( $dwMessageQueue );
	}
	public function __construct( DwMessageQueueKey $dwMessageQueueKey ) {
		parent::__construct( );
		$this->dwMessageQueueKey = $dwMessageQueueKey;
		$this->log->trace(".");
	}
	public function getMessageQueueServer( DwMessageQueueKey $dwMessageQueueKey ) : self {
		$dwMessageQueueServer = new self( $dwMessageQueueKey );
		$this->addRoute(
			$dwMessageQueueKey
			,new DwCallBack( array( &$dwMessageQueueServer ,'messageQueueRead' ) )
			,new DwCallBack( array( &$dwMessageQueueServer ,'messageQueueWrite' ) )
		);
		$dwMessageQueueServer->addRoute(
			$this->getQueueKey()
			,new DwCallBack( array( &$this ,'messageQueueRead' ) )
			,new DwCallBack( array( &$this ,'messageQueueWrite' ) )
		);
		return $dwMessageQueueServer;
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		//$this->addtoSerialize("isListen");
		$this->addtoSerialize("dwMessageQueueKey");
	}
	public function refreshPid( int $pid ) : void {
		$this->log->trace($pid);
		$this->dwMessageQueueKey->setProcessId( $pid );
	}
	public function connect() : DwMessageQueue {
		$this->log->trace(".");
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
		//	$this->log->trace( $dwMessageQueue );
		
		// lunch new waiting connection
		//if ( $dwMessageQueue->isListen() ) {
		//	if ( $this->logEnable )
		//		$this->log->trace( $dwMessageQueue );
		//	$dwMessageQueueListen = $this->connect();
		//	$dwMessageQueueListen->enableListen(); // create channel ! no more cause only in first
		//	//$this->isListen = true;
		//	$dwMessageQueueListen->connect();
		//	$this->dwProcessResources->messageQueueRead( $dwMessageQueueListen, new DwCallBack( array(&$this, "readed") ) );
		//}
			
		$this->dwProcessResources->messagingHandle( $dwMessageQueue );
	}
	//*/
	public function writed( DwMessageQueue $dwMessageQueue ) : void {
		$this->log->trace( "mqId:".$dwMessageQueue->getId() );
		//$dstKey = $dwMessageQueue->getDstKey();
		//$this->log->trace( $dstKey->getProcessId()."/".$dstKey->getClassNameProcess()."=>".$dwMessageQueue->getId()." r:".$dwMessageQueue->getRid()  );
		$dwMessageQueue->disconnect();
	}
	private $dwCallBackMessageReceived = null;
	public function getDwCallBackMessageReceived() : DwCallBack {
		return $this->dwCallBackMessageReceived;
	}
	private $dwProcessResources = null;
	public function start(
		DwProcessResources $dwProcessResources
		,DwCallBack $dwCallBackMessageReceived ) {
		$this->log->trace( $this->dwMessageQueueKey->getClassNameProcess()."/".$this->dwMessageQueueKey->getKey() );
		
		if ( $this->isListen ) {
			$this->log->warn("is ever in listen");
		} else {
			$this->isListen = true;
			$this->dwProcessResources = $dwProcessResources;
			if ( \function_exists('msg_send') ) {
				$dwResources = $this->dwProcessResources->getNewResources();
				$dwResources->addSignal( \SIGUSR1 );
				$dwMessageQueueSelect = new DwMessageQueueSelect(
					$dwResources
					,$this
				);
				$this->dwProcessResources->refSelectForCheckActivity( "dwMessageQueueSelect" ,$dwMessageQueueSelect );
				
				$this->initMsg( $dwMessageQueueSelect ,$dwCallBackMessageReceived );
			} else {
				
			}
			$this->addRoute(
				$this->dwMessageQueueKey
				,$dwCallBackMessageReceived
				,new DwCallBack( array( &$this , 'messageQueueWrite') )
			);
		}
	}
	public function startInObject( DwMessageQueueSelect $dwMessageQueueSelect ,DwCallBack $dwCallBackMessageReceived ) {
		$this->log->trace( $this->dwMessageQueueKey->getClassNameProcess()."/".$this->dwMessageQueueKey->getKey() );
		if ( $this->isListen ) {
			$this->log->warn("is ever in listen");
		} else {
			$this->isListen = true;
			$this->initMsg( $dwMessageQueueSelect ,$dwCallBackMessageReceived );
		}
	}
	private function initMsg( DwMessageQueueSelect $dwMessageQueueSelect ,DwCallBack $dwCallBackMessageReceived ) : void {
		$dwMessageQueueListen = $this->connect();
		$dwMessageQueueListen->enableListen(); // create channel
		$dwMessageQueueListen->connect();
		$this->dwCallBackMessageReceived = $dwCallBackMessageReceived;
		$dwMessageQueueSelect->messageQueueRead( $dwMessageQueueListen, $this->dwCallBackMessageReceived );
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
		$this->log->trace( "msgKey/cn:{$this->dwMessageQueueKey->getClassNameProcess()}/msgKey/key:{$this->dwMessageQueueKey->getKey()}" );
		/*
		if ( is_object( $this->dwMessageQueueListen ) )
			$this->dwMessageQueueListen->disconnect();
		*/
		$this->isListen = false;
	}
	///*
	public function messageQueueRead(
		DwMessageQueue $dwMessageQueue
		,DwCallBack $onReaded = null
	) : void {
		$this->log->trace( $onReaded );
		$this->log->trace( "/dwMessageQueueId {$dwMessageQueue->getId()} ???????????????????????" );
		/*
		$this->dwResourcesMessageQueue->messageQueueRead(
			$dwMessageQueue
			,$onReaded
		);
		//*/
	}
	//*/
	public function messageQueueWrite(
		DwMessageQueue $dwMessageQueue
		,DwCallBack $onWrited = null
	) : void {
		$this->log->trace( "/dwMessageQueue {$dwMessageQueue->getId()} messageQueueWrite" );
		$dwMessageQueue->source( $this->getQueueKey() );
		//if ( is_null( $this->dwResourcesMessageQueue ) )
		$me = $this;
		$this->dwProcessResources->addTick( new DwCallBack( array( &$this,'routing') , $dwMessageQueue, $onWrited) );
		
		/*
		$this->dwResourcesMessageQueue->messageQueueWrite(
			$dwMessageQueue
			,$onWrited
		);
		*/
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

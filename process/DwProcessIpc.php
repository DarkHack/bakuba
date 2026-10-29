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

namespace bakuba\kernel\process;
// use bakuba\kernel\process\DwProcessIpc as DwProcessIpc;

use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\io\ipc\DwIpcKey as DwIpcKey;
use bakuba\kernel\io\ipc\DwIpc as DwIpc;
use bakuba\kernel\io\ipc\DwIpcServer as DwIpcServer;
use bakuba\kernel\io\ipc\DwIpcMsg as DwIpcMsg;

abstract class DwProcessIpc extends DwProcessResources implements iDwProcessIpc {
	static private $IPC_TYPE = "MessageQueue";
	
	private $dwIpcServer = null;
	public function getDwIpcKey() : DwIpcKey {
		return $this->dwIpcServer->getQueueKey();
	}
	public function getKey() : string {
		return $this->getDwIpcKey()->getKey();
	}
	
	private $dwHandles = null;
	public function getDwHandles() : \bakuba\kernel\objects\DwHandles {
		return $this->dwHandles;
	}
	protected function __construct( self $parentProcess = null ) {
		parent::__construct( $parentProcess );
		$this->dwHandles = new \bakuba\kernel\objects\DwHandles( $this );
		///*
		if ( \is_object( $parentProcess ) && isset( $parentProcess->dwIpcServer ) ) {
			$this->log->trace("parent process ipc:{$parentProcess->toString()}" );
			$this->dwIpcServer = $parentProcess->getIpcServer(
				new DwCallBack( array( &$this ,'ipcConnect' ) )
				,$this
			);
		} else
			$this->dwIpcServer = new DwIpcServer(
				$this->getDwResources()->getIpcSelect( $this->log )
				,new DwCallBack( array( &$this ,'ipcConnect' ) )
				,$this
			);
		//$this->dwIpcServer->setDebug( $this->log );
		//*/
		$this->log->trace(".");
	}
	public function getIpcServer ( DwCallBack $dwCallBack ,iDwProcessIpc $forProcess ) : DwIpcServer {
		return $this->dwIpcServer->getIpcServer(
			$dwCallBack
			,$forProcess
		);
	}
	protected function init( ) : void {
		parent::init();
		$this->dwIpcServer->init();//->start( $this );
		$this->log->trace( "." );
	}
	
	public function stopMessaging() : void {
		$this->log->trace( "." );
		//$this->log->trace(debug_backtrace()[0]);
		
		if ( $this->isMyProcess() ) {
			$dwProcessRequest = new DwProcessRequest( "dong" );
			//$dwProcessRequest->setLogs( $dwKernel->getLogs() );
			$this->log->trace( "send dong" );
			$this->ipcSend( new DwCallBack(
				array( &$this ,"stopProcess") )
				,$this->parent
				,$dwProcessRequest
			);
		} else
			$this->futureTick( new DwCallBack(
				array( &$this ,"stopProcess" )
			) );
		//$this->isFinish=true;
	}
	
	public function ipcConnect( \bakuba\kernel\io\ipc\DwIpcConnexion $dwIpcConnexion ) : void {
		//$this->dwHandles->getHandle( $dwIpcConnexion );
		new DwProcessConnexion( $this ,$dwIpcConnexion );
	}
	
	public function connect( DwIpcKey $dwIpcKeyDst ) : DwIpcMsg {
		return $this->dwIpcServer->connect( $dwIpcKeyDst );
	}
	public function stop() : void {
		$this->dwIpcServer->stop();
		parent::stop();
	}
	public function ipcSend(
		DwCallBack $dwCallBack
		,iDwProcessIpc $dwProcessDestination
		,$objectToSerialize
		,DwCallBack $dwCallBackSended = null
	) : void {
		//if ( $this->logEnable )
		$dwIpcMsg = $this->connect( $dwProcessDestination->getDwIpcKey() );
		//$dwIpcMsg = $dwProcessDestination->connect( $this->getDwIpcKey() );
		//$dwIpcMsg->write( serialize( $objectToSerialize ) );
		$dwIpcMsg->write( $objectToSerialize );
		
		//$srcCom = "[{$dwIpcMsg->getSrcKey()->getClassNameProcess()}";
		//$dstCom = "[{$dwIpcMsg->getDstKey()->getClassNameProcess()}";
		
		$this->dwHandles->addResponseHandle( $dwIpcMsg->getId() ,$dwCallBack );
		$this->dwIpcServer->msgWrite( $dwIpcMsg ,$dwCallBackSended );
		/*
		$dbgMsg ="[mId:{$dwIpcMsg->getId()}";
		$dbgMsg.="/cbId:{$dwCallBack->getInstanceKey()}]";
		$dbgMsg.="\r\n";
		$dbgMsg.=DwLogger::print_r(
			$dwIpcMsg, true, false, 3
		)."\r\n";
		$dbgMsg.="===============================\r\n";
		$dbgMsg.=DwLogger::print_r( $dwCallBack, true, false, 3 )."\r\n";
		
		$this->log->trace( $dbgMsg );
		//*/
		//*/
	}
	public function ipcForward(
		self $dwProcessDestination
		,DwIpcMsg $dwIpcMsg
		,DwCallBack $dwCallBackForwaded = null
	) : void {
		$this->log->trace( "processDst:{$dwProcessDestination->toString()}/mid:{$dwIpcMsg->getId()}" );
		//$dstKey = $dwProcessDestination->getDwIpcKey();
		//$testMQ = $dwProcessDestination->connect( $dwIpcMsg->getSrcKey() );
		
		$srcCom = "[{$dwIpcMsg->getSrcKey()->getClassNameProcess()}";
		//$srcCom.= "/{$testMQ->getSrcKey()->getClassNameProcess()}]";
		
		$dwIpcMsg->forwardTo( $dwProcessDestination->getDwIpcKey() );
		
		$dstCom = "[{$dwIpcMsg->getDstKey()->getClassNameProcess()}";
		//$dstCom.= "/{$testMQ->getDstKey()->getClassNameProcess()}]";
		
		$this->dwIpcServer->msgWrite( $dwIpcMsg ,$dwCallBackForwaded );
		$this->log->trace( "\r\n$srcCom\r\n$dstCom" );
	}
	///*
	public function ipcRegisterHandle(
		string $fullQualifiedClassName
		,DwCallBack $dwCallBack
	) : void {
		$this->dwHandles->registerHandle( $fullQualifiedClassName ,$dwCallBack );
	}
	/*
	public function ipcAddHandle( DwCallBack $cbHandle ) {
		$this->dwHandles->addHandle( $dwCallBack );
	}
	public function ipcRemoveHandle( DwCallBack $dwCallBack ) : bool {
		return $this->dwHandles->removeHandle( $dwCallBack );
	}
	//*/
	//*/
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
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__
			: __CLASS__
		;
	}
}
?>

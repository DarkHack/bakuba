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

namespace bakuba\kernel\process\child;
// use bakuba\kernel\process\child\DwProcessChild as DwProcessChild;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\process\DwProcessRequest as DwProcessRequest;
use bakuba\kernel\process\DwProcessOrder as DwProcessOrder;
use bakuba\kernel\process\DwProcessConnexion as DwProcessConnexion;

use bakuba\kernel\io\ipc\DwIpcKey as DwIpcKey;
use bakuba\kernel\io\ipc\DwIpc as DwIpc;
use bakuba\kernel\io\ipc\DwIpcServer as DwIpcServer;
use bakuba\kernel\io\ipc\DwIpcMsg as DwIpcMsg;

class DwProcessChild
	extends \bakuba\kernel\process\DwProcessIpc implements \bakuba\kernel\process\DwIRunnable {
	
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		//echo "\r\n devrais:|{$className}|\r\n";
		
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToFirstSectionName( $this->getForkedClassName() )
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		else
			parent::setDebug( $iDwLogger );
		
		//$this->log->trace( "." );
	}
	
	private $forkedClassName=null;
	public function getForkedClassName() : string {
		return $this->forkedClassName;
	}
	///*
	public function isMyProcess() : bool {
		return false;
	}
	
	public function __construct( \bakuba\kernel\process\DwProcessIpc $parentProcess ,string $forkedClassName ) {
		$this->forkedClassName = $forkedClassName;
		parent::__construct( $parentProcess );
		///*
		//$this->log->trace("parent process ipc:{$parentProcess->toString()}" );
		//$this->dwIpcServer->setDebug( $this->log );
		//*/
		$this->onAlive( new DwCallBack( array( &$this, "forkedProcessReady" ) ) );
		$this->log->trace(".");
	}
	public function getParentProcess() : \bakuba\kernel\process\DwProcessIpc {
		return $this->parent;
	}
	protected function init() : void {
		$this->log->trace( "." );
		parent::init();
		if ( \bakuba\kernel\agartha\DwInstall::isWindows() )
			$dwProcPhpIpcPipe = new windows\DwProcPhpIpcPipe( $this->getDwResources() ,$this->forkedClassName );
		else
			$dwProcPhpIpcPipe = new posix\DwProcPhpIpcPipe( $this->getDwResources() ,$this->forkedClassName );
	}
	public function forkedProcessReady() : void {
		$this->log->trace( $this->getForkedClassName() );
	}
	public function started() : void {
		$this->log->info( "." );
	}
	
	public function forkedAnsweredProcessReady() : void {
		$this->log->trace( "." );
	}
	public function start() : bool {
		$return=true;
		//$this->isFinish=false;
		$this->init();
		return $return;
	}
	public function ipcConnect( \bakuba\kernel\io\ipc\DwIpcConnexion $dwIpcConnexion ) : void {
		//$this->dwHandles->getHandle( $dwIpcConnexion );
		new DwProcessConnexion( $this ,$dwIpcConnexion );
	}
	
	public function connect( DwIpcKey $dwIpcKeyDst ) : DwIpcMsg {
		return $this->dwIpcServer->connect( $dwIpcKeyDst );
	}
    public function addTick( DwCallBack $dwCallBack ) : void {
		$this->getDwResources()->addTick( $dwCallBack );
	}
	/*
	public function stop() : void {
		$this->dwIpcServer->stop();
		parent::stop();
	}
	//*/
	public function ipcSend(
		DwCallBack $dwCallBack
		,\bakuba\kernel\process\iDwProcessIpc $dwProcessDestination
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
	}
	public function ipcForward(
		\bakuba\kernel\process\DwProcessIpc $dwProcessDestination
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
	protected $alive_state = false;
	public function isStarted() : bool {
		return $this->isAlive();
	}
	public function isAlive() : bool {
		return $this->alive_state == true;
	}
	private $onAliveCb=array();
	public function onAlive( DwCallBack $dwCallBack ) : void {
		if ( $this->alive_state )
			$this->addTick( $dwCallBack );
		else 
			$this->onAliveCb[] = $dwCallBack;
	}
	public function setAlive( bool $alive_state ) : void {
		$this->log->info(
			"alive_state:{$this->alive_state} => {$alive_state}"
		);
		if ( $this->alive_state == true )
			$this->log->warn("already done");
		
		if ( ! empty($this->onAliveCb) ) {
			foreach( $this->onAliveCb as $index => $dwCallBack ) {
				$this->addTick( $dwCallBack );
				/*
				try {
					$dwCallBack->callback();
				} catch( \Exception $exception_cb ) {
					$dbgMsg="exception_cb :{$dwCallBack->printHandle()}".\PHP_EOL;
					$dbgMsg.=\darkphp\error\DwException::exceptionToString( $exception_cb ).\PHP_EOL;
					//
					$this->log->error( $dbgMsg );
					//throw $exception_cb;
				} catch( \Error $error_cb ) {
					$dbgMsg="error_cb :{$dwCallBack->printHandle()}".\PHP_EOL;
					$dbgMsg.=\darkphp\error\DwException::exceptionToString( $error_cb ).\PHP_EOL;
					//
					$this->log->error( $dbgMsg );
					//throw $error_cb;
				}
				//*/
				unset($this->onAliveCb[$index]);
			}
		}
		$this->alive_state = $alive_state;
		
	}
	public function __toString() : string {
		return parent::__toString()."[".$this->getForkedClassName()."]";
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
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__
			: __CLASS__
		;
	}
}
?>

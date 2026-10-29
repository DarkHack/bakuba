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

namespace bakuba\kernel\io\ipc;
//use bakuba\kernel\io\ipc\DwIpc as DwIpc;

class DwIpcConnexion extends \bakuba\kernel\io\DwResourceConnexion {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
//				,time()."-".$this->getInstanceKey()
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	///*
	private $dwIpcMsg=null;
	public function getDwIpcMsg() : dwIpcMsg {
		return $this->dwIpcMsg;
	}
	public function __construct(
		DwIpcSelect $dwIpcSelect
		,DwIpc $dwIpc
	) {
		$this->dwIpcMsg = $dwIpc->read();
		parent::__construct( $dwIpcSelect ,$dwIpc );
		//$dwIpc = $this->dwIpcConnexion->getDwIpc();
	}
	//*/
	public function getDwIpcSelect() : DwIpcSelect {
		return $this->getDwResourceSelect();
	}
	public function getDwIpc() : DwIpc {
		return $this->getDwResource();
	}
	public function getSrcIpcKey() : DwIpcKey {
		return $this->dwIpcMsg->getSrcKey();
	}
	public function msgResponse(
		$objectToSerialize
		,DwCallBack $onWrited = null
	) : void {
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		$dwIpcMsgToSend = DwIpcMsg::response(  $this->dwIpcMsg );
		$dwIpcMsgToSend->write( $objectToSerialize );
		$debugKey.="/mid {$dwIpcMsgToSend->getId()}";
		$debugKey.="/rId {$dwIpcMsgToSend->getRid()}";
		/*
		$dbgMsg ="[mId:{$dwIpcMsgToSend->getId()}";
		$dbgMsg.="/rId:{$dwIpcMsgToSend->getRid()}]";
		$dbgMsg.="\r\n";
		$dbgMsg.=DwObject::print_r( $dwIpcMsgToSend ,9 )."\r\n";
		$dbgMsg.="===============================\r\n";
		$dbgMsg.=DwObject::print_r( $dwIpcMsg ,9 )."\r\n";
		$dbgMsg.=DwObject::print_r( $objectToSerialize ,9 )."\r\n";
		$dbgMsg.="===============================\r\n";
		$this->log->trace( $dbgMsg );
		//*/
		$dwIpc = new DwIpc( $this ,$dwIpcMsgToSend->getDstKey() );
		$dwIpc->write( $dwIpcMsgToSend );
		$dwIpc->setOnWrited( new \bakuba\kernel\objects\DwCallBack( array( &$this, "responseSended" ) ,$onWrited ) );
		$this->getDwIpcSelect()->ipcWrite( $dwIpc );
		//$this->log->trace( $dwIpcMsgToSend );
		
		/*
		$mid = $dwIpcMsgToSend->getId();
		$rid = $dwIpcMsgToSend->getRid();
		$dstKey = $dwIpcMsgToSend->getDstKey();
		$srcKey = $dwIpcMsgToSend->getSrcKey();
		$dstClassName = $dstKey->getClassNameProcess();
		$srcClassName = $srcKey->getClassNameProcess();
		$dstPid = $dstKey->getProcessId();
		$srcPid = $srcKey->getProcessId();
		$dstKey = $dstKey->getKey();
		$srcKey = $srcKey->getKey();
		$this->log->trace(
			"\r\n m:{$mid}/r:{$rid} {$srcClassName}($srcPid/$srcKey) "
			."=> {$dstClassName}($dstPid/$dstKey)"
		);
		//*/
		
		$this->rsrcResponse();
	}
	public function responseSended( DwCallBack $onWrited = null ) : void {
		$this->log->info(
			\darkphp\string\DwString::floatMicroSecondePrint(
				\microtime( true ) - $this->floatMicroSecondeStart
			)
		);
		if ( is_object( $onWrited ) )
			$onWrited->callback();
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

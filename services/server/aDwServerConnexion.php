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

namespace bakuba\kernel\services\server;
//use bakuba\kernel\services\server\DwServerConnexion as DwServerConnexion;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\network\DwInterface as DwInterface;
use bakuba\kernel\network\DwIp as DwIp;
use bakuba\kernel\network\DwPort as DwPort;

abstract class aDwServerConnexion extends \darkphp\DwObject implements iDwServerConnexion {
	private $dwServer = null;
	public function getDwServer() : DwServer {
		return $this->dwServer;
	}
	protected function getDwProcessResources() : \bakuba\kernel\process\DwProcessResources {
		return $this->getDwServer()->getDwProcessResources();
	}
	
	private $dwSessionIp = null;
	private $dwSessionIpKey = null;
	
	private function getDwResourceIpConnexion() : \bakuba\kernel\io\DwResourceIpConnexion {
		return $this->parent;
	}
	protected function __construct(
		DwServer $dwServer
		,\bakuba\kernel\io\DwResourceIpConnexion $dwResourceIpConnexion
	) {
		$this->dwServer=$dwServer;
		// define client session
		parent::__construct( $dwResourceIpConnexion );
		$this->getDwResourceIpConnexion()->readRequest(
			new \bakuba\kernel\objects\DwCallBack( array ( &$this, "requestReaded" ) )
		);
		
		$this->log->debug( "." );
	}
	public function requestReaded( ) : void {
		$dwCltPort = $this->getCltPort();
		if ( \is_object( $dwCltPort ) )
			$this->dwSessionIp = $this->getDwServer()->getDwSessionIp( $dwCltPort );
		
		if ( \is_object( $this->dwSessionIp ) )
			$this->dwSessionIpKey = $this->dwSessionIp->connect(
				$this->getSrvPort() ,$dwCltPort
			);
		
		$dwSessionIpKey = $this->getDwSessionIpKey();
		if ( \method_exists( $this->log ,"upTo" ) )
			$this->log = $this->log->upTo(
				$this
//				,$dwSessionIpKey->getSrvPort()->getPortPath()
				,time()."-".$dwSessionIpKey->getCltPort()->getPortPath()
			);
		
		$this->log->debug( "." );
		$this->request();
	}
	public function getDwSessionIpKey() : \bakuba\kernel\session\DwSessionIpKey {
		return $this->dwSessionIpKey;
	}
	public function getDwSessionIp() : \bakuba\kernel\session\DwSessionIp {
		return $this->dwSessionIp;
	}

	private $isSended = false;
	public function isSended() : bool {
		return $this->isSended;
	}
	///*
	//*/
	
	
	public function ipcSend(
		\bakuba\kernel\process\DwProcessIpc $dwProcessDestination
		,$objectToSerialize
		,DwCallBack $dwCallBackSended = null
	) : void {
		$this->getDwProcessResources()->ipcSend(
			new DwCallBack( array( &$this, 'ipcHandle') )
			,$dwProcessDestination
			,$objectToSerialize
			,$dwCallBackSended
		);
	}
	public function ipcHandle( $objectUnserialized ) : void {
		if ( \is_a( $objectUnserialized ,\bakuba\kernel\io\ipc\DwIpcMsgError::getStaticClassName() ) )
			$this->ipcError( $objectUnserialized );
		elseif ( \is_a( $objectUnserialized ,\bakuba\kernel\application\service\DwApplicationResponse::getStaticClassName() ) )
			$this->AppResponse( $objectUnserialized );
		else
			$this->resourceResponse( $objectUnserialized );
	}
	public function AppResponse( \bakuba\kernel\application\service\DwApplicationResponse $dwApplicationResponse ) : void {
			$this->resourceResponse( $dwApplicationResponse->getRawData() );
	}
	public function ipcError( \bakuba\kernel\io\ipc\DwIpcMsgError $dwIpcMsgError ) : void {
		$this->log->error( $dwIpcMsgError );
		if ( \method_exists( $this->log, "getFullPath") )
			$dbgMsg="see:".\PHP_EOL.$this->log->getFullPath().\PHP_EOL;
		else
			$dbgMsg="see dwIpcMsgError:".\PHP_EOL.$dwIpcMsgError;
		$this->resourceResponse( "ipc error ".\PHP_EOL.$dbgMsg );
	}
	
	public function resourceResponse( $objectUnserialized ) : void {
		if ( $this->isSended() )
			throw new \Exception( "response ever sended!!!!" );
		else {
			$this->isSended = true;
			if ( \is_object( $objectUnserialized )
			&& \method_exists( $objectUnserialized,'toString' ))
				$this->getDwResourceIpConnexion()->response( $objectUnserialized->toString() );
			elseif ( \is_string( $objectUnserialized ))
				$this->getDwResourceIpConnexion()->response( $objectUnserialized );
			else
				$this->getDwResourceIpConnexion()->response( "".$objectUnserialized );
			//$this->getDwResourceIpConnexion()->response( $data );
		}
	}
	
	public function read() : ?string {
		return $this->getDwResourceIpConnexion()->read();
	}
	public function getSrvIp() : DwIp {
		return $this->getDwResourceIpConnexion()->getSrvIp();
	}
	public function getSrvPort() : DwPort {
		return $this->getDwResourceIpConnexion()->getSrvPort();
	}
	public function getCltIp() : DwIp {
		return $this->getDwResourceIpConnexion()->getCltIp();
	}
	public function getCltPort() : ?DwPort {
		return $this->getDwResourceIpConnexion()->getCltPort();
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

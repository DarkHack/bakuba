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

namespace bakuba\kernel\services\dns;
//use bakuba\kernel\services\dns\DwDnsd as DwDnsd;

use \bakuba\kernel\protocole\dns\DwRecord as DwRecord;
use \bakuba\kernel\protocole\dns\DwMessage as DwMessage;
use \bakuba\kernel\protocole\dns\DwDnsResponse as DwDnsResponse;
use \bakuba\kernel\protocole\dns\DwDnsRequest as DwDnsRequest;

use \bakuba\kernel\services\server\DwServerConnexion as DwServerConnexion;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwDnsd extends \bakuba\kernel\services\DwService {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className = \darkphp\source\DwSource::classNameToTopName( $this->getClassName() );
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,"dns"
				,$className
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	
	private $ifacesResolvers = array();
	public function query( DwCallBack $dwCallBack ,DwDnsRequest $dwDnsRequest ) {
		$dwDnsResponse = $this->dwDnsDatabase->query( $dwDnsRequest );
		if ( $dwDnsResponse->hasOneAnswer() )
			$dwCallBack->callback( $dwDnsResponse );
		else {
			// --------------------------------------------------
			$dwCltIp = $dwDnsRequest->getCltIp();
			$dwCIDR = $dwCltIp->getDwCIDR();
			$dwInterface = $dwCIDR->getDwInterface();
			$ifaceName=$dwInterface->getIfaceName();
			if ( isset( $this->ifacesResolvers[ $ifaceName ] ) ) {
				$ifaceResolv = $this->ifacesResolvers[ $ifaceName ];
			} else {
				$dnsServers = $dwInterface->getDnsServers();
				$ipDnsServer = isset( $dnsServers[ count( $dnsServers )  - 1 ] ) ? $dnsServers[ count( $dnsServers )  - 1 ] : "8.8.8.8";
				$this->log->debug( "[{$ifaceName}] set DwDnsResolver as ipSrv:{$ipDnsServer}" );
				$ifaceResolv = new \bakuba\kernel\network\dnsResolver\DwDnsResolver(
					$this->getDwApplication()->getDwResources()
					,$ipDnsServer
					,$dwInterface
				);
				$this->ifacesResolvers[ $ifaceName ] = $ifaceResolv;
			}
			$ifaceResolv->dnsResolvRequest( new DwCallBack( array( &$this, "externAnswer" ) ,$dwCallBack ,$dwDnsRequest ) ,$dwDnsRequest );
		}
	}
	public function externAnswer( DwCallBack $dwCallBack ,DwDnsRequest $dwDnsRequest ,\bakuba\kernel\protocole\dns\DwDnsMessage $dwDnsMessage ) {
		$dwDnsResponse = new DwDnsResponse( $dwDnsRequest );
		$dwDnsResponse->setDnsMessage( $dwDnsMessage );
		$dwCallBack->callback( $dwDnsResponse );
	}
	public function addRecord( ) {
			return $this->dwDnsDatabase->addRecord( $dwDnsRequest );
	}
	public function addDomain( string $domain ) {
		$this->dwDnsDatabase->addDomain( $domain );
	}
	private $dwApplication = null;
	private $dwDnsDatabase = null;
	///*
	public function __construct(
		\bakuba\kernel\services\application\DwApplication $dwApplication
	) {
		parent::__construct( $dwApplication );
		$this->dwDnsDatabase=new DwDnsDatabase( $this );
	}
	public function init() {
		$this->getDwApplication()->ipcRegisterHandle(
			DwDnsdRequest::getStaticClassName()
			,new \bakuba\kernel\objects\DwCallBack( array( &$this ,'handleDwDnsdRequest' ) )
		);
		$this->log->trace(".");
		
	}
	public function handleDwDnsdRequest(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwDnsdRequest $dwDnsdRequest
	) : void {
		$this->log->debug(".");
		//$this->log->debug( $dwDnsdRequest );
		$dwDnsdOrder = $dwDnsdRequest->getDwDnsdOrder();
		$this->log->debug( "\r\n{$dwDnsdOrder->orderToInt()}:{$dwDnsdOrder->toString()}" );
		$dwDnsdResponse = new DwDnsdResponse( "what???" );
		
		switch( $dwDnsdOrder->orderToInt() ) {
//		case DwDnsdOrder::addRecord :
//			break;
		case DwDnsdOrder::bindPortToServer :
			$dwPort = $dwDnsdRequest->getObject();
			if ( $this->serverIsFree( $dwPort ) ) {
				
				$dwResources = $this->getDwResources();
				//DwSocketServerProcess(
				$dwSocketServer = new \bakuba\kernel\io\socket\dns\DwSocketServer(
					$dwResources->getSocketSelect()
					,$this->onConnect()
					,$dwPort
				);
				$dwSocketServer->init();
				$this->serverBind( $dwPort ,$dwSocketServer );
				$this->dwDnsDatabase->addIp( $dwPort->getIp() );
				$dwDnsdResponse = new DwDnsdResponse( "socket server created" );
			} else
				$dwDnsdResponse = new DwDnsdResponse( "server ever lunched" );
			
			break;
		default :
			$dwDnsdResponse = new DwDnsdResponse(
				"unknow order:".$dwApplicationRequest->getOrder()
			);
			$this->log->error( $dwApplicationRequest );
			break;
		}
		$dwProcessConnexion->ipcResponse( $dwDnsdResponse );
	}
	//*/
	
	public function resourceIpConnexion( \bakuba\kernel\io\DwResourceIpConnexion $dwResourceIpConnexion ) : void {
		new DwDnsdConnexion(
			$this
			,$dwResourceIpConnexion
		);
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

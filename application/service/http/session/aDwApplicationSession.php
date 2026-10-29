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

namespace bakuba\kernel\application\service\http\session;
//use bakuba\kernel\application\service\http\aDwApplicationSession as aDwApplicationSession;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\io\ipc\DwIpcMsg as DwIpcMsg;
use bakuba\kernel\application\service\http\DwApplicationRequest as DwApplicationRequest;

use bakuba\kernel\session\DwSessionIpKey as DwSessionIpKey;

use bakuba\kernel\services\DwServiceRequest as DwServiceRequest;

use bakuba\kernel\services\http\DwHttpdRequest as DwHttpdRequest;
use bakuba\kernel\services\http\DwHttpdOrder as DwHttpdOrder;

abstract class aDwApplicationSession extends \bakuba\kernel\application\service\session\aDwApplicationSession implements iDwApplicationSession {
	
	private $domain = "darkweb.fr";
	public function getDomain() : string {
		return $this->domain;
	}
	private $otherdomains = null;
	public function getOtherDomains() : ?array {
		return $this->otherdomains;
	}
	public function getDomains( ) : array {
		$domains = $this->getOtherDomains();
		if ( is_null( $domains ) )
			$domains = array();
		$domains[] = $this->getDomain();
		return $domains;
	}
	public function addDomain( string $domain ) : void {
		if ( ! isset( $this->otherdomains ) || \is_null( $this->otherdomains ) )
			$this->otherdomains = array();
		$this->otherdomains[]=$domain;
		$this->dwNode->addDomain( $domain );
	}
	private $dwNode = null;
	protected function __construct( \bakuba\kernel\application\service\aDwApplication $parent ,string $urlApp ,string $domain ) {
		$this->domain = $domain;
		parent::__construct( $parent ,$urlApp );
		$this->dwNode = $this->parent->getNode();
		$this->dwNode->addDomain( $this->domain );
		//$this->log->trace( "[{$this->getClassName()}/{$this->getInstanceKey()}]" );
	}
	public function getDwServerProcess() : \bakuba\kernel\services\application\DwApplication {
		return $this->parent->getDwServerProcess();
	}
	
	protected function init( ) : void {
		parent::init();
		$this->ipcSend(
			new DwCallBack( array( &$this, "appRegistered" ) )
			,$this->getDwApplicationServices()
			,new DwHttpdRequest(
				DwHttpdOrder::getOrder( DwHttpdOrder::appRegister )
				,$this
			)
		);
		$this->log->trace( "[{$this->toString()}] ipcSended:appRegistered" );
	}
	public function appRegistered(
		\bakuba\kernel\services\http\DwHttpdResponse $dwHttpdResponse
	) : void {
		$this->log->trace( $dwHttpdResponse );
	}
	public function rootShortcutInit(
		\bakuba\kernel\services\http\DwRootShortcut $dwRootShortcut
	) : void {
		$this->ipcSend(
			new DwCallBack( array( &$this->log, "info" ) )
			,$this->getDwApplicationServices()
			,new DwHttpdRequest(
				DwHttpdOrder::getOrder( DwHttpdOrder::createRootShortCut )
				,$dwRootShortcut
			)
		);
		//$this->log->trace( "[{$this->toString()}]" );
	}
	public function shortcutInit(
		\bakuba\kernel\services\http\DwShortcut $dwShortcut
	) : void {
		$this->ipcSend(
			new DwCallBack( array( &$this->log, "info" ) )
			,$this->getDwApplicationServices()
			,new DwHttpdRequest(
				DwHttpdOrder::getOrder( DwHttpdOrder::createShortCut )
				,$dwShortcut
			)
		);
		//$this->log->trace( "[{$this->toString()}]" );
	}
	public function searchSession(
		DwHttpdRequest $dwHttpdRequest
	) : ?\bakuba\kernel\session\application\aDwApplicationSession {
		return null;
	}
	/*
	protected function __construct( \bakuba\kernel\application\aDwApplication $parent ,string $urlApp ) {
		parent::__construct( $parent ,$urlApp );
		$this->log->trace("[{$this->toString()}]");
	}
	//*/
	/*
	protected function init( ): void {
		//\bakuba\kernel\session\application\aDwApplicationSessionManager::init();
		parent::init();
		$this->log->trace("[{$this->toString()}]");
	}
	//*/
	/*
	public function handleApplicationRequest(
		\bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : void {
		$this->log->trace( $dwApplicationRequest );
		$this->forwardAndStartIfNotStarted(
			$dwApplicationRequest->getDwHttpServiceRequest()
			,$dwIpcMsg
		);
	}
	//*/
	/*
	public function createApplicationSession(
		\bakuba\kernel\session\DwSessionIpKey $dwSessionIpKey
	) : aDwApplicationSession  {
		return $this->createCustomeApplicationSession(
			new aDwApplicationSession( $this, $dwSessionIpKey )
			,$dwSessionIpKey
		);
	}
	//*/
	/*
	public function createCustomeApplicationSession(
		aDwApplicationSession $aDwApplicationSession
		,DwSessionIpKey $dwSessionIpKey
	) : aDwApplicationSession {
		$this->startApplicationSession( $dwSessionIpKey, $aDwApplicationSession );
		// 1200 s => 60 x 20 => out in 20mn
		$aDwApplicationSession->setTimeout(1200);
		
		return $aDwApplicationSession;
	}
	//*/
	public function connectApp(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\aDwApplicationConnexion {
		$this->log->warn( $dwApplicationRequest );
		throw new \Exception("No connectApp");
	}
	public function serviceConnectApp(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\service\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\service\aDwApplicationConnexion {
		throw new \Exception("No serviceConnectApp");
	}
	
	public function httpServiceConnect(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\service\http\aDwApplicationConnexion {
		
		return new class( $this ,$dwProcessConnexion ,$dwApplicationRequest ) 
			extends \bakuba\kernel\application\service\http\aDwApplicationConnexion {
			
			public function httpServiceRequest( \bakuba\kernel\services\http\DwHttpServiceRequest $dwHttpServiceRequest ) 
				: void {
				$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
				$dwHttpResponse = new \bakuba\kernel\protocole\http\DwHttpResponse(
					$dwHttpRequest
				);
				$dwHttpResponse->setHeader( "Content-type","text/plain; charset=utf-8" );
				$dwHttpResponse->setResponseCode( 200 );
				
				$dwHttpResponse->response( $dwHttpRequest->getCltIp() );
				
				$this->fullResponse( $dwHttpResponse );
			}
		};
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

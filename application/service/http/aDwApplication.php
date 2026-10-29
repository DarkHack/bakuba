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

namespace bakuba\kernel\application\service\http;
//use bakuba\kernel\application\service\http\aDwApplication as aDwApplication;
//use bakuba\kernel\application\service\http\aDwApplicationServer as aDwApplicationServer;
//use bakuba\kernel\application\service\http\DwApplicationRequest as DwApplicationRequest;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use \bakuba\kernel\services\http\DwHttpdRequest as DwHttpdRequest;
use \bakuba\kernel\services\http\DwHttpdOrder as DwHttpdOrder;
abstract class aDwApplication
	extends \bakuba\kernel\application\service\aDwApplication implements iDwApplication {
	
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
	public function getServiceDwApplication() : \bakuba\kernel\application\service\aDwApplication {
		return $this->parent;
	}
	public function getDwServerProcess() : \bakuba\kernel\services\application\DwApplication {
		return $this->getServiceDwApplication()->getDwServerProcess();
	}
	
	protected function init( ) : void {
		parent::init();
		$this->ipcRegisterHandle(
			DwApplicationRequest::getStaticClassName()
			,new \bakuba\kernel\objects\DwCallBack( array( &$this ,'handleApplicationServiceHttpRequest' ) )
		);
		
		//if ( \is_a( $this->parent,\bakuba\kernel\argatha\DwBakuba::getStaticClassName() )
		$this->ipcSend(
			new DwCallBack( array( &$this, "appRegistered" ) )
			,$this->getDwApplicationServices()
			,new DwHttpdRequest(
				DwHttpdOrder::getOrder( DwHttpdOrder::appRegister )
				,$this
			)
		);
		$this->log->trace( "ipcSended:appRegistered" );
	}
	public function handleApplicationServiceHttpRequest(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) : void {
		$this->httpServiceConnect( $dwProcessConnexion ,$dwApplicationRequest );
	}
	public function serviceConnectApp(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\service\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\service\iDwApplicationConnexion {
		throw new \Exception("No serviceConnectApp");
	}
	public function httpServiceConnect(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) : iDwApplicationConnexion {
		return new aDwApplicationConnexion( $this ,$dwProcessConnexion ,$dwApplicationRequest );
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
		$this->log->trace( "[{$this->toString()}]" );
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
		$this->log->trace( "[{$this->toString()}]" );
	}
	public function appRegistered(
		\bakuba\kernel\services\http\DwHttpdResponse $dwHttpdResponse
	) : void {
		$this->log->trace( $dwHttpdResponse );
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
	//DwApplication::createApplication
	// dwItemContainer : DwApplicationManager(item) => DwBakuba(item) => DwNode(item) => DwAgartha(map)
	public static function createApplicationHttp(
		\bakuba\kernel\application\service\aDwApplication $dwParent
		,string $urlApp
		,string $domain
	) : iDwApplication {
		$classname="$urlApp\\DwApplication";
		return new $classname( $dwParent ,$urlApp ,$domain );
	}
}

?>

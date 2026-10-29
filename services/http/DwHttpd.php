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

namespace bakuba\kernel\services\http;
//use bakuba\kernel\services\http\DwHttpd as DwHttpd;

class DwHttpd extends \bakuba\kernel\services\DwService {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className = \darkphp\source\DwSource::classNameToTopName( $this->getClassName() );
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,"http"
				,$className
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	
	///*
	///*
	public function __construct(
		\bakuba\kernel\services\application\DwApplication $dwApplication
	) {
		parent::__construct( $dwApplication );
	}
	//*/
	public function init() {
		$this->parent->ipcRegisterHandle(
			DwHttpdRequest::getStaticClassName()
			,new \bakuba\kernel\objects\DwCallBack( array( &$this ,'handleDwHttpdRequest' ) )
		);
		$dwRootShortcut = new DwRootShortcut( $this );
		$dwRootShortcut->rootCreateFileShortcut( "file://bakuba/kernel/images/favicon.ico" ,"ico" ,"/favicon.ico" );
		
		$dwRootShortcut->rootCreateContentShortcut(
			"function FindProxyForURL(url, host) {".\PHP_EOL
			."    return \"DIRECT\";".\PHP_EOL
			."}".\PHP_EOL
			,"dat"
			,"/wpad.dat"
			,"application/x-ns-proxy-autoconfig"
		);
		$this->createRootShortCut( $dwRootShortcut );
		$this->log->trace(".");
	}
	private $serverBinded = array();
	public function handleDwHttpdRequest(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwHttpdRequest $dwHttpdRequest
	) : void {
		$dwIpcMsg = $dwProcessConnexion->getDwIpcMsg();
		$this->log->debug(".");
		//$this->log->debug( $dwHttpdRequest );
		$dwHttpdOrder = $dwHttpdRequest->getDwHttpdOrder();
		$this->log->debug( "\r\n{$dwHttpdOrder->orderToInt()}:{$dwHttpdOrder->toString()}" );
		$dwHttpdResponse = new DwHttpdResponse( "what???" );
		
		switch( $dwHttpdOrder->orderToInt() ) {
		case DwHttpdOrder::bindPortToSecureServer :
			$dwPort = $dwHttpdRequest->getObject();
			$arrKey = "{$dwPort->getIp()->toString()}:{$dwPort->getPort()}";
			$this->log->trace( "bindPortToSecureServer [{$arrKey}]" );
			if ( ! isset($this->serverBinded[ $arrKey ] ) ) {
				$dwResources = $this->getDwResources();
				//DwSocketServerProcess(
				//$dwSocketServer = new \bakuba\kernel\io\socket\http\DwSocketServerDefaultSsl(
				//	$dwResources->getSocketSelect( $this->parent->log )
				$dwSocketServer = new \bakuba\kernel\io\stream\DwStreamSocketServerSsl(
					$dwResources->getStreamSelect()
					,$this->onConnect()
					,$dwPort
				);
				$dwSocketServer->init();
				$this->serverBinded[ $arrKey ]=$dwSocketServer;
				$dwHttpdResponse = new DwHttpdResponse( "socket server created" );
			} else
				$dwHttpdResponse = new DwHttpdResponse( "port server ever binded" );
			
			break;
		case DwHttpdOrder::bindPortToServer :
			$dwPort = $dwHttpdRequest->getObject();
			if ( $this->serverIsFree( $dwPort ) ) {
				$dwResources = $this->getDwResources();
				//DwSocketServerProcess(
				//$dwSocketServer = new \bakuba\kernel\io\stream\DwStreamSocketServerSsl(
				//	$dwResources->getStreamSelect( $this->parent->log )
				$dwSocketServer = new \bakuba\kernel\io\socket\http\DwSocketServerDefaultSsl(
					$dwResources->getStreamSelect( )
					,$dwResources->getSocketSelect( )
					,$this->onConnect()
					,$dwPort
				);
				$dwSocketServer->init();
				$this->serverBind( $dwPort ,$dwSocketServer );
				$dwHttpdResponse = new DwHttpdResponse( "socket server created" );
			} else
				$dwHttpdResponse = new DwHttpdResponse( "port server ever binded" );
			
			break;
		case DwHttpdOrder::appRegister :
			if ( $this->registerApp( $dwIpcMsg->getSrcKey() ,$dwHttpdRequest->getObject() ) )
				$dwHttpdResponse = new DwHttpdResponse( "appRegistered" );
			else
				$dwHttpdResponse = new DwHttpdResponse( "error on registered" );
			
			break;
		case DwHttpdOrder::dwApplicationSession :
			$dwApplicationSession = $dwHttpdRequest->getObject();
			$dwApplication = $this->dwService->getAppFromHost(
				$dwApplicationSession->getDomain()
			);
			if ( is_null( $dwApplication ) )
				$this->log->error(
					"no dwApplication domain for session :"
					.$dwApplicationSession->getDomain()
				);
			$dwApplication->addSession( $dwApplicationSession );
			$this->log->debug($dwApplicationSession);
			$dwHttpdResponse = new DwHttpdResponse( "dwApplicationSession created" );
			break;
		case DwHttpdOrder::dwApplicationSessionClose :
			$dwApplicationSession = $dwApplicationRequest->getObject();
			$dwApplication = $this->dwService->getAppFromHost(
				$dwApplicationSession->getDomain()
			);
			$dwApplication->removeSession( $dwApplicationSession );
			
			//$this->log->debug($dwApplicationSession);
			$dwHttpdResponse = new DwHttpdResponse( "dwApplicationSession stopped" );
			break;
		case DwHttpdOrder::createRootShortCut :
			$dwObject = $dwHttpdRequest->getObject();
			$this->log->trace( "createRootShortCut :".get_class( $dwObject ) );
			$this->createRootShortCut( $dwObject );
			$dwHttpdResponse = new DwHttpdResponse(
				"application root shortcut created."
			);
			break;
		case DwHttpdOrder::createShortCut :
			$dwObject = $dwHttpdRequest->getObject();
			$this->createShortCut( $dwIpcMsg->getSrcKey() ,$dwObject );
			$dwHttpdResponse = new DwHttpdResponse(
				"application shortcut created to server."
			);
			break;
		case DwHttpdOrder::getApplicationFromHost :
			break;
		case DwHttpdOrder::createApplication :
			$dataApplication = $dwHttpdRequest->getObject();
			$appHost = $dataApplication[0];
			$appUrl = $dataApplication[1];
			/*
			$this->log->trace( "createApplication [$appHost:$appUrl]" );
			$dwApplication = $this->dwApplicationManager->createApplication(
				$this
				,$appHost
				,$appUrl
			);
			
			$this->createProcess( $dwApplication );
			//*/
			$dwHttpdResponse = new DwHttpdResponse(
				"application created $appUrl:$appHost"
			);
			break;
		default :
			$dwHttpdResponse = new DwHttpdResponse(
				"unknow order:".$dwApplicationRequest->getOrder()
			);
			$this->log->error( $dwApplicationRequest );
			break;
		}
		$dwProcessConnexion->ipcResponse( $dwHttpdResponse );
	}
	public function resourceIpConnexion( \bakuba\kernel\io\DwResourceIpConnexion $dwResourceIpConnexion ) : void {
		new DwHttpdConnexion(
			$this
			,$dwResourceIpConnexion
		);
	}
	
	private $appRegistered = array();
	public function registerApp( \bakuba\kernel\io\ipc\DwIpcKey $dwIpcKey ,\bakuba\kernel\application\service\http\iDwApplication $aDwApplication ) : bool {
		$key = $dwIpcKey->getKey();
		if ( isset( $this->appRegistered[ $key ] ) )
			$this->log->warn("overwrite key:$key");
		else
			$this->appRegistered[ $key ] = $aDwApplication;
		$allDomains = $aDwApplication->getOtherDomains();
		if ( is_null( $allDomains ) )
			$allDomains = array();
		
		$allDomains[] = $aDwApplication->getDomain();
		$this->log->debug( $allDomains );
		foreach( $allDomains as $domain ) {
			if ( isset( $this->hostShortcut[$domain] ) )
				$this->log->warn("table[hostShortcut] overwrite :$domain");
			$this->hostShortcut[ $domain ] = $aDwApplication;
			$this->parent->addDomain( $domain );
			$this->log->info("hostShortcut [".$aDwApplication->getClassName()."] : $domain");
		}
		$this->log->info("registre [".$aDwApplication->getClassName()."] : $key");
		return true;
	}
	private $hostShortcut = array();
	public function getAppFromHost( string $domain ) : ?\bakuba\kernel\application\service\http\iDwApplication {
		if ( isset( $this->hostShortcut[ $domain ] ) && is_object( $this->hostShortcut[ $domain ] ) )
			return $this->hostShortcut[ $domain ];
		else
			return null;
	}
	
	private $dwShortcuts = array();
	public function createShortcut( \bakuba\kernel\io\ipc\DwIpcKey $dwIpcKey ,DwShortcut $dwShortcut ) {
		$dwShortcut->init();
		$key = $dwIpcKey->getKey();
		if( ! isset( $this->appRegistered[ $key ] ) ) 
			throw new \Exception( "app not registered :$key" );
		else {
			
			if ( ! is_array( $dwShortcut->shortcuts ) )
				$this->log->error( "attribute shortcuts not an array");
			else {
				foreach( $dwShortcut->shortcuts as $uri => $shortcut ) {
					$this->log->trace( "uri : $uri" );
				}
			}
			
			$aDwApplication=$this->appRegistered[ $key ];
			
			$allDomains = $aDwApplication->getOtherDomains();
			if ( is_null( $allDomains ) )
				$allDomains = array();
			
			$allDomains[] = $aDwApplication->getDomain();
			//$this->log->debug( $allDomains );
			foreach( $allDomains as $domain ) {
				if ( isset( $this->dwShortcuts[$domain] ) )
					$this->log->warn("table[dwShortcuts] overwrite :$domain");
				else
					$this->dwShortcuts[$domain]=array();
				
				$this->dwShortcuts[ $domain ][] = $dwShortcut;
				//$this->log->info("registre [".$aDwApplication->getClassName()."/".$dwShortcut->getClassName()."] : $domain");
			}
		
		}
	}
	public function getShortcut( string $domain ,string $uri ) : ?array {
		$dataShortcut = null;
		if ( isset( $this->dwShortcuts[ $domain ] ) ) {
			//$this->log->trace( $domain.":".count( $this->dwShortcuts[ $domain ] ) );
			foreach( $this->dwShortcuts[ $domain ] as $dwShortcut ) {
				$dataShortcut = $dwShortcut->getFromUri( $uri );
				if ( is_array( $dataShortcut ) )
					break;
			}
		}
		return $dataShortcut;
	}

	
	private $rootShortcut = array();
	public function createRootShortcut( DwRootShortcut $dwRootShortcut ) {
		$this->log->trace(".");
		$dwRootShortcut->init();
		$this->rootShortcut[] = $dwRootShortcut;
	}
	public function getRootShortcut( string $uri ) : ?array {
		$dataShortcut = null;
		foreach( $this->rootShortcut as $dwRootShortcut ) {
			$dataShortcut = $dwRootShortcut->getFromUri( $uri );
			if ( is_array( $dataShortcut ) )
					break;
		}
		return $dataShortcut;
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

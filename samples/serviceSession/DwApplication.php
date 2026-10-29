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

namespace bakuba\kernel\samples\serviceSession;
//use bakuba\kernel\samples\serviceSession\DwApplication as DwApplication;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\services\http\DwHttpdRequest as DwHttpdRequest;
use bakuba\kernel\services\http\DwHttpdOrder as DwHttpdOrder;

//use bakuba\kernel\application\service\aDwApplication as aDwApplication;
//use bakuba\kernel\application\service\aDwApplicationConnexion as aDwApplicationConnexion;
use bakuba\kernel\application\service\iDwApplicationConnexion as iDwApplicationConnexion;

use bakuba\kernel\application\service\session\aDwApplicationSession as aDwApplication;
use bakuba\kernel\application\service\session\aDwApplicationConnexion as aDwApplicationConnexion;
//use bakuba\kernel\application\service\session\iDwApplicationConnexion as iDwApplicationConnexion;

class DwApplication extends aDwApplication implements \bakuba\kernel\application\service\http\iDwApplication  {
	
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
	public function __construct( \bakuba\kernel\application\service\aDwApplication $parent ,string $urlApp ,string $domain ) {
		$this->domain = $domain;
		parent::__construct( $parent ,$urlApp );
		$this->dwNode = $this->parent->getNode();
		$this->dwNode->addDomain( $this->domain );
		//$this->log->trace( "[{$this->getClassName()}/{$this->getInstanceKey()}]" );
	}
	public function getDwServerProcess() : \bakuba\kernel\services\application\DwApplication {
		return $this->parent->getDwServerProcess();
	}
	public function init( ) : void {
		$this->addDomain( "serviceSession.darkweb.fr" );
		parent::init();
		$this->ipcRegisterHandle(
			\bakuba\kernel\application\service\http\DwApplicationRequest::getStaticClassName()
			,new DwCallBack( array( &$this ,'handleApplicationServiceHttpRequest' ) )
		);
		
		$this->ipcSend(
			new DwCallBack( array( &$this, "appRegistered" ) )
			,$this->getDwApplicationServices()
			,new DwHttpdRequest(
				DwHttpdOrder::getOrder( DwHttpdOrder::appRegister )
				,$this
			)
		);
		$this->log->trace( "[{$this->toString()}] ipcSended for Registerer app to httpd service." );
	}
	
	public function handleApplicationServiceHttpRequest(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\service\http\DwApplicationRequest $dwApplicationRequest
	) : void {
		$aDwApplicationConnexion =  $this->httpServiceConnect( $dwProcessConnexion ,$dwApplicationRequest );
	}
	
	public function appRegistered(
		\bakuba\kernel\services\http\DwHttpdResponse $dwHttpdResponse
	) : void {
		$this->log->trace( $dwHttpdResponse );
	}
	public function httpServiceConnect(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\service\http\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\service\http\iDwApplicationConnexion {
		return new class( $this ,$dwProcessConnexion ,$dwApplicationRequest ) 
			extends aDwApplicationConnexion implements \bakuba\kernel\application\service\http\iDwApplicationConnexion {
			public function connectService(  ) : \bakuba\kernel\protocole\DwResponse {
				return $this->connectServiceHttp();
			}
			public function connectServiceHttp(  ) : \bakuba\kernel\protocole\http\DwHttpResponse {
				//$this->log->info( $dwHttpServiceRequest );
				$dwHttpRequest = $this->getDwServiceRequest()->getDwHttpRequest();
				//$this->log->info( $dwHttpRequest );
				$dwSessionIp = $dwHttpRequest->getDwSessionIp();
				
				/*
				$this->log->info( $dwHttpRequest->getGet() );
				$this->log->info( $dwHttpRequest->getPost() );
				//*/
				//$this->log->info( $dwHttpRequest->getMergeValues() );
				
				//$this->log->info( $dwHttpRequest->getDwSessionIpKey() );
				
				$dwHttpResponse = new \bakuba\kernel\protocole\http\DwHttpResponse(
					$dwHttpRequest
				);
				$dwHttpResponse->setHeader( "Cache-Control","no-cache, no-store" );
				$dwHttpResponse->setResponseCode( 200 );
				
				$httpDatas=$dwHttpRequest->getMergeValues();
				$cltIp = $dwHttpRequest->getCltIp();
				$dataResponse = null;
				if ( empty( $httpDatas[ 'type' ] ) )
					$httpDatas[ 'type' ]='html';
				
				$contentType = \bakuba\kernel\protocole\http\DwHttp::extensionToContentType( $httpDatas[ 'type' ] );
				
				$dwHttpResponse->setHeader( "Content-type","{$contentType}; charset=utf-8" );
				
				switch( $httpDatas[ 'type' ] ) {
				case 'css' :
					$dataResponse="/* ip:{$cltIp->getIpAddress()} */";
					break;
				case 'json' :
					$dataResponse='{ "ip":"'.$cltIp->getIpAddress().'" }';
					break;
				case 'js' :
					$dataResponse="var myip = { ip:{$cltIp->getIpAddress()} };";
					break;
				case 'html' :
					$dwSessionChannel = $this->getDwSessionChannel();
					if ( ! isset ( $dwSessionChannel->cpt  ) )
						$dwSessionChannel->cpt = 1;
					else
						$dwSessionChannel->cpt++;
					
					$dataResponse="<html><head><title>Mon IP</title></head><body>{$cltIp->getIpAddress()}<br/><hr/>";
					$dataResponse=\serialize( $dwSessionChannel )."<br/>";
					/*
					foreach( $dwSessionIp->getConnections() as $srvIpKey => $value ) {
						$dataResponse.="<p>connections {$srvIpKey} </p>";
						$dataResponse.="<ul>";
						foreach( $value as $srvPort => $dwSessionIpKey ) {
							$dataResponse.="<li>$srvPort</li>";
						}
						$dataResponse.="</ul>";
					}
					//*/
					$dataResponse.="<hr/>";
					foreach( $dwSessionIp->getCompteurs() as $srvIpKey => $value ) {
						$dataResponse.="<p>compteurs {$srvIpKey} </p>";
						$dataResponse.="<ul>";
						foreach( $value as $srvPort => $count ) {
							$dataResponse.="<li>$srvPort : $count</li>";
						}
						$dataResponse.="</ul>";
					}
					$dataResponse.="</body></html>";
					break;
				default :
					$dataResponse=$cltIp->getIpAddress();
					break;
				}
				
				$dwHttpResponse->response( $dataResponse );
				return $dwHttpResponse;
			}
		};
	}
	public function rootShortcutInit(
		\bakuba\kernel\services\http\DwRootShortcut $dwRootShortcut
	) : void {
		throw new \Exception( 'not implemented here' );
		/*
		$this->ipcSend(
			new DwCallBack( array( &$this->log, "info" ) )
			,$this->getDwApplicationServices()
			,new DwHttpdRequest(
				DwHttpdOrder::getOrder( DwHttpdOrder::createRootShortCut )
				,$dwRootShortcut
			)
		);
		$this->log->trace( "[{$this->toString()}]" );
		//*/
	}
	public function shortcutInit(
		\bakuba\kernel\services\http\DwShortcut $dwShortcut
	) : void {
		throw new \Exception( 'not implemented here' );
		/*
		$this->ipcSend(
			new DwCallBack( array( &$this->log, "info" ) )
			,$this->getDwApplicationServices()
			,new DwHttpdRequest(
				DwHttpdOrder::getOrder( DwHttpdOrder::createShortCut )
				,$dwShortcut
			)
		);
		$this->log->trace( "[{$this->toString()}]" );
		//*/
	}
	public function connectApp(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\iDwApplicationConnexion {
		$this->log->warn($dwApplicationRequest);
		throw new \Exception("No connectApp");
	}
	
	public function serviceConnectApp(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\service\DwApplicationRequest $dwApplicationRequest
	) : iDwApplicationConnexion {
		throw new \Exception("No serviceConnectApp");
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

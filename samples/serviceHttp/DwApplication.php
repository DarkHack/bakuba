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

namespace bakuba\kernel\samples\serviceHttp;
//use bakuba\kernel\samples\serviceHttp\DwApplication as DwApplication;

class DwApplication extends \bakuba\kernel\application\service\http\aDwApplication {
	public function init( ) : void {
		$this->addDomain( "serviceHttp.darkweb.fr" );
		parent::init();
	}
	public function httpServiceConnect(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\service\http\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\service\http\aDwApplicationConnexion {
		
		return new class( $this ,$dwProcessConnexion ,$dwApplicationRequest ) 
			extends \bakuba\kernel\application\service\http\aDwApplicationConnexion {
			
			public function connectServiceHttp(  ) : \bakuba\kernel\protocole\http\DwHttpResponse {
				//$this->log->info( $dwHttpServiceRequest );
				$dwHttpRequest = $this->getDwHttpRequest();
				$this->log->info( $dwHttpRequest );
				/*
				$this->log->info( $dwHttpRequest->getServer() );
				$this->log->info( $dwHttpRequest->getGet() );
				$this->log->info( $dwHttpRequest->getPost() );
				//*/
				//$this->log->info( $dwHttpRequest->getMergeValues() );
				//$this->log->info( $dwHttpRequest->getDwSessionIpKey() );
				
				$dwSessionIp =$dwHttpRequest->getDwSessionIp();
				
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
					$dataResponse="<html><head><title>Mon Service Http</title></head><body>{$cltIp->getIpAddress()}<br/><hr/>";
					/*
					foreach( $dwSessionIp->getConnections() as $srvIpKey => $value ) {
						$dataResponse.="<p>{$srvIpKey} connections</p>";
						$dataResponse.="<ul>";
						foreach( $value as $srvPort => $dwSessionIpKey ) {
							$dataResponse.="<li>$srvPort</li>";
						}
						$dataResponse.="</ul>";
					}
					//*/
					$dataResponse.="<hr/>";
					foreach( $dwSessionIp->getCompteurs() as $srvIpKey => $value ) {
						$dataResponse.="<p>{$srvIpKey} compteurs</p>";
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

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
//use bakuba\kernel\services\http\DwHttpdConnexion as DwHttpdConnexion;

use bakuba\kernel\protocole\http\DwHttpResponse as DwHttpResponse;

class DwHttpdConnexion extends \bakuba\kernel\services\aDwServiceConnexion {
	
	private function getDwHttpd() : DwHttpd {
		return $this->getDwService();
	}
	
	public function __construct(
		\bakuba\kernel\services\http\DwHttpd $dwService
		,\bakuba\kernel\io\DwResourceIpConnexion $dwResourceIpConnexion
	) {
		parent::__construct( $dwService ,$dwResourceIpConnexion );
	}
	
	// DwSocketConnexion->request => 
	public function request( ) : void {
		//parent::request();
		// read request
		$rawData = $this->read();
		try {
			//$this->log->trace( $dwService->getClassName().":".$rawData );
			$dwRequest = \bakuba\kernel\protocole\http\DwHttpRequest::isValidRequest( $this ,$rawData );
			if ( \is_object( $dwRequest ) )
				$this->httpConnect( $dwRequest );
			else
				$this->resourceResponse( "invalide request" );
				
		} catch( \Exception $exception ) {
			if ( ! $this->isSended() ) 
				$this->resourceResponse( "EXCEPTION" );
			$this->log->error( "exception [{$this->toString()}]" );
			$this->log->error( $exception );
			//throw $exception;
		} catch( \Error $error ) {
			if ( ! $this->isSended() ) 
				$this->resourceResponse( "ERROR" );
			$this->log->error( "error [{$this->toString()}]" );
			$this->log->error( $error );
			//throw $error;
		}
	}
	
	public function httpConnect( \bakuba\kernel\protocole\http\DwHttpRequest $dwHttpRequest ) : void {
		$dwHttpResponseError = null;
		$sended=false;
		try {
			$dwHttpResponseError = new DwHttpResponse( $dwHttpRequest );
			$dwHttpResponseError->setHeader("Content-type","text/plain; charset=utf-8");
			$dwHttpResponseError->setResponseCode(500);
			$dwHttpResponseError->response( __CLASS__."->".__METHOD__." echec." );
			
			$this->AppHttpRequest( new DwHttpServiceRequest( $dwHttpRequest ) );
			
		} catch( \Exception $exception ) {
			if ( ! $this->isSended() ) 
				if ( is_object ( $dwHttpResponseError ) )
					$this->response( $dwHttpResponseError );
				else
					$this->resourceResponse( "exception [".$this->getClassName()."]" );
			$this->log->error("exception [".$this->getClassName()."]");
			$this->log->throwable( $exception );
			//throw $exception;
		} catch( \Error $error ) {
			if ( ! $this->isSended() ) 
				if ( is_object ( $dwHttpResponseError ) )
					$this->response( $dwHttpResponseError );
				else
					$this->resourceResponse( "error [".$this->getClassName()."]" );
			$this->log->error("error [".$this->getClassName()."]");
			$this->log->throwable( $error );
			//throw $error;
		}
	}
	private $dwHttpServiceRequest = null;
	private function AppHttpRequest( DwHttpServiceRequest $dwHttpServiceRequest ) {
		$this->dwHttpServiceRequest=$dwHttpServiceRequest; 
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		
		$dwHttpResponseError = new DwHttpResponse( $dwHttpRequest );
		$dwHttpResponseError->setHeader( "Content-type","text/plain; charset=utf-8") ;
		$dwHttpResponseError->setResponseCode( 500 );
		$dwHttpResponseError->response( "DwService->AppHttpRequest echec." );
		
		$domain = $dwHttpRequest->getRqtHost();
		if ( empty( $domain ) ) {
			$this->log->error( "no host to search, missing \$dwHttpRequest->getRqtHost()" );
			$domain="mystate.darkweb.fr";//throw new \Exception( "no host to search, missing \$dwHttpRequest->getRqtHost()" );
		}
		
		$dwHttpResponseError->response( ":|, sorry, i'm little too dumb, on this :$domain" );
		
		$dwHttpAction = $dwHttpRequest->getDwHttp()->getDwHttpAction();
		$uri = \strtolower( $dwHttpAction->getUri() );
		
		$dwHttpd = $this->getDwHttpd();
		
		$sortCutData = $dwHttpd->getShortcut( $domain ,$uri );
		if ( ! is_array( $sortCutData ) )
			$rootSortCutData = $dwHttpd->getRootShortcut( $uri );
		
		if ( is_array( $sortCutData ) )
			$this->response( $this->shortCut( $dwHttpRequest ,$sortCutData ) );
		elseif( is_array( $rootSortCutData ) )
			$this->response( $this->shortCut( $dwHttpRequest ,$rootSortCutData ) );
		else {
			$dwApplication = $dwHttpd->getAppFromHost( $domain );
			
			if ( is_null( $dwApplication ) ) {
				$this->log->warn("no apps for:$domain");
				$dwApplication = $dwHttpd->getAppFromHost( 'mystate.darkweb.fr' ); // fallback to mystate app
			}
			
			if ( is_null( $dwApplication ) )
				$this->response( $dwHttpResponseError );
			else {
				$this->log->trace("request to $domain by [".$dwApplication->getClassName()."/".$dwApplication->getPid()."]");
				$dstProcess = $dwApplication;
				//$isShortCutted = false;
				//if ( method_exists( $dwApplication, "checkShortcut" ) )
				//$isShortCutted = $this->checkShortcut( $$this->parent );
				/*
				if ( \method_exists( $dwApplication, "getAppFromSession" ) ) {
					$dwApplicationSession = $dwApplication->getAppFromSession(
						$dwHttpServiceRequest
					);
					if ( \is_object( $dwApplicationSession ) ) {
						$this->log->info(
							"request with session {$dstProcess->toString()} => {$dwApplicationSession->toString()}"
						);
						$dstProcess = $dwApplicationSession;
					}
				}
				//*/
				$this->ipcSend(
					$dstProcess
					,new \bakuba\kernel\application\service\http\DwApplicationRequest( $dwHttpServiceRequest )
				);
				$this->log->trace("request process sended");
			}
		}
	}
	private function shortCut(
		\bakuba\kernel\protocole\http\DwHttpRequest $dwHttpRequest
		,array $dataShortcut
	) : \bakuba\kernel\protocole\http\DwHttpResponse {
		
		$dwHttpResponse = new \bakuba\kernel\protocole\http\DwHttpResponse( $dwHttpRequest );
		$dwHttpResponse->setResponseCode( 200 );
		
		if ( isset( $dataShortcut[ "headers" ] ) ) 
			foreach( $dataShortcut[ "headers" ] as $header => $data ) 
				$dwHttpResponse->setHeader( $header , $data );
				
		$dwHttpResponse->response( $dataShortcut[ "data" ] );
		return $dwHttpResponse;
	}
	
	public function throwIpcError( \bakuba\kernel\io\ipc\DwIpcMsgError $dwIpcMsgError ) : void {
		$this->log->error( $dwIpcMsgError );
		
		if ( is_object( $this->dwHttpServiceRequest ) ) {
			if ( \method_exists( $this->log, "getFullPath") )
				$dbgMsg="DwHttpServiceConnexion ERROR, see:\r\n".$this->log->getFullPath()."\r\n";
			else
				$dbgMsg="DwHttpServiceConnexion ERROR, see dwIpcMsgError:\r\n{$dwIpcMsgError}";
			$dwHttpResponseError = new DwHttpResponse( $this->dwHttpServiceRequest );
			$dwHttpResponseError->setHeader( "Content-type","text/plain; charset=utf-8" );
			$dwHttpResponseError->setResponseCode( 500 );
			
			$dwHttpResponseError->response( $dbgMsg );
			//.\bakuba\kernel\services\protocole\http\DwHttpException::throwableToHttpMessage( $errorHandle ) );
			$this->response( $dwHttpResponseError );
		} else
			parent::throwIpcError( $dwIpcMsgError );
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

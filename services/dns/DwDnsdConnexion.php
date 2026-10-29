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
//use bakuba\kernel\services\dns\DwDnsdConnexion as DwDnsdConnexion;

use bakuba\kernel\protocole\http\DwHttpResponse as DwHttpResponse;
class DwDnsdConnexion extends \bakuba\kernel\services\aDwServiceConnexion {
	
	private function getDwDnsd() : DwDnsd {
		return $this->getDwService();
	}
	
	private $dwDnsServiceRequest = null;
	public function getDwDnsServiceRequest() : DwDnsServiceRequest {
		return $this->dwDnsServiceRequest;
	}
	
	public function __construct(
		DwDnsd $dwService
		,\bakuba\kernel\io\DwResourceIpConnexion $dwResourceIpConnexion
	) {
		parent::__construct( $dwService ,$dwResourceIpConnexion );
		$this->log->trace(".");
	}
	// DwSocketConnexion->request => 
	public function request( ) : void {
		$this->log->trace(".");
		// read request
		$rawData = $this->read();
		try {
			//$this->log->trace( $dwService->getClassName().":".$rawData );
			$dwRequest = \bakuba\kernel\protocole\dns\DwDnsRequest::isValidRequest( $this ,$rawData );
			if ( \is_object( $dwRequest ) )
				$this->dnsConnect( $dwRequest );
			else
				$this->resourceResponse( "INVALIDE" );
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
	public function dnsConnect(  \bakuba\kernel\protocole\dns\DwDnsRequest $dwDnsRequest ) {
		$dwDnsResponseError = null;
		$sended=false;
		try {
			$dwDnsResponseError = new \bakuba\kernel\protocole\dns\DwDnsResponse( $dwDnsRequest );
			$dwDnsResponseError->setRcode( \bakuba\kernel\protocole\dns\DwDnsMessage::RCODE_SERVER_FAILURE );
			
			$this->getDwDnsd()->query( new \bakuba\kernel\objects\DwCallBack( array( &$this ,'response' ) ) ,$dwDnsRequest );
		} catch( \Exception $exception ) {
			if ( ! $this->isSended() ) 
				if ( is_object ( $dwDnsResponseError ) )
					$this->response( $dwDnsResponseError );
				else
					$this->resourceResponse( "exception [".$this->getClassName()."]" );
			$this->log->error("exception [".$this->getClassName()."]");
			$this->log->throwable( $exception );
			//throw $exception;
		} catch( \Error $error ) {
			if ( ! $this->isSended() ) 
				if ( \is_object( $dwDnsResponseError ) )
					$this->response( $dwDnsResponseError );
				else
					$this->resourceResponse( "error [".$this->getClassName()."]" );
			$this->log->error("error [".$this->getClassName()."]");
			$this->log->throwable( $error );
			//throw $error;
		}
	}
	
	public function throwIpcError( \bakuba\kernel\io\ipc\DwIpcMsgError $dwIpcMsgError ) : void {
		$this->log->error( $dwIpcMsgError );
		$dwDnsRequest = $this->getDwDnsServiceRequest()->getDwDnsRequest();
		if ( is_object( $dwDnsRequest ) ) {
			$dwDnsResponseError = new DwDnsResponse( $dwDnsRequest );
			$dwDnsResponseError->setRcode( \bakuba\kernel\protocole\dns\DwDnsMessage::RCODE_SERVER_FAILURE );
			$this->response( $dwDnsResponseError );
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

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

namespace bakuba\kernel\application\service\http\controller;
//use bakuba\kernel\application\service\http\controller\DwControllerConnexion as DwControllerConnexion;

use darkphp\web\DwWebUrl as DwWebUrl;

use darkphp\web\DwWebValue as DwWebValue;
//use darkphp\web\iDwWebValue as IDwWebValue;
use darkphp\web\DwWebValue64 as DwWebValue64;

use bakuba\kernel\session\DwSessionIpKey as DwSessionIpKey;

use bakuba\kernel\services\http\DwHttpServiceRequest as DwHttpServiceRequest;
use bakuba\kernel\protocole\http\DwHttpRequest as DwHttpRequest;
use bakuba\kernel\application\service\http\aDwApplicationConnexion as aDwApplicationConnexion;

class DwControllerConnexion extends \darkphp\DwObject implements iDwControllerConnexion {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) ) {
			$dwHttpRequest = $this->parent->getDwHttpServiceRequest()->getDwHttpRequest();
			$rootDir = \bakuba\kernel\agartha\DwInstall::getLocalDirectory( $iDwLogger->getLevelLog() );
			$srvIpDir = $rootDir."/".$dwHttpRequest->getSrvIp()->getIpPath();
			if ( ! is_dir( $srvIpDir ) )
				mkdir( $srvIpDir );
			
			//$className = \darkphp\source\DwSource::classNameToTopName( $this->getClassName() );
			//$dwSessionIpKey = $this->dwHttpServiceRequest->getDwSessionIpKey();
			//$this->log = $iDwLogger->upTo( $this ,$dwSessionIpKey->getIpClt() ,"{$className}.log" );
			$this->log = $iDwLogger->upTo(
				$this
				,$dwHttpRequest->getSrvIp()->getIpPath()."/".$dwHttpRequest->getCltIp()->getIpPath()
				,"ctrlCnxion-".$this->getInstanceKey()
			);
		} else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	
	public function getDwHttpServiceRequest() : DwHttpServiceRequest {
		return $this->parent->getDwHttpServiceRequest();
	}
	public function getDwHttpRequest() : DwHttpRequest {
		return $this->getDwHttpServiceRequest()->getDwHttpRequest();
	}
	public function getMergeValues() : array {
		return $this->getDwHttpRequest()->getMergeValues();
	}
	public function getDwApplicationConnexion() : \bakuba\kernel\application\service\http\aDwApplicationConnexion {
		return $this->parent;
	}
	private $iDwController = null;
	public function getiDwController() : iDwController {
		return $this->iDwController;
	}
	
	
	protected $valType = null;
	public function getTypeValue() {
		return $this->valType->getValue();
	}
	
	private $dwHttpResponse = null;
	public function __construct(
		\bakuba\kernel\application\service\http\aDwApplicationConnexion $aDwApplicationConnexion
		,iDwController $iDwController
	) {
		$this->iDwController = $iDwController;
		parent::__construct( $aDwApplicationConnexion );
		$this->log->trace( "." );
		$this->dwHttpResponse = new \bakuba\kernel\protocole\http\DwHttpResponse( $this->getDwHttpRequest() );
		$this->setResponseCode( 200 );
		
		$this->valType = new DwWebValue( "type" );
		$this->valType->setValue( "html" );
		
		$this->addPublicValue( $this->valType );
	}
	protected $publicValues = array();
	public function addPublicValue( DwWebValue $dwWebValue ) {
		$this->publicValues[] = $dwWebValue;
	}
	public function getPublicValues( ) : array {
		return $this->publicValues;
	}
	public function setResponseCode( int $httpResponseCode ) {
		$this->dwHttpResponse->setResponseCode( $httpResponseCode );
	}
	private $redirected = false;
	private $done = false;
	public function answered() {
		return $this->done;
	}
	public function isRedirected() {
		return $this->redirected;
	}
	private $isBase64 = false;
	public function enable64Response() {
		$this->isBase64 =true;
	}
	protected function response( $data ) : void {
		//$this->log->trace(".");
		if ( $this->answered() ) {
			if ( $this->isRedirected() )
				$this->log->warn("request redirected!!!!");
			else
				throw new \Exception( "ever answered!" );
		} else {
			if ( $this->isBase64 )
				$this->response64( $data );
			else {
				if ( is_a( $data, DwWebUrl::getStaticClassName() ) )
					$this->redirect( $data );
				else
					$this->responseClear( $data );
			}
		}
	}
	public function response64( string $data64 ) {
		$this->done = true;
		$this->dwHttpResponse->response64( $data64 );
		$this->parent->fullResponse( $this->dwHttpResponse );
	}
	public function responseClear( string $data ) {
		$this->done = true;
		$this->dwHttpResponse->response($data);
		$this->parent->fullResponse( $this->dwHttpResponse );
		//array( 
		//	"response" => base64_encode( $data )
		//	,"headers" => $this->headersList()
		//	,"httpReponseCode" => $this->getHttpReponseCode()
		//) );
	}
	public function responseError( string $data, int $httpResponseCode = 500 ) {
		$this->done = true;
		$this->setResponseCode( $httpResponseCode );
		$this->dwHttpResponse->response($data);
		$this->parent->fullResponse( $this->dwHttpResponse );
		//array( 
		//	"response" => base64_encode( $data )
		//	,"headers" => $this->headersList()
		//	,"httpReponseCode" => $this->getHttpReponseCode()
		//) );
	}
	public function setHeader( $header, string $value ) : void {
			$this->dwHttpResponse->setHeader( $header, $value );
	}
	public function redirection( int $code , string $url ) {
		$this->log->trace( $url);
		$this->setHeader("Location", "$url");
		$this->setResponseCode( 307 );
		$this->response( "" );
		$this->redirected=true;
	}
	public function redirect ( string $url ) : void {
		// TODO : IF HTTP 1.0 redirect 
		$this->redirection( 307, $url );
	}
	/*
	public function redirectPermanent( string $url ) : void {
		$this->redirection( 307, $url );
	}
	public function redirectTemporary( string $url ) : void {
		$this->redirection( 307, $url );
	}
	//*/
	
	/*
	public function getDwWebUrlValue(
		DwControllerConnexion $dwControllerConnexion
		,DwWebValue $dwWebValue
		,$value
	) {
		$Url = null;
		$Url = $this->getUrl( $dwControllerConnexion->getMergeValues(), $dwWebValue );
		if ( is_a( $dwWebValue, DwWebValue64::getStaticClassName() ) )
			$Url->set( $dwWebValue, base64_encode( $value ) );
		else
			$Url->set( $dwWebValue, $value );
		
		return $Url;
	}
	//*/
	
	public function getDwWebUrl( ) : DwWebUrl {
		$dwWebUrl = null;
		//$this->log->trace(".");
		
		$dwWebUrl = new DwWebUrl( $this->getDwHttpRequest() );
		$dwWebUrl->setBaseUri( "/".$this->getiDwController()->getCtrlName() );
		
		if ( ! empty( $this->publicValues ) ) {
			foreach( $this->publicValues as $dwWebValue ) {
				if ( is_a( $dwWebValue, DwWebValue64::getStaticClassName() ) )
					$dwWebUrl->set( $dwWebValue, $dwWebValue->getValueEncoded() );
				else
					$dwWebUrl->set( $dwWebValue, $dwWebValue->getValue() );
			}
		}
		
		return $dwWebUrl;
	}
	
	private function refresh( ) : void {
		$publicValues = $this->getPublicValues();
		if ( ! empty( $publicValues ) ) {
			$dwHttpServiceRequest = $this->getDwHttpServiceRequest();
			$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
			$values = $dwHttpRequest->getMergeValues();
			foreach( $this->publicValues as $dwWebValue )
				$dwWebValue->refresh( $values );
		}
		if ( is_null( $this->valType->getValue() ) )
			$this->valType->setValue( "html" );
	}
	public function execute() : void {
		$this->log->trace( "." );
		$this->refresh();
	}
	
	public function executeController( ) : void {
		try {
			$this->execute();
		} catch( \Exception $exception ) {
			$this->throwError( $exception ,$debugKey );
			//throw $exception;
		} catch( \Error $error ) {
			$this->throwError( $error ,$debugKey );
			//throw $error;
		}
	}
	private function throwError( $errorHandle ,string $extraMsg = null ) {
		if ( \is_string( $extraMsg ) )
			$this->log->error( "\r\n\t{$extraMsg}" );
		$this->log->throwable( $errorHandle );
		
		if ( \method_exists( $this->log, "getFullPath") )
			$this->responseError( "DwControllerConnexion ERROR, see:\r\n".$this->log->getFullPath()."\r\n" );
		else
			$this->responseError( ( \is_null( $extraMsg ) ? "" : "{$extraMsg}\r\n\t" ).\bakuba\kernel\services\protocole\http\DwHttpException::throwableToHttpMessage( $errorHandle ) );
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

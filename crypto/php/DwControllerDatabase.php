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

namespace bakuba\kernel\application\controller;
//use bakuba\kernel\application\controller\DwControllerDatabase as DwControllerDatabase;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use darkphp\web\DwWebUrl as DwWebUrl;
use darkphp\web\DwWebValue as DwWebValue;
use bakuba\kernel\objects\web\DwWebAction as DwWebAction;
use darkphp\web\DwWebValue64 as DwWebValue64;

use bakuba\kernel\application\controller\DwControllerConnexion as DwControllerConnexion;
use bakuba\kernel\application\session\DwSessionControllerConnexion as DwSessionControllerConnexion;


use bakuba\id\application\DwAuthenticationRequest as DwAuthenticationRequest;
use bakuba\id\application\DwAuthenticationResponse as DwAuthenticationResponse;
use bakuba\id\application\DwAuthenticationResponseInit as DwAuthenticationResponseInit;
use bakuba\kernel\application\session\DwSessionRequest as DwSessionRequest;
//use bakuba\kernel\application\session\DwSessionResponse as DwSessionResponse;

use bakuba\kernel\database\DwDatabaseRequest as DwDatabaseRequest;
use bakuba\kernel\database\DwDatabaseResponse as DwDatabaseResponse;
use bakuba\kernel\application\session\DwSessionManager as DwSessionManager;

abstract class DwControllerDatabase extends DwControllerSession {
	private static $sLog = null;
	
	private $valCiphers = null;
	private $valCryptedData = null;
	
	public function __construct( ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( $this->getClassName() );
			parent::__construct( );
			
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
			$this->valCryptedData = new DwWebValue( \bakuba\kernel\randomizer\DwRandomizer::randomize( 10 ) );//."JsonCryptedData"
			$this->valCiphers = new DwWebValue( "ciphers" );
			
			//$this->addPublicValue( $this->valCiphers );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function execute( DwControllerConnexion $dwControllerConnexion ) : void {
		try {
			self::$sLog->trace("[".self::getStaticClassName()."/".$this->getInstanceKey()."] controller Database execute");
			
			self::$sLog->trace( $dwControllerConnexion->getMergeValues() );
			$this->valCryptedData->setValue( null );
			$requestValues = $dwControllerConnexion->getMergeValues();
			$this->valCryptedData->refresh( $requestValues );
			$this->valCiphers->refresh( $requestValues );
			$cryptedData = $this->valCryptedData->getValue();
			self::$sLog->trace( $this->valCryptedData->getKey().":".$cryptedData );
			if ( empty($cryptedData) )
				parent::execute( $dwControllerConnexion );
			else {
				$dwSessionIpKey = $dwControllerConnexion->getDwSessionIpKey();
				$dwSessionManager = $dwControllerConnexion->getDwSessionManager();
				
				self::$sLog->trace("JsonCryptoSearch");
				$dwSessionRequest = new DwSessionRequest( $dwSessionIpKey, "JsonCryptoSearch" );
				$dwSessionRequest->setObject( $cryptedData );
				$dwSessionResponse = $dwSessionManager->JsonCryptoSearch( $dwSessionRequest );
				//$this->shellCryptoSearch( $dwControllerConnexion, $dwSessionResponse );
				
				$this->response( $dwControllerConnexion ,$dwSessionResponse );
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	// ================ JSON RESPONSE ==============
	
	public function jsonAction( DwSessionControllerConnexion $dwSessionControllerConnexion ,DwWebAction $dwWebAction = null ) : void {
		try {
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
			if ( is_null( $dwWebAction ) ) {
				$dwControllerConnexion = $dwSessionControllerConnexion->getDwControllerConnexion();
				//$dwSessionChannel = $dwSessionControllerConnexion->getDwSessionChannel();
				$dwSessionIpKey = $dwControllerConnexion->getDwSessionIpKey();
				$dwHttpServiceRequest = $dwControllerConnexion->getDwHttpServiceRequest();
				
				$dwSessionManager = $dwControllerConnexion->getDwSessionManager();
				$dwSessionRequest = new DwSessionRequest( $dwSessionIpKey, "JsonCryptoCreate" );
				$ciphers = $this->valCiphers->getValue();
				self::$sLog->trace("ciphers:".$ciphers);
				$dwSessionRequest->setCiphers( explode(":" ,$ciphers) );
				$dwSessionResponse = $dwSessionManager->JsonCryptoCreate( $dwSessionRequest );
				$dwSessionCryptoJsonChannel = $dwSessionResponse->getObject();
				
				$dwWebUrl = $this->getDwWebUrl( $dwControllerConnexion );
				
				$jsonData = $dwSessionCryptoJsonChannel->cryptoChannelGet( "message" );
				$jsonData->{"proto"} = $dwHttpServiceRequest->getRqtHttps() == true ? "https" : "http";
				$jsonData->{"port"} = $dwHttpServiceRequest->getSrvPort();
				$jsonData->{"ipaddress"} = $dwHttpServiceRequest->getSrvIp();
				$jsonData->{"host"} = $dwHttpServiceRequest->getRqtHost();
				$jsonData->{"uri"} = $dwWebUrl->getUrlQuery();
				$jsonData->{"postname"} = $this->valCryptedData->getKey();
				
				self::$sLog->trace( $jsonData );
				$this->response( $dwControllerConnexion ,json_encode( $jsonData ) );
			} else
				$this->fileAction( $dwSessionControllerConnexion ,$dwWebAction );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	// ================ NO TYPE REPONSE ==============
	public function newJsonSession( DwSessionControllerConnexion $dwSessionControllerConnexion ,DwWebAction $dwWebAction = null ) : void {
		try {
			self::$sLog->trace($this->valType->getValue()." [".$this->getClassName()."/".$this->getInstanceKey()."]");
			$this->sessionResponse( $dwSessionControllerConnexion, "newJsonSession ???:".$this->valType->getValue());
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	//private $asking = false;
	private $dataCacheResponse = null;
	public function databaseRequest( DwSessionControllerConnexion $dwSessionControllerConnexion ,DwCallBack $dwCallBack ,DwDatabaseRequest $dwDatabaseRequest ) {
		try {
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			//$this->asking = true;
			//$this->ipcSend( $appId, 
			$dwControllerConnexion = $dwSessionControllerConnexion->getDwControllerConnexion();
			//$ctrl = $dwControllerConnexion->getiDwController();
			$dwApplicationConnexion = $dwControllerConnexion->getDwApplicationConnexion();
			$dwProcessMessaging = $dwApplicationConnexion->getDwProcessMessaging();
			
			$this->ipcSend( 
				$dwControllerConnexion
				,$this->dwDatabaseProcess
				,new DwCallBack( array( &$this,"databaseResponse" ) ,$dwSessionControllerConnexion ,$dwCallBack )
				,$dwDatabaseRequest
			); 
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function databaseResponse( DwSessionControllerConnexion $dwSessionControllerConnexion ,DwCallBack $dwCallBack ,DwDatabaseResponse $dwDatabaseResponse ) : void {
		try {
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]" );
			//$this->asking = false;
			$dwCallBack->callback( $dwDatabaseResponse );
			//if ( ! is_null( $this->dataCacheResponse ) && ! $this->asking )
			//	$this->sessionResponse( $dwSessionControllerConnexion ,$this->dataCacheResponse );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

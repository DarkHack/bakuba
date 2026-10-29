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

namespace bakuba\kernel\dwCrypto;
//use bakuba\kernel\dwCrypto\DwJsonCryptController as DwJsonCryptController;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\application\controller\aDwController as aDwController;

use bakuba\kernel\dwNode\DwNode as DwNode;
use bakuba\kernel\DwPreNode as DwPreNode;
use bakuba\kernel\network\DwIp as DwIp;

//use bakuba\kernel\objects\web\DwWebAction as DwWebAction;
use darkphp\web\DwWebValue as DwWebValue;

use bakuba\kernel\application\session\DwSessionIpKey as DwSessionIpKey;
use bakuba\kernel\application\session\DwSessionRequest as DwSessionRequest;
use bakuba\kernel\application\session\DwSessionResponse as DwSessionResponse;

use bakuba\kernel\application\controller\DwControllerConnexion as DwControllerConnexion;
use bakuba\kernel\application\session\cryptoJson\DwCryptoJsonChannel as DwCryptoJsonChannel;

interface IDwJsonCryptController {
}
abstract class DwJsonCryptController  extends aDwController {
	static private $sLog = null;
	protected $valData;
	
	public function __construct( ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			parent::__construct( );
			
			$this->valData = new DwWebValue( "data" );
			$this->addPublicValue( $this->valData );
		
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	public function jsonCryptoSearch( DwControllerConnexion $dwControllerConnexion, DwSessionResponse $dwSessionResponse ) : void {
		global $dwKernel;
		try {
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
			$dwCryptoJsonChannel = $dwSessionResponse->getObject();
			
			if ( ! is_object( $dwCryptoJsonChannel ) ) {
				self::$sLog->error("lost crypt key");
				$dwControllerConnexion->response( "JsonCryptController understand nothing!!!!!!" );
			} else {
				$clearResponse = "";
				$dwKernel->setLogSession( $dwCryptoJsonChannel->getChannelKey() );
				$nextPacket = null;
				try {
					$nextPacket = $dwCryptoJsonChannel->nextPacket();
					$clearResponse = is_null($nextPacket) ? $this->cryptExecute( $dwCryptoJsonChannel ) : $nextPacket;
				} catch(Exception $exception_crypt) {
					self::$sLog->error("exception/cryptExecute [".$this->getClassName()."/".$this->getInstanceKey()."]");
					$clearResponse="exception/cryptExecute [".$this->getClassName()."]";
					self::$sLog->throwable($exception_crypt);
					//throw $exception_crypt;
				} catch(Error $error_crypt) {
					self::$sLog->error("error/cryptExecute [".$this->getClassName()."/".$this->getInstanceKey()."]");
					$clearResponse="error/cryptExecute [".$this->getClassName()."]";
					self::$sLog->throwable($error_crypt);
					//throw $err;
				}
				
				$dwControllerConnexion->response( $dwCryptoJsonChannel->cryptoChannelResponse( $clearResponse, ! is_null($nextPacket) ) );
				
				$this->jsonCryptoWrite( $dwControllerConnexion ,$dwCryptoJsonChannel );
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			$dwControllerConnexion->response("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			$dwControllerConnexion->response("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function jsonCryptoWrite( DwControllerConnexion $dwControllerConnexion ,DwCryptoJsonChannel $dwCryptoJsonChannel ) : void {
		try {
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
			$dwSessionManager = $dwControllerConnexion->getDwSessionManager();
			$dwSessionIpKey = $dwControllerConnexion->getDwSessionIpKey();
			$dwSessionRequest = new DwSessionRequest( $dwSessionIpKey, "jsonCryptoWrite" );
			$dwSessionRequest->setObject( $dwCryptoJsonChannel );
			$dwSessionResponse = $dwSessionManager->jsonCryptoWrite( $dwSessionRequest );
				
			self::$sLog->trace( $dwSessionResponse );
			
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
			self::$sLog->trace(".");
			$this->valData->setValue( null );
			$dwSessionIpKey = $dwControllerConnexion->getDwSessionIpKey();
			parent::execute( $dwControllerConnexion );
			
			//$contentType="text/plain";
			//$dwControllerConnexion->headersAdd( "Content-type: $contentType; charset=utf-8" );
			
			$cryptedData=$this->valData->getValue();
			//$cryptedData = substr(base64_decode($raw64Content),5);
			
			self::$sLog->trace("[".$dwSessionIpKey->getIpKey()."] cryptedData:".strlen( $cryptedData ));
			if ( empty( $cryptedData ) ) {
				self::$sLog->error(". cryptedData:".strlen( $cryptedData ));
				$dwControllerConnexion->response("echec request 0.");
			} else {
				self::$sLog->trace("jsonCryptoSearch");
				$dwSessionManager = $dwControllerConnexion->getDwSessionManager();
				$dwSessionRequest = new DwSessionRequest( $dwSessionIpKey, "jsonCryptoSearch" );
				$dwSessionRequest->setObject( $cryptedData );
				$dwSessionResponse = $dwSessionManager->jsonCryptoSearch( $dwSessionRequest );
				$this->jsonCryptoSearch( $dwControllerConnexion, $dwSessionResponse );
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			$dwControllerConnexion->response("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			$dwControllerConnexion->response("error [".$this->getClassName()."]");
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

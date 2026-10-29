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

namespace bakuba\kernel\crypto;
//use bakuba\kernel\crypto\aDwSessionChannel as aDwSessionChannel;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use \darkphp\serializable\iDwSerializable as iDwSerializable;

use bakuba\kernel\session\DwSessionIpKey as DwSessionIpKey;

use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;
use bakuba\kernel\crypto\json\DwJsonEndpoint as DwJsonEndpoint;
//use bakuba\kernel\crypto\jsonShell\DwCryptoShell as DwCryptoShell;

use bakuba\kernel\services\http\DwHttpServiceRequest as DwHttpServiceRequest;

abstract class aDwSessionChannel extends \bakuba\kernel\session\DwSessionChannel implements iDwSessionChannel  {
	
	private $access = false;
	private $cpt = 0;
	private $cryptoSessions = array();
	
	/*
	public function __construct(
		\bakuba\kernel\session\DwSessionManager $dwSessionManager
		,string $channelKey
	) {
		parent::__construct( $dwSessionManager ,$channelKey );
	}
	//*/
	
	//public function initSession( DwHttpServiceRequest $dwHttpServiceRequest ,DwCallBack $dwCallBack );
	
	public function initMessage(
		DwCallBack $dwCallBack
		,\bakuba\kernel\protocole\http\DwHttpRequest $dwHttpRequest
		,DwJsonCrypto $jsonMessage
	) : void {
		$jsonStr=$jsonMessage->serialize();
		//$this->log->trace( $jsonStr );
		$dwJsonEndpoint = DwJsonEndpoint::fromJson( $jsonStr );
		$dwJsonEndpoint->{"port"} = $dwHttpRequest->getSrvPort()->getPort();
		$dwJsonEndpoint->{"ipaddress"} = $dwHttpRequest->getSrvIp()->getIpAddress();
		$dwJsonEndpoint->{"host"} = $dwHttpRequest->getRqtHost();
		$dwJsonEndpoint->{"proto"}
			= $dwHttpRequest->getRqtHttps() ? "https" : "http";
		
		$this->cryptoChannelSet(
			"message"
			,$dwJsonEndpoint
		);
		$dwCallBack->callback( $this );
	}
	
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize("access");
		$this->addtoSerialize("cpt");
		$this->addtoSerialize("cryptoSessions");
	}
	
	public function getCiphers() : array {
		return $this->channelDataGet("ciphers");
	}
	public function setCiphers( array $ciphers ) {
		$this->channelDataSet("ciphers", $ciphers);
	}
	public function getDigests() :array {
		return $this->channelDataGet("digests");
	}
	public function setDigests( array $digests ) {
		$this->channelDataSet("digests", $digests);
	}
	public function getCpt() : int {
		return $this->cpt;
	}
	/*************** CRYPTO CHANELL ******************/
	public function cryptoChannelGet( $attributeName ) : iDwSerializable {
		$cryptoSession = null;
		
		if ( ! isset($this->cryptoSessions[$attributeName]) )
			throw new Exception( "no cryptoSessions for $attributeName" );
		$cryptoSession = $this->cryptoSessions[$attributeName];
			
		return $cryptoSession;
	}
	public function cryptoChannelSet(
		$attributeName, iDwSerializable $attributeValue ) {
		$this->cryptoSessions[ $attributeName ] = $attributeValue;
	}
	public function getData( ) : string {
		return base64_decode( $this->channelDataGet("data") );
	}
	//public function read( iDwSerializable $jsonRequest ,DwCallBack $dwDoneCallBack ) : void;
	public function readed(
		DwCallBack $dwCallBack
		, DwJsonCrypto $dwJsonCryptoInitMessage
	) : void {
		$this->cryptoChannelSet( "nextMessage", $dwJsonCryptoInitMessage );
		$dwCallBack->callback( $this );
	}
	public function searchSession(
		DwHttpServiceRequest $dwHttpServiceRequest
		,DwCallBack $dwCallBack
	) : void {
		$dwSessionIpKey = $dwHttpServiceRequest->getDwSessionIpKey();
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		$requestData = $dwHttpRequest->getMergeValues();
		if ( isset( $requestData[ "data" ] ) ) {
			if ( $this->hasIpKey( $dwSessionIpKey->getIpKey() ) )
				$this->decryptTry(
					\preg_replace('~[\r\n\\\\]+~', '',$requestData[ "data" ] )
					,$dwCallBack
				);
			else
				$this->log->warn( "wrong ip" );
		} else {
			$this->log->trace( "no data" );
			$dwCallBack->callback( null );
		}
	}
	
	//private function decryptTry( $cryptedData ,DwCallBack $dwCallBack ) : void;
	
	public function decryptTryed(
		DwCallBack $dwCallBack
		,string $data64 = null
	) : void {
		$found = false;
		try {
			if ( ! is_null( $data64 ) ) {
				$data = base64_decode( $data64 );
				if ( ! is_null( $data ) ) {
					$jsonData = DwJsonCrypto::fromJson( $data );
					if ( is_object( $jsonData ) ) {
						$found = true;
						$this->read( $jsonData, $dwCallBack );
					} else
						throw new Exception(
							"echec fromJson :\r\n"
							."data:$data\r\n"
							."b64 :$data64\r\n"
							."json:$jsonMessage"
						);
				}
			}
		} catch(\Exception $exception_read) {
			$this->log->warn( "exception_read" );
			$this->log->throwable( $exception_read );
			//throw $exception_read;
		} catch(\Error $error_read) {
			$this->log->warn( "error_read" );
			$this->log->throwable( $error_read );
			//throw $error_read;
		}
		if ( ! $found ) $dwCallBack->callback( null );
	}
	
	public function getCryptoSession() : string {
		return base64_encode( serialize( $this->cryptoSessions ) );
	}
	public function setCryptoSession( string $cryptoSession ) : void {
		$this->cryptoSessions = unserialize( base64_decode( $cryptoSession ) );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

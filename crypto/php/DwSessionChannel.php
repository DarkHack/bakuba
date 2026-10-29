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

namespace bakuba\kernel\crypto\php;
//use bakuba\kernel\crypto\php\DwSessionChannel as DwSessionChannel;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\objects\serialize\IDwSerializable as IDwSerializable;

use bakuba\kernel\crypto\jsonPhp\DwCryptoPhp as DwCryptoPhp;

class DwSessionChannel extends \bakuba\kernel\session\DwSessionChannel {
	
	public function initSession( DwHttpServiceRequest $dwHttpServiceRequest ,DwCallBack $dwCallBack ) {
		$this->log->trace( "." );
		$this->addSessionIp( $dwHttpServiceRequest->getDwSessionIpKey() );
		$phpCrypto = \bakuba\kernel\crypto\sDwCrypto::phpCrypto();
		
		//$phpCrypto->listCiphers( DwCallBack::emptyCB() );
		//$phpCrypto->listDigests( DwCallBack::emptyCB() );
		$phpCrypto->initMessage(
			new DwCallBack(
				array( &$this ,"initMessage" )
				,$dwCallBack
				,$dwHttpServiceRequest->getDwHttpRequest()
			)
			,array("aes-128-cbc")//,"aes-128-ecb")
			,array( "md5" )//,"sha1" )
		);
	}
	public function cryptoChannelResponse(
		string $data
		,DwCallBack $dwCallBack = null
		,$isFragment = false
	) : ?string {
		
		$dwJsonCryptoNextMessage = $this->cryptoChannelGet( "nextMessage" );
		$this->cryptoChannelSet("message", $dwJsonCryptoNextMessage );
		$dwJsonCryptoNextMessage->{"data"}=$data;

		$dwJsonCryptoResponse = $this->cryptoChannelGet( "response" );
		$dwJsonCryptoResponse->{"data"}=base64_encode(json_encode( $dwJsonCryptoNextMessage ));
		
		
		$phpCrypto = \bakuba\kernel\crypto\sDwCrypto::phpCrypto();
		$phpCrypto->encrypt(
			$dwCallBack
			,$responseMessage
		);
	}
	public function read(
		\bakuba\kernel\crypto\json\DwJsonCrypto $dwJsonCrypto
		,DwCallBack $dwDoneCallBack
	) : void {
		$data = $dwJsonCrypto->{"data"};
		$this->log->debug( $data );
		$this->channelDataSet( "data", $data);
		
		if ( is_null( $this->channelDataGet( "ciphers" ) ) ) 
			$this->channelDataSet(
				"ciphers"
				,\bakuba\kernel\crypto\aDwCrypto::commonCiphers( $dwJsonCrypto->{"ciphers"} )
			);
		if ( is_null( $this->channelDataGet( "digests" ) ) )
			$this->channelDataSet(
				"digests"
				,\bakuba\kernel\crypto\aDwCrypto::commonDigests( $dwJsonCrypto->{"digests"} )
			);
		$this->cryptoChannelSet( "response", $dwJsonCrypto );
		
		$phpCrypto = \bakuba\kernel\crypto\sDwCrypto::phpCrypto();
		$phpCrypto->initMessage(
			new DwCallBack(
				array( &$this, "readed" )
				,$dwDoneCallBack
			)
			,$this->getCiphers()
			,$this->getDigests()
		);
	}
	private function decryptTry(
		string $cryptedData
		,DwCallBack $dwCallBack
	) : void {
		$jsonMessage =  $this->cryptoChannelGet( "message" );
		$this->log->debug("jsonMessage:".$jsonMessage );
		$jsonMessage->{"data"}=$cryptedData;
		
		$phpCrypto = \bakuba\kernel\crypto\sDwCrypto::phpCrypto();
		$phpCrypto->decrypt(
			new DwCallBack(
				array( &$this , "decryptTryed" )
				,$dwCallBack
			)
			,$jsonMessage
		);
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

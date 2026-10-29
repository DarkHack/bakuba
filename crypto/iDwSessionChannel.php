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
//use bakuba\kernel\crypto\iDwSessionChannel as iDwSessionChannel;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use \darkphp\serializable\iDwSerializable as iDwSerializable;

use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;
//use bakuba\kernel\crypto\json\DwJsonEndpoint as DwJsonEndpoint;

use bakuba\kernel\services\http\DwHttpServiceRequest as DwHttpServiceRequest;

interface iDwSessionChannel extends \bakuba\kernel\session\iDwSessionChannel  {
	public function initSession(
		DwHttpServiceRequest $dwHttpServiceRequest
		,DwCallBack $dwCallBack
	) : void;
	public function initMessage(
		DwCallBack $dwCallBack
		,\bakuba\kernel\protocole\http\DwHttpRequest $dwHttpRequest
		,DwJsonCrypto $jsonMessage
	) : void;
	
	public function DwSerializableInit();
	
	public function getCiphers() : array;
	public function setCiphers( array $ciphers );
	public function getDigests() :array;
	public function setDigests( array $digests );
	public function getCpt() :int;
	/*************** CRYPTO CHANELL ******************/
	public function cryptoChannelGet( $attributeName ) : iDwSerializable;
	public function cryptoChannelSet( $attributeName, iDwSerializable $attributeValue );
	public function cryptoChannelResponse(
		string $data
		,DwCallBack $dwCallBack = null
		,$isFragment = false
	) : void;
	//private function response( $data, DwCallBack $dwCallBack = null ) :void;
	
	public function getCryptoSession() : string;
	public function setCryptoSession( string $cryptoSession ) : void;
	
	public function read( DwJsonCrypto $jsonRequest ,DwCallBack $dwDoneCallBack ) : void;
	public function getData( ) : string;
	public function readed( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCryptoInitMessage ) : void;
	
	public function searchSession( DwHttpServiceRequest $dwHttpServiceRequest ,DwCallBack $dwCallBack ) : void;
	/*
	private function decryptTry(
		string $cryptedData
		,DwCallBack $dwCallBack
	) : void;
	//*/
	public function decryptTryed(
		DwCallBack $dwCallBack
		,string $data64 = null
	) : void;
}

?>

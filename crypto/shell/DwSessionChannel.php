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

namespace bakuba\kernel\crypto\shell;
//use bakuba\kernel\crypto\shell\DwSessionChannel as DwSessionChannel;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;
//use bakuba\kernel\crypto\json\DwJsonEndpoint as DwJsonEndpoint;

class DwSessionChannel extends \bakuba\kernel\crypto\aDwSessionChannel {
	
	public function initSession( 
		\bakuba\kernel\services\http\DwHttpServiceRequest $dwHttpServiceRequest
		,DwCallBack $dwCallBack
	) : void {
		$this->log->trace( "." );
		$this->addSessionIp( $dwHttpServiceRequest->getDwSessionIpKey() );
		$agentCrypto = \bakuba\kernel\crypto\sDwCrypto::getAgentCrypto();
		
		//$agentCrypto->listCiphers( DwCallBack::emptyCB() );
		//$agentCrypto->listDigests( DwCallBack::emptyCB() );
		$agentCrypto->initMessage(
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
	) : void {
		$response = null;
		$maxPacquetSize = 10000;
		$this->log->trace("data:".strlen($data)."\r\n".$data );
		if ( ! $isFragment && strlen( $data ) > $maxPacquetSize) {
			$this->log->error("have to fragment :".strlen( $data )." o" );
			$arrData = str_split($data, $maxPacquetSize);
			$this->channelDataSet("totalData" ,$arrData);
			$this->channelDataSet("indexData" ,0);
			$this->channelDataSet("nbData" ,count($arrData) );
			//$data = $this->nextPacket();
			$cmd="\$cmdPrintf \"".$this->nextPacket()."\" | shAgentFragment\n";
			$data = base64_encode( $cmd );
		}
		$this->response( $data ,$dwCallBack );
	}
	
	private function response( $data, DwCallBack $dwCallBack = null ) : void {
		$response=null;
		$jsonNextMessage = $this->cryptoChannelGet( "nextMessage" );
		$this->cryptoChannelSet("message", $jsonNextMessage );
		$jsonNextMessage->{"data"}=$data;
		$responseMessage = $this->cryptoChannelGet( "response" );
		$responseMessage->{"data"}=base64_encode(
			json_encode( $jsonNextMessage )
		);
		$this->log->trace(
			"responseMessage:".strlen(json_encode($responseMessage))
		);
		$agentCrypto = \bakuba\kernel\crypto\sDwCrypto::getAgentCrypto();
		
		$agentCrypto->encrypt(
			$dwCallBack
			,$responseMessage
		);
	}
	
	public function read(
		DwJsonCrypto $jsonRequest
		,DwCallBack $dwDoneCallBack
	) : void {
		$data = $jsonRequest->{"data"};
		$this->log->debug( $data );
		$this->channelDataSet( "data" ,$data );
		
		if ( $this->channelDataGet( "ciphers" ) == null ) 
			$this->setCiphers( explode( ':' ,$jsonRequest->{"ciphers"} ) );
		if ( $this->channelDataGet( "digests" ) == null )
			$this->setDigests( explode( ':' ,$jsonRequest->{"digests"} ) );
		$this->cryptoChannelSet( "response", $jsonRequest );
		
		$agentCrypto = \bakuba\kernel\crypto\sDwCrypto::getAgentCrypto();
		$agentCrypto->initMessage(
			new DwCallBack(
				array( &$this, "readed" )
				,$dwDoneCallBack
			)
			,$this->getCiphers()
			,$this->getDigests()
		);
	}
	protected function decryptTry(
		string $cryptedData
		,DwCallBack $dwCallBack
	) : void {
		$jsonMessage =  $this->cryptoChannelGet( "message" );
		$this->log->debug("jsonMessage:".$jsonMessage );
		$jsonMessage->{"data"}=$cryptedData;
		
		$agentCrypto = \bakuba\kernel\crypto\sDwCrypto::getAgentCrypto();
		$agentCrypto->decrypt(
			new DwCallBack(
				array( &$this , "decryptTryed" )
				,$dwCallBack
			)
			,$jsonMessage
		);
	}
	
	public function nextPacket( ) {
		$packet = null;
		
		$arrData = $this->channelDataGet( "totalData" );
		if ( ! is_null($arrData) ) {
			$index = $this->channelDataGet( "indexData" );
			$index++;$this->channelDataSet( "indexData", $index );
			$nbData = $this->channelDataGet( "nbData" );
			if ( $index <= $nbData ) {
				$jsonFragment = json_decode( "{}" );
				$jsonFragment->{"index"} = $index;
				$jsonFragment->{"data"} = $arrData[ $index - 1];
				$jsonFragment->{"next"} = count($arrData) > $index ?true:false;
				/*
				$this->log->debug(
					"i:".$index." data:".count($arrData)
				);
				//*/
				$packet=base64_encode( json_encode($jsonFragment) );
			} else {
				$this->channelDataSet( "totalData", null );
				$this->channelDataSet( "indexData", null );
				$this->channelDataSet( "nbData", null );
			}
		}
		return $packet;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

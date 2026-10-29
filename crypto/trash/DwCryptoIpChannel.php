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
//use bakuba\kernel\dwCrypto\DwCryptoIpChannel as DwCryptoIpChannel;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;

use darkphp\serializable\aDwSerializable as DwSerializable;
use bakuba\kernel\application\session\cryptoShell\DwJsonCrypto as DwJsonCrypto;

use bakuba\kernel\shell\DwShellCrypt as DwShellCrypt;

class DwCryptoIpChannel extends aDwSerializable {	
	static private $sLog = null;
	
	private $access = false;
	private $cpt = 0;
	
	private $channelData = array();
	private $cryptoSessions = array();
	private $haveToWrite = false;
	
	public function __construct( $ipSrv, $ipClt, $ciphers = null ) {
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::__construct();
			
			$this->setIpSrv( $ipSrv );
			$this->setIpClt( $ipClt );
			$this->setIpKey( "$ipSrv:$ipClt" );
			$this->setCiphers( $ciphers );
			$this->cryptoChannelSet("message", DwCryptoShellChannel::initMessage( $ciphers ) );
			
			
			self::$sLog->trace(".");
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function DwSerializableInit() {
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::DwSerializableInit();
			
			$this->addtoSerialize("access");
			$this->addtoSerialize("cpt");
			$this->addtoSerialize("channelData");
			$this->addtoSerialize("cryptoSessions");
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	
	public function getChannelKey() {
		return $this->channelDataGet("channelKey");
	}
	public function setChannelKey( $channelKey ) {
		$this->channelDataSet("channelKey", $channelKey);
	}
	public function getIpSrv() {
		return $this->channelDataGet("ipSrv");
	}
	private function setIpSrv( $ipSrv ) {
		$this->channelDataSet("ipSrv", $ipSrv);
	}
	public function getIpClt() {
		return $this->channelDataGet("ipClt");
	}
	private function setIpClt( $ipClt ) {
		$this->channelDataSet("ipClt", $ipClt);
	}
	public function getIpKey() {
		return $this->channelDataGet("ipKey");
	}
	public function setIpKey( $ipKey ) {
		$this->channelDataSet("ipKey", $ipKey);
	}
	public function getCiphers() {
		return $this->channelDataGet("ciphers");
	}
	public function setCiphers( $ciphers ) {
		$this->channelDataSet("ciphers", $ciphers);
	}
	public function getCpt() {
		return $this->cpt;
	}/*
	public function setInitNode( $initNode ) {
		try {
			self::$sLog->trace(".");
			
			if ( $initNode != null ) {
				// [ 'ipSrv', 'ipClt' , 'ctrlNodeKey', 'initCrypto' ]
				$initData = json_decode( base64_decode( $initNode ) );
				$this->setIpSrv( $initData->ipSrv );
				$this->setIpClt( $initData->ipClt );
				$this->ctrlNodeKey = $initData->ctrlNodeKey;
				
				$this->cryptoSessionKey = DwCryptoChannel::initB( $initData->initCrypto );
			} else  {
				$this->cryptoSessionKey = null;
				$this->ipSrv = "xxx.xxx.xxx.xxx";
				$this->ipClt = "xxx.xxx.xxx.xxx";
				$this->ctrlNodeKey = null;
			}
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
	}
	*/
	/*************** CRYPTO CHANELL ******************/
	public function channelDataGet( $attributeName ) {
		if (isset($this->channelData[$attributeName]))
			return $this->channelData[$attributeName];
		else
			return null;
	}
	public function channelDataSet( $attributeName, $attributeValue ) {
		$this->channelData[$attributeName] = $attributeValue;
		$this->haveToWrite = false;
	}
	public function channelDataRemove( $attributeName ) {
		unset($this->channelData[$attributeName]);
	}
	
	public function cryptoChannelGet( $attributeName ) : IDwSerializable {
		$cryptoSession = null;
		try {
			if ( ! isset($this->cryptoSessions[$attributeName]) )
				throw new Exception("no cryptoSessions for $attributeName");
			$cryptoSession = $this->cryptoSessions[$attributeName];
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $cryptoSession;
	}
	public function cryptoChannelSet( $attributeName, IDwSerializable $attributeValue ) {
		$this->cryptoSessions[$attributeName] = $attributeValue;
		$this->haveToWrite = false;
	}
	public function cryptoChannelResponse( $data, $isFragment = false ) {
		$response = null;
		$maxPacquetSize = 10000;
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			self::$sLog->trace("data:".strlen($data)."\r\n".$data );
			if ( ! $isFragment && strlen( $data ) > $maxPacquetSize) {
				self::$sLog->error("have to fragment :".strlen( $data )." o" );
				$arrData = str_split($data, $maxPacquetSize);
				$this->channelData["totalData"]=$arrData;
				$this->channelData["indexData"]=0;
				$this->channelData["nbData"]=count($arrData);
				//$data = $this->nextPacket();
				$cmd="\$cmdPrintf \"".$this->nextPacket()."\" | shAgentFragment\n";
				$data = base64_encode($cmd);
			}
			$response = $this->response( $data );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $response;
	}
	private function response( $data ) {
		$response=null;
		try {
			$jsonNextMessage = $this->cryptoChannelGet( "nextMessage" );
			$this->cryptoChannelSet("message", $jsonNextMessage );
			$jsonNextMessage->{"data"}=$data;
			$responseMessage = $this->cryptoChannelGet( "response" );
			$responseMessage->{"data"}=base64_encode(json_encode( $jsonNextMessage ));
			self::$sLog->trace("responseMessage:".strlen(json_encode($responseMessage)) );
			$response = dwShellCrypt::encrypt( $responseMessage );
			self::$sLog->trace("response:".strlen($response) );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $response;
	}
	
	public function read( IDwSerializable $jsonRequest ) {
		try {
			if ( $this->channelDataGet( "ciphers" ) == null ) 
				$this->channelDataSet( "ciphers", $jsonRequest->{"ciphers"} );
			
			$this->channelDataSet( "data", $jsonRequest->{"data"});
			$this->cryptoChannelSet( "nextMessage", DwCryptoShellChannel::initMessage( $this->channelDataGet( "ciphers" ) ) );
			$this->cryptoChannelSet( "response", $jsonRequest );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return false;
	}
	public function decryptTry( $cryptedData ) {
		$found = null;
		try {
			$jsonMessage =  $this->cryptoChannelGet( "message" );
			self::$sLog->debug("jsonMessage:".print_r($jsonMessage,true));
			$jsonMessage->{"data"}=$cryptedData;
			try {
				$data64 = DwShellCrypt::decrypt($jsonMessage);
				
				$jsonData = DwJsonCrypto::fromJson( base64_decode( $data64 ) );
				if (is_object($jsonData)) {
					self::$sLog->debug( $jsonData );
					$this->read( $jsonData );
					$found = $jsonData;
				}
			} catch(Exception $exception_read) {
				self::$sLog->error("[".$this->getClassName()."/".$this->getInstanceKey()."] exception_read");
				//throw $exception_read;
			} catch(Error $error_read) {
				self::$sLog->error("[".$this->getClassName()."/".$this->getInstanceKey()."] error_read");
				//throw $error_read;
			}
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $found;
	}
	public function getData( ) {
		return base64_decode( $this->channelData["data"] );
	}
	public function nextPacket( ) {
		$packet = null;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
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
					
					self::$sLog->debug("[".$index."/".count($arrData)."]");
					$packet=base64_encode( json_encode($jsonFragment) );
				} else {
					$this->channelDataSet( "totalData", null );
					$this->channelDataSet( "indexData", null );
					$this->channelDataSet( "nbData", null );
				}
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $packet;
	}
	public function getCryptoSession() {
		return base64_encode( serialize($this->cryptoSessions) );
	}
	public function setCryptoSession($cryptoSession) {
		$this->cryptoSessions = unserialize( base64_decode( $cryptoSession ) );
	}
	public function getClassName( $fqcn=false ) {
		$className = __CLASS__;
		try {
			if ( is_null( self::$sLog ) ) self::$sLog = DwLogger::getLogger( $className );
			self::$sLog->trace(".");
			$className = $fqcn ? parent::getClassName( $fqcn ).self::CLASS_SEPARATOR.$className : $className;
		} catch(Exception $exception) {
			self::$sLog->error("exception [$className]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [$className]");
			throw $error;
		}
		return $className;
	}
	public static function getStaticClassName( $fqcn=false ) {
		$className = __CLASS__;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( $className );
			self::$sLog->trace(".");
			$className= $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.$className : $className;
		
		} catch(Exception $exception) {
			self::$sLog->error("exception [$className]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [$className]");
			throw $error;
		}
		return $className;
	}
}

?>

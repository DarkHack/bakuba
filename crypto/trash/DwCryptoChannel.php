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

use \Exception as Exception;
use \Error as Error;
use darkphp\logger\DwLogger as DwLogger;

//use bakuba\kernel\shell\DwShellCrypt as DwShellCrypt;

// use bakuba\kernel\dwCrypto\DwCryptoChannel as DwCryptoChannel;
class DwCryptoChannel {
	static private $cryptoSession = array();
	static private $ciphers = null;
	
	public static function initA( $ciphersClt ) {
		if ( self::$ciphers == null ) self::$ciphers = openssl_get_cipher_methods();
		
		$size = 3;
		do $keySession = \bakuba\kernel\randomizer\DwRandomizer::randomize( $size );
		while( isset( self::$cryptoSession[ $keySession ] ) && ( $size++ || true ) );
		
		self::$cryptoSession[ $keySession ] = array();
		self::$cryptoSession[ $keySession ][ "keySession" ] = $keySession;
		self::$cryptoSession[ $keySession ][ "ciphers" ] = DwCypherManager::intersectPreferedCiphers( self::$ciphers, $ciphersClt); 
		self::nextMessage( $keySession );
		
		$initData = array();
		$initData[ "keySession" ] = self::$cryptoSession[ $keySession ][ "keySession" ];
		$initData[ "firstMessage" ] = self::$cryptoSession[ $keySession ][ "nextMessage" ];
		$initData[ "ciphers" ] = self::$cryptoSession[ $keySession ][ "ciphers" ];
		return $initData;
	}
	
	public static function initB( $jsonCryptoSession ) {
		if ( self::$ciphers == null ) self::$ciphers = openssl_get_cipher_methods();
		
		if ( is_object( $jsonCryptoSession ) )
			$jsonObjCryptSession = $jsonCryptoSession;
		else
			$jsonObjCryptSession = json_decode( $jsonCryptoSession );
		
		$size=3;
		do $keySession = \bakuba\kernel\randomizer\DwRandomizer::randomize( $size );
		while( isset( self::$cryptoSession[ $keySession ] ) && ( $size++ || true ) );
		
		self::$cryptoSession[ $keySession ] = array();
		self::$cryptoSession[ $keySession ][ "keySession" ] = $keySession;
		$nextMessage = array();
		$nextMessage[ "cipher" ] = $jsonObjCryptSession->firstMessage->cipher;
		$nextMessage[ "iv_size" ] = $jsonObjCryptSession->firstMessage->iv_size;
		$nextMessage[ "iv" ] = $jsonObjCryptSession->firstMessage->iv;
		$nextMessage[ "key_size" ] = $jsonObjCryptSession->firstMessage->key_size;
		$nextMessage[ "key" ] = $jsonObjCryptSession->firstMessage->key;
			
		self::$cryptoSession[ $keySession ][ "nextMessage" ] = $nextMessage; 
		self::$cryptoSession[ $keySession ][ "ciphers" ] = DwCypherManager::intersectPreferedCiphers( self::$ciphers, $jsonObjCryptSession->ciphers ); 
		
		return $keySession;
	}
	
	private static function nextMessage( $keySession ) {
		if ( self::$ciphers == null ) self::$ciphers = openssl_get_cipher_methods();
		if ( isset ( self::$cryptoSession[ $keySession ] ) ) {
			$cipher = DwCypherManager::selectAnCipher( self::$ciphers, self::$cryptoSession[ $keySession ][ "ciphers" ] );
			if ( empty( $cipher ) )
				throw new Exception( "nextMessage no cipher.\n".print_r(
					array( "srv" => self::$ciphers
					, "clt" => self::$cryptoSession[ $keySession ][ "ciphers" ] )
				,true ) );
			$nextMessage = array();
			$nextMessage[ "cipher" ] = $cipher;
			$nextMessage[ "iv_size" ] = openssl_cipher_iv_length( $cipher );
			$nextMessage[ "iv" ] = base64_encode(openssl_random_pseudo_bytes( $nextMessage[ "iv_size" ] ));
			$nextMessage[ "key_size" ] = $nextMessage[ "iv_size" ] * 4;
			$nextMessage[ "key" ] = base64_encode( openssl_random_pseudo_bytes( $nextMessage[ "key_size" ] ));
			
			self::$cryptoSession[ $keySession ][ "nextMessage" ] = $nextMessage;
		}
	}
	
	public static function decode( $keySession, $message64Crypted ) {
		$nextMessage = self::$cryptoSession[ $keySession ][ "nextMessage" ];
		if ( !is_array($nextMessage) || ! isset( $nextMessage[ "cipher" ] ) )
			throw new Exception(" no next nextMessage in keySession :$keySession ");
		$messageCrypted = base64_decode($message64Crypted);
		if ( strlen( $messageCrypted ) <= 0 )
			throw new Exception(" no 64 message in :$message64Crypted ");
			
		$message = DwCrypto::decoder( $messageCrypted, $nextMessage[ "cipher" ],  base64_decode($nextMessage[ "key" ]), base64_decode($nextMessage[ "iv" ]) );
		
		//die ( "message :$message / $messageCrypted" );
		$dataGram = json_decode( $message );
		if ( ! is_object($dataGram) || ! isset($dataGram->cipher) )
			throw new Exception(" no next cipher in dataGram :$dataGram / $message / $message64Crypted ");
		else {
			$nextMessage = array();
			$nextMessage[ "cipher" ] = $dataGram->cipher;
			$nextMessage[ "iv_size" ] = $dataGram->iv_size;
			$nextMessage[ "iv" ] = $dataGram->iv;
			$nextMessage[ "key_size" ] = $dataGram->key_size;
			$nextMessage[ "key" ] = $dataGram->key;
			self::$cryptoSession[ $keySession ][ "nextMessage" ] = $nextMessage;
		}
		
		return base64_decode( $dataGram->data );
	}
	public static function encode( $keySession, $message ) {
		$thisMessage = self::$cryptoSession[ $keySession ][ "nextMessage" ];
		self::nextMessage( $keySession );
		$dataGram = array();
		$dataGram[ "cipher" ] = self::$cryptoSession[ $keySession ][ "nextMessage" ]["cipher"];
		$dataGram[ "iv_size" ] = self::$cryptoSession[ $keySession ][ "nextMessage" ][ "iv_size" ];
		$dataGram[ "iv" ] = self::$cryptoSession[ $keySession ][ "nextMessage" ][ "iv" ];
		$dataGram[ "key_size" ] = self::$cryptoSession[ $keySession ][ "nextMessage" ][ "key_size" ];
		$dataGram[ "key" ] = self::$cryptoSession[ $keySession ][ "nextMessage" ][ "key" ];
		$dataGram[ "data" ] = base64_encode( $message );
		
		return base64_encode( DwCrypto::encoder( json_encode( $dataGram ), $thisMessage[ "cipher" ], base64_decode($thisMessage[ "key" ]), base64_decode($thisMessage[ "iv" ]) ) );
	}
	
	/*************** CRYPTO CHANELL BY IP ******************/
	private $ipChannels = array();
	private $ipKeys = array();
	
	public function createCryptoIpChannel( $ipSrv, $ipClt, $jsonb64Ciphers = null ) {
		$dwCryptoIpChannel = null;
		try {
			$ciphers = null;
			if ( ! $jsonb64Ciphers == null )
				$ciphers = json_decode( base64_decode( $jsonb64Ciphers ) );
			
			$dwCryptoIpChannel = new DwCryptoIpChannel( $ipSrv, $ipClt, $ciphers );
			$this->setCryptoIpChannel( $dwCryptoIpChannel );
			
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $dwCryptoIpChannel;
	}
	public function unsetCryptoIpChannel( DwCryptoIpChannel $dwCryptoIpChannel ) {
		try {
			$channelKey = $dwCryptoIpChannel->getChannelKey();
			$ipKey = $dwCryptoIpChannel->getIpKey();
			unset($this->ipChannels[ $channelKey ]);
			$idToDelete = null;
			foreach( $this->ipKeys[$ipKey] as $id => $anChannelKey ) {
				if ( $channelKey == $anChannelKey ) {
					$idToDelete = $id;
					break;
				}
			}
			unset($this->ipKeys[$ipKey][$idToDelete]);
			
			if (empty($this->ipKeys[$ipKey]))
				unset($this->ipKeys[$ipKey]);
			
			//$dwCryptoIpChannel = $this->getCryptoIpChannel( $channelKey );	
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}	
	}
	
	public function setCryptoIpChannel( DwCryptoIpChannel $dwCryptoIpChannel ) {
		try {
			$ipKey = $dwCryptoIpChannel->getIpKey();
			
			if (!isset( $this->ipKeys[$ipKey] ) ) $this->ipKeys[$ipKey] = array();
			
			$channelKey = $dwCryptoIpChannel->getChannelKey( );
			if ( \is_null( $channelKey ) ) {
				$size=3;
				while ( ( $channelKey == null || isset( $this->ipChannels[ $channelKey ] ) ) && ( $size++ || true ) )
					$channelKey = \bakuba\kernel\randomizer\DwRandomizer::randomize( $size );
				
				$dwCryptoIpChannel->setChannelKey($channelKey);
				$channelKey = $dwCryptoIpChannel->getChannelKey();
			}
			
			$this->ipChannels[ $channelKey ] = $dwCryptoIpChannel;
			
			if ( array_search($channelKey, $this->ipKeys[$ipKey]) === false )
				$this->ipKeys[$ipKey][] = $channelKey;
			
			//$dwCryptoIpChannel = $this->getCryptoIpChannel( $channelKey );	
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}	
	}
	public function getCryptoIpChannel( $channelKey ) {
		try {
			if ( isset( $this->ipChannels[ $channelKey ] ) )
				return $this->ipChannels[ $channelKey ];
			else
				throw new Exception("no CryptoIpChannel with key:$channelKey");
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return null;
	}
	public function getCryptoIpChannels() {
		try {
			return $this->ipChannels;
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return null;
	}
	// DwPreNode::cryptoChannelSearchByTry($ipSrv, $ipClt, $cryptedData)
	public function cryptoChannelSearchByTry($ipSrv, $ipClt, $cryptedData) {
		$found = null;
		try {
			$ipKey = "$ipSrv:$ipClt";
			$this->log->trace("search:$ipKey");
			foreach($this->ipKeys[$ipKey] as $channelKey) {
				$this->log->trace("try:$channelKey");
				$dwCryptoIpChannel = $this->getCryptoIpChannel( $channelKey );

				$jsonData = $dwCryptoIpChannel->decryptTry($cryptedData);
				if ( ! empty($jsonData) ) {
					$found = $dwCryptoIpChannel;//array("dwCryptoIpChannel" => $dwCryptoIpChannel, "jsonData" => $jsonData);
					break;
				}
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $found;
	}
	
	private $log;
	public function __construct( ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->info(".");
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}

	/*************** CRYPTO CHANELL ******************/
	/*
	private $sessions = array();
	public function init() {
		try {
			$this->log->trace( ". new channel" );
			do {
				$sessionKey = DwShellCrypt::randomPass(15);
			} while (isset($this->sessions[$sessionKey]));
			$this->sessions[$sessionKey] = array();
			
			$this->set(
				$sessionKey
			);
			return $sessionKey;//$this->get( $sessionKey,"message");
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	public function get( $sessionKey, $attributeName ) {
		if ( isset($this->sessions[$sessionKey][$attributeName]) )
			return $this->sessions[$sessionKey][$attributeName];
		else
			return null;
	}
	public function set( $sessionKey, $attributeName, $attributeValue ) {
		$this->sessions[$sessionKey][$attributeName] = $attributeValue;
	}
	public function response( $sessionKey, $data ) {
		$response=null;
		try {
			$jsonNextMessage=$this->get( $sessionKey, "nextMessage" );
			$this->set( $sessionKey, "message", $jsonNextMessage );
			$jsonNextMessage->{"data"}=$data;
			$responseMessage = $this->get( $sessionKey, "response" );
			$responseMessage->{"data"}=base64_encode(json_encode( $jsonNextMessage ));
			$this->log->trace("responseMessage:".strlen(json_encode($responseMessage)) );
			$response = dwShellCrypt::encrypt( $responseMessage );
			$this->log->trace("response:".strlen($response) );
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return $response;
	}
	*/
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

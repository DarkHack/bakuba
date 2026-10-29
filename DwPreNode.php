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

namespace bakuba\kernel;

use \Exception as Exception;
use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\dwController\abstracts\ACtrlSession as ACtrlSession;

use bakuba\kernel\dwCrypto\DwCryptoChannel as DwCryptoChannel;

class DwPreNode {	
	static private $sLog = null;
	
	private static $preNodes = array();
	private static $ipKeys = array();
	public static function createPreNodeFromInitNode( $initNode ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			if ( $initNode != null ) {
				// [ 'ipSrv', 'ipClt' , 'ctrlNodeKey', 'initCrypto' ]
				$initData = json_decode( base64_decode( $initNode ) );
				$ipSrv = $initData->ipSrv;
				$ipClt = $initData->ipClt;	
			} else  {
				$ipSrv = "xxx.xxx.xxx.xxx";
				$ipClt = "xxx.xxx.xxx.xxx";
			}
			return self::createPreNode( $ipSrv, $ipClt );
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	public static function createPreNode( $ipSrv, $ipClt, $jsonb64Ciphers = null, $masterPass = null ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			//self::$sLog->trace("'$ipSrv', '$ipClt'");
			$ipKey = "$ipSrv:$ipClt";
			if (!isset( self::$ipKeys[$ipKey] ) ) self::$ipKeys[$ipKey] = array();
			
			$preNodeKey = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( self::$preNodes );
			self::$ipKeys[$ipKey][] = $preNodeKey;
			$ciphers = null;
			if ( ! $jsonb64Ciphers == null )
				$ciphers = json_decode( base64_decode( $jsonb64Ciphers ) );
			self::$preNodes[ $preNodeKey ] = new self( $preNodeKey, $ipSrv, $ipClt, $ciphers, $masterPass );
			
			return self::getPreNode( $preNodeKey );
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	public static function getPreNode( $preNodeKey ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			if ( isset( self::$preNodes[ $preNodeKey ] ) )
				return self::$preNodes[ $preNodeKey ];
			else
				throw new Exception("no preNode with key:$preNodeKey");
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	public static function getPreNodes() {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			return self::$preNodes;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	
	private $preNodeKey;
	private $ipSrv;
	private $ipClt;
	private $ipKey;
	private $masterPass;
	private $ciphers;
	private $access = false;
	private $cpt = 0;
	public function getPreNodeKey() {
		return $this->preNodeKey;
	}
	private function setPreNodeKey( $preNodeKey ) {
		$this->preNodeKey=$preNodeKey;
	}
	public function getIpSrv() {
		return $this->ipSrv;
	}
	private function setIpSrv( $ipSrv ) {
		$this->ipSrv=$ipSrv;
	}
	public function getIpClt() {
		return $this->ipClt;
	}
	private function setIpClt( $ipClt ) {
		$this->ipClt=$ipClt;
	}
	public function getIpKey() {
		return $this->ipKey;
	}
	public function getMasterPass() {
		return $this->masterPass;
	}
	public function getCiphers() {
		return $this->ciphers;
	}
	public function setCiphers( $ciphers ) {
		$this->ciphers=$ciphers;
	}
	public function getCpt() {
		return $this->cpt;
	}
	public function getAccess() {
		try {
			$this->log->trace("[ preNodeKey : ".$this->getPreNodeKey()."]");
			$this->cpt++;
			//if ( $this->cpt > 1 ) $this->openAccess();
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $this->access;			
	}
	public function haveAccess() {
		try {
			$this->log->trace("[ preNodeKey : ".$this->getPreNodeKey()."]");
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $this->access;			
	}
	public function openAccess() {
		try {
			$this->log->trace("[ preNodeKey : ".$this->getPreNodeKey()."]");
			$this->access = true;	
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function setInitNode( $initNode ) {
		try {
			$this->log->trace(".");
			
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
			$this->log->error($err);
			throw $err;
		}
	}
	private $dwCryptoChannel = null;
	private function __construct( $preNodeKey, $ipSrv, $ipClt, $ciphers = null, $masterPass = null ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->trace(".");
			$this->setPreNodeKey( $preNodeKey );
			$this->setIpSrv( $ipSrv );
			$this->setIpClt( $ipClt );
			$this->ipKey = "$ipSrv:$ipClt";
			$this->masterPass = $masterPass;
			
			$this->setCiphers( $ciphers );
			$this->dwCryptoChannel = new DwCryptoChannel( $ipSrv, $ipClt, $ciphers  );
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function getStatus( $response = null) {
		try {
			$this->log->trace(".");
			//$response = parent::getStatus($response);
			if ( $response == null )
				$response = array();
			
			$response[ "preNodeKey" ] = $this->preNodeKey;
			$response[ "ipSrv" ] = $this->ipSrv;
			$response[ "ipClt" ] = $this->ipClt;
			$response[ "ciphers" ] = $this->ciphers;
			$response[ "access" ] = $this->access;
			$response[ "cpt" ] = $this->cpt;
			
			return $response;
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	/*************** CRYPTO CHANELL ******************/
	private $cryptoSessions = array();
	public function cryptoChannelInit() {
		try {
			return $this->dwCryptoChannel->init();
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	public function cryptoChannelTry( $cryptedData ) {
		$sessionKey = false;
		try {
			$sessionKey = $this->dwCryptoChannel->decryptTry( $cryptedData );
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return $sessionKey;
	}
	public function cryptoChannelGet( $sessionKey, $attributeName ) {
		return $this->dwCryptoChannel->get( $sessionKey, $attributeName );
	}
	public function cryptoChannelSet( $sessionKey, $attributeName, $attributeValue ) {
		$this->dwCryptoChannel->set( $sessionKey, $attributeName, $attributeValue );
	}
	public function cryptoChannelResponse( $sessionKey, $data ) {
		return $this->dwCryptoChannel->response( $sessionKey, $data );
	}
	// DwPreNode::cryptoChannelSearchByTry($ipSrv, $ipClt, $cryptedData)
	public static function cryptoChannelSearchByTry($ipSrv, $ipClt, $cryptedData) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			$ipKey = "$ipSrv:$ipClt";
			foreach(self::$ipKeys[$ipKey] as $preNodeKey) {
				$dwPreNode = self::getPreNode( $preNodeKey );

				$sessionKey = $dwPreNode->cryptoChannelTry($cryptedData);
				if ( ! empty($sessionKey) ) {
					$found = array();
					$found["sessionKey"]=$sessionKey;
					$found["dwPreNode"]=$dwPreNode;
					return $found;
				}
			}
			return false;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
}

?>

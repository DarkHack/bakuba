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

namespace bakuba\kernel\dwNode;

use \Exception as Exception;
use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\DwPreNode as DwPreNode;

abstract class DwNodeCrypto extends DwNode {
	
	private $log;
	protected function __construct( DwPreNode $dwPreNode ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->trace(".");
			parent::__construct( $dwPreNode );
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function getStatus( $response = null) {
		try {
			$this->log->trace(".");
			$response = parent::getStatus($response);
			if ( $response == null )
				$response = array();
			
			$response[ "crypto" ] = $this->getCryptoState();
			
			return $response;
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	public function setStatus($response) {
		try {
			if ( $this->dwServerConfig != null )
				throw new Exception("no set Status on slave node");
			else {
				$this->log->trace(". ".print_r($response,true));
				parent::setStatus($response);
				
				if (isset($response->crypto))
					$this->lastCryptoState = $response->crypto;
			}
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
	}
	private $lastCryptoState = array();
	public function getCryptoState(  ) {
		$crypto = array();
		try {
			$crypto['crypto']="coucou";
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $crypto;
	}
	
	
	public function getCiphers() {
		return $this->dwPreNode->getCiphers();
	}
	public function cryptoChannelTry( $cryptedData ) {
		return $this->dwPreNode->cryptoChannelTry( $cryptedData );
	}
	public function cryptoChannelResponse( $sessionKey, $data ) {
		return $this->dwPreNode->cryptoChannelResponse( $sessionKey, $data );
	}
	public function cryptoChannelGet( $sessionKey, $attributeName ) {
		return $this->dwPreNode->cryptoChannelGet( $sessionKey, $attributeName );
	}
	public function cryptoChannelSet( $sessionKey, $attributeName, $attributeValue ) {
		$this->dwPreNode->cryptoChannelSet( $sessionKey, $attributeName, $attributeValue );
	}
}

?>

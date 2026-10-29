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
use bakuba\kernel\shell\DwShell as DwShell;

use bakuba\kernel\dwCrypto\DwCryptoChannel as DwCryptoChannel;
use bakuba\kernel\dwCrypto\DwCryptControllerClient as DwCryptControllerClient;

use bakuba\kernel\DwPreNode as DwPreNode;

use  bakuba\kernel\process\server\DwServer as DwServer;

abstract class DwNodeCrypto {	
	static private $sLog = null;
	
	// $serverConf, $initNode, $dwPreNode
	public static function createNode( DwPreNode $dwPreNode, $initNode = null ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			
			$node = new DwNodeApplication( $dwPreNode );
			if ( $initNode != null ) $node->setInitNode( $initNode );
			self::$nodes[] = $node;
			
			return $node;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
			
		return null;
	}
	
	private static $nodes = array();	
	public static function getNodes( ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			return self::$nodes;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	
	private $upTime;
	protected $dwServer = null;
	public function setDwServer( DwServer $dwServer ) {
		try {
			$this->log->trace(".");
			
			$this->dwServer = $dwServer;
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	// construct with logger
	private $log;
	protected function __construct( DwPreNode $dwPreNode ) {
		
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->trace(".");
			$this->dwPreNode = $dwPreNode;
			$this->upTime = microtime();
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	protected $dwControllerClient = null;
	public function setInitNode( $b64CryptoChannel ) {
		try {
			$this->log->trace(".");
			
			if ( $b64CryptoChannel != null ) {
				$this->dwControllerClient = new DwCryptControllerClient( $b64CryptoChannel, "Node" );
			} else  {
				$this->dwControllerClient = null;
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function isMaster() {
		return $this->dwServer != null;
	}
	public function haveParent() {
		return $this->dwControllerClient != null;
	}
	public function getPreNodeKey() {
		return $this->dwPreNode->getPreNodeKey();
	}
	public function getCiphers() {
		return $this->dwPreNode->getCiphers();
	}
	public function getCpt() {
		return $this->dwPreNode->getCpt();
	}
	public function getAccess() {
		return $this->dwPreNode->getAccess();
	}
	public function getIpClt() {
		return $this->dwPreNode->getIpClt();
	}
	public function getIpSrv() {
		return $this->dwPreNode->getIpSrv();
	}
	public function getNodeData() {
		return $this->getIpSrv().":".$this->getIpClt();
	}
	public function getStatus( $response = null) {
		try {
			$this->log->trace(".");
			//$response = parent::getStatus($response);
			if ( $response == null )
				$response = array();
			
			$response[ "upTime" ] = $this->upTime;
			$response[ "machine-id" ] = DwShell::exec("cat /etc/machine-id");
			
			return $response;
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	public function setStatus($response) {
		try {
			if ( $this->isMaster() )
				throw new Exception("no set Status on master node");
			else {
				$this->log->trace(". ".print_r($response,true));
				//parent::setStatus($response);
				
				if (isset($response->upTime))
					$this->upTime = $response->upTime;
			}
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
	}
}

?>

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

namespace bakuba\kernel\io\socket;
//use bakuba\kernel\io\socket\DwSocketListen as DwSocketListen;
use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\agartha\DwAgartha as DwAgartha;
use  bakuba\kernel\network\DwPort as DwPort;

class DwSocketListen extends DwSocket {
	static private $sLog = null;
	
	public static function listen( DwPort $dwPort ) {
		$instance = null;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			self::$sLog->trace(".");
			
			//if ( function_exists("") )
			if( ! ( $sock = socket_create( AF_INET, SOCK_STREAM, SOL_TCP ) ) > 0 ) {
				$sockErrorNo = socket_last_error($sock);
				throw new Exception("failed to socket_create[ErrorNo:$sockErrorNo]: ".socket_strerror($sockErrorNo) );
			} else {
				$ip=$dwPort->getIp();$port=$dwPort->getPort();
				self::$sLog->debug("[".$ip.":".$port."]:listen");
				socket_set_option( $sock, SOL_SOCKET, SO_REUSEADDR, 1);
				if( ! ( $ret = socket_bind ( $sock, $ip, $port ) ) > 0 ) {
					$sockErrorNo = socket_last_error($sock);
					throw new Exception("failed to socket_bind[ErrorNo:$sockErrorNo]: ".socket_strerror($sockErrorNo));
				} else {
					if( ! ( $ret = socket_listen( $sock, 10 ) ) > 0 ) {
						$sockErrorNo = socket_last_error($sock);
						throw new Exception( "failed to socket_listen[ErrorNo:$sockErrorNo]: ".socket_strerror($sockErrorNo) );
					} else {
						socket_set_nonblock( $sock );
					}
				}
			}
			
			$instance = new self( $dwPort, $sock );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".self::getStaticClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".self::getStaticClassName()."]");
			throw $error;
		}
		return $instance;
	}
	
	private function __construct( DwPort $dwPort, $sock ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			self::$sLog->trace(".");
			parent::__construct( $dwPort, $sock );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function newConnection() {
		$dwSocket = null;
		try {
			self::$sLog->trace(".");
			
			$sock = @socket_accept( $this->sock );
			if ( $sock === false ) {
				$sockErrorNo = socket_last_error( $this->sock );
				throw new Exception( "failed to socket_accept[ErrorNo:$sockErrorNo]:".socket_strerror( $sockErrorNo )."|sock:false" );
			} elseif ( $sock >0 ) {
				$dwAgartha = DwAgartha::singleton();
				$sockPeerAddr="";
				$sockPeerPort="";
				if (!@socket_getpeername($sock, $sockPeerAddr, $sockPeerPort)) {
					$sockErrorNo = socket_last_error( $sock );
					throw new Exception( "failed to socket_getpeername[ErrorNo:$sockErrorNo]:".socket_strerror( $sockErrorNo ) );
				} else {
					$dwInterface = $this->getDwPort()->getInterface();
					$dwCIDR = $dwInterface->getCIDR($sockPeerAddr);
					$dwIp = $dwCIDR->getIp($sockPeerAddr);
					$dwPort = $dwIp->getPort($dwInterface, $sockPeerPort);
					$dwSocket = new DwSocket( $dwPort,  $sock );
					$this->insert( $dwSocket );
				}
			} else {
				$sockErrorNo = socket_last_error( $this->sock );
				throw new Exception( "failed to socket_accept[ErrorNo:$sockErrorNo]:".socket_strerror( $sockErrorNo )."|sock:$sock" );
			}
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $dwSocket;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		$className = __CLASS__;
		try {
			if ( is_null( self::$sLog ) ) self::$sLog = DwLogger::getLogger( $className );
			//self::$sLog->trace(".");
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
	public static function getStaticClassName( bool $fqcn=null ) : string {
		$className = __CLASS__;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( $className );
			//self::$sLog->trace(".");
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

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
//use bakuba\kernel\io\socket\DwSocket as DwSocket;

use darkphp\error\DwException as DwException;

use bakuba\kernel\network\DwPort as DwPort;
use bakuba\kernel\network\DwInterface as DwInterface;

class DwSocket extends \bakuba\kernel\io\DwResourceIp {
	protected $packetRcvSize = 0;
	protected $packetSndSize = 0;
	
	protected $connected=false;
	public function __construct( \darkphp\DwObject $parent ,DwInterface $dwInterface ,$sock ) {
		parent::__construct( $parent ,$dwInterface ,$sock );
		//$this->isFullTrace() and $this->log->trace(".");
		///*
		if ( $this->packetRcvSize == 0 ) {//|| ! \socket_set_option( $sock ,\SOL_SOCKET ,\SO_RCVBUF ,$this->packetRcvSize ) ) {
			if ( $this->packetRcvSize != 0 )
				$this->log->error( "echec socket set SO_RCVBUF :".$this->packetRcvSize );
			$packetRcvSize = \socket_get_option( $sock ,\SOL_SOCKET ,\SO_RCVBUF );
			if ( $packetRcvSize > 0 )
				$this->packetRcvSize = $packetRcvSize;
		}
		if ( $this->packetSndSize == 0 ) {//|| ! \socket_set_option( $sock ,\SOL_SOCKET ,\SO_SNDBUF ,$this->packetSndSize ) ) {
			if ( $this->packetSndSize != 0 )
				$this->log->error( "echec socket set SO_SNDBUF :".$this->packetSndSize );
			$packetSndSize = \socket_get_option( $sock ,\SOL_SOCKET ,\SO_SNDBUF );
			if ( $packetSndSize > 0 )
				$this->packetSndSize = $packetSndSize;
		}
		//*/
		$this->isFullTrace() and $this->log->info( "buf size : send/{$this->packetSndSize} recv/{$this->packetRcvSize}" );
	}
	// connectedReadPeer();
	public function connectedReadPeer() : void {
		//$dwPort = null;
		if ( !$this->isResource() )
			throw new \Exception( "failed sock:not resource" );
		$sock = $this->getResource();
		
		$sockPeerAddr=null;$sockPeerPort=null;
		if ( ! @\socket_getpeername($sock ,$sockPeerAddr ,$sockPeerPort ) ) {
			$errNo = \socket_last_error( $sock );
			throw new \Exception(
				"failed to socket_getpeername[{$errNo}]:".\socket_strerror( $errNo )
			);
		} else
			$this->setPeer( $sockPeerAddr ,$sockPeerPort );
		//	$dwPort = DwPort::getPeer( $sockPeerAddr ,$sockPeerPort );
		//return $dwPort;
	}
	//*/
	/*
	protected function readClose( ) : void{
		try {
			//$this->isFullTrace() and $this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			parent::readClose( );
			//socket_shutdown($this->getResource(), 1);
		} catch(\Exception $exception) {
			$this->log->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(\Error $error) {
			$this->log->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	//*/
	private $withSocketClose = true;
	public function noSocketClose() : void {
		$this->withSocketClose = false;
	}
	public function disconnect() : bool {
		$return = false;
		try {
			$this->isFullTrace() and $this->log->trace("[{$this->getInstanceKey()}]");
			
			if ( $this->withSocketClose ) {
				if ( $this->isResource() ) {
					$sock = $this->getResource();
					socket_shutdown( $sock ,2 );
					socket_close( $sock );
				} else
					$this->log->warn("not disconnected.");
			}
			$return = parent::disconnect();
			
		} catch(\Exception $exception) {
			$this->log->error("[{$this->getInstanceKey()}] exception");
			$this->log->throwable($exception);
			//throw $exception;
		} catch(\Error $error) {
			$this->log->error("[{$this->getInstanceKey()}] error");
			$this->log->throwable($error);
			//throw $error;
		}
		return $return;
	}
	public function receive(?string $data = NULL): int {
		$nbByte=0;
		try {
			$sock = $this->getResource();
			if ( $this->isListen() == true ) {
				$nbByte = 0;
				$this->log->error( "no recv is listen" );
			} else {
				//$this->isFullTrace() and $this->log->trace(" is ".( $this->isListen() == true ? "listen" : "connexion" ) );//too much write
				$buf="";
				try {
					//$nbByte = @socket_recv($sock, $buf, $this->packetRcvSize, self::$flags);
					$address=null; $port=null;
					$nbByte = @\socket_recvfrom( $sock ,$buf ,$this->packetRcvSize ,self::$flags ,$address ,$port);
					if ( is_null( $this->getDwPort() ) && ! is_null( $address ) )
						$this->setPeer( $address ,$port );
					$nbByte > 0 and $this->isFullTrace() and $this->log->trace( array( $nbByte ,$buf ) );
				} catch(\Exception $socket_exception) {
					$this->log->throwable($socket_exception);
					//throw $socket_exception;
				} catch(\Error $socket_error) {
					$this->log->throwable($socket_error);
					//throw $socket_error;
				}
				if( $nbByte === false ) {
					$socketLastError = \socket_last_error($sock);
					switch ($socketLastError) {
						case 104 : // ECONNRESET
							$this->log->warn("end reception / ECONNRESET");
							$this->readClose();
							break;
						case 11 : // Resource temporarily unavailable
							// do nothing
							break;
						case 10035 :// Une opération non bloquante sur un socket n’a pas pu être achevée immédiatement.
							// do nothing
							break;
						case 10053 :
						case 10054 : // remote connexion where host has close socket
							// do nothing
							$this->log->warn("[ $socketLastError ] remote connexion closed");
							$this->readClose();
							break;
						default :
							if ( $socketLastError != 0 ) {
								\socket_clear_error($sock);
								$this->readClose();
								throw new \Exception("echec on socket_recv[ Nerror $socketLastError ]\r\n".\socket_strerror($socketLastError));
							} else
								$this->log->warn("socket_recvfrom as return false, and no error in socket_last_error");
							break;
					}
					if ( $socketLastError != 0 )
						\socket_clear_error($sock);
					$nbByte=-1;
				} else if ( $nbByte > 0 ) {
					$this->recvEnded = false;
					//$this->isFullTrace() and $this->log->trace( "size recv:".$nbByte.":\n".$buf );//too much write(litle less)
					//$last = substr($buf,-1);
					
					parent::receive( $buf );
					
					if ( $nbByte < $this->packetRcvSize ) {//$last == "\r"  || $last == "\n" || 
						//$this->isFullTrace() and $this->log->trace("end reception / break octet found");
						//$this->isFullTrace() and $this->log->trace("end reception / $nbByte < ".self::$PACKET_SIZE." ");
						$this->readClose();
					}
				} elseif ( \is_null( $nbByte ) || $nbByte == 0 ) {
					$nbByte=0;
					parent::receive( null );
					if ( $this->recvInactivity > 10 ) {
						$this->isFullTrace() and $this->log->trace( "empty" );
						$this->readClose();
					}
				}
				//$this->isFullTrace() and $this->log->trace("octect[".$byte."]:".$buf);
			}
		} catch(\Exception $exception) {
			$this->log->error("[{$this->getInstanceKey()}] exception");
			$this->log->throwable($exception);
			$nbByte=-1;
		} catch(\Error $error) {
			$this->log->error("[{$this->getInstanceKey()}] error");
			$this->log->throwable($error);
			$nbByte=-1;
		}
		return $nbByte;
	}
	public function send( ) : bool {
		$sended=false;
		try {
			if ( ! $this->isResourceDefined() )
				throw new \Exception("sock not ressource.");
			
			$sock = $this->getResource();
			$buf = \substr( $this->sendBuf, $this->sendCpt, $this->packetSndSize );
			$len = \strlen($buf);
			try {
				$dwPort = $this->getDwPort();
				if ( \is_null( $dwPort ) )
					$nbSended = @\socket_send( $sock, $buf, $len, MSG_DONTROUTE );
				else
					$nbSended = @\socket_sendto(
						$sock
						,$buf
						,$len
						,MSG_DONTROUTE
						,$dwPort->getIp()->toString()
						,$dwPort->getPort()
					);
			} catch(\Exception $socket_exception) {
				$this->log->throwable($socket_exception);
				//throw $socket_exception;
			} catch(\Error $socket_error) {
				$this->log->throwable($socket_error);
				//throw $socket_error;
			}
			
			$this->isFullTrace() and $this->log->trace("$nbSended/$len [".$this->getInstanceKey()."]");//\r\n$buf");
			if ( $nbSended === FALSE ) {
				$sockErrorNo = \socket_last_error( $sock );
				switch ($sockErrorNo) { 
					case 32 : // Broken pipe
						$this->log->warn("Broken pipe");
						\socket_clear_error($sock);
						$this->writeClose();
						break;
					case 10035 : //WSAEWOULDBLOCK
						//$this->log->warn("Broken pipe");
						\socket_clear_error($sock);
						break;
						
					/*	
					case 11 : // Resource temporarily unavailable
						// do nothing
						socket_clear_error($sock);
						break;
					//*/
					default :
						$this->writeClose();
						throw new \Exception("echec on socket_send[ErrorNo:$sockErrorNo]\r\n".\socket_strerror($sockErrorNo));
						break;
				}
			} else {
				$this->sended($nbSended);
				if ( $nbSended < $len ) {
					//throw new \Exception( "echec on socket_send $nbSended < $len" );
					$sended=false;
				} else
					$sended=true;
			}
		
		} catch(\Exception $exception) {
			$this->log->error("[{$this->getInstanceKey()}] exception");
			$this->log->throwable($exception);
		} catch(\Error $error) {
			$this->log->error("[{$this->getInstanceKey()}] error");
			$this->log->throwable($error);
		}
		return $sended;
	}
	///*
	public function __destruct( ) {
		//$this->isFullTrace() and $this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	protected static $PACKET_SIZE = 1024;
	// PHP_NORMAL_READ : stop on \n or \r
//	protected static $SOCKETREADMODE = PHP_NORMAL_READ;
	protected static $SOCKETREADMODE = PHP_BINARY_READ;
	private static $flags = null;
	public static function evaluateFlags() {
		//if(!defined('MSG_DONTWAIT')) define('MSG_DONTWAIT', 0x40);
		if( defined('MSG_DONTWAIT') ) self::$flags = MSG_DONTWAIT;
		else self::$flags = 0;
		//else self::$flags = 0;
	}
}
DwSocket::evaluateFlags();
?>

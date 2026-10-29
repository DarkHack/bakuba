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

namespace bakuba\kernel\io\stream;
//use bakuba\kernel\io\stream\DwStreamSocket as DwStreamSocket;

use bakuba\kernel\network\DwInterface as DwInterface;
use bakuba\kernel\network\DwPort as DwPort;

class DwStreamSocket extends \bakuba\kernel\io\DwResourceIp {
	protected $packetRcvSize = 64;
	protected $packetSndSize = 64;
	
	protected $connected=false;
	private $socketEnableFullTrace = false;
	protected function enableSocketFullTrace() : void {
		$this->socketEnableFullTrace = true;
	}
	public function __construct( \darkphp\DwObject $parent ,DwInterface $dwInterface ,$sock ) {
		//$this->enableSocketFullTrace();
		parent::__construct( $parent ,$dwInterface ,$sock );
		//$this->log->trace(".");
		
		if ( $this->packetRcvSize == 0 ) 
			throw new \Exception( "echec no stream socket without packetRcvSize :".$this->packetRcvSize );
		elseif ( \stream_set_read_buffer( $sock ,$this->packetRcvSize ) != 0 )
			throw new \Exception( "echec stream_set_read_buffer for :".$this->packetRcvSize );
		
		/*
		if ( $this->packetSndSize == 0 )
			throw new \Exception( "echec no stream socket without packetSndSize :".$this->packetSndSize );
		elseif ( \stream_set_write_buffer( $sock ,$this->packetSndSize ) != 0 ) 
			throw new \Exception( "echec stream_set_write_buffer for :".$this->packetSndSize );
		//*/
		
		$this->log->info( "buf size : send/{$this->packetSndSize} recv/{$this->packetRcvSize}" );
		
		// dwObjectContainer : DwPort(item) => DwIp (item) => DwInterface(item) => DwNode(item/stop grow) => DwAgartha(map)
		//if ( ! is_null($this->parent) )
		//	$this->parent->link( $this );
	}
	/*
	private function initSocket() {
		try {
			$sock = $this->dwSocket->getResource();
			socket_getsockname( $sock, $this->srvIp ,$this->srvPort );
			socket_getpeername( $sock, $this->cltIp ,$this->cltPort );
		} catch(\Exception $exception) {
			$this->log->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			$this->log->throwable( $exception );
			//throw $exception;
		} catch(\Error $error) {
			$this->log->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			$this->log->throwable( $error );
			//throw $error;
		}
	}
	//*/
	// connectedReadPeer();
	public function connectedReadPeer() : void {
		//$dwPort = null;
		if ( !$this->isResource() )
			throw new \Exception( "failed sock:not resource" );
		$sock = $this->getResource();
		
		$remotePeerIpPort = \stream_socket_get_name( $sock ,true );
		//$localPeerIpPort = \stream_socket_get_name( $sock ,false );
		if ( !$remotePeerIpPort )
			throw new \Exception( "failed to stream_socket_get_name" );
		else {
			list( $sockPeerAddr ,$sockPeerPort ) = explode( ":" ,$remotePeerIpPort );
			$this->setPeer( $sockPeerAddr ,$sockPeerPort );
			//$dwPort = DwPort::getPeer( $dwInterface ,$sockPeerAddr ,$sockPeerPort );
		}
		//return $dwPort;
	}
	//*/
	/*
	protected function readClose( ) : void {
		try {
			//$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
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
			$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
			if ( $this->withSocketClose && $this->isResource() ) {
				$sock = $this->getResource();
				\stream_socket_shutdown( $sock ,2 );
				\fclose( $sock );
			} else
				$this->log->warn("not disconnected.");
			
			$return = parent::disconnect();
			
		} catch( \Exception $exception ) {
			$this->log->error("exception [{$this->getClassName()}/{$this->getInstanceKey()}]");
			$this->log->throwable($exception);
			//throw $exception;
		} catch( \Error $error ) {
			$this->log->error("error [{$this->getClassName()}/{$this->getInstanceKey()}]");
			$this->log->throwable($error);
			//throw $error;
		}
		return $return;
	}
	/*
	private $listening = false;
	public function isListen() : bool {
		return $this->listening;
	}
	public function enableListen() {
		$this->listening = true;
		parent::enableListen();
	}
	//*/
	public function receive(?string $data = NULL): int {
		$nbByte=0;
		try {
			$sock = $this->getResource();
			if ( $this->isListen() == true ) {
				$nbByte = 0;
				$this->log->error( "no recv is listen" );
			} else {
				//$this->log->trace(" is ".( $this->isListen() == true ? "listen" : "connexion" ) );//too much write
				$buf="";
				try {
					$peerAddressPort=null;
					\stream_set_blocking( $sock ,false );
					$buf = @\stream_socket_recvfrom( $sock ,$this->packetRcvSize ,0 ,$peerAddressPort );
					$nbByte = \strlen($buf);
					if ( is_null( $this->getDwPort() ) ) {
						list( $adresse ,$port ) = explode( ':' ,$peerAddressPort );
						$this->setPeer( $address ,$port );
					}
					$this->socketEnableFullTrace && $nbByte > 0 && $this->log->trace( array( $nbByte ,$buf ) );
				} catch(\Exception $socket_exception) {
					$this->log->throwable($socket_exception);
					//throw $socket_exception;
				} catch(\Error $socket_error) {
					$this->log->throwable($socket_error);
					//throw $socket_error;
				}
				if( $nbByte === false ) {
					$this->log->debug( "echec reveive byte, we close :".print_r( $nbByte ,true ) );
					$this->readClose();
					$nbByte=0;
				} else if ( $nbByte > 0 ) {
					$this->recvEnded = false;
					//$this->log->trace( "size recv:".$nbByte.":\n".$buf );//too much write(litle less)
					//$last = substr($buf,-1);
					
					parent::receive( $buf );
					
					if ( $nbByte < $this->packetRcvSize ) {//$last == "\r"  || $last == "\n" || 
						//$this->log->trace("end reception / break octet found");
						//$this->log->trace("end reception / $nbByte < ".$this->packetRcvSize." ");
						$this->readClose();
					}
				} elseif ( \is_null( $nbByte ) || $nbByte == 0 ) {
					$nbByte=0;
					parent::receive( null );
					if ( $this->recvInactivity > 10 ) {
						$this->log->trace( "no byte received, with inactivity :".$this->recvInactivity );
						$this->readClose();
					}
				}
				//$this->log->trace("octect[".$byte."]:".$buf);
			}
		} catch(\Exception $exception) {
			$this->log->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			$this->log->throwable($exception);
			$nbByte=-1;
		} catch(\Error $error) {
			$this->log->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
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
				$nbSended = @\stream_socket_sendto(
					$sock
					,$buf
					,0 // \MSG_DONTROUTE
				);
				
			} catch(\Exception $socket_exception) {
				$this->log->throwable($socket_exception);
				//throw $socket_exception;
			} catch(\Error $socket_error) {
				$this->log->throwable($socket_error);
				//throw $socket_error;
			}
			
			$this->socketEnableFullTrace && $this->log->trace("$nbSended/$len [".$this->getInstanceKey()."]");//\r\n$buf");
			if ( $nbSended === FALSE ) {
				$this->writeClose();
				$this->log->error( "nbSended is false or 0 :".print_r( $nbSended ,true ) );
				
			} else {
				$this->sended($nbSended);
				if ( $nbSended < $len )
					$sended=false;
				else
					$sended=true;
			}
		
		} catch(\Exception $exception) {
			$this->log->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			$this->log->throwable($exception);
		} catch(\Error $error) {
			$this->log->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			$this->log->throwable($error);
		}
		return $sended;
	}
	///*
	public function __destruct( ) {
		//$this->log->trace( "." );
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
}
?>

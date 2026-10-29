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

namespace  bakuba\kernel\io\socket;
//use bakuba\kernel\io\socket\DwSocketServer as DwSocketServer;

use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

//use bakuba\kernel\io\socket\DwSocket as DwSocket;
//use bakuba\kernel\io\socket\DwSocketSelect as DwSocketSelect;

use bakuba\kernel\services\server\DwServerConnexion as DwServerConnexion;

//use bakuba\kernel\process\server\IDwServer as IDwServer;

class DwSocketServer extends DwSocketIp {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className = \darkphp\source\DwSource::classNameToTopName( $this->getClassName() );
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
			$this
			,str_replace( array( '.' ,':' ,'/' ),'-', $this->dwPort->getIp()->toString()."p".$this->dwPort->getPort() )
			,$className );
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	//protected $log = null;
	
	protected $dwInterface = null;
	private $ipv6_ifaceName = null;
	public function __construct(
		DwSocketSelect $dwSocketSelect
		,\bakuba\kernel\objects\DwCallBack $onConnect
		,\bakuba\kernel\network\DwPort $dwPort
		,int $protocole = \SOL_TCP
	) {
		$dwIp = $dwPort->getIp();
		$dwCIDR = $dwIp->getDwCIDR();
		$this->dwInterface = $dwCIDR->getDwInterface();
		parent::__construct( $dwSocketSelect ,$onConnect ,$dwPort ,$protocole );
	}
	
	public function init(  ) : void {
		$this->listenSock( );
		parent::init();
	}
	private function listenSock( ) {
		if ( $this->isListen )
			throw new \Exception("is ever in listen");
		else {
			$this->listen();
			$this->isListen = true;
		}
	}
	protected function createDwSocket( $sock ) : DwSocket {
		return new DwSocket(
			$this
			,$this->dwInterface
			,$sock
		);
		/*
		 $dwSocket =  new DwSocket( $this ,$dwInterface ,$sock );
		 $dwSocket->enableSocketFullTrace();
		 return $dwSocket;
		//*/
	}
	// dwItemContainer : DwSocketServerMonoThread(item) => DwServer(item) => DwBakuba(item) => DwNode(item) => DwAgartha(map)
	private function listen() : bool {
		$return = false;
		$this->log->trace( $this->sock_family == \AF_INET6 ? "ipv6" : "ipv4" );
		
		if( ! ( $sock = \socket_create( $this->sock_family ,$this->sock_type ,$this->sock_protocole ) ) > 0 ) {
			$errNo = \socket_last_error($sock);
			$sockErrorStr= \socket_strerror( $errNo );
			throw new \Exception(
				"failed to socket_create[{$errNo}]: {$sockErrorStr}"
			);
		}
		
		//$this->log->info( \socket_get_option($sock, \SOL_SOCKET, \SO_REUSEADDR) );
		//if ( function_exists("") )
		if ( ! is_null( $sock ) ) {
			$dwIp = $this->dwPort->getIp();
			$ipAdress = $dwIp->getIpAddress();
			//$dwCIDR = $dwIp->getDwCIDR();
			//$dwInterface = $dwCIDR->getDwInterface();
			//$dwInterface->setDebug( $this->log );
			$port=$this->dwPort->getPort();
			$this->log->debug("[{$ipAdress}]:{$port} -listen");
			/*
			$ipv6Iface="{$dwIp->toString()}{$this->ipv6_ifaceName}";
			$this->log->debug( "ipv6_ifaceName: {$this->ipv6_ifaceName}/{$ipv6Iface}" );
			$ipToBind = $this->sock_family == \AF_INET6 ? $ipv6Iface : $dwIp->toString();
			$this->log->debug( $ipToBind );
			//*/
			\socket_set_option( $sock, \SOL_SOCKET, \SO_REUSEADDR, 1 );
			if( ! ( $ret = \socket_bind ( $sock ,$ipAdress ,$port ) ) > 0 ) {
				$errNo = \socket_last_error( $sock );
				$sockErrorStr= \socket_strerror( $errNo );
				throw new \Exception(
					"failed socket_bind[{$errNo}]:{$sockErrorStr}"
				);
			} else {
				if( $this->sock_type != \SOCK_DGRAM && ! ( $ret = \socket_listen( $sock, 10 ) ) > 0 ) {
					$errNo = \socket_last_error( $sock );
					$sockErrorStr= \socket_strerror( $errNo );
					throw new \Exception(
						"failed socket_listen:{$sockErrorNo} {$sockErrorStr}"
					);
				} else {
					\socket_set_nonblock( $sock );
					$iDwLogger = null;
					
					$dwSocket = $this->createDwSocket( $sock );
					$dwSocket->listenTo( $this->dwPort );
					//$this->getDwSocketSelect()->setSocketLimiteDeltaTimer( 0.25 );
					$dwSocket->setOnReaded( new DwCallBack( array( &$this ,"acceptSocketConnection" ) ,$dwSocket ) );
					$this->getDwSocketSelect()->socketRead( $dwSocket );
					$return = true;
				}
			}
		}
		return $return;
	}
	private $peers = array();
	public function acceptSocketConnection( DwSocket $dwSocketAccept ) : void {
		$this->log->trace(".");
		if ( $this->sock_type != \SOCK_DGRAM )
			$this->acceptTcpConnection( $dwSocketAccept );
		else {
			$dwSocketRead = $this->createDwSocket( $dwSocketAccept->getResource() );
			$dwSocketRead->noSocketClose();
			$this->newSocketConnection( $dwSocketRead );
		}
		//$dwSocketAccept->setOnReaded( new DwCallBack( array( &$this ,"acceptSocketConnection" ) ) );
		$this->getDwSocketSelect()->socketRead( $dwSocketAccept );
	}
	public function acceptTcpConnection( DwSocket $dwSocketAccept ) : void {
		//$dwSocket = null;
		//$this->log->trace(".");
		$sockListen = $dwSocketAccept->getResource();
		$sock = @\socket_accept( $sockListen );
		if ( $sock === false ) {
			$errNo = \socket_last_error( $sockListen );
			if ( $this->sock_type == \SOCK_STREAM && $errNo == 0 )
				$this->log->warn( "redondante connexion" );
			else
				throw new \Exception(
				"failed(sock:false) to socket_accept[{$errNo}]:".\socket_strerror( $errNo )
				);
		} elseif ( $sock !== null ) {
			//$this->log->info( \socket_get_option($sock, \SOL_SOCKET, \SO_REUSEADDR) );
			/*
			if ( ! isset( $this->peers[ $sockPeerAddr ] ) )
				$this->peers[ $sockPeerAddr ]=1;
			else
				$this->peers[ $sockPeerAddr ]++;
			//*/
			
			$dwSocket = $this->createDwSocket( $sock );
			$dwSocket->connectedReadPeer( );
			$this->newSocketConnection( $dwSocket );
				//$iDwLogger->debug("run go in the action, john");
		} else 
			throw new \Exception( "failed to socket_accept sock not false but null" );
	}
	protected function socketConnexion( DwSocket $dwSocket ) : DwSocketConnexion {
		return new DwSocketConnexion(
				$this->getDwSocketSelect()
				,$dwSocket
				,$this->dwPort
		);
	}
	public function newSocketConnection( DwSocket $dwSocket ) : void {
		$this->log->info( " Try Socket" );
		//$dwSocket->setOnReaded( $this->onConnect->getClone( $this->socketConnexion( $dwSocket ) ) );
		///*
		$this->getDwSocketSelect()->getDwObjectTimer()->addTick(
			$this->onConnect->getClone( $this->socketConnexion( $dwSocket ) )
		);
		//*/
		//$this->getDwSocketSelect()->socketRead( $dwSocket );
	}
	
	///*
	public function __destruct() {
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

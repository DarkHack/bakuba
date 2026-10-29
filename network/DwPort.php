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

namespace bakuba\kernel\network;
//use bakuba\kernel\network\DwPort as DwPort;

class DwPort extends \darkphp\serializable\aDwSerializable {
	protected $port = null;
	public function __construct( DwIp $dwIp, int $port = 80 ) {
		$this->port=$port;
		parent::__construct( $dwIp );
		$this->log->trace(". create [$port]:".$this->parent->toString());
		//if ( ! is_null($this->dwItem) && ! $this->dwItem->isLinked()) 
		//	$this->dwItem->link( $this->dwInterface->getItem() );
		//$this->log->trace(".");
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		//$this->addtoSerialize("dwInterface");
		//$this->addtoSerialize("dwIp");
		$this->addtoSerialize("port");
	}
	public function getIp() : DwIp {
		return $this->parent;
	}
	public function getPort() : int {
		return $this->port;
	}
	
	public function listen() {
			//$this->dwSocketServer=;
			//socket_getsockname($this->sockServer, $this->addr, $this->port);
			return \bakuba\kernel\process\server\socket\DwSocketServer::listen( $this );
	}
	public function open() {
		return null;
	}
	public function getPortPath() : string {
		return $this->getIp()->getIpPath()."p".$this->getPort();
	}
	
	public function toString( $withParent = false ) : string {
		return $this->getIp()->toString().":".$this->port;
	}
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	// \bakuba\kernel\network\DwPort::getPeer( $dwInterface ,$peerAddr ,$peerPort );
	public static function getPeer( \bakuba\kernel\network\DwInterface $dwInterface ,string $peerAddr ,string $peerPort ) {
		$dwCIDR = $dwInterface->getCIDR( $peerAddr );
		$dwIp = $dwCIDR->getIp( $peerAddr );
		return $dwIp->getPort( $peerPort );
	}
}

?>

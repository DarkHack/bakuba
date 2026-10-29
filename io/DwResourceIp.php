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

namespace bakuba\kernel\io;
//use bakuba\kernel\io\DwResourceIp as DwResourceIp;

use bakuba\kernel\network\DwPort as DwPort;
use bakuba\kernel\network\DwInterface as DwInterface;

class DwResourceIp extends DwResourceBuffered {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$dwPort = $this->getDwPort();
		if ( is_null( $dwPort ) )
			parent::setDebug( $iDwLogger );
		else {
			$dwIp = $dwPort->getIp();
		
			if ( \method_exists( $iDwLogger ,"upTo" ) )
				$this->log = $iDwLogger->upTo(
					$this
					,$dwIp->getIpPath()
					,$dwPort->getPort()
				);
			else
				parent::setDebug( $iDwLogger );
		}
		//$this->log->trace( "." );
	}
	/*
	protected function getDwInterface() : DwInterface {
		return $this->getDwCIDR()->getDwInterface();
	}
	public function getDwCIDR() : DwCIDR {
		return $this->getDwIp()->getDwCIDR();
	}
	protected function getDwIp() : DwIp {
		return  $this->getDwPort()->getIp();
	}
	//*/
	private $dwInterface = null;
	public function getDwInterface() : DwInterface {
		return $this->dwInterface;
	}
	public function __construct(
		\darkphp\DwObject $parent
		,DwInterface $dwInterface
		,$resource
	) {
		$this->dwInterface = $dwInterface;
		parent::__construct( $parent ,$resource );
		//$this->log->trace(".");
	}
	//*/
	private $dwPort = null;
	private function setDwPort( DwPort $dwPort ) : void {
		$this->dwPort = $dwPort;
	}
	public function listenTo( \bakuba\kernel\network\DwPort $dwPortListen ) {
		$this->setDwPort( $dwPortListen );
		$this->enableListen();
	}
	public function getDwPort() : ?DwPort {
		return $this->dwPort;
	}
	public function setPeer( string $peerAddr ,string $peerPort ) : void {
		if ( \is_object( $this->dwPort ) )
			throw new \Exception( 'Peer Port ever defined' );
		$this->log->info( "connect[{$this->dwInterface->getIfaceName()}] {$peerAddr} {$peerPort}" );
		
		$this->setDwPort( DwPort::getPeer( $this->dwInterface ,$peerAddr ,$peerPort ) );
		
		//	 DwPort::getPeer( $sockPeerAddr ,$sockPeerPort );
		//return $dwPort;
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

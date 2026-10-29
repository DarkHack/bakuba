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

namespace bakuba\kernel\session;
//use bakuba\kernel\session\DwSessionIpKey as DwSessionIpKey;

use bakuba\kernel\network\DwPort as DwPort;

class DwSessionIpKey {
	private $dwSrvPort=null;
	private $dwCltPort=null;
	private $ipKey=null;
	public function __construct( DwPort $dwSrvPort ,DwPort $dwCltPort ) {
		$this->ipKey = \crc32( $dwSrvPort->getIp()->getCrc32().":".$dwSrvPort->getPort().":".$dwCltPort->getIp()->getCrc32() );
		$this->dwSrvPort=$dwSrvPort;
		$this->dwCltPort=$dwCltPort;
	}
	public function getSrvPort() : DwPort {
		return $this->dwSrvPort;
	}
	public function getSrvIp() : string {
		return $this->getSrvPort()->getIp()->getIpAddress();
	}
	public function getCltPort() : DwPort {
		return $this->dwCltPort;
	}
	public function getCltIp() : string {
		return $this->getCltPort()->getIp()->getIpAddress();
	}
	public function getIpKey( ) : int {
		return $this->ipKey;
	}
	public function __toString() {
		$srvIp = $this->dwSrvPort->getIp()->getIpAddress();
		$srvPort = $this->dwSrvPort->getPort();
		$cltIp = $this->dwCltPort->getIp()->getIpAddress();
		$cltPort = $this->dwCltPort->getPort();
		return $this->getClassName()."[{$srvIp}:{$srvPort} <=> {$cltIp}:{$cltPort} ] ".\dechex( $this->ipKey );
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		//parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		//return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
		return __CLASS__;
	}
}
?>

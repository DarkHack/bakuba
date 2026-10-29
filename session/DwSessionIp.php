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
//use bakuba\kernel\session\DwSessionIp as DwSessionIp;

//use bakuba\kernel\session\DwSessionIpKey as DwSessionIpKey;
use bakuba\kernel\network\DwPort as DwPort;
use bakuba\kernel\network\DwIp as DwIp;

class DwSessionIp extends \darkphp\serializable\aDwSerializable {
	protected $connections = array();
	public function getConnections() : array {
		return $this->connections;
	}
	protected $compteurVisites = array();
	public function getCompteurs() : array {
		return $this->compteurVisites;
	}
	
	public function connect( DwPort $dwSrvPort ,DwPort $dwCltPort ) : DwSessionIpKey {
		$srvIpKey = $dwSrvPort->getIp()->getCrc32();
		if ( ! isset( $this->connections[ $srvIpKey ] ) )
			$this->connections[ $srvIpKey ] = array();
		$srvPort = $dwSrvPort->getPort();
		
		if ( ! isset( $this->connections[ $srvIpKey ][ $srvPort ] ) )
			$this->connections[ $srvIpKey ][ $srvPort ] = new DwSessionIpKey( $dwSrvPort ,$dwCltPort );
		
		$dwSessionIpKey = $this->connections[ $srvIpKey ][ $srvPort ];
		
		if ( ! isset( $this->compteurVisites[ $srvIpKey ] ) )
			$this->compteurVisites[ $srvIpKey ] = array();
		if ( ! isset( $this->compteurVisites[ $srvIpKey ][ $srvPort ] ) )
			$this->compteurVisites[ $srvIpKey ][ $srvPort ] = 0;
		
		$this->compteurVisites[ $srvIpKey ][ $srvPort ]++;
		
		return $dwSessionIpKey;
	}
	public function getCltIp() : DwIp {
		return $this->parent;
	}
	public function __construct( DwIp $cltIp ) {
		/*$this->dwSessionIpKey = $dwSessionIpKey;*/
		parent::__construct( $cltIp );
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		//$this->addtoSerialize("dwSessionIpKey");
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

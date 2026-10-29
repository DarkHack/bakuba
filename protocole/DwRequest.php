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

namespace bakuba\kernel\protocole;
//use bakuba\kernel\protocole\DwRequest as DwRequest;

use bakuba\kernel\network\DwInterface as DwInterface;
use bakuba\kernel\network\DwIp as DwIp;
use bakuba\kernel\network\DwPort as DwPort;

use bakuba\kernel\services\server\aDwServerConnexion as aDwServerConnexion;

use bakuba\kernel\session\DwSessionIpKey as DwSessionIpKey;

class DwRequest extends \darkphp\serializable\aDwSerializable {
	public static function isValidRequest( aDwServerConnexion $dwServerConnexion ,$request ) : ?self {
		return empty( $request ) ? null : new self( $dwServerConnexion );
	}
	
	private $unixTimeStamp = null;
	/*
	private $srvIp = null;
	private $srvPort = null;
	private $cltIp = null;
	private $cltPort = null;
	private $dwSessionIp = null;
	//*/
	// protected for serializable
	protected $dwSessionIpKey = null;
	protected $dwMessage = null;
	public function setDwSessionIpKey( DwSessionIpKey $dwSessionIpKey ) : void {
		$this->dwSessionIpKey = $dwSessionIpKey;
}
	protected function __construct( aDwServerConnexion $dwServerConnexion ) {
		$this->unixTimeStamp = \time();
		parent::__construct( $dwServerConnexion );
		/*
		$this->srvIp = $dwServerConnexion->getSrvIp();
		$this->srvPort = $dwServerConnexion->getSrvPort();
		$this->cltIp = $dwServerConnexion->getCltIp();
		$this->cltPort = $dwServerConnexion->getCltPort();
		//*/
	}
	public function getDwSessionIp() : \bakuba\kernel\session\DwSessionIp {
		return $this->getDwServerConnexion()->getDwSessionIp();
	}
	public function getDwSessionIpKey() : DwSessionIpKey {
		return $this->getDwServerConnexion()->getDwSessionIpKey();
	}
	public function getDwServerConnexion() : aDwServerConnexion {
		return $this->parent;
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize( "unixTimeStamp" );
		/*
		$this->addtoSerialize( "srvIp" );
		$this->addtoSerialize( "srvPort" );
		$this->addtoSerialize( "cltIp" );
		$this->addtoSerialize( "cltPort" );
		*/
	}
	
	public function getRequestTime() : int {
		return $this->unixTimeStamp;
	}
	
	public function getSrvPort() : DwPort {
		return $this->getDwSessionIpKey()->getSrvPort();
	}
	public function getCltPort() : DwPort {
		return $this->getDwSessionIpKey()->getCltPort();
	}
	public function getSrvIp() : DwIp {
		return $this->getSrvPort()->getIp();
	}
	public function getCltIp() : DwIp {
		return $this->getCltPort()->getIp();
	}
	/*
	protected function setSrvPort( DwPort $dwPort ) : void {
		$this->srvPort = $dwPort;
	}
	protected function setCltPort( DwPort $dwPort ) : void {
		$this->cltPort = $dwPort;
	}
	protected function setSrvIp( DwIp $dwIp ) : void {
		$this->srvIp = $dwIp;
	}
	protected function setCltIp( DwIp $dwIp ) : void {
		$this->cltIp = $dwIp;
	}
	//*/
	protected function switchSession( DwPort $cltPort,DwPort $srvPort ) : void {
		$dwServer = $this->getDwServerConnexion()->getDwServer();
		$dwSessionIp = $dwServer->getDwSessionIp( $cltPort );
		$this->setDwSessionIpKey( $dwSessionIp->connect( $srvPort, $cltPort ) );
	}
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct ) {
			if ( isset( $this->$key ) )
				unset( $this->$key );
		}
		parent::__destruct();
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		//return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
		return __CLASS__;
	}
}
?>

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
//use bakuba\kernel\network\DwInterface as DwInterface;
use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;

class DwInterface extends DwObject {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$ifaceName = \preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->ifaceName );
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,"network"
				,$ifaceName
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
		foreach($this->CIDRs as $anDwCIDR) {
			$anDwCIDR->setDebug( $this->log );
		}
	}
	protected $hmac="00:00:00:00:00:00";
	protected $CIDRs=array();
	public function getCIDRs() {
		return $this->CIDRs;
	}
	protected $cptCIDR = 0;
	protected $ifaceName = "lo";
	public function getIfaceName() {
		return $this->ifaceName;
	}
	protected $mtu = -1;
	public function setMtu( int $mtu ) {
		$this->mtu=$mtu;
	}
	protected $metric = -1;
	public function setMetric( int $metric ) {
		$this->metric=$metric;
	}
	protected $state = "inactive";
	public function setState( string $state ) {
		$this->state=$state;
	}
	protected $prefix = "none";
	public function setPrefix( string $prefix ) {
		$this->prefix=$prefix;
	}
	protected $confType = "none";
	public function setConfType( string $confType ) {
		$this->confType=$confType;
	}
	protected $conf6Type = "none";
	public function setConf6Type( string $conf6Type ) {
		$this->conf6Type=$conf6Type;
	}
	protected $DNSservers=array();
	public function getDnsServers() {
		return $this->DNSservers;
	}
	protected $DNS6servers=array();
	public function getDns6Servers() {
		return $this->DNS6servers;
	}
	
	public function __construct( DwObject $parent ,$ifaceName = "lo" ) {
		$this->ifaceName = $ifaceName;
		parent::__construct( $parent );
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize("hmac");
		$this->addtoSerialize("CIDRs");
		$this->addtoSerialize("ifaceName");
		$this->addtoSerialize("mtu");
		$this->addtoSerialize("metric");
		$this->addtoSerialize("state");
		$this->addtoSerialize("cptCIDR");
		$this->addtoSerialize("dnsServer");
	}
	public function addDnsServer( string $ip4dns ) {
		if ( is_array( $this->DNSservers ) )
			$this->DNSservers[]=$ip4dns;
		else
			$this->DNSservers=array( $ip4dns );
	}
	public function addDns6Server( $ip6dns ) {
		if ( is_array( $this->DNS6servers ) )
			$this->DNS6servers[]=$ip6dns;
		else
			$this->DNS6servers=array( $ip6dns );
	}
	public function createCIDR($cidr) : DwCIDR4 {
		$dwCIDR = null;
		if ( \is_object( $this->log ) )
			$this->log->trace( $cidr );

		$dwCIDR = new DwCIDR4( $this ,$cidr );
		//$this->insert( $dwCIDR );
		$this->CIDRs[] = $dwCIDR;
		$this->cptCIDR++;
		return $dwCIDR;		
	}
	public function createCIDR6($cidr6) : DwCIDR6 {
		$dwCIDR6 = null;
		if ( \is_object( $this->log ) )
			$this->log->trace( $cidr6 );

		$dwCIDR6 = new DwCIDR6( $this ,$cidr6 );
		//$this->insert( $dwCIDR6 );
		$this->CIDRs[] = $dwCIDR6;
		$this->cptCIDR++;
		return $dwCIDR6;		
	}
	public function getCIDR( $ipAddressWithCidr ) : ?DwCIDR {
		$ipCidr = null;
		if ( \is_object( $ipAddressWithCidr ) && \method_exists( $ipAddressWithCidr, 'toString' ) )
			$ipAddressWithCidr=$ipAddressWithCidr->toString();
		if ( \strstr( $ipAddressWithCidr ,'/' ) != false )
			list( $ipAddress ,$ipCidr ) = \explode( '/', $ipAddressWithCidr );
		else
			$ipAddress=$ipAddressWithCidr;
		if ( \is_object( $this->log ) )
			$this->log->trace( $ipAddress );
		$dwCIDR = null;
		foreach($this->CIDRs as $anDwCIDR) {
			if ( $anDwCIDR->inside( $ipAddress ) ) {
				$dwCIDR = $anDwCIDR;
				break;
			}
		}
		/*
		if ( \is_null( $dwCIDR ) )
			$dwCIDR = $this->createCIDR(
				$ipAddress."/".( is_null( $ipCidr ) ?
					( DwCIDR::IsInside( "127.0.0.0/8" ,$ipAddress ) ? "8" : "24" )
					:
					$ipCidr )
			);
		//*/
		
		return $dwCIDR;
	}
	// $dwPort = $dwInterface->getRandomIpPort();
	public function getRandomIpPort( ) : DwPort {
		$dwPort = null;
		// DwCidr[]
		$cirds = $this->getCIDRs();
		// DwIp => DwPort
		if ( is_array( $cirds) && isset( $cirds[0] ) && is_object($cirds[0]) )
			$dwPort = $cirds[0]->randomIp()->getPort( \rand( 1024 ,65534 ) );//8085
		else
			throw new \Exception("no one CIDRS inside :".$this->getIfaceName() );
		return $dwPort;
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

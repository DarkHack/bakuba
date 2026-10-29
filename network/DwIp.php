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
//use bakuba\kernel\network\DwIp as DwIp;

abstract class DwIp extends \darkphp\serializable\aDwSerializable {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		/*
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,$this->getDwInterface()->getIfaceName()
				,\str_replace( '/', '-', $this->CIDR6 )
			);
		else
		//*/
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
		foreach($this->ports as $anDwPort) {
			$anDwPort->setDebug( $this->log );
		}
	}
	
	
	protected $ipAddress = null;
	public function getIpAddress() : string {
		return $this->ipAddress;
	}
	private $crc32 = null;
	public function getCrc32() : int {
		if ( is_null( $this->crc32 ) ) $this->crc32 = \crc32($this->ipAddress);
		return $this->crc32;
	}
	/*
	protected $ip6 = null;
	public function isV6() :bool {
		if ( is_null( $this->ip6 ) )
			return false;
		return $this->ip6;
	}
	//*/
	protected $ports=array();
	
	public function __construct( DwCIDR $parent ,string $ipAddress ) {
		$this->ipAddress = $ipAddress;
		parent::__construct( $parent );
		//$this->log->trace(".");
	}
	
	private function parse( $ipAddress ) {
		// Strip out the netmask, if there is one.
		$cx = strpos($ipaddr, '/');
		if ($cx) {
			$subnet = (int)(substr($ipaddr, $cx+1));
			$ipaddr = substr($ipaddr, 0, $cx);
		} else $subnet = null; // No netmask present
		
		// Convert address to packed format
		$addr = inet_pton($ipaddr);
		
		// Let's display it as hexadecimal format
		foreach(str_split($addr) as $char) echo str_pad(dechex(ord($char)), 2, '0', STR_PAD_LEFT);
		echo "<br />\n";
		
		// Convert the netmask
		if (is_integer($subnet)) {
			// Maximum netmask length = same as packed address
			$len = 8*strlen($addr);
			if ($subnet > $len) $subnet = $len;
			// Create a hex expression of the subnet mask
			$mask  = str_repeat('f', $subnet>>2);
			switch($subnet & 3) {
			case 3: $mask .= 'e'; break;
			case 2: $mask .= 'c'; break;
			case 1: $mask .= '8'; break;
			}
			$mask = str_pad($mask, $len>>2, '0');
			
			// Packed representation of netmask
			$mask = pack('H*', $mask);
		}
		
		// Display the netmask as hexadecimal
		foreach(str_split($mask) as $char) echo str_pad(dechex(ord($char)), 2, '0', STR_PAD_LEFT);
	}
	public function getDwCIDR() : DwCIDR {
		return $this->parent;
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize("ipAddress");
		//$this->addtoSerialize("ports");
		
	}
	public function getPort( int $port = 80 ) : DwPort {
		//$this->log->trace(".");
		if ( ! isset( $this->ports[$port] ) ) {
			$dwPort = new DwPort( $this, $port );
			//$this->insert($dwPort);
			$this->ports[$port]=$dwPort;
		}
		return $this->ports[$port];
	}
	public static function mask32(
		string $hexNet
		,int $mask
		,string $hexIp
	) : bool {
//		echo " net:".$hexNet."\r\n";
//		echo "mask:".$mask."\r\n";
//		echo "  ip:".$hexIp."\r\n";
		$ip_net = hexdec($hexNet);
		$ip_ip = hexdec($hexIp);
		$ip_mask = ~((1 << (32 - $mask)) - 1);
//		echo " net binary:".sprintf("%032b",$ip_net)."\r\n";
//		echo "mask binary:".substr(sprintf("%032b",$ip_mask),32)."\r\n";
//		echo "  ip binary:".sprintf("%032b",$ip_ip)."\r\n";
		$result = (($ip_ip & $ip_mask) == ($ip_net & $ip_mask));
//		echo "     result:".($result?"oui":"non")."\r\n";
		return $result;
	}
	// PERPLEXITY Production :
	//\bakuba\kernel\network\DwIp::parseIp( '127.0.0.1' ) || \bakuba\kernel\network\DwIp::parseIp( '::1' )
	public static function parseIp( string $parseIp ,DwInterface $dwInterface ) {
		$dwIp=null;
		// Découper l'adresse et le netmask le cas échéant
		$parts = explode('/', $parseIp);
		$addr = $parts[0];
		//$mask = isset($parts[1]) ? intval($parts[1]) : null;
		if ( \filter_var( $addr ,\FILTER_VALIDATE_IP ,\FILTER_FLAG_IPV4 ) ) {
			// Instancier DwIp4
			$dwIp=\bakuba\kernel\network\DwIp4::parseIp( $addr ,$dwInterface );
		} elseif ( \filter_var( $addr ,\FILTER_VALIDATE_IP ,\FILTER_FLAG_IPV6 ) ) {
			// Instancier DwIp6
			$dwIp=\bakuba\kernel\network\DwIp6::parseIp( $addr ,$dwInterface );
		}
		return $dwIp;
	}
	public function getIpPath() : string {
		return \str_replace( array( '.' ,':' ,'/' ),'-', $this->getIpAddress() );
	}
	
	public function toString( $withParent = false ) : string {
		if ( ! isset( $this->ipAddress ) || \is_null( $this->ipAddress ) )
			return parent::toString( $withParent );
		//if ( $this->ip6 )
		//	return "[".$this->ipAddress."]";
		//else
			return $this->ipAddress;
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
}

?>

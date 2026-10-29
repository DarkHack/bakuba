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
//use bakuba\kernel\network\DwCIDR as DwCIDR;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;

abstract class DwCIDR extends DwObject {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$ifaceName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->getDwInterface()->getIfaceName());
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,$ifaceName
				,\str_replace( array( '.' ,':' ,'/' ), '-', $this->CIDR )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
		foreach($this->ips as $anDwIp) {
			$anDwIp->setDebug( $this->log );
		}
	}
	
	protected $CIDR = null;
	protected $ips=array();
	
	public function __construct( DwInterface $parent ,string $CIDR ) {
		$this->CIDR = $CIDR;
		parent::__construct( $parent );
	}
	public function getDwInterface() : DwInterface {
		return $this->parent;
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize("CIDR");
		$this->addtoSerialize("ips");
	}
	public function getIps() : array {
		return $this->ips;
	}
	
	function addGw( string $ip ) : DwIp {
		$dwIp = $this->getIp( $ip );
		
		if ( \is_object( $this->log ) )
			$this->log->trace( $dwIp );
		
		return $dwIp;
	}
	function inside( $dwIp )  : bool {
		$this->log->info( $dwIp );
		$result = self::IsInside( $this->CIDR, $dwIp );
		if ( \is_object( $this->log ) )
			$this->log->trace( "dwIp: $dwIp =>".( $result ? "inside"  : "not inside" )." " );
		
		return $result;
	}
	/*
	//DwCIDR::inside("192.168.1.0/24", "192.168.1.23") //
	static function IsInside( string $cidr, string $ip ) : bool {
		$result = null;
		list ($net, $mask) = explode ('/', $cidr);
		//$ip_net = ip2long($net);
		$ip_net = hexdec(bin2hex(inet_pton($net)));
		$ip_mask = ~((1 << (32 - $mask)) - 1);
		//$ip_ip = ip2long($ip);
		$ip_ip = hexdec(bin2hex(inet_pton($ip)));
		$result = (($ip_ip & $ip_mask) == ($ip_net & $ip_mask));
		return $result;
	}
	//*/
	//DwCIDR::IsInside("::1/128", "::1") //
	static function IsInside( string $cidr, string $ip ) : bool {
		$isInside=true;
		list ($net, $mask) = explode ('/', $cidr);
		$hexNet = bin2hex(inet_pton($net));
		$hexIp = bin2hex(inet_pton($ip));
		$isNetV6 = strlen( $hexNet ) > 8;
		$isIpV6 = strlen( $hexIp ) > 8;
		$isV6 = $isNetV6 && $isIpV6;
		if ( $isNetV6 != $isIpV6 )
			$isInside=false;	
		elseif ( $isV6 ) {
			$arrNet = str_split( $hexNet ,8 );
			$arrIp = str_split( $hexIp ,8 );
			$mymask=null;
			for( $index=0 ;$index<count($arrNet) ;$index++ ) {
				if ( $mask >= 32 )
					$mymask=32;
				else
					$mymask=$mask;
				$mask=$mask - $mymask;
				$isInside&=DwIp::mask32(
					$arrNet[ $index ]
					,$mymask
					,$arrIp[ $index ]
				);
			}
		} else {
			$isInside&=DwIp::mask32(
				$hexNet
				,$mask
				,$hexIp
			);
		}
		/*
		if ( $isInside )
			echo " $ip in $cidr.\r\n";
		else
			echo " $ip not in $cidr.\r\n";
		//*/
		return $isInside;
	}
	/*
	public function toString( $withParent = false ) : string {
		return $this->CIDR;
	}
	//*/
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

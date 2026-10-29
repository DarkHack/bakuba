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
//use bakuba\kernel\network\DwIp4 as DwIp4;

class DwIp4 extends DwIp {
	// bakuba\kernel\network\DwIp4::$ipv4RegExp
	public static $ipv4RegExp='([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})';
	// \bakuba\kernel\network\DwIp4::parseIps( $dwInterface ,$ipAddress )
	
	// \bakuba\kernel\network\DwIp4::parseIps( $dwInterface ,$dataIpAddress );
	public static function parseIps( DwInterface $dwInterface ,string $dataIpAddress ) : array {
		$found = array();
		if ( preg_match_all(
			'/'.self::$ipv4RegExp.'.*/m'
			,$dataIpAddress
			,$matchesIP
			,PREG_SET_ORDER
			,0
		) >= 0 ) {
			foreach( $matchesIP as $foundedIP ) {
				$ipAdress = $foundedIP[1];
				$dwCIDR = $dwInterface->getCIDR( $ipAdress );
			}
		}
	}
	// \bakuba\kernel\network\DwIp4::parseIp( $strIpAddress ,$dwInterface )
	public static function parseIp( string $dataIpAddress ,DwInterface $dwInterface = null ) : ?self {
		$dwIp=null;
		if ( preg_match_all(
			'/'.self::$ipv4RegExp.'.*/m'
			,$dataIpAddress
			,$matchesIP
			,PREG_SET_ORDER
			,0
		) >= 0 ) {
			if ( is_null( $dwInterface ) ) {
				$dwCIDR4 = new \bakuba\kernel\network\DwCIDR4( null, $dataIpAddress."/32" );
			} else {
				$dwCIDR4 = $dwInterface->getCIDR( $dataIpAddress );
				if ( is_null( $dwCIDR4 ) )
					$dwCIDR4 = $dwInterface->createCIDR( $dataIpAddress."/32" );
			}
			$dwIp=new self( $dwCIDR4 ,$dataIpAddress );
		}
		return $dwIp;
	}
	//*/
	//
	public function __construct( DwCIDR4 $parent ,string $ipAddress ) {
		parent::__construct( $parent ,$ipAddress );
		//$this->log->trace(".");
	}
	public function rev() : string {
		$reversed=null;
		foreach( \array_reverse( explode( '.' ,$this->ipAddress ) ) as $ipDec )
			if ( is_null($reversed) ) $reversed=$ipDec;else $reversed.=".{$ipDec}";
		return $reversed;
	}
	public function revDNS() : string {
		return $this->rev().'.in-addr.arpa';
	}
	public function getIpPath() : string {
		return \str_replace( array( '.' ,':' ,'/' ),'-', $this->getIpAddress() );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

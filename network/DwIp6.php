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
//use bakuba\kernel\network\DwIp6 as DwIp6;

class DwIp6 extends DwIp {
	
	/*
	(
([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|          # 1:2:3:4:5:6:7:8
([0-9a-fA-F]{1,4}:){1,7}:|                         # 1::                              1:2:3:4:5:6:7::
([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|         # 1::8             1:2:3:4:5:6::8  1:2:3:4:5:6::8
([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|  # 1::7:8           1:2:3:4:5::7:8  1:2:3:4:5::8
([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|  # 1::6:7:8         1:2:3:4::6:7:8  1:2:3:4::8
([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|  # 1::5:6:7:8       1:2:3::5:6:7:8  1:2:3::8
([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|  # 1::4:5:6:7:8     1:2::4:5:6:7:8  1:2::8
[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|       # 1::3:4:5:6:7:8   1::3:4:5:6:7:8  1::8  
:((:[0-9a-fA-F]{1,4}){1,7}|:)|                     # ::2:3:4:5:6:7:8  ::2:3:4:5:6:7:8 ::8       ::     
fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|     # fe80::7:8%eth0   fe80::7:8%1     (link-local IPv6 addresses with zone index)
::(ffff(:0{1,4}){0,1}:){0,1}
((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}
(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|          # ::255.255.255.255   ::ffff:255.255.255.255  ::ffff:0:255.255.255.255  (IPv4-mapped IPv6 addresses and IPv4-translated addresses)
([0-9a-fA-F]{1,4}:){1,4}:
((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}
(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])           # 2001:db8:3:4::192.0.2.33  64:ff9b::192.0.2.33 (IPv4-Embedded IPv6 Address)
)
	//*/
	//private static $ipv6RegExp='(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))';
	//DwIp6::$ipv6RegExp
	public static $ipv6RegExp='((?:[a-f0-9:]+:+)+[a-f0-9]+)';
	// \bakuba\kernel\network\DwIp4::parseIps( $dwInterface ,$ipAddress )
	public static function parseIps( DwInterface $dwInterface ,string $ipAddress ) : array {
		$found = array();
		if ( preg_match_all(
		$ipv6 ?
							'/'.DwIp6::$ipv6RegExp.'.*/m'
						:
							'/([0-9]{0,3}\.[0-9]{0,3}\.[0-9]{0,3}\.[0-9]{0,3}).*/m'
						,$founded[4]
						,$matchesIP
						,PREG_SET_ORDER
						,0
					) >= 0 )
						foreach( $matchesIP as $foundedIP ) {
							//$this->log->trace( "Dns SRV:".$foundedIP[1] );
							$dataDns['server'][]=$foundedIP[1];
						}
	}
	
	// \bakuba\kernel\network\DwIp6::parseIp( $strIpAddress ,$dwInterface )
	public static function parseIp( string $dataIpAddress ,DwInterface $dwInterface = null ) : ?self {
		$dwIp=null;
		if ( preg_match_all(
			'/'.self::$ipv6RegExp.'.*/m'
			,$dataIpAddress
			,$matchesIP
			,PREG_SET_ORDER
			,0
		) >= 0 ) {
			if ( is_null( $dwInterface ) ) {
				$dwCIDR6 = new \bakuba\kernel\network\DwCIDR6( null, $dataIpAddress."/128" );
			} else {
				$dwCIDR6 = $dwInterface->getCIDR6( $dataIpAddress );
				if ( is_null( $dwCIDR6 ) )
					$dwCIDR6 = $dwInterface->createCIDR6( $dataIpAddress."/128" );
			}
			$dwIp=new self( $dwCIDR6 ,$dataIpAddress );
		}
		return $dwIp;
	}
	public function __construct( DwCIDR6 $parent ,string $ipAddress ) {
		parent::__construct( $parent ,$ipAddress );
		//$this->log->trace(".");
	}
	public function rev() : string {
		$addr = inet_pton( $this->ipAddress );
		
		// Let's display it as hexadecimal format
		$reversed=null;
		foreach(array_reverse( str_split($addr) ) as $char)  {
			foreach( array_reverse(
				str_split( str_pad(dechex(ord($char)), 2, '0', STR_PAD_LEFT) )
			) as $hex ) {
				if ( is_null( $reversed ) )
					$reversed="";
				else
					$reversed.=".";
				$reversed.=$hex;
			}
		}
		return $reversed;
	}
	public function revDNS() : string {
		return $this->rev().'.ip6.arpa';
	}
	public function getIpPath() : string {
		return \str_replace( array( ':' ,'/' ),'-', $this->getIpAddress() );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

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

function mask32( string $hexNet ,int $mask ,string $hexIp ) : bool {
//	echo " net:".$hexNet."\r\n";
//	echo "mask:".$mask."\r\n";
//	echo "  ip:".$hexIp."\r\n";
	
	$ip_net = hexdec($hexNet);
	$ip_ip = hexdec($hexIp);
	$ip_mask = ~((1 << (32 - $mask)) - 1);
	
//	echo " net binary:".sprintf("%032b",$ip_net)."\r\n";
//	echo "mask binary:".substr(sprintf("%032b",$ip_mask),32)."\r\n";
//	echo "  ip binary:".sprintf("%032b",$ip_ip)."\r\n";
	$result = (($ip_ip & $ip_mask) == ($ip_net & $ip_mask));
//	echo "     result:".($result?"oui":"non")."\r\n";
	return $result;
}
function IsInside( string $cidr, string $ip ) : bool {
	$isInside=true;
	$result = null;
	list ($net, $mask) = explode ('/', $cidr);
	$hexNet = bin2hex(inet_pton($net));
	$hexIp = bin2hex(inet_pton($ip));
	$isV6 = strlen( $hexNet ) > 8;
	if ( $isV6 ) {
		$arrNet = str_split( $hexNet ,8 );
		$arrIp = str_split( $hexIp ,8 );
		$mymask=null;
		for( $index=0 ;$index<count($arrNet) ;$index++ ) {
			if ( $mask >= 32 )
				$mymask=32;
			else
				$mymask=$mask;
			$mask=$mask - $mymask;
			$isInside&=mask32(
				$arrNet[ $index ]
				,$mymask
				,$arrIp[ $index ]
			);
		}
	} else {
		$isInside=mask32(
			$hexNet
			,$mask
			,$hexIp
		);
	}
	if ( $isInside )
		echo " $ip in $cidr.\r\n";
	else
		echo " $ip not in $cidr.\r\n";
	return $isInside;
}
//echo var_dump( IsInside( "192.168.12.1/32" ,"192.168.12.0" ) );
//echo var_dump( IsInside( "127.0.0.0/8" ,"127.168.12.1" ) );
//echo var_dump( IsInside( "255.255.255.255/0" ,"127.127.127.127" ) );
echo var_dump( IsInside( "fe80::/64" ,"fe80::6359:2760:eab1:cc13" ) );
echo var_dump( IsInside( "::/127" ,"::2" ) );

//echo decbin(hex2bin('FF00FFFFFFFF'));
?>

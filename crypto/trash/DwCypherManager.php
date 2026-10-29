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

namespace bakuba\kernel\dwCrypto;
use \Exception as Exception;
// use bakuba\kernel\dwCrypto\DwCypherManager as DwCypherManager;
class DwCypherManager {
	private static $ciphersOpMode = array( "cbc", "cfb", "cfb1", "cfb8", "ofb", "xts" );
	private static $ciphersOpModeDeprecated = array( "ecb", "ctr", "ccm", "gcm" );
	// aes mode		: cbc/cfb/cfb1/cfb8/ctr/ecb/ofb/xts/ccm/gcm
	// camelia mode	: cbc/cfb/cfb1/cfb8/ecb/ofb
	private static $ciphers256 = array(
	 "AES-256-CBC", "aes-256-cbc"
	,"AES-256-CFB", "aes-256-cfb"
	,"AES-256-CFB1", "aes-256-cfb1"
	,"AES-256-CFB8", "aes-256-cfb8"
	,"AES-256-CTR", "aes-256-ctr"
	,"AES-256-ECB", "aes-256-ecb"
	,"AES-256-OFB", "aes-256-ofb"
	,"AES-256-XTS", "aes-256-xts"
	
	,"aes-256-ccm"
	,"aes-256-gcm"
	
	,"CAMELLIA-256-CBC", "camellia-256-cbc"
	,"CAMELLIA-256-CFB", "camellia-256-cfb"
	,"CAMELLIA-256-CFB1", "camellia-256-cfb1"
	,"CAMELLIA-256-CFB8", "camellia-256-cfb8"
	,"CAMELLIA-256-ECB", "camellia-256-ecb"
	,"CAMELLIA-256-OFB", "camellia-256-ofb"
	);
	// keySize 32 à 448
	private static $ciphersBlowFish = array(
	 "BF-CBC", "bf-cbc"
	,"BF-CFB", "bf-cfb"
	,"BF-ECB", "bf-ecb"
	,"BF-OFB", "bf-ofb"
	);
	
	private static $ciphers192 = array(
	 "AES-192-CBC", "aes-192-cbc"
	,"AES-192-CFB", "aes-192-cfb"
	,"AES-192-CFB1", "aes-192-cfb1"
	,"AES-192-CFB8", "aes-192-cfb8"
	,"AES-192-CTR", "aes-192-ctr"
	,"AES-192-ECB", "aes-192-ecb"
	,"AES-192-OFB", "aes-192-ofb"
	
	,"aes-192-ccm"
	,"aes-192-gcm"
	
	,"CAMELLIA-192-CBC", "camellia-192-cbc"
	,"CAMELLIA-192-CFB", "camellia-192-cfb"
	,"CAMELLIA-192-CFB1", "camellia-192-cfb1"
	,"CAMELLIA-192-CFB8", "camellia-192-cfb8"
	,"CAMELLIA-192-ECB", "camellia-192-ecb"
	,"CAMELLIA-192-OFB", "camellia-192-ofb"
	);
	
	private static $ciphers128 = array(
	 "CAMELLIA-128-CBC", "camellia-128-cbc"
	,"CAMELLIA-128-CFB", "camellia-128-cfb"
	,"CAMELLIA-128-CFB1", "camellia-128-cfb1"
	,"CAMELLIA-128-CFB8", "camellia-128-cfb8"
	,"CAMELLIA-128-ECB", "camellia-128-ecb"
	,"CAMELLIA-128-OFB", "camellia-128-ofb"
	
	,"AES-128-CBC", "aes-128-cbc"
	,"AES-128-CFB", "aes-128-cfb"
	,"AES-128-CFB1", "aes-128-cfb1"
	,"AES-128-CFB8", "aes-128-cfb8"
	,"AES-128-CTR", "aes-128-ctr"
	,"AES-128-ECB", "aes-128-ecb"
	,"AES-128-OFB", "aes-128-ofb"
	,"AES-128-XTS", "aes-128-xts"
	
	,"aes-128-ccm"
	,"aes-128-gcm"
	
	,"CAST5-CBC", "cast5-cbc"
	,"CAST5-CFB", "cast5-cfb"
	,"CAST5-ECB", "cast5-ecb"
	,"CAST5-OFB", "cast5-ofb"
	
	,"IDEA-CBC", "idea-cbc"
	,"IDEA-CFB", "idea-cfb"
	,"IDEA-ECB", "idea-ecb"
	,"IDEA-OFB", "idea-ofb"
	
	,"SEED-CBC", "seed-cbc"
	,"SEED-CFB", "seed-cfb"
	,"SEED-ECB", "seed-ecb"
	,"SEED-OFB", "seed-ofb"
	);
	
	private static $ciphersDeprecated = array(
	 "DES-CBC", "des-cbc"
	,"DES-CFB", "des-cfb"
	,"DES-CFB1", "des-cfb1"
	,"DES-CFB8", "des-cfb8"
	,"DES-ECB", "des-ecb"
	
	,"DES-EDE", "des-ede"
	,"DES-EDE-CBC", "des-ede-cbc"
	,"DES-EDE-CFB", "des-ede-cfb"
	,"DES-EDE-OFB", "des-ede-ofb"
	
	,"DES-EDE3", "des-ede3"
	,"DES-EDE3-CBC", "des-ede3-cbc"
	,"DES-EDE3-CFB", "des-ede3-cfb"
	,"DES-EDE3-CFB1", "des-ede3-cfb1"
	,"DES-EDE3-CFB8", "des-ede3-cfb8"
	,"DES-EDE3-OFB", "des-ede3-ofb"
	
	,"DES-OFB", "des-ofb"
	,"DESX-CBC", "desx-cbc"
	
	,"RC2-40-CBC", "rc2-40-cbc"
	,"RC2-64-CBC", "rc2-64-cbc"
	,"RC2-CBC", "rc2-cbc"
	,"RC2-CFB", "rc2-cfb"
	,"RC2-ECB", "rc2-ecb"
	,"RC2-OFB", "rc2-ofb"
	
	,"RC4", "rc4"
	,"RC4-40", "rc4-40"
	,"RC4-HMAC-MD5", "rc4-hmac-md5"
	);
	
	private static $ciphersUnknow = array(
	 "id-aes128-CCM"
	,"id-aes128-GCM"
	,"id-aes128-wrap"
	
	,"id-aes192-CCM"
	,"id-aes192-GCM"
	,"id-aes192-wrap"
	
	,"id-aes256-CCM"
	,"id-aes256-GCM"
	,"id-aes256-wrap"
	,"id-smime-alg-CMS3DESwrap"
	);
	
	public static function filterPreferedCiphers( $ciphers ) {
		$ciphersPrefered = array();
		foreach( $ciphers as $cipher ) {
			$cipherMode = substr( $cipher, -3);
			/*
			if ( in_array( $cipherMode, self::$ciphersOpModeDeprecated )
			|| in_array( $cipher, self:: $ciphersDeprecated )
			|| in_array( $cipher, self:: $ciphersUnknow )
			|| in_array( $cipher, self:: $ciphersBlowFish ) // may be later, work about blowfish methods, but not now
			)
				continue;
			//*/
			
			if ( ( in_array( $cipher, self::$ciphers256 ) 
			|| in_array( $cipher, self:: $ciphers128 )
			|| in_array( $cipher, self:: $ciphers192 )
			) && in_array( $cipherMode, self::$ciphersOpMode ) )
				$ciphersPrefered[] = $cipher;
		}
		return $ciphersPrefered;
	}
	
	public static function intersectPreferedCiphers( $ciphersSrv, $ciphersClt ) {
		$ciphersSrvFiltered=self::filterPreferedCiphers( $ciphersSrv );
		if ( empty( $ciphersSrvFiltered ) )
			throw new Exception( "intersectPreferedCiphers ciphersSrvFiltered.\n".print_r( $ciphersSrv,true ) );
		$ciphersCltFiltered=self::filterPreferedCiphers( $ciphersClt );
		if ( empty( $ciphersCltFiltered ) )
			throw new Exception( "intersectPreferedCiphers ciphersCltFiltered.\n".print_r( $ciphersClt,true ) );
		$array=array();
		foreach( array_intersect( $ciphersSrvFiltered, $ciphersCltFiltered ) as $key => $cipher)
			$array[]=$cipher;
		return $array;
	}
	
	public static function selectAnCipher( $ciphersSrv, $ciphersClt ) {
		$commonCiphers = self::intersectPreferedCiphers( $ciphersSrv, $ciphersClt );
		if ( empty( $commonCiphers ) )
			throw new Exception( "selectAnCipher no cipher.\n".print_r( array( "srv" => $ciphersSrv, "clt" => $ciphersClt, "cmt" => $commonCiphers ),true ) );
		return $commonCiphers[ rand( 0, ( count($commonCiphers ) - 1 ) ) ];
	}
}
?>

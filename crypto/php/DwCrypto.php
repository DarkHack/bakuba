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

namespace bakuba\kernel\crypto\php;
// use bakuba\kernel\crypto\php\DwCrypto as DwCrypto;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;

class DwCrypto extends \bakuba\kernel\crypto\aDwCrypto {
	
	public function generateSaltKeyIv( DwCallBack $dwCallBack, string $cipher , string $pass ): void {
		$dwCallBack->callback( openssl_cipher_iv_length( $cipher ) );
	}
	/*
	public function openssl_deriv_key_iv($password, $salt, $key_len, $iv_len, $digest = 'md5') {
		$dtot = $d = '';
		while (strlen($dtot) < ($key_len + $iv_len)) {
			$d = hash($digest, $d . $password . $salt, true);
			$dtot .= $d;
		}
		$key = substr($dtot, 0, $key_len);
		$iv = substr($dtot, $key_len, $iv_len);
		return array($key, $iv);
	}
	// Utilisation :
	$password = 'toto';
	$salt = hex2bin('57B0DB81CFE460B8'); // changer pour chaque usage !
	$key_len = 16; // AES-128
	$iv_len = 16; // CBC
	list($key, $iv) = openssl_deriv_key_iv($password, $salt, $key_len, $iv_len);
	// $key et $iv exactement comme openssl enc -aes-128-cbc -P -pass pass:toto -S 57B0DB81CFE460B8
	//*/

	// DwCrypto::listCiphers();
	public function listCiphers( DwCallBack $dwCallBack ) : void {
		$this->getDwResources()->addTick( new DwCallBack( array( &$this, 'runListCiphers' ) ,$dwCallBack ) );
	}
	public function runListCiphers( DwCallBack $dwCallBack ) : void {
		$dwCallBack->callback( self::removeBadCiphers( \openssl_get_cipher_methods() ) );
	}
	// DwCrypto::listDigests();
	public function listDigests( DwCallBack $dwCallBack ) : void {
		$this->getDwResources()->addTick( new DwCallBack( array( &$this, 'runListDigests' ) ,$dwCallBack ) );
	}
	public function runListDigests( DwCallBack $dwCallBack ) : void {
		$dwCallBack->callback( \openssl_get_md_methods() );
	}
	
	//DwCrypto::initMessage
	public function initMessage( DwCallBack $dwCallBack ,array $ciphers = null, array $digests = null ) : void {
		$this->getDwResources()->addTick( new DwCallBack( array( &$this, 'runInitMessage' ), $dwCallBack ,$ciphers ,$digests ) );
	}
	public function runInitMessage( DwCallBack $dwCallBack ,array $ciphers = null, array $digests = null ) : void {
		$dwJsonCrypto = new DwJsonCrypto();
		
		if ( is_null( $ciphers ) ) 
			$dwJsonCrypto->{"cipher"}=self::$defaultCipher;
		else
			$dwJsonCrypto->{"cipher"}=\darkphp\DwArray::randomValueFromArray( $ciphers );
		//$dwJsonCrypto->{"ciphers"}=implode(':',$ciphers);
		
		if ( is_null( $digests ) )
			$dwJsonCrypto->{"digest"}=self::$defaultDigest;
		else
			$dwJsonCrypto->{"digest"}=\darkphp\DwArray::randomValueFromArray( $digests );
		//$dwJsonCrypto->{"digests"}=implode(':',$digests);
		
		$this->log->trace( "c:".$dwJsonCrypto->{"cipher"}." ,d:".$dwJsonCrypto->{"digest"} );
		
		$dwJsonCrypto->{"pass"} = \bakuba\kernel\randomizer\DwRandomizer::randomize( 10 );
		$dwJsonCrypto->{"salt"} = \bakuba\kernel\randomizer\DwRandomizer::randomize( 8 );
		throw new \Exception( "should generate Key Iv Salt" );
		//$cipherIvSize = self::getCipherIVSize( $dwJsonCrypto->{"cipher"} );
		$cipherKeySize = $cipherIvSize;
		$dwJsonCrypto->{"iv"} = \bakuba\kernel\randomizer\DwRandomizer::randomize( $cipherIvSize );
		$dwJsonCrypto->{"key"} = \bakuba\kernel\randomizer\DwRandomizer::randomize( $cipherKeySize );
		
		//$dwJsonCrypto = DwJsonCrypto::fromJson($strInitMessage);
		
		$dwCallBack->callback( $dwJsonCrypto );
	}
	
	public function encrypt( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : void {
		$this->getDwResources()->addTick( new DwCallBack( array( &$this, 'runEncrypte' ), $dwCallBack ,$dwJsonCrypto ) );
	}
	public function runEncrypte( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : void {
		$dataEncrypted = null;
		$cipher=$dwJsonCrypto->{"cipher"};
		$digest=$dwJsonCrypto->{"digest"};
		$pass=$dwJsonCrypto->{"pass"};
		//$salt=$dwJsonCrypto->{"salt"};
		$iv=$dwJsonCrypto->{"iv"};
		$key=$dwJsonCrypto->{"key"};
		echo __CLASS__."::".__METHOD__."( $cipher ,$digest )\r\n";
		if ( stripos( $cipher ,"gcm" )===false && stripos( $cipher ,"ccm" )=== false ) {
			$dataEncrypted = base64_encode( openssl_encrypt ( base64_encode( $dwJsonCrypto->{"data"} ) ,$cipher ,$key ,$options = 0 ,$iv ) );
		} else {
			$tag="";
			$encrypted = base64_encode( openssl_encrypt ( base64_encode( $dwJsonCrypto->{"data"} ) ,$cipher ,$key ,$options = 0 ,$iv ,$tag ) );
			//mode cipher AEAD (GCM ou CCM) :[, string &$tag = NULL [, string $aad = "" [, int $tag_length = 16 ]]]
			$dataEncrypted = $encrypted.":".base64_encode($tag);
		}
		//$key = openssl_digest($pass, $digest);//: string|false
		
        //$app_key = hash( $digest, $pass);
        //$iv = substr(hash($digest, $iv),0,16);
		$dwCallBack->callback( $dataEncrypted );
	}
	public function decrypt( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : void {
		$this->getDwResources()->addTick( new DwCallBack( array( &$this, 'runDecrypte' ), $dwCallBack ,$dwJsonCrypto ) );
	}
	public function runDecrypte( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : string {
		$cipher=$dwJsonCrypto->{"cipher"};
		$digest=$dwJsonCrypto->{"digest"};
		$pass=$dwJsonCrypto->{"pass"};
		//$salt=$dwJsonCrypto->{"salt"};
		$iv=$dwJsonCrypto->{"iv"};
		$key=$dwJsonCrypto->{"key"};
		echo __CLASS__."::".__METHOD__."( $cipher ,$digest )\r\n";
		if ( stripos( $cipher ,"gcm" )===false && stripos( $cipher ,"ccm" )=== false )
			$uncrypted = base64_decode( openssl_decrypt( base64_decode( $dwJsonCrypto->{"data"} ) ,$cipher ,$key ,$options = 0 ,$iv ) );
		else {
			$dataTag = explode( ":" ,$dwJsonCrypto->{"data"} );
			$data=$dataTag[0];
			$tag=base64_decode($dataTag[1]);
			//$key = openssl_digest($pass, $digest);//: string|false
			//$app_key = hash( $digest ,$pass );
			//$iv = substr( hash($digest ,$iv ) ,0 ,16 );
			$uncrypted = base64_decode( openssl_decrypt( base64_decode( $data ) ,$cipher ,$key ,$options = 0 ,$iv ,$tag ) );
			//mode cipher AEAD (GCM ou CCM) :[, string &$tag = NULL [, string $aad = "" [, int $tag_length = 16 ]]]
		}
		$dwCallBack->callback( $uncrypted );
	}
	
	/*
	public static function encoder( $plainText, $method, $key, $iv ) {
		$encrypted="";
		
		$border="";for($i=0;$i<500;$i++) $border.=" ";
		$data = bin2hex($border."|80859095|".$plainText."|80859095|".$border);
		$encrypted = openssl_encrypt ( $data ,$method ,$key ,$options = 0 ,$iv );//mode cipher AEAD (GCM ou CCM) :[, string &$tag = NULL [, string $aad = "" [, int $tag_length = 16 ]]]
		
		return $encrypted;
	}
	
	public static function decoder( $encrypted, $method, $key, $iv ) {
		$decrypted="";
		
		$decrypted = @pack("H*" ,trim(openssl_decrypt( $encrypted, $method, $key, $options=0, $iv )));
		$eclate=preg_split("/\|80859095\|/i",$decrypted);
		$plainText=@$eclate[1];
		
		return $plainText;
	}
	//*/
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	private static $ciphers = null;
	private static $digests = null;
	private static $defaultCipher = "aes-128-cbc";
	private static $defaultDigest = "sha256";
	
	
	/*
	public static function getCipherKeySize( string $cipher ) : int {
		return openssl_cipher_key_length( $cipher );
	}
	*/
	private static $badCiphers = array(
		"id-"
		,"aria-"
		,"sm4-"
//		,"BF-CFB"
//		,"CAMELLIA-128-CFB8"
//		,'CAMELLIA-192-CFB1'
		,"DES-EDE3-CFB1"
		,"aes-256-cbc-hmac-sha1"
		,"aes-256-cbc-hmac-sha256"
		,"aes-128-cbc-hmac-sha256"
		,"aes-256-ccm"
		,"aes-192-ccm"
		,"aes-128-ccm"
		/*
		,"id-smime-alg-CMS3DESwrap"
		,"id-aes128-wrap"
		,"id-aes256-wrap"
		//*/
	);// CAMELLIA-128-OFB
	
	private static function removeBadCiphers( array $allCiphers ) : array {
		$ciphers = array();
		foreach ( $allCiphers as $akey => $cipher ) {
			$isBadCipher = false;
			foreach( self::$badCiphers as $bkey => $badCipher ) {
				if ( stripos( $cipher ,$badCipher )===false )
					$isBadCipher = false;
				else
					$isBadCipher = true;
				//echo "\r\n".__CLASS__." reject bad cipher :\r\n$cipher ,$badCipher\r\n";
				
				if ( $isBadCipher )
					break ;
			}
			if ( ! $isBadCipher )
				$ciphers[] = $cipher;
		}
		//echo "\r\n".__CLASS__."::".__METHOD__." : ".\count( $ciphers )."/".\count( $allCiphers )."\r\n";
		
		return $ciphers;
	}
}
?>

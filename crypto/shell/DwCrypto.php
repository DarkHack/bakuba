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

namespace bakuba\kernel\crypto\jsonShell;
//use bakuba\kernel\crypto\jsonShell\DwCryptoShell as DwCryptoShell;

use \Exception as Exception;
use \Error as Error;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\shell\DwShell as DwShell;
use bakuba\kernel\shell\DwShellCrypt as DwShellCrypt;

use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;

use bakuba\kernel\process\DwProcessShell as DwProcessShell;
class DwCryptoShell {
		
	public static function initMessage(
		$ciphers
		,$digests
		,DwProcessShell $dwProcessShell= null
		,DwCallBack $dwDoneCallBack = null
	) : ?DwJsonCrypto {
		if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
		$dwJsonCrypto = null; 
		try {
			self::$sLog->info( ". ".(is_array( $digests ) ? implode('/',$digests):$digests)."\r\n".(is_array( $ciphers ) ? implode('/',$ciphers):$ciphers) );
			$shellCiphers = $ciphers;
			if ( is_array( $ciphers ) )
				$shellCiphers = implode( ":", $ciphers );
			$shellDigests = $digests;
			if ( is_array( $digests ) )
				$shellDigests = implode( ":", $digests );
			
			$dwCallBack = null;
			if ( is_object( $dwDoneCallBack ) ) {
				$dwCallBack = new DwCallBack(
					array( self::getStaticClassName() ,"initiedMessage" )
					,$dwDoneCallBack
					,$shellCiphers
					,$shellDigests
				);
			}
			$scriptContent = "shCryptInitMessage \"$shellCiphers\" \"$shellDigests\"";
			
			if ( is_null( $dwProcessShell ) || is_null($dwCallBack) ) {
				$strInitMessage = DwShell::exec(
					DwShellCrypt::getInitScript().$scriptContent." 2>/dev/null"
				);
				if ( is_null($dwCallBack) ) {
					self::$sLog->trace( ". strInitMessage:\n".$strInitMessage );
					$dwJsonCrypto = DwJsonCrypto::fromJson( $strInitMessage );
					$dwJsonCrypto->{"ciphers"}=$shellCiphers;
					$dwJsonCrypto->{"digests"}=$shellDigests;
				} else 
					$dwCallBack->callback( $strInitMessage );
				self::$sLog->info(". end");
			} else {
				self::$sLog->info("shell start");
				$dwProcessShell->shellCryptoStart( $scriptContent ,$dwCallBack );
			}
		} catch(Exception $exception) {
			self::$sLog->error($exception);
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error($error);
			throw $error;
		}
		return $dwJsonCrypto;
	}
	public static function initiedMessage(
		DwCallBack $dwCallBack
		,string $shellCiphers
		,string $shellDigests
		,string $strInitMessage
	) {
		self::$sLog->trace(". strInitMessage:\n".$strInitMessage);
		$dwJsonCrypto = DwJsonCrypto::fromJson( $strInitMessage );
		$dwJsonCrypto->{"ciphers"}=$shellCiphers;
		$dwJsonCrypto->{"digests"}=$shellDigests;
		$dwCallBack->callback( $dwJsonCrypto );
	}
	     //json_decode(json_encode(array( "cipher" => $cipher, "pass" => $pass, "data" => $clear )))
	public static function encrypt(
		DwJsonCrypto $dwJsonCrypto
		,DwProcessShell $dwProcessShell= null
		,DwCallBack $dwDoneCallBack = null
	) : ?string {
		if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
		$result = null; 
		try {
			/*
			self::$sLog->info(". ".print_r($dwJsonCrypto,true) );
			foreach(array( "cipher", "pass", "salt", "iv", "key", "data" ) as $key ) {
				if (isset($dwJsonCrypto->{$key}))
					self::$sLog->info("$key:".$dwJsonCrypto->{$key} );
			}
			*/
			
			$dwCallBack = null;
			if ( ! is_null( $dwDoneCallBack ) ) {
				$dwCallBack = new DwCallBack(
					array( self::getStaticClassName() ,"encrypted" )
					,$dwDoneCallBack
				);
			}
			$strJsonCrypto = json_encode($dwJsonCrypto);
			self::$sLog->info(". {$strJsonCrypto}" );
			$scriptContent ="\$cmdPrintf \"{$strJsonCrypto}\" ";
			$scriptContent.="| shCryptEncode";
			//$scriptContent .=" \$cmdPrintf \"".json_encode($dwJsonCrypto)."\" | shCryptEncode 2>/tmp/encrypt.log";
			//file_put_contents("/tmp/encrypt.sh",$scriptContent);
			
			if ( is_null( $dwProcessShell ) || is_null($dwCallBack) ) {
				$resultExec = DwShell::exec(
					DwShellCrypt::getInitScript().$scriptContent." 2>/tmp/lastEncrypt.log"
				);
				if ( is_null($dwCallBack) ) 
					$result = $resultExec;
				else
					$dwCallBack->callback( $result );
			 } else
				$dwProcessShell->shellCryptoStart( $scriptContent , $dwCallBack );
		} catch(Exception $exception) {
			self::$sLog->error("an encrypt exception");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("an encrypt error");
			throw $error;
		}
		return $result;
	}
	public static function encrypted(
		DwCallBack $dwCallBack
		,string $strEncrypted
	) {
		$dwCallBack->callback( $strEncrypted );
	}
        //json_decode(json_encode(array( "cipher" => $cipher, "pass" => $pass, "data" => $crypted )))
	public static function decrypt(
		$jsonMessage
		,DwProcessShell $dwProcessShell= null
		,DwCallBack $dwDoneCallBack = null
	) : ?string {
		if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
		$result = null; 
		try {
			$dwCallBack = null;
			if ( ! is_null( $dwDoneCallBack ) ) {
				$dwCallBack = new DwCallBack(
					array( self::getStaticClassName() ,"decrypted" )
					,$dwDoneCallBack
				);
			}
			self::$sLog->info(".");
			
			$scriptContent =" \$cmdPrintf \"".json_encode($jsonMessage)."\" ";
			$scriptContent.="| shCryptDecode 2>/tmp/lastDecrypt";
			
			if ( is_null( $dwProcessShell ) || is_null($dwCallBack) ) {
				$resultExec = DwShell::exec(DwShellCrypt::getInitScript().$scriptContent);
				if ( is_null($dwCallBack) ) 
					$result = $resultExec;
				else
					$dwCallBack->callback( $resultExec );
			 } else
				$dwProcessShell->shellCryptoStart( $scriptContent , $dwCallBack );
		} catch(\Exception $exception) {
			self::$sLog->error($exception);
			throw $exception;
		} catch(\Error $error) {
			self::$sLog->error($error);
			throw $error;
		}
		return $result;
	}
	public static function decrypted(
		DwCallBack $dwCallBack
		,string $strDecrypted
	) {
		$dwCallBack->callback( $strDecrypted );
	}
	public static function listCiphers() : ?array {
		return DwShellCrypt::listCiphers();
	}
	public static function listDigests() : ?array {
		return DwShellCrypt::listDigests();
	}
	//*/
	public static function getStaticClassName( bool $fqcn=null ) : string {
		//return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
		return __CLASS__;
	}
}
?>

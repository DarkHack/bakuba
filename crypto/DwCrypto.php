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

namespace bakuba\kernel\crypto;
//use bakuba\kernel\crypto\DwCrypto as DwCrypto;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;

//use bakuba\kernel\process\child\DwLocalAgent as DwLocalAgent;

class DwCrypto extends aDwCrypto {
	private $cListCiphers = null;
	private $cListDigests = null;
	private $cListIVSize = array();
	
	
	public function listCiphers( DwCallBack $dwCallBack ) : void {
		if ( is_null( $this->cListCiphers ) )
			$this->getDwLocalAgent()->listCiphers( new DwCallBack( array( &$this ,"listedCiphers" ) ,$dwCallBack ) );
		else
			$dwCallBack->callback( $this->cListCiphers );
	}
	public function listedCiphers( DwCallBack $dwCallBack ,string $ciphers ) : void {
		$this->log->trace( $ciphers );
		$this->cListCiphers=$ciphers;
		$dwCallBack->callback( $this->cListCiphers );
	}
	public function listDigests( DwCallBack $dwCallBack  ) : void {
		$this->log->trace( "." );
		if ( is_null( $this->cListDigests ) )
			$this->getDwLocalAgent()->listDigests( new DwCallBack( array( &$this ,"listedDigests" ) ,$dwCallBack ) );
		else
			$dwCallBack->callback( $this->cListDigests );
		
	}
	public function listedDigests( DwCallBack $dwCallBack ,string $digests ) : void {
		$this->log->trace( $digests );
		$this->cListDigests=$digests;
		$dwCallBack->callback( $this->cListDigests );
	}
	public function generateSaltKeyIv( DwCallBack $dwCallBack ,string $cipher ,string $pass ) : void {
		$this->log->trace( "." );
		if ( isset( $this->cListIVSize[ $cipher ] ) )
			$this->getDwLocalAgent()->listDigests( new DwCallBack( array( &$this ,"generatedSaltKeyIv" ) ,$cipher ,$dwCallBack ) );
		else
			$dwCallBack->callback( $this->cListIVSize[ $cipher ] );
		
	}
	public function generatedSaltKeyIv( string $cipher ,DwCallBack $dwCallBack ,string $saltKeyIv ) : void {
		$this->log->trace( "$cipher:$saltKeyIv" );
		$this->cListIVSize[ $cipher ]=$ivSize;
		$dwCallBack->callback( $this->cListIVSize[ $cipher ] );
	}
	
	public function initMessage( DwCallBack $dwCallBack ,array $ciphers ,array $digests ) : void {
		$this->log->trace( "." );
		$this->getDwLocalAgent()->initMessage( $dwCallBack ,$ciphers ,$digests );
	}
	public function encrypt( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : void {
		$this->log->trace( "." );
		$this->getDwLocalAgent()->encrypt( $dwCallBack ,$dwJsonCrypto );
	}
	public function decrypt( DwCallBack $dwCallBack ,DwJsonCrypto $jsonMessage ) : void {
		$this->log->trace( "." );
		$this->getDwLocalAgent()->decrypt( $dwCallBack ,$jsonMessage );
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

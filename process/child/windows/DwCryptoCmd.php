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

use bakuba\kernel\objects\DwCallBack as DwCallBack;


use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;

class DwCryptoCmd extends \bakuba\kernel\crypto\aDwCrypto {
	private $cListCiphers = null;
	private $cListDigests = null;
	private $cListIVSize = array();
	
	
	public function __construct( \bakuba\kernel\process\child\windows\DwWinAgent $dwWinAgent ) {
		parent::__construct( $dwWinAgent );
	}
	
	public function listCiphers( DwCallBack $dwCallBack ) : void {
		if ( is_null( $this->cListCiphers ) )
			$this->parent->listCiphers( new DwCallBack( array( &$this ,"listedCiphers" ) ,$dwCallBack ) );
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
			$this->parent->listDigests( new DwCallBack( array( &$this ,"listedDigests" ) ,$dwCallBack ) );
		else
			$dwCallBack->callback( $this->cListDigests );
		$this->parent->listDigests( $dwCallBack );
	}
	public function listedDigests( DwCallBack $dwCallBack ,string $digests ) : void {
		$this->log->trace( $ciphers );
		$this->cListDigests=$digests;
		$dwCallBack->callback( $this->cListDigests );
	}
	
	public function initMessage( DwCallBack $dwCallBack ,array $ciphers ,array $digests ) : void {
		$this->log->trace( "." );
		$this->parent->initMessage( $dwCallBack ,$ciphers ,$digests );
	}
	public function encrypt( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : void {
		$this->log->trace( "." );
		$this->parent->encrypt( $dwCallBack ,$dwJsonCrypto );
	}
	public function decrypt( DwCallBack $dwCallBack ,$jsonMessage ) : void {
		$this->log->trace( "." );
		$this->parent->decrypt( $dwCallBack ,$jsonMessage );
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

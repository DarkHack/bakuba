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

namespace bakuba\kernel\process\child\windows;
//use bakuba\kernel\process\child\windows\DwCscriptCrypto as DwCscriptCrypto;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwCscriptCrypto extends \bakuba\kernel\process\child\windows\DwCscriptRandomizer {
	
	public function __construct( \bakuba\kernel\process\child\windows\DwWinAgent $dwWinAgent ) {
		parent::__construct( $dwWinAgent );
		$this->createDwProcCscript();
	}
	protected function createDwProcCscript() : DwProcCscript {
		$this->log->trace( "." );
		$dwProcCscript = parent::createDwProcCscript();
		$dwProcCscript->execWithoutPrepare( self::getScript("jsCrypto/jsCryptInitDefault.js") );
		$dwProcCscript->execWithoutPrepare( self::getScript("jsCrypto/jsCryptInitList.js") );
		$dwProcCscript->execWithoutPrepare( self::getScript("jsCrypto/jsCrypt.js") );
		$dwProcCscript->execWithoutPrepare( self::getScript("jsCrypto/jsCryptTest.js"), new DwCallBack( array( &$this, 'endedTestCrypt' ) ) );
		return $dwProcCscript;
	}
	public function endedTestCrypt() {
		$this->log->trace( "." );
	}
	/*
	static private $ciphers = null;
	static private $digests = null;
	static private $dwRandomizer = null;
	// DwShellCrypt::setRandomizer( $dwRandomizer );
	public static function setRandomizer( DwRandomizer $dwRandomizer ) : void {
		self::$dwRandomizer = $dwRandomizer;
	}
	public static function getRandomCache( int $size = 1500 ) : string {
		$scriptContent="";
		if ( isset( self::$dwRandomizer ) && \is_object( self::$dwRandomizer ) ) {
			$scriptContent.="\n";
			$scriptContent.="\$cmdPrintf \"";
			$scriptContent.=self::$dwRandomizer->random( $size );
			$scriptContent.="\"| shPipeWrite \$shRandomizePipe;\n";
		}
		return $scriptContent;
	}
	
	// DwShellCrypt::listCiphers();
	public static function listCiphers(
		DwProcessShell $dwProcessShell= null
		,DwCallBack $dwCallBack = null
	) : ?string {
		$ciphers = null; 
		if ( is_null( self::$ciphers ) ) {
			$scriptContent = "shCryptTestListCiphers 2>/dev/null ";
			//$scriptContent .= "shCryptTestListCiphers >/var/log/bakuba/testCipher.log 2>&1\n";
			//file_put_contents("/var/log/bakuba/testCipher.sh", $scriptContent);
			$scriptContent ="\$(which echo) -e \"aes-128-cbc:aes-128-ecb\c\";echo coucouCipher >&2";
//			$scriptContent ="\$(which echo) \"\$(which echo) -e 'aes-128-cbc:aes-128-ecb\c';\";";
			if ( is_null( $dwProcessShell ) )
				 self::$ciphers = DwShell::exec(
				 	self::getInitScript().$scriptContent." 2>/dev/null "
				 );
			else {
				$dwProcessShell->shellCryptoStart(
					$scriptContent
					,new DwCallBack(
						self::getStaticClassName()."::setListCiphers"
						,$dwCallBack
					)
				);
			}
		}
		$ciphers = self::$ciphers;
		return $ciphers;
	}
	//*/
	/*
	// on bakuba init
		\bakuba\kernel\shell\DwShellCrypt::listCiphers(
			$this
			,new DwCallBack( array( &$this ,"setListCiphers" ) )
		);

	public function setListCiphers( string $listCiphers ) {
		$this->log->trace( $listCiphers );
		
		\bakuba\kernel\shell\DwShellCrypt::listDigests(
			$this
			,new DwCallBack( array( &$this ,"setListDigests" ) )
		);
		//new DwCallBack( array( \bakuba\kernel\shell\DwShellCrypt::getStaticClassName(), "setListCiphers" );
		//\bakuba\kernel\shell\DwShellCrypt::setListCiphers( $listCiphers )
		
	}
	//*/
	/*
	public static function setListCiphers(
		DwCallBack $dwCallBack
		,string $listCiphers
	)  : void {
		self::$ciphers = $listCiphers;
		$dwCallBack->callback( $listCiphers );
	}
	//*/
	/*
	// on bakuba init
	public function setListDigests( string $listDigests ) {
		$this->log->trace( $listDigests );
	}

	//*/
	/*
	// DwShellCrypt::listDigests();
	public static function listDigests(
		DwProcessShell $dwProcessShell= null
		,DwCallBack $dwCallBack = null
	) {
		$digests = "echec list digests"; 
		if ( is_null(self::$digests) ) {
			$scriptContent = "\$(which echo) -e \"\$shCryptdigests\c\";echo coucou >&2 ";
			//$scriptContent .= "shCryptTestListCiphers >/var/log/bakuba/testCipher.log 2>&1\n";
			//file_put_contents("/var/log/bakuba/testCipher.sh", $scriptContent);
			//$scriptContent ="\$(which echo) -e 'aes-128-cbc:aes-128-ecb\c';";
			//$scriptContent ="\$(which echo) -e 'md4:md5:rmd160:sha:sha1\c';";
			
			if ( is_null( $dwProcessShell ) )
				 self::$digests = DwShell::exec(
				 	self::getInitScript().$scriptContent." 2>/dev/null "
				 );
			else {
				$dwProcessShell->shellCryptoStart(
					$scriptContent
					,new DwCallBack(
						self::getStaticClassName()."::setListDigests"
						,$dwCallBack
					)
				);
			}
			//echo "========== end list digests: \r\n".self::$digests."\r\n=======\r\n";
		}
		$digests = self::$digests;
		
		return $digests;
	}
	public static function setListDigests(
		DwCallBack $dwCallBack
		,string $listDigests
	)  : void {
		self::$digests = $listDigests;
		$dwCallBack->callback( $listDigests );
	}
	//*/
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
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

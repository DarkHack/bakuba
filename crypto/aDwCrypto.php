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
//use bakuba\kernel\crypto\aDwCrypto as aDwCrypto;

use darkphp\DwObject as DwObject;
use bakuba\kernel\process\child\DwLocalAgent as DwLocalAgent;

abstract class aDwCrypto extends DwObject implements iDwCrypto {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToFirstSectionName( $this->getClassName() )
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	///* 
	public function __construct( DwLocalAgent $parent ) {
		parent::__construct( $parent );
	}
	//*/
	protected function getDwLocalAgent() : DwLocalAgent {
		return $this->parent;
	}
	protected function getDwResources() : \bakuba\kernel\process\DwResources {
		return $this->getDwLocalAgent()->getDwResources();
	}
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
	
	// \bakuba\kernel\crypto\aDwCrypto::commonCiphers( $ciphers );
	public static function commonCiphers( array $ciphers ) : array {
		return \darkphp\DwArray::commonInsensibleCasseArray( self::listCiphers() ,$ciphers );
	}
	// \bakuba\kernel\crypto\aDwCrypto::commonDigests( $digests );
	public static function commonDigests( array $digests ) : array {
		return \darkphp\DwArray::commonInsensibleCasseArray( self::listDigests() ,$digests );
	}
	
}
?>

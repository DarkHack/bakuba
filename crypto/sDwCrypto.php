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
use bakuba\kernel\process\child\DwLocalAgent as DwLocalAgent;

use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\shell\DwShell as DwShell;
use bakuba\kernel\shell\DwShellCrypt as DwShellCrypt;

use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;

use bakuba\kernel\process\DwProcessShell as DwProcessShell;

use darkphp\DwObject as DwObject;
class sDwCrypto extends DwObject {
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
	
	private static $instance = null;
	// \bakuba\kernel\crypto\sDwCrypto::start( $this );
	public static function start( DwLocalAgent $dwLocalAgent ) {
		if ( \is_null( self::$instance ) )
			self::$instance = new self( $dwLocalAgent );
	}
	//public static function 
	private function __construct( DwLocalAgent $parent ) {
		parent::__construct( $parent );
		$this->phpCrypto = new php\DwCrypto( $parent );
		$this->agentCrypto = new DwCrypto( $parent );
	}
	private function phpCrypto() : php\DwCrypto {
		return $this->phpCrypto;
		
	}
	private function agentCrypto() : DwCrypto {
		return $this->agentCrypto;
		
	}
	
	public static function getPhpCrypto() : php\DwCrypto {
		return self::$instance->phpCrypto();
	}
	public static function getAgentCrypto() : DwCrypto {
		return self::$instance->agentCrypto();
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
}
?>

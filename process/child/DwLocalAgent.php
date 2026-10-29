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

namespace bakuba\kernel\process\child;
//use bakuba\kernel\process\child\DwLocalAgent as DwLocalAgent;

/*
use bakuba\kernel\objects\DwCallBack as DwCallBack;
interface iDwAgent {
	public function random( int $length , DwCallBack $dwCallBack ) : void;
	public function listNetFace( DwCallBack $dwCallBack ) : void;
	public function listCiphers( DwCallBack $dwCallBack ) : void;
	public function listDigests( DwCallBack $dwCallBack ) : void;
	public function initMessage( $ciphers ,$digests ,DwCallBack $dwCallBack ) : void;
	public function encrypt( DwJsonCrypto $dwJsonCrypto ,DwCallBack $dwCallBack ) : void;
	public function decrypt( $jsonMessage ,DwCallBack $dwCallBack ) : void;
}
//*/
//DwLocalAgent::getLocalAgent( $dwResources ,$iDwLogger );
abstract class DwLocalAgent
	extends \darkphp\DwObject implements \bakuba\kernel\agartha\iDwAgent {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className=$this->getClassName();
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToFirstSectionName( $className )
				,\darkphp\source\DwSource::classNameToTopName( $className )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	private static $instance = null;
	// bakuba\kernel\process\child\DwLocalAgent::getLocalAgent() : DwLocalAgent;
	public static function getLocalAgent(
		\bakuba\kernel\process\DwResources $dwResources = null
	) : self {
		if ( is_null( self::$instance ) ) {
			if ( is_null( $dwResources ) )
				$this->log->error( "param dwResources not defined, cannot instance localAgent." );
			
			if ( \bakuba\kernel\agartha\DwInstall::isWindows() )
				self::$instance = new windows\DwWinAgent( $dwResources );
			else
				self::$instance = new posix\DwPosixAgent( $dwResources );
		}
		return self::$instance;
	}
	///*
	protected function getDwResources() : \bakuba\kernel\process\DwResources {
		return $this->parent;
	}
	protected function __construct( \bakuba\kernel\process\DwResources $dwResources ) {
		parent::__construct( $dwResources );
	}
	public function overrideDns( \bakuba\kernel\network\DwPort $dwPort, \bakuba\kernel\objects\DwCallBack $callback) {
		$this->log->trace( $dwPort );
		throw new \Exception( 'not implemented');
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
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

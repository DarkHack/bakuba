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

namespace bakuba\kernel\io\stream;
//use bakuba\kernel\io\stream\DwStreamIpConnexion as DwStreamIpConnexion;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use darkphp\DwObject as DwObject;

use bakuba\kernel\io\stream\DwStreamSelect as DwStreamSelect;

use bakuba\kernel\io\stream\DwStream as DwStream;

use bakuba\kernel\io\messageQueue\IConnexionMessaging as IConnexionMessaging;

use bakuba\kernel\network\DwInterface as DwInterface;
use bakuba\kernel\network\DwIp as DwIp;
use bakuba\kernel\network\DwPort as DwPort;

class DwStreamIpConnexion extends \bakuba\kernel\io\DwResourceIpConnexion {
	
	public function getDwStreamSelect() : DwStreamSelect {
		return $this->getDwResourceSelect();
	}
	public function getDwStream() : DwStream {
		return $this->getDwResource();
	}
	public function getDwPort() : ?DwPort {
		return $this->dwPortClient;
	}
	public function read() : ?string {
		return $this->getDwStream()->read();
	}
	private $dwPortClient = null;
	public function __construct(
		DwStreamSelect $dwStreamSelect
		,DwStream $dwStream
		,DwPort $dwPortServer
		,DwPort $dwPortClient
	) {
		$this->dwPortClient = $dwPortClient;
		parent::__construct( $dwStreamSelect ,$dwStream ,$dwPortServer );
	}
	
	public function readRequest( DwCallBack $dwCallBack ) : void {
		$dwStream = $this->getDwStream();
		$dwStream->setOnReaded( new DwCallBack( array( &$this, "requestReceived" ), $dwCallBack ) );
		$this->getDwStreamSelect()->streamRead( $dwStream );
	}
	// from DwLightHttpd->response
	public function response( string $data ) : void {
		parent::response( $data );
		$dwStream = $this->getDwStream();
		$dwStream->setOnReaded( new DwCallBack( array( &$this, "requestSended" ) ) );
		$dwStream->write( $data );
		$this->getDwStreamSelect()->streamWrite( $dwStream );
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
		parent::__destruct();
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ 
			: __CLASS__
		;
	}
}

?>

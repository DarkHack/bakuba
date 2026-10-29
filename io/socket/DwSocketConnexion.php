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

namespace bakuba\kernel\io\socket;
//use bakuba\kernel\io\socket\DwSocketConnexion as DwSocketConnexion;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use darkphp\DwObject as DwObject;

use bakuba\kernel\io\socket\DwSocketSelect as DwSocketSelect;

use bakuba\kernel\io\socket\DwSocket as DwSocket;

use bakuba\kernel\services\server\DwServerConnexion as DwServerConnexion;

use bakuba\kernel\io\messageQueue\IConnexionMessaging as IConnexionMessaging;

use bakuba\kernel\network\DwInterface as DwInterface;
use bakuba\kernel\network\DwIp as DwIp;
use bakuba\kernel\network\DwPort as DwPort;

class DwSocketConnexion extends \bakuba\kernel\io\DwResourceIpConnexion implements IConnexionMessaging {
	public function getDwSocketSelect() : DwSocketSelect {
		return $this->getDwResourceSelect();
	}
	public function getDwSocket() : DwSocket {
		return $this->getDwResource();
	}
	public function getDwPort() : ?DwPort {
		$dwPort = $this->getDwSocket()->getDwPort();
		if ( is_null( $dwPort ) )
			$this->log->info( "no dwPort" );
		
		return $dwPort;
	}
	public function read() : ?string {
		return $this->getDwSocket()->read();
	}
	
	public function __construct(
		DwSocketSelect $dwSocketSelect
		,DwSocket $dwSocket
		,DwPort $dwPortServer
	) {
		parent::__construct( $dwSocketSelect ,$dwSocket ,$dwPortServer );
	}
	// from DwLightHttpd->response
	public function response( string $data ) : void {
		//parent::response( $data );
		$dwSocket = $this->getDwSocket();
		$dwSocket->write( $data );
		$dwSocket->setOnWrited( new DwCallBack( array( &$this, "requestSended" ) ) );
		$this->getDwSocketSelect()->socketWrite( $dwSocket );
		$this->rsrcResponse();
	}
	
	public function readRequest( DwCallBack $dwCallBack ) : void {
		$dwSocket = $this->getDwSocket();
		$dwSocket->setOnReaded( new DwCallBack( array( &$this, "requestReceived" ), $dwCallBack ) );
		$this->getDwSocketSelect()->socketRead( $dwSocket );
	}
	public function requestReceived( \bakuba\kernel\objects\DwCallBack $dwCallBack ) {
		if ( is_null ( $this->getDwPort() ) )
			$this->log->warn("no peer defined, request cancel");
		else
			parent::requestReceived( $dwCallBack );
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

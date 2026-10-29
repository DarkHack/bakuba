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

namespace bakuba\kernel\services;
//use bakuba\kernel\services\DwService as DwService;

use bakuba\kernel\protocole\DwRequest as DwRequest;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

abstract class DwService extends \darkphp\DwObject implements iDwService {
	public static function createService(
		application\DwApplication $parent
		,string $className = http\DwHttpd::class
	 ) : self {
		return new $className( $parent );
	}
	protected function getDwApplication() : application\DwApplication {
		return $this->parent;
	}
	private $dwServer = null;
	public function getDwServer() : server\DwServer {
		return $this->dwServer;
	}
	protected function getDwResources() : \bakuba\kernel\process\DwResources {
		return $this->getDwServer()->getDwResources();
	}
	
	private $serverBinded = array();
	protected function serverIsFree( \bakuba\kernel\network\DwPort $dwPort ) : bool {
		$arrKey = $dwPort->getPortPath();
		return ! isset( $this->serverBinded[ $arrKey ] );
	}
	protected function serverBind( \bakuba\kernel\network\DwPort $dwPort ,\bakuba\kernel\io\DwResourceServer $dwResourceServer ) : void {
		$arrKey = $dwPort->getPortPath();
		$this->log->trace( "bindPortToServer [{$arrKey}]" );
		$this->serverBinded[ $arrKey ] = $dwResourceServer;
	}
	
	private $onIpConnect = null;
	protected function onConnect() : DwCallBack {
		return $this->onIpConnect;
	}
	
	protected function __construct( application\DwApplication $parent ) {
		$this->dwServer = new server\DwServer( $parent );
		parent::__construct( $parent );
		$this->onIpConnect = new DwCallBack( array( &$this ,'resourceIpConnexion' ) );
		$this->log->trace(".");
	}
	//public function resourceIpConnexion( \bakuba\kernel\io\DwResourceIpConnexion $dwResourceIpConnexion ) : void;
	
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

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

namespace bakuba\kernel\application;
//use bakuba\kernel\application\aDwApplicationNetIpc as aDwApplicationNetIpc;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
 
abstract class aDwApplicationNetIpc extends aDwApplication/* implements iDwApplication */{
	/*
	protected function __construct( self $parent ,string $urlApp ) {
		parent::__construct( $parent ,$urlApp );
	}
	protected function init( ) : void {
		$this->log->trace( "." );
		parent::init();
	}
	//*/
	private $netIpcServer = null;
	private $dwPort = null;
	protected function initNetIpc( \bakuba\kernel\network\DwInterface $dwInterfaceLo ) : void {
		$this->dwPort=$dwInterfaceLo->getRandomIpPort();
		$this->log->trace( $this->dwPort->getPortPath() );
		
		$this->netIpcServer = new \bakuba\kernel\io\ipc\net\DwNetIpcServer(
			$this->getDwResources()->getSocketSelect()
			,new DwCallBack( array( &$this, 'ipcNetConnect' ) )
			,$this->dwPort
		);
	}
	public function ipcNetConnect( \bakuba\kernel\io\DwResourceIpConnexion $dwResourceIpConnexion ) : void {
		$this->log->trace( '.' );
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

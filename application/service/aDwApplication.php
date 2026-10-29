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

namespace bakuba\kernel\application\service;
//use bakuba\kernel\application\service\aDwApplication as aDwApplication;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

abstract class aDwApplication extends \bakuba\kernel\application\aDwApplication implements iDwApplication {
	/*
	private $dwApplicationService = null;
	protected function setDwApplicationServices(
		\bakuba\kernel\services\application\DwApplication $dwApplicationService
	) : void {
		$this->dwApplicationService = $dwApplicationService;
	}
	public function getDwApplicationServices() : \bakuba\kernel\services\application\DwApplication {
		return $this->dwApplicationService;
	}
	protected function __construct( self $parent ,string $urlApp ) {
		parent::__construct( $parent ,$urlApp );
		//$this->log->trace( "." );	
	}
	//*/
	
	/*
	public function getDwBakuba() : \bakuba\kernel\agartha\DwBakuba {
		return $this->getDwApplication();
	}
	//*/
	
	protected function init( ) : void {
		parent::init();
		//$this->setDwApplicationServices( $this->getDwBakuba()->getDwApplicationServices() );
		
		$this->ipcRegisterHandle(
			DwApplicationRequest::getStaticClassName()
			,new \bakuba\kernel\objects\DwCallBack( array( &$this ,'handleApplicationServiceRequest' ) )
		);
	}
	public function connectApplication(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\iDwApplicationConnexion {
		throw new \Exception( "no connectApplication in service app process." );
	}
	/*
	public function serviceConnectApp(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) : iDwApplicationConnexion {
		return new aDwApplicationConnexion( $this ,$dwProcessConnexion ,$dwApplicationRequest );
	}
	//*/
	public function handleApplicationServiceRequest(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) : void {
		$this->serviceConnectApp( $dwProcessConnexion ,$dwApplicationRequest );
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
	//DwApplication::createApplicationService
	// \bakuba\kernel\application\service\aDwApplication::createApplicationService
	public static function createApplicationService(
		\bakuba\kernel\agartha\DwBakuba $dwBakuba
		,string $urlApp
	) : self {
		$classname="$urlApp\\DwApplication";
		return new $classname( $dwBakuba ,$urlApp );
	}
}

?>

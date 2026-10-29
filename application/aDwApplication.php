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
//use bakuba\kernel\application\aDwApplication as aDwApplication;

abstract class aDwApplication extends \bakuba\kernel\process\child\DwProcessChild implements iDwApplication {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className = \darkphp\source\DwSource::classNameToTopName( $this->getClassName() );
		$urlApp = $this->getUrlApp();
		if ( $urlApp == '/' )
			$urlApp="agarthaRoot";
		
		if ( \method_exists( $iDwLogger ,"upTo" ) ) {
			$this->log = $iDwLogger->upTo(
				$this
				,\str_replace( array( '\\','/' ) ,'-' ,$urlApp )
				,$className
			);
		} else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	/*
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className = $this->getClassName();
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
	//*/
	protected function __construct( \bakuba\kernel\process\DwProcessIpc $parent ,string $urlApp ) {
		$this->urlApp = $urlApp;
		parent::__construct( $parent );
		$this->log->trace( "." );	
	}
	protected function getDwApplication() : self {
		return $this->parent;
	}
	protected function init( ) : void {
		$this->setRootFile( $this->urlApp );
		parent::init();
		$this->ipcRegisterHandle(
			DwApplicationRequest::getStaticClassName()
			,new \bakuba\kernel\objects\DwCallBack( array( &$this ,'connectApplication' ) )
		);
		$this->ipcRegisterHandle(
			\bakuba\kernel\io\ipc\DwIpcMsgError::getStaticClassName()
			,new \bakuba\kernel\objects\DwCallBack( array( &$this ,'handleDwIpcMsgError' ) )
		);
	}
	public function handleDwIpcMsgError(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\io\ipc\DwIpcMsgError $dwIpcMsgError ) : void {
		$this->log->fatal( $dwProcessConnexion->toString() );
		$this->log->fatal( $dwIpcMsgError->toString() );
	}
	/*
	public function connectApplication(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) : iDwApplicationConnexion {
		return new aDwApplicationConnexion( $this ,$dwProcessConnexion ,$dwApplicationRequest );
	}
	//*/
	public $urlApp = "root";
	public function getUrlApp() : string {
		return $this->urlApp;
	}
	public $cpt = 0;
	public function getCpt() : int {
		return $this->cpt;
	}
	
	//*/
	private $rootFolder = "/bakuba/kernel";
	private function setRootFile( string $rootFolder = "bakuba" ) : void {
		$this->rootFolder = "{$rootFolder}";
	}
	public function getFile( string $filePath ) : ?string {
		//$this->log->info("getFile: {$this->rootFolder}/{$filePath} ");
		return $this->parent->getFile( "{$this->rootFolder}/{$filePath}" );
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
	//aDwApplication::createApplication
	// dwItemContainer : DwApplicationManager(item) => DwBakuba(item) => DwNode(item) => DwAgartha(map)
	public static function createApplication(
		\bakuba\kernel\process\DwProcessIpc $dwParent
		,string $urlApp
	) : self {
		$classname="$urlApp\\DwApplication";
		return new $classname( $dwParent ,$urlApp );
	}
}

?>

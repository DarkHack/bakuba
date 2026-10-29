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

namespace bakuba\kernel\database\application\controller;
//use bakuba\kernel\database\application\controller\aDwApplicationControllerDatabase as aDwApplicationControllerDatabase;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

abstract class aDwApplicationControllerDatabase 
	extends \bakuba\kernel\application\service\http\controller\aDwApplicationController
	implements \bakuba\kernel\database\application\iDwApplicationDatabase {
	
	public function databaseprocess_ready( ) : void {
	}
	
	private $dwDatabaseManager = null;
	public function __construct(
		\bakuba\kernel\application\aDwApplication $aDwApplication
		,string $urlApp
		,string $domain
		,string $dataBaseClassName = null	
	) {
		$this->dataBaseClassName = $dataBaseClassName;
		parent::__construct( $aDwApplication ,$urlApp ,$domain );
		$this->dwDatabaseManager = new \bakuba\kernel\database\DwDatabaseManager( $this ,$dataBaseClassName );
	}
	protected function init( ): void {
		//DwApplication::init();
		parent::init();
		//self::$sLog->trace("parent::init");
		$this->dwDatabaseManager->initDatabase();
	}
	public function getDataBaseProcess() : \bakuba\kernel\database\DwDatabaseProcess {
		return $this->dwDatabaseManager->getDataBaseProcess();
	}
	public function getDataBaseClassName() : ?string {
		return $this->dwDatabaseManager->getDataBaseClassName();
	}
	protected function addDataBaseProcess( object $object ) : void {
		$this->dwDatabaseManager->addDataBaseProcess( $object );
	}
	public function databaseRequest(
		\bakuba\kernel\database\DwDatabaseRequest $dwDatabaseRequest
		,DwCallBack $dwCallBack = null
	) : void {
		$this->dwDatabaseManager->databaseRequest( $dwDatabaseRequest ,$dwCallBack );
	}
	public function databaseIsPending() : bool {
		return $this->dwDatabaseManager->databaseIsPending();
	}
	public function databaseResponse(
		string $requestKey
		,\bakuba\kernel\database\DwDatabaseResponse $dwDatabaseResponse
	) : void {
		$this->dwDatabaseManager->databaseResponse( $requestKey ,$dwDatabaseResponse );
	}
	public function onAllDataBaseRequestReady( DwCallBack $dwCallBack ) {
		$this->dwDatabaseManager->onAllDataBaseRequestReady( $dwCallBack );
	}
	/*
	public function onDatabaseLoaded( DwCallBack $dwCallBack ) {
		$this->dwDatabaseManager->onDatabaseLoaded( $dwCallBack );
	}
	//*/
	
	/*
	public function getController(
		string $className
		,string $strFullQualifiedNameClass
	) : \bakuba\kernel\application\controller\aDwController {
		$dwControllerBin = parent::getController( $className, $strFullQualifiedNameClass );
		self::objAddDataBaseProcess( $dwControllerBin ,$this->dwDatabaseProcess );
		return $dwControllerBin;
	}
	*/
	public function getController( string $className ,string $strFullQualifiedNameClass ) : \bakuba\kernel\application\service\http\controller\aDwController {
		$aDwController = parent::getController( $className, $strFullQualifiedNameClass );
		$this->addDataBaseProcess( $aDwController );
		return $aDwController;
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

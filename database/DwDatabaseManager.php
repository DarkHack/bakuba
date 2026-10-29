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

namespace bakuba\kernel\database;
//use bakuba\kernel\database\DwDatabaseManager as DwDatabaseManager;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\database\DwDatabaseProcess as DwDatabaseProcess;

use bakuba\kernel\database\DwDatabaseRequest as DwDatabaseRequest;
use bakuba\kernel\database\DwDatabaseResponse as DwDatabaseResponse;

use bakuba\kernel\application\session\DwSessionIpKey as DwSessionIpKey;

class DwDatabaseManager extends \darkphp\DwObject {
	
	public function databaseprocess_ready( ) : void {
		$this->parent->databaseprocess_ready();
	}
	private $dwDatabaseProcess = null;
	public function getDataBaseProcess() : DwDatabaseProcess {
		return $this->dwDatabaseProcess;
	}
	
	private $dataBaseClassName = null;
	public function getDataBaseClassName() : ?string {
		return $this->dataBaseClassName;
	}
	
	public function __construct(
		\bakuba\kernel\database\application\iDwApplicationDatabase $iDwApplicationDatabase
		,string $dataBaseClassName = null
	) {
		$this->dataBaseClassName = $dataBaseClassName;
		parent::__construct( $iDwApplicationDatabase );
	}
	public function initDatabase( ): void {
		if ( ! is_null( $this->dataBaseClassName ) )
			$this->startDatabaseProcess(
				new DwDatabaseProcess( $this->parent ,$this->parent->getUrlApp() )
			);
	}
	protected function startDatabaseProcess( DwDatabaseProcess $dwDatabaseProcess ) {
		if ( is_null( $this->dwDatabaseProcess ) ) {
			$this->dwDatabaseProcess = $dwDatabaseProcess;
			$this->dwDatabaseProcess->onalive(
				new DwCallBack( array( &$this ,"databaseprocess_ready" ) )
			);
		}
		// start database process
		$this->parent->createProcess( $this->dwDatabaseProcess );
	}
	protected function addDataBaseProcess(
		object $object
	) : void {
		self::objAddDataBaseProcess( $object ,$this->dwDatabaseProcess );
	}
	private static function objAddDataBaseProcess(
		object $object
		,DwDatabaseProcess $dwDatabaseProcess = null
	) : void {
		if ( is_object( $dwDatabaseProcess ) )
			if ( is_object( $object) && method_exists( $object, "setDataBaseProcess" ) )
				$object->setDataBaseProcess( $dwDatabaseProcess );
	}
	private $requestInstances = array();
	public function databaseRequest(
		DwDatabaseRequest $dwDatabaseRequest
		,DwCallBack $dwCallBack = null
	) : void {
		
		$requestKey = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( $this->requestInstances );
		
		$this->requestInstances[ $requestKey ] = \is_object( $dwCallBack ) ? $dwCallBack : 1;
		$this->parent->ipcSend(
			new DwCallBack( array( &$this ,"databaseResponse" ) ,$requestKey )
			,$this->getDataBaseProcess()
			,$dwDatabaseRequest
		);
	}
	
	public function databaseIsPending() : bool {
		return ! empty($this->requestInstances);
	}
	public function databaseResponse(
		string $requestKey
		,DwDatabaseResponse $dwDatabaseResponse
	) : void {
		$debugKey=$this->getClassName()."/requestKey:{$requestKey}";
		
		if ( isset( $this->requestInstances[ $requestKey ] ) ) {
			$dwCallBack = $this->requestInstances[ $requestKey ];
			unset( $this->requestInstances[ $requestKey ] );
			if ( is_object( $dwCallBack ) )
				$dwCallBack->callback( $dwDatabaseResponse );
		} else
			throw new Exception("{$debugKey} no requestKey found in 'requestInstances'");
		
		if ( empty( $this->requestInstances ) ) {
			foreach( $this->cbOnDatabaseReady as $key => $dwCallBack ) {
				unset( $this->cbOnDatabaseReady[ $key ] );
				if ( is_object( $dwCallBack ) )
					$dwCallBack->callback(); 
			}
		}
	}
	private $cbOnDatabaseReady = array();
	public function onAllDataBaseRequestReady( DwCallBack $dwCallBack ) {
		$this->cbOnDatabaseReady[] = $dwCallBack;
	}
	/*
	private $cbOnDataLoaded = array();
	
	public function onDatabaseLoaded( DwCallBack $dwCallBack ) {
		$this->cbOnDataLoaded[] = $dwCallBack;
	}
	//*/
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

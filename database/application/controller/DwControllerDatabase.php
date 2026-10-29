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

namespace bakuba\kernel\application\controller;
//use bakuba\kernel\application\controller\DwControllerDatabase as DwControllerDatabase;

abstract class DwControllerDatabase extends \bakuba\kernel\application\controller\DwControllerIpc implements iDwControllerDatabase {
	/*
	public function __construct( ) {
		parent::__construct( );
	}
	public function execute(
		\bakuba\kernel\application\controller\DwControllerConnexion $dwControllerConnexion
	) : void {
		parent::execute( $dwControllerConnexion );
	}
	//*/
	protected $dwDatabaseProcess = null;
	public function setDataBaseProcess( 
		\bakuba\kernel\database\DwDatabaseProcess $dwDatabaseProcess
	) : void  {
		$this->dwDatabaseProcess = $dwDatabaseProcess;
	}
	public function databaseRequest(
		\bakuba\kernel\application\session\DwSessionControllerConnexion $dwSessionControllerConnexion
		,\bakuba\kernel\objects\DwCallBack $dwCallBack
		,\bakuba\kernel\database\DwDatabaseRequest $dwDatabaseRequest
	) : void {
		$dwControllerConnexion = $dwSessionControllerConnexion->getDwControllerConnexion();
		//$ctrl = $dwControllerConnexion->getiDwController();
		$dwApplicationConnexion = $dwControllerConnexion->getDwApplicationConnexion();
		$dwProcessMessaging = $dwApplicationConnexion->getDwProcessMessaging();
		
		$this->ipcSend( 
			$dwControllerConnexion
			,$this->dwDatabaseProcess
			,$dwCallBack
			,$dwDatabaseRequest
		);
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

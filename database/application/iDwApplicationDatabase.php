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

namespace bakuba\kernel\database\application;
//use bakuba\kernel\database\application\iDwApplicationDatabase as iDwApplicationDatabase;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

interface iDwApplicationDatabase extends \bakuba\kernel\application\iDwApplication {
	public function databaseprocess_ready( ) : void;
	public function getDataBaseProcess() : \bakuba\kernel\database\DwDatabaseProcess;
	public function getDataBaseClassName() : ?string;
	
	//protected function initDatabase( ): void;
	//protected function startDatabaseProcess( \bakuba\kernel\database\DwDatabaseProcess $dwDatabaseProcess );
	//protected function addDataBaseProcess( object $object ) : void;
	//private static function objAddDataBaseProcess( object $object ,\bakuba\kernel\database\DwDatabaseProcess $dwDatabaseProcess = null ) : void;
	
	public function getController( string $className ,string $strFullQualifiedNameClass ) 
		: \bakuba\kernel\application\service\http\controller\aDwController;
	public function databaseRequest( \bakuba\kernel\database\DwDatabaseRequest $dwDatabaseRequest ,DwCallBack $dwCallBack = null ) : void;
	public function databaseIsPending() : bool;
	public function databaseResponse( string $requestKey ,\bakuba\kernel\database\DwDatabaseResponse $dwDatabaseResponse ) : void;
	public function onAllDataBaseRequestReady( DwCallBack $dwCallBack );
	
}

?>

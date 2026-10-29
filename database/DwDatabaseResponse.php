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
//use bakuba\kernel\database\DwDatabaseResponse as DwDatabaseResponse;

use bakuba\kernel\database\crud\DwCrud as DwCrud;
use bakuba\kernel\io\ipc\DwIpcMsgError as DwIpcMsgError;

class DwDatabaseResponse {
	private $dwIpcMsgError = null;
	public function setIpcMsgError( DwIpcMsgError $dwIpcMsgError ) : void {
		$this->dwIpcMsgError = $dwIpcMsgError;
	}
	public function getIpcMsgError() : ?DwIpcMsgError {
		return $this->dwIpcMsgError;
	}
	/*
	public function __construct( ) {
			//parent::__construct();
	}
	//*/
	// 'result':bool
	private $result = false;
	public function sucess( bool $result ) : void {
		$this->result=$result;
	}
	public function isSucess( ) : bool {
		return $this->result;
	}
	// 'isNew':bool
	private $isNew = false;
	public function newDataBaseCreated( bool $isNew ) : void {
		$this->isNew=$isNew;
	}
	public function isNewDataBaseCreated( ) : bool {
		return $this->isNew;
	}
	// 'dwCrud':dwCrud
	private $dwCrud = null;
	public function setDwCrud( DwCrud $dwCrud ) : void {
		$this->dwCrud=$dwCrud;
	}
	public function getDwCrud() : DwCrud {
		return $this->dwCrud;
	}
	// 'dwCruds':array
	private $dwCruds = null;
	public function setArray( array $dwCruds ) {
		$this->dwCruds = $dwCruds; 
	}
	public function getArray( ) : array {
		return $this->dwCruds;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		//return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
		return __CLASS__;
	}
}
?>

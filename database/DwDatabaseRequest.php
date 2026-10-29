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
//use bakuba\kernel\database\DwDatabaseRequest as DwDatabaseRequest;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;

use bakuba\kernel\database\crud\DwCrud as DwCrud;

class DwDatabaseRequest {
	/*
	public function __construct() {
	}
	*/
	private $order = null;
	private function setOrder( string $order ) {
		$this->order = $order;
	}
	public function getOrder() : string {
		return $this->order;
	}
	
	private $tableName=null;
	public function getTableName() : string {
		return $this->tableName;
	}
	
	private $tableClass;
	public function getTableClass() : string {
		return $this->tableClass;
	}
	
	private $key= null;
	public function getKey() : string {
		return $this->key;
	}
	
	private $value= null;
	public function getValue() {
		return $this->value;
	}
	
	private $dwCrud = null;
	public function getDwCrud() : DwCrud {
		return $this->dwCrud;
	}
	
	public function createTable( string $tableName ,string $tableClass ) : void {
		$this->setOrder("tableCreate");
		$this->tableName = $tableName;
		$this->tableClass = $tableClass;
	}
	
	private $tableDstLink=null;
	public function getTableDstLink() : string {
		return $this->tableDstLink;
	}
	private $crudLinkParamName=null;
	public function getCrudLinkParamName() : string {
		return $this->crudLinkParamName;
	}
	private $tableSrcLink=null;
	public function getTableSrcLink() : string {
		return $this->tableSrcLink;
	}
	public function linkTable( string $tableDstLink ,string $crudLinkParamName ,string $tableSrcLink ) : void {
		$this->setOrder("tableLink");
		$this->tableDstLink = $tableDstLink;
		$this->crudLinkParamName = $crudLinkParamName;
		$this->tableSrcLink = $tableSrcLink;
	}
	
	public function listTable( string $tableName ) : void {
		$this->setOrder("tableList");
		$this->tableName = $tableName;
	}
	public function add( DwCrud $dwCrud ) : void {
		$this->setOrder("tableCrudCreate");
		$this->tableName = $dwCrud->getClassName();
		$this->dwCrud = $dwCrud;
	}
	public function read( DwCrud $dwCrud ) : void {
		$this->setOrder("tableCrudRead");
		$this->tableName = $dwCrud->getClassName();
		$this->dwCrud = $dwCrud;
	}
	public function edit( DwCrud $dwCrud ) : void {
		$this->setOrder("tableCrudUpdate");
		$this->tableName = $dwCrud->getClassName();
		$this->dwCrud = $dwCrud;
	}
	public function remove( DwCrud $dwCrud ) : void {
		$this->setOrder("tableCrudDelete");
		$this->tableName = $dwCrud->getClassName();
		$this->dwCrud = $dwCrud;
	}
	public function search( string $tableName ,string $key ,$value ) : void {
		$this->setOrder("tableCrudSearch");
		$this->tableName = $tableName;
		$this->key = $key;
		$this->value = $value;
	}
	/*
		$keysValuesToSearch = array(
			"table" => DwUserProvider::getStaticClassName()
			,"search" => array(
				"key" => "_provider"
				,"value" => $dwUserProvider->_provider
				,"and" => array(
					"key" => "_provider_user_id"
					"value" => $dwUserProvider->_provider_user_id
				)
			)
		);
	//*/
	private $keysValuesToSearch = null;
	public function searchArray( array $keysValuesToSearch ) : void {
		$this->setOrder("searchArray");
		$this->keysValuesToSearch = $keysValuesToSearch;
	}
	public function getArray( ) : array {
		return $this->keysValuesToSearch;
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

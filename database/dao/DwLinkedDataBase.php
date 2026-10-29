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

namespace bakuba\kernel\database\dao;
//use bakuba\kernel\database\dao\DwLinkedDataBase as DwLinkedDataBase;

use \bakuba\kernel\database\crud\DwCrud as DwCrud;

class DwLinkedDataBase
	extends aDwDao
{
	private $linkedAttrName = null;
	private $tableName = null;
	private $fileExt = null;
	private $folder = null;
	
	private $myCache = array();
	
	public function __construct(
		\bakuba\kernel\database\DwDatabaseProcess $dwDatabaseProcess
		,string $attrName
		,string $tableName
		,string $fileExt = "dbphp"
		,string $folder = null
	) {
		if ( \is_null( $folder ) ) $folder=\bakuba\kernel\agartha\DwInstall::getBakubaDataDirectory()."/default-link";
		parent::__construct( $dwDatabaseProcess );
		$this->linkedAttrName = $attrName;
		$this->tableName = $tableName;
		$this->fileExt = $fileExt;
		$this->folder = $folder;
	}
	
	public function add( DwCrud $dwCrudToAdd ) : DwCrud {
		$dwCrud = null;
		$dwFileLinkedDataBase = $this->getFileCache(
			$dwCrudToAdd->{$this->linkedAttrName}
		);
		
		$dwCrud = $dwFileLinkedDataBase->add( $dwCrudToAdd );
		return $dwCrud;
	}
	
	private $isNewFile = true;
	public function isNew() : bool {
		return $this->isNewFile;
	}
	public function remove( DwCrud $dwCrud ) : bool {
		$found = false;
		$dwFileLinkedDataBase = $this->getFileCache(
			$dwCrud->{$this->linkedAttrName}
		);
		$found = $dwFileLinkedDataBase->remove( $dwCrud );
		return $found;
	}
	
	public function edit( DwCrud $dwCrud ) : DwCrud {
		$return = null;
		$dwFileLinkedDataBase = $this->getFileCache(
			$dwCrud->{$this->linkedAttrName}
		);
		$return = $dwFileLinkedDataBase->edit( $dwCrud );
		return $return;
	}
	public function getObjects(
		string $key
		,string $value = null ) : array {
		throw new \Exception("not implemented");
	}
	public function getLinkedObjects(
		int $linkedValue
		,string $key
		,string $value = null ) : array {
		return $this->getFileCache( $linkedValue )->getObjects( $key ,$value );
	}
	private function getFileCache( int $linkedValue ) : DwFileLinkedDataBase{
		if ( ! isset( $this->myCache[ $linkedValue ] ) ) {
			$this->myCache[ $linkedValue ] = new DwFileLinkedDataBase(
				$this->linkedAttrName
				,$linkedValue
				,$this->tableName
				,$this->folder
				,$this->fileExt
			);
			foreach( $this->dataLinked as $keyLinked => $tableSrcLink )
				$this->myCache[ $linkedValue ]->crudLink( $keyLinked ,$tableSrcLink );
		}
		
		return $this->myCache[ $linkedValue ];
	}
	private $dataLinked = array();
	public function crudLink(
		string $keyLinked
		,DwCache $tableSrcLink
	) : bool {// : ?bool {
		$isDone = true;// = null; 
		if ( isset( $this->dataLinked[ $keyLinked ] ) )
			$isDone = false;
		else {
			$this->dataLinked[ $keyLinked ] = $tableSrcLink;
			foreach( $this->myCache as $myCache )
				$myCache->crudLink( $keyLinked ,$tableSrcLink );
			//$isDone = true;
		}
		return $isDone;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

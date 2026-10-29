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
//use bakuba\kernel\database\DwDatabaseProcess as DwDatabaseProcess;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\process\DwProcess as DwProcess;
use bakuba\kernel\process\DwProcessIpc as DwProcessIpc;

use bakuba\kernel\database\DwDatabase as DwDatabase;

use bakuba\kernel\database\crud\DwCrudCache as DwCrudCache;
use bakuba\kernel\database\crud\DwCrud as DwCrud;
use bakuba\kernel\database\crud\IDwCrudCache as IDwCrudCache;

interface DwIDatabaseProcess {
	public function tableCreate ( string $tableName ) : bool;
	public function tableCrudCreate( DwCrud $dwCrud );
	public function tableCrudRead( DwCrud $dwCrud ) ;
	public function tableCrudUpdate( DwCrud $dwCrud );
	public function tableCrudDelete( DwCrud $dwCrud ): bool;
	public function tableCrudSearch( string $tableName, string $key, $value ) : array;// or array
	public function tableDelete( string $tableName ) : bool;
	public function tableGet( string $tableName ) ;
}
class DwDatabaseProcess extends DwProcess implements DwIDatabaseProcess {
	protected $ableToFork = false;
	
	private $databaseName = "noname";
	public function __construct( DwProcessIpc $dwProcessParent ,string $dataBaseName ) {
		$this->databaseName = $dataBaseName;
		parent::__construct( $dwProcessParent );
			
		$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
	}
	
	private $onEditEvents = null;
	private $tables = null;
	protected function init( ) : void {
		$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
		parent::init();
		$this->tables = array();
		$this->onEditEvents = array();
		
		$this->ipcRegisterHandle(
			DwDatabaseRequest::getStaticClassName()
			,new DwCallBack( array( &$this ,'handleDatabaseRequest' ) )
		);
	}
	
	public function handleDatabaseRequest(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwDatabaseRequest $dwDatabaseRequest
	) {
		$dwDatabaseResponse = new DwDatabaseResponse( );
		switch( $dwDatabaseRequest->getOrder() ) {
		case "tableCreate" :
			$tableName = $dwDatabaseRequest->getTableName();
			$dwDatabaseResponse->sucess(
				$this->tableCreate(  $tableName,$dwDatabaseRequest->getTableClass() )
			);
			$dwDatabaseResponse->newDataBaseCreated( $this->tables[$tableName]->isNew() );
			break;
		case "tableLink" :
			$dwDatabaseResponse->sucess( $this->tables[$dwDatabaseRequest->getTableDstLink()]->crudLink(
				$dwDatabaseRequest->getCrudLinkParamName()
				,$this->tables[$dwDatabaseRequest->getTableSrcLink()]
			) );
			break;
		case "tableList" :
			$dwDatabaseResponse->setArray( $this->tableGet( $dwDatabaseRequest->getTableName() ) );
			break;
		case "tableCrudCreate" :
			$dwDatabaseResponse->setDwCrud( $this->tableCrudCreate( $dwDatabaseRequest->getDwCrud() ) );
			break;
		case "tableCrudRead" :
			$dwDatabaseResponse->setDwCrud( $this->tableCrudRead( $dwDatabaseRequest->getDwCrud() ) );
			break;
		case "tableCrudUpdate" :
			$dwDatabaseResponse->setDwCrud( $this->tableCrudUpdate( $dwDatabaseRequest->getDwCrud() ) );
			break;
		case "tableCrudDelete" :
			$dwDatabaseResponse->sucess( $this->tableCrudDelete( $dwDatabaseRequest->getDwCrud() ) );
			break;
		case "tableCrudSearch" :
			$dwDatabaseResponse->setArray( $this->tableCrudSearch( $dwDatabaseRequest->getTableName() ,$dwDatabaseRequest->getKey() ,$dwDatabaseRequest->getValue() ) );
			break;
		case "searchArray" :
			$dwDatabaseResponse->setArray( $this->searchArray( $dwDatabaseRequest->getArray() ) );
			break;
		case "tableDelete" :
			$dwDatabaseResponse->sucess( $this->tableDelete( $tableName ) );
			break;
		case "tableGet" :
			$dwDatabaseResponse->setArray( $this->tableGet( $tableName ) );
			break;
		default :
			throw new \Exception( "unknow order.".DwLogger::print_r($dwDatabaseRequest) );
			break;
		}
		
		$dwProcessConnexion->ipcResponse( $dwDatabaseResponse );
	}
	
	public function tableOnEdit( string $tableName = "DwCrud", DwCallBack $dwCallBack ) : void {
		if ( ! isset( $this->onEditEvents[$tableName] ) )
			$this->onEditEvents[$tableName] = array();
		
		$this->onEditEvents[$tableName][] = $dwCallBack;
	}
	private function tableEdit( DwCrud $dwCrud ) : void {
		$tableName = $dwCrud->getClassName();
		if ( isset( $this->onEditEvents[$tableName] ) ) {
			foreach( $this->onEditEvents[$tableName] as $dwCallBack ) 
				$dwCallBack->callback( $dwCrud );
		}
	}
	public function tableCreate ( string $tableName = "DwCrud", string $tableClass = null ) : bool {
		$return = false;
		if ( is_null( $tableClass ) ) $tableClass = DwCrudCache::getStaticClassName();
		if ( ! isset( $this->tables[$tableName] ) ) {
			$this->log->trace("start table [$tableClass]");
			$this->tables[$tableName] = new $tableClass( $this ,$tableName );
			$return = true;
		}
		return $return;
	}
	public function tableCrudCreate( DwCrud $dwCrud ) {
		$return = null;
		$tableName = $dwCrud->getClassName();
		if ( ! isset( $this->tables[$tableName] ) || ! \is_object($this->tables[$tableName]) )
			throw new \Exception("no table name :$tableName");
		else {
			$dwCrudCache = $this->tables[$tableName];
			$return = $dwCrudCache->add( $dwCrud );
				$this->tableEdit( $dwCrud );
		}
		return $return;
	}
	public function tableCrudReadById(  string $tableName = "DwCrud", $dwCrudId ) : ?DwCrud {
		$return = null;
		if ( ! isset( $this->tables[$tableName] ) || ! \is_object($this->tables[$tableName]) )
			throw new \Exception("no table name :$tableName");
		else {
			$dwCrudCache = $this->tables[$tableName];
			$return = $dwCrudCache->getObject( $dwCrudId );
		}
		return $return;
	}
	public function tableCrudRead( DwCrud $dwCrud ) : ?DwCrud {
		$return = null;
		$tableName = $dwCrud->getClassName();
		if ( ! isset( $this->tables[$tableName] ) || ! is_object($this->tables[$tableName]) )
			throw new \Exception("no table name :$tableName");
		else {
			$dwCrudCache = $this->tables[$tableName];
			$return = $dwCrudCache->getObject( $dwCrud->_id );
		}
		return $return;
	}
	public function tableCrudUpdate( DwCrud $dwCrud ) : DwCrud {
		$return = null;
		$tableName = $dwCrud->getClassName();
		if ( ! isset( $this->tables[$tableName] ) || ! \is_object($this->tables[$tableName]) )
			throw new \Exception("no table name :$tableName");
		else {
			$dwCrudCache = $this->tables[$tableName];
			$return = $dwCrudCache->edit( $dwCrud );
			$this->tableEdit( $dwCrud );
		}
		return $return;
	}
	public function tableCrudDelete( DwCrud $dwCrud ): bool {
		$return = false;
		$tableName = $dwCrud->getClassName();
		if ( ! isset( $this->tables[$tableName] ) || ! \is_object($this->tables[$tableName]) )
			throw new \Exception("no table name :$tableName");
		else {
			$dwCrudCache = $this->tables[$tableName];
			$return = $dwCrudCache->remove( $dwCrud );
			$this->tableEdit( $dwCrud );
		}
		
		return $return;
	}
	public function tableCrudSearch( string $tableName, string $key, $value ) : array {
		$return = array();
		if ( ! isset( $this->tables[$tableName] )
		|| ! \is_object($this->tables[$tableName]) )
			throw new \Exception("no table name :$tableName");
		else {
			$dwCrudCache = $this->tables[$tableName];
			if ( 
				\is_a( $dwCrudCache, dao\DwLinkedDataBase::getStaticClassName() )
			) {
				$keyData=\explode( ':',$key );
				$key = $keyData[0];
				$linkedValue = \intval( $keyData[1] );
				if ( \is_array( $value ) ) {
					foreach( $value as $searchValue ) 
						foreach( $dwCrudCache->getLinkedObjects(
							$linkedValue
							,$key
							,$searchValue
							)
						as $dwCrud )
							$return[] = $dwCrud;
				} else
					foreach( $dwCrudCache->getLinkedObjects(
						$linkedValue
						,$key
						,$value
						)
					as $dwCrud )
						$return[] = $dwCrud;
			} else {
				if ( is_array( $value ) ) {
					foreach( $value as $searchValue ) 
						foreach( $dwCrudCache->getObjects( $key, $searchValue )
							as $dwCrud )
							$return[] = $dwCrud;
				} else
					foreach( $dwCrudCache->getObjects( $key, $value ) as $dwCrud )
						$return[] = $dwCrud;
			}
		}
		return $return;
	}
	public function tableDelete( string $tableName ) : bool {
		$return = false;
		if ( ! isset( $this->tables[$tableName] ) || ! \is_object($this->tables[$tableName]) )
			throw new \Exception("no table name :$tableName");
		else {
			unset($this->tables[$tableName]);
			$return = true;
		}
		return $return;
	}
	public function tableGet( string $tableName ) {
		$return = array();
		if ( ! isset( $this->tables[$tableName] ) || ! \is_object($this->tables[$tableName]) )
			throw new \Exception("no table name :$tableName");
		else
			$return = $this->tables[$tableName]->getCache();
		return $return;
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
	public function searchInArray( IDwCrudCache $iDwCrudCache, array $searchArray ) : array {
		$founds = array();
		if ( ! isset( $searchArray["key" ] ) )
			throw new \Exception( "no key in :\r\n".DwLogger::print_r( $searchArray ) );
		//elseif ( ! isset( $searchArray["value" ] ) )
		//	throw new Exception( "no value in :\r\n".DwLogger::print_r($searchArray) ); 
		else {
			$key = $searchArray["key" ];
			$value = $searchArray["value" ];
			
			$this->log->trace("search $key/$value");
			$founds = $iDwCrudCache->getObjects( $key, $value );
			
			if ( isset( $searchArray[ "and" ] ) && \is_array( $searchArray[ "and" ] ) && isset(  $searchArray[ "and" ]["key" ] ) ) {
				$andFounds = $this->searchInArray( $iDwCrudCache, $searchArray[ "and" ] );
				//$this->log->trace("and :\r\n".DwLogger::print_r( $andFounds ) );
				
				foreach( $founds as $idCrud => $dwCrud ) 
					if ( ! isset( $andFounds[ $idCrud ] ) )
						unset( $founds[ $idCrud ] );
				
			} elseif ( isset( $searchArray[ "or" ] ) && \is_array( $searchArray[ "or" ] ) && isset(  $searchArray[ "or" ]["key" ] ) ) {
				$orFounds = $this->searchInArray( $iDwCrudCache, $searchArray[ "or" ] );
				//$this->log->trace("or :\r\n".DwLogger::print_r( $orFounds ) );
				
				foreach( $orFounds as $idCrud => $dwCrud )
					if ( !isset( $founds[ $idCrud ] ) )
						$founds[ $idCrud ] = $dwCrud;
			}
		}
		//$this->log->trace("result:\r\n".DwLogger::print_r( $founds ) );
		return $founds;
	}
	public function searchArray( array $keysValuesToSearch ) : array {
		$found = array();
		$this->log->trace("array :\r\n".DwLogger::print_r( $keysValuesToSearch ) );
		if ( ! isset( $keysValuesToSearch[ "table" ] ) )
			throw new \Exception( "no table in :\r\n".DwLogger::print_r($keysValuesToSearch) );
		elseif ( ! isset( $keysValuesToSearch[ "search" ] ) )
			throw new \Exception( "no search in :\r\n".DwLogger::print_r($keysValuesToSearch) ); 
		elseif ( ! isset( $this->tables[ $keysValuesToSearch["table" ] ] ) )
			throw new \Exception( "no table like :\r\n".$keysValuesToSearch["table" ] ); 
		else {
			$found = $this->searchInArray(
				$this->tables[ $keysValuesToSearch["table" ] ]
				,$keysValuesToSearch["search" ]
			);
		}
		return $found;
	}
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

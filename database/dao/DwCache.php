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
// use bakuba\kernel\database\dao\DwCache as DwCache;

use bakuba\kernel\database\crud\DwCrud as DwCrud;

/**
 * object variable declaration name
 * @author chapuis
 *
 * @package bakuba
 * @subpackage bakuba.objects
 *
 * @author $Author: chapuis $
 * @version $Revision: 136 $
 *
 */
class DwCache extends aDwDao {
//	static private $sLog = null;
	private static $echoDebug = false;
	
	protected $crudClassName = null;
	protected $tableName = null;
	protected $cache = array();
	public function __construct(
		\bakuba\kernel\database\DwDatabaseProcess $dwDatabaseProcess
		,string $tableName
	) {
		$this->tableName=$tableName;
		parent::__construct( $dwDatabaseProcess );
	}
	/*
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize("cache");
		$this->addtoSerialize("crudClassName");
	}
	//*/
	public function serialize() : string {
		return \serialize( array( 'cache' => $this->cache, 'crudClassName' => $this->crudClassName ) );
	}
	public function unserialize( $strData ) : void {
		$data = \unserialize( $strData );
		$this->cache = $data[ 'cache' ];
		$this->crudClassName = $data[ 'crudClassName' ];
	}
	
	public function getCache( ) : array {
		return $this->cache;
	}
	
	public function add( DwCrud $dwCrud ) : DwCrud {
		foreach( $this->dataLinked as $keyLinked => $tableSrcLink ) {
			if ( ! isset( $dwCrud->$keyLinked ) )
				throw new \Exception( "{$dwCrud->getClassName()} not linked to {$keyLinked}!" );
			else {
				$dwLinkedCrud = $tableSrcLink->getObject( $dwCrud->$keyLinked );
				if ( is_null( $dwLinkedCrud ) )
					throw new \Exception( "{$dwCrud->getClassName()} not crud linked found for {$dwCrud->$keyLinked}!" );
				
			}
		}
		$classNameObj = get_class( $dwCrud );
		if ( is_null( $this->crudClassName )) $this->crudClassName = $classNameObj;
		if ( is_null( $dwCrud->_id) || ! ( $dwCrud->_id >= 0 ) ) {
			$dwCrud->_id = $this->getNewId();
		} elseif ( ! $this->check_newId( $dwCrud->_id ) )
			throw new \Exception( "try to add an id ever present!" );
			
		if ( $classNameObj != $this->crudClassName )
			throw new \Exception( "try to add {classNameObj} for an {$this->crudClassName}" );
		$this->cache[] = $dwCrud;
			
		return $dwCrud;
	}
	public function remove( DwCrud $dwCrud ) : bool {
		$found = false;
		if ( is_object( $dwCrud ) ) {
			foreach( $this->cache as $key => $anDwCrud ) {
				if ( ! is_object( $anDwCrud ) ) unset( $this->cache[ $key ] );
				elseif ( $anDwCrud->_id == $dwCrud->_id ) {
					unset( $this->cache[ $key ] );
					$found = true;
					break;
				}
			}
		}
		return $found;
	}
	public function edit( DwCrud $dwCrud ) : DwCrud {
		$found = null;
		if ( $this->remove( $dwCrud ) )
			$found = $this->add( $dwCrud );
		else
			throw new \Exception("edit echec, remove is false");
		return $found;
	}
	public function getObject( int $_id ) : DwCrud {
		$result=null;
		foreach( $this->cache as $dwCrud ) {
			if ( $dwCrud->_id == $_id ) {
				$result=$dwCrud;
				break;
			}
		}
		return $result;
	}
	
	
	public function getObjects( string $key, string $value = null ) : array{
		$result=array();
		foreach( $this->cache as $dwCrud ) {
			if ( ! is_object( $dwCrud ) )
				continue;
			if ( ( is_null($value) && is_null($dwCrud->$key) ) || $dwCrud->$key == $value )
				$result[ $dwCrud->_id ] = $dwCrud;
			elseif ( is_array( $dwCrud->$key ) ) {
				foreach( $dwCrud->$key as $arrKey => $arrValue ) {
					if ( $arrValue == $value ) {
						$result[ $dwCrud->_id ] = $dwCrud;
						break;
					}
				}
			}
		}
		return $result;
	}
	public function getNewId( ) : int {
		$cptId=1;
		while ( ! $this->check_newId( $cptId ) )
			$cptId++;
		return $cptId;
	}
	
	public function check_newId( int $_id ) : bool {
		$result=true;
		foreach( $this->cache as $dwCrud )
			if ( ! is_object( $dwCrud ) ) continue;
			elseif ( $dwCrud->_id == $_id ) {
				$result=false;
				break;
			}
			
		return $result;
	}
	public function isNew() : bool {
		return true;
	}
	
    public function __serialize() : array {
		$this->DwSerializableInit();
		$serialized = array();
		foreach( $this->toSerialize as $index => $keyValue ) {
			if ( is_array( $keyValue ) )
				$serialized[ $keyValue[0] ] = $keyValue[1];
			else {
				if ( $keyValue == "cache" ) {
					$serialized[ $keyValue ] = array();
					foreach( $this->$keyValue as $arrayValue ) {
						if ( is_object( $arrayValue ) && method_exists( $arrayValue,"__serialize" ) )
							$serialized[ $keyValue ][] = $arrayValue->__serialize();
						else
							$serialized[ $keyValue ][] = $arrayValue;
					}
				} else {
					$serialized[ $keyValue ] = $this->$keyValue;
				}
			}
		}
		if (self::$echoDebug )
			echo $this->getClassName()."->".__METHOD__." => count:".count($serialized)."\r\n";
		return $serialized;
	}
    public function __unserialize( array $data ) : void {
		foreach( $data as $key => $value ) {
			if ( $key == "cache" ) 
				continue;
			else
				$this->$key = $value ;
		}
		if ( isset( $data["cache"] ) ) {
			foreach( $data["cache"] as $dataDwCrud ) {
				if ( is_array( $dataDwCrud ) ) {
					//$dwCrud = \darkphp\DwObject::createInstanceWithoutConstructor( $this->crudClassName );
					$crudClassName = $this->crudClassName;
					$dwCrud = new $crudClassName( $this );
					$dwCrud->__unserialize( $dataDwCrud );
					$this->cache[]=$dwCrud;
				}
			}
			//self::$sLog->trace("[".$this->getClassName()."] cache:".count( $this->cache ) );
		}
		if (self::$echoDebug )
			echo $this->getClassName()."->".__METHOD__." => count:".count($data)."\r\n";
    }
	/*
    public function __serialize() : array {
		$serialized = null;
		try {
			$serialized = parent::__serialize();
		} catch(\Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(\Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $serialized;
	}
    public function __unserialize( array $data ) : void {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::__unserialize( $data );
			
		} catch(\Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(\Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
    }
	*/
	private $dataLinked = array();
	public function crudLink( string $keyLinked ,DwCache $tableSrcLink ) : bool {
		if ( isset( $this->dataLinked[ $keyLinked ] ) )
			return false;
		else
			$this->dataLinked[ $keyLinked ] = $tableSrcLink;
		return true;
		
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

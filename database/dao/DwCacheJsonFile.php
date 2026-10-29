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
//use bakuba\kernel\database\dao\DwCacheJsonFile as DwCacheJsonFile;

use bakuba\kernel\io\stream\file\DwFile as DwFile;
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
class DwCacheJsonFile extends DwCacheFile {
	private static $echoDebug = false;
	
	public function __construct(
		\bakuba\kernel\database\DwDatabaseProcess $dwDatabaseProcess
		,string $tableName
		,string $fileExt ="json"
	) {
		parent::__construct( $dwDatabaseProcess ,$tableName ,$fileExt );
	}
	private function read( ) : void {
		$this->log->trace( $this->toString() );
		$fileContent = DwFile::getContent( $this->filePath );
		
		if ( is_null( $fileContent ) )
			$this->write();
		else {
			$this->unserialize( $fileContent );
			$this->isNewFile = false;
		}
		
		$this->log->trace("\r\n[{$this->tableName}] count:".count($this->cache)."\r\n[".$this->toString()."]\r\n");
	}
	public function write( ) : bool {
		
		$return = DwFile::putContent( $this->filePath, $this->serialize() );
		
		$this->log->trace("\r\n[{$this->tableName}] count:".count($this->cache)."\r\n[".$this->toString()."]\r\n");
		
		return $return;
	}
	
	public function serialize() : string {
		$serialized = json_encode( $this->__serialize() );
		if (self::$echoDebug )
			echo $this->toString()."->".__METHOD__." => strlen:".strlen($serialized)."\r\n";
		return $serialized;//
    }
    public function unserialize( $serialized ) : void {
		if (self::$echoDebug )
			echo $this->toString()."->".__METHOD__." => strlen:".strlen($serialized)."\r\n";
		$this->__unserialize( json_decode( $serialized, true ) );
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
			echo $this->toString()."->".__METHOD__." => count:".count($serialized)."\r\n";
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
				//$dwCrud = \darkphp\DwObject::createInstanceWithoutConstructor( $this->crudClassName );
				$crudClassName = $this->crudClassName;
				$dwCrud = new $crudClassName( $this );
				$dwCrud->__unserialize( $dataDwCrud );
				$this->cache[]=$dwCrud;
			}
			$this->log->trace("[".$this->toString()."] cache:".count( $this->cache ) );
		}
		if (self::$echoDebug )
			echo $this->toString()."->".__METHOD__." => count:".count($data)."\r\n";
    }
	/*
	private $isNewFile = true;
	public function isNew() : bool {
		return $this->isNewFile;
	}
	public function add( DwCrud $dwCrud ) : DwCrud {
		$return = null;
		$this->log->trace("[".$this->toString()."]");
		$return = parent::add( $dwCrud );
		$this->write();
		
		return $dwCrud;
	}
	public function remove( DwCrud $dwCrud ) : bool {
		$found = false;
		$this->log->trace("[".$this->toString()."]");
			
		$found = parent::remove( $dwCrud );
		$this->write();
		
		return $found;
	}
	public function edit( DwCrud $dwCrud ) : DwCrud {
		$return = parent::edit( $dwCrud );
		
		$this->write();
		
		return $return;
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

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

namespace bakuba\kernel\objects\serialize;
//use bakuba\kernel\objects\serialize\DwJson as DwJson;

//use bakuba\kernel\objects\serialize\IDwJson as IDwJson;

use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;
use \JsonSerializable as JsonSerializable;

use darkphp\DwObject as DwObject;

use bakuba\kernel\DwLogger\DwLogger as DwLogger;

	/*
    "boolean"
    "integer"
    "double" (for historical reasons "double" is returned in case of a float, and not simply "float")
    "string"
    "array"
    "object"
    "resource"
    "resource (closed)" as of PHP 7.2.0
    "NULL"
    "unknown type"
	*/
class DwJson implements IDwSerializable ,IDwJson {
	static private $sLog = null;
	//DwJson::$type
	static public $type = array( "boolean" ,"integer" ,"double" ,"string" ,"array" ,"object" ,"resource" ,"resource (closed)" ,"NULL" ,"unknown type" );
	static public $typeInString = array( "resource" ,"resource (closed)" ,"NULL" ,"unknown type" );
	
	protected $keys = array();
	protected $data = array();
	protected $prototype = array();
	protected $classNames = array();
	
	public function __construct() {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			parent::__construct();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function DwSerializableInit() {
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::DwSerializableInit();
			//$this->addJsonAttribute( "thiscn", "thiscn", "string" ,$this->getClassName() );
			
			$this->addtoSerialize( "keys" );
			$this->addtoSerialize( "data" );
			$this->addtoSerialize( "prototype" );
			$this->addtoSerialize( "classNames" );
			//$this->classname=$;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function getModel() : array {
		$dataModel = array();
		foreach( $this->keys as $intKey => $extKey ) {
			$dataModel[ $intKey ] = array(
				"key" => $extKey
				,"type" => $this->prototype[ $intKey ]
				,"class" => $this->classNames[ $intKey ]
			);
		}
		return $dataModel;
	}
	
	public function getJsonModel() : string {
		return json_encode( $this->getModel() );
	}
	protected function addJsonAttribute( string $intKey ,string $extKey ,string $typename, string $className = null ) {
		try {
			$this->keys[ $intKey ] = $extKey;
			if ( array_search( $typename , self::$type ) === false )
				throw new Exception( "unknow type name" );
			else {
				if ( array_search( $typename , self::$typeInString ) !== false ) {
					$typename="string";
				}
				$this->prototype[ $intKey ] = $typename;
				$this->data[ $intKey ] = null;
				if ( $typename == "object" ) {
					if ( is_null( $className ) )
						throw new Exception("no object without classname defined" );
					else
						$this->classNames[ $intKey ] = $className;
				} else if ( ! is_null( $className ) )
					self::$sLog->trace( "why className:$className for an not 'object' type :$typename" );
				
				self::$sLog->trace( "define $intKey:$extKey as $typename" );
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
    public function __set( string $intKey ,$value) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			$typeValue = gettype($value);
			if ( isset( $this->keys[$intKey] ) ) {
				if ( $typeValue != $this->prototype[ $intKey ] &&  $typeValue != "NULL") {
					if ( $typeValue == "string" && $this->prototype[ $intKey ] == "object" ) {
						$strValue = $value;
						//$value = DwObject::createInstanceWithoutConstructor( $this->classNames[ $intKey ] );
						$className=$this->classNames[ $intKey ];
						$value = new $className( $this );
						$value->jsonUnserialize( json_decode( $strValue , true ) );
						//$this->__set( $intKey , $value );
					} else
						throw new Exception("[ $intKey ] try to put $typeValue for an ".$this->prototype[ $intKey ] );
				} else if ( $typeValue == "object" )
					if ( $value->getClassName() != $this->classNames[ $intKey ] )
						throw new Exception( "[ $intKey ] try to put ".$value->getClassName()." for an ".$this->classNames[ $intKey ] );
			} else 
				$this->addJsonAttribute( $intKey, $intKey, $typeValue, is_object( $value ) ? $value->getClassName() : null );
			
			$this->data[$intKey] = $value;
			
			$strCount = "0";
			if ( is_array( $value ) )
				$strCount = count( $value );
			else
				$strCount = strlen($value);
				
			self::$sLog->trace( "set[$intKey/$typeValue]:$strCount [".$this->getClassName()."]" );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
    }

    public function __get( string $intKey ) {
		$dataVal = null;
		try {
			if ( is_null( self::$sLog ) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			//self::$sLog->trace("get $intKey [".$this->getClassName()."]" );
			if ( $this->__isset( $intKey ) )
				$dataVal = $this->data[ $intKey ];
			else
				self::$sLog->error("no data set for:$intKey [".$this->getClassName()."]" );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
        return $dataVal;
    }

    public function __isset( string $intKey ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			return array_key_exists( $intKey ,$this->data ) && isset( $this->data[ $intKey ] );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return null;
    }
	
    public function __unset( $intKey ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			unset( $this->data[ $intKey ] );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		//return null;
    }
	/*
	public function getIdJson($key) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			if ( isset($this->keys[$key]) )
				return $this->keys[$key];
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return null;
	}
	public function setIdJson($key, $idJson) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			//if ( isset($this->keys[$key]) )
			$this->keys[$key] = $idJson;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	*/
    public function toString( $withParent = false ) : string {
		$objStr = null;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			self::$sLog->trace($this->getClassName());
			$objStr = json_encode( $this );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $objStr;
	}
	
	public function serialize() : string {
		$dataArr = array( "model" => $this->getJsonModel() ,"" => );
		$serialized = json_encode( $this->__serialize() );
		if (self::$echoDebug )
			echo $this->getClassName()."->".__METHOD__." => strlen:".strlen($serialized)."\r\n";
		return $serialized;//
    }
    public function unserialize( string $serialized ) : void {
		if (self::$echoDebug )
			echo $this->getClassName()."->".__METHOD__." => strlen:".strlen($serialized)."\r\n";
		$this->__unserialize( json_decode( $serialized ) );
    }
	
	public function jsonSerialize() : array {
		$toJsonData = array();
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			//self::$sLog->trace($this->getClassName());
			foreach( $this->keys as $intKey => $extKey ) {
				if ( isset( $this->data[$intKey] ) )
					$toJsonData[ $extKey ] = $this->data[$intKey];
				//else
				//	$toJsonData[$idJson] = null;
			}
			//self::$sLog->trace($toJsonData);
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $toJsonData;
	}
	public function jsonUnserialize( array $jsonData ) : void {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			self::$sLog->trace( $this->getClassName() );
			
			foreach( $this->keys as $intKey => $extKey ) {
				if ( isset( $jsonData[ $extKey ] ) ) {
					$this->__set( $intKey ,$jsonData[$extKey] );
					unset( $jsonData[ $extKey ] );
				} else if ( isset( $this->data[$intKey] ) )
					unset( $this->data[ $intKey ] );
				else
					$this->data[ $intKey ] = null;
			}
			foreach( $jsonData as $key => $value )
				$this->__set( $key, $value );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}	
	}
	/*
    public function __unserialize( array $data ) : void {
		try {
			foreach( $data as $key => $value ) 
				$this->$key = $this->__unserializeValue( $key ,$value );
		
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
    }
	/*
	/*
	
	
	public static function fromJson( $strJson ) : IDwJson {
		$dwJson = null;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			self::$sLog->trace(self::getStaticClassName());
			$dwJson = new self();
			$dwJson->jsonUnserialize(json_decode($strJson,true));
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".self::getStaticClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".self::getStaticClassName()."]");
			throw $error;
		}
		return $dwJson;
	}
	
    public function __serialize() : array {
		$data = null;
		try {
			$data = parent::__serialize();
			self::$sLog->trace($this->getClassName());
			$data[$this->getClassName()."::keys"]=array();
			$data[$this->getClassName()."::data"]=array();
			foreach($this->keys as $key => $idJson) {
				$data[$this->getClassName()."::keys"][$key] = $idJson;
				if ( $this->__isset($key) )
					$data[$this->getClassName()."::data"][$key] = $this->__get($key);
				//else
				//	$data[$this->getClassName()."::data"][$key] = null;
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $data;
	}
	
    public function __unserialize( array $data ) : void
    {
		try {
			parent::__unserialize();
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			self::$sLog->trace($this->getClassName());
			$this->data = array();
			$this->keys = array();
			
			$dataKey = $data[$this->getClassName()."::keys"];
			$dataList = $data[$this->getClassName()."::data"];
			foreach($dataKey as $key => $idJson) {
				if ( isset($dataList[$key]) )
					$this->__set($key, $dataList[$key]);
				elseif ( $this->_isset($key) )
					$this->_unset($key);
				
				$this->keys[$key] = $idJson;
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
    }
	*/
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

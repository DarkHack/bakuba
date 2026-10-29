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

namespace darkphp\serializable\json;
//use darkphp\serializable\json\DwJson as DwJson;

//use bakuba\kernel\objects\serialize\IDwJson as IDwJson;

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

class DwJson extends \darkphp\serializable\aDwSerializable implements iDwJson ,\IteratorAggregate {
	private $internalJsonData = array();
	/*
	public function __construct() {
		//parent::__construct();
	}
	//*/
	///*
	public function DwSerializableInit() {
		//parent::DwSerializableInit();
		//$this->addValuetoSerialize( "className" , $this->getClassName() );
		//$this->addtoSerialize( "toSerialize" );
	}
	protected function addValuetoSerialize(
		string $attributeKey
		, $attributeValue
	) {
			$this->internalJsonData[$attributeKey] = $attributeValue;
	}
	protected function addtoSerialize( string $attributeKey ) {
			$this->internalJsonData[$attributeKey] = null;
	}
    public function __unserializeValue( $key ,$data ) {
		throw new \Exception( "not implemented" );;
	}
    public function __serializeValue( $key ,$value ) {
		throw new \Exception( "not implemented" );;
	}
	/*
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		//$this->addJsonAttribute( "thiscn", "thiscn", "string" ,$this->getClassName() );
		//$this->addtoSerialize( "data" );
		
		//$this->addtoSerialize( "keys" );
		//$this->addtoSerialize( "prototype" );
		//$this->addtoSerialize( "classNames" );
		
		//$this->classname=$;
	}
	//*/
	private function checkArray( array $myArray ) : bool {
		$valid = true;
		foreach( $myArray as $idx => $value ) {
			$typeValue = gettype($value);
			if ( \array_search( $typeValue ,self::$forbidenType ) !== false )
				throw new \Exception( "forbiden Type '{$typeValue}' in array index :[{$idx}]for Json" );
			elseif ( $typeValue == "array" )
				$valid&=$this->checkArray( $value );
			
		}
		return $valid;
	}
    public function __set( string $intKey ,$value ) : void {
		$typeValue = gettype($value);
		if ( array_search( $typeValue , self::$forbidenType ) !== false ) {
			throw new \Exception( "forbiden Type :'$typeValue' for key '$intKey' Json" );
		} else {
			if ( $typeValue == "array" && ! $this->checkArray($value) )
				throw new \Exception( "an forbiden Type in array for Json" );
				
			$this->internalJsonData[$intKey] = $value;
		}
		/*
		$strCount = 0;
		if ( is_array( $value ) )
			$strCount = count( $value );
		elseif ( ! is_null( $value ) )
			$strCount = strlen( $value );
		//*/
    }

    public function __get( string $intKey ) {
		$dataVal = null;
		if ( $this->__isset( $intKey ) )
			$dataVal = $this->internalJsonData[ $intKey ];
		else
			if ( \is_object( $this->log ) ) $this->log->trace( "no data set for:$intKey" );
		
        return $dataVal;
    }

    public function __isset( string $intKey ) : bool {
		return \array_key_exists( $intKey ,$this->internalJsonData )
		&& isset( $this->internalJsonData[ $intKey ] );
    }
	
    public function __unset( string $intKey ) : void {
		unset( $this->internalJsonData[ $intKey ] );
    }
	public function serialize() : string {
		$serialized = \json_encode(
			$this->__serialize()
			,JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);
		if ( \is_object( $this->log ) )
			$this->log->trace( "strlen:".strlen($serialized) );
		
		return $serialized;//
    }
    public function unserialize( $serialized ) : void {
		$this->__unserialize( \json_decode( $serialized ,true ) );
		
		if ( \is_object( $this->log ) )
			$this->log->trace( $this->getClassName()."->".__METHOD__." => strlen:".strlen($serialized) );
    }
	public function jsonSerialize() : array {
		$toJsonData = array();
		//echo "json Serialize()\r\n";
		//self::$sLog->trace($this->getClassName());
		//self::$sLog->trace($this->getClassName());
		foreach( $this->internalJsonData as $key => $value )
			$toJsonData[ $key ] = $value;
		return $toJsonData;
	}
	public function jsonUnserialize( array $jsonData ) : void {
		//echo "json Unserialize()\r\n";
		//unset($this->internalJsonData);
		//$this->internalJsonData=array();
		foreach( $jsonData as $key => $value )
			$this->$key = $value;
	}
    public function __serialize() : array {
		$this->DwSerializableInit();
		return $this->jsonSerialize();
	}
    public function __unserialize( array $data ) : void {
		$this->jsonUnserialize( $data );
    }
	public function getIterator() : \ArrayIterator {
        return new \ArrayIterator( $this->internalJsonData );
    }
    /*
	protected const CLASS_SEPARATOR = \darkphp\autoload\DwAutoLoadBoot::CLASS_SEPARATOR;
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? "//".self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	*/
	
    public function __toString() : string {
		//return \serialize( $this );
		return $this->serialize( );
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
	/*
	"boolean"
	"integer"
	"double" (pour des raisons historiques, "double" est retournée lorsqu'une valeur de type float est fournie, et non "float"")
	"string"
	"array"
	"NULL"
	"object"
	"resource"
	"resource (closed)" à partir de PHP 7.2.0
	"unknown type"
	//*/
	static public $allowType 
		= array( "boolean" ,"integer" ,"double" ,"string" ,"array" ,"NULL" );
	static public $forbidenType 
		= array( "object" ,"resource" ,"resource (closed)" ,"unknown type" );
	
	public static function test(
		\darkphp\logger\iDwLogger $iDwLogger
		,\darkphp\serializable\iDwNoSerializable $dwToTest = null
	) : \darkphp\serializable\iDwNoSerializable {
		if ( \is_null( $dwToTest ) ) {
			$dwToTest = new self();
			$dwToTest->setDebug( $iDwLogger );
		}
		$iDwLogger->trace( "\r\n\tassigne value string" );
		$dwToTest->myKey = "myValue";
		$iDwLogger->trace( "\r\n\tassigne value int" );
		$dwToTest->myInt = 5;
		$iDwLogger->trace( "\r\n\tassigne value float" );
		$dwToTest->myFloat = 3.14;
		$iDwLogger->trace( "\r\n\tTry print ---" );
		$iDwLogger->trace( $dwToTest );
		
		\darkphp\serializable\DwSerializable::test( $iDwLogger ,$dwToTest );
		$dwToTest->myJson = $dwToTest;
		\darkphp\serializable\DwSerializable::test( $iDwLogger ,$dwToTest );
		return $dwToTest;
	}
}
?>

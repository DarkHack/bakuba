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

namespace darkphp\serializable;
//use darkphp\serializable\aDwSerializable as DwSerializable;

use darkphp\DwObject as DwObject;

abstract class aDwSerializable extends aDwNoSerializable implements iDwSerializable {
	///*
    protected function __construct( aDwNoSerializable $dwParentNoSerializable = null ) {
		parent::__construct( \is_null( $dwParentNoSerializable ) ? $this : $dwParentNoSerializable );
	}
	//*/
	protected $toSerialize = null;
	public function DwSerializableInit() {
		$this->toSerialize = array();
		
		$this->addValuetoSerialize(
			aDwSerializable::getStaticClassName()."-className"
			,$this->getClassName()
		);
		//$this->addtoSerialize( "toSerialize" );
	}
	
	protected function addValuetoSerialize(
		string $attributeKey
		, $attributeValue
	) {
			$this->toSerialize[] = array( $attributeKey ,$attributeValue );
	}
	protected function addtoSerialize( string $attributeKey ) {
			$this->toSerialize[] = $attributeKey;
	}
    
	///*
	public function serialize() : string {
		$serialized = \serialize( $this->__serialize() );
		if ( \is_object( $this->log ) ) $this->log->trace( "strlen:".strlen($serialized) );
		return $serialized;//
    }
    public function unserialize( $serialized ) : void {
		$this->__unserialize( \unserialize( $serialized ) );
		if (\is_object ( $this->log ) ) $this->log->trace( "strlen:".\strlen($serialized) );
    }
	//*/
    public function __unserializeValue( $key ,$data ) {
		$debugKey ="key:{$key}";
		$unserialized = null;
		$keyCN = DwSerializable::getStaticClassName()."-className";
		if ( is_array( $data ) && isset( $data[ $keyCN ] ) ) {
			$debugKey.="/cn:{$data[$keyCN]}";
			if ( \is_object( $this->log ) ) $this->log->trace( "[{$debugKey}] count:".count($data) );
			/*
			$instance 
				= DwObject::createInstanceWithoutConstructor(
					$data[$keyCN]
			);
			//*/
			$className = $data[$keyCN];
			$instance = new $className( $this );
			if ( is_object($instance)
			&&  method_exists( $instance ,"__unserialize" ) )
				$instance->__unserialize( $data );
			
			$unserialized = $instance;
		} else {
			if ( \is_string( $data ) ) {
				if ( \is_object( $this->log ) ) $this->log->trace( "[{$debugKey}] strlen:".strlen($data) );
				if ( \darkphp\string\DwString::startWith( $data ,"C:" ) ) {
					try {
						$unserialized = unserialize( $data );
					} catch(\Exception $exceptionUnserialize) {
						$unserialized = $data;
						if ( \is_object( $this->log ) ) {
							$this->log->error( $exceptionUnserialize );
							$this->log->throwable( $exceptionUnserialize );
						}
						//throw $exceptionUnserialize;
					} catch(\Error $errorUnserialize) {
						$unserialized = $data;
						if ( \is_object( $this->log ) ) {
							$this->log->error( $errorUnserialize );
							$this->log->throwable( $errorUnserialize );
						}
						//throw $errorUnserialize;
					}
				} else $unserialized = $data;
			} else {
				$unserialized = $data;
				if ( \is_object( $this->log ) ) $this->log->trace( "[{$debugKey}] unknow :".gettype($data) );
			}
		}
		if ( \is_object( $this->log ) ) $this->log->trace( "[{$debugKey}]  ".gettype( $unserialized ) );
		return $unserialized;
	}
    public function __serializeValue( $key ,$value ) {
		$debugKey ="key:{$key}";
		$serialized = null;
		///*
		if ( \is_object($value) && \method_exists( $value ,"__serialize" ) )
			$serialized = $value->__serialize();
		else
			$serialized = $value;
		//*/
		//$serialized = serialize($value);
		if ( \is_object( $this->log ) ) {
			if ( \is_array( $serialized ) )
				$this->log->trace( "[{$debugKey}] count:".\count($serialized) );
			elseif ( \is_string( $serialized ) )
				$this->log->trace( "[{$debugKey}] strlen:".\strlen($serialized) );
			else
				$this->log->trace( "[{$debugKey}] ".gettype( $serialized )  );
		}
		return $serialized;
	}
	
    public function __serialize() : array {
		$this->DwSerializableInit();
		$serialized = array();
		foreach( $this->toSerialize as $index => $keyValue ) {
			if ( is_array( $keyValue ) )
				$serialized[ $keyValue[0] ] = $this->__serializeValue(
					$keyValue[0]
					,$keyValue[1]
				);
			else
				$serialized[ $keyValue ] = $this->__serializeValue(
					$keyValue
					,$this->$keyValue
				);
		}
		if ( \is_object( $this->log ) ) $this->log->trace( "count:".\count($serialized) );
		return $serialized;
	}

    public function __unserialize( array $data ) : void {
		foreach( $data as $key => $value ) 
			$this->$key = $this->__unserializeValue( $key ,$value );
		if ( \is_object( $this->log ) ) $this->log->trace( "count:".\count($data) );
    }
	
	/*
	public function __destruct( ) {
		unset( $this->toSerialize );
		parent::__destruct();
	}
	//*/
	
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) )
				unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	//\darkphp\serializable\aDwSerializable::IntSerialize( 10 );
	public static function IntSerialize( int $toSerialize ) : self {
		return new class (  $toSerialize ) extends aDwSerializable {
			protected $myInt = null;
			public function DwSerializableInit() {
				parent::DwSerializableInit();
				$this->addtoSerialize("myInt");
			}
			protected function __construct( int $value ) {
				$this->myInt = $value;
				parent::__construct();
			}
			public function toString( $withParent = false ) : string {
				return $this->myInt;
			}
			public function getValue() : int {
				return is_int( $this->myInt ) ? $this->myInt : \intval( $this->myInt ) ;
			}
		}; 
	}
	public static function test(
		\darkphp\logger\iDwLogger $iDwLogger
		,iDwNoSerializable $dwToTest = null
	) : iDwNoSerializable {
		if ( \is_null( $dwToTest ) ) {
			$dwToTest = new self();
			$dwToTest->setDebug( $iDwLogger );
		}
		$iDwLogger->trace( "\r\n\tTry Serialize ClassName:{$dwToTest->getClassName()}\r\n\t\tprint_r:\r\n\t".DwObject::print_r( $dwToTest, 3 )."" );
		$strData = $dwToTest->serialize();
		//unset( $dwToTest );
		$iDwLogger->trace( "\r\n\tserialize 1:{$strData}" );
		//$dwUnserialized = DwObject::createInstanceWithoutConstructor( $dwToTest->getClassName() );
		$className = $dwToTest->getClassName();
		$dwUnserialized = new $className( $dwToTest );
		$dwUnserialized->unserialize( $strData );
		$iDwLogger->trace( "\r\n\t UnSerialized ClassName:{$dwUnserialized->getClassName()}\r\n\t\tprint_r:\r\n\t".DwObject::print_r( $dwUnserialized, 3 )."" );
		$strData = $dwUnserialized->serialize();
		$iDwLogger->trace( "\r\n\tserialize 2:{$strData}" );
		return $dwToTest;
	}
}
?>

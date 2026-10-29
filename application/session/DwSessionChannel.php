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

namespace bakuba\kernel\application\session;
//use bakuba\kernel\application\session\DwSessionChannel as DwSessionChannel;

class DwSessionChannel extends \darkphp\serializable\aDwSerializable  implements iDwSessionChannel {
	protected $channelData = array();
	
	public function __construct( \darkphp\DwObject $parent ,int $channelKey ) {
		parent::__construct( $parent );
		$this->channelDataSet( "channelKey" ,$channelKey );
		$this->log->trace( "." );
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		$this->addtoSerialize("channelData");
	}
	
	public function getChannelKey() {
		return $this->channelDataGet("channelKey");
	}
	/*
	public function setChannelKey( $channelKey ) {
		$this->channelDataSet("channelKey", $channelKey);
	}
	//*/
	/******************  MAGIQUE METHODE ***************************/
    function __isset( $isset ) : bool {
		return $this->channelDataIsset( $isset );
    }
    function __unset($unset) {
		$this->channelDataUnset( $unset );
	}
	function __set( $set, $val ) {
		$this->channelDataSet( $set ,$val );
    }
    function __get( $get ) {
		$result = null;
		if ( $this->channelDataIsset( $get ) )
			$result = $this->channelDataGet( $get );
        return $result;
    }
    /******************** CHANNEL DATA ***************************/
	public function channelDataIsset( $attributeName ) : bool {
		return isset( $this->channelData[$attributeName] );
	}
	public function channelDataUnset( $attributeName ) : void {
		unset( $this->channelData[$attributeName] );
	}
	public function channelDataGet( $attributeName ) {
		$data = null;
		if ( $this->channelDataIsset($attributeName) )
			$data = $this->channelData[$attributeName];
		return $data;
	}
	public function channelDataSet( $attributeName ,$attributeValue ) {
		$this->channelData[$attributeName] = $attributeValue;
	}
	/*****************************************************************/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__
			: __CLASS__
		;
	}
}

?>

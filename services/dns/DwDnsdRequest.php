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

namespace bakuba\kernel\services\dns;
//use bakuba\kernel\services\dns\DwDnsdRequest as DwDnsdRequest;

class DwDnsdRequest extends \bakuba\kernel\services\DwServicedRequest {
	public function getDwDnsdOrder() : DwDnsdOrder {
		return $this->dwServicedOrder;
	}
	public function __construct( DwDnsdOrder $dwDnsdOrder ,object $myobject = null ) {
		parent::__construct( $dwDnsdOrder ,$myobject );
	}
	public function toString( $withParent = false ) : string {
		$str=parent::toString( $withParent );
		$str.="dwDnsdOrder:'{$this->dwServicedOrder->toString()}'";
		return $str;
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
}
?>

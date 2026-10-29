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

namespace bakuba\kernel\agartha;
//use bakuba\kernel\agartha\DwBakubaResponse as DwBakubaResponse;

class DwBakubaResponse {
	private $message=null;
	
	public function __construct( string $message = null ) {
		$this->message = $message;
	}
	
	public function getMessage( ) : ?string {
		return $this->message;
	}
	/*
	private $myobject = null;
	public function getObject( ) {
		return $this->myobject;
	}
	public function setObject( $myobject ) {
		$this->myobject = $myobject;
	}
	*/
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return __CLASS__;
	}
}
?>

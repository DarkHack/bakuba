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

namespace bakuba\kernel\application\service;
//use bakuba\kernel\application\service\DwApplicationResponse as DwApplicationResponse;

class DwApplicationResponse extends \bakuba\kernel\application\DwApplicationResponse {
	private $rawObjectData=null;
	public function __construct( object $rawObjectData = null ) {
		$this->rawObjectData = $rawObjectData;
		parent::__construct();
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		$this->addtoSerialize( "rawObjectData" );
	}
	public function getRawData() {
		return $this->rawObjectData;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}



?>


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

namespace bakuba\kernel\services\http;
//use bakuba\kernel\services\http\DwHttpServiceRequest as DwHttpServiceRequest;

use bakuba\kernel\protocole\http\DwHttpRequest as DwHttpRequest;

class DwHttpServiceRequest extends \bakuba\kernel\services\DwServiceRequest {
	
	public function getDwHttpRequest() : DwHttpRequest {
		return $this->parent;
	}
	public function __construct( DwHttpRequest $dwHttpRequest ) {
		parent::__construct( $dwHttpRequest );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

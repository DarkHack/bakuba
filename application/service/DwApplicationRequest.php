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
//use bakuba\kernel\application\service\DwApplicationRequest as DwApplicationRequest;

use bakuba\kernel\services\DwServiceRequest as DwServiceRequest;

class DwApplicationRequest extends \bakuba\kernel\application\DwApplicationRequest {
	public function getDwServiceRequest( ) : DwServiceRequest {
		return $this->getObject();
	}
	
	public function __construct(
		DwServiceRequest $dwServiceRequest
		,\darkphp\DwObject $dwParent = null
	) {
		parent::__construct( $dwServiceRequest ,$dwParent ); 
	}

	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

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
//use bakuba\kernel\services\dns\DwDnsdOrder as DwDnsdOrder;

class DwDnsdOrder extends \bakuba\kernel\services\DwServicedOrder {
	const bindPortToServer = 0;		// DwDnsdOrder::bindPortToServer
	
	private static $enums= array( self::bindPortToServer );
	private static $levelMap = null;
	
	// $anDwOrder = self::getOrder( DwOrder::bindPortToServer );
	public static function getOrder( int $order ) : \bakuba\kernel\services\DwServicedOrder {
		if( ! isset( self::$levelMap ) || \is_null( self::$levelMap ) ) {
			self::$levelMap=array();
			foreach( self::$enums as $intOrder )
				self::$levelMap[ $intOrder ]=new self( $intOrder );
		}
		return self::$levelMap[ $order ];
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

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

namespace bakuba\kernel\services;
//use bakuba\kernel\services\DwServicedOrder as DwServicedOrder;

class DwServicedOrder extends \bakuba\kernel\objects\aDwOrder {
	
	const bindPortToServer = 0;		// DwServiceOrder::bindPortToServer
	
	private static $levelMap = null;
	public static function sInit() : void {
		self::$levelMap=array();
		foreach( self::constParse( self::getStaticClassName() ) as $constName => $constValue ) {
			//echo "{$myClassName}::{$constName}=>{$constValue}\r\n";
			$index = \intval( $constValue );
			if ( isset( self::$levelMap[ $index ] ) )
				throw new \Exception( "index:{$index} ever defined for {$constName}" );
			if ( ! \is_int( $index ) )
				throw new \Exception( "index:{$index} not an int for {$constName}" );
			self::$levelMap[ $index ]=new self( $index );
		}
		//echo print_r( self::$levelMap ,true );
	}
	// $anDwOrder = self::getOrder( DwOrder::bindPortToServer );
	public static function getOrder( int $order ) : \bakuba\kernel\services\DwServicedOrder {
		return self::$levelMap[ $order ];
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
DwServicedOrder::sInit();
?>

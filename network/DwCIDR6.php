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

namespace bakuba\kernel\network;
//use bakuba\kernel\network\DwCIDR6 as DwCIDR6;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;

class DwCIDR6 extends DwCIDR {
	public function __construct( DwInterface $parent ,string $CIDR = "::/128" ) {
		parent::__construct( $parent ,$CIDR );
	}
	/*
	public function randomIp() : DwIp {
		return $this->getIp( '127.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254) );
	}
	*/
	public function getIp( string $ipAddress = "::1" )  : DwIp6 {
		return $this->addIp( $ipAddress );
	}
	public function addIp( string $ipAddress = "::1" ) : DwIp6 {
		if ( ! isset( $this->ips[ $ipAddress ] ) && $this->inside( $ipAddress ) ) {
			$dwIp = new DwIp6( $this ,$ipAddress, true );
			//$this->insert( $dwIp );
			$this->ips[ $ipAddress ]= $dwIp;
		}
		return $this->ips[ $ipAddress ];
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

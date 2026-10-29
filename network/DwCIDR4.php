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
//use bakuba\kernel\network\DwCIDR4 as DwCIDR4;

class DwCIDR4 extends DwCIDR {
	public function __construct( DwInterface $parent ,string $CIDR = "127.0.0.0/8" ) {
		parent::__construct( $parent ,$CIDR );
	}
	public function getDwInterface() : DwInterface {
		return $this->parent;
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize("CIDR");
		$this->addtoSerialize("ips");
	}
	public function randomIp() : DwIp4 {
		return $this->getIp( '127.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254) );
	}
	public function getIp( string $ipAddress = "127.0.0.1" )  : DwIp4 {
		return $this->addIp( $ipAddress );
	}
	public function addIp( string $ipAddress = "127.0.0.1" ) : DwIp4 {
		if ( ! isset( $this->ips[ $ipAddress ] ) ) {
			if ( $this->inside( $ipAddress ) )
				$this->ips[ $ipAddress ]= new DwIp4( $this ,$ipAddress );
			else
				throw new \Exception( "{$ipAddress} not in {$this->CIDR}." );
		}
		return $this->ips[ $ipAddress ];
	}
	/*
	function inside( $dwIp )  : bool {
		$this->log->info( $dwIp );
		$result = self::IsInside( $this->CIDR, $dwIp );
		if ( \is_object( $this->log ) )
			$this->log->trace( "dwIp: $dwIp =>".( $result ? "inside"  : "not inside" )." " );
		
		return $result;
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

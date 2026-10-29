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

namespace bakuba\kernel\io;
//use bakuba\kernel\io\DwResourceIpConnexion as DwResourceIpConnexion;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\network\DwIp as DwIp;
use bakuba\kernel\network\DwPort as DwPort;

class DwResourceIpConnexion extends DwResourceConnexion /*implements iDwResourceIp*/ {
	private $dwPortServer = null;
	public function getSrvIp() : DwIp {
		return $this->dwPortServer->getIp();
	}
	public function getSrvPort() : DwPort {
		return $this->dwPortServer;
	}
	public function getCltPort() : DwPort {
		return $this->getDwResourceIp()->getDwPort();
	}
	public function getCltIp() : DwIp {
		return $this->getCltPort()->getIp();
	}
	
	public function getDwResourceIp() : DwResourceIp {
		return $this->getDwResource();
	}
	public function __construct(
		DwResourceSelect $dwResourceSelect
		,DwResourceIp $dwResourceIp
		,DwPort $dwPortServer
	) {
		$this->dwPortServer=$dwPortServer;
		//DwSessionIpKey
		parent::__construct( $dwResourceSelect ,$dwResourceIp );
	}
	/*
	public function response( string $data ) : void {
		//$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
		$this->rsrcResponse();
	}
	//*/
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
		parent::__destruct();
	}
	//*/
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

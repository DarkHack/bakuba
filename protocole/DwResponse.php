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

namespace bakuba\kernel\protocole;
//use bakuba\kernel\protocole\DwResponse as DwResponse;

use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;

use darkphp\DwObject as DwObject;
use darkphp\logger\DwLogger as DwLogger;
//use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwResponse {
	private $data = null;
	private $data64 = null;
	
	public function response64( string $data ) {
		$this->data64 = \base64_encode( $data );
	}
	public function response( string $data ) {
		$this->data = $data;
	}
	
	public function __construct() {
	}
	
	public function toString( ) {
		$response="";
		if ( ! \is_null($this->data) )
			$response.=$this->data;
		elseif ( ! \is_null($this->data64) )
			$response.=\base64_decode( $this->data64 );
		return $response;
	}
	public function __toString( ) : string {
		return $this->toString();
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		//parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		//return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
		return __CLASS__;
	}

}
?>

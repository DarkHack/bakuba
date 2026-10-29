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

namespace bakuba\kernel\autoload;
//use bakuba\kernel\autoload\DwAutoLoadCltInit as DwAutoLoadCltInit;

use \bakuba\kernel\crypto\json\DwJsonEndpoint as DwJsonEndpoint;

class DwAutoLoadCltInit extends DwAutoLoadBoot  {
	
	// singleton class pattern:
	private static $instance = null;
	public static function singleton( string $jsonEndpoint ) {
		try {
			echo "[".__CLASS__.":".__METHOD__."]  singleton \r\n$jsonEndpoint";
			null === self::$instance and self::$instance = new self( DwJsonEndpoint::fromJson( $jsonEndpoint ) );
			
		} catch(\Exception $exception) {
			echo "[".__CLASS__.":".__METHOD__."] Exception:\r\n$exception";
		} catch(\Error $error) {
			echo "[".__CLASS__.":".__METHOD__."] Error:\r\n$error";
		}
		return self::$instance;
	}
	
	private $dwControllerClient = null;
	private function __construct( DwJsonEndpoint $dwJsonEndpoint ) {
		try {
			if ( null !== self::$instance )
				throw new Exception( "instance ever run." );
				
			$this->dwControllerClient = new \bakuba\kernel\dwCrypto\DwJsonCryptControllerClient( $dwJsonEndpoint, "PhpAgent" );
			
			parent::__construct( );
		} catch(\Exception $exception) {
			echo "[".__CLASS__.":".__METHOD__."] Exception:\r\n$exception";
		} catch(\Error $error) {
			echo "[".__CLASS__.":".__METHOD__."] Error:\r\n$error";
		}
	}
	protected function readFile( string $classPath ) {
		return $this->dwControllerClient->request( $classPath );
	}
	
	public function scandir( string $folder ) : ?array {
		return $this->readFile( "scandir://$folder" );
	}
	
	public function isFile( string $path ) : bool {
		return $this->readFile( "isfile://$path" );
	}
	public function isDir( string $path ) : bool {
		return $this->readFile( "isdir://$path" );
	}
	
	public function getFile( $filePath ) : ?string {
		return $this->readFile( $filePath );
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

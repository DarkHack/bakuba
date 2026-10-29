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

namespace bakuba\kernel\autoload\standalone;
//use darkphp\autoload\standalone\DwAutoLoadFile as DwAutoLoadFile;

class DwAutoLoadFile extends \darkphp\autoload\DwAutoLoadBoot {
	private static $instance = null;
	// \darkphp\autoload\standalone\DwAutoLoadFile::standalone
	public static function standalone( string $bakubaFolder, string $logSection = "bakubalog", string $logUnit = null ) {
		self::$instance = new self( $bakubaFolder ,$logSection ,$logUnit );
	}
	
	private $bakubaFolder = null;
	private function __construct( string $bakubaFolder ,string $logSection = "bakubalog" ,string $logUnit = null ) {
		//echo "[ darkphp\autoload\standalone\DwAutoLoadFile]  {$bakubaFolder} ".\PHP_EOL;
		$this->bakubaFolder = $bakubaFolder;
		parent::__construct( $logSection ,$logUnit );
	}
	public function getFile( string $filePath ) : ?string {
		$content = null;
		if ( ! isset( $this->bakubaFolder ) ) {
			debug_print_backtrace();
			throw new \Exception("can't have bakuba folder not defined.");
		}
		//if ( substr( $filePath, 0, 7 ) == "file://" )
		//	$filePath = str_replace( "file://bakuba", self::$bakubaFolder, $filePath );
		$realfilePath = \str_replace( "file://", "{$this->bakubaFolder}/../", $filePath );
		//if ( defined("logByEcho") ) echo "[ standalone\DwAutoLoadFile::getFile] {$realfilePath}".\PHP_EOL;
		if ( \file_exists( $realfilePath ) )
			$content = \file_get_contents( $realfilePath );
		
		return $content;
	}
	public function isFile( string $path ) : bool {
		return \is_file( "{$this->bakubaFolder}/../$path" );
	}
	public function isDir( string $path ) : bool {
		return \is_dir( "{$this->bakubaFolder}/../$path" );
	}
	public function scandir( string $folder ) : ?array {
		$return = null;
		$elements=null;
		if ( $this->isDir( $folder ) )
			$elements = \scandir( "{$this->bakubaFolder}/..$folder" );
		if ( \is_array( $elements ) )
			$return = $elements;
		return $return;
	}
	///*
	public function getLogDir() : string {
		//throw new \Exception(" no get Log dir ");
		//return "{$this->bakubaFolder}/../log/bakuba";
		return \bakuba\kernel\agartha\DwInstall::getLocalDirectory( "./bakubaLog" );
	}
	//*/
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
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

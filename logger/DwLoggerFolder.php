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

namespace bakuba\kernel\logger;
//use darkphp\logger\DwLoggerFile as DwLoggerFile;

use bakuba\kernel\extInc\log4php\appenders\LoggerAppenderFile as LoggerAppenderFile;
//use bakuba\kernel\extInc\log4php\layouts\LoggerLayoutSimple as LoggerLayoutSimple;

class DwLoggerFolder
	extends \darkphp\DwObject 
	{
	// singleton class pattern:
	private static $instance = null;
	//DwLoggerFolder::singleton()
	public static function singleton( string $folderPath = "/" ) : self {
		null === self::$instance and self::$instance = new self( 
			$folderPath
		);
		return self::$instance;
	}
	private $folderLog = null;
	private function __construct( string $folderLog ) {
		parent::__construct();
		$this->folderLog = $folderLog;
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
		parent::__destruct();
	}
	//*/
	public function upToFolder( string $folderUp ) : DwLoggerFolder {
		return new DwLoggerFolder( "{$this->folderLog}/$folderUp" );
	}
	
	public function getDwLoggerFile( string $fileName ) : DwLoggerFile {
		return new DwLoggerFile(
			$this->folderLog
			,$fileName
		);
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

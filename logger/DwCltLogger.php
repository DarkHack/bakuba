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

namespace bakuba\kernel\dwLogger;
//use darkphp\logger\DwLogger as DwLogger;
//use bakuba\kernel\dwLogger\DwCltLogger as DwLogger;

use \Exception as Exception;
use \Error as Error;

interface IDwLogger {
	public function trace();
	public function debug();
	public function info();
	public function warn();
	public function error();
	public function fatal();
}

class DwNullLogger {
	public function trace() {
		return null;
	}
	public function debug() {
		return null;
	}
	public function info() {
		return null;
	}
	public function warn() {
		return null;
	}
	public function error() {
		return null;
	}
	public function fatal() {
		return null;
	}
}
class DwCltLogger {	
	// singleton class pattern:
	private static $instance = null;
	
	public static function getLogger( $class ) {
		try {
			if ( null === self::$instance ) {
				self::$instance = new DwNullLogger();
			}
			/*
			$className = $class;
			if ( is_object($class) ) 
				$className = $class->getClassName();
			*/
			//echo "[DwCltLogger:getLogger] get $className\r\n";
		} catch(Exception $exception) {
			echo "[DwCltLogger:getLogger] error $exception\r\n";
		}
		return self::$instance;
	}	
	
	public function __construct() {
	}
	
	public function logSwitchTo( $filePath, $rootDir = false ) {
	}
}

?>

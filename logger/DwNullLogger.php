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
//use bakuba\kernel\logger\DwNullLogger as DwNullLogger;

class DwNullLogger implements \bakuba\kernel\logger\iDwLogger {
	
	// singleton class pattern:
	private static $instance = null;
	private static function singleton() {
		if ( null === self::$instance )
			self::$instance = new self();
		return self::$instance;
	}
	
	//\bakuba\kernel\logger\DwNullLogger::getLogger
	public function getLogger( $forThisObject = null ) : \bakuba\kernel\logger\iDwLogger {
		return self::getFirstLogger( $forThisObject );
	}
	public static function getFirstLogger( $forThisObject = null ) : \bakuba\kernel\logger\iDwLogger {
		return self::singleton();
	}
	private function __construct( ) {
		//parent::__construct();
	}	
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
		//parent::__destruct();
	}
	//*/
	public function trace($message, $throwable = null) {
		//$this->log('TRACE', $message, $throwable);
	} 
	public function debug($message, $throwable = null) {
		//$this->log('DEBUG', $message, $throwable);
	}
	public function info($message, $throwable = null) {
		//$this->log('INFO', $message, $throwable);
	}
	public function warn($message, $throwable = null) {
		//$this->log('WARN', $message, $throwable);
	}
	public function error($message, $throwable = null) {
		//$this->log('ERROR', $message, $throwable);
	}
	public function fatal($message, $throwable = null) {
		//$this->log('FATAL', $message, $throwable);
	}
	public function throwable( $exception ) : void {
		//$this->log('THROW', \darkphp\error\DwException::exceptionToString($exception) );
	}

	/**
	 * Log a message using the provided logging level.
	 *
	 * @param log4php\DwLoggerLevel $level The logging level.
	 * @param mixed $message Message to log.
 	 * @param Exception $throwable Optional throwable information to include 
	 *   in the logging event.
	 */
	public function log(string $level, $message, $throwable = null) {
		// nothing append in null logger
	}
	
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return __CLASS__;
	}
}
?>

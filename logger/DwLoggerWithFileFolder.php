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
//use darkphp\logger\DwLogger as DwLogger;

class DwLogger
	extends \darkphp\DwObject {
	
	private $dwLoggerFile = null;
	private $logClassName = null;
	
	public function __construct(
		DwLoggerFile $dwLoggerFile
		,string $logClassName
	) {
		$this->logClassName = $logClassName;
		$this->dwLoggerFile = $dwLoggerFile;
		parent::__construct();
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
	/**
	 * Log a message using the provided logging level.
	 *
	 * @param log4php\DwLoggerLevel $level The logging level.
	 * @param mixed $message Message to log.
 	 * @param Exception $throwable Optional throwable information to include 
	 *   in the logging event.
	 */
	public function log(string $level, $message, $throwable = null) {
		$this->dwLoggerFile->log(
			new \bakuba\kernel\extInc\log4php\LoggerLoggingEvent(
				$this->logClassName."-fqcn"
				,$this->logClassName
				,$level
				,$message
				,null
				,$throwable
			)
		);
	}
	// ******************************************
	// *** Logging methods                    ***
	// ******************************************
	
	/**
	 * Log a message object with the TRACE level.
	 *
	 * @param mixed $message message
 	 * @param Exception $throwable Optional throwable information to include 
	 *   in the logging event.
	 */
	public function trace($message, $throwable = null) {
		$this->log('TRACE', $message, $throwable);
	} 		
	
	/**
	 * Log a message object with the DEBUG level.
	 *
	 * @param mixed $message message
 	 * @param Exception $throwable Optional throwable information to include 
	 *   in the logging event.
	 */
	public function debug($message, $throwable = null) {
		$this->log('DEBUG', $message, $throwable);
	}

	/**
	 * Log a message object with the INFO Level.
	 *
	 * @param mixed $message message
 	 * @param Exception $throwable Optional throwable information to include 
	 *   in the logging event.
	 */
	public function info($message, $throwable = null) {
		$this->log('INFO', $message, $throwable);
	}

	/**
	 * Log a message with the WARN level.
	 *
	 * @param mixed $message message
  	 * @param Exception $throwable Optional throwable information to include 
	 *   in the logging event.
	 */
	public function warn($message, $throwable = null) {
		$this->log('WARN', $message, $throwable);
	}
	
	/**
	 * Log a message object with the ERROR level.
	 *
	 * @param mixed $message message
	 * @param Exception $throwable Optional throwable information to include 
	 *   in the logging event.
	 */
	public function error($message, $throwable = null) {
		$this->log('ERROR', $message, $throwable);
	}
	
	/**
	 * Log a message object with the FATAL level.
	 *
	 * @param mixed $message message
	 * @param Exception $throwable Optional throwable information to include 
	 *   in the logging event.
	 */
	public function fatal($message, $throwable = null) {
		$this->log('FATAL', $message, $throwable);
	}
	
	public function throwable( $exception ) : void {
		$this->log(
			'THROW'
			,\darkphp\error\DwException::exceptionToString($exception)
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

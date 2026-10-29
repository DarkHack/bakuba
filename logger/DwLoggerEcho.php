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
//use bakuba\kernel\logger\DwLoggerEcho as DwLoggerEcho;

use bakuba\kernel\logger\log4dw\appenders\DwAppenderFile as DwAppenderFile;
//use bakuba\kernel\logger\log4dw\layouts\DwLayoutSimple as DwLayoutSimple;

class DwLoggerEcho extends \darkphp\DwObject  implements \bakuba\kernel\logger\iDwLogger {
	/*
	protected function initLogger( ) : \bakuba\kernel\logger\log4dw\appenders\aDwAppender {
		$loggerAppenderEcho = new \bakuba\kernel\logger\log4dw\appenders\DwAppenderEcho();
		$loggerAppenderEcho->setLayout( self::getLoggerLayoutPattern() );
		return $loggerAppenderEcho;
	}
	//*/
	private static $loggerAppender = null;
	protected function initLogger( ) : log4dw\appenders\aDwAppender {
		if ( \is_null( self::$loggerAppender ) ) {
			$loggerAppender = new log4dw\appenders\DwAppenderEcho();
			$loggerAppender->setLayout( self::getLayoutPattern() );
			self::$loggerAppender=$loggerAppender;
		}
		return self::$loggerAppender;
	}
	//private $loggerCategoryName = "noname";
	//private $loggerFqcn = "noname";
	
	private $folderLog = null;
	private $fileLog = null;
	private function __construct( $forThisObject ,string $folderLog ,string $fileLog ,\bakuba\kernel\logger\log4php\appenders\DwAppenderFile $dwAppenderFile = null ) {
		if ( \is_object( $dwAppenderFile ) ) 
			$this->logAppender=$dwAppenderFile;
		$this->folderLog=$folderLog;
		$this->fileLog=$fileLog;
		parent::__construct( $forThisObject );
		/*
		$this->loggerCategoryName = $forThisObject;
		if ( is_object ( $forThisObject ) )
			$this->loggerFqcn = $forThisObject->getClassName();
		else
			$this->loggerFqcn = $forThisObject;
		$this->initLogger();
		//*/
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
	/*
	public function upToLevel( $forThisObject, string $dirToUp ,string $newfileName = null ) : \bakuba\kernel\logger\iDwLogger {
		return new self( $forThisObject ,$this->folderLog."/".$dirToUp ,\is_null($newfileName) ? $this->fileLog : $newfileName );
	}
	public function upToUnit( $forThisObject, string $file ) : \bakuba\kernel\logger\iDwLogger {
		return new self( $forThisObject ,$this->folderLog ,$file );
	}
	/**
	 * Log a message using the provided logging level.
	 *
	 * @param \bakuba\kernel\logger\log4php\DwLoggerLevel $level The logging level.
	 * @param mixed $message Message to log.
 	 * @param Exception $throwable Optional throwable information to include 
	 *   in the logging event.
	 */
	 /*
	public function log(string $level, $message, $throwable = null) {
		$loggerLoggingEvent = new LoggerLoggingEvent(
			$this->loggerFqcn
			,$this->loggerCategoryName
			,$level
			,$message
			,null
			,$throwable
		);
		$this->logAppender->append( $loggerLoggingEvent );
	}
	//*/
	public function log( string $level, $message, $throwable = null) {
		$time=\microtime(true);
		if ( \is_object ( $this->forThisObject ) )
			if ( method_exists( $this->forThisObject ,'toString' ) )
				$loggerFqcn = $this->forThisObject->toString();
			elseif ( method_exists( $this->forThisObject ,'__toString' ) )
				$loggerFqcn = $this->forThisObject;
			elseif ( method_exists( $this->forThisObject ,'getClassName' ) )
				$loggerFqcn = $this->forThisObject->getClassName();
			else
				$loggerFqcn = \get_class( $this->forThisObject );
		else
			$loggerFqcn = $this->forThisObject;
		$loggerLoggingEvent = new log4dw\DwLoggingEvent(
			$this->loggerCategoryName
			,$loggerFqcn."\r\n\t[{$this->getFullLevel()}] "
			,$level
			,$message
			,$time
			,$throwable
		);
		//$yep = $loggerLoggingEvent->getLevel();
		$this->logAppender->append( $loggerLoggingEvent );
	}
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

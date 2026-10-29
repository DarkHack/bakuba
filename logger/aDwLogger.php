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
//use bakuba\kernel\logger\aDwLogger as aDwLogger;

use darkphp\error\DwException as DwException;
use darkphp\DwObject as DwObject;
use \Exception as Exception;
use \Error as Error;

//
abstract class aDwLogger extends \darkphp\serializable\aDwNoSerializable  implements \bakuba\kernel\logger\iDwLogger {
	static private $keys = array();
	static private $instanceCreated = array();
	
	private $instanceKey = null;
	
	private static $loggerLayoutPattern = null;
	private	static $patern = "%pid %-5level %relative [%logger] %class->%method()  %msg%newline";
//	private	static $patern = "%date %pid %-5level %relative %logger->%method() %msg%newline";
	//self::getLoggerLayoutPattern()
	protected static function getDefaultLayoutPattern() : log4dw\layouts\DwLayoutPattern {
		if ( \is_null( self::$loggerLayoutPattern ) ) {
			$loggerLayoutPattern = new log4dw\layouts\DwLayoutPattern();
			$loggerLayoutPattern->setConversionPattern( self::$patern );
			$loggerLayoutPattern->activateOptions();
			self::$loggerLayoutPattern = $loggerLayoutPattern;
		}
		return self::$loggerLayoutPattern;
	}
	protected function getLayoutPattern() : log4dw\layouts\DwLayoutPattern {
		return self::getDefaultLayoutPattern();
	}
	public function addAppender( log4dw\appenders\aDwAppender $aDwAppender ) : void {
		//$aDwAppender->setLayout( self::getLayoutPattern() );
		$this->logAppender->addAppender( $aDwAppender );
	}
	/*
	private static $sLogAppender = null;
	//private static $loggers =  array();
	private static function sInitLogger() : LoggerAppenderFile {
		if ( is_null( self::$sLogAppender ) ) {
			//echo "[".__CLASS__."::".__METHOD__."] data/config.xml\r\n";
			//Logger::configure("file://bakuba/data/config.xml");
			$loggerLayoutPattern = new LoggerLayoutPattern();
			$loggerLayoutPattern->setConversionPattern( "%pid %-5level %relative [%logger] %class->%method()  %msg%newline" );
			$loggerLayoutPattern->activateOptions();
			
			$loggerAppenderEcho = new LoggerAppenderEcho();
			$loggerAppenderEcho->setLayout( $loggerLayoutPattern );
			self::$sLogAppender = $loggerAppenderEcho;
			$loggerAppenderFile = new LoggerAppenderFile();
			$loggerAppenderFile->setFile( "c:/var/bakuba.log" );
			$loggerAppenderFile->setLayout( $loggerLayoutPattern );
			self::$sLogAppender = $loggerAppenderFile;
			
			$loggerAppenderFilePid = new LoggerAppenderFilePid("file");
			$loggerAppenderFilePid->setFile("default.log");
			$loggerAppenderFilePid->setLayout( $loggerLayoutPattern );
			self::$sLogAppender = $loggerAppenderFilePid;
			
			$loggerLayoutPattern = new LoggerLayoutPattern();
			$loggerLayoutPattern->setConversionPattern( "%relative %date %pid %-5level %relative [%logger] %class->%method() %msg%newline" );
			$loggerLayoutPattern->activateOptions();
			
			$loggerAppenderKernel = new LoggerAppenderKernel("kernel");
			$loggerLayoutPattern = new LoggerLayoutPattern();
			$loggerLayoutPattern->setConversionPattern( "%relative %pid %-5level [%logger] %class->%method() %msg%n" );
			$loggerLayoutPattern->activateOptions();
			$loggerAppenderKernel->setLayout( $loggerLayoutPattern );
			self::$sLogAppender = $loggerAppenderKernel;
		}
		return self::$sLogAppender;
	}
	//*/
	
	//\bakuba\kernel\logger\aDwLogger::getLogger
	public function getLogger( $forThisObject ) : iDwLogger {
		$className = $this->getClassName();
		return new $className( $forThisObject/*, $this*/ );
	}
	protected $loggerCategoryName = "noname";
	protected $forThisObject = null;
	protected $logAppender = null;
	protected function __construct( $forThisObject /*,$parent = null*/ ) {
		$this->dwObjectRefInstance( $forThisObject );
		$this->forThisObject = $forThisObject;
		$this->loggerCategoryName = \gettype( $this->forThisObject );
		
		//$this->log = $this;
		if ( \is_null( $this->logAppender ) )
			$this->logAppender = $this->initLogger( );
		parent::__construct( /*$parent*/ );
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		if ( ( $key = \array_search( $this->instanceKey, self::$keys ) ) !== false )
			unset(self::$keys[$key]);
		
		if ( isset( self::$instanceCreated[ $this->getClassName() ] ) ) {
			self::$instanceCreated[ $this->getClassName() ]--;
			if ( self::$instanceCreated[ $this->getClassName() ] == 0 )
				unset(self::$instanceCreated[ $this->getClassName() ]);
			
		} elseif ( isset( $this->log ) && \is_object($this->log) )
			$this->log->error( \PHP_EOL."\t [{$this->toString()}] no refrenced instance for :{$this->getClassName()} !!!" );
			
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
		
		parent::__destruct();
	}
	//*/
	public static function objectToFqcn( $forThisObject ) : string {
		if ( \is_object ( $forThisObject ) )
			if ( method_exists( $forThisObject ,'toString' ) )
				$loggerFqcn = $forThisObject->toString();
			elseif ( method_exists( $forThisObject ,'__toString' ) )
				$loggerFqcn = $forThisObject;
			elseif ( method_exists( $forThisObject ,'getClassName' ) )
				$loggerFqcn = $forThisObject->getClassName();
			else
				$loggerFqcn = \get_class( $forThisObject );
		else
			$loggerFqcn = \gettype( $forThisObject );
		return $loggerFqcn;
	}
	public function dwObjectRefInstance( $forThisObject ) {
		if ( \is_null( $this->instanceKey ) ) {
			$this->instanceKey = self::getNewInstanceKey( $forThisObject );
			if ( ! isset(self::$instanceCreated[$this->getClassName()]) )
				self::$instanceCreated[$this->getClassName()]=0;
			self::$instanceCreated[$this->getClassName()]++;
			$cpt=self::$instanceCreated[$this->getClassName()];
			//echo "dwObjectRefInstance [{$this}]:{$this->getClassName()}:{$this->instanceKey}/{$cpt}\r\n";
			//echo "dwObjectRefInstance:{$cpt}/{$this->getClassName()}:{$this->instanceKey}\r\n";
		} else
			throw new \Error ( "instanceKey ever here:{$this->instanceKey} !!!!!!!!!!!!!\r\n [ {$this} ]" );
	}
	
	private static function getNewInstanceKey( $forThisObject ) : string {
		$instanceKey = \bakuba\kernel\randomizer\DwRandomizer::progressiveInArrayUUID( self::$keys );
		self::$keys[]=$instanceKey;
		return "aDwLogger[{$instanceKey}].{".self::objectToFqcn( $forThisObject )."]}/";
	}
	
	// ******************************************
	// *** Logging methods                    ***
	// ******************************************
	public function trace($message, $throwable = null) {
		$this->log('TRACE', $message, $throwable);
	} 
	public function debug($message, $throwable = null) {
		$this->log('DEBUG', $message, $throwable);
	}
	public function info($message, $throwable = null) {
		$this->log('INFO', $message, $throwable);
	}
	public function warn($message, $throwable = null) {
		$this->log('WARN', $message, $throwable);
	}
	public function error($message, $throwable = null) {
		$this->log('ERROR', $message, $throwable);
	}
	public function fatal($message, $throwable = null) {
		$this->log('FATAL', $message, $throwable);
	}
	public function throwable( $exception ) : void {
		$this->fatal( \PHP_EOL.DwException::exceptionToString( $exception ) );
	}

	/**
	 * Log a message using the provided logging level.
	 *
	 * @param DwLoggerLevel $level The logging level.
	 * @param mixed $message Message to log.
 	 * @param Exception $throwable Optional throwable information to include 
	 *   in the logging event.
	 */
	public function log( string $level, $message, $throwable = null) {
		$time=\microtime(true);
		if ( isset( $this->forThisObject ) ) {
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
		} else
			$loggerFqcn = "fqcn-not-defined";
		
		if ( isset( $this->loggerCategoryName ) )
			$loggerCategoryName = $this->loggerCategoryName;
		else
			$loggerCategoryName = "category-not-defined";
		
		$dwLoggingEvent = new log4dw\DwLoggingEvent(
			$loggerCategoryName
			,$loggerFqcn
			,$level
			,$message
			,$time
			,$throwable
		);
		if ( isset( $this->logAppender ) && \is_object( $this->logAppender ) )
			$this->logAppender->append( $dwLoggingEvent );
		//else
		//	echo $this->getInstanceKey().":no more logger for ".$loggerFqcn." msg:".$message."\r\n";
	}
	public function getInstanceKey() : string {
		return $this->instanceKey;
	}
	public function toString( $withParent = false ) : string {
		if ( ! isset( $this->instanceKey ) || \is_null( $this->instanceKey ) )
			$objStr="!NoInstanceKey/";
		else
			$objStr="{$this->getInstanceKey()}/";
		$objStr.= parent::toString();
		return $objStr;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	// \bakuba\kernel\logger\aDwLogger::listInstancesCreated()
	public static function listInstancesCreated() : array {
		return self::$instanceCreated;
	}
	// \bakuba\kernel\logger\aDwLogger::getLastKeys( );
	public static function getLastKeys( int $nbKeys ) : array {
		$return = array();
		foreach( array_reverse( self::$keys ) as $key ) {
			$return[]=$key;
			$nbKeys--;
			if ( $nbKeys<=0)
				break;
		}
		return $return;
	}
}
?>

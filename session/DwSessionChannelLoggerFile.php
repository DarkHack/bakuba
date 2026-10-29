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

namespace bakuba\kernel\session;
//use bakuba\kernel\session\DwSessionChannelLoggerFile as DwSessionChannelLoggerFile;

use \Exception as Exception;
use \Error as Error;

use darkphp\DwObject as DwObject;
use  as LoggerLoggingEvent;

use bakuba\kernel\extInc\log4php\Logger as Logger;
use bakuba\kernel\extInc\log4php\appenders\LoggerAppenderFile as LoggerAppenderFile;
//use bakuba\kernel\extInc\log4php\layouts\LoggerLayoutSimple as LoggerLayoutSimple;
use  as LoggerLayoutPattern;

use  as LoggerAppenderFile;
use bakuba\kernel\extInc\log4php\appenders\LoggerAppenderKernel as LoggerAppenderKernel;

abstract class DwSessionChannelLoggerFile
	extends \darkphp\DwObject 
	{
	
	private $logAppender = array();
	public function __construct(
		string $folderLog
		,string $fileLog = "default.log"
	) {
		parent::__construct();
		$loggerLayoutPattern 
			= new \bakuba\kernel\extInc\log4php\layouts\LoggerLayoutPattern();
		$loggerLayoutPattern->setConversionPattern(
			"%date %pid %-5level %relative %logger->%method() %msg%newline"
		);
		$loggerLayoutPattern->activateOptions();
		
		$loggerAppenderFile 
			= new bakuba\kernel\extInc\log4php\appenders\LoggerAppenderFile("file");
		$loggerAppenderFile->setLayout( $loggerLayoutPattern );
		$loggerAppenderFile->setFolder( $folderLog );
		$loggerAppenderFile->setFile( $fileLog );
		
		$this->logAppender[] = $loggerAppenderFile;
	}
	
	public function getLogger( string $className ) : DwSessionChannelLogger {
		return new DwSessionChannelLogger( $this, $className );
	}
	public function log(
		\bakuba\kernel\extInc\log4php\LoggerLoggingEvent $loggerLoggingEvent
	) {
		foreach($this->logAppender as $appender)
			$appender->append( $loggerLoggingEvent );
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

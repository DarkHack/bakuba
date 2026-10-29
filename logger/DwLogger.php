<?php
/*
# =====================================================================
#  Projet  : darkphp
#  Fichier : logger/DwLogger.php
#  Auteur  : Nicolas "DarkHack" Chapuis (c) 2026
# ================== DARKHACK ==================
#        [ darkweb.fr  -  system online ]
#   Powered by Perplexity, your AI assistant
#       https://www.perplexity.ai
# ==============================================
#
#  Licensed under the Apache License, Version 2.0 (the "License");
#  you may not use this file except in compliance with the License.
#  You may obtain a copy of the License at
#
#      http://www.apache.org/licenses/LICENSE-2.0
#
#  Unless required by applicable law or agreed to in writing, software
#  distributed under the License is distributed on an "AS IS" BASIS,
#  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#  See the License for the specific language governing permissions and
#  limitations under the License.
# =====================================================================
//*/
namespace darkphp\logger;
//use darkphp\logger\DwLogger as DwLogger;

/*
use \Exception as Exception;
use \Error as Error;

use darkphp\DwObject as DwObject;
//*/

class DwLogger extends aDwLogger {
	/*
	private static $sLog = null;
	// singleton class pattern:
	private static $instance = null;
	private static function singleton() {
		try {
			if ( null === self::$instance ) {
				//echo "[DwLogger:singleton] new self\r\n";
				self::$instance = new self();
			}
		
		} catch(Exception $exception) {
			if ( is_object( self::$sLog ) ) self::$sLog->error("exception [".self::getStaticClassName()."]");
			throw $exception;
		} catch(Error $error) {
			if ( is_object( self::$sLog ) ) self::$sLog->error("error [".self::getStaticClassName()."]");
			throw $error;
		}
		return self::$instance;
	}
	/*
	public static function getLogger( $class ) {
		$logger = null;
		try {
			if ( null === self::$instance ) {
				//echo "[DwLogger:getLogger] init\r\n";
				self::singleton();
			}
			$className = $class;
			if ( is_object($class) ) {
				echo "[DwLogger:getLogger] isobject try get classname\r\n";
				$className = $class->getClassName();
			}
			//echo "[DwLogger:getLogger] get($className)\r\n";
			$logger = aDwLogger::getLogger( str_replace( '\\' ,'.' ,$className ) );
		} catch(Exception $exception) {
			if ( is_object( self::$sLog ) ) self::$sLog->error("exception [".self::getStaticClassName()."]");
			else echo "exception [".self::getStaticClassName()."]";
			throw $exception;
		} catch(Error $error) {
			if ( is_object( self::$sLog ) ) self::$sLog->error("error [".self::getStaticClassName()."]");
			else echo "exception [".self::getStaticClassName()."]";
			throw $error;
		}
		return $logger;
	}
	*/
	/*
	//private $logFileFolder;
	//private $loggerAppenderFileAll;
	private function __construct() {
		try {
			if ( is_null( self::$sLog ) ) self::$sLog =  Logger::getLogger( str_replace('\\', '.',  __CLASS__) );
			self::$sLog->trace(".");
			parent::__construct();
			
			
			//$this->logFileFolder = "/var/log/bakuba/".getmypid();
			//$this->initLogger();
			//$this->logInit();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	//*/
/*
	public function __destruct( ) {
		try {	
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			//unset( $this->dwSignalsHandler );
			
			parent::__destruct();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	*/
	/*
	public static function getFolderInit( ) {
		return LoggerAppenderFilePid::getFolderInit();
	}
	public static function getDirLog( ) {
		return LoggerAppenderFilePid::getDirLog();
	}
	public static function upLogDir( $dirToUp ) {
		LoggerAppenderFilePid::folderAdd("/$dirToUp");
	}
	public static function rootLogDir( $dirToRoot ) {
		LoggerAppenderFilePid::folderRoot("/$dirToRoot");
	}
	public function logSwitchTo( $filePath, $rootDir = false ) {
		$this->_logSwitchTo($filePath, $rootDir);
	}
	
	private function _logSwitchTo( $filePath, $rootDir = false ) {
		try {
			self::$sLog->trace(".");
			if ( ! $rootDir ) {
				$fullFilePath = $this->logFileFolder."/".$filePath;
			} else {
				$fullFilePath = "$rootDir/log/".getmypid()."/".$filePath;
			}
			if ( $this->loggerAppenderFileAll == null )
				throw new Exception("loggerAppenderFileAll is null");
			else {
				echo "[DwLogger:_logSwitchTo] Go to log file : ".$fullFilePath;
				self::$sLog->debug( "Go to log file : ".$fullFilePath );
				$this->loggerAppenderFileAll->close();
				$this->loggerAppenderFileAll->setFile($fullFilePath);
				$this->loggerAppenderFileAll->activateOptions();
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	private function initLogger() {
		try {
			self::$sLog->trace(".");
			echo "[DwLogger:initLogger] ".$this->logFileFolder."/default.log";
			self::$sLog->debug("disable fileAll:".$this->logFileFolder."/default.log");
			if ( ! is_dir($this->logFileFolder) )
				if (!mkdir($this->logFileFolder, 0777, true))
    					die('[DwLogger:initLogger] Echec lors de la création des répertoires...');
			chmod($this->logFileFolder, 0777); 
			/*
			// Create an appender which logs to file
			$this->loggerAppenderFileAll = new LoggerAppenderFile('dwLogger');
			$this->loggerAppenderFileAll->setFile($this->logFileFolder."/default.log");
			$this->loggerAppenderFileAll->setAppend(true);
			$this->loggerAppenderFileAll->setThreshold('all');
			$this->loggerAppenderFileAll->activateOptions();
			
			$rootLogger = Logger::getRootLogger();
			$rootLogger->addAppender($this->loggerAppenderFileAll);
			
			$loggerAppenderDefault = $rootLogger->getAppender("default");
			if ( $loggerAppenderDefault == null )
				throw new Exception("loggerAppenderFileAll is null");
			else {
				$this->loggerAppenderFileAll->setLayout($loggerAppenderDefault->getLayout());
				//$loggerAppenderFileAll->close();
				echo "[DwLogger:initLogger] disable fileAll:".$this->logFileFolder."/default.log";
				self::$sLog->debug("disable fileAll:".$this->logFileFolder."/default.log");
			}
			
			self::$sLog->debug("disable fileAll:".$this->logFileFolder."/default.log");
			//*/
			/*
			//self::$sLog->setParent($rootLogger);
			foreach(Logger::getCurrentLoggers() as $logger)  {
				if ($logger != $rootLogger && $logger != self::$sLog )
					$logger->setParent(self::$sLog);
			}
			
			self::$sLog = Logger::getLogger('ServerManager');
			//*/
			/*
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

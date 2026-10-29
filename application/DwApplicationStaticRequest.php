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

namespace bakuba\kernel\application;
//use bakuba\kernel\application\DwApplicationStaticRequest as DwApplicationStaticRequest;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;

class DwApplicationStaticRequest {
	private static $sLog = null;
	
	private $staticFilesRequest = null;
	public function getStaticFilesRequest( ) : array { 
		return $this->staticFilesRequest;
	}
	
	public function __construct( array $staticFilesRequest ) {
		try {
			//parent::__construct();
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			self::$sLog->trace("[".$this->getClassName()."]");
			
			$this->staticFilesRequest = $staticFilesRequest;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	/*
	private $myobject = null;
	public function getObject( ) {
		return $this->myobject;
	}
	public function setObject( $myobject ) {
		$this->myobject = $myobject;
	}
	*/
	public function getClassName( bool $fqcn=null ) : string {
		$className = __CLASS__;
		try {
			if ( is_null( self::$sLog ) ) self::$sLog = DwLogger::getLogger( $className );
			//self::$sLog->trace(".");
			$className = $fqcn ? parent::getClassName( $fqcn ).self::CLASS_SEPARATOR.$className : $className;
		} catch(Exception $exception) {
			self::$sLog->error("exception [$className]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [$className]");
			throw $error;
		}
		return $className;
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		$className = __CLASS__;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( $className );
			//self::$sLog->trace(".");
			$className= $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.$className : $className;
		
		} catch(Exception $exception) {
			self::$sLog->error("exception [$className]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [$className]");
			throw $error;
		}
		return $className;
	}
}



?>


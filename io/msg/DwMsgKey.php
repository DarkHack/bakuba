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

namespace bakuba\kernel\io\msg;
//use bakuba\kernel\io\msg\DwMsgKey as DwMsgKey;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\serializable\aDwSerializable as DwSerializable;

class DwMsgKey extends aDwSerializable {
	private static $sLog = null;
	// [ classNameProcess ] : string
	protected $processClassName;
	public function getClassNameProcess() : string {
		return $this->processClassName;
	}
	// [ classNameProcess ] : string
	protected $processID = null;
	public function getProcessId() : int {
		if ( is_null( $this->processID ) )
			$processId = -1;
		else 
			$processId = $this->processID;
		return $processId;
	}
	public function setProcessId( int $myPid ) {
		$this->processID=$myPid;
	}
	
	// [ queue_key ] : int
	protected $queue_key;
	public function getKey() : int {
		return $this->queue_key;
	}
	private function setKey( int $queue_key) {
		$this->queue_key = $queue_key;
		//self::$sLog->trace("[{$this->getClassNameProcess()}({$this->getProcessId()})]\r\n$queue_key");
	}
	
	private function generateNewKey( int $crc32Name) : int {
		$key = $crc32Name;
		try {
			do {
				$key = rand( -1000, 1000 ) + $crc32Name;
			} while ( msg_queue_exists( $key ) );
			
			//self::$sLog->trace("[{$this->getClassNameProcess()}({$this->getProcessId()})] $key");
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $key;
	}
	
	public function __construct( string $processClassName = "classUnserialize", int $processID = null ) {
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::__construct();
			
			$this->processID = $processID;
			$this->processClassName = $processClassName;
			$this->setKey( $this->generateNewKey( crc32( $this->processClassName."(".$this->processID.")_".\bakuba\kernel\randomizer\DwRandomizer::randomize( 10 ) ) ) );
			
			//self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function DwSerializableInit() {
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::DwSerializableInit();
			
			$this->addtoSerialize("processClassName");
			$this->addtoSerialize("processID");
			$this->addtoSerialize("queue_key");
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

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

namespace bakuba\kernel\io\messageQueue;

use \Exception as Exception;
use \Error as Error;

use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\logger\DwLogger as DwLogger;

use darkphp\serializable\aDwSerializable as DwSerializable;
class DwMessageQueuePacket extends aDwSerializable {
	private static $sLog = null;
	
	// [ id ] : string / not null
	protected	$id = null;
	
	// [ rid ] : string / null
	protected $rid = null;
	public function getRid() {
		return $this->rid;
	}
	// [ srcKey ] : DwMessageQueueKey / not null
	protected $srcKey = null;
	public function getSrcKey() : DwMessageQueueKey {
		return $this->srcKey;
	}
	// [ message ] : string
	protected $message = null;
	public function getMessage() : string {
		return $this->message;
	}
	// [ message_id ] : string
	protected $message_id = null;
	public function getMessageId() : string {
		return $this->message_id;
	}
	// [ index ] : int
	protected $index = null;
	public function getIndex() : int {
		return $this->index;
	}
	// [ last ] : bool
	protected $last = null;
	public function isLast() : bool {
		return $this->last;
	}
	
	// DwMessageQueuePacket::message( $dwInternalMessage, $index, $message )
	public static function message(
			string $message_id,
			$message_rid,
			DwMessageQueueKey $dwMessageQueueKeySrc, 
			string $message, 
			bool $last,
			int $index = -1
	) : DwMessageQueuePacket {
		$dwMessageQueuePacket = null;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			
			if ( is_null($message) || ! is_string($message) )
				throw new Exception("message param is null or not string.");
			if ( is_null($last) || ! is_bool($last) )
				throw new Exception("last param is null or not bool.");
			
			$dwMessageQueuePacket = new self();
			
			$dwMessageQueuePacket->message_id = $message_id;
			$dwMessageQueuePacket->rid = $message_rid;
			$dwMessageQueuePacket->last = $last;
			$dwMessageQueuePacket->index = $index;
			//if ( ! $isSelfTest )
			$dwMessageQueuePacket->srcKey = $dwMessageQueueKeySrc;
			//$dwMessageQueuePacket->dstKey = $dwInternalMessage->getDstKey();
			$dwMessageQueuePacket->message = $message;
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".self::getStaticClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".self::getStaticClassName()."]");
			throw $error;
		}
		return $dwMessageQueuePacket;
	}/*
	public static function fromJson( $jsonData ) : ?DwMessageQueuePacket {
		$dwMessageQueuePacket = null;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			
			$dwMessageQueuePacket = new self();
			$dwMessageQueuePacket->__unserialize( json_decode( $jsonData, true ) );
			
			
		} catch(Exception $exception) {
			$dbgMsg="exception [".self::getStaticClassName()."]\r\n";
			$dbgMsg.=DwObject::print_r( $exception )."\r\n";
			$dbgMsg.=$jsonData."\r\n";
			self::$sLog->error($dbgMsg);
			//throw $exception;
			$dwMessageQueuePacket = null;
		} catch(Error $error) {
			$dbgMsg="error [".self::getStaticClassName()."]\r\n";
			$dbgMsg.=DwObject::print_r( $error )."\r\n";
			$dbgMsg.=$jsonData."\r\n";
			self::$sLog->error($dbgMsg);
			//throw $error;
			$dwMessageQueuePacket = null;
		}
		return $dwMessageQueuePacket;
	}
	*/
    public function __unserializeValue( $key ,$data ) {
		$unserialized = null;
		if ( $key == "srcKey"  ) { //is_array( $data ) && 
			//self::$sLog->info( $this->getClassName()."->".__METHOD__." => key:$key count:".count($data) );//."\r\n".$data."\r\n" );
			//$instance = DwObject::createInstanceWithoutConstructor( DwMessageQueueKey::getStaticClassName() );
			$className = DwMessageQueueKey::getStaticClassName();
			$instance = new $className( $this );
			
			if ( is_object($instance) &&  method_exists( $instance ,"__unserialize" ) ) {
				$instance->__unserialize( $data );
			}
			$unserialized = $instance;
		} else {
			$unserialized = $data;
		}
		//if (self::$echoDebug )
		//	echo $this->getClassName()."->".__METHOD__." => ".strlen($unserialized)."\r\n";
		return $unserialized;
	}
	private function __construct() {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::__construct();
			
			$this->id = \bakuba\kernel\randomizer\DwRandomizer::randomize( 10 );
			
			//self::$sLog->trace("[".$this->getClassName()."]");
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
			
			$this->addtoSerialize("id");
			$this->addtoSerialize("rid");
			$this->addtoSerialize("message");
			$this->addtoSerialize("message_id");
			$this->addtoSerialize("index");
			$this->addtoSerialize("last");
			$this->addtoSerialize("srcKey");
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	/*
	public function serialize() {
		$dataStr = null;
		try {
			//self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			$dataStr = json_encode( $this->__serialize() );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $dataStr;
    }
	
    public function unserialize( $dataStr ) : void {
		try {
			//self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			$this->__unserialize( json_decode( $dataStr, true ) );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
    }
	*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

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
//use bakuba\kernel\io\messageQueue\DwMessageQueueKey as DwMessageQueueKey;

class DwMessageQueueKey extends \darkphp\serializable\aDwSerializable {
	// [ classNameProcess ] : string
	protected $processClassName;
	public function getClassNameProcess() : string {
		return $this->processClassName;
	}
	// [ getProcessId ] : int
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
	
	// [ key ] : int
	protected $key;
	public function getKey() : int {
		return $this->key;
	}
	private function setKey( int $key) {
		$this->key = $key;
	}
	
	private function generateNewKey() : int {
		return rand( -1000, 1000 ) + \crc32( $this->processClassName."(".$this->processID.")_".\bakuba\kernel\randomizer\DwRandomizer::randomize( 10 ) );
	}
	
	public function __construct( string $processClassName = "classUnserialize", int $processID = null ) {
		parent::__construct();
		
		$this->processID = \is_null($processID)?getmypid():$processID;
		$this->processClassName = $processClassName;
		$this->setKey( $this->generateNewKey() );
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		$this->addtoSerialize("processClassName");
		$this->addtoSerialize("processID");
		$this->addtoSerialize("key");
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

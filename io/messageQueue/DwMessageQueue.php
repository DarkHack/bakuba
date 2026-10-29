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
//use bakuba\kernel\io\messageQueue\DwMessageQueue as DwMessageQueue;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\process\DwProcess as DwProcess;

class DwMessageQueue extends \darkphp\serializable\aDwNoSerializable implements \bakuba\kernel\io\iDwResource {
	private $dstKey = null;
	public function getDstKey() : DwMessageQueueKey {
		return $this->dstKey;
	}
	private $srcKey = null;
	public function getSrcKey() : DwMessageQueueKey {
		return $this->srcKey;
	}
	private	$id = null;
	public function getId() : string {
		return $this->id;
	}
	///*
	private	$rid = null;
	public function getRid() {
		return $this->rid;
	}
	
	public function __construct(
		DwMessageQueueKey $dwMessageQueueKey
		,string $mid = null
	) {
		parent::__construct( );
		$debugKey="{$this->getClassName()}";
		$this->dstKey = $dwMessageQueueKey;
		
		
		$this->id = $mid == null ? \bakuba\kernel\randomizer\DwRandomizer::randomize( 10 ) : $mid;
		
		if ( $this->logEnable )
			$this->log->trace("[{$debugKey}]");
	}
	private $isForward = false;
	public function forwardTo( DwMessageQueueKey $dwMessageQueueKey ) {
		$this->disconnect();
		$this->dstKey = $dwMessageQueueKey;
		$this->isForward=true;
		//$this->connect();
		parent::forward();
	}
	public function source( DwMessageQueueKey $dwMessageQueueKey ) {
		if ( ! $this->isForward )
			$this->srcKey = $dwMessageQueueKey;
	}
	public function connect( $ressource = null ) : bool {
		return true;
	}
	public function disconnect() : bool {
		return true;
	}
	public function forward(  ) {
		
	}
	public function isResource() : bool {
		return true;
	}
	public function getResource() {
		return null;
	}
	public function clearRecvBuf() {
		
	}
	private $data = null;
	public function write( string $data ) {
		$this->data = $data;
	}
	public function read( ) : string {
		return $this->data;
	}
	public static function response(
		DwMessageQueue $dwMessageQueue
	) : DwMessageQueue {
		$dwMessageQueueResponse = new DwMessageQueue(
			$dwMessageQueue->getSrcKey()
		);
		$dwMessageQueueResponse->rid = $dwMessageQueue->getId();
		return $dwMessageQueueResponse;
	}
	
	protected const CLASS_SEPARATOR = \darkphp\autoload\DwAutoLoadBoot::CLASS_SEPARATOR;
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? "//".self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

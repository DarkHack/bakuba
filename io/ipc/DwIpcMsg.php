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

namespace bakuba\kernel\io\ipc;
//use bakuba\kernel\io\ipc\DwIpcMsg as DwIpcMsg;

class DwIpcMsg extends \darkphp\serializable\aDwNoSerializable implements iDwMsg {
	
	private $dstKey = null;
	public function getDstKey() : DwIpcKey {
		return $this->dstKey;
	}
	//private $srcKey = null;
	public function getSrcKey() : DwIpcKey {
		return $this->parent;
	}
	private	$id = null;
	public function getId() : string {
		return $this->id;
	}
	///*
	private	$rid = null;
	public function getRid() : ?string {
		return $this->rid;
	}
	
	public function __construct(
		DwIpcKey $dwIpcKey
		,string $mid = null
	) {
		parent::__construct( $dwIpcKey );
		//$this->srcKey = $dwIpcKey;
		$this->id = $mid == null ? $dwIpcKey->getInstanceKeyProcess()."[".time()."/".\bakuba\kernel\randomizer\DwRandomizer::randomize( 3 )."]": "mid:".$mid;
		//$this->log->trace( "." );
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
	private $isForward = false;
	public function forwardTo( DwIpcKey $dwIpcKey ) {
		$this->dstKey = $dwIpcKey;
		$this->isForward=true;
		//$this->connect();
		//parent::forward();
	}
	public function destination( DwIpcKey $dwIpcKey ) {
		$this->dstKey = $dwIpcKey;
	}
	/*
	public function source( DwIpcKey $dwIpcKey ) {
		if ( ! $this->isForward )
			$this->srcKey = $dwIpcKey;
	}
	//*/
	//\bakuba\kernel\io\ipc\DwIpcMsg::response( $dwIpcMsg );
	public static function response( iDwMsg $iDwMsg ) : iDwMsg {
		$iDwMsgResponse = new DwIpcMsg(
			$iDwMsg->getDstKey()
		);
		$iDwMsgResponse->destination( $iDwMsg->getSrcKey() );
		$iDwMsgResponse->rid = $iDwMsg->getId();
		return $iDwMsgResponse;
	}
	private $data = null;
	public function write( $response ) {
		$this->data = $response;
	}
	public function read() {
		return $this->data;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return __CLASS__;
//		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

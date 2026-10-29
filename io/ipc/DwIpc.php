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
//use bakuba\kernel\io\ipc\DwIpc as DwIpc;

class DwIpc extends \bakuba\kernel\io\DwResource {
	///*
	private $dstIpcKey = null;
	public function __construct( \darkphp\DwObject $dwParent ,DwIpcKey $ipcKeyDst ) {
		$this->dstIpcKey = $ipcKeyDst;
		parent::__construct( $dwParent ,true  );
	}
	/*
	public function getDwIpcServer() : DwIpcServer {
		return $this->parent;
	}
	//*/
	public function getDstIpcKey() : DwIpcKey {
		return $this->dstIpcKey;
	}
	//*/
	public function isResource() : bool {
		return $this->isResourceDefined();
	}
	private $flagActivity = false;
	public function checkActivity() :bool {
		$activity = $this->flagActivity;$this->flagActivity=false;
		return $activity;
	}
	
	public function receive( string $data = null ) : int {
		//$this->recvInactivity++;
		return $this->readed ? 1 : 0;
	}
	private $readed=false;
	public function readReady() : bool {
		return $this->readed;
	}
	public function readEnd() : bool {
		return $this->readed;
	}
	private $writed=false;
	public function send() :void {
	}
	public function writeEnd() : bool {
		return $this->writed;
	}
	private $anIpcMsg = null;
	public function read( ) : ?DwIpcMsg {
		//$this->log->trace(".");
		$this->flagActivity = true;
		$this->writed=true;
		$this->readed=false;
		return $this->anIpcMsg;
	}
	public function write( DwIpcMsg $dwIpcMsg ) :void {
		//$this->log->trace(".");
		$this->flagActivity = true;
		$this->writed=false;
		$this->readed=true;
		$this->anIpcMsg = $dwIpcMsg;
	}
	public function getRcvSize() : int {
		return $this->writed ? 1 : 0;
	}
	public function __toString() : string {
		return "[{$this->dstIpcKey}] ".( $this->isListen() ? "listen" : "" );
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ 
			: __CLASS__
		;
	}
}
?>

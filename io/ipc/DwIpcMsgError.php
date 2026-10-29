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
//use bakuba\kernel\io\ipc\DwIpcMsgError as DwIpcMsgError;

class DwIpcMsgError {
	// \bakuba\kernel\io\ipc\DwIpcMsgError::handle
	/*
	public static function handle( iDwMsg $iDwMsg, $msgError ) : iDwMsg {
		$iDwMsgResponse = DwIpcMsg::response( $iDwMsg );
		$iDwMsgResponse->write( new DwIpcMsgError( $iDwMsg ,$msgError ) );
		return $iDwMsgResponse;
	}
	//*/
	private $errorMsg="NOT DEFINED";
	public function __construct( iDwMsg $iDwMsg ,$msgError ) {
		$dstKey = $iDwMsg->getDstKey();
		$srcKey = $iDwMsg->getSrcKey();
		$mid = $iDwMsg->getId();
		$rid = $iDwMsg->getRid();
		$dstClassName = $dstKey->getClassNameProcess();
		$srcClassName = $srcKey->getClassNameProcess();
		$this->errorMsg = "[ {$srcClassName} => {$dstClassName} ({$mid}/{$rid}) ]".\PHP_EOL;
		$this->errorMsg .= \darkphp\error\DwException::exceptionToString( $msgError );
	}
	public function toString() : string {
		return $this->errorMsg;
	}
	public function __destruct() {
		//$this->log->trace( "." );
		//parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return __CLASS__;
		//return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

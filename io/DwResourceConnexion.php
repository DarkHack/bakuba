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

namespace bakuba\kernel\io;
//use bakuba\kernel\io\DwResourceConnexion as DwResourceConnexion;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;

//use bakuba\kernel\io\DwResource as DwResource;
//use bakuba\kernel\io\DwResourceSelect as DwResourceSelect;

class DwResourceConnexion extends \darkphp\DwObject {
	
	private $dwResourceSelect = null;
	private $everResponse = false;
	
	protected $floatMicroSecondeStart;
	private $floatMicroSecondeEndDownload;
	private $floatMicroSecondeEndInternal;
	private $floatMicroSecondeEndRequest;
	public function __construct(
		DwResourceSelect $dwResourceSelect
		,DwResource $dwResource
	) {
		$this->floatMicroSecondeStart = \microtime( true );
		$this->dwResourceSelect=$dwResourceSelect;
		
		parent::__construct( $dwResource );
	}
	
	///*
	public function getDwResourceSelect() : DwResourceSelect {
		return $this->dwResourceSelect;
	}
	//*/
	public function getDwResource() : DwResource {
		return $this->parent;
	}
	
	public function read() : ?string {
		return $this->getDwResource()->read();
	}
	public function rsrcResponse() : void {
		if ( $this->everResponse ) {
			//$this->log->trace($data."\r\n============================================\r\n".$this->lastResponse);
			throw new \Exception( "[".$this->getClassName()."/".$this->getInstanceKey()."] :response ever write");
		}else {
			$this->floatMicroSecondeEndInternal=\microtime( true );
			//$this->log->debug( $data );
			$this->everResponse = true;
		}
	}
	/*
	//*/
	/*
	public function readRequest( \bakuba\kernel\objects\DwCallBack $dwCallBack) : void {
		$this->getDwResourceSelect()->ressourceRead(
			$this->getDwResource()
			,new DwCallBack( array( &$this, "requestReceived" ), $dwCallBack )
		);
	}
	//*/
	public function requestReceived( \bakuba\kernel\objects\DwCallBack $dwCallBack ) {
		$this->floatMicroSecondeEndDownload=\microtime( true );
		$dwCallBack->callback();
	}
	public function requestSended( ) {
		$this->floatMicroSecondeEndRequest = \microtime( true );
		$totalTime = \darkphp\string\DwString::floatMicroSecondePrint( $this->floatMicroSecondeEndRequest - $this->floatMicroSecondeStart );
		$receiveTime = \darkphp\string\DwString::floatMicroSecondePrint( $this->floatMicroSecondeEndDownload - $this->floatMicroSecondeStart );
		$interneTime = \darkphp\string\DwString::floatMicroSecondePrint( $this->floatMicroSecondeEndInternal - $this->floatMicroSecondeEndDownload );
		$sendTime = \darkphp\string\DwString::floatMicroSecondePrint( $this->floatMicroSecondeEndRequest - $this->floatMicroSecondeEndInternal );
		$this->log->debug("\r\n|-------- request( $totalTime ) receiveTime:$receiveTime + interneTime:$interneTime + sendTime:$sendTime" );
		$this->getDwResource()->disconnect();
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
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

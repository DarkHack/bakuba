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

namespace bakuba\kernel\protocole\dns;
//use bakuba\kernel\protocole\dns\DwDnsResponse as DwDnsResponse;

//use bakuba\kernel\objects\DwCallBack as DwCallBack;
//use bakuba\kernel\protocole\http\DwHttpHeader as DwHttpHeader;

class DwDnsResponse extends \bakuba\kernel\protocole\DwResponse {
	private static $dumper = null;
	
	private $dwDnsRequest = null;
	private $dwDnsResponse = null;
	
	public function __construct( DwDnsRequest $dwDnsRequest ) {
		if ( \is_null( self::$dumper ) ) self::$dumper = new \bakuba\kernel\protocole\dns\DwDnsBinaryDumper();
		$this->dwDnsRequest = $dwDnsRequest;
		
		$request = $this->dwDnsRequest->getDwDnsMessage();
		//$this->log->info( $request );
		
		$this->dwDnsResponse = new DwDnsMessage();
		$this->dwDnsResponse->id = $request->id;
		// dwDnsResponse flag
		$this->dwDnsResponse->rd = true;
		
		parent::__construct();
	}
	public function setDnsMessage( DwDnsMessage $dwDnsMessage ) {
		$this->dwDnsResponse = $dwDnsMessage;
	}
	public function setRcode( int $rcode ) {
		$this->dwDnsResponse->rcode = $rcode;
	}
	public function addQuestion( DwDnsQuery $dwQuery ) {
		$this->dwDnsResponse->questions[] = $dwQuery;
	}
	public function addAnswerData( DwDnsQuery $dwQuery ,$answer ,int $forceType = null ) {
		if (\is_null( $forceType ) ) $forceType=$dwQuery->type;
		
		if ( is_array( $answer ) ) { foreach( $answer as $anData ) {
			$dwRecord = new DwDnsRecord( $dwQuery ,600 ,$anData );
			$dwRecord->type = $forceType;
			$this->addAnswer( $dwRecord );
		} } else {
			$dwRecord = new DwDnsRecord( $dwQuery ,600 ,$answer );
			$dwRecord->type = $forceType;
			$this->addAnswer( $dwRecord );
		}
	}
	public function addAnswer( DwDnsRecord $dwRecord ) {
		$this->dwDnsResponse->answers[] = $dwRecord;
	}
	public function hasOneAnswer() : bool {
		return count( $this->dwDnsResponse->answers ) > 0;
	}
	public function toString( ) {
		return self::$dumper->toBinary( $this->dwDnsResponse );
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

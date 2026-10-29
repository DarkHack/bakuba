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

namespace bakuba\kernel\protocole\http;
//use bakuba\kernel\protocole\http\DwHttpHeader as DwHttpHeader;

class DwHttpHeader {//extends DwService
	// DwHttpHeader::$httpHeaders
	private static $httpHeaders = array(
		"Accept"
		,"Accept-Charset"
		,"Accept-Encoding"
		,"Accept-Language"
		,"Connection"
		,"Cookie"
		,"Host"
		,"Referer"
		,"User-Agent"
		,"Https"
		,"Content-Type"
		,"Content-Length"
		,"Content-Disposition"
		,"DNT"
		,"X-Forwarded-For"
		,"X-Forwarded-By"
		,"X-Forwarded-Host"
		,"X-Forwarded-Server"
		,"Upgrade-Insecure-Requests"
	);
	
	public static function parse( $request ) : DwHttpHeader {
		$instance = null;
		$matches = array();
		if ( preg_match_all(
			'/^[\s]*([\S]*):[\ ]*([\S]*)[\ ]*(.*)$/m'
			,$request
			,$matches
			,PREG_OFFSET_CAPTURE
		) )
			$instance = self::checkHeader( $request, $matches );
		else
			throw new \Exception( "no match [".$this->toString()."]" );
		return $instance;
	}
	public static function checkHeader( $request, $matches ) : ?DwHttpHeader {
		$dwHttpHeader = null;
		$dwHttpHeader = new self();
		$idx=0;
		foreach($matches[1] as $headerData) {
			//if ( $idx > 0 ) { 
				$headerName=$matches[1][$idx][0];
				$headerValue=(
					isset($matches[2][$idx][0])  ? 
						"".$matches[2][$idx][0].(
							isset($matches[3][$idx][0]) ? "".$matches[3][$idx][0]:"" 
						) 
					:""
				);
				$dwHttpHeader->setHeader( $headerName, rtrim($headerValue, "\r\n") );//substr($headerValue,0,-1) );
			//}
			$idx++;
		}
		return $dwHttpHeader;
	}
	private $data = array();
	public function setHeader( $header, $value ) : void {
		/*
		if ( ! in_array( $header, self::$httpHeaders ) )
			throw new \Exception("header:$header unknow.");
		else
		//*/
		$this->data[ strtolower($header) ] = $value;
	}
	public function issetHeader( $header ) : bool {
		return isset( $this->data[strtolower( $header )] );
	}
	
	public function getHeader( $header ) : string {
		$headerContent = "";
		/*
		if ( ! in_array( $header, self::$httpHeaders ) )
			throw new \Exception("header:$header unknow.");
		else
		//*/
		if ( $this->issetHeader( $header ) )
			$headerContent=$this->data[strtolower( $header )];
		return $headerContent;
	}
	public function getHeaders( ) : array {
		$headers = array();
		$headers = $this->data;
		return $headers;
	}
	public function toString() : string {
		return $this->getClassName();
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		//parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		//return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
		return __CLASS__;
	}
}
?>

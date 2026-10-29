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
//use bakuba\kernel\protocole\http\DwHttpResponse as DwHttpResponse;

//use bakuba\kernel\objects\DwCallBack as DwCallBack;
//use bakuba\kernel\protocole\http\DwHttpHeader as DwHttpHeader;

class DwHttpResponse extends \bakuba\kernel\protocole\DwResponse {
	
	// self::$messageHttpCode[]
	public static $messageHttpCode = array(
		//  Informational 1xx
		100 => "Continue"
		,101 => "Switching Protocols"
		// Successful 2xx
		,200 => "OK"
		,201 => "Created"
		,202 => "Accepted"
		,203 => "Non-Authoritative Information"
		,204 => "No Content"
		,205 => "Reset Content"
		,206 => "Partial Content"
		// Redirection 3xx
		,300 => "Multiple Choices"
		,301 => "Moved Permanently"
		,302 => "Found"
		,303 => "See Other"
		,304 => "Not Modified"
		,305 => "Use Proxy"
		,306 => ""
		,307 => "Temporary Redirect"
		// Client Error 4xx
		,400 => "Bad Request"
		,401 => "Unauthorized"
		,402 => "Payment Required"
		,403 => "Forbidden"
		,404 => "Not Found"
		,405 => "Method Not Allowed"
		,406 => "Not Acceptable"
		,407 => "Proxy Authentication Required"
		,408 => "Request Timeout"
		,409 => "Conflict"
		,410 => "Gone"
		,411 => "Length Required"
		,412 => "Precondition Failed"
		,413 => "Request Entity Too Large"
		,414 => "Request-URI Too Long"
		,415 => "Unsupported Media Type"
		,416 => "Requested Range Not Satisfiable"
		,417 => "Expectation Failed"
		// Server Error 5xx
		,500 => "Internal Server Error"
		,501 => "Not Implemented"
		,502 => "Bad Gateway"
		,503 => "Service Unavailable"
		,504 => "Gateway Timeout"
		,505 => "HTTP Version Not Supported"
	);
	
	private $dwHttpRequest = null;
	private $dwHttpHeader=null;
	private $httpResponseCode = 500;
	
	public function __construct( DwHttpRequest $dwHttpRequest ) {
		$this->dwHttpRequest = $dwHttpRequest;
		$this->dwHttpHeader = new DwHttpHeader();
		parent::__construct();
	}
	public function setHeader( $header, string $value ) : void {
		$this->dwHttpHeader->setHeader( $header, $value );
	}
	public function setResponseCode( int $httpResponseCode ) {
		$this->httpResponseCode = $httpResponseCode;
	}
	public function toString( ) {
		$httpResponse="";
		$dwHttpAction = $this->dwHttpRequest->getDwHttp()->getDwHttpAction();
		$httpResponse.=$dwHttpAction->getVersion();
		
		$httpCode = \intval( $this->httpResponseCode );
		$httpResponse.=" $httpCode";
		
		if ( isset( self::$messageHttpCode[$httpCode] ) )
			$httpResponse.=" ".self::$messageHttpCode[$httpCode];
		$httpResponse.="\r\n";
		
		$headers = $this->dwHttpHeader->getHeaders();
		if ( \is_array( $headers ) ) {
			foreach($headers as $header => $value) {
				$httpResponse.="$header: $value\r\n";
			}
		}
		$httpResponse.="\r\n";
		$httpResponse.=parent::toString();
		$httpResponse.="\r\n";
		
		return $httpResponse;
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

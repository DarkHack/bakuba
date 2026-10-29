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

namespace bakuba\kernel\protocole\smtp;
//use bakuba\kernel\protocole\smtp\DwSmtpMessage as DwSmtpMessage;

//use bakuba\kernel\objects\DwCallBack as DwCallBack;
//use bakuba\kernel\protocole\http\DwHttpHeader as DwHttpHeader;

class DwSmtpMessage extends \bakuba\kernel\protocole\DwProtocole {
	
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

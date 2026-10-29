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
//use bakuba\kernel\protocole\dns\DwDnsRequest as DwHttpRequest;

//use bakuba\kernel\protocole\http\DwHttpHeader as DwHttpHeader;
//use bakuba\kernel\protocole\http\DwHttpAction as DwHttpAction;

use \bakuba\kernel\services\server\aDwServerConnexion as aDwServerConnexion;

class DwDnsRequest extends \bakuba\kernel\protocole\DwRequest {
	private static $dwParser = null;
	
	public static function isValidRequest(
		aDwServerConnexion $dwServerConnexion
		,$binarydata
	) : ?\bakuba\kernel\protocole\DwRequest {
		$return = null;
		if ( is_null( self::$dwParser ) )
			self::$dwParser = new \bakuba\kernel\protocole\dns\DwDnsParser();
		
		$dwDnsMessage = self::$dwParser->parseMessage( $binarydata );
		if ( is_object( $dwDnsMessage ) )
			$return = new self( $dwServerConnexion ,$dwDnsMessage );
		return $return;
	}

	private function __construct(
		aDwServerConnexion $dwServerConnexion
		,DwDnsMessage $dwDnsMessage
	) {
		parent::__construct( $dwServerConnexion );
		$this->dwDnsMessage = $dwDnsMessage;
	}
	private $dwDnsMessage = null;
	public function getDwDnsMessage() : ?DwDnsMessage {
		return $this->dwDnsMessage;
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

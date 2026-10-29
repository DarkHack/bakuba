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
//use bakuba\kernel\protocole\http\DwHttpAction as DwHttpAction;

class DwHttpAction {//extends DwService
	private static $httpActions = array(
		"GET"
		,"HEAD"
		,"POST"
		,"PUT"
		,"DELETE"
		,"CONNECT"
		,"OPTIONS"
		,"TRACE"
	);
	
	public static function parse( string $request ) : self {
		$return = null;
		if ( empty( $request ) )
			throw new \Exception( "emtpy request(".\strlen($request)."):\r\n$request" );
		else {
			$matches = \preg_split('/\r?\n/' ,$request ,2 );
			if ( \is_array( $matches ) ) {
				$dwHttpAction = new self();
				if ( $dwHttpAction->analyseRequest( $matches[0] ) )
					$return = $dwHttpAction;
				else
					throw new \Exception(" no match:".print_r( $matches[0] ,true ) );
			}
		}
		return $return;
	}
	private function __construct() {
		
	}
	private function analyseRequest($request) : bool {
		$validRequest=false;
		$matches = array();
		if ( \preg_match_all( '/^[\s]*([\S]*)[\ ]*([\S]*)[\ ]*(.*)$/m', $request, $matches, PREG_OFFSET_CAPTURE ) ) {
			if ( isset( $matches[1][0][0] ) )  {
				$this->setAction( $matches[1][0][0] );
				if ( isset($matches[2][0][0]) ) {
					$this->setUri( $matches[2][0][0] );
					if ( isset($matches[3][0][0]) )
						$this->setVersion( $matches[3][0][0] );
					else
						$this->setVersion( null );
				} else
					$this->setUri( null );
				$validRequest=true;
			}
		} else
			throw new \Exception( "no match:".$matches[0] );
		
		return $validRequest;
	}
	
	private $action = null;
	public function setAction( $action ) {
		if ( ! in_array( $action, self::$httpActions ) )
			throw new \Exception("action:$action unknow.");
		else
			$this->action = $action;
	}
	public function getAction() {
		return $this->action;
	}
	private $uri = null;
	public function setUri( $uri ) {
			$this->uri = $uri;
	}
	public function getUri() {
		return $this->uri;
	}
	private $version = null;
	public function setVersion( $version ) {
		$this->version = $version;
	}
	public function getVersion() {
		return $this->version;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		//return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
		return __CLASS__;
	}
}
?>

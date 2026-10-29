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

namespace bakuba\kernel\io\curl;
//use bakuba\kernel\io\curl\DwCurl as DwCurl;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;

class DwCurl extends DwObject {
	
	private $url;
	
	private $options = array(
		CURLOPT_RETURNTRANSFER => true			// return web page
		,CURLOPT_HEADER         => false		// no return headers
		,CURLOPT_FOLLOWLOCATION => true			// follow redirects
		,CURLOPT_ENCODING       => ""			// handle all encodings
		,CURLOPT_USERAGENT      => "darkweb.fr"	// who am i
		,CURLOPT_AUTOREFERER    => true			// set referer on redirect
		,CURLOPT_CONNECTTIMEOUT => 120			// timeout on connect
		,CURLOPT_TIMEOUT        => 120			// timeout on response
		,CURLOPT_MAXREDIRS      => 10			// stop after 10 redirects
		,CURLOPT_SSL_VERIFYPEER => false		// Disabled SSL Cert checks
	);
	
	/**
	 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
	 * array containing the HTTP server response header fields and content.
	 */
	static private $sLog = null;
	public static function getHttps( $url, $postParams = null ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			self::$sLog->trace(". $url");
			$me = new self($url);
			$me->setPost( $postParams );
			return $me->execRequest()["content"];
		} catch(Exception $exception) {
			self::$sLog->error("exception [".self::getStaticClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".self::getStaticClassName()."]");
			throw $error;
		}
		return null;
	}
	
	public function __construct( $url, $postParams = null ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::__construct();
			//self::$sLog->trace ( $url );
			$this->setPost( $postParams );
			$this->url = $url;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}

	public function execContent() {
		try {
			self::$sLog->trace ( "." );
			return $this->execRequest()["content"];
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return null;
	}

	public function execAll() {
		try {
			self::$sLog->trace ( "." );
			return $this->execRequest();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return null;
	}

	private function execRequest() {
		$header  = null;
		try {
			self::$sLog->trace ( "".$this->url );
			if ( isset( $this->options[ CURLOPT_HTTPHEADER ] ) )
				self::$sLog->trace ( $this->options[ CURLOPT_HTTPHEADER ] );
			
			$ch      = curl_init( "".$this->url );
			curl_setopt_array( $ch, $this->options );
			$content = curl_exec( $ch );
			$err     = curl_errno( $ch );
			$errmsg  = curl_error( $ch );
			$header  = curl_getinfo( $ch );
			curl_close( $ch );
			/*
			self::$sLog->info("errno:".print_r($err,true));
			self::$sLog->info("errmsg:".print_r($errmsg,true));
			self::$sLog->info("content:".print_r($content,true));
			self::$sLog->info("header:".print_r($header,true));
			*/
			$header['errno']   = $err;
			$header['errmsg']  = $errmsg;
			$header['content'] = $content;
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $header;
	}

	//array('Content-Type: application/json' , 'Authorization: Bearer '.$token )
	public function setHeaders( array $headers = null ) {
		$this->options[ CURLOPT_HTTPHEADER ] = $headers; 
	}
	
	public function setPost( array $postParams = null ) {
		try {
			self::$sLog->trace ( "." );
			
			if ( is_array($postParams) ) {
				$this->options[ CURLOPT_POST ] = true;
				$this->options[ CURLOPT_POSTFIELDS ] = http_build_query( $postParams );
			}
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}

	}
	
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

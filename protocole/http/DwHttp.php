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
//use bakuba\kernel\protocole\http\DwHttp as DwHttp;

use bakuba\kernel\network\DwIp as DwIp;
//use bakuba\kernel\network\DwIp4 as DwIp4;
use bakuba\kernel\network\DwIp6 as DwIp6;

class DwHttp extends \bakuba\kernel\protocole\DwProtocole { //extends DwService
	private $dwHttpHeader = null;
	public function getDwHttpHeader() : ?DwHttpHeader {
		return $this->dwHttpHeader;
	}
	private $dwHttpAction = null;
	public function getDwHttpAction() : ?DwHttpAction {
		return $this->dwHttpAction;
	}
	
	private function __construct( DwHttpAction $dwHttpAction ,DwHttpHeader $dwHttpHeader ) {
		parent::__construct();
		$this->dwHttpHeader = $dwHttpHeader;
		$this->dwHttpAction = $dwHttpAction;
		
	}
	
	// bakuba\kernel\protocole\http\DwHttp::parseURL( string $url ) : array
	public static function parseURL( string $url ) : array {
		$urlPath = \parse_url( $url, \PHP_URL_PATH);
		if ( $urlPath == "/" ) {
			$type = "html";
			$ctrlName="Welcome";
			$file="";
		} else {
			$subUrlPath=substr( $urlPath ,1 );
			$type = substr( strrchr( $subUrlPath ,"." ) ,1 );
			if (empty($type))
				$type="-none";
			$file=strstr( substr( strrchr( $subUrlPath ,"/" ) ,1 ) ,"." ,true );
			$slashPost = strpos( $subUrlPath ,"/" );
			if ( $slashPost === false )
				$ctrlName=$subUrlPath;
			else 
				$ctrlName=strstr( $subUrlPath ,"/" ,true );
			
			$endPosClassName=strpos( $ctrlName ,"." );
			if ($endPosClassName)
				$ctrlName=substr( $ctrlName ,0 ,$endPosClassName );
		}
		
		$urlQuery = \parse_url( $url, \PHP_URL_QUERY);
		$getArray = array();
		if ( ! is_null( $urlQuery ) )
			\parse_str( $urlQuery, $getArray );
		
		if ( is_null( $getArray ) ) $getArray=array();
		$getArray["type"] = $type;
		$getArray["file"] = $file;
		$getArray["ctrlName"] = $ctrlName;
		
		return $getArray;
	}
	private static $apache2HeadersToServer = array(
		"Accept" => "HTTP_ACCEPT"
		,"Accept-Charset" => "HTTP_ACCEPT_CHARSET"
		,"Accept-Encoding" => "HTTP_ACCEPT_ENCODING"
		,"Accept-Language" => "HTTP_ACCEPT_LANGUAGE"
		,"Connection" => "HTTP_CONNECTION"
		,"Host" => "HTTP_HOST"
		,"Referer" => "HTTP_REFERER"
		,"User-Agent" => "HTTP_USER_AGENT"
		,"Https" => "HTTPS"
		,"Content-Type" => "CONTENT_TYPE"
		,"Content-Length" => "CONTENT_LENGTH"
		,"DNT" => "HTTP_DNT"
		,"X-Forwarded-For" => "HTTP_X_FORWARDED_FOR"
		,"X-Forwarded-By"	=> "HTTP_X_FORWARDED_BY"
		,"X-Forwarded-Host" => "HTTP_X_FORWARDED_HOST"
		,"X-Forwarded-Server" => "HTTP_X_FORWARDED_SERVER"
		// little hack for Forward HTTPS
		//,"Upgrade-Insecure-Requests" => "HTTPS"
	);
	// bakuba\kernel\protocole\http\DwHttp::parseApacheHeaders( $dwHttpHeader )
	public static function parseApacheHeaders( DwHttpHeader $dwHttpHeader ) : array {
		$server=array();
		foreach(self::$apache2HeadersToServer as $headerName => $serverName ) {
			if ( $dwHttpHeader->issetHeader( $headerName ) ) {
				$server[$serverName]= $dwHttpHeader->getHeader($headerName);
			}
		}
		return $server;
	}
	//
	public static function parse( $request ) : ?\bakuba\kernel\protocole\DwProtocole {
		$return = null;
		$matches = preg_split( '/\r?\n/', $request."\n\n", 2 );
		if ( is_array( $matches ) ) {
			$dwHttpAction = DwHttpAction::parse( $matches[0] );
			if ( is_object( $dwHttpAction ) ) {
				if ( isset( $matches[1] ) ) {
					$dwHttpHeader = null;
					$requestWithoutOrder=$matches[1];$matches=array();
					preg_match_all('/(?:\r?\n){2}/', $requestWithoutOrder, $matches, PREG_OFFSET_CAPTURE);
					if (isset($matches[0]) && isset($matches[0][0]) && isset($matches[0][0][1])) {
						$headersStr=substr($requestWithoutOrder,0, intval($matches[0][0][1])+1 );
						$dwHttpHeader = DwHttpHeader::parse( $headersStr );
					}
				} else
					$dwHttpHeader = new DwHttpHeader();
			}
			if ( is_object( $dwHttpAction ) && is_object( $dwHttpHeader ) ) {
				$return = new self( $dwHttpAction, $dwHttpHeader, $request );
			}
		}
		
		return $return;
	}
	/*
	private $matches = array();
	public function isValidRequest($request) {
		if ( parent::isValidRequest($request)) {
			return preg_match_all(
				'/^[\s]*([\S]*)[\ ]*([\S]*)[\ ]*(.*)$/m'
				,$request
				,$this->matches
				,PREG_OFFSET_CAPTURE
			);
		}
		return false;
	}
	//*/
	//\bakuba\kernel\protocole\http\DwHttp::extensionToContentType
	public static function extensionToContentType( string $extension ) : string {
		$contentType = null;
		switch( $extension ) {
		case "ico" :
			$contentType="image/vnd.microsoft.icon";
			break;
		case "ttf" :
			$contentType="application/x-font-ttf";
			break;
		case "woff" :
			$contentType="application/font-woff";
			break;
		case "woff2" :
			$contentType="application/font-woff2";
			//$contentType="font/".$extension.";";
			break;
		case "eot" :
			$contentType="application/vnd.ms-fontobject;";
			break;
		case "gif" :
			$contentType="image/gif;";
			break;
		case "png" :
			$contentType="image/png;";
			break;
		case "jpe":
		case "jpeg":
		case "jpg":
		case "pjpg":
		case "jfif":
		case "jfif-tbnl":
		case "jif":
			$contentType="image/jpeg;";
			break;
		case "svg" :
			$contentType="image/svg+xml; charset=utf-8";
			break;
		case "xml" :
			$contentType="application/xml; charset=utf-8";
			break;
		case "css" :
			$contentType="text/css; charset=utf-8";
			break;
		case "json" :
			$contentType="application/json; charset=utf-8";
			break;
		case "js" :
			$contentType="application/javascript; charset=utf-8";
			break;
		default :
		case "-none" :
		case "htm" :
		case "html" :
			$contentType="text/html; charset=utf-8";
			break;
		}
		
		return $contentType;
	}
	// DwHttp::proxyGetCltIp( $dwIp ,$arrServer ) : DwIp
	public static function proxyGetCltIp( DwIp $dwIp ,array $server ) : DwIp {
		$ipAddress = "x.x.x.x";
		
		$ipAddress = $server['REMOTE_ADDR'];
		if (isset($server['HTTP_X_FORWARDED_FOR']))
			$ipAddress = strpos($server['HTTP_X_FORWARDED_FOR'],',') === false ?
				$server['HTTP_X_FORWARDED_FOR'] 
				: array_pop(
					explode(',', $server['HTTP_X_FORWARDED_FOR'])
				)
			;
		
		$dwCIDR = $dwIp->getDwCIDR();
		$dwInterface = $dwCIDR->getDwInterface();
		$myCIDR = $dwInterface->getCIDR( $ipAddress );
		if ( is_null( $myCIDR ) ) {
			if ( is_a( $dwIp ,DwIp6::getStaticClassName() ) )
				$myCIDR = $dwInterface->createCIDR6( $ipAddress."/128" );
			else
				$myCIDR = $dwInterface->createCIDR( $ipAddress."/32" );
		}
		return $myCIDR->getIp( $ipAddress );
	}
}



?>


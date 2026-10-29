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
//use bakuba\kernel\protocole\smtp\DwHttpRequest as DwHttpRequest;

//use bakuba\kernel\protocole\http\DwHttpHeader as DwHttpHeader;
//use bakuba\kernel\protocole\http\DwHttpAction as DwHttpAction;

use \bakuba\kernel\services\server\DwServerConnexion as DwServerConnexion;

use bakuba\kernel\network\DwPort as DwPort;
use bakuba\kernel\network\DwIp as DwIp;
//use bakuba\kernel\network\DwIp4 as DwIp4;
use bakuba\kernel\network\DwIp6 as DwIp6;

class DwHttpRequest extends \bakuba\kernel\protocole\DwRequest {
	
	public static function isValidRequest( DwServerConnexion $dwServerConnexion ,$request ) : ?\bakuba\kernel\protocole\DwRequest {
		$return = null;
		$dwHttp = DwHttp::parse( $request );
		if ( is_object( $dwHttp ) ) {
			$return = new self( $dwServerConnexion ,$dwHttp );
			$return->request( $request );
		}
		return $return;
	}

	private function __construct( DwServerConnexion $dwServerConnexion ,DwHttp $dwHttp ) {
		parent::__construct( $dwServerConnexion );
		$this->dwHttp = $dwHttp;
	}
	private $dwHttp = null;
	public function getDwHttp() : DwHttp { return $this->dwHttp; }
	
	
	private $rqtHttps = null;
	public function getRqtHttps() : bool { return $this->rqtHttps; }
	private $rqtHost = null;
	public function getRqtHost() : string { return $this->rqtHost; }
	
	private $server = array();
	public function getServer() : array { return $this->server; }
	private $get = array();
	public function getGet() : array{ return $this->get; }
	private $post = array();
	public function getPost() : array{ return $this->post; }
	
	
	private $mergedValues = array();
	public function getMergeValues() : array { return $this->mergedValues; }
	
	
	public function getValue( string $name ) {
		return isset( $this->mergedValues[ $name ] ) ? $this->mergedValues[$name] : null;
	}

	private $postContent = null;
	public function getPostContent() : string {
		return $this->postContent;
	}
	
	private function parseBoundary( string $request ) : array {
		$dwHttpAction = $this->dwHttp->getDwHttpAction();
		$dwHttpHeader = $this->dwHttp->getDwHttpHeader();
		$post = array();
		
		$contentLength = intval( $dwHttpHeader->getHeader( "Content-Length" ) );
		$contentType = $dwHttpHeader->getHeader( "Content-Type" );
		
		if ( $contentLength > 0 ) {
			$boundaryArr=array();
			$this->postContent = \substr( $request ,-1 * $contentLength );
			if ( \preg_match('/^.*boundary="?([^";\n\r]*)/i' ,$contentType ,$boundaryArr ) ) {
				
				$boundary=$boundaryArr[1];
				$cpContent=-1;
				$bodyContent=array();
				foreach(preg_split('/\r?\n/', $this->postContent) as $line) {
					if ( \strpos( $line,$boundary ) === false && $cpContent >= 0 )
						$bodyContent[$cpContent].=$line."\r\n";
					else {
						$cpContent++;
						$bodyContent[$cpContent]="";
					}
				}
				$bodysRequest=array();
				foreach( $bodyContent as $content ) {
					$bodyRequest = $this->boundaryReadContent( $content );
					if ( is_array( $bodyRequest ) )
						$bodysRequest[]=$bodyRequest;
				}
				foreach( $bodysRequest as $bodyRequest ) {
					$nameArr=array();
					$bodyHeader=$bodyRequest["header"];
					if ( \preg_match('/^.*name="?([^";\n\r]*)/i', $bodyHeader->getHeader( "Content-Disposition" ), $nameArr) ) {
						$name=$nameArr[1];
						$post[$name]=$bodyRequest["content"];
					}
				}
			} else
				\parse_str( $this->postContent ,$post );
		} else
			$this->postContent = "";
		return $post;
	}
	private function parseApacheHeaders() : array {
		$dwHttpAction = $this->getDwHttp()->getDwHttpAction();
		$dwHttpHeader = $this->getDwHttp()->getDwHttpHeader();
		
		$srvIp = $this->getSrvIp()->getIpAddress();
		$srvPort = $this->getSrvPort()->getPort();
		$cltIp = $this->getCltIp()->getIpAddress();
		$cltPort = $this->getCltPort()->getPort();
		
		$server = DwHttp::parseApacheHeaders( $dwHttpHeader );
		
		if ( $dwHttpHeader->issetHeader( "Https" ) )
			$srvPort=443;
			
		$server["SERVER_ADDR"] = $srvIp;
		$server["SERVER_PORT"] = $srvPort;
		$server["REMOTE_ADDR"] = $cltIp;
		$server["REMOTE_PORT"] = $cltPort;
		$server["REQUEST_URI"] = $dwHttpAction->getUri();
		$server['BAKUBA_URI'] = $dwHttpAction->getUri();
		return $server;
	}
	private function request( $request ) {
		
		$dwHttpAction = $this->getDwHttp()->getDwHttpAction();
		$dwHttpHeader = $this->getDwHttp()->getDwHttpHeader();
		
		$server = $this->parseApacheHeaders();
		$this->proxyCheck( $server );
		//$raw64Content = base64_encode( $this->dwHttpRequest->getPostContent() );
		$url="http://";
		if ( isset( $server[ 'HTTP_HOST' ] ) )
			$url.=$server[ 'HTTP_HOST' ];
		else
			$url.=$srvIp;
		
		$url.=$dwHttpAction->getUri();
		
		$this->server=$server;
		$this->get=DwHttp::parseURL( $url );
		$this->post=$this->parseBoundary( $request );
		$this->mergedValues = array_merge( $this->server, $this->get, $this->post );
		///*
		//*/
		$this->rqtHost = explode(':' ,$this->server["HTTP_HOST"] )[0];
		$this->rqtHttps = isset($this->server["HTTPS"]) && !empty($this->server["HTTPS"]);
	}
	
	private function boundaryReadContent( string $content ) : ?array {
		$bodyRequest = null;
		$matches=array();
		\preg_match_all('/(?:\r?\n){2}/', $content, $matches, PREG_OFFSET_CAPTURE);
		if ( isset($matches[0]) && isset($matches[0][0]) && isset( $matches[0][0][1] ) ) {
			$headersStr = \substr( $content,0, \intval( $matches[0][0][1] ) + 1 );
			
			$bodyHeader = DwHttpHeader::parse( $headersStr );
			$bodyStr=substr($content,intval($matches[0][0][1])+4 );
			
			$bodyRequest = array( "header" => $bodyHeader ,"content" => $bodyStr );
		}
		return $bodyRequest;
	}
	private function proxyCheck( array $apacheProxyData ) {
		
		$dwHttpHeader = $this->getDwHttp()->getDwHttpHeader();
		if ( $dwHttpHeader->issetHeader( "Referer" ) ) {
			if ( strtolower( substr( $dwHttpHeader->getHeader( "Referer" ) ,0 ,5) ) == "https" )
				$dwHttpHeader->setHeader( "Https" ,1 );
		} elseif ( 
			$dwHttpHeader->issetHeader("Sec-Fetch-Dest")
			|| $dwHttpHeader->issetHeader("Sec-Fetch-Mode")
			|| $dwHttpHeader->issetHeader("Sec-Fetch-Site")
		)
				$dwHttpHeader->setHeader( "Https" ,1 );
		
		$cltIp = DwHttp::proxyGetCltIp( $this->getCltIp() ,$apacheProxyData );
		
		$cltPortToSwitch=$cltIp->getPort( $apacheProxyData["REMOTE_PORT"] );
		$srvPortToSwitch = $this->getSrvPort();
		if ( isset( $apacheProxyData[ "HTTP_X_FORWARDED_BY" ] ) ) {
			$ipAddress = $apacheProxyData[ "HTTP_X_FORWARDED_BY" ];
			$dwCIDR = $this->getSrvIp()->getDwCIDR();
			$dwInterface = $dwCIDR->getDwInterface();
			$myCIDR = $dwInterface->getCIDR( $ipAddress );
			if ( is_null( $myCIDR ) )
				if ( is_a( $this->getSrvIp() ,DwIp6::getClassName() ) )
					$myCIDR = $dwInterface->createCIDR6( $ipAddress."/128" );
				else
					$myCIDR = $dwInterface->createCIDR( $ipAddress."/32" );
			$srvIp=$myCIDR->getIp( $ipAddress );
			$srvPortToSwitch = $srvIp->getPort( $apacheProxyData["SERVER_PORT"] );
		}
		$this->switchSession( $cltPortToSwitch ,$srvPortToSwitch );
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

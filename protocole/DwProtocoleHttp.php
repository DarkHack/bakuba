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

namespace bakuba\kernel\protocole;
//use bakuba\kernel\protocole\DwProtocoleHttp as DwProtocoleHttp;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;

class DwProtocoleHttp extends DwObject {
	private $matches = array();
	private function __construct() {
		parent::__construct();
		$this->className=$className;
		$this->dwApplicationManager = $dwApplicationManager;
		//$this->link($this->dwApplicationManager);
		$this->log->trace(".");
	}

	public function isValidRequest($request) {
		//if ( parent::isValidRequest($request)) {
			return preg_match_all(
				'/^[\s]*([\S]*)[\ ]*([\S]*)[\ ]*(.*)$/m'
				,$request
				,$this->matches
				,PREG_OFFSET_CAPTURE
			);
		//}
		return false;
	}

	public function getHeaders() {

	}
	private $httpActions = array("GET","HEAD","POST");
	private $httpHeaders = array("Host", "Accept", "Accept-Encoding", "Accept-Language", "User-Agent");
	public function response( DwSocket $dwSocket ) {
		$httpResponse="fatal error";
		try {
			socket_getpeername ( $dwSocket->sock , $address , $port ); 
			$headersRequest=array();
			$requestCmd = $this->matches[1][0][0];
			$requestUri = $this->matches[2][0][0];
			$requestVersion = $this->matches[3][0][0];
			$idx=0;
			foreach($this->matches[1] as $headerData) {
				if ( $idx > 0 ) { 
					$headerName=$this->matches[1][$idx][0];
					$headerValue=( isset($this->matches[2][$idx][0])  ? "".$this->matches[2][$idx][0].(isset($this->matches[3][$idx][0]) ? "".$this->matches[3][$idx][0]:"" ) :"" );
					$headersRequest[substr($headerName,0 ,-1)] = substr($headerValue,0,-1);
				}
				$idx++;
			}
			$server = array();
			$post=array();
			$server['HTTP_HOST']=$headersRequest["Host"];//"bakuba.darkweb.fr";
			if ( $requestCmd == "POST" ) {
				$idx--;
				//print_r(array("1" => $this->matches[1][$idx], "2" => $this->matches[2][$idx], "3" => $this->matches[3][$idx]) );
				$postQuery=$this->matches[1][$idx][0].( isset($this->matches[2][$idx][0])  ? "".$this->matches[2][$idx][0].(isset($this->matches[3][$idx][0]) ? "".$this->matches[3][$idx][0]:"" ) :"" );
				//echo "post query============================\n$$postQuery";
				$raw64Content = base64_encode( $postQuery );
				parse_str($postQuery, $post);
				//print_r($post);
			}
			else $raw64Content = base64_encode( "" );
			$url= "http://".$headersRequest["Host"].$requestUri;
			$urlPath = parse_url($url, PHP_URL_PATH);
			$urlQuery = parse_url($url, PHP_URL_QUERY);
			if ( $urlPath == "/" ) {
				$type = "html";
				$className="Welcome";
				$file="";
			} else {
				$type = substr(strrchr($urlPath, "."),1);
				if (empty($type))
					$type="-none";
				/*
				$firtOccur=strpos($urlPath,"/",1);
				if ($firtOccur) {
					$nextOccur=strpos($urlPath,"/",$firtOccur);
					if (!$nextOccur)
						$className = substr( $urlPath, 1);
					else
						$className = substr( $urlPath, 1,  $nextOccur - 1 );
				} else
					$className = substr( $urlPath, 1);
				*/
				/*
				if ($nextOccur)
					$file=substr( $urlPath, $firtOccur, $nextOccur);
				else
					$file=substr( $urlPath, $firtOccur + 1, strpos($urlPath,".",$firtOccur + 1) - $firtOccur); 
				*/
				$file=substr(strrchr($urlPath, "/"),1);
				$file=substr($file,0,strpos($file,"."));
				$className = $file;
			}
			echo "DwProtocoleHttp";
			print_r(array("urlPath" => $urlPath, "className" => $className, "type" => $type, "file" => $file));
			$server["SERVER_ADDR"]="127.0.0.1";
			$server['REMOTE_ADDR']=$address;
			$server['REQUEST_URI']=$requestUri;

			$request = array(
				"server" => $server
				, "get" => array("type" => $type, "file" => $file,"className" => $className)
				, "post" => $post
				, "raw64Content" => $raw64Content
			);//
			//$response64 = $this->answer( json_decode( json_encode( $request ), true ) );
			$tabResponse = $this->answer( $request );
			$httpResponse= "$requestVersion 200 OK\r\n";
			foreach($tabResponse["headers"] as $header) {
				$httpResponse.="$header\r\n";
			}
			$httpResponse.="\r\n";
			$httpResponse.=base64_decode($tabResponse["response"]);
		} catch( \Exception $err ) {
			$this->log->error($err);
		}
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


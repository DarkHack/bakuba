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

namespace bakuba\kernel\services;

use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\io\socket\DwSocket as DwSocket;

use bakuba\kernel\services\protocole\http\DwHttpServiceRequest as DwHttpServiceRequest;

use bakuba\kernel\application\DwApplicationManager as DwApplicationManager;

use bakuba\kernel\process\server\DwServerConnexion as DwServerConnexion;
class DwDarkSrv extends DwService {
	static private $sLog = null;
	
	private $tabRequest = null;
	public function isValidRequest($request) {
		$this->tabRequest = null;
		try {
			if ( parent::isValidRequest($request)) {
				$this->tabRequest = json_decode( base64_decode($request), true );
				return is_array($this->tabRequest);
			}
		
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			//throw $exception;
			$this->tabRequest = null;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			//throw $error;
			$this->tabRequest = null;
		}
			
		return false;
	}
	
	protected function __construct( $className, DwApplicationManager $dwApplicationManager ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			self::$sLog->trace(".");
			parent::__construct( $className, $dwApplicationManager );
			
		
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}

	public function request( DwServerConnexion $dwServerConnexion ) {
		try {
		
			$tabResponse=$this->AppRequest( new DwServiceConnexion( 
				$dwServerConnexion
				,$this
				,new DwHttpServiceRequest( $this->tabRequest["server"] ,$this->tabRequest["get"] ,$this->tabRequest["post"] )//,$this->tabRequest["raw64Content"])
			) );
		} catch(Exception $exception) {
			$dwServerConnexion->response("DwDarkSrv->request:fatal error!");
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			$dwServerConnexion->response("DwDarkSrv->request:fatal error!");
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function response( DwServiceConnexion $dwServiceConnexion, array $tabResponse ) {
		//$httpResponse="DwDarkSrv->responseCB:fatal error!";
		if (  ! is_array($tabResponse) )
			throw new Exception("tabResponse is null.");
		
		$response64 = base64_encode( json_encode( $tabResponse ) );
		//$this->log->trace(". $response64");
		
		$dwServiceConnexion->responseRaw($response64."\r\n\r\n\r\n");
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


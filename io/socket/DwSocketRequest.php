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

namespace bakuba\kernel\io\socket;
//use bakuba\kernel\io\socket\DwSocketRequest as DwSocketRequest;
use \Exception as Exception;
use \Error as Error;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;
use bakuba\kernel\io\socket\DwSocket as DwSocket;

use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\agartha\DwAgartha as DwAgartha;

use bakuba\kernel\network\DwPort as DwPort;
use bakuba\kernel\services\server\DwServerConnexion as DwServerConnexion;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;

class DwSocketRequest extends DwObject {
	private static $sLog = null;
	
	private $dwProcessResources;
	public function getProcess() : DwProcessResources {
		return $this->dwProcessResources;
	}
	private $dwSocket;
	public function getDwSocket() : DwSocket {
		return $this->dwSocket;
	}
	private $dwSocketConnexion;
	private $start;
	private $end;
	
	public function __construct( DwProcessResources $dwProcessResources , DwSocket $dwSocket ,DwServerConnexion $dwServerConnexion ) {
		//DwSocket $dwSocket, DwServerConnexion $dwServerConnexion
		try {
			if ( is_null( self::$sLog ) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			parent::__construct(  );
			//self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			//$parentProcess, new DwCallBack( array($this,"requestReceived" ) ), new DwCallBack( array($this,"requestSended" ) )
			$this->dwProcessResources = $dwProcessResources;
			$this->dwSocket=$dwSocket;
			$this->start = microtime( true );
			$this->everResponse = false;
			try {
				$sock = $this->dwSocket->getResource();
				socket_getsockname( $sock, $this->srvIp ,$this->srvPort );
				socket_getpeername( $sock, $this->cltIp ,$this->cltPort );
			} catch(Exception $exception) {
				self::$sLog->error("exception getsockname/getpeername [".$this->getClassName()."/".$this->getInstanceKey()."]");
				//throw $exception;
			} catch(Error $error) {
				self::$sLog->error("error getsockname/getpeername [".$this->getClassName()."/".$this->getInstanceKey()."]");
				//throw $error;
			}
			
			$this->dwSocketConnexion = new DwSocketConnexion( $this, $dwServerConnexion);
			
			$this->dwSocket->setOnReaded( new DwCallBack( array( &$this, "requestReceived" ) ) );
			$this->dwProcessResources->socketRead( $this->dwSocket );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Exception $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	private $srvIp = null;
	public function getSrvIp() : string {
		return $this->srvIp;
	}
	private $srvPort = null;
	public function getSrvPort() : string {
		return $this->srvPort;
	}
	private $cltIp = null;
	public function getCltIp() : string {
		return $this->cltIp;
	}
	private $cltPort = null;
	public function getCltPort() : string {
		return $this->cltPort;
	}
	
	private $writed = false;
	
	public function requestReceived( DwSocket $dwSocket ) {
		try {
			//self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			$this->dwSocketConnexion->request( $this->dwSocket );
			
		} catch(Exception $exception) {
			$this->response( $dwSocket ,"dwSocketConnexion->request fatal error!" );
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Exception $error) {
			$this->response( $dwSocket ,"dwSocketRequest->request fatal error!" );
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}	
	}
	
	public function requestSended( DwSocket $dwSocket ) {
		try {
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
			$this->end = microtime( true );
			self::$sLog->debug("start:".( $this->end - $this->start )."s ".$this->start."/".$this->end." " );
			$this->dwSocket->disconnect();
			$this->writed = true;
			//$this->stop();
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	private $everResponse = false;
	private $lastResponse = false;
	// from DwLightHttpd->response
	public function response( string $data ) {
		try {
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			if ( $this->everResponse ) {
				//self::$sLog->trace($data."\r\n============================================\r\n".$this->lastResponse);
				throw new Exception($this->getInstanceKey().":response ever write");
			} else {
				//self::$sLog->debug( $data );
				$this->dwSocket->write( $data );
				$this->dwSocket->setOnWrited( new DwCallBack( array( &$this, "requestSended" ) ) );
				$this->dwProcessResources->socketWrite( $this->dwSocket );
				$this->everResponse = true;
				$this->lastResponse = $data;
			}
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
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

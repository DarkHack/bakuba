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

namespace bakuba\kernel\application\session\httpAction;
//use bakuba\kernel\application\session\httpAction\DwSessionControllerConnexion as DwSessionControllerConnexion;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;

use bakuba\kernel\application\session\DwSessionRequest as DwSessionRequest;
use bakuba\kernel\application\session\DwSessionResponse as DwSessionResponse;

use bakuba\kernel\application\session\httpAction\DwHttpActionChannel as DwHttpActionChannel;
use bakuba\kernel\application\controller\DwControllerConnexion as DwControllerConnexion;

use bakuba\kernel\objects\web\DwWebAction as DwWebAction;

class DwSessionControllerConnexion extends DwObject {
	private static $sLog  = null;
	
	public static function connection(
		\bakuba\kernel\application\controller\DwControllerConnexion $dwControllerConnexion
	) : DwSessionControllerConnexion {
		$dwSessionControllerConnexion = null;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			
			$dwSessionManager = $dwControllerConnexion->getDwSessionManager();
			
			$dwSessionRequest = new DwSessionRequest(
				$dwControllerConnexion->getDwSessionIpKey()
				,"HttpSessionStart"
			);
			
			$dwSessionRequest->setObject( $dwControllerConnexion->getMergeValues() );
			
			$dwSessionResponse = $dwSessionManager->HttpSessionStart( $dwSessionRequest );
			
			$dwSessionControllerConnexion = new DwSessionControllerConnexion(
				$dwControllerConnexion
				,$dwSessionResponse->getObject()
			);
			
		} catch( Exception $exception ) {
			self::$sLog->error("exception [".self::getStaticClassName()."]");
			self::$sLog->error($exception);
			throw $exception;
		} catch( Error $error ) {
			self::$sLog->error("error [".self::getStaticClassName()."]");
			self::$sLog->error($error);
			throw $error;
		}
		return $dwSessionControllerConnexion;
	}
	
	private $dwControllerConnexion = null;
	public function getDwControllerConnexion() : DwControllerConnexion {
		return $this->dwControllerConnexion;
	}
	
	private $dwHttpActionChannel = null;
	public function getDwHttpActionChannel() : DwHttpActionChannel {
		return $this->dwHttpActionChannel;
	}
	
	public function __construct( DwControllerConnexion $dwControllerConnexion, DwHttpActionChannel $dwHttpActionChannel  ) {
		try {
			if ( is_null( self::$sLog ) ) self::$sLog = DwLogger::getLogger( __CLASS__ );
			parent::__construct();
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
			$this->dwControllerConnexion=$dwControllerConnexion;
			$this->dwHttpActionChannel=$dwHttpActionChannel;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	// $this->dwHttpActionChannel->getUrlAction( $this->dwControllerConnexion->getDwWebUrl(), $dwWebAction );
	public function sessionWrite( ) : bool {
		$return = false;
		try {
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
			$dwSessionManager = $this->dwControllerConnexion->getDwSessionManager();
			$dwSessionRequest = new DwSessionRequest( $this->dwControllerConnexion->getDwSessionIpKey(), "HttpSessionWrite" );
			$dwSessionRequest->setObject( $this->dwHttpActionChannel );
			$dwSessionResponse = $dwSessionManager->HttpSessionWrite( $dwSessionRequest );
			//$this->sessionWrited( $dwSessionResponse );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
		return $return;
	}
	/*
	public function sessionWrited( DwSessionResponse $dwSessionResponse ) : void {
		try {
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
			self::$sLog->trace( $dwSessionResponse );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	//*/
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

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

namespace bakuba\kernel\application\session;
//use bakuba\kernel\application\session\aDwApplicationConnexion as aDwApplicationConnexion;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

abstract class aDwApplicationConnexion
	 extends \darkphp\DwObject implements \bakuba\kernel\application\iDwApplicationConnexion {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
//				,time()."-".$this->getInstanceKey()
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	
	private $dwApplicationRequest = null;
	public function getDwApplicationRequest() : \bakuba\kernel\application\DwApplicationRequest {
		return $this->dwApplicationRequest;
	}
	public function getDwApplication() : \bakuba\kernel\application\aDwApplication {
		return $this->getDwApplicationSession();
	}
	public function getDwApplicationSession() : aDwApplicationSession {
		return $this->parent;
	}
	private $dwProcessConnexion = null;
	public function getSrcIpcKey() : \bakuba\kernel\io\ipc\DwIpcKey {
		return $this->dwProcessConnexion->getSrcIpcKey();
	}
	public function __construct(
		aDwApplicationSession $aDwApplicationSession
		,\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) {
		$this->dwApplicationRequest = $dwApplicationRequest;
		$this->dwProcessConnexion = $dwProcessConnexion;
		parent::__construct( $aDwApplicationSession );
		try {
			$this->connectSession();
		} catch( \Exception $exception ) {
			$this->throwError( $exception );
			//throw $exception;
		} catch( \Error $error ) {
			$this->throwError( $error );
			//throw $error;
		}
		//$this->log->trace(".");
		
	}
	public function connectSession() : void {
		$dwSessionManager = $this->getDwApplicationSession()->getDwSessionManager();
		$dwSessionManager->connect( $this , new DwCallBack( array( &$this ,'sessionConnected' ) ) );
	}
	private $dwSessionChannel = null;
	public function getDwSessionChannel( ) : DwSessionChannel {
		return $this->dwSessionChannel;
	}
	public function sessionConnected( DwSessionChannel $dwSessionChannel ) : void {
		try {
			$this->dwSessionChannel = $dwSessionChannel;
			$this->appResponse( $this->connect() );
		} catch( \Exception $exception ) {
			$this->throwError( $exception );
			//throw $exception;
		} catch( \Error $error ) {
			$this->throwError( $error );
			//throw $error;
		}
	}
	//public function connect() : \bakuba\kernel\application\DwApplicationResponse;
	
	protected function ipcResponse( $anResponse ) : void {
		$this->dwProcessConnexion->ipcResponse( $anResponse );
	}
	private function appResponse( \bakuba\kernel\application\DwApplicationResponse $dwApplicationResponse ) : void{
		$this->dwProcessConnexion->ipcResponse( $dwApplicationResponse );
	}
	protected function throwError( $errorHandle ) : void {
		$this->dwProcessConnexion->throwError( $errorHandle );
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getSessionClassName() : string {
		return DwSessionChannel::getStaticClassName();
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

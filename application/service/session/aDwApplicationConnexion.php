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

namespace bakuba\kernel\application\service\session;
//use bakuba\kernel\application\service\session\aDwApplicationConnexion as aDwApplicationConnexion;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

abstract class aDwApplicationConnexion
	 extends \bakuba\kernel\application\session\aDwApplicationConnexion implements \bakuba\kernel\application\service\iDwApplicationConnexion {
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
	public function getDwApplicationServiceSession() : aDwApplicationSession {
		return $this->getDwApplicationSession();
	}
	public function __construct(
		aDwApplicationSession $aDwApplicationSession
		,\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) {
		parent::__construct( $aDwApplicationSession ,$dwProcessConnexion ,$dwApplicationRequest );
		//$this->log->trace(".");
		
	}
	public function sessionConnected( \bakuba\kernel\application\session\DwSessionChannel $dwSessionChannel ) : void {
		$dwSessionManager = $this->getDwApplicationServiceSession()->getDwSessionServiceManager();
		$dwSessionManager->connect( $this , new DwCallBack( array( &$this ,'sessionConnected' ) ) );
	}
	public function sessionServiceConnected( DwSessionChannel $dwSessionChannel ) : void {
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
	public function connect() : \bakuba\kernel\application\DwApplicationResponse {
		return new \bakuba\kernel\application\service\DwApplicationResponse( $this->connectService() );
	}
	//public function connectService(  ) : \bakuba\kernel\protocole\DwResponse;
	public function getServiceDwApplicationRequest() : \bakuba\kernel\application\service\DwApplicationRequest {
		return $this->getDwApplicationRequest();
	}
	public function getDwServiceRequest() : \bakuba\kernel\services\DwServiceRequest {
		return $this->getServiceDwApplicationRequest()->getDwServiceRequest();
	}
	/*
	public function connectSession() : void {
		$dwSessionManager = $this->getDwApplicationSession()->getDwSessionManager();
		$dwSessionManager->connect( $this , new DwCallBack( array( &$this ,'sessionConnected' ) ) );
	}
	private $dwSessionChannel = null;
	public function getDwSessionChannel( ) : DwSessionChannel {
		return $this->dwSessionChannel;
	}
	public function sessionConnected( DwSessionChannel $dwSessionChannel ) : void {
		$this->dwSessionChannel = $dwSessionChannel;
		$this->appResponse( $this->connect() );
	}
	//*/
	//public function connect() : \bakuba\kernel\application\DwApplicationResponse;
	
	public function getSessionClassName() : string {
		return DwSessionChannel::getStaticClassName();
	}
	public function serviceThrowError( $errorHandle ) : \bakuba\kernel\protocole\DwResponse {
		$dwResponseError = new \bakuba\kernel\protocole\DwResponse ( $this->getDwRequest() );
		
		if ( \method_exists( $this->log ,"getFullPath" ) )
			$dwResponseError->response(
				"aDwApplicationConnexion ERROR, see:\r\n".$this->log->getFullPath().\PHP_EOL
			);
		else
			$dwResponseError->response(
				\bakuba\kernel\protocole\DwException::throwableToMessage( $errorHandle )
			);
		return $dwResponseError;
	}
	protected function throwError( $errorHandle ) : void {
		//$this->log->error( \darkphp\error\DwException::exceptionToString( $errorHandle ) );
		$this->ipcResponse( $this->serviceThrowError( $errorHandle ) );
		$this->log->throwable( $errorHandle );
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

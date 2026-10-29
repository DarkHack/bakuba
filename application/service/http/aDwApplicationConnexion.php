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

namespace bakuba\kernel\application\service\http;
//use bakuba\kernel\application\service\http\aDwApplicationConnexion as aDwApplicationConnexion;

//use bakuba\kernel\objects\DwCallBack as DwCallBack;

abstract class aDwApplicationConnexion
	extends \bakuba\kernel\application\service\aDwApplicationConnexion implements iDwApplicationConnexion {
	
	public function __construct(
		iDwApplication $iDwApplication
		,\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) {
		parent::__construct( $iDwApplication ,$dwProcessConnexion ,$dwApplicationRequest );
		$this->log->trace(".");
	}
	public function connectService() : \bakuba\kernel\protocole\DwResponse {
		$dwHttpResponse=null;
		try {
			$dwHttpResponse=$this->connectServiceHttp();
		} catch( \Exception $exception ) {
			$dwHttpResponse=$this->httpThrowError( $exception );
			//throw $exception;
		} catch( \Error $error ) {
			$dwHttpResponse=$this->httpThrowError( $error );
			//throw $error;
		}
		return $dwHttpResponse;
	}
	//public function connectServiceHttp(  ) : \bakuba\kernel\protocole\http\DwHttpResponse;
	
	public function getHttpServiceApplicationRequest() : DwApplicationRequest {
		return $this->getServiceDwApplicationRequest();
	}
	public function getDwHttpServiceRequest() : \bakuba\kernel\services\http\DwHttpServiceRequest {
		return $this->getHttpServiceApplicationRequest()->getDwHttpServiceRequest();
	}
	public function getDwHttpRequest() : \bakuba\kernel\protocole\http\DwHttpRequest {
		return $this->getDwHttpServiceRequest()->getDwHttpRequest();
	}
	public function httpThrowError( $errorHandle ) : \bakuba\kernel\protocole\http\DwHttpResponse {
		$this->log->throwable( $errorHandle );
		$dwHttpResponseError = new \bakuba\kernel\protocole\http\DwHttpResponse(
			$this->getDwHttpRequest()
		);
		$dwHttpResponseError->setHeader( "Content-type" ,"text/plain; charset=utf-8" );
		$dwHttpResponseError->setResponseCode( 500 );
		if ( \method_exists( $this->log ,"getFullPath" ) )
			$dwHttpResponseError->response(
				"aDwApplicationConnexion ERROR, see:\r\n".$this->log->getFullPath().\PHP_EOL
			);
		else
			$dwHttpResponseError->response(
				\bakuba\kernel\protocole\DwException::throwableToMessage( $errorHandle )
			);
		return $dwHttpResponseError;
	}
	protected function throwError( $errorHandle ) : void {
		$this->ipcResponse( $this->httpThrowError( $errorHandle ) );
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

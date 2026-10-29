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

namespace bakuba\kernel\application\service\http\controller;
//use bakuba\kernel\application\service\http\controller\DwApplicationControllerConnexion as DwApplicationControllerConnexion;

class DwApplicationControllerConnexion extends \bakuba\kernel\application\service\http\aDwApplicationConnexion {
	/*
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className = \darkphp\source\DwSource::classNameToTopName( $this->getClassName() );
		if ( \method_exists( $iDwLogger ,"upTo" ) ) {
			$dwSessionIpKey = $this->getDwHttpServiceRequest()->getDwSessionIpKey();
			$this->log = $iDwLogger->upTo( $this ,$dwSessionIpKey->getIpClt() ,$className );
		} else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	//*/
	public function __construct(
		aDwApplicationController $aDwApplicationController
		,\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\service\http\DwApplicationRequest $dwApplicationRequest
	) {
		parent::__construct( $aDwApplicationController ,$dwProcessConnexion ,$dwApplicationRequest );
		$this->log->trace(".");
	}
	public function getDwHttpServiceRequest() : \bakuba\kernel\services\http\DwHttpServiceRequest {
		return $this->getHttpServiceApplicationRequest()->getDwHttpServiceRequest();
	}
	
	protected $end;
	protected $start;
	/*
	public function runApp( \bakuba\kernel\application\aDwApplication $aDwApplication ) {
		try {
			$this->start = \microtime( true );
			$dwController = $this->requestGetController( $aDwApplication );
			$dwControllerConnexion = new DwControllerConnexion( $this ,$dwController );
			$dwControllerConnexion->executeController();
		} catch(\Exception $exception) {
			$this->throwError( $exception );
		//	throw $exception;
		} catch(\Error $error) {
			$this->throwError($error);
		//	throw $error;
		}
	}
	//*/
	public function httpServiceRequest( \bakuba\kernel\services\http\DwHttpServiceRequest $dwHttpServiceRequest ) : void {
		$dwController = $this->requestGetController( $dwHttpServiceRequest );
		if ( \is_null( $dwController ) ) {
			$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
			$dwHttpResponse = new \bakuba\kernel\protocole\http\DwHttpResponse(
				$dwHttpRequest
			);
			$dwHttpResponse->setHeader( "Content-type","text/plain; charset=utf-8" );
			$dwHttpResponse->setResponseCode( 404 );
			$dwHttpResponse->response( "aDwApplicationController not found for :".$dwHttpRequest->getCltIp()->toString() );
			$this->fullResponse( $dwHttpResponse );
		} else {
			$dwControllerConnexion = $dwController->connect( $this );
			$dwRessources = $this->getDwApplication()->getDwResources();
			$dwRessources->addTick( new \bakuba\kernel\objects\DwCallBack( array( &$dwControllerConnexion ,'executeController' ) ) );
		}
	}
	public function requestGetController( \bakuba\kernel\services\http\DwHttpServiceRequest $dwHttpServiceRequest ) : ?aDwController {
		return $this->parent->requestGetController( $dwHttpServiceRequest );
	}
	
	/*
	public function connectApp(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\aDwApplicationConnexion {
		return new self( $this ,$dwProcessConnexion ,$dwApplicationRequest );
	}
	//*/
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

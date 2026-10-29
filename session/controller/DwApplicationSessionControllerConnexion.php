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

namespace bakuba\kernel\application\controller;
//use bakuba\kernel\application\controller\DwApplicationConnexion as DwApplicationConnexion;

use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\application\controller\DwControllerConnexion as DwControllerConnexion;

use bakuba\kernel\services\protocole\http\DwHttpServiceRequest as DwHttpServiceRequest;

use bakuba\kernel\application\session\DwSessionIpKey as DwSessionIpKey;

class DwApplicationSessionControllerConnexion extends \bakuba\kernel\application\controller\DwApplicationControllerConnexion {
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
	/*
	public function __construct(
		\bakuba\kernel\process\DwProcess $dwApplication
		,\bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) {
		parent::__construct( $dwApplication ,$dwIpcMsg ,$dwApplicationRequest );
		$this->log->trace(".");
	}
	//*/
	public function runApp( \bakuba\kernel\application\aDwApplication $aDwApplication ) {
		try {
			$this->start = \microtime( true );
			$dwController = $this->requestGetController();
			$dwControllerConnexion = new DwControllerSessionConnexion( $this ,$dwController );
			$dwControllerConnexion->executeController();
		} catch(\Exception $exception) {
			$this->throwError( $exception );
		//	throw $exception;
		} catch(\Error $error) {
			$this->throwError($error);
		//	throw $error;
		}
	}
	
	public function throwError( $errorHandle ) {
		$this->log->throwable( $errorHandle );
		//$this->log->error( \darkphp\error\DwException::exceptionToString( $errorHandle ) );
		$dwHttpResponseError = new \bakuba\kernel\services\protocole\http\DwHttpResponse(
			$this->getDwHttpRequest()
		);
		$dwHttpResponseError->setHeader( "Content-type","text/plain; charset=utf-8" );
		$dwHttpResponseError->setResponseCode( 500 );
		if ( \method_exists( $this->log, "getFullPath") )
			$dwHttpResponseError->response( "DwApplicationSessionControllerConnexion ERROR, see:\r\n".$this->log->getFullPath()."\r\n" );//.\bakuba\kernel\services\protocole\http\DwHttpException::throwableToHttpMessage( $errorHandle ) );
		else
			$dwHttpResponseError->response( \bakuba\kernel\services\protocole\http\DwHttpException::throwableToHttpMessage( $errorHandle ) );
		$this->fullResponse( $dwHttpResponseError );
	}
	public function requestGetController() : ?\bakuba\kernel\application\controller\aDwController {
		//global $dwKernel;
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		$dwControllerBin = null;
		$dwHttpResponseError = null;
		try {
			$dwHttpServiceRequest = $this->getDwHttpServiceRequest();
			if ( is_null( $dwHttpServiceRequest ) ) {
				$filesRequest = $this->dwApplicationRequest->getStaticFilesRequest();
				$this->log->trace( $filesRequest );
			} else {
				$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
				
				$paramClassName = new \darkphp\web\DwWebValue( "className" );
				$paramClassName->refresh( $dwHttpRequest->getMergeValues() );
				$className = $paramClassName->getValue();
				if ( empty( $className ) )
					$className="Welcome";
				else
					$className = ucfirst( str_replace(array("/", "."), "", $className) );
				
				$debugKey="{$this->getClassName()}/Ctrl:{$className}";
				$this->log->trace( "{$this->parent->getUrlApp()} [{$debugKey}]" );
				
				$strFullQualifiedNameClass =
					"{$this->parent->getUrlApp()}\\controller\\Ctrl{$className}";
				
				if ( ! \bakuba\kernel\autoload\DwAutoLoad::classExist( $strFullQualifiedNameClass ) )
					$strFullQualifiedNameClass = "bakuba\\controller\\Ctrl$className";
				
				list($sec, $usec) = explode('.', microtime(true));
				//$dwKernel->setLogTime( date('Ymd/His-', $sec) . $usec );
				//$dwKernel->setLogCtrl( $className );
				
				try {
					$dwControllerBin = $this->getController(
						$className
						,$strFullQualifiedNameClass
						,$this
					);
				} catch(\Exception $exception_handle_createController) {
					$this->throwError( $exception_handle_createController );
					$this->log->throwable($exception_handle_createController);
					//throw $exception_handle_createController;
				} catch(\Error $error_handle_createController) {
					$this->throwError( $error_handle_createController );
					$this->log->throwable($error_handle_createController);
					//throw $error_handle_createController;
				}
				
			}
		} catch(\Exception $exception) {
			$this->throwError( $exception );
			$this->log->error("[{$debugKey}] exception");
			$this->log->throwable($exception);
			throw $exception;
		} catch(\Error $error) {
			$this->throwError( $error );
			$this->log->error("[{$debugKey}] error");
			$this->log->throwable($error);
			throw $error;
		}
		return $dwControllerBin;
	}
	
	private $listContrler = array();
	public function getController(
		string $className
		,string $strFullQualifiedNameClass
	) : \bakuba\kernel\application\controller\aDwController {
		$debugKey="{$this->getClassName()}/{$strFullQualifiedNameClass}";
		$dwControllerBin = null;
		if ( isset( $this->listContrler[ $strFullQualifiedNameClass ] ) ) {
			$this->log->info("[{$debugKey}] load");
			$dwControllerBin = $this->listContrler[ $strFullQualifiedNameClass ];
		} else {
			$this->listContrler[ $strFullQualifiedNameClass ]
				= \bakuba\kernel\application\controller\aDwController::createController( 
				$strFullQualifiedNameClass 
				,$className
				,$this->parent
			);
			//$this->insert( $this->listContrler[ $strFullQualifiedNameClass ] );
			$this->log->info("[{$debugKey}] created");
			$dwControllerBin = $this->listContrler[ $strFullQualifiedNameClass ];
		}
		return $dwControllerBin;
	}
	/*
	public function connectApp(
		\bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\aDwApplicationConnexion {
		return new self( $this ,$dwIpcMsg ,$dwApplicationRequest );
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

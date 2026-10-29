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
//use bakuba\kernel\application\service\http\controller\aDwApplicationController as aDwApplicationController;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\services\http\DwHttpServiceRequest as DwHttpServiceRequest;

use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\html\controller\DwControllerIpc as DwControllerIpc;

//use bakuba\kernel\application\controller\aDwController as aDwController;
use bakuba\kernel\dwCrypto\DwShellCryptController as DwShellCryptController;

use bakuba\kernel\application\session\DwSessionRequest as DwSessionRequest;
use bakuba\kernel\application\session\DwSessionResponse as DwSessionResponse;

use bakuba\kernel\application\session\cryptoShell\DwCryptoShellChannel as DwCryptoShellChannel;
use bakuba\kernel\application\session\httpAction\DwHttpActionChannel as DwHttpActionChannel;

use bakuba\kernel\process\IDwProcessIpc as IDwProcessIpc;

use bakuba\id\application\DwAuthenticationResponseInit as DwAuthenticationResponseInit;
use bakuba\id\application\DwAuthenticationRequestInit as DwAuthenticationRequestInit;

use bakuba\root\application\DwContratRequestInit as DwContratRequestInit;
use bakuba\root\application\DwContratResponseInit as DwContratResponseInit;

use bakuba\kernel\application\service\http\DwApplicationRequest as DwApplicationRequest;

use bakuba\kernel\services\protocole\http\DwHttpResponse as DwHttpResponse;
use bakuba\kernel\services\DwServiceConnexion as DwServiceConnexion;

use bakuba\kernel\agartha\DwBakubaRequest as DwBakubaRequest;
use bakuba\kernel\agartha\DwBakubaResponse as DwBakubaResponse;

use bakuba\kernel\services\http\DwHttpdRequest as DwHttpdRequest;
use bakuba\kernel\services\http\DwHttpdOrder as DwHttpdOrder;

abstract class aDwApplicationController 
	extends \bakuba\kernel\application\service\http\aDwApplication implements iDwApplicationController {
	private $controllerManager = null;
	public function __construct(
		\bakuba\kernel\application\service\aDwApplication $aDwApplication
		,string $urlApp
		,string $domain
	) {
		parent::__construct( $aDwApplication ,$urlApp ,$domain );
		$this->controllerManager = new DwControllerManager( $this );
		//$this->log->trace("[{$this->getClassName()}/{$this->getInstanceKey()}]");
	}
	///*
	/*
	protected function init( ) : void {
		parent::init();
		$this->ipcRegisterHandle(
			DwApplicationRequest::getStaticClassName()
			,new \bakuba\kernel\objects\DwCallBack( array( &$this ,'handleApplicationServiceHttpRequest' ) )
		);
	}
	//*/
	public function handleApplicationServiceHttpRequest(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) : void {
		
		$aDwApplicationConnexion =  $this->httpServiceConnect( $dwProcessConnexion ,$dwApplicationRequest );
		//$aDwApplicationConnexion->httpServiceRequest( $dwApplicationRequest->getDwHttpServiceRequest() );
		
		try {
			$aDwApplicationConnexion->httpServiceRequest( $dwApplicationRequest->getDwHttpServiceRequest() );
		} catch(\Exception $exception) {
			$aDwApplicationConnexion->throwError( $exception );
			//	throw $exception;
		} catch(\Error $error) {
			$aDwApplicationConnexion->throwError($error);
			//	throw $error;
		}
	}
	public function httpServiceConnect(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\service\http\aDwApplicationConnexion {
		return new DwApplicationControllerConnexion( $this ,$dwProcessConnexion ,$dwApplicationRequest );
	}
	//*/
	///*
	public function connectApp(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\aDwApplicationConnexion {
		return new DwApplicationControllerConnexion( $this ,$dwProcessConnexion ,$dwApplicationRequest );
	}
	//*/
	protected $controllerDir="\\controller";
	public function getControllerFQCN( string $ctrlName ) : ?string {
		$strFullQualifiedNameClass = "{$this->getUrlApp()}".( is_string ( $this->controllerDir ) ? $this->controllerDir : "" )."\\Ctrl{$ctrlName}";
		$this->log->trace( $strFullQualifiedNameClass );
		if ( ! \bakuba\kernel\autoload\DwAutoLoad::classExist( $strFullQualifiedNameClass ) )
			if ( \bakuba\kernel\autoload\DwAutoLoad::classExist( "bakuba\\controller\\Ctrl$ctrlName" ) )
				$strFullQualifiedNameClass = "bakuba\\controller\\Ctrl$ctrlName";
			else
				$strFullQualifiedNameClass = null;
		return $strFullQualifiedNameClass;	
	}
	public function getController( string $ctrlName ) : ?aDwController {
		$fqcn = $this->getControllerFQCN( $ctrlName );
		return is_null( $fqcn ) ? null : $this->controllerManager->getController( $ctrlName ,$fqcn );
	}
	public function requestGetController( \bakuba\kernel\services\http\DwHttpServiceRequest $dwHttpServiceRequest ) : ?aDwController {
		$dwController = null;
		$dwHttpResponseError = null;
		
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		/*
		$this->log->trace( print_r( array( 
			"server" => $dwHttpRequest->getServer()
			,"get" => $dwHttpRequest->getGet()
			,"post" => $dwHttpRequest->getPost()
		), true ) );
		//$this->log->info( $dwHttpRequest->getMergeValues() );
		//*/
		
		$paramClassName = new \darkphp\web\DwWebValue( "ctrlName" );
		$paramClassName->refresh( $dwHttpRequest->getMergeValues() );
		$ctrlName = ucfirst( $paramClassName->getValue() );
		
		$this->log->trace( "{$this->getUrlApp()} Ctrl:{$ctrlName}" );
		return $this->getController( $ctrlName );
	}
	/*
	private function executeRequest( aDwController $dwControllerBin , \bakuba\kernel\application\aDwApplicationConnexion $dwApplicationConnexion = null, DwControllerConnexion $dwControllerConnexion = null ) : void {
		global $dwKernel;
		$dwControllerBin
		if ( is_null( $dwControllerConnexion ) )
				$this->executeController( $dwControllerConnexion );
		//$this->ipcResponse( $dwApplicationRequest->getCallBackMessageQueue(), $ctrlObj );
		}
	}
	//*/
	/*
	public function askWebUrlStatic(
		DwControllerIpc $dwControllerCall
		,DwCallBack $dwCallBack
		,DwSessionRequest $dwSessionRequest
	) : void {
		//$this->log->trace($dwSessionRequest);
		$dwControllerCall->ipcSend( $this, $dwCallBack, $dwSessionRequest );
		$this->log->trace( "[{$this->getClassName()}/{$this->getInstanceKey()}]" );
	}
	public function askWebUrlAction(
		DwControllerIpc $dwControllerCall
		,DwCallBack $dwCallBack
		,DwSessionRequest $dwSessionRequest
	) : void {
		$dwControllerCall->ipcSend( $this, $dwCallBack, $dwSessionRequest );
		$this->log->trace( "[{$this->getClassName()}/{$this->getInstanceKey()}]" );
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

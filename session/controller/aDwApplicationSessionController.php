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

namespace bakuba\kernel\session\controller;
//use bakuba\kernel\session\controller\aDwApplicationSessionController as aDwApplicationSessionController;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\io\ipc\DwIpcMsg as DwIpcMsg;

//use bakuba\kernel\services\protocole\http\DwHttpServiceRequest as DwHttpServiceRequest;

use bakuba\kernel\application\DwApplicationRequest as DwApplicationRequest;

use bakuba\kernel\session\DwSessionRequest as DwSessionRequest;
use bakuba\kernel\session\DwSessionResponse as DwSessionResponse;

abstract class aDwApplicationSessionController extends \bakuba\kernel\session\application\aDwApplicationSessionManager {
	
	public function createApplicationSession( DwSessionIpKey $dwSessionIpKey ) : self;
	
	//*/
	public function getController( string $className ,string $strFullQualifiedNameClass ) : aDwController {
		throw new \Exception( "should occure in session process.");
	}
	/*
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$strFolderSession = \str_replace( array( '.' ,':' ) ,'_' ,$this->dwSessionIpKey->getIpKey() );
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,$strFolderSession
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	//*/
	protected $ableToFork = false;
	
	/*
	public function __construct(
		\bakuba\kernel\session\application\aDwApplicationSessionManager $aDwApplicationSessionManager
		,\bakuba\kernel\session\DwSessionIpKey $dwSessionIpKey
	) {
		parent::__construct( 
			$aDwApplicationSessionManager
			,$dwSessionIpKey
		);
		$this->log->trace("[{$this->getClassName()}/{$this->getInstanceKey()}]");
	}
	//*/
	/*
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize("domain");
		$this->addtoSerialize("urlApp");
		$this->addtoSerialize("parentProcess");
	}
	/*
	private $dirLog = null;
	protected $dwSessionManager = null;
	
	//*/
	/*
	protected function init( ): void {
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		$this->log->trace("[{$debugKey}]");
		
		parent::init( );
		
	}
	//*/
	///*
	public function connectApp(
		\bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg
		, DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\aDwApplicationConnexion {
		return new DwApplicationSessionControllerConnexion(
			$this
			,$dwIpcMsg
			,$dwApplicationRequest
		);
	}
	//*/
	protected function controllerConnect(
		DwIpcMsg $dwIpcMsg
		,DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\controller\DwControllerConnexion {
		$dwApplicationSessionConnexion = $this->connectApp( $dwIpcMsg ,$dwApplicationRequest );
		return new \bakuba\kernel\session\controller\DwControllerSessionConnexion(
			$dwApplicationSessionConnexion
			,$dwApplicationSessionConnexion->requestGetController( $this->getUrlApp() )
		);
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

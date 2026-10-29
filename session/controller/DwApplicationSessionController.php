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
//use bakuba\kernel\session\controller\DwApplicationSessionController as DwApplicationSessionController;

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

class DwApplicationSessionController extends \bakuba\kernel\session\application\aDwApplicationSession implements iDwApplicationController {
	
	public function getController( string $className ,string $strFullQualifiedNameClass ) : aDwController {
		return $this->controllerManager->getController( $className ,$strFullQualifiedNameClass );
	}
	
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$strFolderSession = \str_replace( array( '.' ,':' ) ,'_' ,$this->getDwSessionIpKey()->getIpKey() );
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
	
	protected $ableToFork = false;
	
	public function getUrlApp() {
		return $this->getParentProcess()->getUrlApp();
	}
	public function getDomain() {
		return $this->getParentProcess()->getDomain();
	}
	public function getDataBaseProcess(
	) : \bakuba\kernel\database\DwDatabaseProcess {
		return $this->getParentProcess()->getDataBaseProcess();
	}
	public function getDwSessionManager(
	) : \bakuba\kernel\session\DwSessionManager {
		return $this->getParentProcess()->getDwSessionManager();
	}
	private $dwSessionIpKey = null;
	public function getDwSessionIpKey() {
		return $this->dwSessionIpKey;
	}
	public function __construct(
		aDwApplicationSessionManager $aDwApplicationSessionManager
		,\bakuba\kernel\session\DwSessionIpKey $dwSessionIpKey
	) {
		$this->dwSessionIpKey=$dwSessionIpKey;
		parent::__construct( 
			$aDwApplicationSessionManager
		);
		$this->log->trace("[{$this->getClassName()}/{$this->getInstanceKey()}]");
	}
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
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
	protected function init( ): void {
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		$this->log->trace("[{$debugKey}]");
		
		parent::init( );
		//$this->setProcessTitle( "php-".$this->dwSessionIpKey->getIpKey() );
		///*
		$this->ipcRegisterHandle(
			DwApplicationRequest::getStaticClassName()
			,new DwCallBack( array( &$this ,'handleApplicationRequest' ) )
		);
		//*/
		$this->ipcRegisterHandle(
			DwSessionRequest::getStaticClassName()
			,new DwCallBack( array( &$this ,'handleSessionRequest' ) )
		);
	}
	public function handleSessionRequest(
		DwIpcMsg $dwIpcMsg
		,DwSessionRequest $dwSessionRequest
	) {
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		$this->log->trace("[{$debugKey}]");
		// no:DwApplication::messagingHandle hop:DwProcess
		$this->forwardAndStartIfNotStarted(
			$dwSessionRequest
			,$dwIpcMsg
		);
	}
	/*
	public function handleApplicationRequest(
		DwIpcMsg $dwIpcMsg
		,DwApplicationRequest $dwApplicationRequest
	) : void {
		$applicationConnexion = $this->connectApp( $dwIpcMsg, $dwApplicationRequest );
		$applicationConnexion->runApp( $this );
		$this->log->trace("[{$this->getClassName()}/{$this->getInstanceKey()}]");
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

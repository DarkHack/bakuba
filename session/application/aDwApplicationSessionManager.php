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

namespace bakuba\kernel\session\application;
//use bakuba\kernel\session\application\aDwApplicationSessionManager as aDwApplicationSessionManager;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\io\ipc\DwIpcMsg as DwIpcMsg;
use bakuba\kernel\application\DwApplicationRequest as DwApplicationRequest;

use bakuba\kernel\agartha\DwBakubaRequest as DwBakubaRequest;

use bakuba\kernel\services\protocole\http\DwHttpServiceRequest as DwHttpServiceRequest;

use bakuba\kernel\session\DwSessionRequest as DwSessionRequest;
//use bakuba\kernel\session\DwSessionResponse as DwSessionResponse;
use bakuba\kernel\session\DwSessionIpKey as DwSessionIpKey;

use bakuba\kernel\services\DwServiceRequest as DwServiceRequest;

abstract class aDwApplicationSessionManager extends \bakuba\kernel\application\service\aDwApplication implements iDwApplicationSession {
	
	protected function __construct( \bakuba\kernel\application\aDwApplication $parent ,string $urlApp ) {
		parent::__construct( $parent ,$urlApp );
		$this->log->trace("[{$this->toString()}]");
	}
	protected $dwSessionManager = null;
	protected function init( ): void {
		$this->ipcRegisterHandle(
			DwApplicationRequest::getStaticClassName()
			,new DwCallBack( array( &$this ,'handleApplicationRequest' ) )
		);
		$this->ipcRegisterHandle(
			DwSessionRequest::getStaticClassName()
			,new DwCallBack( array( &$this ,'handleSessionRequest' ) )
		);
		
		if ( is_null( $this->dwSessionManager ) )
			$this->dwSessionManager
				= \bakuba\kernel\session\DwSessionManager::singleton( $this );
		//DwApplicationDatabase::init();
		parent::init();
		
		$this->log->trace("[{$this->getClassName()}/{$this->getInstanceKey()}]");
	}
	/*
	public function getAppFromSession( DwServiceRequest $dwServiceRequest ) : ?aDwApplicationSession {
		$aDwApplicationSession = null;
		$dwSessionIpKey = $dwServiceRequest->getDwSessionIpKey();
		$ipKey = $dwSessionIpKey->getIpKey();
		if ( isset( $this->runningApplicationSessionProcess[$ipKey] ) )
			$aDwApplicationSession = $this->runningApplicationSessionProcess[$ipKey];
		return $aDwApplicationSession;
	}
	protected function forwardAndStartIfNotStarted(
		DwServiceRequest $dwServiceRequest
		,DwIpcMsg $dwIpcMsg
	) {
		if ( ( $aDwApplicationSession = $this->getAppFromSession( $dwServiceRequest ) ) === null ) {
			$dwSessionIpKey = $dwServiceRequest->getDwSessionIpKey();
			$ipKey = $dwSessionIpKey->getIpKey();
			if ( isset( $this->startingApplicationSessionProcess[ $ipKey ] ) ) {
				$aDwApplicationSession
					= $this->startingApplicationSessionProcess[ $ipKey ];
				$aDwApplicationSession->onAlive( new DwCallBack(
					array( &$this ,"forward" )
					,$aDwApplicationSession
					,$dwIpcMsg
				) );
			} else {
				//
				$aDwApplicationSession = $this->createApplicationSession(
					$dwServiceRequest->getDwSessionIpKey()
				);
				$aDwApplicationSession->onAlive(
					new DwCallBack(
						array( &$this ,"applicationSessionStarted" )
						,$aDwApplicationSession
						,$dwIpcMsg
					)
				);
			}
		} else
			$this->forward( $aDwApplicationSession ,$dwIpcMsg );
		
	}
	//*/
	/*
	public function handleApplicationRequest(
		DwIpcMsg $dwIpcMsg
		,DwApplicationRequest $dwApplicationRequest
	) : void {
		$this->log->trace( $dwApplicationRequest );
		$this->forwardAndStartIfNotStarted(
			$dwApplicationRequest->getDwHttpServiceRequest()
			,$dwIpcMsg
		);
	}
	//*/
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
	
	private $startingApplicationSessionProcess = array();
	private $runningApplicationSessionProcess = array();
	protected function getRunningSession() : array {
		return $this->runningApplicationSessionProcess;
	}
	///*
	public function createApplicationSession(
		\bakuba\kernel\session\DwSessionIpKey $dwSessionIpKey
	) : aDwApplicationSession  {
		return $this->createCustomeApplicationSession(
			new aDwApplicationSession( $this, $dwSessionIpKey )
			,$dwSessionIpKey
		);
	}
	//*/
	public function createCustomeApplicationSession(
		aDwApplicationSession $aDwApplicationSession
		,DwSessionIpKey $dwSessionIpKey
	) : aDwApplicationSession {
		$this->startApplicationSession( $dwSessionIpKey, $aDwApplicationSession );
		// 1200 s => 60 x 20 => out in 20mn
		$aDwApplicationSession->setTimeout(1200);
		
		return $aDwApplicationSession;
	}
	protected function startApplicationSession(
		DwSessionIpKey $dwSessionIpKey
		,aDwApplicationSession $aDwApplicationSession
	)  : void {
		$this->addDataBaseProcess( $aDwApplicationSession );
		$this->startingApplicationSessionProcess[ $dwSessionIpKey->getIpKey() ]
			= $aDwApplicationSession;
		
		$this->createProcess( $aDwApplicationSession );	
	}
	public function addSession( aDwApplicationSession $aDwApplicationSession ) : void {
		$dwSessionIpKey = $aDwApplicationSession->getDwSessionIpKey();
		
		$ipKey = $dwSessionIpKey->getIpKey();
		/*
		if ( $this->isMyProcess() ) {
			$dwBakubaRequest = new DwBakubaRequest(
				"dwApplicationSession"
				,$aDwApplicationSession
			);
			$this->ipcSend(
				new DwCallBack(array( &$this->log ,"trace" ) )
				,$this->getParentProcess()->getDwServerProcess()
				,$dwBakubaRequest
			);
			unset( $this->startingApplicationSessionProcess[ $ipKey ] );
		}
		//*/
		if ( isset( $this->runningApplicationSessionProcess[$ipKey] ) )
			throw new \Exception( " ipkey is ever in array " );
		elseif ( isset( $this->startingApplicationSessionProcess[ $ipKey ] ) ) {
			$this->runningApplicationSessionProcess[$ipKey]
				= $this->startingApplicationSessionProcess[ $ipKey ];
			unset( $this->startingApplicationSessionProcess[ $ipKey ] );
		} else
			throw new \Exception( " application not in starting " );
		$this->log->info( "application session recorded." );
	}
	public function removeSession( aDwApplicationSession $aDwApplicationSession ) : void {
		$dwSessionIpKey = $aDwApplicationSession->getDwSessionIpKey();
		
		$ipKey = $dwSessionIpKey->getIpKey();
		if ( $this->isMyProcess() ) {
			$dwBakubaRequest = new DwBakubaRequest(
				"dwApplicationSessionClose"
				,$aDwApplicationSession
			);
			$this->ipcSend(
				new DwCallBack( array( &$this->log ,"trace" ) )
				,$this->getParentProcess()->getDwServerProcess()
				,$dwBakubaRequest
			);
		}
		if ( isset( $this->startingApplicationSessionProcess[ $ipKey ] ) )
			unset($this->startingApplicationSessionProcess[ $ipKey ]);
		if ( isset( $this->runningApplicationSessionProcess[ $ipKey ] ) )
			unset( $this->runningApplicationSessionProcess[ $ipKey ] );
	}
	public function applicationSessionStarted(
		aDwApplicationSession $aDwApplicationSession
		,\bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg
	) {
		$aDwApplicationSession->onClose(
			new DwCallBack( array( &$this ,"applicationSessionClosed" ) ,$aDwApplicationSession )
		);
		$this->forward( $aDwApplicationSession ,$dwIpcMsg );
		$this->addSession( $aDwApplicationSession );
	}
	public function applicationSessionClosed(
		aDwApplicationSession $aDwApplicationSession
	) : void {
		$this->removeSession( $aDwApplicationSession );
	}
	///*
	// need 'forward' cause "ipcForward" is protected, here is public
	public function forward(
		aDwApplicationSession $aDwApplicationSession
		,\bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg
	) {
		$this->ipcForward( $aDwApplicationSession ,$dwIpcMsg );
	}
	//*/
	
	public function connectApp(
		DwIpcMsg $dwIpcMsg
		,DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\aDwApplicationConnexion {
		throw new \Exception( "shouldn't occure in session manager process." );
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

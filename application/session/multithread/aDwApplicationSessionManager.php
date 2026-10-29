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
//use bakuba\kernel\application\session\aDwApplicationSessionManager as aDwApplicationSessionManager;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\io\ipc\DwIpcMsg as DwIpcMsg;
use bakuba\kernel\application\DwApplicationRequest as DwApplicationRequest;

use bakuba\kernel\session\DwSessionIpKey as DwSessionIpKey;

use bakuba\kernel\services\DwServiceRequest as DwServiceRequest;

abstract class aDwApplicationSessionManager
	extends \bakuba\kernel\application\aDwApplication
	implements iDwApplicationSessionManager {
	
	/*
	protected function __construct( \bakuba\kernel\application\aDwApplication $parent ,string $urlApp ) {
		parent::__construct( $parent ,$urlApp );
		$this->log->trace("[{$this->toString()}]");
	}
	//*/
	/*
	protected function init( ): void {
		//\bakuba\kernel\session\application\aDwApplicationSessionManager::init();
		parent::init();
		$this->log->trace("[{$this->toString()}]");
	}
	//*/
	public function getAppFromSession(
		DwServiceRequest $dwServiceRequest
	) : ?\bakuba\kernel\session\application\aDwApplicationSession {
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
	) : void {
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
	/*
	public function createApplicationSession(
		\bakuba\kernel\session\DwSessionIpKey $dwSessionIpKey
	) : aDwApplicationSession  {
		return $this->createCustomeApplicationSession(
			new aDwApplicationSession( $this, $dwSessionIpKey )
			,$dwSessionIpKey
		);
	}
	//*/
	/*
	public function createCustomeApplicationSession(
		aDwApplicationSession $aDwApplicationSession
		,DwSessionIpKey $dwSessionIpKey
	) : aDwApplicationSession {
		$this->startApplicationSession( $dwSessionIpKey, $aDwApplicationSession );
		// 1200 s => 60 x 20 => out in 20mn
		$aDwApplicationSession->setTimeout(1200);
		
		return $aDwApplicationSession;
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

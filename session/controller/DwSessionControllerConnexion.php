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
//use bakuba\kernel\session\controller\DwSessionControllerConnexion as DwSessionControllerConnexion;

use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\application\service\http\controller\DwControllerConnexion as DwControllerConnexion;
//use bakuba\kernel\services\http\DwHttpServiceRequest as DwHttpServiceRequest;

use bakuba\kernel\session\DwSessionRequest as DwSessionRequest;
use bakuba\kernel\session\DwSessionResponse as DwSessionResponse;
use bakuba\kernel\session\DwSessionChannel as DwSessionChannel;

use bakuba\kernel\session\DwSessionManager as DwSessionManager;

class DwSessionControllerConnexion extends \darkphp\DwObject {
	
	public static function connection(
		DwControllerConnexion $dwControllerConnexion
		,\bakuba\kernel\objects\DwCallBack $dwCallBack
		,string $classNameDwSessionChannel
		,\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$debugKey=self::getStaticClassName();
		$iDwLogger->trace( $debugKey );
		$dwSessionControllerConnexion = null;
		
		$dwApplicationConnexion = $dwControllerConnexion->getDwApplicationConnexion();
		$dwSessionManager = DwSessionManager::singleton( $dwControllerConnexion->getiDwController() );
		//$dwSessionManager = $dwApplicationConnexion->getDwSessionManager();
		/*
		$dwSessionRequest = new DwSessionRequest(
			$dwControllerConnexion->getDwSessionIpKey()
			,"SessionStart"
		);
		$dwHttpServiceRequest = $dwControllerConnexion->getDwHttpServiceRequest();
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		$dwSessionRequest->setObject( $dwHttpRequest->getMergeValues() );
		//*/
		$iDwLogger->trace( "Session start" );
		$dwSessionManager->SessionStart(
			$dwControllerConnexion->getDwHttpServiceRequest()
			,new \bakuba\kernel\objects\DwCallBack(
				array( self::getStaticClassName(), "takeinfly" )
				,$dwSessionManager
				,$dwControllerConnexion
				,$dwCallBack
			)
			,$classNameDwSessionChannel
		);
		/*
		$dwSessionControllerConnexion = self::(
			$dwControllerConnexion
			,
		);
		*/
		//return $dwSessionControllerConnexion;
	}
	public static function takeinfly(
		DwSessionManager $dwSessionManager
		,DwControllerConnexion $dwControllerConnexion
		,\bakuba\kernel\objects\DwCallBack $dwCallBack
		,DwSessionResponse $dwSessionResponse 
	) : void {
		$debugKey=self::getStaticClassName();
		$dwSessionChannel=$dwSessionResponse->getObject();
		
		$dwSessionControllerConnexion = new self(
			$dwSessionManager
			,$dwControllerConnexion
			,$dwSessionChannel
		);
		
		$dwCallBack->callback( $dwSessionControllerConnexion );
	}
	
	public function getDwControllerConnexion() : DwControllerConnexion {
		return $this->parent;
	}
	
	private $dwSessionChannel = null;
	public function getDwSessionChannel() : ?DwSessionChannel {
		return $this->dwSessionChannel;
	}
	private $dwSessionManager = null;
	public function getDwSessionManager() : DwSessionManager {
		return $this->dwSessionManager;
	}
	
	private function __construct(
		DwSessionManager $dwSessionManager
		,DwControllerConnexion $dwControllerConnexion
		,DwSessionChannel $dwSessionChannel = null
	) {
		$this->dwSessionManager=$dwSessionManager;
		$this->dwSessionChannel=$dwSessionChannel;
		parent::__construct( $dwControllerConnexion );
	}
	public function sessionWrite( \bakuba\kernel\objects\DwCallBack $dwCallBack ) : bool {
		$debugKey="{$this->getStaticClassName()}/{$this->getInstanceKey()}";
		$return = false;
		
		$dwSessionManager = $this->getDwSessionManager();
		$dwSessionRequest = new DwSessionRequest(
			$this->getDwSessionChannel()->getChannelKey()
			, "SessionWrite"
		);
		$dwSessionRequest->setObject( $this->dwSessionChannel );
		$dwSessionResponse = $dwSessionManager->SessionWrite(
			$dwSessionRequest
		);
		//$this->sessionWrited( $dwSessionResponse );
		$dwCallBack->callback( $dwSessionResponse );
		
		return $return;
	}
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

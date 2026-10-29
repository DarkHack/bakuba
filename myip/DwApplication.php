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

namespace bakuba\kernel\myip;
//use bakuba\kernel\myip\DwApplication as DwApplication;

class DwApplication extends \bakuba\kernel\application\service\http\controller\aDwApplicationController {
	private $arrRequest = array();
	
	public function init( ) : void {
		$this->addDomain( "ip4.darkweb.fr" );
		$this->addDomain( "ip6.darkweb.fr" );
		parent::init();
		
		$this->ipcRegisterHandle(
			DwIpRequest::getStaticClassName()
			,new \bakuba\kernel\objects\DwCallBack(
				array( &$this, 'handleIpRequest' )
			)
		);
	}
	
	protected $controllerDir=null;
	public function requestGetController(
		\bakuba\kernel\services\http\DwHttpServiceRequest $dwHttpServiceRequest
	) : ?\bakuba\kernel\application\service\http\controller\aDwController {
		return $this->getController( "Welcome" );
	}
	public function handleIpRequest(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwIpRequest $dwIpRequest
	) : void {
		$dwIpcMsg = $dwProcessConnexion->getDwIpcMsg();
		$dwIpRequest->setDwIpcKey( $dwIpcMsg->getSrcKey() );
		$requestKey = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( $this->arrRequest );
		$this->arrRequest[ $requestKey ] = $dwIpRequest;
		$dwProcessConnexion->ipcResponse( new DwIpResponse( $requestKey ) );
	}
	public function getIpRequest( string $requestKey ) : ?DwIpRequest {
		return isset($this->arrRequest[ $requestKey ]) ?
			$this->arrRequest[ $requestKey ] : null;
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

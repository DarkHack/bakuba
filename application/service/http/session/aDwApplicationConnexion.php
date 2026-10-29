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

namespace bakuba\kernel\application\service\http\session;
//use bakuba\kernel\application\service\http\session\aDwApplicationConnexion as aDwApplicationConnexion;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

abstract class aDwApplicationConnexion
	 extends \bakuba\kernel\application\service\session\aDwApplicationConnexion implements \bakuba\kernel\application\iDwApplicationConnexion {
	public function getServiceHttpDwApplicationSession() : aDwApplicationSession {
		return $this->getServiceDwApplicationSession();
	}
	
	public function __construct(
		aDwApplicationSession $aDwApplicationSession
		,\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) {
		parent::__construct( $aDwApplicationSession ,$dwProcessConnexion ,$dwApplicationRequest );
		//$this->log->trace(".");
	}
	public function connectSession() : void {
		$dwSessionManager = $this->getDwApplicationSession()->getDwSessionManager();
		$dwSessionManager->connect( $this , new DwCallBack( array( &$this ,'sessionConnected' ) ) );
	}
	private $dwSessionChannel = null;
	public function getDwSessionChannel( ) : DwSessionChannel {
		return $this->dwSessionChannel;
	}
	public function sessionConnected( DwSessionChannel $dwSessionChannel ) : void {
		$this->dwSessionChannel = $dwSessionChannel;
		$this->appResponse( $this->connect() );
	}
	//public function connect() : \bakuba\kernel\application\DwApplicationResponse;
	
	private function appResponse( \bakuba\kernel\application\DwApplicationResponse $dwApplicationResponse ) : void{
		$this->ipcResponse( $dwApplicationResponse );
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getSessionClassName() : string {
		return DwSessionChannel::getStaticClassName();
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

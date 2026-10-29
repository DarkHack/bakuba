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

namespace bakuba\kernel\mystate\controller;
//use bakuba\kernel\mystate\controller\CtrlProcess as CtrlProcess;

class CtrlProcess extends \bakuba\kernel\application\service\http\controller\aDwControllerIpc {
	
	private $valRequest = null;
	public function __construct( \bakuba\kernel\application\aDwApplication $aDwApplication ) {
		parent::__construct( $aDwApplication );
			
		$this->valRequest = new \darkphp\web\DwWebValue( "request" );
		//$this->valRequest->setValue( "anIdRequest" );
		
		$this->addPublicValue( $this->valRequest );
		$this->log->trace( "." );
	}
	public function execute(
		\bakuba\kernel\application\service\http\controller\DwControllerConnexion $dwControllerConnexion
	) : void {
		$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		$this->valRequest->setValue( null );
		parent::execute( $dwControllerConnexion );

		$dwApplicationConnexion = $dwControllerConnexion->getDwApplicationConnexion();
		$dwProcessMessaging = $dwApplicationConnexion->getDwApplication();
		
		$dwBakubaRequest = new \bakuba\kernel\agartha\DwBakubaRequest( "stateProcess" );
		
		$this->ipcSend(
			$dwControllerConnexion
			,$dwProcessMessaging->parent
			,new \bakuba\kernel\objects\DwCallBack( array( &$this ,'cbMsg' ) ,$dwControllerConnexion )
			,$dwBakubaRequest
		);
	}
	public function cbMsg(
		\bakuba\kernel\application\service\http\controller\DwControllerConnexion $dwControllerConnexion
		,$dwBakubaResponse
	) : void {
		$this->response( $dwControllerConnexion,
			"<html><body>"
			."<h1>State process</h1>"
			."<pre>".\darkphp\DwObject::print_r( $this )."</pre>"
			."<pre>".(
				is_a( $dwBakubaResponse ,\bakuba\kernel\agartha\DwBakubaResponse::class)
				? print_r( \unserialize( $dwBakubaResponse->getMessage() ) ,true )
				: print_r( $dwBakubaResponse ,true )
			)."</pre>"
			."</body></html>"
		);
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
		parent::__destruct();
	}
	//*/
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}


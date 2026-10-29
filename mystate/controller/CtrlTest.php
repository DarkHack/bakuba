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
//use bakuba\kernel\mystate\controller\CtrlWelcome as CtrlWelcome;

use bakuba\kernel\application\service\http\controller\DwControllerConnexion as DwControllerConnexion;

use darkphp\error\DwException as DwException;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

class CtrlTest extends \bakuba\kernel\application\service\http\controller\aDwControllerIpc {
	
	private $valRequest = null;
	public function __construct( \bakuba\kernel\application\aDwApplication $aDwApplication ) {
		parent::__construct( $aDwApplication );
			
		$this->valRequest = new \darkphp\web\DwWebValue( "request" );
		//$this->valRequest->setValue( "anIdRequest" );
		
		$this->addPublicValue( $this->valRequest );
		$this->log->trace( "." );
	}
	public function execTest( DwControllerConnexion $dwControllerConnexion ) : void {
		$dwHttpServiceRequest = $dwControllerConnexion->getDwHttpServiceRequest();
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		$cltIp="".$dwHttpRequest->getCltIp()->getIpAddress();
		
		$dwApplicationConnexion = $dwControllerConnexion->getDwApplicationConnexion();
		$aDwApplication = $dwApplicationConnexion->getDwApplication();
		$phpCode=$aDwApplication->getFile("/page/pageWelcome.php");
		ob_start();
		try {
			include( "data://text/plain,".\urlencode( $phpCode ) );
		} catch( \Exception $anException ) {
			echo DwException::exceptionToString( $anException );
		} catch( \Error $anError ) {
			echo DwException::exceptionToString( $anError );
		}
		$data=ob_get_clean();            // récupère le HTML généré
		
		//$this->log->info( $data );
		$this->response( $dwControllerConnexion, $data );
	}
	
	
	public function execIpcTest( DwControllerConnexion $dwControllerConnexion ) : void {
		global $dwBakuba;
		$this->ipcSend(
			$dwControllerConnexion
			,$dwBakuba->getDwDnsResolverProcess()
			,new \bakuba\kernel\objects\DwCallBack( array( &$this ,'execIpcTestCallBack' ) ,$dwControllerConnexion )
			,new \bakuba\kernel\network\dnsResolver\DwDnsResolverRequest( $dwDnsQuery )
		);
	}
	public function execIpcTestCallBack(
		\bakuba\kernel\application\service\http\controller\DwControllerConnexion $dwControllerConnexion
		,$dwDnsResolverResponse = null
	) : void {
		$this->response( $dwControllerConnexion,
			"<html><body>"
			."<h1>DNS RESOLV /dwDnsResolverResponse</h1>"
			."<pre>".json_encode( $dwDnsResolverResponse )."</pre>"
//			."<pre>".(
//				is_a( $dwBakubaResponse ,\bakuba\kernel\agartha\DwBakubaResponse::class)
//				? print_r( \unserialize( $dwBakubaResponse->getMessage() ) ,true )
//				: print_r( $dwBakubaResponse ,true )
//			)."</pre>"
			."</body></html>"
		);
	}
	public function execDnsTest( DwControllerConnexion $dwControllerConnexion ) : void {
		$ctrlTest = $this;
		\bakuba\kernel\network\dnsResolver\DwDnsResolver::resolv(
			new DwCallBack( function ( $result ) use ( $ctrlTest ,$dwControllerConnexion ) {
				$ctrlTest->response( $dwControllerConnexion,
					"<html><body>"
					."<h1>DNS RESOLV by DwDnsResolver</h1>"
					."<pre>".print_r( $result ,true )."</pre>"
					."</body></html>"
				);
			} )
			,'darkweb.fr'
		);
	}
	public function execute(
		DwControllerConnexion $dwControllerConnexion
	) : void {
		$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		$this->valRequest->setValue( null );
		parent::execute( $dwControllerConnexion );
		$errorMsg=null;
		try {
			//$this->execTest( $dwControllerConnexion );
			//$this->execIpcTest( $dwControllerConnexion );
			$this->execDnsTest( $dwControllerConnexion );
			
		} catch( \Exception $anException ) {
			$errorMsg = DwException::exceptionToString( $anException );
		} catch( \Error $anError ) {
			$errorMsg = DwException::exceptionToString( $anError );
		}
		if ( is_string( $errorMsg ) )
			$this->response( $dwControllerConnexion, $errorMsg );
		
		$this->log->trace( "[{$debugKey}] " );
	}
	public function ipRequested( DwControllerConnexion $dwControllerConnexion ) : void {
		$this->response( $dwControllerConnexion, "/* request ip */" );
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


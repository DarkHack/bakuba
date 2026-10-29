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
//use bakuba\kernel\myip\CtrlWelcome as CtrlWelcome;

use bakuba\kernel\application\service\http\controller\DwControllerConnexion as DwControllerConnexion;

class CtrlWelcomeConnexion extends \bakuba\kernel\application\service\http\controller\DwControllerConnexion {
	
	public function execute() : void {
		parent::execute();
		
		$dwHttpServiceRequest = $this->getDwHttpServiceRequest();
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		$cltIp="".$dwHttpRequest->getCltIp()->getIpAddress();
		$this->log->trace( $cltIp );
		
		$dwApplicationConnexion = $this->getDwApplicationConnexion();
		$aDwApplication = $dwApplicationConnexion->getDwApplication();
		
		$ctrlName = $dwHttpRequest->getValue('ctrlName');
		/*
		$this->log->trace( print_r( array( 
			"server" => $dwHttpRequest->getServer()
			,"get" => $dwHttpRequest->getGet()
			,"post" => $dwHttpRequest->getPost()
		), true ) );
		//$this->log->info( $dwHttpRequest->getMergeValues() );
		//*/
		
		if ( $ctrlName == "Welcome" ) {
			switch( $this->getTypeValue() ) {
			case 'css' :
				$dataResponse="/* ip:{$cltIp} */";
				break;
			case 'json' :
				$dataResponse='{ "ip":"'.$cltIp.'" }';
				break;
			case 'js' :
				$dataResponse="var myip = { ip:{$cltIp} };";
				break;
			case 'html' :
				$dataResponse="<html><head><title>Mon IP</title></head><body>{$cltIp}</body></html>";
				break;
			default :
				$dataResponse=$cltIp;
				break;
			}
			$this->response( $dataResponse );
		} else {
			$requestId =  $ctrlName;
			$this->log->trace( "requestId:{$requestId}" );
			$dwIpRequest = $aDwApplication->getIpRequest( $requestId );
			if ( is_null( $dwIpRequest ) )
				$this->response( "bad requestId:{$requestId}" );
			else {
				$dwIpcMsg = new \bakuba\kernel\io\ipc\DwIpcMsg( $dwIpRequest->getDwIpcKey() );
				
				$dwIpcMsg->write( serialize( 
					new \bakuba\myip\application\DwIpRequested( $dwIpRequest ,$cltIp )
				 ) );
				 
				$this->messagingWrite(
					$dwIpcMsg
					, new \bakuba\kernel\objects\DwCallBack(
						array( &$this, "ipRequested" )
						,$dwIpRequest
					)
				);
			}
		}
	}
	public function ipRequested() : void {
		$this->response( "/* request ip */" );
	}
}
class CtrlWelcome extends \bakuba\kernel\application\service\http\controller\aDwController {
	
	public function connect(
		\bakuba\kernel\application\service\http\controller\DwApplicationControllerConnexion $dwApplicationControllerConnexion
	) : DwControllerConnexion {
		return new CtrlWelcomeConnexion( $dwApplicationControllerConnexion ,$this );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}


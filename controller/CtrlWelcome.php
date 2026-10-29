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

namespace bakuba\kernel\controller;
//use bakuba\kernel\controller\CtrlWelcome as CtrlWelcome;

class CtrlWelcomeConnexion extends \bakuba\kernel\application\service\http\controller\DwControllerConnexion {
	
	public function execute() : void {
		$dwHttpServiceRequest = $this->getDwHttpServiceRequest();
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		$this->response(
			"<html><body><center><h1>Salut Kernel bakuba.</h1></center><br/>Bienvenue<br/>"
			.$dwHttpRequest->getCltIp()->getIpAddress()."<br/>".date( 'l jS \of F Y h:i:s A' )
			."</body></html>"
		);
	}
}
class CtrlWelcome extends \bakuba\kernel\application\service\http\controller\aDwController{
	
	public function connect( \bakuba\kernel\application\service\http\controller\DwApplicationControllerConnexion $dwApplicationControllerConnexion ) : DwControllerConnexion {
		return new CtrlWelcomeConnexion( $dwApplicationControllerConnexion ,$this );
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

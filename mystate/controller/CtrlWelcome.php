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

class CtrlWelcomeConnexion extends \bakuba\kernel\application\service\http\controller\DwControllerConnexion {
	
	public function execute() : void {
		parent::execute();
		
		$dwHttpServiceRequest = $this->getDwHttpServiceRequest();
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		$cltIp="".$dwHttpRequest->getCltIp()->getIpAddress();
		
		$dwApplicationConnexion = $this->getDwApplicationConnexion();
		$aDwApplication = $dwApplicationConnexion->getDwApplication();
		
		$htmlPhpCode=$aDwApplication->getFile("/page/pageWelcome.php");
		ob_start();
		include( "data://text/plain,".\urlencode( $htmlPhpCode ) );
		$pageWelcome = ob_get_clean();            // récupère le HTML généré
		//$this->log->info( $pageWelcome );
		
		$this->response( $pageWelcome );
	}
	public function ipRequested() : void {
		$this->response( "/* request ip */" );
	}
}
class CtrlWelcome extends \bakuba\kernel\application\service\http\controller\aDwController {
	protected $cpt = 0;
	
	public function connect( \bakuba\kernel\application\service\http\controller\DwApplicationControllerConnexion $dwApplicationControllerConnexion ) : DwControllerConnexion {
		$this->log->trace( "cpt:".$this->cpt++ );
		return new CtrlWelcomeConnexion( $dwApplicationControllerConnexion ,$this );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}


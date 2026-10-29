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

namespace bakuba\kernel\application\service\http\controller\session;
//use bakuba\kernel\application\service\http\controller\session\aDwControllerSession as aDwControllerSession;

abstract class aDwControllerSession
	extends \bakuba\kernel\application\service\http\controller\aDwControllerIpc
	implements iDwControllerSession {
	
	public function execute(
		\bakuba\kernel\application\service\http\controller\DwControllerConnexion $dwControllerConnexion
	) : void {
		$this->log->trace( $dwControllerConnexion );
		//aDwController::execute( $dwControllerConnexion );
		parent::execute( $dwControllerConnexion );
		//$this->log->trace( "end execute - try DwSessionControllerConnexion::connection" );
		DwSessionControllerConnexion::connection(
			$dwControllerConnexion
			,new \bakuba\kernel\objects\DwCallBack( array( &$this ,'startSession') )
			,$this->getClassNameDwSessionChannel()
			,$this->log
		);
	}
	
	public function startSession(
		DwSessionControllerConnexion $dwSessionControllerConnexion
	) : void {
		$this->log->trace( "." );
		$this->executeSession( $dwSessionControllerConnexion );
	}
	
	public function sessionResponse(
		DwSessionControllerConnexion $dwSessionControllerConnexion
		,$data
	) : void {
		/*
		$this->response(
			$dwSessionControllerConnexion->getDwControllerConnexion()
			,$data
		);
		//*/
		$dwSessionControllerConnexion->sessionWrite(
			new \bakuba\kernel\objects\DwCallBack(
				array( &$this ,'sessionWrited')
				,$dwSessionControllerConnexion
				,$data
			)
		);
	}
	public function sessionWrited(
		DwSessionControllerConnexion $dwSessionControllerConnexion
		,$data
	) : void {
		$this->response( 
			$dwSessionControllerConnexion->getDwControllerConnexion()
			,$data
		);
	}
	/*
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

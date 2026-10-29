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

namespace bakuba\kernel\application\service\http\controller;
//use bakuba\kernel\application\service\http\controller\DwControllerManager as DwControllerManager;

class DwControllerManager extends \darkphp\DwObject {
	/*
	// singleton class pattern:
	private static $instance = null;

	public static function singleton( \bakuba\kernel\application\service\http\aDwApplication $aDwApplication ) : self {
		null === self::$instance and self::$instance = new self( $dwObject );
		return self::$instance;
	}
	// bakuba\kernel\application\DwApplicationManager::instance();
	public static function instance() : self {
		return self::$instance;
	}
	//*/
	public function __construct( iDwApplicationController $iDwApplicationController ) {
		parent::__construct( $iDwApplicationController );
	}
	private function getDwApplication() : iDwApplicationController {
		return $this->parent;
	}
	private $listContrler = array();
	public function getController(
		string $ctrlName
		,string $strFullQualifiedNameClass
	) : ?\bakuba\kernel\application\service\http\controller\aDwController {
		$dwController = null;
		if ( isset( $this->listContrler[ $strFullQualifiedNameClass ] ) ) {
			$this->log->info( "load" );
			$dwController = $this->listContrler[ $strFullQualifiedNameClass ];
		} else {
			$dwController = \bakuba\kernel\application\service\http\controller\aDwController::createController( 
				$strFullQualifiedNameClass 
				,$ctrlName
				,$this->getDwApplication()
			);
			$this->listContrler[ $strFullQualifiedNameClass ] = $dwController;
				
			//$this->insert( $this->listContrler[ $strFullQualifiedNameClass ] );
			if ( is_null( $dwController ) )
				$this->log->info( "echec on instanciate $strFullQualifiedNameClass" );
			else
				$this->log->info( "created" );
		}
		return $dwController;
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

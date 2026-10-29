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

namespace bakuba\kernel\application;
//use bakuba\kernel\application\DwApplicationManager as DwApplicationManager;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwApplicationManager extends DwObject {
	// singleton class pattern:
	protected static $instance = null;
	/*
	public static function singleton( aDwApplication $dwApplication ) : self {
		null === self::$instance and self::$instance = new self( $dwApplication );
		return self::$instance;
	}
	//*/
	// bakuba\kernel\application\DwApplicationManager::instance();
	public static function instance() : self {
		return self::$instance;
	}
	
	protected function __construct( aDwApplication $dwApplication ) {
		$this->applications = array();
		parent::__construct( $dwApplication );
	}
	protected $applications = null;
	private $urlAppDefined = array();
	
	public function getApplications() :array {
		return $this->applications;
	}
	public function endApplication( aDwApplication $aDwApplication ) {
		$this->log->info( "end of :{$aDwApplication->toString()}" );
		if ( isset( $this->applications[ $aDwApplication->getInstanceKey() ] ) )
			unset( $this->applications[ $aDwApplication->getInstanceKey() ] );
		else
			$this->log->error( "no applications to remove for :{$aDwApplication->toString()}" );
	}
	public function createApplication(
		$urlApp = "bakuba"
	) : ?aDwApplication {
		$aDwApplication = null;
		$this->log->trace( "{$urlApp}:{$this->getParentClassName()}" );
		$aDwApplication = aDwApplication::createApplication(
			$this->parent
			,$urlApp
		);
		$this->applications[ $aDwApplication->getInstanceKey() ] = $aDwApplication;
		$aDwApplication->onClose( new DwCallBack( array( &$this ,"endApplication" ) ,$aDwApplication ) );
		return $aDwApplication;
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

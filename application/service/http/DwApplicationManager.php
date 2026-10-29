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

namespace bakuba\kernel\application\service\http;
//use bakuba\kernel\application\service\http\DwApplicationManager as DwApplicationManager;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

//use bakuba\kernel\application\session\DwSessionResponse as DwSessionResponse;
//use bakuba\kernel\application\DwApplicationRequest as DwApplicationRequest;
class DwApplicationManager extends \bakuba\kernel\application\service\DwApplicationManager {
	public static function singleton( \bakuba\kernel\services\application\DwApplication $dwServicesNetApplication ) : \bakuba\kernel\application\DwApplicationManager {
		null === self::$instance and self::$instance = new self( $dwServicesNetApplication );
		return self::$instance;
	}
	
	public function getDwApplicationServices() :\bakuba\kernel\services\application\DwApplication {
		return $this->parent;
	}
	private function __construct( \bakuba\kernel\services\application\DwApplication $parent ) {
		parent::__construct( $parent );
	}
	
	private $appIp = null;
	public function getAppIp() : \bakuba\kernel\myip\DwApplication {
		return $this->appIp;
	}
	private $appId = null;
	public function getAppId() : \bakuba\id\DwApplication {
		return $this->appId;
	}
	
	private $appContrat = null;
	public function getAppContrat() : \bakuba\root\DwApplication {
		return $this->appContrat;
	}
	
	public function createApplicationHttp(
		$urlApp = "bakuba"
		,$domain = "bakuba.darkweb.fr"
	) : ?iDwApplication {
		$aDwApplication = null;
		$this->log->trace("$domain/$urlApp:".$this->getParentClassName() );
		$aDwApplication = aDwApplication::createApplicationHttp(
			$this->getDwApplicationServices()
			,$urlApp
			,$domain
		);
		//if ( isset( $this->applications[ $domain ] ) )
		//	throw new Exception("application $domain ever exist.");
		
		if ( $urlApp == "bakuba\\kernel\\myip" )
			$this->appIp = $aDwApplication;
		if ( $urlApp == "bakuba\\root" )
			$this->appContrat = $aDwApplication;
		if ( $urlApp == "bakuba\id" )
			$this->appId = $aDwApplication;
		
		$this->applications[ $aDwApplication->getInstanceKey() ] = $aDwApplication;
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

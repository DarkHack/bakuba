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

namespace bakuba\kernel\services;
//use bakuba\kernel\services\DwApplication as DwApplication;

use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use \bakuba\kernel\session\DwSessionIp as DwSessionIp;

class DwApplication extends \bakuba\kernel\application\service\aDwApplication {
	protected $ableToFork = false;
	
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className = $this->getClassName();
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
//				,\darkphp\source\DwSource::classNameToFirstSectionName( $className )
				,"services"
				,\darkphp\source\DwSource::classNameToTopName( $className )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	private $dwServiceHttp = null;
	private $dwServiceDns = null;
	protected function init() : void {
		$this->log->trace(".");
		$this->dwServiceDns = new \bakuba\kernel\services\dns\DwDnsd( $this );
		$this->dwServiceHttp = new \bakuba\kernel\services\http\DwHttpd( $this ); 
		parent::init();
		$this->dwServiceDns->init();
		$this->dwServiceHttp->init();
		
		$this->dwApplicationManager = \bakuba\kernel\application\service\http\DwApplicationManager::singleton( $this );
		$dwApplicationServices = $this->dwApplicationManager->getDwApplicationServices();
		
		$this->scanFolder();
	}
	private $sessionArray = array();
	public function getDwSessionIp(
		\bakuba\kernel\network\DwPort $cltPort
	) : DwSessionIp {
		$cltIp = $cltPort->getIp();
		$ipKey = $cltIp->getCrc32();
		if ( ! isset( $this->sessionArray[ $ipKey ] ) )
			$this->sessionArray[ $ipKey ] = new DwSessionIp( $cltIp );
		
		return $this->sessionArray[ $ipKey ];
	}
	public function addDomain( string $domain ) {
		$this->dwServiceDns->addDomain( $domain );
	}
	public function serviceConnectApp(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\service\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\service\iDwApplicationConnexion {
		throw new \Exception( "no connectApplication in service app process." );
	}
	private function rootCreateApplication( string $appUrl ,string $appHost ) {
		//if ( $this->logEnable )
		echo "rootCreateApplication:{$appUrl}/{$appHost}".PHP_EOL;
		$this->log->trace(" $appUrl:$appHost ");
		$dwApplication = $this->dwApplicationManager->createApplicationHttp(
			$appUrl
			,$appHost
		);
		if ( \is_null( $dwApplication )) 
			$this->log->error(" echec create application $appUrl:$appHost ");
		else {
			//$this->dwApplications[] = $dwApplication;
			$this->createProcess( $dwApplication );
			$this->log->trace("created application $appUrl:$appHost ");
		}
	}
	
	public function scanFolder( ) {
		//global $dwBakuba;
		$this->log->trace("scan");
		
		$rootHost="darkweb.fr";
		///*
		foreach( array(
			"root" => $rootHost
			,"kernel\\myip" => "myip.{$rootHost}"
			,"kernel\\mystate" => "mystate.{$rootHost}"
			,"kernel\\samples\\serviceHttp" => "servicehttp.{$rootHost}"
			,"kernel\\samples\\serviceSession" => "servicesession.{$rootHost}"
			,"id" ,"kernel" ,"user" ,"company"
//			,"id" ,"kernel" ,"user" ,"company"
		) as $appKey => $appDomain ) {
			if ( \is_int( $appKey ) ) {
				$appUrl = "bakuba\\$appDomain";
				$appDomain = "$appDomain.$rootHost";
			} elseif ( \is_null( $appDomain ) || \is_int( $appDomain )  ) {
				$appUrl = "bakuba\\$appKey";
				$appDomain = "$appKey.$rootHost";
			} else {
				$appUrl = "bakuba\\$appKey";
			}
			echo "Try[ {$appKey} => {$appDomain} ] {$appUrl}\\DwApplication";
			if ( DwAutoLoad::classExist("$appUrl\\DwApplication") )
				$this->rootCreateApplication( $appUrl ,$appDomain );
			else
				$this->log->error( "class not exist:{$appUrl}\\DwApplication" );
		}
		//*/
		///*
		$dwNode = $this->getNode();
		if ( $dwNode->isMaster() ) {
			$listFolders = DwAutoLoad::scandir( "/" );
			if ( is_array($listFolders) ) {
				//if ( $this->logEnable )
					$this->log->trace($listFolders);
				foreach($listFolders as $folder) {
					if ( $folder=="." || $folder==".." ) continue;
					$appUrl = "$folder";
					if ( DwAutoLoad::classExist("$appUrl\\DwApplication") )
						$this->rootCreateApplication( $appUrl ,"$appUrl.$rootHost" );
				}
			}
		}
		//*/	
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

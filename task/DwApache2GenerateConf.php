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

namespace bakuba\kernel\dwTask;

use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;

use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\agartha\DwNode as DwNode;

//use bakuba\kernel\process\server\DwServer as DwServer;

use bakuba\kernel\network\DwPort as DwPort;
use bakuba\kernel\process\server\DwServer as DwServer;
use bakuba\kernel\dwService\DwService as DwService;

//use bakuba\kernel\dwTask\DwApache2GenerateConf as DwApache2GenerateConf;
class DwApache2GenerateConf extends DwTask {
	private static $sLog = null;
	
	public static function create( DwNode $dwNode ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			
			if (self::$tasks == null ) self::$tasks = array();
			$dwNodeKey = $dwNode->getInstanceKey();
			
			if ( ! isset( self::$tasks[ $dwNodeKey ] ) ) self::$tasks[ $dwNodeKey ] = array();
			
			do { $taskKey = uniqid(); }while ( isset( self::$tasks[ $dwNodeKey ][ $taskKey ] ) ) ;
			
			self::$sLog->trace("dwNodeKey:'$dwNodeKey', taskKey:'$taskKey'");
			
			self::$tasks[ $dwNodeKey ][ $taskKey ] = new self( $dwNode, $taskKey );
			
			return self::$tasks[ $dwNodeKey ][ $taskKey ];
		
		} catch(Exception $exception) {
			self::$sLog->error("exception [".self::getStaticClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".self::getStaticClassName()."]");
			throw $error;
		}
		return null;
	}
	
	
	public function __construct( DwNode $dwNode, $taskKey ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			self::$sLog->trace(".");
			parent::__construct( $dwNode, $taskKey, "upnpclient", "/etc/srvconf/upnp/client" );
			
			// command : /etc/darksrv/upnp/bin/check
			// conf : /etc/srvconf/upnp/client/*
			//
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	private function domainConfigure( DwService $dwService, array $apache2SrvConf ) {
		try {
			self::$sLog->trace("configure bakuba web service");
			$dwApplicationManager =\bakuba\kernel\application\DwApplicationManager::singleton();
			
			$varsServiceContent= chr(0x23) . chr(0x21) . "/bin/sh".chr(0x0A);
			
			$racineBakubaApp = "/var/www";
			if ( ! is_dir( $racineBakubaApp ) ) mkdir ($racineBakubaApp);
			
			$wwwFolder = "$racineBakubaApp/bakuba";
			if ( ! is_dir($wwwFolder) ) mkdir ($wwwFolder);
			
			foreach( $apache2SrvConf as $dataSrvConf ) {
				$serviceType = $dataSrvConf[0]; // "proxy"
				$serviceProto = $dataSrvConf[1]; // "https"
				$serviceDomain = $dataSrvConf[2];// "darkweb.fr"
				$servicePath = $dataSrvConf[3]; // "/"
				$serviceData = $dataSrvConf[4]; // "http://".$this->dwPort->getIp().":".$this->dwPort->getPort()
				$serviceKeyFile = $dataSrvConf[5];// "/etc/darksrv/ssl/darkweb.fr/darkweb.fr.key"
				$servicePemFile = $dataSrvConf[6];// "/etc/darksrv/ssl/darkweb.fr/darkweb.fr.pem"
				
				self::$sLog->trace("configure :{$serviceProto}://{$serviceDomain}{$servicePath} ");
				$varsServiceContent.="local services=\"\$services;{$serviceType}";
				$varsServiceContent.="|{$serviceProto}|{$serviceDomain}|{$servicePath}";
				$varsServiceContent.="|{$serviceData}|{$servicePemFile}|{$serviceKeyFile}";
				$varsServiceContent.="\"\n";
			}
			
			//$this->fileRead(, "/etc/hosts" );
			$dwService->fileWrite( "$racineBakubaApp/bakuba/vars_services", $varsServiceContent );
			$dwService->execute( "/etc/darksrv/web/apache2/bin/generateServicesConf 2>&1" );
			//$dwService->execute("chown -R www-data:www-data /var/www;chmod -R a+rwx /var/www 2>&1");
			$dwService->restart( );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	public function generate( DwService $dwService, array $apache2SrvConf ) {
		try {
			self::$sLog->trace(".");
			
			$this->domainConfigure( $dwService, $apache2SrvConf );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

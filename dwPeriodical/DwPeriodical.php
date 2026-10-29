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

namespace bakuba\kernel\dwBakuba;

use \Exception as Exception;
use darkphp\DwObject as DwObject;
use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\shell\DwShell as DwShell;
use bakuba\kernel\application\DwApplicationManager as DwApplicationManager;
use bakuba\kernel\dwController\abstracts\ACtrlSession as ACtrlSession;

use bakuba\kernel\dwNode\DwNode as DwNode;
use bakuba\kernel\DwService as DwService;

use bakuba\kernel\process\server\DwServer as DwServer;
use bakuba\kernel\network\DwInterface as DwInterface;
use bakuba\kernel\network\DwPort as DwPort;
use bakuba\kernel\agartha\DwAgartha as DwAgartha;

use bakuba\kernel\dwController\DwControllerManager as DwControllerManager;

use bakuba\kernel\services\DwLightHttpd as DwLightHttpd;
use bakuba\kernel\services\DwDarkSrv as DwDarkSrv;


class DwPeriodical extends DwObject {
	// singleton class pattern:
	private static $instance = null;
	public static function singleton( $initAgartha = null ) {
		try {
			null === self::$instance and self::$instance = new self( $initAgartha );
			return self::$instance;
		} catch(Exception $err) {
			//echo "[DwBakuba:singleton]ERROR:$err\r\n";
			throw $err;
		}
		return null;
	}
	private $dwServer = null;
	private $srvIp = null;
	private $srvPort = null;
	private $dwNode = null;
	private $dwApplicationManager = null;
	private $log = null;
	private function __construct( $initAgartha = null ) {
		try {
			$this->log = DwLogger::getLogger( $this );
			$this->log->trace(".");			
			parent::__construct();
			
			//DwLogger::logSwitchTo("DwBakuba.log");
			$this->log->debug("--- create Agartha");
			// Init Agartha
			$dwAgartha = DwAgartha::singleton( $initAgartha );
			// Create node
			$this->dwNode = $dwAgartha->createNode();
			
			$this->dwApplicationManager = DwApplicationManager::singleton();
			$this->insert($this->dwApplicationManager);

			// Create bakuba instance
			//$dwBakuba = $dwNode->getBakuba();
			$this->log->debug("--- dwApplicationManager");
			$dwApplicationManager = $this->dwApplicationManager;
			//$this->getApplicationManager();
			$this->log->debug("#".$this->scanInterfaces($this->dwNode));
			// get first interface 'lo'
			//$ifaceLo = ;
			$ifaceLo = $this->dwNode->getInterface("lo");
			// get first CIDR:127.0.0.0/8 of 'lo' interface, null place like... here
			//$dwCIDR = $ifaceLo->getCIDRs()[0];
			$this->srvIp='127.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254);//"127.0.0.1"
			$this->srvPort=rand(1024,65534);//8085
			$dwCIDR = $ifaceLo->getCIDR( $this->srvIp );//$dwAgartha
			// $dwIp = $dwCIDR->getIp( '127.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254) );
			$dwIp = $dwCIDR->getIp( $this->srvIp );
			//$this->dwServer = $dwBakuba->createServer( $dwIp->getPort($ifaceLo,rand(1025,65534)) );
			$this->log->debug("--- dwBakuba->createServer");
			$this->dwServer = $this->createServer( $dwIp->getPort($ifaceLo, $this->srvPort) );
			/*
			$this->log->debug("--- dwBakuba->stop");
			if ( true )
				return ;
			//*/
			
			$this->log->debug("--- dwApplicationManager");
			//$dwApplicationManager = $this->getControllerManager();
			$dwApplicationManager->createApplication( "bakuba.darkweb.fr", "bakuba" );
			$dwApplicationManager->createApplication( "kernel.darkweb.fr", "bakuba\kernel" );
			
			$this->dwServer->addService( "bakuba\kernel\services\DwDarkSrv", $dwApplicationManager );
			$this->dwServer->addService( "bakuba\kernel\services\DwLightHttpd", $dwApplicationManager );
			/*
			$dwLightHttpd = new DwLightHttpd( $dwNode, $dwApplicationManager );
			//$dwLightHttpd->addControllerManage( $dwApplicationManager );
			$dwDarkSrv = new DwDarkSrv($dwNode);
			$this->dwServer->addService( $dwLightHttpd );
			$this->dwServer->addService( $dwDarkSrv );
			//*/
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function getServer() {
		return $this->dwServer;
	}
	public function getNode() {
		return $this->dwNode;
	}
	public function getSrvIp() {
		return $this->srvIp;
	}
	public function getSrvPort() {
		return $this->srvPort;
	}
	public function start( ) {
		try {
			$this->log->trace(".");
			$this->dwServer->start();
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	private function scanInterfaces() {
		try {
			$shellCmd="ip link show | cut -d: -f 2 | grep '^ ' | tr '\n' ' ' | tr '  ' ' '";
			foreach( explode(" ",DwShell::exec($shellCmd)) as $ifaceName ) {
				if (!empty($ifaceName)) {
					$this->log->debug("found interface:$ifaceName");
					$dwInterface = $this->dwNode->createInterface($ifaceName);
					$dwCIDR = $this->interfaceScanIp($dwInterface);
				}
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	private function interfaceScanIp(DwInterface $dwInterface) {
		try {
			$tabCidr = array();
			$shellCmd="ip addr show ".$dwInterface->getIfaceName()." | grep -v inet6 | grep inet | cut -d' ' -f6 | tr '\n' ' '";
			foreach( explode(" ",DwShell::exec($shellCmd)) as $cidrWithIp ) {
				if (empty($cidrWithIp)) continue;
				$this->log->debug($dwInterface->getIfaceName()." ip found:$cidrWithIp");
				$dwCIDR = $dwInterface->createCIDR($cidrWithIp);
				$tabCidr[] = $dwCIDR;
				$splitDataIp =explode("/",$cidrWithIp);
				if (isset($splitDataIp[0]))
					$dwIp = $dwCIDR->addIp( $splitDataIp[0] );
			}
			$shellCmd="ip route show | grep via | cut -d' ' -f3,5 | tr ' ' '_' | tr '\n' ' '";
			foreach( explode(" ",DwShell::exec($shellCmd)) as $gwWithDev ) {
				if (empty($gwWithDev) ) continue;
				list($ip, $iface) = explode('_', $gwWithDev);
				if ( $dwInterface->getIfaceName() == $iface ) {
					echo $dwInterface->getIfaceName()." gateway found:$ip\r\n";
					$this->log->debug($dwInterface->getIfaceName()." gateway found:$ip");
					foreach($tabCidr as $dwCIDR) {
						if ( $dwCIDR->inside($ip) )
							$dwCIDR->addGw($ip);
					}
				}
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	
	public function getApplicationManager() {
		return $this->dwApplicationManager;
	}
	protected $dwServers = array();
	public function createServer( DwPort $dwPort ) {
		$dwServer = null;
		try {
			$this->log->trace(".");
			$dwServer = DwServer::createServer( $dwPort );
			$this->insert($dwServer);
			$this->dwServers[] = $dwServer;
		} catch(Exception $exception) {
			$this->log->error($exception);
			throw $exception;
		}
		return $dwServer;
	}
	public function createSession() {
	}

	private $cptPing = 0;
	public function ping() {
		try {
			$this->cptPing++;
			if ( $this->cptPing%10 == 0 ) {
				$this->log->debug("ping[".$this->cptPing."]");
				$this->serviceCheck();
			}
		} catch(Exception $exception) {
			$this->log->error($exception);
			throw $exception;
		}
		try {
			if ( ! $this->haveParent() ) {
				$this->log->trace("no ping, cause no parent");
				return ;
			} else {
				$request = base64_encode( json_encode( array(
					"action" => "ping"
					,"status" => $this->getStatus()
				) ) );
				$this->log->trace("ping to ".$this->getIpSrv().":$request");
				$response = json_decode( base64_decode( $this->dwControllerClient->request( $request ) ) );
				if  ( isset ( $response->action ) ) {
					switch( $response->action ) {
						case "pong" :
							$this->executeOrders( $response->orders );
							$this->log->trace("pong received");
							break;
						default :
							$this->log->trace( "unknow action :".$response->action );
							break;
					}
				}
			}
		} catch(Exception $exception) {
			$this->log->error($exception);
			// lost ping we shutdown
			$this->shutdown();
			throw $exception;
		}
	}
	private $isAlive = true;
	public function shutdown() {
		try {
			$this->isAlive = false;
			if ( ! $this->haveParent() ) {
				$this->log->trace("no shutdown, cause no parent");
				return ;
			} else {
				$request = base64_encode( json_encode( array(
					"action" => "shutdown"
					,"status" => $this->getStatus()
				) ) );
				$this->log->trace("shutdown to ".$this->getIpSrv().":$request");
				$response = json_decode( base64_decode( $this->dwControllerClient->request( $request ) ) );
				if  ( isset ( $response->action ) ) {
					switch( $response->action ) {
						case "thanks!" :
							$this->log->trace("thanks! received");
							break;
						default :
							$this->log->trace( "unknow action :".$response->action );
							break;
					}
				}
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

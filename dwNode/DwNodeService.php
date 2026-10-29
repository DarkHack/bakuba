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

namespace bakuba\kernel\dwNode;

use \Exception as Exception;
use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\DwPreNode as DwPreNode;

use bakuba\kernel\dwService\DwService as DwService;

use bakuba\kernel\apache2\ServiceHttpd as ServiceHttpd;
use bakuba\kernel\dwService\DwDhcpd as DwDhcpd;
use bakuba\kernel\dwService\DwMiniupnpd as DwMiniupnpd;
use bakuba\kernel\dwService\DwOpenVpn as DwOpenVpn;
use bakuba\kernel\dwService\DwInTftpd as DwInTftpd;
use bakuba\kernel\dwService\DwExtNamed as DwExtNamed;
use bakuba\kernel\dwService\DwIntNamed as DwIntNamed;

class DwNodeService extends DwNodeNetFace {
	
	private $log;
	protected function __construct( DwPreNode $dwPreNode ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->trace(".");
			parent::__construct( $dwPreNode );
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function getStatus( $response = null) {
		try {
			$this->log->trace(".");
			$response = parent::getStatus($response);
			if ( $response == null )
				$response = array();
			
			$services = array();
			foreach( $this->services as $dwService ) {
				$services[] = $dwService->getStatus();
			}
			
			$response[ "services" ] = $services;
			
			return $response;
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	public function setStatus($response) {
		try {
			if ( $this->isMaster() )
				throw new Exception("no setStatus on master node");
			else {
				$this->log->trace(". ".print_r($response,true));
				parent::setStatus($response);
				///*
				if ( isset( $response->services ) && is_array($response->services) ) {
					//$this->log->trace("addServices ".print_r($response->services,true));
					///*
					$this->services = array();
					foreach( $response->services as $statusService ) {
						//$this->log->trace("addService ".print_r($statusService,true));
						$this->services[] = DwService::serviceFromStatus( $this, $statusService );
					}
					//*/
				}
				//*/
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	
	private $services = array();
	public function serviceConfigure( ) {
		try {
			$dwServerConfig = $this->dwServer->getConfig();
			$this->log->trace("./serviceConfigure(".$dwServerConfig->{"addr"}.":".$dwServerConfig->{"port"}."):".print_r($dwServerConfig,true));
			$this->services = array();
			if ( is_dir ( "/srv/httpd" ) )
				$this->services[] = ServiceHttpd::create( $this );
			
			if ( is_dir ( "/srv/dhcpd" ) )
				$this->services[] = DwDhcpd::create( $this );
			
			if ( is_dir ( "/srv/miniupnpd" ) ) 
				$this->services[] = DwMiniupnpd::create( $this );
			
			if ( is_dir ( "/srv/openvpn" ) )
				$this->services[] = DwOpenVpn::create( $this );
			
			if ( is_dir ( "/srv/in.tftpd" ) )
				$this->services[] = DwInTftpd::create( $this );
			
			if ( is_dir ( "/srv/named-ext" ) )
				$this->services[] = DwExtNamed::create( $this );
			
			if ( is_dir ( "/srv/named-int" ) )
				$this->services[] = DwIntNamed::create( $this );
			
			foreach($this->services as $service) {
				$service->configure( $dwServerConfig );
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function getServices( ) {
		try {
			$this->log->trace(".");
			return $this->services;
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return null;
	}
	private $domains = array();
	protected function addDomain( $domain ) {
		try {
			$this->log->trace("$domain");
			//parent::addDomain( $domain );
			$this->domains[] = $domain;
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	
	private function serviceBakubaCheck() {
		try {
			$this->log->trace("check bakuba web service");
			
			foreach($this->domains as $domain) {
				$this->log->trace("check bakuba web service :".$domain);
			}
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function serviceCheck() {
		try {
			$this->log->trace(".");
			$racine="/etc/srvconf";
			if ( is_dir( "/var/www/bakuba" ) ) {
				$this->serviceBakubaCheck();
			}
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
}

?>

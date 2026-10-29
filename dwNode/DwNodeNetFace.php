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

use bakuba\kernel\extInc\NetworkInterfaces\NetworkInterfaces as NetworkInterfaces;
use bakuba\kernel\extInc\NetworkInterfaces\Adaptor as Adaptor;
class DwNodeNetFace extends DwNodeMemory {
	private $networkInterfaces;
	private $log;
	protected function __construct( DwPreNode $dwPreNode ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->trace(".");
			parent::__construct( $dwPreNode );
			/*
			if ( $this->dwServerConfig != null ) {
				$this->networkInterfaces = new NetworkInterfaces();
				$this->networkInterfaces->parse();
			}
			//*/
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
			
			$interfaces = array();
			foreach( $this->networkInterfaces->Adaptors as $ifaceName => $adaptor ) {
				$interfaces[] = $adaptor->getStatus();
			}
			
			$response[ "interfaces" ] = $interfaces;
			
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
				if ( isset( $response->interfaces ) && is_array($response->interfaces) ) {
					//$this->log->trace("addServices ".print_r($response->services,true));
					///*
					$this->interfaces = array();
					foreach( $response->interfaces as $statusInterface ) {
						//$this->log->trace("addService ".print_r($statusService,true));
						$adaptor = new Adaptor();
						$adaptor->setStatus( $statusInterface );
						$this->interfaces[] = $adaptor;
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
	
	public function getInterfaces( ) {
		try {
			$this->log->trace(".");
			return $this->interfaces;
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return null;
	}
	
}

?>

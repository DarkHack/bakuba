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

use bakuba\kernel\DwApplication as DwApplication;

class DwNodeApplication extends DwNodePeriodical {
	// construct with logger
	private $log;
	public function __construct( DwPreNode $dwPreNode ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->trace(".");
			
			parent::__construct( $dwPreNode );
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	private $appInstalled = array();
	protected function addDomain( $domain ) {
		try {
			$this->log->trace("$domain");
			parent::addDomain( $domain );
			$this->appInstalled[] = array( "proto" => "https", "domaine" => $domain, "path" => "/var/www/bakuba" );	
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	
	private function updateDwApplicationFromStatus( $appStatus ) {
		try {
			$this->log->trace(".");
			
			$applicationKey = $appStatus->applicationKey;
			if (! isset ($this->applications[ $applicationKey ] ) )
				$this->applications[ $applicationKey ] = new DwApplication( $applicationKey, $appStatus->domain, $appStatus->domain );
			
			$dwApplication = $this->getAppFromKey( $applicationKey );
			$dwApplication->setStatus( $appStatus );
			return $dwApplication;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	public function createApplication( $appDomain, $appUrl ) {
		$dwApplication = null;
		try {
			$this->log->trace(".");
			
			if ( ! $this->isMaster() )
				throw new Exception("no createApplication on slave node"); 
			else {
				$dwApplication = $this->createDwApplication( $appDomain, $appUrl );
				$this->addDomain( $appDomain );
				$this->log->trace("serviceConfigure:$appDomain/$appUrl");
				//$this->serviceConfigure( );
			}
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return $dwApplication;
	}
	public function getStatus( $response = null) {
		try {
			$this->log->trace(".");
			$response = parent::getStatus($response);
			if ( $response == null )
				$response = array();
			
			$applications = array();
			foreach( $this->applications as $dwApplication ) {
				$applications[] = $dwApplication->getStatus();
			}
			
			$response[ "applications" ] = $applications;
			
			return $response;
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	public function setStatus($response) {
		try {
			if ( ! $this->isMaster() )
				throw new Exception("no setStatus on master node");
			else {
				
				$this->log->trace(". ".print_r($response,true));
				parent::setStatus($response);
				
				if (isset( $response->applications ) ) {
					foreach( $response->applications as $statusApplication ) {
						//$this->log->trace("addApp $statusApplication");
						$this->updateDwApplicationFromStatus( $statusApplication );
					}
				}
			}
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
}

?>

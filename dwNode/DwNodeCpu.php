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

abstract class DwNodeCpu extends DwNodeCrypto {
	
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
			
			$response[ "cpu" ] = $this->getCpuState();
			
			return $response;
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	public function setStatus($response) {
		try {
			if ( $this->dwServerConfig != null )
				throw new Exception("no set Status on master node");
			else {
				$this->log->trace(". ".print_r($response,true));
				parent::setStatus($response);
				
				if (isset($response->cpu))
					$this->lastCpuState = $response->cpu;
			}
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
	}
	private function getCpuStats() {
		$cpu = array();
		try {
			$stat1 = file('/proc/stat');
			sleep(1);
			$stat2 = file('/proc/stat');
			$info1 = explode(" ", preg_replace("!cpu +!", "", $stat1[0]));
			$info2 = explode(" ", preg_replace("!cpu +!", "", $stat2[0]));
			$dif = array();
			$dif['user'] = $info2[0] - $info1[0];
			$dif['nice'] = $info2[1] - $info1[1];
			$dif['sys'] = $info2[2] - $info1[2];
			$dif['idle'] = $info2[3] - $info1[3];
			$total = array_sum($dif);
			foreach($dif as $x=>$y) $cpu[$x] = round($y / $total * 100, 1);
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $cpu;
	}
	
	private $lastCpuState = array();
	public function getCpuState(  ) {
		$cpu = array();
		try {
			if ( $this->dwControllerClient != null )
				$cpu = $this->lastCpuState;
			else {
				$cpu["stats"] = $this->getCpuStats();
			}
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $cpu;
	}
}

?>

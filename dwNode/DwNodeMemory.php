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

abstract class DwNodeMemory extends DwNodeCpu {
	
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
			
			$response[ "memory" ] = $this->getMemoryState();
			
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
				
				if (isset($response->memory))
					$this->lastMemoryState = $response->memory;
			}
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
	}
	
	private function memory_get_process_usage() {
		try {
			if ( ! $this->isMaster() )
				throw new Exception("no memory_get_process_usage on slave node");
			else {
				$status = file_get_contents('/proc/' . getmypid() . '/status');
				$matchArr = array();
				preg_match_all('~^(VmRSS|VmSwap):\s*([0-9]+)\s*([k|m])B.*$~im', $status, $matchArr);
				if(!isset($matchArr[2][0]) || !isset($matchArr[2][1])) {
					return false;
				}
				$factore = ( !isset($matchArr[3][0]) ? 1 : $matchArr[3][0]=='k' ? 1024 : 1024*1024 );
				
				return ( intval($matchArr[2][0]) + intval($matchArr[2][1]) ) * $factore;
			}
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
	}
	public function getMemoryDelta( $mstart, $mstop ) {
		$delta = array();
		try {
			if ( is_array($mstart) && is_array($mstop) ) {
				//$delta["mstart"] = $mstart;
				//$delta["mstop"] = $mstop;
				$delta["usage"] = $mstart["usage"] - $mstop["usage"];
				$delta["usageTrue"] = $mstart["usageTrue"] - $mstop["usageTrue"];
				$delta["usagePeak"] = $mstart["usagePeak"] - $mstop["usagePeak"];
				$delta["usagePeakTrue"] = $mstart["usagePeakTrue"] - $mstop["usagePeakTrue"];
				$delta["usageUnix"] = $mstart["usageUnix"] - $mstop["usageUnix"];
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $delta;
	}
	private $lastMemoryState = array();
	public function getMemoryState(  ) {
		$response = array();
		try {
			if ( ! $this->isMaster() )
				$response = $this->lastMemoryState;
			else {
				$response["usage"] = memory_get_usage();
				$response["usageTrue"] = memory_get_usage(true);
				$response["usagePeak"] = memory_get_peak_usage();
				$response["usagePeakTrue"] = memory_get_peak_usage(true);
				$response["usageUnix"] = $this->memory_get_process_usage();
				$response["limit"] = $this->readNbOctet( ini_get('memory_limit') );
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $response;
	}
	
	private function readNbOctet( $strNbOctet ) {
		$nbOctet = $strNbOctet;
		try {
			if (preg_match('/^(\d+)(.)$/', $strNbOctet, $matches)) {
				if ($matches[2] == 'M') {
					$nbOctet = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
				} else if ($matches[2] == 'K') {
					$nbOctet = $matches[1] * 1024; // nnnK -> nnn KB
				}
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $nbOctet;
	}
}

?>

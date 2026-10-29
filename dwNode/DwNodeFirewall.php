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

use bakuba\kernel\dwTask\DwTask as DwTask;
/*
use bakuba\kernel\apache2\ServiceHttpd as ServiceHttpd;
use bakuba\kernel\dwService\DwDhcpd as DwDhcpd;
use bakuba\kernel\dwService\DwMiniupnpd as DwMiniupnpd;
use bakuba\kernel\dwService\DwOpenVpn as DwOpenVpn;
// /openvpn/service
// /openvpn/task

use bakuba\kernel\dwService\DwInTftpd as DwInTftpd;
use bakuba\kernel\dwService\DwExtNamed as DwExtNamed;
use bakuba\kernel\dwService\DwIntNamed as DwIntNamed;
*/

class DwNodePeriodical extends DwNodeService {
	
	private $log;
	protected function __construct( DwPreNode $dwPreNode ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->trace(".");
			parent::__construct( $dwPreNode );
			$this->periodicalConfigure();
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
			
			$tasks = array();
			foreach( $this->tasks as $dwTask ) {
				$tasks[] = $dwTask->getStatus();
			}
			
			$response[ "tasks" ] = $tasks;
			
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
				if ( isset( $response->tasks ) && is_array($response->tasks) ) {
					//$this->log->trace("addServices ".print_r($response->services,true));
					///*
					$this->tasks = array();
					foreach( $response->tasks as $statusTask ) {
						//$this->log->trace("addService ".print_r($statusTask,true));
						$this->tasks[] = DwTask::taskFromStatus( $this, $statusTask );
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
	
	private function periodicalParseConf() {
		try {
			$fileConf="/etc/srvconf/periodical/periodical";
			if ( is_file ( $fileConf ) ) {
				$fileConfHandle = fopen($fileConf, "r");
				if ($fileConfHandle) {
					$this->tasks = array();
					while (($line = fgets($fileConfHandle)) !== false) {
						$matches=array();
						preg_match('/^.*\|(.*?;)(.*?;)(.*?;)(.*?\")/m',$line,$matches);//,PREG_PATTERN_ORDER);
						if (count($matches) == 5 ) {
							$task = array();
							$task["periode"]=trim(substr($matches[1],0,-1));
							$task["userGroup"]=trim(substr($matches[2],0,-1));
							$task["name"]=trim(substr($matches[3],0,-1));
							$task["commandes"]=trim(substr($matches[4],0,-1));
							$this->tasks[] = $task;
							$this->log->trace(print_r(array("task" => $task, "line" => $line ),true));
						}
					}
					fclose($fileConfHandle);
				} else {
					// error opening the file.
				}
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	private function periodicalParseHashBdd() {
		try {
			$fileHashBdd="/tmp/periodical/hash.bdd";
			if ( is_file ( $fileHashBdd ) ) {
				$handle = fopen($fileHashBdd, "r");
				if ($handle) {
					while (($line = fgets($handle)) !== false) {
						$matches=array();
						preg_match('/^(.*?\|)(.*?\|)(.*)/m',$line,$matches);//,PREG_PATTERN_ORDER);
						
						if (count($matches) == 4 ) {
							$timeexecute=trim(substr($matches[1],0,-1));
							$hashCmd=trim(substr($matches[2],0,-1));
							$name=trim(substr($matches[3],0));
							$found=false;
							foreach($this->tasks as $index => $task) {
								if ( $task[ "name" ] == $name ) {
									$this->tasks[ $index ][ "timeExecute" ] = $timeexecute;
									$this->tasks[ $index ][ "hashCmd" ] = $hashCmd;
									$found=true;
								}
							}
							if (!$found)
								$this->log->trace("no task ?".$name);
						}
					}
					fclose($handle);
				} else {
					// error opening the file.
				}
				//$this->log->trace(print_r(array("tasks" => $this->tasks ),true));
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	// /var/log/cron_periodical.log
	private function periodicalParseFilesLog() {
		try {
			$dirFilesLog="/var/log/periodical";
			if ( is_dir ( $dirFilesLog ) ) {
				foreach (scandir($dirFilesLog) as $entry) {
					if ( $entry == "." || $entry == ".." ) continue;
					foreach($this->tasks as $index => $task) {
						if ( $task[ "name" ] == $entry ) {
							$this->tasks[ $index ][ "dirCronLog" ] = $entry;
						}else if ( $task[ "name" ].".log" == $entry ) {
							$this->tasks[ $index ][ "dirCronLogFile" ] = $entry;
						}
					}
				}
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	private $tasks = array();
	public function periodicalConfigure( ) {
		try {
			$this->log->trace(".");
			$this->periodicalParseConf();
			$this->periodicalParseHashBdd();
			$this->periodicalParseFilesLog();
			$this->log->trace(print_r(array("tasks" => $this->tasks ),true));
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
}

?>

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
use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\shell\DwShell as DwShell;

use bakuba\kernel\agartha\DwNode as DwNode;

//use bakuba\kernel\dwTask\DwTask as DwTask;
class DwTask {
	static private $sLog = null;
	protected static $tasks = null;
	
	public static function createTask( DwNode $dwNode, $nom = "taskName", $period = "600/1200", $userGroup  = "root/root", $commands = array() ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			if (self::$tasks == null ) self::$tasks = array();
			$dwNodeKey = $dwNode->getPreNodeKey();
			
			if ( ! isset( self::$tasks[ $dwNodeKey ] ) ) self::$tasks[ $dwNodeKey ] = array();
			
			do { $serviceKey = uniqid(); }while ( isset( self::$services[ $dwNodeKey ][ $serviceKey ] ) ) ;
			
			self::$sLog->trace("dwNodeKey:'$dwNodeKey', serviceKey:'$serviceKey', serviceName:'$nom'");
			
			self::$services[ $dwNodeKey ][ $serviceKey ] = new self( $dwNode, $serviceKey, $nom, $folder, $config_file );
			
			return self::$services[ $dwNodeKey ][ $serviceKey ];
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	public static function serviceFromStatus( DwNode $dwNode,  $status ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			
			if (self::$services == null ) self::$services = array();
			$dwNodeKey = $dwNode->getPreNodeKey();
			if ( ! isset( self::$services[ $dwNodeKey ] ) ) self::$services[ $dwNodeKey ] = array();
			
			self::$sLog->trace("dwNodeKey:'".$dwNodeKey."' ,serviceKey:'".$status->serviceKey."' ,serviceName:'".$status->nom."' ,create?".isset( self::$services[ $dwNodeKey ][ $status->serviceKey ] )?"yes":"no");
			
			if ( isset( self::$services[ $dwNodeKey ][ $status->serviceKey ] ) )
				$dwService = self::$services[ $dwNodeKey ][ $status->serviceKey ];
			else
				$dwService = new self( $dwNode, $status->serviceKey, $status->nom, $status->folder, $status->config_file );
			
			$dwService->setStatus( $status );
			self::$services[ $dwNodeKey ][ $status->serviceKey ] = $dwService;
			return self::$services[ $dwNodeKey ][ $status->serviceKey ];
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	public static function getServices( ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			
			if (self::$services == null ) self::$services = array();
			
			return self::$services;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	
	protected $dwNode;
	// DwCrud::$_id
	public $nom;
	public $folder;
	public function getChrootFolder() {
		return $this->folder;
	}
	public $script_start;
	public $script_status;
	public $script_stop;
	public $config_file;
	public $serviceKey;
	public function getServiceKey() {
		return $this->serviceKey;
	}
	// construct with logger
	private $log;
	public function __construct( DwNode $dwNode, $serviceKey, $nom="name", $folder="/folder", $config_file="/con" ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->trace(".");
			
			$this->dwNode = $dwNode;
			$this->nom = $nom;
			$this->folder = $folder;
			$this->config_file = $config_file;
			$this->serviceKey = $serviceKey;
			
			$this->script_start="/etc/bakuba/$nom start";
			$this->script_stop="/etc/bakuba/$nom stop";
			$this->script_status="/etc/bakuba/$nom status";
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	private $lastCommand = null;
	private $lastResult = null;
	private function execute( $cmd ) {
		try {
			$this->log->trace(".");
			
			if ( ! $this->dwNode->isMaster() )
				throw new Exception(" no execute service on slave node" );
			else {
				$this->lastCommand = $cmd;
				$this->lastResult = DwShell::exec( $cmd );
			}
			return $this->lastResult;
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	public function start() {
		try {
			$this->log->trace(".");
			if ( ! $this->dwNode->isMaster() )
				$this->addOrder("start");
			else
				return $this->execute( $this->script_start." 2>&1 " );
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	public function stop() {
		try {
			$this->log->trace(".");
			if ( ! $this->dwNode->isMaster() )
				$this->addOrder("stop");
			else
				return $this->execute( $this->script_stop." 2>&1 " );
		} catch(Exception $err) {
			$this->log->error($err);
			//throw $err;
		}
		return null;
	}
	private $lastStatus = null;
	public function getStatus() {
		try {
			$this->log->trace(".");
			
			$status = array(
				"serviceKey" => $this->serviceKey
				,"nom" => $this->nom
				,"folder" => $this->folder
				,"config_file" => $this->config_file
				,"status" => is_null($this->lastStatus ) ? DwShell::exec( $this->script_status." 2>&1 " ) : $this->lastStatus
			);
			if (! is_null( $this->lastCommand ) )
				$status["lastCommand"] = $this->lastCommand;
			if (! is_null($this->lastResult)) 
				$status["lastResult"] = $this->lastResult;
			
			if ( $this->dwNode->isMaster() ) {
				$filesLog = array();
				foreach( $this->filesLog as $fileLog ) {
					$filesLog[ base64_encode($fileLog) ] = base64_encode(DwShell::exec( "tail -1l $fileLog 2>&1 " ));
				}
				$status["filesLog"] = $filesLog;
			} else
				$status["filesLog"] = $this->filesLog;
			
			//if ( ! $this->dwNode->isMaster() ) {
			$status["orders"] = $this->orders;
			//}
			return $status;
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return null;
	}
	
	public function setStatus($status) {
		try {
			$this->log->trace(".");
			if (is_null($status))
				return ;
			
			if ( $this->dwNode->isMaster() ) 
				throw new Exception(" no set Status on service of master node" );
			else {
				
				if (isset($status->serviceKey))
					$this->serviceKey = $status->serviceKey;
				if (isset($status->nom))
					$this->nom = $status->nom;
				if (isset($status->folder))
					$this->folder = $status->folder;
				if (isset($status->config_file))
					$this->config_file = $status->config_file;
				if (isset($status->status))
					$this->lastStatus = $status->status;
				if (isset($status->lastResult))
					$this->lastResult = $status->lastResult;
				if (isset($status->lastCommand))
					$this->lastCommand = $status->lastCommand;
				if (isset($status->filesLog)) {
					$this->filesLog = array();
					foreach( $status->filesLog as $filePath => $fileContent ) {
						$this->filesLog[ $filePath ] = $fileContent;
					}
				}
				if (isset($status->orders)) {
					foreach( $status->orders as $orderId => $order ) {
						$this->orders[ $orderId ] = $order;
					}
				}
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
}

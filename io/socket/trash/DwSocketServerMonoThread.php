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

namespace bakuba\kernel\io\socket;

use \Exception as Exception;
use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\network\DwPort as DwPort;


class DwSocketServerMonoThread extends DwSocketSelect {
	private static $sLog = null;
	
	public static function createSocketServerMonoThread( DwPort $dwPort, DwCallBack $dwCallBack ) {
		$dwSocketServer = null;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
			$dwSocketServer = new self( $dwPort, $dwCallBack );
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
			
		return $dwSocketServer;
	}
	private $dwPort;
	private $dwSockServer;
	private $dwCallBack;
	
	// construct with logger
	private $log;
	public function __construct( DwPort $dwPort, DwCallBack $dwCallBack ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->trace(".");
			parent::__construct();
			
			$this->dwPort=$dwPort;
			$this->dwCallBack=$dwCallBack;
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function getClassName( $fullFqdn=false ) {
		$className = __CLASS__;
		try {
			$this->log->trace(".");
			$className= $fullFqdn ? parent::getClassName( $fullFqdn ).".".$className : $className;
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $className;
	}
	public function readEnd( $dwSocketsReaded ) {
		try {
			//$this->log->trace("."); // too much write
			if ( ! empty( $dwSocketsReaded ) ) {
				$this->log->trace("."); // too much write (little less)
				foreach( $dwSocketsReaded as $dwSocket )
					$this->answer( $dwSocket );
			}
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function writeEnd( $dwSocketsWrited ) {
		try {
			//$this->log->trace("."); // too much write
			if ( ! empty( $dwSocketsWrited ) ){
				$this->log->trace("."); // too much write (little less)
				foreach( $dwSocketsWrited as $dwSocket )
					$dwSocket->close( );	
			}
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function run() {
		try {
			$this->log->trace(".");
			set_time_limit(0);
			
			// agartha{ dwNode{ bakuba{ server{ ->port } } } | cidr{ ip{ port{} } } 
			// server and interface have to be linked
			//$dwSockServer = $this->dwPort->listen();
			$dwSockServer = DwSocketServer::createDwSocketServer( $this->dwPort );
			$this->insert($dwSockServer);
			$dwInterface = $this->dwPort->getInterface();
			//$this->dwItem->link( $dwInterface->getItem() );
			
			$this->dwSockServer = $dwSockServer;
			$this->clientsReadSock[] = $this->dwSockServer;
			
			$this->log->debug("run go in the do, john");
			declare(ticks=10000) {
				do {
					if ( $this->check() )
					// 1 000 000 : 1s
					// 5 000 : 5 ms
					usleep(5000);
					else
						usleep(1000000);
				} while(true);
			}
			$this->dwSockServer->close();			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	protected function read( $readSocks ) {
		$clientsReadedSock = array();
		try {
			//$this->log->trace(".");
			$toReadSocks = array();
			foreach($readSocks as $sock) 
				if ( $sock == $this->dwSockServer->sock )
					$this->clientsReadSock[] = $this->dwSockServer->newConnection();
				else
					$toReadSocks[] = $sock;
				
			return parent::read($toReadSocks);	
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $clientsReadedSock;
	}
	public function answer(DwSocket $dwSocket) {
		try {
			$this->log->trace(".");
			$response="fatal error";
			try {
				$response = $this->dwCallBack->callback( $dwSocket );
				//$this->log->trace("request:".$request."response:".$response);
			} catch (Exception $e) {
				$this->log->error($e);
				$response = "dwCallBack->callback fatal error!";
				throw $e;
			}
			$dwSocket->write( $response );
			$this->clientsWriteSock[] = $dwSocket;
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		
	}
}

?>

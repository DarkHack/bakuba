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



use bakuba\kernel\dwNode\DwNode as DwNode;

use bakuba\kernel\dwSocket\dwServer\DwServerConfig as DwServerConfig;



class DwUpnpClient extends DwTask {

	static private $sLog = null;

	

	public static function create( DwNode $dwNode ) {

		try {

			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");

			if (self::$tasks == null ) self::$tasks = array();

			$dwNodeKey = $dwNode->getPreNodeKey();

			

			if ( ! isset( self::$tasks[ $dwNodeKey ] ) ) self::$tasks[ $dwNodeKey ] = array();

			

			do { $taskKey = uniqid(); }while ( isset( self::$tasks[ $dwNodeKey ][ $taskKey ] ) ) ;

			

			self::$sLog->trace("dwNodeKey:'$dwNodeKey', taskKey:'$taskKey'");

			

			self::$tasks[ $dwNodeKey ][ $taskKey ] = new self( $dwNode, $taskKey );

			

			return self::$tasks[ $dwNodeKey ][ $taskKey ];

		} catch(Exception $err) {

			self::$sLog->error($err);

			throw $err;

		}

		return null;

	}

	

	

	// construct with logger

	private $log;

	public function __construct( DwNode $dwNode, $taskKey ) {

		try {

			$this->log = DwLogger::getLogger(__CLASS__);

			$this->log->trace(".");

			parent::__construct( $dwNode, $taskKey, "upnpclient", "/etc/srvconf/upnp/client" );

			// command : /etc/darksrv/upnp/bin/check

			// conf : /etc/srvconf/upnp/client/*

			// 

			$filesLog = array( "server.log","server-status.log" );

			foreach($filesLog as $fileLog) {

				$this->addLogFile($this->getChrootFolder()."/var/log/openvpn/$fileLog");

			}

		} catch(Exception $err) {

			$this->log->error($err);

			throw $err;

		}

	}

	

}


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


namespace bakuba\kernel\dwService;



use \Exception as Exception;

use darkphp\logger\DwLogger as DwLogger;



use bakuba\kernel\dwNode\DwNode as DwNode;

use bakuba\kernel\dwSocket\dwServer\DwServerConfig as DwServerConfig;



class DwInTftpd extends DwService {

	static private $sLog = null;

	

	public static function create( DwNode $dwNode ) {

		try {

			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");

			if (self::$services == null ) self::$services = array();

			$dwNodeKey = $dwNode->getPreNodeKey();

			

			if ( ! isset( self::$services[ $dwNodeKey ] ) ) self::$services[ $dwNodeKey ] = array();

			

			do { $serviceKey = uniqid(); }while ( isset( self::$services[ $dwNodeKey ][ $serviceKey ] ) ) ;

			

			self::$sLog->trace("dwNodeKey:'$dwNodeKey', serviceKey:'$serviceKey'");

			

			self::$services[ $dwNodeKey ][ $serviceKey ] = new self( $dwNode, $serviceKey );

			

			return self::$services[ $dwNodeKey ][ $serviceKey ];

		} catch(Exception $err) {

			self::$sLog->error($err);

			throw $err;

		}

		return null;

	}

	

	// construct with logger

	private $log;

	public function __construct( DwNode $dwNode ,$serviceKey ) {

		try {

			$this->log = DwLogger::getLogger(__CLASS__);

			$this->log->trace(".");

			parent::__construct( $dwNode, $serviceKey, "in.tftpd", "/srv/in.tftpd", "/etc/srvconf/tftpd-hpa/tftpd-hpa" );

		} catch(Exception $err) {

			$this->log->error($err);

			throw $err;

		}

	}

	public function configure( DwServerConfig $dwServerConfig ) {

		try {

			$this->log->trace("configure ".$this->nom." service");

		} catch(Exception $err) {

			$this->log->error($err);

			throw $err;

		}

	}

}


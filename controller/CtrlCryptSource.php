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

namespace bakuba\controller;

use \Exception as Exception;
use bakuba\kernel\objects\DwKernel as DwKernel;
use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\DwAutoLoad as DwAutoLoad;

class CtrlCryptSource extends \bakuba\kernel\dwCrypto\DwCryptController {
	private $log;

	public function __construct( ) {
		parent::__construct( __CLASS__ );
		
	}
	public function executeCrypt( $dwNode, $message ) {
		$response = null;
		try {
			$this->log->info(".");
			$className = $message;
			$this->log->trace(". ".$dwNode->getNodeData()." ask:$className");
			if ( substr( $className, 0, 7 ) == "file://" )
				$response = DwAutoLoad::getFile( $className );
			else
				$response = DwAutoLoad::getSource( $className );
			
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $response;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

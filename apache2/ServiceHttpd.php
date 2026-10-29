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

namespace bakuba\kernel\apache2;
//use bakuba\kernel\apache2\ServiceHttpd as ServiceHttpd;

use \Exception as Exception;
use bakuba\kernel\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\agartha\DwNode as DwNode;

use bakuba\kernel\dwService\DwService as DwService;
//use bakuba\kernel\apache2\HtAccess as HtAccess;
use bakuba\kernel\dwTask\DwApache2GenerateConf as DwApache2GenerateConf;

use bakuba\kernel\process\server\DwServer as DwServer;
use bakuba\kernel\network\DwPort as DwPort;

class ServiceHttpd extends DwService {
	static private $sLog = null;

	public static function create( DwNode $dwNode ) {
		if (self::$services == null ) self::$services = array();
		$dwNodeKey = $dwNode->getInstanceKey();
		
		if ( ! isset( self::$services[ $dwNodeKey ] ) ) self::$services[ $dwNodeKey ] = array();
		do { $serviceKey = \bakuba\kernel\randomizer\DwRandomizer::randomize( ); }while ( isset( self::$services[ $dwNodeKey ][ $serviceKey ] ) ) ;
		$this-log->trace("dwNodeKey:'$dwNodeKey', serviceKey:'$serviceKey'");
			
			self::$services[ $dwNodeKey ][ $serviceKey ] = new self( $dwNode, $serviceKey );
			
			return self::$services[ $dwNodeKey ][ $serviceKey ];
		} catch(Exception $exception) {
			self::$sLog->error("exception [".self::getStaticClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".self::getStaticClassName()."]");
			throw $error;
		}
		return null;
	}
	
	private $dwApache2GenerateConf;
	public function __construct( DwNode $dwNode, $serviceKey ) {
		parent::__construct( $dwNode, $serviceKey, "httpd", "/srv/httpd", "/var/www/bakuba/vars_services" );
		$this->log->trace(".");
		$this->addLogFile($this->getChrootFolder()."/var/log/apache2.log");
		$this->addLogFile($this->getChrootFolder()."/var/log/apache2ServiceConf.log");
		//$filesLog = ;
		foreach(array("bakuba_http-access.log", "bakuba_http-error.log", "bakuba_https-access.og", "bakuba_https-error.log") as $fileLog) {
			$this->addLogFile($this->getChrootFolder()."/var/log/apache2/darkweb.fr/$fileLog");
		}
		$this->dwApache2GenerateConf = DwApache2GenerateConf::create( $dwNode );
	}
	public function configure( array $apache2SrvConf ) {
		$this->log->trace("configure httpd service");
		$this->dwApache2GenerateConf->generate( $this ,$apache2SrvConf );
	}
}

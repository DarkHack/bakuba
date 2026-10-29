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

namespace bakuba\kernel\agartha;

use \Exception as Exception;

use bakuba\kernel\objects\DwAutoLoad as DwAutoLoad;
use bakuba\kernel\agartha\DwInstall as DwInstall;

class DwCltStarter {
	private $ipSrv = "";
	private $ipClt = "";
	private $preNodeKey = "";
	
	private function getStarterCode ( ) {
		return file_get_contents('https://'.$this->ipSrv.'/Starter.php', false, stream_context_create(array(
			'socket' => array('bindto' => $this->ipClt.':0' )
			,'ssl' => array(
				'verify_peer'   => false
				,'SNI_enabled'	=> true
				,'SNI_server_name' => 'bakuba.darkweb.fr'
				,'peer_name'	=> 'bakuba.darkweb.fr'
				,'cafile'		=> '/etc/darksrv/ssl/_.darkweb.fr/GandiStandardSSLCA2.pem'
				,'verify_depth'	=> 5
			)
			,'http' => array(
				'method'  => 'POST'
				,'header'  => array ( 'Content-type: application/x-www-form-urlencoded', 'Host: bakuba.darkweb.fr' )
				,'content' => http_build_query( array( 'preNodeKey' => $this->preNodeKey ) )
			)
		)));
	}
	public function lunch() {
		try {
			do {
				//echo 'retry in 5s.\\r\\n';
				if ( isset( $code ) )
					sleep(5);
				$code = $this->getStarterCode();
			} while($code == 'wait' );
			$paramStart = json_decode( base64_decode( $code ) );
			include( "data://text/plain;base64,".base64_encode( $paramStart->source ) );
			new DwAutoLoad($paramStart->initCryptSource );
			new DwInstall( $paramStart->initNode );
		} catch( Exception $exception) {
			throw $exception;
		}
		return true;
	}
	public function __construct( $b64params ) {
		try {
			$params = json_decode( base64_decode( $b64params ) );
			$this->ipSrv = $params->ipSrv;
			$this->ipClt = $params->ipClt;
			$this->preNodeKey = $params->preNodeKey;
		} catch( Exception $exception) {
			throw $exception;
		}
	}
}

?>

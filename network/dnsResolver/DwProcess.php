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

namespace bakuba\kernel\network\dnsResolver;
//use bakuba\kernel\network\dnsResolver\DwApplication as DwApplication;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
use \bakuba\kernel\application\DwApplicationRequest as DwApplicationRequest;

class DwProcess extends \bakuba\kernel\process\DwProcessIpc {
	protected $ableToFork = false;
	private $socketSelect = null;
	///*
	public function start(): bool {
		$dwRessources = $this->getDwResources();
		$this->socketSelect = $dwRessources->getSocketSelect();
		//$this->log->trace(".");
		return true;
	}
	
	public function connectApplication(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\iDwApplicationConnexion {
		return new class( $this ,$dwProcessConnexion ,$dwApplicationRequest ) extends \bakuba\kernel\application\aDwApplicationConnexion {
			public function connect() : \bakuba\kernel\application\DwApplicationResponse {
				$this->log->debug( "." );
				$dwDnsResolverRequest = $this->getDwApplicationRequest();
				return new \bakuba\kernel\application\DwApplicationResponse( print_r( $dwDnsResolverRequest ,true ) );
			}
		};
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

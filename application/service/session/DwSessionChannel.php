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

namespace bakuba\kernel\application\service\session;
//use bakuba\kernel\application\service\session\DwSessionChannel as DwSessionChannel;

use darkphp\DwObject as DwObject;
use darkphp\logger\DwLogger as DwLogger;
use darkphp\web\DwWebUrl as DwWebUrl;

use bakuba\kernel\application\session\DwSessionIpKey as DwSessionIpKey;

class DwSessionChannel extends \bakuba\kernel\application\session\DwSessionChannel {
	/*
	public function __construct( \bakuba\kernel\application\session\DwSessionManager $dwSessionManager ,string $channelKey ) {
		parent::__construct( $dwSessionManager ,$channelKey );
		$this->log->trace( "." );
	}
	//*/
	// IP SRV
	public function hasIpSrv( string $ipSrv ) : bool {
		$ipsSrv = $this->getIpsSrv();
		return (
			! is_null($ipsSrv)
		&&	! ( ( $key = array_search( $ipSrv, $ipsSrv ) ) === false )
		);
	}
	private function setIpsSrv( array $ipsSrv ) : void {
		$this->channelDataSet( "ipsSrv" ,$ipsSrv );
	}
	private function getIpsSrv() : ?array {
		return $this->channelDataGet( "ipSrv" );
	}
	// IP CLT
	public function hasIpClt( $ipClt ) : bool {
		$ipsClt = $this->getIpsClt();
		return (
			! is_null($ipsSrv)
		&&	! ( ( $key = array_search( $ipClt, $ipsClt ) ) === false )
		);
	}
	private function setIpsClt( array $ipsClt ) : void {
		$this->channelDataSet( "ipsClt" ,$ipsClt);
	}
	private function getIpsClt() : ?array {
		return $this->channelDataGet( "ipsClt" );
	}
	// IP KEY
	public function hasIpKey( $ipKey ) : bool {
		$ipsKey = $this->getIpsKey();
		return (
			! is_null( $ipsKey )
		&&	! ( ( $key = array_search( $ipKey ,$ipsKey ) ) === false )
		);
	}
	private function setIpsKey( array $ipsKey ) : void {
		$this->channelDataSet( "ipsKey" ,$ipsKey );
	}
	public function getIpsKey() : ?array {
		return $this->channelDataGet( "ipsKey" );
	}
	
	// @return: true if new ip srv or ip clt or ipkey (couple srv/clt)
	protected function addSessionIp( DwSessionIpKey $dwSessionIpKey ) : bool {
		$new=false;
		
		// IP SRV
		$ipSrv = $dwSessionIpKey->getSrvIp();
		if ( ! $this->hasIpSrv( $ipSrv ) ) {
			$ipsSrv = $this->getIpsSrv();
			if ( is_null( $ipsSrv ) )
				$ipsSrv = array();
			$ipsSrv[] = $ipSrv;
			$this->setIpsSrv( $ipsSrv );
			$this->log->trace("add ip srv:{$ipSrv}");
			$new=true;
		}
		// IP CLT
		$ipClt = $dwSessionIpKey->getCltIp();
		if ( ! $this->hasIpClt( $ipClt ) ) {
			$ipsClt = $this->getIpsClt();
			if ( is_null( $ipsClt ) )
				$ipsClt = array();
			$ipsClt[] = $ipClt;
			$this->setIpsClt( $ipsClt );
			$this->log->trace("add ip clt:{$ipClt}");
			$new=true;
		}
		// IP KEY
		$ipKey = $dwSessionIpKey->getIpKey();
		if ( ! $this->hasIpKey( $ipKey  ) ) {
			$ipsKey = $this->getIpsKey();
			if ( is_null( $ipsKey ) )
				$ipsKey = array();
			$ipsKey[] = $ipKey;
			$this->setIpsKey( $ipsKey );
			$this->log->trace("add ip key:{$ipKey}");
			$new=true;
		}
		
		return $new;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

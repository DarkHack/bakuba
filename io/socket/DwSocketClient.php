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
//use bakuba\kernel\io\socket\DwSocketClient as DwSocketClient;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\network\DwPort as DwPort;

//use darkphp\DwObject as DwObject;

class DwSocketClient extends DwSocketIp {
	
	public function __construct( 
		DwSocketSelect $dwSocketSelect
		,\bakuba\kernel\objects\DwCallBack $onConnect
		,\bakuba\kernel\network\DwPort $dwPort
		,int $protocole = \SOL_TCP
	) {
		parent::__construct( $dwSocketSelect ,$onConnect ,$dwPort, $protocole );
	}
	public function connection( \bakuba\kernel\network\DwPort $dwPortSrc = null ) : ?DwSocket {
		$dwSocket = null;
		

		$this->log->trace( $this->sock_family == \AF_INET6 ? "ipv6" : "ipv4" );
		if( ! ( $sock = \socket_create( $this->sock_family ,$this->sock_type ,$this->sock_protocole ) ) > 0 ) {
			$errNo = \socket_last_error($sock);
			$sockErrorStr= \socket_strerror( $errNo );
			throw new \Exception(
				"failed to socket_create[{$errNo}]: {$sockErrorStr}"
			);
		} else {
			$dwIp = $this->dwPort->getIp();
			if ( is_object( $dwPortSrc ) ) {
				$dwIpSrc = $dwPortSrc->getIp();
				\socket_bind( $sock ,$dwIpSrc->getIpAddress() ,$dwPortSrc->getPort() ); // IP & port source voulus
				$dwInterface =$dwIpSrc->getDwCIDR()->getDwInterface();
			} else
				$dwInterface =$dwIp->getDwCIDR()->getDwInterface();
			
			$addr=$dwIp->getIpAddress();
			$port=$this->dwPort->getPort();
			
			if( ! ( $ret = \socket_connect ( $sock ,$addr ,$port ) ) > 0 ) {
				$errNo = \socket_last_error( $sock );
				$sockErrorStr= \socket_strerror( $errNo );
				throw new \Exception(
					"failed socket_connect[{$errNo}]:{$sockErrorStr}"
				);
			} else {
				$this->log->debug("[".$addr.":".$port."]:connected");
				socket_set_nonblock( $sock );
				$dwSocket = new DwSocket( $this, $dwInterface ,$sock );
			}
		}
		return $dwSocket;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

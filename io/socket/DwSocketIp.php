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

namespace  bakuba\kernel\io\socket;
//use bakuba\kernel\io\socket\DwSocketServer as DwSocketServer;

use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

class DwSocketIp extends \bakuba\kernel\io\DwResourceServer {
	
	protected function getDwSocketSelect() : DwSocketSelect {
		return $this->getDwResourceSelect();
	}
	
	protected $dwPort = null;
	
	protected $sock_family = null;
	protected $sock_protocole = null;
	protected $sock_type = null;
	
	public function __construct(
		DwSocketSelect $dwSocketSelect
		,\bakuba\kernel\objects\DwCallBack $onConnect
		,\bakuba\kernel\network\DwPort $dwPort
		,int $protocole = \SOL_TCP
	) {
		$this->dwPort=$dwPort;
		$this->sock_protocole=$protocole;
		
		switch( $this->sock_protocole ) {
			case \SOL_TCP :
				$this->sock_type=\SOCK_STREAM;
				break;
			case \SOL_UDP :
				$this->sock_type=\SOCK_DGRAM;
				//$this->sock_type=\SOCK_SEQPACKET;
				break;
			default :
				throw new \Exception("unknow protocole:$protocole");
				break;
		}
		$dwIp = $this->dwPort->getIp();
		
		switch( $dwIp->getClassName() ) {
			case \bakuba\kernel\network\DwIp6::getStaticClassName() :
				$this->sock_family=\AF_INET6;
				break;
			case \bakuba\kernel\network\DwIp4::getStaticClassName() :
				$this->sock_family=\AF_INET;
				break;
			default :
				throw new \Exception("unknow dwIp:{$dwIp->getClassName()}");
				break;
		}
		
		parent::__construct( $dwSocketSelect ,$onConnect );
	}
	
	public function init(  ) : void {
		$this->log->trace("[{$this->toString()}]");
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

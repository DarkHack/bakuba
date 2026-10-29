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
//use bakuba\kernel\application\service\session\aDwApplicationSessionManager as aDwApplicationSessionManager;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\application\DwApplicationRequest as DwApplicationRequest;

use bakuba\kernel\session\DwSessionIpKey as DwSessionIpKey;

use bakuba\kernel\services\DwServiceRequest as DwServiceRequest;

abstract class aDwApplicationSessionManager
	extends \bakuba\kernel\session\application\aDwApplicationSessionManager 
	implements iDwApplicationSessionManager {
	///*
	protected function __construct( \bakuba\kernel\application\service\aDwApplication $parent ,string $urlApp ) {
		parent::__construct( $parent ,$urlApp );
		$this->log->trace("[{$this->toString()}]");
	}
	public function getDwServerProcess() : \bakuba\kernel\services\application\DwApplication {
		return $this->parent->getDwServerProcess();
	}
	//*/
	/*
	protected function init( ): void {
		//\bakuba\kernel\session\application\aDwApplicationSessionManager::init();
		parent::init();
		$this->log->trace("[{$this->toString()}]");
	}
	//*/
	public function getIpKey( DwServiceRequest $dwServiceRequest ) : int {
		$dwRequest = $dwServiceRequest->getDwRequest();
		$dwSrvIp = $dwRequest->getSrvIp();
		$dwSrvPort = $dwRequest->getSrvPort();
		$dwCltIp = $dwRequest->getCltIp();
		$dwCltPort = $dwRequest->getCltPort();
		
		$ipKey = $dwSrvIp->toString().":".$dwSrvPort->toString()."-".$dwCltIp->toString();
		return \crc32($ipKey);
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

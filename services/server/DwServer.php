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

namespace bakuba\kernel\services\server;
//use bakuba\kernel\services\server\DwServer as DwServer;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\services\application\DwApplication as DwApplication;

class DwServer extends \darkphp\DwObject {
	public function __construct( DwApplication $dwApplication ) {
		parent::__construct( $dwApplication );
	}
	
	public function getDwSessionIp( \bakuba\kernel\network\DwPort $dwPort ) : \bakuba\kernel\session\DwSessionIp {
		return $this->getDwApplication()->getDwSessionIp( $dwPort );
	}
	public function getDwApplication() : DwApplication {
		return $this->parent;
	}
	public function getDwProcessResources() : DwApplication {
		return $this->parent;
	}
	public function getDwResources() : \bakuba\kernel\process\DwResources {
		return $this->parent->getDwResources();
	}
	
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

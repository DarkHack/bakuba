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

namespace bakuba\kernel\process\child\windows;
//use bakuba\kernel\process\child\windows\DwCmd as DwCmd;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwWinExe extends \darkphp\DwObject {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className=$this->getClassName();
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToTopName( $className )
				,\darkphp\source\DwSource::classNameToTopName( $className )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	public function getMyLog() : \darkphp\logger\iDwLogger {
		return $this->log;
	}
	public function getMyResources() : \bakuba\kernel\process\DwResources {
		return $this->getMyAgent()->getMyResources();
	}
	public function getMyAgent() : \bakuba\kernel\process\child\windows\DwWinAgent {
		return $this->parent;;
	}
	
	protected function __construct( 
		\bakuba\kernel\process\child\windows\DwWinAgent $dwWinAgent
	) {
		parent::__construct( $dwWinAgent );
		$this->log->trace( "." );
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

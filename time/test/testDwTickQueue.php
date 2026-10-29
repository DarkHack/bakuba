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

namespace bakuba\kernel\time\test;
//use bakuba\kernel\time\test\testDwObjectTick as testDwObjectTick;

use bakuba\kernel\time\DwObjectTick as DwObjectTick;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\time\DwChrono as DwChrono;
use bakuba\kernel\time\DwObjectTick as DwObjectTick;

class testDwObjectTick extends \darkphp\DwObject {
	function __construct( \darkphp\DwObject $parent = null ) {
		if ( \is_null( $parent ) ) {
			$dwLoggerEcho = new \bakuba\kernel\logger\standalone\DwLoggerEcho( $this );
			$this->setDebug( $dwLoggerEcho );
		}
		parent::__construct( $parent );
		
		$this->log->trace( "." );
		\bakuba\kernel\time\DwTickQueue::run( \bakuba\kernel\time\DwTickQueue::test( $this->log ) );
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

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

namespace bakuba\kernel\objects\test;
//use bakuba\kernel\objects\test\testDwCallback as testDwCallback;

class testDwCallback extends \bakuba\kernel\test\testUnit {
	private $cpt = null;
	function __construct( \bakuba\kernel\test\testUnits $parent ) {
		parent::__construct( $parent );
		$this->log->trace( "." );
		$this->cpt=0;
		$dwTest = new \bakuba\kernel\objects\DwCallBack( array ( &$this ,'emptyFunction' ) );
		\bakuba\kernel\objects\DwCallBack::test(
			$this->log
			,$dwTest
		);
		$this->log->info( "\r\n\tcpt:{$this->cpt}" );
		//$this->log->info( "\r\n\t".\debug_zval_dump( $dwTest )."\r\n" );
		unset( $dwTest );
	}
	public function emptyFunction() {
		$this->cpt++;
		//$this->log->info( "cpt:{$this->cpt}" );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>




?>

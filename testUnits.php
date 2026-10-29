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

namespace bakuba\kernel;
//use bakuba\kernel\testUnits as testUnits;

class testUnits extends \darkphp\DwObject {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) ) {
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToFirstSectionName( $this->getClassName() )
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		} else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	function __construct( \darkphp\DwObject $parent = null ) {
		parent::__construct( $parent );
		$this->log->trace( "." );
		
		//\set_error_handler( array( &$this->log ,'error_handle' ) );
		//\set_exception_handler( array( &$this->log ,'throwable' ) );
		
	}
	public function lunchTests() : void {
		try {
			$this->log->trace("\r\n\texecute TESTS -----------------------");
			$this->log->info( \darkphp\DwObject::listInstancesCreated() );
			$testUnits = new test\testUnits( $this );
			$testUnits->lunchTests();
			unset( $testUnits );
			$this->log->trace("\r\n\tend execute TESTS -----------------------");
			$this->log->info( \darkphp\DwObject::listInstancesCreated() );
		} catch( \Exception $exception ) {
			//$this->log->throwable( $exception );
			throw $exception;
		} catch( \Error $error ) {
			//$this->log->throwable( $error );
			throw $error;
		}
	}
	function __destruct() {
		$this->log->trace( "." );
		\restore_error_handler();
		\restore_exception_handler();
		parent::__destruct();
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

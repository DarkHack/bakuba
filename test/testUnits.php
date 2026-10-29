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

namespace bakuba\kernel\test;
//use bakuba\kernel\test\testUnits as testUnits;

use darkphp\source\DwSource as DwSource;

class testUnits extends test {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) ) {
			$topName = \darkphp\source\DwSource::classNameToTopName( $this->getClassName() );
			$sectionName = \darkphp\source\DwSource::classNameToSectionName( $this->getClassName() );
			$toRemove="bakuba\\kernel\\";
			$classSection = \substr( $sectionName ,\strlen( $toRemove ) ,\strlen( $sectionName ) - \strlen( $toRemove )  );
			$sectionC = \str_replace( "\\", "_" ,$classSection );
			$classSection = \str_replace( "_test" ,"" ,$sectionC );
			$section = \str_replace( "_" ,"/" ,$classSection );
			
			if ( empty( $section ) )
				$this->log = $iDwLogger->upTo( $this ,"_{$topName}" );
			else
				$this->log = $iDwLogger->upTo( $this ,$section ,$topName );
			
			$this->log->info( "{$sectionName} {$classSection} up {$section} {$topName}" );
		} else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	/*
	function __construct( \darkphp\DwObject $parent ) {
		parent::__construct( $parent );
		$this->log->trace( "." );
	}
	//*/
	public function lunchTests() : void {
		$unitsToTest = array();
		foreach( array(
			"serializable"
//			,"logger\\log4dw"
			,"objects"
			,"serializable\\json"
			,"time"
			,"time\\timer"
		) as $unit ) {
			$className="\\bakuba\\kernel\\".$unit."\\test\\testUnits";
			$unitsToTest[]=$className;//::getStaticClassName();
		}
		$this->log->info( $this->tryTestUnits( $unitsToTest ) );
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

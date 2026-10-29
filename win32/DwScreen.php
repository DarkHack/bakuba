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

namespace bakuba\kernel\win32;
//use bakuba\kernel\win32\DwScreen as DwScreen;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

// DwScreen
class DwScreen extends DwObject {
	private $nbLignes = null;
	public function setLines( int $nbLines ) {
		$this->nbLignes = $nbLines;
	}
	public function getLines( ) : int {
		return $this->nbLignes;
	}
	private $dataToPrint = null;
	public function setDataToPrint( array $dataToPrint ) {
		$this->dataToPrint = $dataToPrint;
	}
	public function getDataToPrint( ) : ?array {
		return $this->dataToPrint;
	}
	private $maxLignes = null;
	public function __construct( DwSubScreen $dwSubScreen ,int $nbMaxLines ) {
		$this->maxLignes = $nbMaxLines;
		$this->setLines( $nbMaxLines );
		$dwSubScreen->add( $this );
		parent::__construct( $dwSubScreen );
	}
	public function getColonnes() : int {
		return $this->parent->getColonnes();
	}
	public function print() : array {
		if ( \is_null( $this->dataToPrint ) )
			$this->dataToPrint = array( 0 => "Nothing to print", 2 => "Really nothing", 3 => "No more");
		//$arrStr = explode( \PHP_EOL, DwObject::print_r(  ) );
		$toPrint = array();
		$cpt=0;
		foreach( $this->dataToPrint as $strToPrint ) {
			$idx = $cpt++;
			$toPrint[ $idx ]=$strToPrint;
		}
		
		$totalData=array();
		for ( $idx=0 ;$idx< $this->getLines() ;$idx++ ) {
			$nbColonnes = $this->getColonnes();
			$restant = $nbColonnes;
			$strLayout="";
			if ( isset( $toPrint[$idx] ) ) {
				$strToShow = $toPrint[$idx];
				$subStrLen = \strlen( $strToShow );
				if ( $subStrLen >= $restant )
					$subStr = \substr( $strToShow ,0 ,$restant -1 );
				else
					$subStr = $strToShow;
				
				if ( $idx==0 || $idx==$this->maxLignes || $idx==$this->getLines() ) {
					if ( \strlen( $subStr ) % 2 > 0 )
						$subStr .="_";
					$restant-=\strlen( $subStr );
					$toLoad=\intval( $restant / 2 );
					if ( $toLoad > 0 )
						$strLayout.= \str_repeat( '-', $toLoad ).$subStr. \str_repeat( '-', $toLoad );
					else
						$strLayout.=$subStr;
				} else 
					$strLayout.=$subStr;
			} elseif ( $idx==0 || $idx==$this->maxLignes || $idx==$this->getLines() )
				$strLayout.= \str_repeat( '-', $restant );
			
			$restant = $this->getColonnes() - \strlen( $strLayout );
			if ( $restant>=1 )
				$strLayout.=\str_repeat( ' ', $restant  );
			
			$strLayout.="I";
			$totalData[] = $strLayout;
		}
		//$totalData[]=\str_repeat( '-', $nbColonnes );
		//$this->log->info( $totalData );
		return $totalData;
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

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
//use bakuba\kernel\win32\DwSubScreen as DwSubScreen;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

// DwSubScreen
class DwSubScreen extends DwObject {
	protected $nbColonnes = null;
	public function getColonnes() : int {
		return $this->nbColonnes;
	}
	private $screens = array();
	public function add( DwScreen $dwScreen ) {
		$this->screens[] = $dwScreen;
	}
	
	public function __construct( DwCmdScreenMain $dwCmdScreenMain ,int $nbColonnes ,int $nbLines ) {
		$this->nbColonnes = $nbColonnes;
		$this->nbLines = $nbLines;
		parent::__construct( $dwCmdScreenMain );
	}
	private $nbLines = null;
	public function getLines() : int {
		return $this->nbLines;
	}
	public function print() : array {
		$nbScreens = \count( $this->screens );
		$nbLines = $this->getLines();
		if ( $nbScreens <= 0 || $nbLines <= 0 ) {
			$this->log->warn(" screen without line or screens");
			return array();
		}
		$lastLines = $nbLines % $nbScreens;
		$nbLinesByScreen = \intval( $nbLines / $nbScreens );
		
		foreach ( $this->screens as $dwScreen )
			$dwScreen->setLines( $nbLinesByScreen );
		// for the last screen
		if ( $lastLines > 0 )
			$dwScreen->setLines( $nbLinesByScreen + $lastLines );
		
		$totalData=array();
		$idx = -1;
		foreach ( $this->screens as $dwScreen ) {
			foreach( $dwScreen->print() as $strToShow ) {
				$idx++;
				$restant = $this->getColonnes();
				$strLayout="";
				
				$subStrLen = \strlen( $strToShow );
				if ( $subStrLen > $restant )
					$subStr = \substr( $strToShow ,0 ,$restant );
				else
					$subStr = $strToShow;
				
				if ( $idx==0 || $idx==$this->getLines() ) {
					if ( \strlen( $subStr ) % 2 > 0 )
						$subStr .="h";
					
					$restant-=\strlen( $subStr );
					$toLoad=\intval( ( $restant - 1) / 2 );
					if ( $toLoad > 0 )
						$strLayout.= \str_repeat( 'g', $toLoad ).$subStr. \str_repeat( 'g', $toLoad );
					else
						$strLayout.=$subStr;
				} else
					$strLayout.=$subStr;
				
				$restant = $this->getColonnes() - \strlen( $strLayout );
				if ( $restant>1 )
						$strLayout.=\str_repeat( 'c', $restant - 1  );
				
				$strLayout.="!";
				$totalData[] = $strLayout;
				/*
				
				$subStrLen = \strlen( $strToShow );
				if ( $subStrLen >= $restant )
					$subStr = \substr( $strToShow ,0 ,$restant -1 );
				else
					$subStr = $strToShow;
				$strLayout="";
				if ( $idx == 0 || $idx==$this->getLines() ) {
					$restant-=\strlen( $subStr );
					$toLoad=\intval( ( $restant - 1) / 2 );
					if ( $toLoad > 0 )
						$strLayout.= \str_repeat( '_' ,$toLoad ).$subStr.\str_repeat( '_' ,$toLoad );
					else
						$strLayout.=$subStr;
				} else 
					$strLayout.=$subStr;
				
				$toLoad=$this->getColonnes() - \strlen( $strLayout );
				if ( $toLoad > 0 )
					$strLayout.= \str_repeat( 'a' ,$toLoad );
				
				$totalData[] = $strLayout;//\substr( $subStr ,0 ,$restant );
				//*/
			}
		}
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

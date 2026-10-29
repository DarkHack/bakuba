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

namespace bakuba\kernel\autoload;
//use bakuba\kernel\autoload\DwSource as DwSource;

class DwSource {
	static private $sLog = null;
	private $clearSource = null;
	private $dwNameSpace = null;
	
	public function __construct( $clearSource ) {
		//echo "DwSource :".strlen($clearSource)."\r\n";
		$this->clearSource= \bakuba\kernel\dwEncoding\Encoding::UTF8FixWin1252Chars($clearSource); 
		//echo "DwSource :".strlen($this->clearSource)."\r\n";
		$nameSpace = $this->readNameSpace();
		if ( is_string( $nameSpace ) ) {
			//echo "DwSource nameSpace:$nameSpace\r\n";
			$this->dwNameSpace = DwNameSpace::getNameSpace( $nameSpace );
			//echo "DwSource use:".print_r( $this->readUses(), true )."\r\n";
			//echo "DwSource class:".print_r( $this->readClass(), true )."\r\n";
			//echo "DwSource function:".print_r( $this->readFunctions(), true )."\r\n";
		}
	}
	public function toString() {
		return $this->clearSource;
	}
	private function readNameSpace( ) {
		$nameSpace = null;
		$matches = array();
		if ( preg_match_all(
			'/^.*namespace[\s]*(.*);[\s\S]*$/m'
			,$this->clearSource
			,$matches
			,PREG_OFFSET_CAPTURE
		) ){  $nameSpace = $matches[1][0][0];}
		//else echo "no namespace in \r\n".$this->clearSource."\r\n";
		return $nameSpace;
	}//^[\s]*class[\s]*(.*){[\s]*$
	private function readUses( ) {
		$uses = array("\Exception" => "Exception");
		$matches = array();
		if ( preg_match_all(
			'/^[\s]*use[\s]*(.*)[\s]as[\s]*(.*);[\s]*$/m'
			,$this->clearSource
			,$matches
			,PREG_OFFSET_CAPTURE
		) ) {
			for ($cpt = 0;$cpt < sizeof($matches[0]);$cpt++) {
				$uses[$matches[1][$cpt][0]]=$matches[2][$cpt][0];
			}
		} //else echo "no match use in ".$this->clearSource;
		return $uses;
	}
	private function readClass( ) {
		$className = "";
		$matches = array();
		if ( preg_match_all(
			'/^[\s]*class[\s]*(.*){[\s]*$/m'
			,$this->clearSource
			,$matches
			,PREG_OFFSET_CAPTURE
		) ) {
			$className = $matches[1][0][0];
		} else {
			$matches = array();
			if ( preg_match_all(
				'/^[\s]*interface[\s]*(.*){[\s]*$/m'
				,$this->clearSource
				,$matches
				,PREG_OFFSET_CAPTURE
			) ) {
				$className = "(interface)".$matches[1][0][0];
			}
		}
		return $className;
	}
	private function readFunctions( ) {
		$functions = array();
		$matches = array();
		if ( preg_match_all(
			'/^.*[\s]+function[\s]+(.*)[\s]*\({1}.*\){1}[\s]*{+.*$/m'
			,$this->clearSource
			,$matches
			,PREG_OFFSET_CAPTURE
		) ) {
			for ($cptFunction = 0;$cptFunction < sizeof($matches[1]);$cptFunction++) {
				$functions[]=$matches[1][$cptFunction][0];
			}
		}
		return $functions;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

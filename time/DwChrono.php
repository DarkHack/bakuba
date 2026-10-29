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

namespace bakuba\kernel\time;
//use bakuba\kernel\time\DwChrono as DwChrono;

class DwChrono extends \darkphp\serializable\aDwSerializable {
	private $timeMeasures = array();
	
	public function __construct( \darkphp\serializable\aDwNoSerializable $dwParentNoSerializable ) {
		parent::__construct( $dwParentNoSerializable );
		$this->tick();
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
		parent::__destruct();
	}
	//*/
	/*
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		$this->addtoSerialize("timeMeasures");
	}
	*/
	
	// ppET production :
	const MAX_MEASURES = 10000; // Choisis la taille adaptée
	public function tick() {
		if (count($this->timeMeasures) >= self::MAX_MEASURES) {
			array_shift($this->timeMeasures); // Retire le plus ancien si max atteint
		}
		$this->timeMeasures[] = DwTime::fSecondeToiMicroSeconde(
			\microtime(true)// as float
		);
		//array_push( $this->timeMeasures ,\microtime( true ) );
	}
	
	public function printStats() : string {
		$strStats = "";
		
		$lastTimeMeasure = null;
		$deltaTime = null;
		$minDeltaTime = null;
		$maxDeltaTime = null;
		$totalTime = 0;
		$deltaFrequency = null;
		$totalFrequency = null;	
		//us => 10^-6 s
		foreach( $this->timeMeasures as $cpt => $iTimeMeasure ) {
			if ( is_null( $lastTimeMeasure ) ) {
				$strStats.="First Tick:".date(
					"Y/M/d H:i:s.u"
					, DwTime::iMicroSecondeToiSeconde( $iTimeMeasure )
				)."\r\n";
			} else {
				$deltaTime = $iTimeMeasure - $lastTimeMeasure;
				$totalTime += $deltaTime;
				
				if ( is_null( $minDeltaTime ) || $deltaTime < $minDeltaTime )
					$minDeltaTime = $deltaTime;
				if ( is_null( $maxDeltaTime ) || $deltaTime > $maxDeltaTime )
					$maxDeltaTime = $deltaTime;
				
				//$strStats.="[".sprintf( " %2d" ,$cpt )."] ";
				//$strStats.="{$this->printMeasure( $deltaTime )}\r\n";
			}
			$lastTimeMeasure = $iTimeMeasure;
		}
		if ( $cpt > 1 ) {
			if ( ! is_null( $minDeltaTime ) ) {
				$strStats.="MIN      --------------------------------------\r\n";
				$strStats.=$this->printMeasure( $minDeltaTime )."\r\n";
			}
			if ( ! is_null( $maxDeltaTime ) ) {
				$strStats.="MAX      --------------------------------------\r\n";
				$strStats.=$this->printMeasure( $maxDeltaTime )."\r\n";
			}
			if ( ! is_null( $totalTime ) ) {
					$cpt++;
					$strStats.="AVERAGE  [{$cpt}]------------------------------\r\n";
					$strStats.=$this->printMeasure( $totalTime/$cpt )."\r\n";
			}
		}
		$strStats.="TOTAL    --------------------------------------\r\n";
		$strStats.=$this->printMeasure( $totalTime )."\r\n";
		
		return $strStats;
	}
	public function printMeasure( int $iMeasure ) : string {
		$strStats="";
		if ( $iMeasure >= 0 ) {
			$strConv = " %4.2f";
			if ( $iMeasure > 0 )
				$fFrequency = 1 / $iMeasure;
			else
				$fFrequency = 0;
			//echo $iMeasure;
			if ( $iMeasure > 1000000 )
				$strStats=sprintf( $strConv ,$iMeasure/1000000 )." s	|"
					.sprintf( $strConv ,$fFrequency * 1000000)." Hz	|";
			elseif( $iMeasure > 1000 )
				$strStats=sprintf( $strConv ,$iMeasure/1000)." ms	|"
					.sprintf( $strConv ,$fFrequency * 1000000 )." Hz	|";
			else
				$strStats=sprintf( $strConv ,$iMeasure)." us	|"
					.sprintf( $strConv ,$fFrequency* 1000)." KHz	|";
		}
		return $strStats;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? 
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__
			: __CLASS__
		;
	}
}

?>

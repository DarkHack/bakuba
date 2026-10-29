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
//use bakuba\kernel\time\DwChronoStartStop as DwChronoStartStop;

class DwChronoStartStop extends DwChrono {
	private $tickStart = null;
	private $timeMeasures = array();
	
	public function __construct( \darkphp\serializable\aDwNoSerializable $dwParentNoSerializable ) {
		parent::__construct( $dwParentNoSerializable );
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
	public function start() {
		$this->tick();
		$this->tickStart = DwTime::fSecondeToiMicroSeconde(
			\microtime( true ) // as float
		);
	}
	public function stop() {
		$deltaTime = ( DwTime::fSecondeToiMicroSeconde( \microtime( true ) ) -  $this->tickStart );
		//if ( $deltaTime==0 )
		//	echo "deltaTime0 :$deltaTime\r\n";
		$this->timeMeasures[] = $deltaTime;
		//array_push(
		//	$this->timeMeasures
		//	,\( \microtime( true ) -  $this->tickStart )
		//);
		$this->tickStart = null;
	}
	
	public function printStats() : string {
		$strStats = "";
		$strStats.="=== Execution Stats /Stats betwen Start/Stop\r\n";
		//$strStats.="[time]	|[last]	|[frequence]\r\n";
		$deltaTime = null;
		$minDeltaTime = null;
		$maxDeltaTime = null;
		$totalTime = 0;
		//us => 10^-6 s
		foreach( $this->timeMeasures as $cpt => $deltaTime ) {
			$totalTime += $deltaTime;
			if ( is_null( $minDeltaTime ) || $deltaTime < $minDeltaTime )
				$minDeltaTime = $deltaTime;
			if ( is_null( $maxDeltaTime ) || $deltaTime > $maxDeltaTime )
				$maxDeltaTime = $deltaTime;
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
		
		$strStats.="=== Call Stats / Stats betwen 2*Start\r\n";
		$strStats.=parent::printStats();
		
		return $strStats;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return __CLASS__;
	}
}

?>

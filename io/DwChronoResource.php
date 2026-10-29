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

namespace bakuba\kernel\io;
//use bakuba\kernel\io\DwChronoResource as DwChronoResource;

use \bakuba\kernel\time\DwTime as DwTime;

class DwChronoResource {
	private $last = null;
	private $startReceive = null;
	private $stopReceive = null;
	private $timeMeasuresReceive = array();
	private $startEmet = null;
	private $stopEmet = null;
	private $timeMeasuresEmet = array();
	
	public function __construct( ) {
		//parent::__construct();
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
		//parent::__destruct();
	}
	//*/
	public function receiveStart() {
		$this->last = DwTime::fSecondeToiMicroSeconde(
			\microtime( true ) // as float
		);
		$this->startReceive = $this->last;
	}
	public function receive( int $count = 0 ) {
		$last = DwTime::fSecondeToiMicroSeconde( \microtime( true ) );
		$deltaTime = ( $last - $this->last );
		$this->last = $last;
		
		$this->timeMeasuresReceive[] = array(
			'count' => $count
			,'deltaTime' => $deltaTime
		);
	}
	public function receiveStop() {
		$this->stopReceive = DwTime::fSecondeToiMicroSeconde( \microtime( true ) );
	}
	public function receiveTimeSpend() {
		if ( ! isset( $this->startReceive ) || \is_null( $this->startReceive ) )
			return 0;
		if ( ! isset( $this->stopReceive ) || \is_null( $this->stopReceive ) )
			$stopReceive = DwTime::fSecondeToiMicroSeconde( \microtime( true ) );
		else
			$stopReceive = $this->stopReceive;
		return $this->stopReceive - $this->startReceive;
	}
	
	public function emetStart() {
		$this->last = DwTime::fSecondeToiMicroSeconde(
			\microtime( true ) // as float
		);
		$this->startEmet = $this->last;
	}
	public function emet( int $count = 0 ) {
		$last = DwTime::fSecondeToiMicroSeconde( \microtime( true ) );
		$deltaTime = ( $last - $this->last );
		$this->last = $last;
		
		$this->timeMeasuresEmet[] = array(
			'count' => $count
			,'deltaTime' => $deltaTime
		);
	}
	public function emetStop() {
		$this->stopEmet = DwTime::fSecondeToiMicroSeconde( \microtime( true ) );
	}
	public function emetTimeSpend() {
		if ( ! isset( $this->startEmet ) || is_null( $this->startEmet ) )
			return 0;
		if ( ! isset( $this->stopEmet ) || is_null( $this->stopEmet ) )
			$stopEmet = DwTime::fSecondeToiMicroSeconde( \microtime( true ) );
		else
			$stopEmet = $this->stopEmet;
		return $stopEmet -$this->startEmet;
	}
	
	public function printStats() : string {
		$strStats = "";
		//$strStats.="[time]	|[last]	|[frequence]\r\n";
		$deltaTime = null;
		$minDeltaTime = null;
		$maxDeltaTime = null;
		$totalTime = 0;
		//us => 10^-6 s
		foreach( $this->timeMeasures as $cpt => $timeMeasure ) {
			$count = $timeMeasure['count'];
			$deltaTime = $timeMeasure['deltaTime'];
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

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

namespace bakuba\kernel\time\timer\test;
//use bakuba\kernel\time\test\testDwObjectTimer as testDwObjectTimer;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\time\DwChrono as DwChrono;
use bakuba\kernel\time\DwChronoStartStop as DwChronoStartStop;
use bakuba\kernel\time\timer\DwObjectTimer as DwObjectTimer;
use bakuba\kernel\time\timer\DwTimer as DwTimer;

class testDwObjectTimer extends \bakuba\kernel\test\testUnit {
	function __construct( \bakuba\kernel\test\testUnits $parent ) {
		parent::__construct( $parent );
		
		//$dwTest = new \bakuba\kernel\time\DwObjectTick( $this, new \bakuba\kernel\time\DwTickQueue( $this ) );
		//$dwTest->setDebug( $this->log );
		/*
		\bakuba\kernel\time\DwObjectTick::test(
			$this->log
			,$dwTest
		);
		//*/
		$dwObjectTimer = new DwObjectTimer( $this , new \bakuba\kernel\time\timer\DwTimers( $this ) );
		$myLog = $this->log;
		$dwObjectTimer->setDebug( $myLog );
		
		$dwChrono = new DwChrono( $this );
		$dwObjectTimer->enableStats( $dwChrono );
		
		$dwChronoTest = new DwChrono( $this );
		$myLog->trace( "test" );
		//\bakuba\kernel\time\DwObjectTick::test( $dwObjectTimer );
		$dwChronoTest->tick();
		
		$dwChronoStartStop = new DwChronoStartStop( $this );
		$cpt=0;
		$dwCallBack = null;
		$dwCallBack = new DwCallBack(
			function(
				DwChronoStartStop $dwChronoStartStop
				,DwObjectTimer $dwObjectTimer
			) use (  &$dwCallBack ,&$cpt ,&$myLog ) {
				if ( $cpt > 99 ) {
					$dwObjectTimer->cancelCallBack( $dwCallBack );
					$myLog->trace( " DwCallBack 1:==========================" );
					//$myLog->trace( $dwCallBack->printHandle(false) );
					$myLog->trace( $dwChronoStartStop->printStats() );
				} 
				$cpt++;
			}
			,$dwChronoStartStop
			,$dwObjectTimer
		);
		$dwCallBack->enableStats( $dwChronoStartStop );
		$dwObjectTimer->addPeriodicTimer( 
			0.001 // every 1ms
			,$dwCallBack
		);
		$dwChronoStartStop2 = new DwChronoStartStop( $this );
		$cpt2=0;
		$dwCallBack2 = null;
		$dwCallBack2 = new DwCallBack(
			function(
				DwChronoStartStop $dwChronoStartStop
				,DwObjectTimer $dwObjectTimer
			) use (  &$dwCallBack2 ,&$cpt2 ,&$myLog ) {
				//echo "$cpt2|";
				if ( $cpt2 > 99 ) {
					$dwObjectTimer->cancelCallBack( $dwCallBack2 );
					$myLog->trace( " DwCallBack 2:==========================" );
					//echo $dwCallBack->printHandle(false);
					$myLog->trace( $dwChronoStartStop->printStats() );
				}
				$cpt2++;
			}
			,$dwChronoStartStop2
			,$dwObjectTimer
		);
		$dwCallBack2->enableStats( $dwChronoStartStop2 );
		$dwObjectTimer->addPeriodicTimer(
			0.0005 // every 500us
			,$dwCallBack2
		);
		$this->log->trace( "run" );
		$dwObjectTimer->addTimer(
			10 // in 10s
			,new DwCallBack( function () use ( &$myLog ) { $myLog->trace( "ten secondes." ); } )
		);
		DwObjectTimer::run( $dwObjectTimer );
		
		$dwChronoTest->tick();
		$myLog->trace( "end" );
		$myLog->trace( $dwChrono->printStats() );
		$myLog->trace( "==============================" );
		$myLog->trace( $dwChronoTest->printStats() );
		$myLog->trace("end test");
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}

}
?>

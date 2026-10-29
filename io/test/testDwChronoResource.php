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

$bakubaKernelDir = dirname(__FILE__).'/../../';
require_once "$bakubaKernelDir/autoload/startFile.php";

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\time\DwChrono as DwChrono;
use bakuba\kernel\time\DwChronoStartStop as DwChronoStartStop;
use bakuba\kernel\time\timer\DwObjectTimer as DwObjectTimer;
use bakuba\kernel\time\timer\DwTimer as DwTimer;
use bakuba\kernel\io\DwResources as DwResources;
use bakuba\kernel\io\stream\DwStreamSelect as DwStreamSelect;


$dwObjectTimer = new DwObjectTimer();

$dwResources = new DwResources( $dwObjectTimer );
$dwStreamSelect = new DwStreamSelect( $dwResources );

//$dwStream->setOnReaded( $onReaded );
//$dwStreamSelect->streamRead( $dwStream );

$dwObjectTimer->setDebug( \bakuba\kernel\logger\echolog\DwLogger::getLogger( $dwObjectTimer ) );

$dwChrono = new DwChrono();
$dwObjectTimer->enableStats( $dwChrono );

$dwChronoTest = new DwChrono();
echo "test\r\n";
//\bakuba\kernel\time\DwObjectTick::test( $dwObjectTimer );
$dwChronoTest->tick();

$dwChronoStartStop = new DwChronoStartStop();
$cpt=0;
$dwCallBack = null;
$dwCallBack = new DwCallBack(
	function(
		DwChronoStartStop $dwChronoStartStop
		,DwObjectTimer $dwObjectTimer
	) use (  &$dwCallBack ,&$cpt ) {
		if ( $cpt > 99 ) {
			$dwObjectTimer->cancelCallBack( $dwCallBack );
			echo " DwCallBack 1:==========================\r\n";
			//echo $dwCallBack->printHandle(false);
			echo $dwChronoStartStop->printStats();
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

$dwChronoStartStop2 = new DwChronoStartStop();
$cpt2=0;
$dwCallBack2 = null;
$dwCallBack2 = new DwCallBack(
	function(
		DwChronoStartStop $dwChronoStartStop
		,DwObjectTimer $dwObjectTimer
	) use (  &$dwCallBack2 ,&$cpt2 ) {
		//echo "$cpt2|";
		if ( $cpt2 > 99 ) {
			$dwObjectTimer->cancelCallBack( $dwCallBack2 );
			echo " DwCallBack 2:==========================\r\n";
			//echo $dwCallBack->printHandle(false);
			echo $dwChronoStartStop->printStats();
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
echo "run\r\n";
$dwObjectTimer->addTimer(
	10 // in 10s
	,new DwCallBack( function () { echo "ten secondes.\r\n"; } )
);
\bakuba\kernel\time\DwObjectTick::run( $dwObjectTimer );

$dwChronoTest->tick();
echo "end\r\n";
echo $dwChrono->printStats();
echo "==============================\r\n";
echo $dwChronoTest->printStats();
?>

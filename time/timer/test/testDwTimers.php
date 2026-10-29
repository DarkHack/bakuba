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

$bakubaKernelDir = dirname(__FILE__).'/../../../';
require_once "$bakubaKernelDir/autoload/startFile.php";

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\time\DwChronoStartStop as DwChronoStartStop;
use \bakuba\kernel\time\timer\DwTimers as DwTimers;
use \bakuba\kernel\time\timer\DwTimer as DwTimer;

$dwTimers = new DwTimers();

$dwChronoStartStop = new DwChronoStartStop();
$cpt=0;
$dwTimer = null;
$dwCallBack = new DwCallBack(
	function(
		DwChronoStartStop $dwChronoStartStop
		,DwTimers $dwTimers
	) use (  &$dwTimer ,&$cpt ) {
		if ( $cpt > 999 ) {
			$dwTimers->cancel( $dwTimer );
			//echo $dwCallBack->printHandle(false);
			echo "DwCallBack:0.03/30ms ($cpt) \r\n";
			echo $dwChronoStartStop->printStats();
		} 
		$cpt++;
	}
	,$dwChronoStartStop
	,$dwTimers
);
$dwCallBack->enableStats( $dwChronoStartStop );
$dwTimer = new DwTimer(
	0.03 // every 30ms
	,$dwCallBack
	,true // periodic
);
$dwTimers->addTimer( $dwTimer );

$dwChronoStartStop2 = new DwChronoStartStop();
$cpt2=0;
$dwTimer2 = null;
$dwCallBack2 = new DwCallBack(
	function(
		DwChronoStartStop $dwChronoStartStop
		,DwTimers $dwTimers
	) use (  &$dwTimer2 ,&$cpt2 ) {
		if ( $cpt2 > 15 ) {
			$dwTimers->cancel( $dwTimer2 );
			//echo $dwCallBack->printHandle(false);
			echo "DwCallBack2:2s($cpt2) \r\n";
			echo $dwChronoStartStop->printStats();
		} 
		$cpt2++;
	}
	,$dwChronoStartStop2
	,$dwTimers
);
$dwCallBack2->enableStats( $dwChronoStartStop2 );
$dwTimer2 = new DwTimer(
	2 // every 2s
	,$dwCallBack2
	,true // periodic
);
$dwTimers->addTimer( $dwTimer2 );

$dwChrono = new bakuba\kernel\time\DwChrono();
$dwChrono->tick();
\bakuba\kernel\time\DwTickQueue::run( $dwTimers );
$dwChrono->tick();
echo $dwChrono->printStats();
?>

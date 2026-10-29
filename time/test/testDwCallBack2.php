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

use bakuba\kernel\time\DwChronoStartStop as DwChronoStartStop;

$dwTickQueue = new \bakuba\kernel\time\DwTickQueue();
$dwChronoStartStop = new DwChronoStartStop();
$cpt=0;
$dwCallBack = null;
///*
// la fonction la plus longue
$dwCallBack = new DwCallBack(
function(  ) use ( $dwTickQueue ,&$dwCallBack ,&$cpt ,&$dwChronoStartStop ) {
	if ( $cpt < 99999 )
		$dwTickQueue->addTick( $dwCallBack );
	else {
		echo $dwCallBack->printHandle(false);
		echo $dwChronoStartStop->printStats();
	}
	
	$cpt++;
} );
$dwCallBack->enableStats( $dwChronoStartStop );
$dwTickQueue->addTick( $dwCallBack );
//*/
///*
$dwChronoStartStop2 = new DwChronoStartStop();
$dwCallBack2 = new DwCallBack( function() {} );
$dwCallBack2->enableStats( $dwChronoStartStop2 );
for ( $anCpt=0;$anCpt<=99999;$anCpt++ )
	$dwTickQueue->addTick( $dwCallBack2 );
	
$dwTickQueue->addTick( new DwCallBack( function() use ( $dwChronoStartStop2 ) {
		echo $dwChronoStartStop2->printStats();
}
) );
//*/

\bakuba\kernel\time\DwTickQueue::run( $dwTickQueue );

?>

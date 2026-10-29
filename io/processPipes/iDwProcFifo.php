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

namespace bakuba\kernel\io\processPipes;
//use bakuba\kernel\io\processPipes\iDwProcFifo as iDwProcFifo;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

interface iDwProcFifo /*extends iDwProcPipe*/ {
	public function emergencyGetQueue() : \SplQueue;
	public function emergencySetQueue( \SplQueue $emergencyQueue ) : void;
	public function taskTimeOut() : void;
	public function stop( ) :void ;
	//protected function prepareCmdBoundary( string $boundary ) : string;
	//protected function prepareExec( string $cmdToExec, string $boundary ) : string;
	public function exec( string $cmdToExec ,DwCallBack $dwCallBack = null ) : void ;
	
	//protected function execWithoutPrepare( string $cmdToExec ,DwCallBack $dwCallBack = null ) : void ;
	//protected function addTask( string $script ,string $boundary ,DwCallBack $dwCallBack = null ) ;
	//protected function lunchTask();
	//protected function getBundaryPos( string $boundary ,int $offset = 0 );
	//protected function getBundarySucess( string $boundary );
	//protected function getBundaryEchec( string $boundary );
	//protected function getBundarySucessPos( string $boundary ,int $offset = 0 );
	//protected function getBundaryEchecPos( string $boundary ,int $offset = 0 );
	//protected function dataExcract( int $boundaryPos, int $boundarySize,int $offset = 0 ) : string;
	//protected function extractTaskResult( string $boundary ) : ?array;
	public function totalTasks() : int;
	//private function analyseBoundary() : bool;
	public function receiveTask( string $data ) : void;
	public function shutdown() : void;
	// array( "content", "success" )
	public function endTask( array $taskResult, DwCallBack $dwCallBack = null ,\darkphp\logger\iDwLogger $iDwLogger = null );
}
?>

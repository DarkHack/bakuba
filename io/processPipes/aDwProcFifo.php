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
//use bakuba\kernel\io\processPipes\aDwProcFifo as aDwProcFifo;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

abstract class aDwProcFifo extends DwProcPipe implements iDwProcFifo {
	
	private $queueWaiting = null;
	private $queueRunning = null;
	
	private $taskCpt = 0;
	/*
	protected function getDwResources() : \bakuba\kernel\process\DwResources {
		return $this->parent;
	}
	//*/
	/*
	public function __construct(
		\bakuba\kernel\process\DwResources $dwResources
		,string $cmdPath
		,bool $withErrPipe = true
	) {
		parent::__construct( $dwResources ,$cmdPath ,$withErrPipe );
	}
	//*/
	
	private $initied=false;
	private $cptTaskLunch = 0;
	private $cptTaskEnd = 0;
	private $cbTimeout = null;
	private $timercheck = null;
	///*
	protected function startProcess() : void {
		$this->log->trace( "." );
		
		$this->queueWaiting = new \SplQueue();
		$this->queueRunning = new \SplQueue();
		$this->cbTimeout=new DwCallBack( array( &$this ,'taskTimeOut' ) );
		
		$this->cptTaskLunch=0;
		$this->cptTaskEnd=0;
		
		$this->initied=true;
		parent::startProcess();
		$this->lunchTasks();
		/*
		$this->lunchRead();
		$this->lunchReadErr();
		//*/
	}
	//*/
	// Perplexity.ai Production :
	public function pipeInWrited() : void {
		//$this->log->trace( "." );
		parent::pipeInWrited();
		$this->log->trace( "[task:{$this->cptTaskLunch}] END TASKS WRITED, NOW LISTEN RESULT" );
		$this->lunchRead();
	}
	
	public function pipeOutReaded( string $data ,bool $withoutRelunch = false ) : void {
		$this->log->trace( "." );
		parent::pipeOutReaded( $data ,$withoutRelunch );
		if ( ! empty( $data ) ) {
			$this->log->trace( "DATA:".\PHP_EOL."$ $data " );
			$this->receiveTask( $data );
		}
		//$this->getDwResources()->addTick( $cbRead->getClone( $data ) );
	}
	public function emergencyGetQueue() : \SplQueue {
		return $this->queueWaiting;
	}
	public function emergencySetQueue( \SplQueue $emergencyQueue ) : void {
		while(  $emergencyQueue->count() > 0 )
			$this->queueWaiting->enqueue( $emergencyQueue->dequeue() );
	}
	private $resetState=false;
	public function taskTimeOut() : void {
		$this->log->trace( "." );
		$this->resetState=true;
		$queueWaiting = new \SplQueue();
		$queueWaiting->enqueue( $this->currentTask );
		while(  $this->queueWaiting->count() > 0 )
			$queueWaiting->enqueue( $this->queueWaiting->dequeue() );
		
		$this->shutdown();
		$this->queueWaiting = $queueWaiting;
		
		$this->log->trace( "ECHEC TASK TIMEOUT" );
	}
	public function stop( ) : void {
		parent::stop();
		if ( $this->resetState ) {
			$this->procReset();
		}
	}
	/*
	protected function prepareCmdBoundary( string $boundary ) : string {
		//return "echo '{$boundary}';";
		throw new \Exception("not implemented !!!");
	}
	protected function prepareExec( string $cmdToExec, string $boundary ) : string {
		//return "{$cmdToExec}".$this->prepareCmdBoundary( $boundary );
		throw new \Exception("not implemented !!!");
	}
	//*/
	protected function generateBoundary() : string {
		return \sha1( $this->getClassName()."-".\bakuba\kernel\randomizer\DwRandomizer::randomize( 10 ) );
	}
	public function exec( string $cmdToExec ,DwCallBack $dwCallBack = null ) : void {
		$boundary=$this->generateBoundary();
		$this->addTask( $this->prepareExec( $cmdToExec ,$boundary ) ,$boundary ,$dwCallBack );
	}
	
	public function execWithoutPrepare( string $cmdToExec ,DwCallBack $dwCallBack = null ) : void {
		$boundary=$this->generateBoundary();
		$this->addTask( $cmdToExec.$this->prepareCmdBoundary( $boundary ) ,$boundary ,$dwCallBack );
	}
	protected function addTask( string $script ,string $boundary ,DwCallBack $dwCallBack = null ) {
		$waitingTask = array( $script ,$boundary ,$dwCallBack  );
		if ( is_null( $waitingTask[1] ) )
			throw new \Exception("waiting bound null".print_r( $waitingTask, true ));
		$this->queueWaiting->enqueue( $waitingTask );
		if ( $this->initied && ! $this->taskRunning )
			$this->lunchTasks();
	}
	private $currentTask = null;
	protected function lunchTask() {
		$this->taskRunning=true;
		$this->cptTaskLunch++;
		$toWrite="";
		$fifoLogger = $this->log->upTo(
			$this
//			,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			,"t_".$this->cptTaskLunch
		);
		$waitingTask = $this->queueWaiting->dequeue();
		$this->currentTask = $waitingTask;
		$toWrite.=$waitingTask[0];
		$this->queueRunning->enqueue( array(
			$waitingTask[1]
			,$waitingTask[2]
			,$fifoLogger
		) );
		//$fifoLogger->info( $toWrite );
		$this->timercheck = $this->getDwResources()->addTimer( 120 ,$this->cbTimeout );
		//$this->log->trace( $this->timercheck );
		$this->writeToStd( $toWrite );
		//$this->write( $toWrite, new DwCallBack( array( &$this ,'writed' ) ) );
	}
	private function lunchTasks() {
		$this->log->info( "." );
		if ( $this->taskRunning )
			$this->log->error( "ever taskRunning" );
		elseif ( ! $this->initied )
			$this->log->error( "init not yet." );
		elseif ( $this->queueWaiting->count() > 0 )
			$this->lunchTask( );
		else
			$this->log->warn( "no write, no more next task" );
	}
	protected $currentData = null;
	protected function getBundaryPos( string $boundary ,int $offset = 0 ) {
		return \strpos( $this->currentData ,$boundary ,$offset );
	}
	protected function getBundarySucess( string $boundary ) {
		return "{$boundary}1";
	}
	protected function getBundaryEchec( string $boundary ) {
		return "{$boundary}0";
	}
	protected function getBundarySucessPos( string $boundary ,int $offset = 0 ) {
		return $this->getBundaryPos( $this->getBundarySucess( $boundary ) ,$offset );
	}
	protected function getBundaryEchecPos( string $boundary ,int $offset = 0 ) {
		return $this->getBundaryPos( $this->getBundaryEchec( $boundary ), $offset );
	}
	protected function dataExcract( int $boundaryPos, int $boundarySize,int $offset = 0 ) : string {
		$content = \substr( $this->currentData ,$offset ,$boundaryPos );
		$this->currentData = \substr( $this->currentData ,$boundaryPos+$boundarySize );
		return $content;
	}
	protected function extractTaskResult( string $boundary ) : ?array {
		$taskResult = null;
		$boundarySize = \strlen( $boundary )+1;
		$echecPos = $this->getBundaryEchecPos( $boundary );
		$sucessPos = $this->getBundarySucessPos( $boundary );
		//$this->log->trace("data:".\strlen($this->currentData)." echecPos:{$echecPos} sucessPos:{$sucessPos}");
		if ( $echecPos === false && $sucessPos === false ) {
			if ( ( $boundaryPos = $this->getBundaryPos( "{$boundary}" ) ) !== false )
				$taskResult = array(
					$this->dataExcract( $boundaryPos ,$boundarySize )
					,true
				);
		} elseif ( $echecPos === false && $sucessPos !== false )
			$taskResult = array(
				$this->dataExcract( $sucessPos ,$boundarySize )
				,true
			);
		elseif ( $echecPos !== false && $sucessPos === false )
			$taskResult = array(
				$this->dataExcract( $echecPos ,$boundarySize )
				,false
			);
		elseif ( $echecPos !== false && $sucessPos !== false ) {
			$secondEchecPos = $this->getBundaryEchecPos( $boundary ,$echecPos+$boundarySize );
			$secondSucessPos = $this->getBundarySucessPos( $boundary ,$sucessPos+$boundarySize );
			if ( $secondEchecPos === false && $secondSucessPos !== false )
				$taskResult = array(
					$this->dataExcract( $secondSucessPos ,$boundarySize ,$echecPos+$boundarySize )
					,true
				);
			elseif ( $secondEchecPos !== false && $secondSucessPos === false )
				$taskResult = array(
					$this->dataExcract( $secondEchecPos ,$boundarySize ,$echecPos+$boundarySize )
					,false
				);
			else
				$this->log->warn( "!not end! secondEchecPos:{$secondEchecPos} secondSucessPos:{$secondSucessPos}" );
		} else
			$this->log->warn( "!not end! echecPos:{$echecPos} sucessPos:{$sucessPos}" );
		return $taskResult;
	}
	
	protected $taskRunning =false;
	public function totalTasks() : int {
		return $this->queueRunning->count() + $this->queueWaiting->count();
	}
	private $taskCurrent = null;
	private function analyseBoundary() : bool {
		$continueAnalyse=false;
		if ( \is_null( $this->taskCurrent ) ) {
			if ( $this->queueRunning->count() > 0 )
				$this->taskCurrent = $this->queueRunning->dequeue();
			else
				throw new \Exception(" echec no task running !!!!" );
		}
		$task = $this->taskCurrent;
		//if ( \is_string( $this->taskCurrent[0] ) ) {
		$taskResult = $this->extractTaskResult( $task[0] );
		if ( \is_array( $taskResult ) ) {
			$this->endTask( $taskResult ,$task[1] ,$task[2] );
			$this->taskCurrent = null;
			$continueAnalyse = $this->queueRunning->count() > 0;
		}
		//}
		return $continueAnalyse;
	}
	public function receiveTask( string $data ) : void {
		$this->log->trace( "[task:{$this->cptTaskLunch}] recv:".\strlen( $data ) );
		$this->log->trace( $data );
		if ( \is_null( $this->currentData ))
			$this->currentData=$data;
		else
			$this->currentData.=$data;
		// ...... else
		while( $this->analyseBoundary() ) ;
		
		if ( \is_array( $this->taskCurrent ) || $this->queueRunning->count() > 0 )	
			$this->lunchRead();
		else {
			$this->taskRunning=false;
			$this->getDwResources()->cancelTimer( $this->timercheck ) && $this->timercheck = null;
			if ( $this->queueWaiting->count() <= 0 )
				$this->log->warn("[task:{$this->cptTaskLunch}] no task running any more");
			else
				$this->lunchTasks();
		}
		
	}
	public function shutdown() : void {
		$this->log->trace(".");
		if ( $this->getDwResources()->isEverSchudeled( $this->cbTimeout ) )
			$this->getDwResources()->cancelTimer( $this->timercheck );
		$this->timercheck = null;
		
		parent::shutdown();
	}
	// array( "content", "success" )
	public function endTask(
		array $taskResult,
		DwCallBack $dwCallBack = null
		,\darkphp\logger\iDwLogger $iDwLogger = null
	) {
		if ( $taskResult[1] === false )
			$this->log->error(" echec task :\r\n".$taskResult[1] );
		if ( \is_object( $dwCallBack ) ) {
			$this->getDwResources()->addTick( $dwCallBack->getClone( $taskResult[0] ,$taskResult[1] ) );
		}
		//else $this->log->debug( "no cb:".$taskResult[1] );
		$this->cptTaskEnd++;
		
		///*
		$this->log->trace( "[task:{$this->cptTaskLunch}] ended" );
		/*
		$this->log->info( $taskResult );
		if ( \is_object( $iDwLogger ) )
			$iDwLogger->info( $taskResult );
		//*/
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

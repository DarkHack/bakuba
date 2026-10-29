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

namespace bakuba\kernel\process;
//use bakuba\kernel\process\DwProcessManager as DwProcessManager;
use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;

use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\logger\DwLogger as DwLogger;

use bakuba\kernel\objects\DwTime as DwTime;

use bakuba\kernel\agartha\DwNode as DwNode;

//use bakuba\kernel\dwTask\DwTask as DwTask;
abstract class DwProcessManager extends DwProcessIpc {
	private static $isChildInterrupt = false;
	private static $periodPongCall = 1000;
	
	private $processRunning = null;
	protected $processToStart = array();
	protected $listProcess = array();
	protected $listDeathProcess = array();
	
	protected $pingCptTrace = 0;
	public function getCptPing() : int {
		return $this->pingCptTrace;
	}
	protected $cptPong = array();
	public function getCptPong() : array {
		return $this->cptPong;
	}
	protected $alive_state = false;
	public function isStarted() : bool {
		return $this->isAlive();
	}
	public function isAlive() : bool {
		return $this->alive_state == true;
	}
	
	public function __construct( self $parentProcess ) {
		parent::__construct( $parentProcess );
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize("pingCptTrace");
		$this->addtoSerialize("cptPong");
		$this->addtoSerialize("alive_state");
	}
	///*
	protected function init(): void {
		$this->log->trace(".");
		parent::init( );
		
		$this->ipcRegisterHandle(
			DwProcessRequest::getStaticClassName()
			,new DwCallBack( array( &$this ,'handleProcessRequest' ) )
		);
		
		//$this->setProcessRun( $this );
		$this->sendStart();
		
		$this->log->trace("end");
	}
	
	protected static $currentProcess = null;
	//DwProcessTick::getCurrentProcess()
	public static function getCurrentProcess() : ?DwProcessManager {
		return self::$currentProcess;
	}
	// DwProcessManager::run
	/*
	public static function run( \bakuba\kernel\time\DwObjectTick $dwProcess ) : void {
		\bakuba\kernel\time\timer\DwObjectTimer::runTimer( $dwProcess );
	}
	//*/
	/*
	public static function run( DwProcessManager $dwProcess ) : void {
		global $dwKernel;
		$debugKey="{self::getStaticClassName()}/{$dwProcess->getClassName()}";
		
		self::$currentProcess = $dwProcess;
		\set_time_limit(0);
		declare(ticks=10000) {
			$nextTimeout = 0;
			$dwKernel->addSignal(
				\SIGTERM
				,$dwProcess
				,new DwCallBack( array( &$dwProcess ,"stop" ) )
			);
			//$dwKernel->addSignal(
			//	\SIGKILL
			//	,$dwProcess
			//	,new DwCallBack( array( &$dwProcess ,"stop" ) )
			//);
			
			while ( ! $dwProcess->isEnd() ) {
				$nextTimeout = $dwProcess->trigger();
				if ( is_null( $nextTimeout ) )
					break;
				
				if ( $nextTimeout == -1 )
					$nextTimeout=3000000;
				$dwProcess->sleep( $nextTimeout );
			}
		}
	}
	//*/
	protected function sendStart( ) : void {
		$this->log->trace( "send start :{$this->getClassName()} => {$this->getParentProcess()->getClassName()}" );
		
		$this->ipcSend( new DwCallBack(
			array( &$this ,"pinglunch" ) )
			,$this->getParentProcess()
			,new DwProcessRequest( DwProcessOrder::getOrder( DwProcessOrder::STARTED ) )
		);
	}
	protected function isForkEnable() : bool {
		return false;
	}
	public function clearDeathProcess( $indexListProcess ) : void {
		if ( isset( $this->listProcess[$indexListProcess] ) ) {
			$dwProcess = $this->listProcess[$indexListProcess];
			$this->log->trace( $dwProcess );
			
			unset( $this->listProcess[$indexListProcess] );
		} else
			throw new \Exception( "no process:{$indexListProcess}" );
			
		if ( isset( $this->listDeathProcess[ $indexListProcess ] ) ) 
			unset( $this->listDeathProcess[ $indexListProcess ] );
		
	}
	public function relunchProcess( DwIRunnable $dwIRunnable ) : void {
		$this->log->trace( "relunchProcess:{$dwIRunnable->getClassName()}" );
		$this->addTick( new DwCallBack( array( &$this, 'createProcess' ), $dwIRunnable ) );
	}
	public function createProcess( DwIRunnable $dwProcess ) : void {
		$this->log->trace( "createProcess:{$dwProcess->getClassName()}" );
		$this->addTick( new DwCallBack( array( &$this ,'startProcess' ), $dwProcess ) );
	}
	public function startProcess( DwIRunnable $dwProcess ) : void {
		$this->getDwResources()->startProcess( $dwProcess );
		$this->listProcess[ $dwProcess->getKey() ] = $dwProcess;
		$dwProcess->onClose( new DwCallBack(
			array( &$this ,"clearDeathProcess" )
			,$dwProcess->getKey()
		) );
	}
	protected function getListProcess() : array {
		return $this->listProcess;
	}
	///*
	private $stopCB = null;
	public function stop() : void {
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		try {
			$this->log->trace( "[{$debugKey}]" );
			if ( is_null( $this->stopCB ) )
				$this->stopCB = new DwCallBack( array( &$this ,"stop" ) );
			//$this->log->trace(debug_backtrace()[0]);
			if ( $this->isMyProcess() ) {
				if ( ! empty( $this->listProcess ) ) {
					$numItems = count( $this->listProcess );
					$cptItem = 0;
					$this->log->trace(
						"[{$debugKey}] have to shutdown '$numItems' process!"
					);
					
					$sended = false;
					foreach( $this->listProcess as $anChildProcess ) {
						$sended = ! $anChildProcess->isEnd();
						if ( $sended ) {
							$this->ipcSend( 
								$this->stopCB
								,$anChildProcess
								,new DwProcessRequest( "stop" )
							);
							$anChildProcess->onClose( $this->stopCB );
							//break ;
						}
					}
					if ( ! $sended )
						$this->futureTick( $this->stopCB );
				} else 
					$this->stopMessaging();
			} else
				$this->childCheckPidClose( );
			
		} catch(\Exception $exception) {
			$this->log->error("[{$debugKey}] exception");
			throw $exception;
		} catch(\Error $error) {
			$this->log->error("[{$debugKey}] error");
			throw $error;
		}
	}
	public function childCheckPidClose( int $cpt = 0 ) {
		$debugKey ="{$this->getClassName()}/{$this->getInstanceKey()}";
		$debugKey.="/pid:{$this->getPid()}/cpt:{$cpt}";
		if ( DwProcess::checkPid( $this->getPid() ) ) {
			$this->log->warn( "[{$debugKey}] pid is alive!" );
			$this->futureTick(
				new DwCallBack( array( &$this ,"childCheckPidClose" ) ,$cpt + 1 )
			);
		} else
			$this->futureTick( new DwCallBack( array( &$this ,"stopProcess" ) ) );
	}
	//*/
	
	/*
	array( "mid" ,"pid" ,"type" ,"data64" => "" );
	*/
	public function handleProcessRequest(
		DwProcessConnexion $dwProcessConnexion
		,DwProcessRequest $dwProcessRequest
	) : void {
		$dwIpcMsg = $dwProcessConnexion->getDwIpcMsg();
		//global $dwKernel;
		$debugKey ="{$this->getClassName()}/{$this->getInstanceKey()}";
		$srcKey = $dwIpcMsg->getSrcKey();
		$keyProcess = $srcKey->getKey();
		
		$dwProcess = null;
		if ( isset($this->listProcess[ $keyProcess ]) )
			$dwProcess = $this->listProcess[ $keyProcess ];
		
		switch( $dwProcessRequest->getDwProcessOrder()->orderToInt() ) {
		case DwProcessOrder::STARTED :
			if ( ! is_object( $dwProcess ) )
				throw new \Error("no dwProcess with key {$keyProcess}");
			else {
				$this->log->trace( "set alive:{$dwProcess->getClassName()}" );
				$dwProcess->setAlive( true );
				$dwProcessConnexion->ipcResponse( new DwProcessResponse( "goodjob" ) );
				/*
				$this->addTimer(
					60
					,new DwCallBack( array( &$this ,"newPing" ), $dwProcess )
				);
				//*/
			}
			break;
		case DwProcessOrder::ALIVE :
			$this->log->trace( "ALIVE:{$dwProcess->toString()}" );
			break;
		case DwProcessOrder::SHOW :
			$dwProcessConnexion->ipcResponse( new DwProcessResponse( array( DwObject::listInstancesCreated() ,$this ) ) );
			break;
		case DwProcessOrder::LISTCHILD :
			$dwProcessConnexion->ipcResponse( new DwProcessResponse( $this->listProcess ) );
			break;
		case DwProcessOrder::PING :
			$this->pingCptTrace++;
			
			if ( $this->isEnd() )
				$dwProcessRequest = new DwProcessRequest("dong");
			else
				$dwProcessRequest = new DwProcessRequest("pong");
			
			//$dwProcessRequest->setLogs( $dwKernel->getLogs() );
			$dwProcessConnexion->ipcResponse( $dwProcessRequest );
			
			if ( $this->pingCptTrace > 10000 ) {
				$this->pingCptTrace=0;
				$this->log->trace( "[{$debugKey}] {$this->pingCptTrace} ping" );
			}
			break;
		case DwProcessOrder::DONG :
			if ( ! is_object($dwProcess) ) {
				$this->log->error("[{$debugKey}] dong no dwProcess");
				$dwProcessConnexion->ipcResponse( new DwProcessRequest("echec pong") );
			} else {
				$this->log->trace( 
					"[{$debugKey}] dong:{$dwProcess->getClassName()}"
				);
				//$this->pong( $dwProcess, $dwProcessRequest );
				$dwProcessConnexion->ipcResponse( new DwProcessRequest("pong") ,new DwCallBack( array( &$dwProcess, "stop" ) ) );
				//$dwProcess->stop();
				//unset($this->listProcess[$keyProcess]);
			}
			//break;
		case DwProcessOrder::PONG :
			if ( ! is_object($dwProcess) )
				throw new \Error("no dwProcess with key {$keyProcess}");
			else {
				$this->log->trace(
					"[{$debugKey}] pong:{$dwProcess->getClassName()}"
				);
				//$this->onNTrigger(
				//	100
				//	, new DwCallBack( array( $this, "newPing" ), $dwProcess )
				//);
				//$dwProcessConnexion->ipcResponse( new DwProcessRequest("ping") );
			}
			break;
		case DwProcessOrder::STOP :
			$this->stop();
			$dwProcessConnexion->ipcResponse( new DwProcessResponse( "Stopped" ) );
			break;
		default :
			$unknowMsg = "unknow[{$keyProcess}]";
			if ( is_object($dwProcess) )
				$unknowMsg.=" from {$dwProcess->getClassName()}";
			else 
				$unknowMsg.=" from nowhere";
			$this->log->warn( "[{$debugKey}] {$unknowMsg}\r\n\t".$dwProcessRequest->getMessage() );
			break;
		}
		
	}
	//*/
	// return true, if process is not child forked
	///*
	public function start( ) : bool {
		$return=true;
		$this->isFinish=false;
		$this->init();
		$this->log->trace(".");
		return $return;
	}
	//*/
	
	public function newPing( DwProcessManager $dwProcess ) : void {
		//$this->log->trace( "." );
		$this->ipcSend(
			new DwCallBack( array( &$this, "pong" ), $dwProcess )
			, $dwProcess
			,new DwProcessRequest("ping")
		);
		
	}
	public function pong(
		DwProcessManager $dwProcess
		,DwProcessRequest $dwProcessRequest
	) : void {
		//$this->addTimer(
		//	60
		//	,new DwCallBack( array( &$this ,"newPing" ), $dwProcess )
		//);
		
		$processKey = $dwProcess->getInstanceKey();
		if ( ! isset( $this->cptPong[ $processKey ] ) )
			$this->cptPong[ $processKey ] = self::$periodPongCall;
			
		if ( $this->cptPong[ $processKey ] >= self::$periodPongCall ) {
			$parentPong = $this->getPid().":".$this->getClassName();
			$childPong = $dwProcess->getPid().":".$dwProcess->getClassName();
			$this->log->trace( 
				"pong Num:{$this->cptPong[$processKey]} ".\PHP_EOL
				."{$parentPong} <=> {$childPong}"
			);
			$this->cptPong[ $processKey ] = 0;
		} else 
			$this->cptPong[ $processKey ]++;
	}
	public function pinglunch( ) : void {
		$this->log->trace(".");
	}
	/*
	public function nopong( DwProcessManager $dwProcess ) {
		$dwProcess->setAlive(true);
		$this->log->trace(
			"no more ping\r\n"
			."process:{$dwProcess->getClassName()}"
		);
	}
	//*/
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

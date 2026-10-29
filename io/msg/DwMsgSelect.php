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

namespace bakuba\kernel\io\msg;
//use bakuba\kernel\io\msg\DwMsgSelect as DwMsgSelect;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

//use bakuba\kernel\io\DwResource as DwResource;
use bakuba\kernel\io\IDwResource as IDwResource;
use bakuba\kernel\io\DwResourceSelect as DwResourceSelect;

use bakuba\kernel\io\IDwResources as IDwResources;
class DwMsgSelect extends DwResourceSelect {
	private static $sLog = null;
	private $debugEnable = false;
	public function enableDebug( bool $withParent = false ) : void {
		$this->debugEnable = true;
		self::$sLog->trace( $withParent ? "with parent":"this only" );
		if ( $withParent )
			parent::enableDebug( $withParent );
	}
	
	private $dwMessageQueueServer = null;
	public function __construct( IDwResources $dwResources, DwMessageQueueServer $dwMessageQueueServer ) {
		global $dwKernel;
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			//self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			parent::__construct( $dwResources );
			
			$this->dwMessageQueueServer = $dwMessageQueueServer;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	public function resourceWrite( IDwResource $dwResource ) : void {
		try {
			if ( ! $dwResource->isResource() && ! $dwResource->connect() && ! $dwResource->isResource() ) {
				$dstKey = $dwResource->getDstKey();
				$srcKey = $dwResource->getSrcKey();
				
				$dbgMsg = "[".( is_null($srcKey) ? "null" : $srcKey->getClassNameProcess() );
				$dbgMsg.= "=>".( is_null($dstKey) ? "null" : $dstKey->getClassNameProcess() );
				$dbgMsg.= "]";
				throw new Exception("queue null or not ressource. $dbgMsg");
			}
			if ( $this->debugEnable )
				$dwResource->enableDebug( true );
			parent::resourceWrite($dwResource);
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	/*
	public function resourceRead( IDwResource $dwResource ) : void {
		if ( $this->debugEnable )
			$dwResource->enableDebug( true );
		parent::resourceRead($dwResource);
	}
	//*/
	//private $debugIdRecept = array();
	/*
	public function read( array &$dwResourcesRead, array &$dwResourcesExcept ) : array {
		$dwResourcesReaded = array();
		$dwResource = null;
		try {
			$dwMessageQueueKey = $this->dwMessageQueueServer->getQueueKey();
			$tabMessageReceived = $this->readAll( $dwResourcesRead, $dwResourcesExcept );
			
			foreach( $tabMessageReceived as $idxTabMessage => $dwMessageQueuePacket ) {
				$mid = $dwMessageQueuePacket->getMessageId();
				
				// read message, if not exist yet, new connection !!!
				try {
					$found=false;
					foreach( $dwResourcesRead as $idxLcalResRead => $dwMessageQueue ) {
						if ( $dwMessageQueue->getId() == $mid ) {
							if ( $this->debugEnable )
								self::$sLog->trace( "frag message : {$dwMessageQueueKey->getClassNameProcess()}/{$mid}" );
							$dwMessageQueue->receivePacket( $dwMessageQueuePacket );
							$found=true;
							break;
						}//elseif ( $this->debugEnable )
						//		self::$sLog->trace( $dwMessageQueue->getId()."!=$mid" );
					}
					if ( ! $found ) {
						foreach( $this->resourcesToRead as $idxExtResRead => $dwMessageQueue ) {
							if ( $dwMessageQueue->getId() == $mid ) {
								if ( $this->debugEnable )
									self::$sLog->trace( "frag message 2 : {$dwMessageQueueKey->getClassNameProcess()}/{$mid}" );
								$dwMessageQueue->receivePacket( $dwMessageQueuePacket );
								$found=true;
								break;
							}//elseif ( $this->debugEnable )
							//	self::$sLog->trace( $dwMessageQueue->getId()."!=$mid" );
						}
					}
					if ( ! $found ) {
						$dwMessageQueue = new DwMessageQueue( $dwMessageQueueKey ,$mid );
						if ( $this->debugEnable ) {
							$dwMessageQueue->enableDebug( true );
							self::$sLog->trace( "new message for : {$dwMessageQueueKey->getClassNameProcess()}/{$mid}" );
						}
						$dwMessageQueue->receivePacket( $dwMessageQueuePacket );
						$this->messageQueueRead( $dwMessageQueue ,$this->dwMessageQueueServer->getDwCallBackMessageReceived() );
						$this->resourcesToRead[] = $dwMessageQueue;
					}
					unset( $tabMessageReceived[ $idxTabMessage ] );
					
					$dwResourcesRead[] = $dwMessageQueue;
					//$this->resourceRead($dwMessageQueue);
				} catch(Exception $exception_rhandle) {
					//$dwResourcesExcept[] = $dwMessageQueue;
					self::$sLog->error ( "read exception_rhandle [".$this->getClassName()."/".$this->getInstanceKey()."]" );
					self::$sLog->throwable($exception_rhandle);
					//throw $exception_rhandle;
				} catch(Error $error_rhandle) {
					//$dwResourcesExcept[] = $dwMessageQueue;
					self::$sLog->error ( "read error_rhandle [".$this->getClassName()."/".$this->getInstanceKey()."]" );
					self::$sLog->throwable($error_rhandle);
					//throw $error_rhandle;
				}
			}
			$dwResourcesReaded = parent::read( $dwResourcesRead, $dwResourcesExcept );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			//self::$sLog->error($exception);
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			//self::$sLog->error($error);
			throw $error;
		}
		return $dwResourcesReaded;
	}
	//*/
	public function messageQueueTryWriteAgain( DwMessageQueue $dwMessageQueue, DwCallBack $onWrited ) : void {
		try {
			
			$dstKey = $dwMessageQueue->getDstKey();
			$dwMessageQueue->rewrite();
			
			if ( $this->debugEnable )
				self::$sLog->trace( $dstKey->getProcessId()."/".$dstKey->getClassNameProcess()."=>".$dstKey->getKey()."/".$dwMessageQueue->getId() );
			
			$this->writeCancel( $dwMessageQueue );
			$this->messageQueueWrite( $dwMessageQueue ,$onWrited );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	public function messageQueueWrite( DwMessageQueue $dwMessageQueue, DwCallBack $onWrited ) : void {
		try {
			$dstKey = $dwMessageQueue->getDstKey();
			
			if ( $this->debugEnable )
				self::$sLog->trace( $dstKey->getProcessId()."/".$dstKey->getClassNameProcess()."=>".$dstKey->getKey()."/".$dwMessageQueue->getId() );
			//self::$sLog->trace( "[".$this->getClassName()."/".$this->getInstanceKey()."]\r\n".$dwMessageQueue->getId() );
			//$dwMessageQueue->setSrcKey( $this->getSrcKey() );
			$this->LunchWrite(
				$dwMessageQueue
				,$onWrited
				,new DwCallBack( array( &$this, 'messageQueueTryWriteAgain' ) ,$dwMessageQueue ,$onWrited )
			);
			
			//$pidDst = $dwMessageQueue->getDstKey()->getProcessId();
			//posix_kill( $pidDst, \SIGUSR1 );
			//$pidSrc = $dwMessageQueue->getSrcKey()->getProcessId();
			//posix_kill( $pidSrc, \SIGUSR1 );
			
			//self::$sLog->trace("send posixkill[$pidSrc => $pidDst]/".$this->dwMessageQueueKey->getProcessId()." \r\n");
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	public function messageQueueRead( DwMessageQueue $dwMessageQueue, DwCallBack $onReaded ) : void{
		try {
			if ( $this->debugEnable )
				self::$sLog->trace( "[".$this->getClassName()."/".$this->getInstanceKey()."]\r\n".$dwMessageQueue->getId() );
			//$dwMessageQueue->setDstKey( $this->getSrvKey() );
			
			
			$this->lunchRead(
				$dwMessageQueue
				,$onReaded
				,$dwMessageQueue->isListen() ? null : new DwCallBack( array( &$this->dwResources, 'readCancel' ) ,$dwMessageQueue )
			);
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	//messaging_select
	public function messaging_select( array &$dwResourcesRead, array &$dwResourcesWrite, array &$dwResourcesExcept ) : int {
		$nbInternalMessageReady = 0;
		$nbInternalMessageRead = 0;
		$nbInternalMessageListen = 0;
		$nbInternalMessageWrite = 0;
		$tabMessageReceivedByDstKey = array();
		$tabDstKeyByDstKey = array();
		
		$dwResourceRead = \reset( $dwResourcesRead );
		while( $dwResourceRead !== false ) {
			$idxRead = \key( $dwResourcesRead );
			if ( $dwResourceRead->isListen() ) {
				$nbInternalMessageListen++;
				try {
					$dwMessageQueueKey = $dwResourceRead->getDstKey();
					$key = $dwMessageQueueKey->getKey();
					$tabMessageReceivedByDstKey[ $key ] = $dwResourceRead->retreiveMessages();
					$tabDstKeyByDstKey[ $key ] = $dwMessageQueueKey;
					
				} catch(Exception $exception_receiveall) {
					$dwResourcesExcept[] = $dwResourceRead;
					if ( isset( $dwResourcesRead[$idxRead] ) ) unset($dwResourcesRead[$idxRead]);
					self::$sLog->error( "read exception_receiveall [".$this->getClassName()."/".$this->getInstanceKey()."]" );
					self::$sLog->throwable( $exception_receiveall );
					//throw $exception_receiveall;
				} catch(Error $error_receiveall) {
					$dwResourcesExcept[] = $dwResourceRead;
					if ( isset( $dwResourcesRead[$idxRead] ) ) unset($dwResourcesRead[$idxRead]);
					self::$sLog->error( "read error_receiveall [".$this->getClassName()."/".$this->getInstanceKey()."]" );
					self::$sLog->throwable( $error_receiveall );
					//throw $error_receiveall;
				}
			} else
				$nbInternalMessageRead++;
			$dwResourceRead = \next( $dwResourcesRead );
		}
		$tabMessageReceived = \reset( $tabMessageReceivedByDstKey );
		while( $tabMessageReceived !== false ) {
			$toDstKey =  \key( $tabMessageReceivedByDstKey );
			$dwMessageQueueKey = $tabDstKeyByDstKey[ $toDstKey ];
			
			if ( $this->debugEnable ) {
				$nbNewMes = count( $tabMessageReceived );
				if ( $nbNewMes > 0 )
					self::$sLog->trace( "tabMessageReceived[ $toDstKey ]:$nbNewMes" );
			}
			$dwMessageQueuePacket = \reset( $tabMessageReceived );
			while( $dwMessageQueuePacket !== false ) {
				$idxTabMessage = \key( $tabMessageReceived );
				$mid = $dwMessageQueuePacket->getMessageId();
				// read message, if not exist yet, new connection !!!
				try {
					$found=false;
					foreach( $dwResourcesRead as $idxLcalResRead => $dwMessageQueueLcal ) {
						if ( $dwMessageQueueLcal->isListen() )
							continue;
						elseif ( $dwMessageQueueLcal->getId() == $mid ) {
							if ( $this->debugEnable )
								self::$sLog->trace( "frag message {$mid} => {$dwMessageQueueKey->getClassNameProcess()}" );
							$dwMessageQueueLcal->receivePacket( $dwMessageQueuePacket );
							$found=true;
							break;
						}//elseif ( $this->debugEnable )
						//		self::$sLog->trace( $dwMessageQueueLcal->getId()."!=$mid" );
					}
					if ( ! $found ) {
						foreach( $this->resourcesToRead as $idxExtResRead => $dwMessageQueueExt ) {
							if ( $dwMessageQueueExt->getId() == $mid ) {
								if ( $this->debugEnable )
									self::$sLog->error( "frag message 2 : {$dwMessageQueueKey->getClassNameProcess()}/{$mid}" );
								$dwMessageQueueExt->receivePacket( $dwMessageQueuePacket );
								$dwResourcesRead[]=$dwMessageQueueExt;
								$found=true;
								break;
							}//elseif ( $this->debugEnable )
						//	self::$sLog->trace( $dwMessageQueue->getId()."!=$mid" );
						}
					}
					if ( ! $found ) {
						$dwMessageQueueNewMsg = new DwMessageQueue( $dwMessageQueueKey ,$mid );
						if ( $this->debugEnable ) {
							$dwMessageQueueNewMsg->enableDebug( true );
							self::$sLog->trace( "new {$mid} => {$dwMessageQueueKey->getClassNameProcess()}" );
						}
						$dwMessageQueueNewMsg->receivePacket( $dwMessageQueuePacket );
						$this->messageQueueRead(
							$dwMessageQueueNewMsg
							,$this->dwMessageQueueServer->getDwCallBackMessageReceived()
						);
						//$this->resourcesToRead[] = $dwMessageQueueNewMsg;
						//$dwResourcesRead[]=$dwMessageQueueNewMsg;
					}
					//unset( $tabMessageReceived[ $idxTabMessage ] );
					//$dwResourcesRead[] = $dwMessageQueue;
					//$this->resourceRead($dwMessageQueue);
				} catch(Exception $exception_rhandle) {
					//$dwResourcesExcept[] = $dwMessageQueue;
					self::$sLog->error ( "read exception_rhandle" );
					self::$sLog->throwable($exception_rhandle);
					//throw $exception_rhandle;
				} catch(Error $error_rhandle) {
					//$dwResourcesExcept[] = $dwMessageQueue;
					self::$sLog->error ( "read error_rhandle" );
					self::$sLog->throwable($error_rhandle);
					//throw $error_rhandle;
				}
				$nbInternalMessageReady++;
				$dwMessageQueuePacket = \next( $tabMessageReceived );
			}
			$tabMessageReceived = \next( $tabMessageReceivedByDstKey );
		}
		$nbInternalMessageWrite = \count( $dwResourcesWrite );
		
		//$nbInternalMessageReady+=$nbInternalMessageRead + $nbInternalMessageWrite;
		$nbInternalMessageReady+=$nbInternalMessageWrite;
		
		$this->lastState ="ready:$nbInternalMessageReady ";
		$this->lastState.="r:$nbInternalMessageRead ";
		$this->lastState.="w:$nbInternalMessageWrite ";
		$this->lastState.="l:$nbInternalMessageListen ";
		
		if ( $this->debugEnable )
			self::$sLog->trace( $this->lastState );
		
		return $nbInternalMessageReady;
	}
	private function readAll( array &$dwResourcesRead, array &$dwResourcesExcept ) : array {
		$tabMessageReceived = array();
		try {
			// read message ever exist
			foreach( $dwResourcesRead as $index => $dwMessageQueue ) {
				try {
					if ( $dwMessageQueue->isListen() )
						$tabMessageReceived = $dwMessageQueue->retreiveMessages();
				} catch(Exception $exception_receiveall) {
					$dwResourcesExcept[] = $dwMessageQueue;
					if ( isset( $dwResourcesRead[$index] ) ) unset($dwResourcesRead[$index]);
					self::$sLog->error( "read exception_receiveall [".$this->getClassName()."/".$this->getInstanceKey()."]" );
					self::$sLog->throwable( $exception_receiveall );
					//throw $exception_receiveall;
				} catch(Error $error_receiveall) {
					$dwResourcesExcept[] = $dwMessageQueue;
					if ( isset( $dwResourcesRead[$index] ) ) unset($dwResourcesRead[$index]);
					self::$sLog->error( "read error_receiveall [".$this->getClassName()."/".$this->getInstanceKey()."]" );
					self::$sLog->throwable( $error_receiveall );
					//throw $error_receiveall;
				}
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			//self::$sLog->error($exception);
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			//self::$sLog->error($error);
			throw $error;
		}
		return $tabMessageReceived;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

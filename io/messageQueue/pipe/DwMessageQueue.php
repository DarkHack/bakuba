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

namespace bakuba\kernel\io\messageQueue;
//use bakuba\kernel\io\messageQueue\DwMessageQueue as DwMessageQueue;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\process\DwProcess as DwProcess;

use bakuba\kernel\io\DwResource as DwResource;

class DwMessageQueue extends DwResource {
	private static $emptyPacketSize = null;
	
	private $dstKey = null;
	public function getDstKey() : DwMessageQueueKey {
		return $this->dstKey;
	}
	private $srcKey = null;
	public function getSrcKey() : DwMessageQueueKey {
		return $this->srcKey;
	}
	private	$id = null;
	public function getId() : string {
		return $this->isListen() ? "isListen:".$this->id : $this->id;
	}
	///*
	private	$rid = null;
	public function getRid() {
		return $this->rid;
	}
	//*/
	private $packetSize = -1;
	private $packetAutoSize = false;
	public function setPacketSize( int $packetSize ) {
		$this->packetSize = $packetSize;
		$this->packetAutoSize = true;
	}
	public function getPacketSize() :int {
		return $this->packetSize;
	}
	public function isPacketAutoSize( ) : bool {
		return $this->packetAutoSize;
	}
	
	private $index = -1;
	public function getIndex() : int {
		return $this->index;
	}
	
	public function __construct(
		\darkphp\DwObject $parent
		,DwMessageQueueKey $dwMessageQueueKey
		,string $mid = null
	) {
		$this->dstKey = $dwMessageQueueKey;
		
		parent::__construct( $parent );
		
		$this->id = $mid == null ? \bakuba\kernel\randomizer\DwRandomizer::randomize( 10 ) : $mid;
		
		if ( is_null( self::$emptyPacketSize ) ) {
			$serializedEmptyPacket = serialize( DwMessageQueuePacket::message(
				"0"
				,null
				,$dwMessageQueueKey
				,""
				,true
			) );
			self::$emptyPacketSize = strlen( $serializedEmptyPacket );
			//self::$sLog->trace($serializedEmptyPacket);
			//self::$sLog->trace("emptyPacketSize:".self::$emptyPacketSize);
			if ( $this->debugEnable )
				self::$sLog->trace( "emptyPacketSize:".self::$emptyPacketSize );
		}
		
		if ( $this->debugEnable )
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
	}
	private $isForward = false;
	public function forwardTo( DwMessageQueueKey $dwMessageQueueKey ) {
		$this->disconnect();
		$this->dstKey = $dwMessageQueueKey;
		$this->isForward=true;
		//$this->connect();
		parent::forward();
	}
	public function source( DwMessageQueueKey $dwMessageQueueKey ) {
		if ( ! $this->isForward )
			$this->srcKey = $dwMessageQueueKey;
		
	}
	public function connect( $queue = null ) : bool {
		$return = false;
		$key = null;
		try {
			$key = $this->dstKey->getKey();
			//self::$sLog->info( "[".$this->getClassName()."/$key]".( $this->isListen() ? "create" : "" ) );
			
			if ( $this->isListen() || msg_queue_exists( $key ) ) {
				$queue = msg_get_queue( $key );//, 777 );
				if ( $queue === false ) 
					throw new Exception( "echec msg_get_queue" );
				
			} else
					throw new Exception( "not listen and '$key' not existe" );
			
			$return = parent::connect( $queue );
		} catch(Exception $exception) {
			$msgError="[".$this->getClassName()."/".( $this->isListen() ? "listenning" : "client" )."/".( is_null($key) ? "key null" : $key )."]";
			self::$sLog->error("exception $msgError");
			self::$sLog->throwable($exception);
			//self::$sLog->error($exception);
			throw $exception;
		} catch(Error $error) {
			$msgError="[".$this->getClassName()."/".( $this->isListen() ? "listenning" : "client" )."/".( is_null($key) ? "key null" : $key )."]";
			self::$sLog->error("error $msgError");
			self::$sLog->throwable($error);
			//self::$sLog->error($error);
			throw $error;
		}
		return $return;
	}
	public function disconnect() : bool {
		$return = false;
		try {
			//self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			
			if ( ! $this->isResource() )
				self::$sLog->error("not connected.");
			elseif ( $this->isListen() ) {
				self::$sLog->trace("stop listen (".$this->dstKey->getKey().") [".$this->getClassName()."/".$this->getInstanceKey()."]");
				msg_remove_queue( $this->getResource() );
			}
			
			$return = parent::disconnect();
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			self::$sLog->error($exception);
			//throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			self::$sLog->error($error);
			//throw $error;
		}
		return $return;
	}
	
	private function retreiveMessage( ) : DwMessageQueuePacket {
		$dwMessageQueuePacket = null;
		try {
			$msgNoError=true;
			$msgType = null;
			$msgData = null;
			$errCode = null;
			$mid = null;
			
			$resource = $this->getResource();
			$packetSize = $this->getPacketSize();
			try {
				if (! msg_receive( $resource, 0, $msgType, $packetSize, $msgData, false, MSG_IPC_NOWAIT, $errCode ) )
					throw new Exception('echec msg_receive (code:$errCode)');
				else
					$dwMessageQueuePacket = unserialize( $msgData );
			} catch(Exception $exception_receive) {
				$dwMessageQueuePacket = false;
				self::$sLog->error( "read exception_receive [".$this->getClassName()."/".$this->getInstanceKey()."]\r\n$msgData" );
				self::$sLog->throwable($exception_receive);
				//throw $exception_receive;
			} catch(Error $error_receive) {
				$dwMessageQueuePacket = false;
				self::$sLog->error( "read error_receive [".$this->getClassName()."/".$this->getInstanceKey()."]\r\n$msgData" );
				self::$sLog->throwable( $error_receive );
				//throw $error_receive;
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
		return $dwMessageQueuePacket;
	}
	public function retreiveMessages() : array {
		$tabMessageReceived = array();
		try {
			$nbMessageReady = $this->stats();
			
			while ( $nbMessageReady > 0 ) {
				$nbMessageReady--;
				$tabMessageReceived[] = $this->retreiveMessage();
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
		return $tabMessageReceived;
	}
	private $packetReceived = array();
	public function receivePacket( DwMessageQueuePacket $dwMessageQueuePacket ) : void {
		try {
			if ( $this->id != $dwMessageQueuePacket->getMessageId() )
				throw new Exception("not good id for mine :".$this->id."/".$dwMessageQueuePacket->getMessageId());
			else {
				if ( $this->debugEnable )
					self::$sLog->trace( "[mid:".$this->id."]:".$dwMessageQueuePacket->getIndex()."/".count($this->packetReceived) );
				
				$this->packetReceived[$dwMessageQueuePacket->getIndex()] = $dwMessageQueuePacket;
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
		
	}
	
	public function receive( ) : void {
		try {
			//self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]".$this->packetSize);
			foreach( $this->packetReceived as $index => $dwMessageQueuePacket ) {
				$this->srcKey = $dwMessageQueuePacket->getSrcKey();
				$this->rid = $dwMessageQueuePacket->getRid();
				
				$message = $dwMessageQueuePacket->getMessage();
				$last = $dwMessageQueuePacket->isLast();
				unset($this->packetReceived[$index]);
				if ( $this->debugEnable )
					self::$sLog->trace( "[mid:".$this->id."]:".$dwMessageQueuePacket->getIndex()."/".($last===true?"last":"to continue") );
				$this->recept( $message );
				if ( $last === true )
					$this->readClose();
			}
			if ( $this->readEnd() ) {
				if ( ! is_null( $this->dstKey ) ) {
					//self::$sLog->trace("send posix dst:".$this->dstKey->getProcessId()." \r\n");
					posix_kill( $this->dstKey->getProcessId(), \SIGUSR1 );
				}
				if ( ! is_null( $this->srcKey ) ) {
					//self::$sLog->trace("send posix src:".$this->srcKey->getProcessId()." \r\n");
					posix_kill( $this->srcKey->getProcessId(), \SIGUSR1 );
				}
				if ( function_exists("pcntl_signal_dispatch") ) \pcntl_signal_dispatch();
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
		//return $dataMessage;
	}
	public function write( string $response ) {
		try {
			//self::$sLog->trace(".");
			parent::write( $response );
			$this->index = 0;
			$this->trySend=-1;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	public function rewrite( ) {
		try {
			//self::$sLog->trace(".");
			$this->write( $this->sendBuf );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
	}
	private $trySend = -1;
	public function send() : bool {
		$sended=false;
		$nbSended=0;
		
		$serialize=false;
		$blocking=false;
		$errCode=null;
		
		try {
			if ( $this->packetSize <= 0 && $this->stats() < 0 ) 
				throw new Exception("no packet Size defined");
			$type = 1;
			
			if ( ! $this->isResource() && ! $this->connect() && ! $this->isResource() )
				throw new Exception("[{$this->dstKey->getClassNameProcess()}] queue null or not ressource.");//[data:{$this->sendBuf}]
			$last =  true;
			$packetSizeUtil = 0;
			$packetSize = $this->packetSize;
			do {
				$packetSize -= self::$emptyPacketSize > 0 ? self::$emptyPacketSize : 5;
				$message = substr( $this->sendBuf, $this->sendCpt, $packetSize );
				$packetSizeUtil = strlen( $message );
				$last = ( $this->sendCpt + $packetSizeUtil ) >= $this->sendCptToSend;
				$strData = serialize( DwMessageQueuePacket::message( 
					$this->id
					,$this->rid
					,$this->srcKey
					,$message
					,$last
					,$this->index
				) );
				$packetSizeReal = strlen( $strData );
				//self::$sLog->trace("[".$packetSizeReal."/".$this->packetSize."] try msg");
			} while( $packetSizeReal >= $this->packetSize );
			$nbSended = @msg_send(
				$this->getResource()
				,$type
				,$strData
				,$serialize
				,$blocking
				,$errCode
			);
			if ( $nbSended === false ) {
				$this->trySend++;
				$errorMsg="[{$this->id}] [key:{$this->dstKey->getKey()}]";
				switch($errCode) {
					case MSG_IPC_NOWAIT:
						$errorMsg.="MSG_IPC_NOWAIT echec msg_send";
						break;
						
					case 22 :
					    $debugMsg="({$this->getInstanceKey()})[{$this->dstKey->getClassNameProcess()}/{$this->id}/{$this->trySend}]";
						self::$sLog->warn("$debugMsg error 22\r\n".print_r(error_get_last(),true));
					case MSG_EAGAIN :
					    $debugMsg="({$this->getInstanceKey()})[{$this->dstKey->getClassNameProcess()}/{$this->id}/{$this->trySend}]";
						$dstProcessAlive=true;
						$dstProcessId = $this->dstKey->getProcessId();
						//$errorMsg.="MSG_EAGAIN";
						if ( $this->trySend % 1000 == 0 ) {
							if ( DwProcess::checkPid( $dstProcessId ) ) {
								posix_kill( $dstProcessId, \SIGUSR1 );
								if ( function_exists("pcntl_signal_dispatch") ) \pcntl_signal_dispatch();
								self::$sLog->trace("$debugMsg overflow send kill:$dstProcessId \r\n");
							} else {
								self::$sLog->error("$debugMsg death pid:$dstProcessId no more message !!!\r\n");
								$dstProcessAlive=false;
							}
								
						}
						//self::$sLog->warn("[{$this->id}] exception overflow ignored");
						if ( $dstProcessAlive && $this->trySend < 200000 )
							return false;// exception overflow ignored
						break;
					case MSG_ENOMSG :
						$errorMsg.="MSG_ENOMSG";
						break;
					case MSG_NOERROR :
						$errorMsg.="MSG_NOERROR";
						break;
					case MSG_EXCEPT :
						$errorMsg.="MSG_EXCEPT";
						break;
					default :
						$errorMsg.="errno:$errCode";//intl_error_name($errCode);
						break;
				}
				throw new Exception("($errorMsg)\r\n".print_r(error_get_last(),true)."\r\n\r\n[".$this->sendCpt."/".$this->sendCptToSend."] $packetSize($packetSizeReal/".$this->packetSize.")\r\n\r\n$strData" );
			} elseif ( $nbSended < $packetSizeReal )
					throw new Exception( "echec on msg_send $nbSended < $packetSizeReal" );
			else {
				$this->trySend=-1;
				///*
				if ( $last || $this->index == 0 ) {
					if ( ! is_null( $this->dstKey ) ) {
						//self::$sLog->trace("send posix dst:".$this->dstKey->getProcessId()." \r\n");
						posix_kill( $this->dstKey->getProcessId(), \SIGUSR1 );
					}
					if ( ! is_null( $this->srcKey ) ) {
						//self::$sLog->trace("send posix src:".$this->srcKey->getProcessId()." \r\n");
						posix_kill( $this->srcKey->getProcessId(), \SIGUSR1 );
					}
					if ( function_exists("pcntl_signal_dispatch") ) \pcntl_signal_dispatch();
					
				}
				
				if ( ! $last || $this->index > 0 ) {
					if ( $this->debugEnable )
						self::$sLog->trace( "[{$this->id}/".$this->index."] ".( $last ? "last" : "to continue" ) );
				}
				//*/
				//self::$sLog->trace("[{$this->id}/{$this->index}] ".($last?"dernier":"asuivre")." $packetSizeUtil + {$this->sendCpt} /".$this->sendCptToSend);
				
				$this->sended($packetSizeUtil);
				$this->index++;
				$sended=true;
			}
			
		} catch(Exception $exception) {
			//$this->writeClose();
			self::$sLog->error("exception [{$this->getClassName()}/{$this->getInstanceKey()}]");
			
			throw $exception;
		} catch(Error $error) {
			//$this->writeClose();
			self::$sLog->error("error [{$this->getClassName()}/{$this->getInstanceKey()}]");
			
			throw $error;
		}
		return $sended;
	}
	public static function response(
		DwMessageQueue $dwMessageQueue
	) : DwMessageQueue {
		$dwMessageQueueResponse = new DwMessageQueue(
			$dwMessageQueue->getSrcKey()
		);
		$dwMessageQueueResponse->rid = $dwMessageQueue->getId();
		return $dwMessageQueueResponse;
	}
	
	
	private $tickMessage = null;
	/*
	public function getTickMessage () {
		return $this->tickMessage;
	}
	//*/
	private function tickMessage( ) {
		$tickMessage = false;
		try {
			if ( ! $this->isResource() && ! $this->connect() )
				return array( 'msg_qnum' => 0 );
			if ( ! $this->isResource() )
				throw new Exception("queue null or not resource.");
			
			$tickMessage = msg_stat_queue( $this->getResource() );
		if ( $tickMessage === false )
			throw new \Exception(
				"echec msg_stat_queue :"
				.$this->dwMessageQueueListen->getResource()
			);
		
		return $tickMessage;
	}
	public function stats() : int {
		$return = -1;
		try {
			//self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
			$this->tickMessage = $this->tickMessage();
			
			if ( ! is_array($this->tickMessage) ) 
				throw new Exception( "echec tickMessage from msg_stat_queue not an array." );
			else {
				/*
				array(
				'msg_perm.uid'		// The uid of the owner of the queue.
				,'msg_perm.gid'		// The gid of the owner of the queue.
				,'msg_perm.mode'	// The file access mode of the queue.
				,'msg_stime'		// The time that the last message was sent to the queue.
				,'msg_rtime'		// The time that the last message was received from the queue.
				,'msg_ctime'		// The time that the queue was last changed.
				,'msg_qnum'			// The number of messages waiting to be read from the queue.
				,'msg_qbytes'		// The maximum number of bytes allowed in one message queue. On Linux, this value may be read and modified via /proc/sys/kernel/msgmnb.
				,'msg_lspid'		// The pid of the process that sent the last message to the queue.
				,'msg_lrpid '		// The pid of the process that received the last message from the queue.
				)
				//*/
				if ( ! $this->isPacketAutoSize() && isset( $this->tickMessage['msg_qbytes' ] ) ) {
				//if ( isset( $this->tickMessage['msg_qbytes' ] ) ) {
					$this->setPacketSize( intval( $this->tickMessage['msg_qbytes' ] ) / 2 );
					//$this->setPacketSize( 2048 );
				}
				if ( isset( $this->tickMessage[ 'msg_qnum' ] ) )
					$return = intval( $this->tickMessage[ 'msg_qnum' ] );
				else
					$return = 0;
			}
			
		} catch(Exception $exception) {
			self::$sLog->error("exception (key:".$this->dstKey->getKey().") [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error (key:".$this->dstKey->getKey().") [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
		return $return;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ 
			: __CLASS__
			;
	}
}

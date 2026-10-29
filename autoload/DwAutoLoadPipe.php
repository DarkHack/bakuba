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

namespace bakuba\kernel\autoload;
//use bakuba\kernel\autoload\DwAutoLoadPipe as DwAutoLoadPipe;

use bakuba\kernel\agartha\DwBakubaRequest as DwBakubaRequest;
use bakuba\kernel\agartha\DwBakubaResponse as DwBakubaResponse;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;
use darkphp\DwObjectTimer as DwObjectTimer;
use bakuba\kernel\io\DwResources as DwResources;

use bakuba\kernel\io\messageQueue\DwMessageQueue as DwMessageQueue;
use bakuba\kernel\io\messageQueue\DwMessageQueueServer as DwMessageQueueServer;
use bakuba\kernel\io\messageQueue\DwMessageQueueKey as DwMessageQueueKey;
use bakuba\kernel\io\messageQueue\DwMessageQueueSelect as DwMessageQueueSelect;
//
//
abstract class DwAutoLoadPipe extends DwObject {
	private static $sLog = null;
	public static function initDebug( /*\darkphp\logger\DwLogger*/ $dwLogger ,bool $withParent = false ) : void {
		self::$sLog = $dwLogger;
		self::$sLog->trace( "[".__CLASS__."]".($withParent ? "with parent":"this only" ) );
		//if ( $withParent )
		//	parent::initDebug( $dwLogger ,$withParent );
		$mypid = getmypid();
		if ( isset( self::$serverByPid[ $mypid ] ) ) {
			$dwMessageQueueServer = self::$serverByPid[ $mypid ]["dwMessageQueueServer"];
			$dwMessageQueueSelect = self::$serverByPid[ $mypid ]["dwMessageQueueSelect"];
			$dwResources = self::$serverByPid[ $mypid ]["dwResources"];
			
			$dwResources->enableDebug( true );
			$dwMessageQueueSelect->enableDebug( );
		}
	}

	static protected $sourcesFile = array();
	static private $requestSended = false;
	
	static private $serverByPid = array();
	static private $cptRequest = 0;
	static private $cptSubRequest = 0;
	public static function parentGetSource( string $filePath ) : ?string {
		global $dwBakuba;
		if ( is_object( self::$sLog ) )
			self::$sLog->trace( "[".__CLASS__."]ask(".getmypid()."):$filePath" );
		
		$mypid = getmypid();
		if ( isset( self::$serverByPid[ $mypid ] ) ) {
			$dwMessageQueueServer = self::$serverByPid[ $mypid ]["dwMessageQueueServer"];
			$dwMessageQueueSelect = self::$serverByPid[ $mypid ]["dwMessageQueueSelect"];
			$dwResources = self::$serverByPid[ $mypid ]["dwResources"];
		} else {
			self::$cptRequest = 0;
			self::$cptSubRequest++;
			$dwResources = new DwResources();
			$dwMessageQueueServer = new DwMessageQueueServer( new DwMessageQueueKey( 
				$mypid."/".self::getStaticClassName()
			) );
			$dwMessageQueueServer->refreshPid( getmypid() );
			
			$dwMessageQueueSelect =  new DwMessageQueueSelect( $dwResources, $dwMessageQueueServer );
			$dwMessageQueueServer->startInObject(
				$dwMessageQueueSelect
				,new DwCallBack( array( self::getStaticClassName(), 'messagingHandle' ) )
			);
			/*
			if ( is_object( self::$sLog ) ) {
				$dwResources->enableDebug( true );
				$dwMessageQueueSelect->enableDebug( );
			}
			//*/
			self::$serverByPid[ $mypid ] = array(
				"dwMessageQueueServer" => $dwMessageQueueServer
				,"dwMessageQueueSelect" => $dwMessageQueueSelect
				,"dwResources" => $dwResources
			);
		}
		self::$cptRequest++;
		self::$cptTry=0;
		self::trySend( $filePath ,$dwMessageQueueServer ,$dwMessageQueueSelect ,$dwResources );
		
		$cpt=-1;
		while( self::$requestSended !== false ) 
			DwObjectTimer::runTimer( $dwResources );
		
		if ( is_object( self::$sLog ) )
			self::$sLog->trace( "[".__CLASS__."](".self::$cptTry.") $filePath" );
		return self::$sourcesFile[ $filePath ];
	}
	private static $cptTry = 0;
	private static $timeoutTimer = null;
	public static function trySend(
		string $filePath
		,DwMessageQueueServer $dwMessageQueueServer
		,DwMessageQueueSelect $dwMessageQueueSelect
		,DwResources $dwResources ) {
		global $dwBakuba;
		
		if ( isset( self::$sourcesFile[ $filePath ] ) ) {
			if ( is_object( self::$sLog ) )
				self::$sLog->trace( "[".__CLASS__."](".self::$cptTry.") ever received $filePath/".self::$requestSended );
			return ;
		} else {
			$dwResources->restart();
			$dwResources->addSignal( \SIGUSR1 );
			self::$cptTry++;
			self::$requestSended = $filePath;
			if ( is_object( self::$sLog ) )
				self::$sLog->trace( "try:".self::$cptTry." (".self::$cptSubRequest."/".self::$cptRequest.")\r\n{$filePath}" );
			$dwMessageQueue = $dwBakuba->connect();
			$dwMessageQueue->write( serialize( new DwBakubaRequest( "getsource", $filePath ) ) );
			$dwMessageQueue->source( $dwMessageQueueServer->getQueueKey() );
			
			$cbTrySend = new DwCallBack(
				array( self::getStaticClassName(), "trySend" )
				,$filePath
				,$dwMessageQueueServer
				,$dwMessageQueueSelect
				,$dwResources
			);
			// timeout 3s
			if ( is_object( self::$timeoutTimer ) )
				$dwResources->cancelTimer( self::$timeoutTimer );
			self::$timeoutTimer = $dwResources->addTimer( 3, $cbTrySend );
			$dwMessageQueueSelect->messageQueueWrite(
				$dwMessageQueue
				,new DwCallBack( array( self::getStaticClassName(), "writed" ) )
				,$cbTrySend
			);
		}
	}
	public static function writed() {
		if ( is_object( self::$sLog ) )
			self::$sLog->trace( "autoloadMessaging writed( ".self::$cptSubRequest."/".self::$cptRequest.") " );
	}
	public static function messagingHandle( DwMessageQueue $dwMessageQueue ) {
		$mypid = getmypid();
		$filePath = self::$requestSended;
		self::$requestSended = false;
		
		if ( isset( self::$serverByPid[ $mypid ] ) ) {
			$dwMessageQueueServer = self::$serverByPid[ $mypid ]["dwMessageQueueServer"];
			$dwMessageQueueSelect = self::$serverByPid[ $mypid ]["dwMessageQueueSelect"];
			$dwResources = self::$serverByPid[ $mypid ]["dwResources"];
			
			$dwResources->stop();
		}  elseif ( is_object( self::$sLog ) )
			self::$sLog->error( " no server/select/resources" );
		
		if ( is_object( self::$sLog ) )
			self::$sLog->trace( "messagingHandle( ".self::$cptSubRequest."/".self::$cptRequest.") " );
		
		$msgData = $dwMessageQueue->read();
		try {
			$contentObj = unserialize( $msgData );
		} catch(Exception $exception_unserialize ) {
			if ( is_object( self::$sLog ) ) {
				self::$sLog->error( "unserialize exception [".$this->getClassName()."/".$this->getInstanceKey()."]\r\n$msgData" );
				self::$sLog->throwable($exception_unserialize);
			}
			//throw $exception_unserialize;
		} catch(Error $error_unserialize) {
			if ( is_object( self::$sLog ) ) {
				self::$sLog->error( "unserialize error [".$this->getClassName()."/".$this->getInstanceKey()."]\r\n$msgData" );
				self::$sLog->throwable( $error_unserialize );
			}
			//throw $error_unserialize;
		}
		if ( is_a( $contentObj, DwBakubaResponse::getStaticClassName(), false ) )
			self::$sourcesFile[ $filePath ] = base64_decode( $contentObj->getMessage() );
		else {
			if ( is_object( self::$sLog ) ) {
				self::$sLog->error( "we try again\r\n{$filePath}\r\nechec on :{$contentObj}\r\n{$msgData}" );
			}
			self::trySend( $filePath ,$dwMessageQueueServer ,$dwMessageQueueSelect ,$dwResources );
		}
	}
	///*
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return __CLASS__;
	}
	//*/
}
?>

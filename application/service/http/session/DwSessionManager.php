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

namespace bakuba\kernel\session;
//use bakuba\kernel\session\DwSessionManager as DwSessionManager;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\services\http\DwHttpServiceRequest as DwHttpServiceRequest;

class DwSessionManager extends \darkphp\DwObject {
	
	private function __construct( \darkphp\DwObject $dwObject ) {
		$debugKey="{$this->getClassName()}";
		parent::__construct( $dwObject );
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		
		$this->log->trace("[{$debugKey}]");
		
		$this->init();
	}
	protected function init( ) : void {
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		$this->log->trace("[{$debugKey}]");
		//parent::init();
	}
	private $sessions = array();
	private $searchInProgress = array();
	public function request(
		\bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg
		,DwHttpServiceRequest $dwHttpServiceRequest
	) : void {
		$this->log->trace( $dwIpcMsg );
		
		$requestId = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( $this->tabCbId );
		$this->searchInProgress[ $requestId ]=array();
		
		foreach( $this->sessions as $channelKey => $dwSessionChannel ) {
			$this->log->trace("[{$debugKey}] try channel:$channelKey");
			$this->searchInProgress[ $requestId ][$channelKey]=1;
				$dwSessionChannel->searchSession(
					$dwHttpServiceRequest
					,new DwCallBack(
						array( &$this, 'tryedSession' )
						,$requestId
						,$dwDoneCallBack
						,$channelKey
					)
				);
			}
	}
	public function SessionStart(
		DwHttpServiceRequest $dwHttpServiceRequest
		,DwCallBack $dwCallBack
		,string $classNameDwSessionChannel
	) : void {
		//$this->log->trace(".");
		$dwSessionChannel = $this->getSession(
			$dwHttpServiceRequest
			,$classNameDwSessionChannel
			,new DwCallBack(
				array( &$this ,"SessionStarted" ) 
				,$dwCallBack
				,$classNameDwSessionChannel
				,$dwHttpServiceRequest
			)
		);
	}
	
	public function getSession(
		DwHttpServiceRequest $dwHttpServiceRequest
		,string $classNameDwSessionChannel
		,DwCallBack $dwCallBack
	) : void {
		if ( \count( $this->channelByKeys ) > 0 )
			$this->searchSession(
				$dwHttpServiceRequest
				,$dwCallBack
			);
		else
			$this->createSession(
				$dwHttpServiceRequest
				,$classNameDwSessionChannel
				,$dwCallBack
			);
		
	}
	
	protected $channelByKeys = array();
	public function createSession(
		DwHttpServiceRequest $dwHttpServiceRequest
		,string $classNameDwSessionChannel
		,DwCallBack $dwDoneCallBack
	) : void {
		$this->log->trace(".");
		$chKey = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( $this->channelByKeys );
		
		$dwSessionChannel = new $classNameDwSessionChannel( $this ,$chKey );
		$this->channelByKeys[ $chKey ] = $dwSessionChannel;
		
		$dwSessionChannel->initSession( $dwHttpServiceRequest ,$dwDoneCallBack );
	}
	public function SessionStarted(
		DwCallBack $dwCallBack
		,string $classNameDwSessionChannel
		,DwHttpServiceRequest $dwHttpServiceRequest
		,DwSessionChannel $dwSessionChannel = null
	) : void {
		if ( is_null( $dwSessionChannel ) )
			$this->createSession(
				$dwHttpServiceRequest
				,$classNameDwSessionChannel
				,new DwCallBack(
					array( &$this ,"SessionStarted" ) 
					,$dwCallBack
					,$classNameDwSessionChannel
					,$dwHttpServiceRequest
				)
			);
		else
			$dwCallBack->callback( new DwSessionResponse( $dwSessionChannel ) );
	}
	
	public function SessionWrite(
		DwSessionRequest $dwSessionRequest
	) : DwSessionResponse {
		$dwSessionResponse = null;
		
		$dwSessionChannel = $dwSessionRequest->getObject();
		$found=false;
		$chKey = $dwSessionChannel->getChannelKey( );
		
		$this->log->trace("[chanKey:{$chKey}]");
		
		if ( isset( $this->channelByKeys[ $chKey ] ) )
			$this->channelByKeys[ $chKey ] = $dwSessionChannel;
		$dwSessionResponse = new DwSessionResponse( 
			$found ? "writed" : "no writed"
		);
		
		return $dwSessionResponse;
	}
	
	public function createdSession(
		DwCallBack $dwCallBack
		,DwSessionChannel $dwSessionChannel
	) {
		$this->editSession( $dwSessionChannel );
		$dwCallBack->callback( $dwSessionChannel );
	}
	public function getSessionByChanKey( string $chanKey ) : DwSessionChannel {
		return $this->channelByKeys[ $chanKey ];
	}
	private $lunchedSearchSession = null;
	private $foundSession = null;
	public function searchSession(
		DwHttpServiceRequest $dwHttpServiceRequest
		,DwCallBack $dwDoneCallBack
		,int $idxToSearch=0
		,DwSessionChannel $dwSessionChannelFound = null
	) : void {
		$this->log->trace( array( $idxToSearch ,$dwSessionChannelFound ) );
		if ( is_null( $dwSessionChannelFound ) ) {
			$idx=0;$noMoreSearch=true;
			foreach( $this->channelByKeys as $chKey => $dwSessionChannel ) {
				if ( $idx == $idxToSearch ) {
					$dwSessionChannel->searchSession(
						$dwHttpServiceRequest
						,new DwCallBack(
							array(&$this,"searchSession" )
							,$dwHttpServiceRequest
							,$dwDoneCallBack
							,( $idxToSearch + 1 )
						)
					);
					$noMoreSearch=false;
					break;
				}
				$idx++;
			}
			if ( $noMoreSearch )
				$dwDoneCallBack->callback( null );
		} else
			$dwDoneCallBack->callback( $dwSessionChannelFound );
	}
	public function tryedSession(
		DwCallBack $dwFoundCallBack
		,string $channelKey
		,DwSessionChannel $dwSessionChannel = null
	) : void {
		$debugKey="{$this->getClassName()}/channelKey:{$channelKey}";
		unset($this->lunchedSearchSession[$channelKey]);
		$nbSearch = count($this->lunchedSearchSession);
		
		$this->log->trace( "[{debugKey}] nb in search:{$nbSearch}" );
		if ( is_object( $dwSessionChannel ) )
			$this->foundSession = $dwSessionChannel;
		
		if ( $nbSearch == 0 )
			$dwFoundCallBack->callback( $this->foundSession );
	}
	/*
	public function readSession( string $channelKey ) : ?DwSessionChannel {
		$dwSessionChannel = null;
		try {
			if ( isset( $this->ipChannels[ $channelKey ] ) )
				$dwSessionChannel = $this->ipChannels[ $channelKey ];
			else
				throw new Exception("no SessionChannel with key:$channelKey");
		} catch(Exception $exception) {
			$this->log->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			$this->log->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $dwSessionChannel;
	}
	public function deleteSession( DwSessionChannel $dwSessionChannel ) : bool {
		$isDeleted = false;
		try {
			$channelKey = $dwSessionChannel->getChannelKey();
			$ipKey = $dwSessionChannel->getIpKey();
			if ( ! isset( $this->ipChannels[ $channelKey ] ) )
				$isDeleted &= false;
			else {
				unset($this->ipChannels[ $channelKey ]);
				$isDeleted = true;
			}
			$idToDelete = null;
			foreach( $this->channelByKeys[$ipKey] as $id => $anChannelKey ) {
				if ( $channelKey == $anChannelKey ) {
					$idToDelete = $id;
					break;
				}
			}
			if ( ! isset($this->channelByKeys[$ipKey][$idToDelete]) )
				$isDeleted &= false;
			else {
				$isDeleted &= true;
				unset($this->channelByKeys[$ipKey][$idToDelete]);
				if (empty($this->channelByKeys[$ipKey]))
					unset($this->channelByKeys[$ipKey]);
			}
			
		} catch(Exception $exception) {
			$this->log->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			$this->log->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $isDeleted;
	}
	public function listSession() : array {
		$sessions = array();
		try {
			$sessions = $this->ipChannels;
		} catch(Exception $exception) {
			$this->log->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			$this->log->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $sessions;
	}
	//*/
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

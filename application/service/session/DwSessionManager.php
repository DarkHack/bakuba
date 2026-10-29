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

namespace bakuba\kernel\application\service\session;
//use bakuba\kernel\application\service\session\DwSessionManager as DwSessionManager;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\services\DwServiceRequest as DwServiceRequest;

class DwSessionManager extends \bakuba\kernel\application\session\aDwSessionManager {
	
	public function __construct( aDwApplicationSession $parent ) {
		parent::__construct( $parent );
	}
	
	private $sessions = array();
	private $searchInProgress = array();
	public function request(
		\bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg
		,DwServiceRequest $dwServiceRequest
	) : void {
		$this->log->trace( $dwIpcMsg );
		$requestId = $dwIpcMsg->getId();
		$size=3;
		$requestId = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( $this->searchInProgress );
		$this->searchInProgress[ $requestId ]=array();
		
		foreach( $this->sessions as $channelKey => $dwSessionChannel ) {
			$this->log->trace("[{$debugKey}] try channel:$channelKey");
			$this->searchInProgress[ $requestId ][$channelKey]=1;
				$dwSessionChannel->searchSession(
					$dwServiceRequest
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
		DwServiceRequest $dwServiceRequest
		,DwCallBack $dwCallBack
		,string $classNameDwSessionChannel
	) : void {
		//$this->log->trace(".");
		$dwSessionChannel = $this->getSession(
			$dwServiceRequest
			,$classNameDwSessionChannel
			,new DwCallBack(
				array( &$this ,"SessionStarted" ) 
				,$dwCallBack
				,$classNameDwSessionChannel
				,$dwServiceRequest
			)
		);
	}
	
	public function getSession(
		DwServiceRequest $dwServiceRequest
		,string $classNameDwSessionChannel
		,DwCallBack $dwCallBack
	) : void {
		if ( \count( $this->channelByKeys ) > 0 )
			$this->searchSession(
				$dwServiceRequest
				,$dwCallBack
			);
		else
			$this->createSession(
				$dwServiceRequest
				,$classNameDwSessionChannel
				,$dwCallBack
			);
		
	}
	
	protected $channelByKeys = array();
	public function createSession(
		DwServiceRequest $dwServiceRequest
		,string $classNameDwSessionChannel
		,DwCallBack $dwDoneCallBack
	) : void {
		$this->log->trace(".");
		$chKey = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( $this->channelByKeys );
		
		$dwSessionChannel = new $classNameDwSessionChannel( $this ,$chKey );
		$this->channelByKeys[ $chKey ] = $dwSessionChannel;
		
		$dwSessionChannel->initSession( $dwServiceRequest ,$dwDoneCallBack );
	}
	public function SessionStarted(
		DwCallBack $dwCallBack
		,string $classNameDwSessionChannel
		,DwServiceRequest $dwServiceRequest
		,DwSessionChannel $dwSessionChannel = null
	) : void {
		if ( is_null( $dwSessionChannel ) )
			$this->createSession(
				$dwServiceRequest
				,$classNameDwSessionChannel
				,new DwCallBack(
					array( &$this ,"SessionStarted" ) 
					,$dwCallBack
					,$classNameDwSessionChannel
					,$dwServiceRequest
				)
			);
		else
			$dwCallBack->callback( new DwSessionResponse( $dwSessionChannel ) );
	}
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

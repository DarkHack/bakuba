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

namespace bakuba\kernel\application\session;
//use bakuba\kernel\application\session\aDwSessionManager as aDwSessionManager;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwSessionManager extends \darkphp\DwObject {
	
	public function __construct( aDwApplicationSession $parent ) {
		parent::__construct( $parent );
	}
	public function getDwApplicationSession() : aDwApplicationSession {
		return $this->parent;
	}
	protected $channelByKeys = array();
	
	public function connect( aDwApplicationConnexion $aDwApplicationConnexion ,DwCallBack $dwCallBack ) : void {
		$dwIpcKey = $aDwApplicationConnexion->getSrcIpcKey();
		$intKey = $dwIpcKey->getKey();
		if ( ! isset( $this->channelByKeys[ $intKey ] ) ) 
			$this->channelByKeys[ $intKey ] = $this->createSession( $aDwApplicationConnexion ,$intKey );
		$this->getDwApplicationSession()->addTick( $dwCallBack->getClone( $this->channelByKeys[ $intKey ] ) );
	}
	public function createSession(
		aDwApplicationConnexion $aDwApplicationConnexion
		,int $intKey
	) : DwSessionChannel {
		$classNameDwSessionChannel = $aDwApplicationConnexion->getSessionClassName();
		return new $classNameDwSessionChannel( $this ,$intKey );
	}
	
	public function SessionWrite(
		DwSessionRequest $dwSessionRequest
	) : DwSessionResponse {
		$dwSessionResponse = null;
		
		$dwSessionChannel = $dwSessionRequest->getDwSessionChannel();
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
	
	public function getSessionByChanKey( int $chanKey ) : DwSessionChannel {
		return $this->channelByKeys[ $chanKey ];
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

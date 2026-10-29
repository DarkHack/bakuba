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

namespace bakuba\kernel\application\service\http\controller;
//use bakuba\kernel\application\service\http\controller\DwControllerIpcConnexion as DwControllerIpcConnexion;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwControllerIpcConnexion extends DwControllerConnexion {
	
	private $objectResponse = null;
	private $tabCbId = array();
	public function __construct(
		\bakuba\kernel\application\service\http\aDwApplicationConnexion $aDwApplicationConnexion
		,iDwController $iDwController
	) {
		parent::__construct( $aDwApplicationConnexion ,$iDwController );
		$this->log->trace( "." );
	}
	
	public function ipcSend(
		\bakuba\kernel\process\iDwProcessIpc $dwProcessDst
		,DwCallBack $dwCallBack
		,$dwObjRequest
	) : void {
		$dwApplicationConnexion = $this->getDwApplicationConnexion();
		$dwApplication = $dwApplicationConnexion->getDwApplication();
		$cbId = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( $this->tabCbId );
		$this->log->info( " send for {$cbId}" );
		
		$this->tabCbId[$cbId] = $dwCallBack;
		$dwApplication->ipcSend(
			new DwCallBack( array( $this,"messagingCb" ), $cbId )
			,$dwProcessDst
			,$dwObjRequest
		);
		
	}
	public function messagingWrite(
		DwControllerConnexion $dwControllerConnexion
		,\bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg
		,DwCallBack $dwCallBack
	) : void {
		$dwApplicationConnexion = $dwControllerConnexion->getDwApplicationConnexion();
		$dwProcessMessaging = $dwApplicationConnexion->getDwProcessMessaging();
		
		$cbId = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( $this->tabCbId );
		$this->log->info( " send for {$cbId}" );
		$this->tabCbId[$cbId] = $dwCallBack;
		$dwIpcMsg->setOnWrited( new DwCallBack( array( $this,"messagingCb" ), $cbId ) );
		$dwProcessMessaging->ipcWrite( $dwIpcMsg );
	}
	public function messagingCb( $processIdCb, $response ) {
		if ( isset( $this->tabCbId[$processIdCb] ) ) {
			$callback = $this->tabCbId[$processIdCb];
			// no cb 
			unset($this->tabCbId[$processIdCb]);
			
			try {
				if ( \is_a( $response ,\bakuba\kernel\io\ipc\DwIpcMsgError::getStaticClassName() ) ) {
					
					$this->log->fatal( $dwIpcMsgError );
				} else
					$callback->callback( $response );
			} catch( \Exception $exception_cb) {
				$this->log->error("exception_cb");
				$this->log->throwable( $exception_cb );
				//throw $exception_cb;
			} catch( \Error $error_cb) {
				$this->log->error("error_cb");
				$this->log->throwable( $error_cb );
				//throw $error_cb;
			}
		} else { 
			$this->log->error(  "no callback id for response." );
			$this->log->error(  $response );
		}
		
		if ( is_null( $this->objectResponse ) )
			$this->log->error( "end wait next message for now, no response defined" );
		else {
			$anData = $this->objectResponse;
			$this->objectResponse = null;
			$this->response( $anData  );
		}
	}
	///*
	public function response( string $data ) : void {
		if ( empty( $this->tabCbId ) )
			parent::response( $data );
		else {
			if ( is_null( $this->objectResponse ) ) {
				$this->log->info("force synchrone, wait next callback message ,but response is ready.");
				$this->objectResponse = $data;
			} else
				throw new \Exception("an response, ever registered!");
		}
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

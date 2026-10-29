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

namespace bakuba\kernel\io\ipc;
//use bakuba\kernel\io\ipc\DwIpcRouting as DwIpcRouting;

//use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwIpcRouting extends \darkphp\DwObject {
	
	private $ipcParent = null;
	public function __construct( \bakuba\kernel\process\DwResources $parent, self $ipcParent = null ) {
		$this->ipcParent=$ipcParent;
		parent::__construct( $parent );
		$this->log->trace(".");
	}
	
	private $routeHandles = array();
	public function addRoute( DwIpcServer $dwIpcServerRouteHandle  ) {
		$dwIpcKey = $dwIpcServerRouteHandle->getQueueKey();
		$key = $dwIpcKey->getKey();
		$this->log->trace( $dwIpcKey->getClassNameProcess().":{$key}" );
		if ( isset( $this->routeHandles[ $key ] ) )
			throw new \Exception( "route ever registered" );
		else
			$this->routeHandles[ $key ] = $dwIpcServerRouteHandle;
	}
	
	private function send( DwIpcServer $dwIpcServerRouteHandle ,DwIpcMsg $dwIpcMsg ) {
		//$this->log->trace("send => {$dwIpc->getQueueKey()->getClassNameProcess()} : {$dwIpcMsg->getDstKey()->getClassNameProcess()}");
		$dwIpcServerRouteHandle->msgWrite( $dwIpcMsg );
	}
	
	public function routing( DwIpcMsg $dwIpcMsg ,DwCallBack $onWrited = null ) : bool {
		$isRouted =false;
		$dwIpcRouteHandle = null;
		
		if ( isset( $this->routeHandles[ $dwIpcMsg->getDstKey()->getKey() ] ) ) {
			$dwIpcRouteHandle = $this->routeHandles[ $dwIpcMsg->getDstKey()->getKey() ];
		} else {
			if ( \is_null( $this->ipcParent ) )
				$this->log->error("no more parent route receiveRouting for => {$dwIpcMsg->getDstKey()->getClassNameProcess()} : {$dwIpcMsg->getDstKey()->getKey()}");
			else {
				$this->log->warn("no route try parent for => {$dwIpcMsg->getDstKey()->getClassNameProcess()} : {$dwIpcMsg->getDstKey()->getKey()}");
				$dwIpcRouteHandle=$this->ipcParent;
			}
		}
		if ( \is_object( $dwIpcRouteHandle ) ) {
			$this->send( $dwIpcRouteHandle ,$dwIpcMsg );
			$isRouted=true;
			if ( \is_object( $onWrited ) )
				$onWrited->callback( $dwIpcMsg );
		} else {
			$this->log->error("echec routing for : ");
			$this->log->error("{$dwIpcMsg->getSrcKey()->getClassNameProcess()} : {$dwIpcMsg->getSrcKey()->getKey()} => {$dwIpcMsg->getDstKey()->getClassNameProcess()} : {$dwIpcMsg->getDstKey()->getKey()}");
		}
		
		return $isRouted;
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

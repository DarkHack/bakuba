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

namespace bakuba\kernel\agartha;
//use bakuba\kernel\agartha\DwAgartha as DwAgartha;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwAgartha extends \darkphp\DwObject {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,\darkphp\source\DwSource::classNameToFirstSectionName( $this->getClassName() )
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	// singleton class pattern:
	private static $instance = null;
	public static function singleton( DwBakuba $parent ) : DwAgartha {
		null === self::$instance and self::$instance = new self( $parent );
		return self::$instance;
	}
	// construct with logger
	private $dwControllerClient = null;
	private $dwMap;
	private function __construct( DwBakuba $parent ) {
		/*
		if ( $initAgartha != null )
			$this->dwControllerClient = new DwCryptControllerClient( $b64CryptoChannel, "Agartha" );
		else 
		//*/
			$this->dwControllerClient = null;
		
		parent::__construct(  $parent );
		$this->log->trace(".");
	}
	public function isMaster() {
		return $this->dwControllerClient == null;
	}
	/*
	public function initMap() {
		$this->log->trace(".");
		//$this->cptPrevInit=15*40 - 1;
		//$this->cptPrev=$this->cptPrevInit;
		
		$dwItem = $this->dwMap->createItem( 4, 14, 4, 4 );
		$dwItem->set("&#xf12e;");// personn
		
		$dwItem = $this->dwMap->createItem( 11, 5, 8, 8 );
		$dwItem->set("&#xf203;");// list
		
		$dwItem = $this->dwMap->createItem( 2, 30, 3, 15 );
		$dwItem->set("&#xf209;");// road
	}
	//*/
	public function getMap() {
		return $this->dwMap;
	}
	private $cptNode = 0;
	private $dwNodes = array();
	public function createNode( iDwAgent $iDwAgent ,string $nodePass = null ) {
		$dwNode = null;
		$this->log->trace(".");
		
		$dwNode = new DwNode( $this ,$iDwAgent ,$nodePass );
		$this->dwNodes[]=$dwNode;
		$this->cptNode++;
		return $dwNode;
	}
	//
	public function handleNodeRequest(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwNodeRequest $dwNodeRequest
	) : void {
		global $dwBakuba;
		$dwNodeResponse = new DwNodeResponse( "what???" );
		$arrSearchNode = $dwNodeRequest->getParams();
		$nodeUUID = $arrSearchNode[0];
		
		$dwNode = null;
		foreach( $this->dwNodes as $anDwNode ) {
			if ( $anDwNode->getUUID() == $nodeUUID )
				$dwNode = $anDwNode;
		}

		switch ( $dwNodeRequest->getOrder() ) {
		case "executeNode" :
			$orderId = $arrSearchNode[1];
			$result = $arrSearchNode[2];
			$dwNode = null;
			foreach( $this->dwNodes as $anDwNode ) {
				if ( $anDwNode->getUUID() == $nodeUUID )
					$dwNode = $anDwNode;
			}
			$dwShAgent = $dwNode->getShAgent();
			$dwShAgent->executeOrder( $orderId ,$result );
			$dwNodeResponse = new DwNodeResponse( "executed" );
			break;
		case "createShNode" :
			$dwShAgent = \bakuba\kernel\dwNode\shAgent\DwShAgent::createShAgent( $this );
			$dwShAgent->setUUID( $nodeUUID );
			$dwNode = $this->createNode( $dwShAgent );
			$dwNodeResponse->setDwShAgent( $dwShAgent );
			break;
		case "getNode" :
			/*
			if ( is_null( $dwNode ) ) {
				$nodePass = $arrSearchNode[1];
				$dwNode = $dwBakuba->getNode();
				if ( $nodePass != $dwNode->getMasterPass() )
					self::$sLog->error("wrong nodePass!");
				elseif ( $dwNode->getShAgent() != null )
					self::$sLog->error("shAgent ever defined!");
				else {
					$dwNode->setUUID( $nodeUUID );
					$dwShAgent = $dwNode->createShAgent();
				}
				
			} else
			//*/
				$dwShAgent = $dwNode->getShAgent();
			$dwNodeResponse->setDwShAgent( $dwShAgent );
			break;
		default :
			$dwNodeResponse = new DwNodeResponse( "unknow order:".$dwNodeRequest->getOrder() );
			break;
		}
		$dwProcessConnexion->ipcResponse( $dwNodeResponse  );
	}
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) )
				unset( $this->$key );
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

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
//use bakuba\kernel\io\ipc\DwIpcSelect as DwIpcSelect;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwIpcSelect extends \bakuba\kernel\io\DwResourceSelect {
	
	private $dwIpcRouting = null;
	public function getDwIpcRouting() : DwIpcRouting {
		return $this->dwIpcRouting;
	}
	private function setDwIpcRouting( DwIpcRouting $dwIpcRouting ) : void {
		$this->dwIpcRouting = $dwIpcRouting;;
	}
	/*
	public function addRoute( DwIpcServer $dwIpcServerRouteHandle  ) {
		$this->dwIpcRouting->addRoute( $dwIpcServerRouteHandle );
	}
	//*/
	
	public function __construct(
		\bakuba\kernel\process\DwResources $parent
	) {
		$this->setDwIpcRouting( $parent->getDwIpcRouting() );
		parent::__construct( $parent );
		$this->log->trace(".");
	}
	public function ipcWrite( DwIpc $dwIpc ) : void {
		$this->writeLunch( $dwIpc );
	}
	public function ipcRead( DwIpc $dwIpc ) : void {
		$this->readLunch( $dwIpc );
	}
	
	//messaging_select
	public function messaging_select( array &$dwResourcesRead, array &$dwResourcesWrite, array &$dwResourcesExcept ) : int {
		$nbReady=0;
		$nbRead=0;
		$nbReaded=0;
		$nbWrite=0;
		$nbWrited=0;
		//$nbStreamListen=0;
		
		$ipcsToRead = array();
		$nbRead=count( $dwResourcesRead );
		foreach( $dwResourcesRead as $index => $dwIpc ) {
			$dwIpcKey = $dwIpc->getDstIpcKey();
			if ( is_object( $dwIpcKey ) &&  ! $dwIpc->readEnd() ) {
				$ipcsToRead[ $dwIpcKey->getKey() ] = $dwIpc;
			} else 
				unset($dwResourcesRead[$index]);
		}
		
		$ipcsToWrite = array();
		$nbWrite=count( $dwResourcesWrite );
		foreach($dwResourcesWrite as $index => $dwIpc) {
			$dwIpcKey = $dwIpc->getDstIpcKey();
			$ipcKey = $dwIpcKey->getKey();
			if ( isset( $ipcsToRead[ $ipcKey ] ) ) {
				$ipcInRead = $ipcsToRead[ $ipcKey ];
				if ( ! $ipcInRead->readEnd() ) {
					$ipcInRead->write( $dwIpc->read() );
					$nbReady++;
				} else 
					unset($dwResourcesWrite[$index]);
				
				unset( $ipcsToRead[ $ipcKey ] );
			} elseif ( ! $this->getDwIpcRouting()->routing( $dwIpc->read() ) ) {
				$this->log->error( "echec Routing" );
				$this->log->error( $dwIpc );
				//unset($dwResourcesWrite[$index]);
			}
		}
		$streamsExpected = array();
		
		//*/
		
		$this->lastState ="ready:$nbReady ";
		$this->lastState.="r:$nbReaded/$nbRead ";
		$this->lastState.="w:$nbWrited/$nbWrite ";
		//$this->lastState.="l:$nbStreamListen ";
		$this->log->trace( $this->lastState );
		
		return $nbReady;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

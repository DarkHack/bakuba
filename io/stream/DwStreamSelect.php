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

namespace bakuba\kernel\io\stream;
//use bakuba\kernel\io\stream\DwStreamSelect as DwStreamSelect;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
//use darkphp\DwObject as DwObject;

class DwStreamSelect extends \bakuba\kernel\io\DwResourceSelect {
	/*
	public function __construct( \darkphp\DwObject $parent ,\bakuba\kernel\io\iDwResources $dwResources ) {
		parent::__construct( $parent ,$dwResources );
	}
	//*/
	
	public function streamWrite( DwStream $dwStream ) : void {
		$this->writeLunch( $dwStream );
	}
	public function streamRead( DwStream $dwStream ) : void {
		$this->readLunch( $dwStream );
	}
	public function streamSocketWrite( DwStreamSocket $dwStreamSocket ) : void {
		$this->writeLunch( $dwStreamSocket );
	}
	public function streamSocketRead( DwStreamSocket $dwStreamSocket ) : void {
		$this->readLunch( $dwStreamSocket );
	}
	
	public function read( array &$dwResourcesRead, array &$dwResourcesExcept ) : array {
		$dwResourcesReaded = array();
		$dwResource = null;
		//$this->log->trace(".");
		foreach( $dwResourcesRead as $dwResource ) {
			try {
				if ( $dwResource->isListen() ) {
					$dwResource->readEnd();
					$dwResourcesReaded[] = $dwResource;
				} else {
					$dwResource->receive();
					if ( $dwResource->readEnd() ) {
						// end transmition, no more receive
						$dwResourcesReaded[] = $dwResource;
					}
				}
			} catch(\Exception $exception_read) {
				$dwResourcesExcept[] = $dwResource;
				$this->log->throwable( $exception_read );
				//throw $exception_read;
			} catch(\Error $error_read) {
				$dwResourcesExcept[] = $dwResource;
				$this->log->throwable( $error_read );
				//throw $error_read;
			}
		}
		return $dwResourcesReaded;
	}
	
	public function write( array &$dwResourcesWrite, array &$dwResourcesExcept ) : array {
		$dwResourcesWrited = array();
		$dwResource = null;
		//$this->log->trace(".");
		foreach( $dwResourcesWrite as $dwResource ) {
			try {
				$dwResource->send();
				// end transmition, no more send
				if ( $dwResource->writeEnd() )
					$dwResourcesWrited[] = $dwResource;
			} catch( \Exception $exception_write ) {
				$dwResourcesExcept[] = $dwResource;
				$this->log->throwable( $exception_write );
				//throw $exception_write;
			} catch( \Error $error_write ) {
				$dwResourcesExcept[] = $dwResource;
				$this->log->throwable( $error_write );
				//throw $error_write;
			}
		}
		return $dwResourcesWrited;
	}
	//messaging_select
	public function messaging_select( array &$dwResourcesRead, array &$dwResourcesWrite, array &$dwResourcesExcept ) : int {
		$nbStreamReady=0;
		$nbStreamRead=0;
		$nbStreamReaded=0;
		$nbStreamWrite=0;
		$nbStreamWrited=0;
		//$nbStreamListen=0;

		$streamsToRead = array();
		$nbStreamRead=count( $dwResourcesRead );
		foreach( $dwResourcesRead as $index => $dwResource) {
			if ( $dwResource->isResource() ) 
				$streamsToRead[] = $dwResource->getResource();
			else 
				unset($dwResourcesRead[$index]);
		}
		
		$streamsToWrite = array();
		$nbStreamWrite=count( $dwResourcesWrite );
		foreach($dwResourcesWrite as $index => $dwResource) {
			if ( $dwResource->isResource() ) 
				$streamsToWrite[] = $dwResource->getResource();
			else 
				unset($dwResourcesWrite[$index]);
		}
		$streamsExpected = array();
		
		if ( empty( $streamsToRead ) && empty( $streamsToWrite ) )
			$nbStreamReady=0;
		else
			$nbStreamReady = stream_select( $streamsToRead, $streamsToWrite, $streamsExpected, 0); // 0 : no wait, null : wait until the end, else seconde timeout
		
		if ( $nbStreamReady === false ) {
			$this->log->error("stream select error");
			$nbStreamReady=0;
			//$this->log->warn( DwObject::print_r( $dwResourceRead ,2 ) );
			//throw new \Exception("failed to streamselect ");
		} else {
			if ( ! empty($streamsExpected) ) {
				if ( is_null($dwResourcesExcept) ) $dwResourcesExcept=array();
				foreach($streamsExpected as $streamExpected) {
					$found=false;
					foreach($dwResourcesWrite as $index => $dwResourceWrite) {
						if ( $streamExpected == $dwResourceWrite->getResource() ) {
							$dwResourcesExcept[] = $dwResourceWrite;
							unset($dwResourcesWrite[$index]);
							$found=true;
							$this->log->error("excepted write source");
							$this->log->error( $dwResourceWrite );
							break;
						}
					}
					if ( ! $found ) {
						foreach($dwResourcesRead as $index => $dwResourceRead) {
							if ( $streamExpected == $dwResourceRead->getResource() ) {
								$dwResourcesExcept[] = $dwResourcesRead;
								unset($dwResourcesRead[$index]);
								$found =true;
								$this->log->error("excepted read source");
								$this->log->error( $dwResourceRead );
								break;
							}
						}
					}
					if ( ! $found )
						$this->log->warn("[select:".$this->getClassName()."] excepted ressource not found");
				}
			}
			///*
			foreach($dwResourcesRead as $idxResource => $dwResource) {
				if ( empty( $streamsToRead ) ) 
					unset( $dwResourcesRead[$idxResource] );
				else {
					$found = false;
					$streamToRead = $dwResource->getResource();
					foreach($streamsToRead as $idxStream => $stream) {
						if ($stream == $streamToRead) {
							$found = true;
							unset($streamsToRead[$idxStream]);
							break;
						}
					}
					if ( $found )
						$nbStreamReaded++;
					else
						unset( $dwResourcesRead[$idxResource] );
				}
			}
			
			foreach($dwResourcesWrite as $idxResource => $dwResource) {
				if ( empty( $streamsToWrite ) )
					unset( $dwResourcesWrite[$idxResource] );
				else {
					$found = false;
					$streamToWrite = $dwResource->getResource();
					foreach($streamsToWrite as $idxStream => $stream) {
						if ($stream == $streamToWrite) {
							$found = true;
							unset($streamsToWrite[$idxStream]);
							break;
						}
					}
					if ( $found )
						$nbStreamWrited++;
					else
						unset( $dwResourcesWrite[$idxResource] );
				}
			}
			//*/
			
			
			$this->lastState ="ready:$nbStreamReady ";
			$this->lastState.="r:$nbStreamReaded/$nbStreamRead ";
			$this->lastState.="w:$nbStreamWrited/$nbStreamWrite ";
			//$this->lastState.="l:$nbStreamListen ";
			$this->log->trace( $this->lastState );
		}
		
		return $nbStreamReady;
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

?>

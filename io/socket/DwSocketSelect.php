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

namespace bakuba\kernel\io\socket;
//use bakuba\kernel\io\socket\DwSocketSelect as DwSocketSelect;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

use bakuba\kernel\network\DwPort as DwPort;

use bakuba\kernel\io\iDwResources as iDwResources;
class DwSocketSelect extends \bakuba\kernel\io\DwResourceSelect {
	/*
	public function __construct( \darkphp\DwObject $parent ) {
		parent::__construct( $parent );
	}
	//*/
	public function setSocketLimiteDeltaTimer( float $fSeconde ) {
		$this->setLimiteDeltaTimer( $fSeconde );
	}
	public function socketWrite( DwSocket $dwSocket ) : void {
		$this->writeLunch( $dwSocket );
	}
	public function socketRead( DwSocket $dwSocket ) : void {
		$this->readLunch( $dwSocket );
	}
	
	public function read( array &$dwResourcesRead ,array &$dwResourcesExcept ) : array {
		$dwResourcesReaded = array();
		$dwResource = null;
		//$this->log->trace( ".");
		foreach($dwResourcesRead as $dwResource ) {
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
			} catch( \Exception $exception_read) {
				$dwResourcesExcept[] = $dwResource;
				$this->log->throwable($exception_read);
				//throw $exception_read;
			} catch( \Error $error_read) {
				$dwResourcesExcept[] = $dwResource;
				$this->log->throwable($error_read);
				//throw $error_read;
			}
		}
		return $dwResourcesReaded;
	}
	
	
	//messaging_select
	public function messaging_select( array &$dwResourcesRead, array &$dwResourcesWrite, array &$dwResourcesExcept ) : int {
		$nbSocketReady=0;
		$nbSocketRead=0;
		$nbSocketReaded=0;
		$nbSocketWrite=0;
		$nbSocketWrited=0;
		$onePresent=false;
		
		$socksToRead=array();
		$nbSocketRead=\count( $dwResourcesRead );
		foreach($dwResourcesRead as $idxRsrc => $dwResource) {
			if ( $dwResource->isResourceDefined() )
				$socksToRead[] = $dwResource->getResource() and $onePresent=true;
			else 
				unset($dwResourcesRead[$idxRsrc]);
		}
		
		$socksToWrite=array();
		$nbSocketWrite=\count( $dwResourcesWrite );
		foreach($dwResourcesWrite as $idxRsrc => $dwResource) {
			if ( $dwResource->isResourceDefined() ) 
				$socksToWrite[] = $dwResource->getResource() and $onePresent=true;
			else 
				unset($dwResourcesWrite[$idxRsrc]);
		}
		$socksExcpeted=array();
		
		try {
			if ( $onePresent )
				$nbSocketReady = \socket_select( $socksToRead, $socksToWrite, $socksExcpeted, 0); // 0 : no wait, null : wait until the end, else seconde timeout
		} catch( \Exception $socket_exception) {
			$this->log->throwable($socket_exception);
			//throw $socket_exception;
		} catch( \Error $socket_error) {
			$this->log->throwable($socket_error);
			//throw $socket_error;
		}
		
		if ( $nbSocketReady === false ) {
			$sockErrorNo=socket_last_error();
			$this->log->error( new \Exception("failed to socket_select[$sockErrorNo]: ".socket_strerror($sockErrorNo)) );
		} elseif ( $onePresent ) {
			if ( ! empty($socksExcpeted) ) {
				foreach($socksExcpeted as $sockExpected) {
					$found=false;
					foreach($dwResourcesWrite as $idxRsrc => $dwResourceWrite) {
						if ( $sockExpected  == $dwResourceWrite->getResource() ) {
							$this->log->warn("[select:".$this->getClassName()."] excepted write source");
							$this->log->warn( $dwResourceWrite );
							unset( $dwResourcesWrite[$idxRsrc] );
							$found =true;
							break;
						}
					}
					if ( ! $found ) {
						foreach($dwResourcesRead as $idxRsrc => $dwResourceRead) {
							if ( $sockExpected == $dwResourceRead->getResource() ) {
								$this->log->warn("[select:".$this->getClassName()."] excepted read source");
								$this->log->warn( $dwResourceRead );
								unset( $dwResourcesRead[$idxRsrc] );
								$found =true;
								break;
							}
						}
					}
				}
			}
			
			foreach($dwResourcesRead as $idxResource => $dwResource) {
				if ( empty( $socksToRead ) ) 
					unset( $dwResourcesRead[$idxResource] );
				else {
					$found = false;
					$sockToRead = $dwResource->getResource();
					foreach($socksToRead as $idxSock => $sock) {
						if ($sock == $sockToRead) {
							$found = true;
							unset($socksToRead[$idxSock]);
							break;
						}
					}
					if ( $found )
						$nbSocketReaded++;
					else
						unset( $dwResourcesRead[$idxResource] );
				}
			}
				
			foreach($dwResourcesWrite as $idxResource => $dwResource) {
				if ( empty( $socksToWrite ) )
					unset( $dwResourcesWrite[$idxResource] );
				else {
					$found = false;
					$sockToWrite = $dwResource->getResource();
					foreach($socksToWrite as $idxSock => $sock) {
						if ($sock == $sockToWrite) {
							$found = true;
							unset($socksToWrite[$idxSock]);
							break;
						}
					}
					if ( $found )
						$nbSocketWrited++;
					else
						unset( $dwResourcesWrite[$idxResource] );
				}
			}
			
			$this->lastState ="ready:$nbSocketReady ";
			$this->lastState.="r:$nbSocketReaded/$nbSocketRead ";
			$this->lastState.="w:$nbSocketWrited/$nbSocketWrite ";
			
			//$this->log->trace( $this->lastState );
		}
		
		return $nbSocketReady;
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

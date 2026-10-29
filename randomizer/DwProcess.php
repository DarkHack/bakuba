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

namespace bakuba\kernel\randomizer;
//use bakuba\kernel\randomizer\DwProcess as DwProcess;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\application\aDwApplication as aDwApplication;
use bakuba\kernel\application\aDwApplicationConnexion as aDwApplicationConnexion;

//use bakuba\kernel\application\session\aDwApplicationSession as aDwApplication;
//use bakuba\kernel\application\session\aDwApplicationConnexion as aDwApplicationConnexion;

class DwProcess extends \bakuba\kernel\process\DwProcessIpc {
	static private $bufferSize = 100000;
	protected $ableToFork = false;
	
	private $cbCheckAndGenerateBuffer = null;
	private $cbLocalAgentEnded = null;
	private $dwTimer = null;
	private $buffer = "";
	///*
	public function start(): bool {
		
		$this->cbLocalAgentEnded = new DwCallBack( array( &$this, "localAgentEnded" ) );
		$this->cbCheckAndGenerateBuffer = new DwCallBack( array( &$this, "checkAndGenerateBuffer" ) );
		$this->addTick( $this->cbCheckAndGenerateBuffer );
		$this->log->trace(".");
		return true;
	}
	public function checkAndGenerateBuffer() : void {
		$this->log->trace( "." );
		$this->dwTimer = null;
		$bufSize = \strlen( $this->buffer );
		//$this->log->trace("checkAndGenerateBuffer:$bufSize");
		if ( $bufSize < self::$bufferSize ) {
			$bufSizeToGenerate = self::$bufferSize - $bufSize;
			if ( $bufSizeToGenerate > 100 ) $bufSizeToGenerate=100;
			$this->getDwLocalAgent()->random( $bufSizeToGenerate ,$this->cbLocalAgentEnded );
		}
		$ratioMem = \floatval( $bufSize / self::$bufferSize * 100 );
		$this->log->trace( \sprintf( "%.2f", $ratioMem )." %");
	}
	public function localAgentEnded( string $processResult ) : void {
		//$this->log->trace( $processResult );
		$this->buffer.=$processResult;
		if ( is_null( $this->dwTimer ) )
			$this->dwTimer = $this->addTimer( 3, $this->cbCheckAndGenerateBuffer );
	}
	
	public function connectApplication(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\iDwApplicationConnexion {
		return new class( $this ,$dwProcessConnexion ,$dwApplicationRequest ) extends aDwApplicationConnexion {
			public function connect() : \bakuba\kernel\application\DwApplicationResponse {
				/*
				$dwSessionChannel = $this->getDwSessionChannel();
				if ( ! isset ( $dwSessionChannel->cpt  ) )
					$dwSessionChannel->cpt = 1;
				else
					$dwSessionChannel->cpt++;
				$this->log->debug( serialize( $dwSessionChannel ) );
				//*/
				return $this->getDwApplication()->request( $this->getDwApplicationRequest() );
			}
		};
	}
	//*/
	public function request( 
		\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\DwApplicationResponse {
		$sizeAskedObj = $dwApplicationRequest->getObject();
		$this->log->info( $sizeAskedObj );
		$sizeAsked = $sizeAskedObj->getValue();
		$this->log->debug( $sizeAsked );
		if ( $sizeAsked < \strlen( $this->buffer ) ) {
			$newBuf = \substr( $this->buffer ,$sizeAsked + 1 );
			$rndToSend = \substr( $this->buffer ,0 ,$sizeAsked );
			$this->buffer = $newBuf;
		} else {
			$rndToSend =$this->buffer;
			$this->buffer="";
		}
		$dwApplicationResponse = new \bakuba\kernel\application\DwApplicationResponse( $rndToSend );
		if ( ! isset( $this->dwTimer ) || \is_null( $this->dwTimer ) )
			$this->dwTimer = $this->addTimer( 3, $this->cbCheckAndGenerateBuffer );
		
		return $dwApplicationResponse;
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

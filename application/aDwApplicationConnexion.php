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

namespace bakuba\kernel\application;
//use bakuba\kernel\application\aDwApplicationConnexion as aDwApplicationConnexion;

//use bakuba\kernel\objects\DwCallBack as DwCallBack;

abstract class aDwApplicationConnexion  extends \darkphp\DwObject implements iDwApplicationConnexion {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,"ipckey-".$this->getSrcIpcKey()->getKey()
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	
	private $dwApplicationRequest = null;
	public function getDwApplicationRequest() : DwApplicationRequest {
		return $this->dwApplicationRequest;
	}
	public function getDwApplication() : aDwApplication {
		return $this->parent;
	}
	private $dwProcessConnexion = null;
	
	public function __construct(
		aDwApplication $aDwApplication
		,\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) {
		$this->dwApplicationRequest = $dwApplicationRequest;
		$this->dwProcessConnexion = $dwProcessConnexion;
		parent::__construct( $aDwApplication );
		try {
			$this->appResponse( $this->connect() );
		} catch( \Exception $exception ) {
			$this->throwError( $exception );
			//throw $exception;
		} catch( \Error $error ) {
			$this->throwError( $error );
			//throw $error;
		}
		$this->log->trace(".");
	}
	//public function connect(  ) : DwApplicationResponse;
	protected function ipcResponse( $anResponse ) : void {
		$this->dwProcessConnexion->ipcResponse( $anResponse );
	}
	protected function appResponse( DwApplicationResponse $dwApplicationResponse ) : void {
		$this->dwProcessConnexion->ipcResponse( $dwApplicationResponse );
	}
	protected function getSrcIpcKey() : \bakuba\kernel\io\ipc\DwIpcKey {
		return $this->dwProcessConnexion->getSrcIpcKey();
	}
	protected function throwError( $errorHandle ) : void {
		//$this->log->throwable( $errorHandle );
		$this->dwProcessConnexion->throwError( $errorHandle );
	}
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

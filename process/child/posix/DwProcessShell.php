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

namespace bakuba\kernel\process;
//use bakuba\kernel\process\DwProcessShell as DwProcessShell;

use \Exception as Exception;
use \Error as Error;
use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

use bakuba\kernel\process\DwProcess as DwProcess;
use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;

use bakuba\kernel\io\stream\DwStream as DwStream;
use bakuba\kernel\shell\DwShellProcess as DwShellProcess;

class DwProcessShell extends DwProcessManager {
	/*
	public function __construct( DwProcessIpc $parentProcess) {
		parent::__construct( $parentProcess );
	}
	//*/
	private $dwShellProcess = null;
	private $dwShellProcessRandomizer = null;
	private $dwShellProcessCrypto = null;
	protected function init(): void {
		parent::init( );
		if ( $this->isForkEnable() && $this->isFork() ) {
			$this->log->trace("is Forked , null prepare");
			$this->dwShellProcess = null;
			$this->dwShellProcessRandomizer = null;
			$this->dwShellProcessCrypto = null;
		} else {
			if ( $this->getParentProcess() != $this ) {
				$this->log->trace("no Forked , parent prepare:".$this->getParentProcess()->getClassName() );
				$this->dwShellProcess
					= $this->getParentProcess()->dwShellProcess;
				$this->dwShellProcessRandomizer
					= $this->getParentProcess()->dwShellProcessRandomizer;
				$this->dwShellProcessCrypto
					= $this->getParentProcess()->dwShellProcessCrypto;
			}
		}
	}
	
	public function shellStart( string $scriptToExecute, DwCallBack $dwCallBack ) {
		$this->log->trace("shellStart");
		if ( is_null( $this->dwShellProcess ) ) {
			$this->dwShellProcess = new \bakuba\kernel\shell\DwShellPerm( $this );
			$this->dwShellProcess->setDebug( $this->log );
		}
		$this->dwShellProcess->start(
			$scriptToExecute
			,$dwCallBack
		);
	}
	public function shellRandomizerStart(
		string $scriptToExecute
		,DwCallBack $dwCallBack
	) : void {
		$this->log->trace(".");
		if ( is_null( $this->dwShellProcessRandomizer ) ) {
			$this->dwShellProcessRandomizer
				= new \bakuba\kernel\shell\DwShellRandomizer( $this );
			$this->dwShellProcessRandomizer->setDebug( $this->log );
		}
		
		$this->dwShellProcessRandomizer->start(
			$scriptToExecute
			,$dwCallBack
		);
	}
	
	public function shellCryptoStart(
		string $scriptToExecute
		,DwCallBack $dwCallBack
	) : void {
		$this->log->trace(".");
		
		if ( is_null( $this->dwShellProcessCrypto ) ) {
			$this->dwShellProcessCrypto
				= new \bakuba\kernel\shell\DwShellCrypt( $this );
			$this->dwShellProcessCrypto->setDebug( $this->log );
		}
		
		$this->dwShellProcessCrypto->start(
			$scriptToExecute
			,$dwCallBack
		);
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

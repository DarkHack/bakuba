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
//use bakuba\kernel\process\DwProcessShellManager as DwProcessShellManager;

use \Exception as Exception;
use \Error as Error;
use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

use bakuba\kernel\process\DwProcess as DwProcess;
use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;

use bakuba\kernel\io\stream\DwStream as DwStream;

class DwProcessShellManager extends DwProcessShell {
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
	}
	//*/
	/*
	private static $sLog = null;
	private $debugEnable = false;
	public function enableDebug( $withParent = false ) : void {
		$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		$this->debugEnable = true;
		if ( $withParent > 0 )
		 $withParent--;
		self::$sLog->trace( "[{$debugKey}] withParent:$withParent" );
		if ( $withParent || $withParent > 0 )
			parent::enableDebug( $withParent );
	}
	public function __construct( DwProcessIpc $parentProcess) {
		$debugKey=$this->getClassName();
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::__construct( $parentProcess );
			$debugKey=$this->getClassName()."/".$this->getInstanceKey();
			//self::$sLog->trace("[{$debugKey}]");
			
		} catch(\Exception $exception) {
			self::$sLog->error("[{$debugKey}] exception");
			throw $exception;
		} catch(\Error $error) {
			self::$sLog->error("[{$debugKey}] error");
			throw $error;
		}
	}
	private $dwShellProcess = null;
	private $dwShellProcessRandomizer = null;
	private $dwShellProcessCrypto = null;
	protected function init(): void {
		$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		try {
			parent::init( );
			$this->dwShellProcess = null;
			$this->dwShellProcessRandomizer = null;
			$this->dwShellProcessCrypto = null;
			
		} catch(\Exception $exception) {
			self::$sLog->error("[{$$debugKey}] exception");
			throw $exception;
		} catch(\Error $error) {
			self::$sLog->error("[{$$debugKey}] error");
			throw $error;
		}
	}
	
	public function shellStart( string $scriptToExecute, DwCallBack $dwCallBack ) {
		$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		try {
			if ( is_null( $this->dwShellProcess ) ) {
				$this->dwShellProcess = new \bakuba\kernel\shell\DwShellPerm( $this );
				
				if ( $this->debugEnable )
					$this->dwShellProcess->enableDebug();
			}
			if ( $this->debugEnable )
				self::$sLog->trace("[{$debugKey}]");
			
			$this->dwShellProcess->start(
				$scriptToExecute
				,$dwCallBack
			);
		} catch(Exception $exception) {
			self::$sLog->error("[{$debugKey}] exception");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("[{$debugKey}] error");
			throw $error;
		}
	}
	public function shellRandomizerStart(
		string $scriptToExecute
		,DwCallBack $dwCallBack
	) : void {
		$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		try {
			if ( is_null( $this->dwShellProcessRandomizer ) ) {
				$this->dwShellProcessRandomizer
					= new \bakuba\kernel\shell\DwShellRandomizer( $this );
				
				if ( $this->debugEnable )
					$this->dwShellProcessRandomizer->enableDebug();
			}
			if ( $this->debugEnable )
				self::$sLog->trace("[{$debugKey}]");
			
			$this->dwShellProcessRandomizer->start(
				$scriptToExecute
				,$dwCallBack
			);
		} catch(Exception $exception) {
			self::$sLog->error("[{$debugKey}] exception");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("[{$debugKey}] error");
			throw $error;
		}
	}
	public function shellCryptoStart(
		string $scriptToExecute
		,DwCallBack $dwCallBack
	) : void {
		$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		try {
			if ( is_null( $this->dwShellProcessCrypto ) ) {
				$this->dwShellProcessCrypto
					= new \bakuba\kernel\shell\DwShellCrypt( $this );
				
				if ( $this->debugEnable )
					$this->dwShellProcessCrypto->enableDebug();
			}
			if ( $this->debugEnable )
				self::$sLog->trace("[{$debugKey}]");
			
			$this->dwShellProcessCrypto->start(
				$scriptToExecute
				,$dwCallBack
			);
		} catch(Exception $exception) {
			self::$sLog->error("[{$debugKey}] exception");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("[{$debugKey}] error");
			throw $error;
		}
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

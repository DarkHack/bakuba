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
//use bakuba\kernel\randomizer\DwRandomizer as DwRandomizer;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use bakuba\kernel\objects\DwObject as DwObject;

class DwRandomizer extends DwObject {
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
	
	private $dwCallBack = null;
	private $dwTimerCallBack = null;
	private $myProcess = null;
	private $dwApplicationRandomizer = null;
	public function __construct( \bakuba\kernel\process\DwProcessIpc $myProcess ) {
		global $dwBakuba;
		parent::__construct( $myProcess );
			
		$this->myProcess = $myProcess;
		$this->dwApplicationRandomizer = $dwBakuba->getDwRandomizerProcess();
			
		$this->dwCallBack = new DwCallBack(
			array( &$this ,"checkAndGenerateBuffer" )
		);
		$this->myProcess->addTick( $this->dwCallBack );
		//$this->addPeriodicTimer( 0.1 ,$dwCallBack );
		self::setRandomizer( $this );
		//self::$sLog->trace( "[{$debugKey}] ".$this->myProcess->getClassName() );
	}
	private $buffer = "";
	static private $bufferSize = 1000;
	//\bakuba\kernel\randomizer\DwRandomizer::random( $size );
	public function random( int $size = 1000 ) : string {
		if ( $size <= 0 )
			return "";
		
		$buffer = "";
		if ( $size < strlen( $this->buffer ) ) {
			$newBuf = substr( $this->buffer ,$size + 1 );
			$buffer = substr( $this->buffer ,0 ,$size );
		} else {
			$newBuf="";
			$buffer = $this->buffer;
		}
		$this->buffer=$newBuf;
		
		if ( ! $this->asking && is_null( $this->dwTimerCallBack ) ) {
			$this->log->trace( "relunch ask" );
			//$this->myProcess->cancelTimer( $this->dwTimerCallBack );
			
			$this->myProcess->addTick( $this->dwCallBack );
			$this->log->trace( $size."/".strlen($this->buffer) );
		}
		
		return $buffer;
	}
	
	private $asking = false;
	public function processResponse(
		\bakuba\kernel\application\DwApplicationResponse $dwRandomizerResponse
	) : void {
		//$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		$buffer=$dwRandomizerResponse->getRawData();
		//$this->log->trace( strlen( $buffer ) );
		if ( strlen($buffer) == 0 ) {
			if ( is_null($this->dwTimerCallBack) )
				$this->dwTimerCallBack = $this->myProcess->addTimer(
					180
					,new DwCallBack( array( &$this ,'differedCheckAndGenerateBuffer' ) ) 
				);
		} else {
			$this->buffer.=$buffer;
			if ( is_null($this->dwTimerCallBack) )
				$this->differedCheckAndGenerateBuffer();
		}
	}
	public function differedCheckAndGenerateBuffer() : void {
		//$this->log->info(".");
		$this->dwTimerCallBack = null;
		$this->asking = false;
		$this->myProcess->addTick( $this->dwCallBack );
	}
	public function checkAndGenerateBuffer() : void {
		//$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		if ( ! $this->asking ) {
			$bufSize = strlen( $this->buffer );
			//$this->log->trace("[{$debugKey}] checkAndGenerateBuffer:$bufSize");
			if ( $bufSize < self::$bufferSize ) {
				$ratioMem = floatval( $bufSize / self::$bufferSize * 100 );
				//$this->log->trace( sprintf("%.2f", $ratioMem)." %");
				$bufSizeToGenerate = self::$bufferSize - $bufSize;
				//$this->log->info( "ask :".$bufSizeToGenerate );
				$this->asking = true;
				$this->myProcess->ipcSend(
					new DwCallBack( array(&$this, "processResponse" ) )
					,$this->dwApplicationRandomizer
					,new \bakuba\kernel\application\DwApplicationRequest(
						\bakuba\kernel\serializable\aDwSerializable::IntSerialize( $bufSizeToGenerate )
					)
				);
			} else
				$this->log->info("no need ask, full buffer");
		}// else
		//	$this->log->info("ever in asking");
	}
	public function toString( $withParent = false ) : string {
		if ( $withParent )
			$objStr=parent::toString( $withParent );
		else
			$objStr="{$this->getClassName()}";
		//$objStr.= parent::toString();
		return $objStr;
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
	
	static private $dwRandomizer = null;
	
	private static $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	private static function phpRandomize( int $size ) : string {
		$charactersLength = strlen( self::$characters ) - 1;
		$random="";
		do {
			$toGenerate = $size - \strlen( $random );
			/*
			if ( $toGenerate > 5 )
				$toGenerate = 5;
			$random .= \substr( \strrev( \uniqid() ) ,0 ,$toGenerate );
			//*/
			for ( $i = 0; $i < $toGenerate; $i++ )
				$random .= self::$characters[ random_int( 0, $charactersLength ) ];
		} while( $toGenerate > 0 );
		
		return $random;
	}
	private static function setRandomizer( DwRandomizer $dwRandomizer ) : void {
		self::$dwRandomizer = $dwRandomizer;
	}
	
	// bakuba\kernel\randomizer\DwRandomizer::getRandomizer
	public static function getRandomizer() : ?DwRandomizer {
		return self::$dwRandomizer;
	}
	// 
	//\bakuba\kernel\randomizer\DwRandomizer::randomize( $size );
	public static function randomize( int $size = 3 ) : string {
		if ( $size <= 0 )
			return "";
		
		$random="";
		$dwRandomizer = self::getRandomizer();
		if ( \is_object( $dwRandomizer ) )
			$random .= $dwRandomizer->random( $size );
		
		$random .= self::phpRandomize( $size - strlen( $random ) );
		
		return $random;
	}
	
	// \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( $array );
	public static function progressiveUUID( array $array ) : string {
		$progressiveUUID=null;
		while( is_null( $progressiveUUID ) || isset( $array[ $progressiveUUID ] ) )
			$progressiveUUID .= self::randomize( 1 );
		return $progressiveUUID;
	}
	// \bakuba\kernel\randomizer\DwRandomizer::progressiveInArrayUUID( $array );
	public static function progressiveInArrayUUID( array $array ) : string {
		$progressiveUUID=null;
		while( is_null( $progressiveUUID ) || \in_array( $progressiveUUID ,$array ) )
			$progressiveUUID .= self::randomize( 1 );
		return $progressiveUUID;
	}
	
}
?>

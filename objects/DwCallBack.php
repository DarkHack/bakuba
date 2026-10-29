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

namespace bakuba\kernel\objects;
//use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\time\DwChronoStartStop as DwChronoStartStop;

class DwCallBack
	extends \darkphp\DwObject  {
	
	public static $dwCbEmpty = null;
	// DwCallBack::emptyCB();
	public static function emptyCB() : DwCallBack {
		if ( \is_null( self::$dwCbEmpty ) )
			self::$dwCbEmpty = new DwCallBack( function () {} );
		return self::$dwCbEmpty;
	}
	private $handle = null;
	private $params = array();
	
	// objCallBack( $object, $method, $param );
	// objCallBack( $handle, $param );
	// objCallBack( $handle );
	function __construct( $p1 ,$p2 = null ) {
		parent::__construct(
			\is_a( $p1,\darkphp\DwObject::getStaticClassName() ) ? $p1	: (
				\is_array( $p1 ) && isset( $p1[0] ) && \is_a( $p1[0],\darkphp\DwObject::getStaticClassName() ) ?
				$p1[0]
				: $this 
				)
		);
		if ( is_callable( $p1 ) ) {
			$this->handle = $p1;
			if ( ! is_null( $p2 ) )
				$this->params[]=$p2;
		} else
			$this->handle=array( $p1, $p2 );
		
		$this->dwCallBackRefInstance();
		
		if ( \is_null( $this->handle ) || ! \is_callable( $this->handle ) )
			throw new \Exception("no handle callable [{$this->toString()}]");
		
		$numargs = \func_num_args();
		if ( $numargs > 2 ) {
			$arg_list = \func_get_args();
			for ($i = 2; $i < $numargs; $i++)
				$this->params[]=$arg_list[$i];
		}
		//if ( \is_object( $this->log ) ) $this->log->debug(".");
	}
	private function dwCallBackRefInstance() {
		self::registrerInstance( $this );
	}
	
	//*/
	public function addParam( $param ) {
		$this->params[] = $param;
	}
	public function setParam( int $index, $param ) {
		return $this->params[$index];
	}
	public function getParam( int $index = 0 ) {
		return $this->params[$index];
	}
	public function getClone() : self {
		//if ( \is_object( $this->log ) ) $this->log->debug(".");
		$cloneCB = new DwCallBack( $this->handle );
		foreach( $this->params as $param )
		 $cloneCB->addParam( $param );
		
		$numargs = func_num_args();
		if ( $numargs > 0 ) {
			$arg_list = func_get_args();
			for ($i = 0; $i < $numargs; $i++)
				$cloneCB->addParam( $arg_list[$i] );
		}
		return $cloneCB;
	}
	private $dwChronoStartStop = null;
	private $statsEnable = false;
	public function enableStats( DwChronoStartStop $dwChronoStartStop ) {
		$this->dwChronoStartStop=$dwChronoStartStop;
		$this->statsEnable = true;
	}
	public function disableStats() {
		$this->statsEnable = false;
		unset( $this->dwChronoStartStop );
	}
	function callback( ) {
		if ( $this->statsEnable )
			$this->dwChronoStartStop->start();
		$params = array();
		foreach($this->params as $param )
			$params[] = $param;
		
		$numargs = func_num_args();
		if ( $numargs > 0 ) {
			$arg_list = func_get_args();
			for ($i = 0; $i < $numargs; $i++)
				$params[]=$arg_list[$i];
		}
		$return = \call_user_func_array( $this->handle, $params );
		//$return = ( false === \call_user_func_array( $this->handle, $params ) );
		
		if ( $this->statsEnable )
			$this->dwChronoStartStop->stop();
		return $return;
	}
	
	function printHandle( $withParam = false ) : string {
		$strHandle = "|";
		if ( ! isset( $this->handle ) )
			$strHandle.= "NOT SET";
		elseif ( \is_null($this->handle) )
			$strHandle.= "NULL";
		elseif ( \is_array($this->handle) ) {
			$strHandle.= DwString::toStringEx( $this->handle[0] );				
			$strHandle.= "->".DwString::toStringEx( $this->handle[1] );
		} elseif ( \is_a( $this->handle ,'Closure' ) ) {
			$strHandle.= "Closure";
		} elseif ( \is_object( $this->handle ) )
			$strHandle.= $this->handle;
		else
			$strHandle.= gettype( $this->handle );
		if ( $withParam ) {
			$strParam="(";
			if ( is_array( $this->params ) )
				foreach( $this->params as $param )
					$strParam.=DwString::toStringEx( $param )." ,";
			$strParam.=")";
			$strHandle .= $strParam;
		}
		$strHandle.="|";
		return $strHandle;
	}
	private $staticHandleReferenced = null;
	private function getHandleReference() :string {
		if ( is_null( $this->staticHandleReferenced ) )
			$this->staticHandleReferenced = $this->printHandle();
		return$this->staticHandleReferenced;
	}
	
	public function toString( $withParent = false ) : string {
		$objStr = parent::toString( $withParent );
		$objStr.="/hdl:{$this->printHandle()}";
		return $objStr;
	}
	
	public function __destruct() {
		//$this->log->trace( "." );
		self::unregistrerInstance( $this );
		//if ( \is_object( $this->log ) ) $this->log->debug(".");
		if ( isset( $this->handle ) ) unset( $this->handle );
		if ( isset( $this->params ) ) {
			foreach( $this->params as $key => $param )
				if ( isset( $this->params[ $key ] ) )
					unset ( $this->params[ $key ] );
			unset( $this->params );
		}
		if ( isset( $this->dwChronoStartStop ) )
			unset( $this->dwChronoStartStop );
		
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__
			: __CLASS__
			;
	}
	
	static private $instanceCreated = array();
	private static function registrerInstance( self $dwCallBack ) {
		$strHandleWithoutParams = $dwCallBack->getHandleReference();
		if ( ! isset(self::$instanceCreated[ $strHandleWithoutParams ]) )
			self::$instanceCreated[ $strHandleWithoutParams ]=0;
		self::$instanceCreated[ $strHandleWithoutParams ]++;
	}
	private static function unregistrerInstance( self $dwCallBack ) {
		$iDwLogger = null;
		if ( isset( $dwCallBack->log ) && \is_object( $dwCallBack->log ) )
			$iDwLogger = $dwCallBack->log;
		else
			$iDwLogger = \bakuba\kernel\agartha\DwInstall::getLogger();
		$strHandleWithoutParams = $dwCallBack->getHandleReference();
		
		if ( ! isset( self::$instanceCreated[ $strHandleWithoutParams ] ) )
			$iDwLogger->error( \PHP_EOL." no refrenced instance handle for :{$dwCallBack->toString()} !!!\r\n" );
		else {
			self::$instanceCreated[ $strHandleWithoutParams ]--;
			if ( self::$instanceCreated[ $strHandleWithoutParams ] === 0 )
				unset(self::$instanceCreated[ $strHandleWithoutParams]);
		}
	}
	// DwCallBack::listInstancesCreated
	public static function listInstancesCreated() : array {
		return self::$instanceCreated;
	}
	
	public static function test(
		\darkphp\logger\iDwLogger $iDwLogger
		,\darkphp\serializable\iDwNoSerializable $dwToTest = null
	) : \darkphp\serializable\iDwNoSerializable {
		try {
			if ( \is_null( $dwToTest ) ) {
				$dwToTest = new self( function(  ) { } );
				$dwToTest->setDebug( $iDwLogger );
			}
			$nbLoop = 1000 * 100; //100k
			//$nbLoop = 100; //100
			$dwChronoStartStop = new \bakuba\kernel\time\DwChronoStartStop( $dwToTest );
			$dwToTest->enableStats( $dwChronoStartStop );
			$cpt=0;
			while ( $cpt++ < $nbLoop ) 
				$dwToTest->callback();
			
			$iDwLogger->info( $dwToTest->printHandle(false) );
			$iDwLogger->info( $dwChronoStartStop->printStats() );
			$dwToTest->disableStats();
		} catch( \Exception $exception ) {
			$iDwLogger->error( "\r\n\t{$exception}" );
		} catch( \Error $error ) {
			$iDwLogger->error( "\r\n\t{$error}" );
		}
		return $dwToTest;
	}
}

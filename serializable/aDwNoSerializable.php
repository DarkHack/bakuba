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

namespace darkphp\serializable;
//use darkphp\serializable\aDwNoSerializable as aDwNoSerializable;

abstract class aDwNoSerializable implements iDwNoSerializable {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$this->log = $iDwLogger->getLogger( $this );
		//$this->log->trace( "setDebug:".$this->getClassName() );
	}
	protected $log = null;
	protected $parent = null;
	private $isRootObject = false;
    public function __construct( self $parent = null ) {
		//parent::__construct
		//parent::__construct();
		if ( ! \is_null( $parent ) ) {
			$this->isRootObject=( \is_object ( $parent ) && $parent === $this );
			if ( ! $this->isRootObject ) {
				$this->parent=&$parent;
				if ( \is_null( $this->log ) && !\is_null( $this->parent->log ) ) $this->setDebug( $this->parent->log );//->getLogger( $this );
			} else
				$this->parent = null;
		}
		//if ( \is_null( $this->log ) ) {
		//		echo "\r\nNull Logger Creation:".\get_class( $this )." \r\n";
			/*
			$dwLoggerFirst=\darkphp\logger\DwLoggerLevel::getFirst();
			if ( \is_null( $dwLoggerFirst ) ) {
				//$this->log = \bakuba\kernel\logger\standalone\DwNullLogger::getFirstLogger( $this );
				//echo "\r\nNull Logger Creation:".\get_class( $this )." ".\get_class( $this->log )."\r\n";
				echo "\r\nNull Logger Creation:".\get_class( $this )." \r\n";
			} else
				$this->log = $dwLoggerFirst->getLogger( $this );
			if ( \is_object( $this->log ) ) $this->log->info( "\r\nNo log for:{$this->toString()}\r\n" );
			//*/
		//}
		if ( $this->isRootObject )
			if ( \is_object( $this->log ) ) $this->log->info( "root:".$this->getClassName() );
		//else
		//	$this->log->trace( "." );
	}
	public function getParentClassName( bool $fqcn=null ) : string {
		$className="/NoParent";
		if ( isset( $this->parent ) && \is_object( $this->parent ) )
			$className=$this->parent->getClassName( $fqcn );
		return $className;
	}
	protected function parentToString() : ?string {
		//if ( $this->parent === $this )
		//	$strParent="/ParentIsThis";
		if ( isset( $this->isRootObject ) && $this->isRootObject )
			$strParent="/Root";
		elseif ( ! isset( $this->parent ) || \is_null( $this->parent ) )
			$strParent="/NoParent";
		elseif ( \is_object( $this->parent ) ) {
			if ( \method_exists( $this->parent ,'toString' ) )
				$strParent="/parent({$this->parent->toString()})";
			elseif ( \method_exists( $this->parent ,'__toString' ) )
				$strParent="/parent({$this->parent->__toString()})";
			elseif ( \method_exists( $this->parent ,'getClassName' ) )
				$strParent="/parent({$this->parent->getClassName()})";
			else
				$strParent="/ParentNoToString object:".\get_class( $this->parent );
		}
		else
			$strParent="/ParentNoToString type:".\gettype( $this->parent );
		return $strParent;
	}
	public function __serialize() : array { throw new \Exception("No Serialize!"); }
	public function serialize() : string { throw new \Exception("No Serialize!"); }
	public function __unserialize( array $serialized ) : void { throw new \Exception("No Serialize!"); }
	public function unserialize( $serialized ) : void { throw new \Exception("No Serialize!"); }
	
	public function toString( $withParent = false ) : string {
		return $this->getClassName().( $withParent ? $this->parentToString() : "" );
	}
	//private static $lastGcCollect = null;
	public function __destruct( ) {
		/*
		if ( isset( $this->log ) && \is_object( $this->log ) ) {
			if ( ! \is_null( self::$lastGcCollect ) && self::$lastGcCollect > 0 )
				$this->log->trace( "nb garbage collector cyles:".self::$lastGcCollect );
			self::$lastGcCollect=null;
		}
		//*/
		//if ( \is_object( $this->parent ) )
		//	unset( $this->parent );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) {
				//echo "destruct aDwNoSerializable '$key' $nbCycle\r\n";
				unset( $this->$key );
			}
		// else
		//	echo "\r\n\t".__CLASS__."[ {$this->toString()} ] destruct without log object\r\n";
		//parent::__destruct();
		//if ( \is_null( self::$lastGcCollect ) ) 
		//	self::$lastGcCollect = \gc_collect_cycles();
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	protected const CLASS_SEPARATOR = \darkphp\autoload\DwAutoLoadBoot::CLASS_SEPARATOR;
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? "//".self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	public static function test(
		\darkphp\logger\iDwLogger $iDwLogger
		,iDwNoSerializable $dwToTest = null
	) : iDwNoSerializable {
		if ( \is_null( $dwToTest ) ) {
			$dwToTest = new self();
			$dwToTest->setDebug( $iDwLogger );
		}
		$iDwLogger->trace( "\r\n\tClassName:{$dwToTest->getClassName()}/string:{$dwToTest->toString()}" );
		$iDwLogger->trace( "\r\n\tserialize:".\serialize( $dwToTest ) );
		return $dwToTest;
	}
	//*/
}
?>

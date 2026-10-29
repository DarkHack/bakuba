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


class emptyTestClass implements \darkphp\serializable\iDwNoSerializable {
	protected $log = null;
	function __construct( \darkphp\logger\iDwLogger $iDwLogger  ) {
		$this->setDebug( $iDwLogger );
		$this->log->trace( "." );
	}
	function __destruct( ) {
		$this->log->trace( "." );
	}
	
	public function setDebug(
		\darkphp\logger\iDwLogger &$iDwLogger
	) : void {
		$this->log = $iDwLogger->getLogger( $this );
		//$this->log = $iDwLogger;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? "//".__CLASS__ : __CLASS__;
	}
}
class anotherEmptyTestClass extends emptyTestClass {
	function __construct( \darkphp\logger\iDwLogger $iDwLogger  ) {
		parent::__construct( $iDwLogger );
		$this->log->trace( "." );
	}
	function __destruct( ) {
		$this->log->trace( "." );
		parent::__destruct();
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return __CLASS__;
	}
	
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
class DwNoSerializable implements \darkphp\serializable\iDwNoSerializable {
	public function setDebug(
		\darkphp\logger\iDwLogger &$iDwLogger
	) : void {
		$this->log = $iDwLogger->getLogger( $this );
		//$this->log->trace( "." );
	}
	protected $log = null;
	//protected $parent = null;
    public function __construct( self $parent = null ) {
		$this->initParentToString();
		//parent::__construct();
		$this->log->trace( "." );
		/*
		if ( \is_null( $parent ) ) {
			if ( \is_null( $this->log ) ) {
				echo "\r\nNo log for:{$this}\r\n";
				$this->log = \darkphp\logger\DwNullLogger::getFirstLogger( $this );
			}
		} else {
			$this->parent=&$parent;
			$this->setDebug( $this->parent->log );//->getLogger( $this );
		}
		*/
	}
	public function __destruct( ) {
		$this->log->trace( "." );
		if ( \is_object( $this->parent ) )
			unset( $this->parent );
		if ( \is_object( $this->log ) ) {
			$this->log->trace( "." );
			unset( $this->log );
		} else
			echo "\r\n\t".__CLASS__."[ {$this} ] destruct without log object\r\n";
		//parent::__destruct();
	}
	private $strParent = "/noParent";
	private function initParentToString() : void {
		if ( \is_object( $this->parent ) ) {
			if ( \method_exists( $this->parent ,'toString' ) )
				$this->strParent="/parent({$this->parent->toString()})";
			elseif ( \method_exists( $this->parent ,'__toString' ) )
				$this->strParent="/parent({$this->parent})";
		} else
			$this->strParent="/ParentNoToString";
	}
    protected function parentToString() : ?string {
		return $this->strParent;
	}/*
	public function toString( $withParent = false ) : string {
		return $this->getClassName().$this->parentToString();
	}
	/*
    public function __serialize() : array { throw new \Exception("No Serialize!"); }
	public function serialize() : string { throw new \Exception("No Serialize!"); }
	public function __unserialize( array $serialized ) : void { throw new \Exception("No Serialize!"); }
	public function unserialize( $serialized ) : void { throw new \Exception("No Serialize!"); }
	//*/
	
	public function getClassName( bool $fqcn=null ) : string {
		return __CLASS__;//self::getStaticClassName( $fqcn );
	}
	/*
	protected const CLASS_SEPARATOR = \darkphp\autoload\DwAutoLoadBoot::CLASS_SEPARATOR;
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? "//".self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	public static function test(
		\darkphp\logger\iDwLogger $iDwLogger
		,iDwSerializable $dwToTest = null
	) : iDwSerializable {
		try {
			if ( \is_null( $dwToTest ) ) {
				$dwToTest = new self();
				$dwToTest->setDebug( $iDwLogger );
			}
			$iDwLogger->trace( "\r\n\tClassName:{$dwToTest->getClassName()}/string:{$dwToTest}" );
			$iDwLogger->trace( "\r\n\tserialize:".\serialize( $dwToTest ) );
		} catch( \Exception $exception ) {
			$iDwLogger->error( "\r\n\t{$exception}" );
		} catch( \Error $error ) {
			$iDwLogger->error( "\r\n\t{$error}" );
		}
		return $dwToTest;
	}
	//*/
}
class anTestClass extends \darkphp\serializable\aDwNoSerializable {
	function __construct( \darkphp\logger\iDwLogger $iDwLogger  ) {
		$this->log = $iDwLogger->getLogger( $this );
		parent::__construct( );
		$this->log->trace(".");
	}
	function unsetLog() {
		unset( $this->log );
	}
	function __destruct( ) {
		if (\is_object( $this->log ) ) $this->log->trace(".");
		parent::__destruct();
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
$dwLoggerEcho = new \bakuba\kernel\logger\standalone\DwLoggerEcho( new class {} );
new emptyTestClass( $dwLoggerEcho );
new anotherEmptyTestClass( $dwLoggerEcho );
new anTestClass( $dwLoggerEcho );
//$test->unsetLog();
//unset( $test );
/*
$anTestClass = new anTestClass( $dwLoggerEcho );
$dwLoggerEcho->trace("un");
unset( $anTestClass );
$dwLoggerEcho->trace("deux");
//*/
$dwLoggerEcho->trace("trois");
unset( $dwLoggerEcho );
echo "quatre\r\n";
?>

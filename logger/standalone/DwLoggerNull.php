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

namespace bakuba\kernel\logger\standalone;
//use bakuba\kernel\logger\standalone\DwNullLogger as DwNullLogger;

use darkphp\logger\DwLoggerLevel as DwLoggerLevel;
class DwLoggerNull extends  DwLoggerLevel {
	public static function getFirstLogger(
		$forThisObject
		,string $levelLog = "unitlog"
		,string $unitLog = null
	) : self {
		$first = self::getFirst();
		if ( \is_null( $first ) ) {
			self::setFirst( new self( $forThisObject ,$levelLog ,$unitLog ) );
			$instance = self::getFirst();
		} elseif ( \is_a( $first ,self::getStaticClassName() ) )
			$instance = $first->upTo( $forThisObject ,$levelLog ,$unitLog );
		else 
			throw new \Exception("first logger not:'".self::getStaticClassName()."'");
		return $instance;
	}
	
	public static function switchFirstLogger(
		$forThisObject
		,string $levelLog = "unitlog"
		,string $unitLog = null
	) : self {
		$first = self::getFirst();
		if ( \is_null( $first ) ) {
			self::setFirst( new self( $forThisObject ,$levelLog ,$unitLog ) );
			$instance = self::getFirst();
		} elseif ( \is_a( $first ,self::getStaticClassName() ) )
			$instance = $first->upTo( $forThisObject ,$levelLog ,$unitLog );
		else {
			self::setFirst( new self( $forThisObject ,$levelLog ,$unitLog ) );
			$instance = self::getFirst();
		}
		return $instance;
	}
	///*
	private static $aDwAppender = null;
	protected function initLogger( ) : \darkphp\logger\appenders\aDwAppender {
		if ( \is_null( self::$aDwAppender ) ) {
			self::$aDwAppender = new class extends \darkphp\logger\appenders\aDwAppender {
				public function append( \darkphp\logger\DwLoggingEvent $event) {
					echo $this->layout->format( $event );
					if ( isset( $this->logAppenders ) && \is_array( $this->logAppenders ) )
						foreach( $this->logAppenders as $aDwAppender )
							$aDwAppender->append( $event );
				}
			};
			self::$aDwAppender->setLayout( self::getLayoutPattern() );
		}
		return self::$aDwAppender;
	}
	//*/
	/*
	protected function initLogger( ) : \darkphp\logger\appenders\aDwAppender {
		if ( \is_null( self::$loggerAppender ) ) {
			$loggerAppender = new \darkphp\logger\appenders\DwAppenderEcho();
			$loggerAppender->setLayout( self::getLayoutPattern() );
			self::$loggerAppender=$loggerAppender;
		}
		return self::$loggerAppender;
	}
	//*/
	
	public function log( string $level, $message, $throwable = null) {
		///*
		$time=\microtime(true);
		if ( \is_object ( $this->forThisObject ) )
			if ( method_exists( $this->forThisObject ,'toString' ) )
				$loggerFqcn = $this->forThisObject->toString();
			elseif ( method_exists( $this->forThisObject ,'__toString' ) )
				$loggerFqcn = $this->forThisObject;
			elseif ( method_exists( $this->forThisObject ,'getClassName' ) )
				$loggerFqcn = $this->forThisObject->getClassName();
			else
				$loggerFqcn = \get_class( $this->forThisObject );
		else
			$loggerFqcn = $this->forThisObject;
		$loggerLoggingEvent = new \darkphp\logger\DwLoggingEvent(
			$this->loggerCategoryName
			,$loggerFqcn."\r\n\t NullAppender [{$this->getFullLevel()}] "
			,$level
			,$message
			,$time
			,$throwable
		);
		//$yep = $loggerLoggingEvent->getLevel();
		$this->logAppender->append( $loggerLoggingEvent );
		
		if ( \is_array( $this->logAppenders ) )
			foreach( $this->logAppenders as $aDwAppender )
				$aDwAppender->append( $dwLoggingEvent );
		//*/
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

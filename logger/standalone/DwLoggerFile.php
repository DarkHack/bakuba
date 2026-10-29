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
//use bakuba\kernel\logger\standalone\DwLoggerFile as DwLoggerFile;

use darkphp\logger\appenders\aDwAppender as aDwAppender;
use darkphp\logger\appenders\DwAppenderFile as DwAppenderFile;
//use darkphp\logger\layouts\DwLayoutSimple as DwLayoutSimple;

use darkphp\logger\DwLoggerLevel as DwLoggerLevel;

class DwLoggerFile extends DwLoggerLevel {
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
	//private $logAppender = null;
	protected function initLogger( ) : aDwAppender {
		$dwAppenderFile = new DwAppenderFile( "file" );
		$dwAppenderFile->setLayout( self::getLayoutPattern() );
		$dwAppenderFile->setFile( $this->getFullPath() );
		//echo "\r\n ============ Appender File:".$this->getFullPath()."\r\n";
		return $dwAppenderFile;
	}
	
	public function getFullPath() : string {
		return \bakuba\kernel\agartha\DwInstall::getLocalDirectory( $this->getLevelLog() )
			."/{$this->getUnitLog()}.log";
	}
	/*
	public function upTo( $forThisObject, string $newLevel ,string $newUnit = null ) : \darkphp\logger\iDwLogger {
		if ( \is_null( $newUnit ) ) {
			$newUnit = $newLevel;
			$newLevel = null;
		}
		return new self(
			$forThisObject
			,$this->getLevelLog().( \is_null( $newLevel ) ? "" : "/".$newLevel )
			,$newUnit
			,$this
		);
	}
	/*
	public function upToLevel( $forThisObject, string $levelToUp ,string $newUnit = null ) : \darkphp\logger\iDwLogger {
		return new self(
			$forThisObject
			,$this->getLevelLog()."/".$levelToUp
			,\is_null($newUnit) ? $this->getUnitLog() : $newUnit
			,$this
		);
	}
	public function upToUnit( $forThisObject, string $unitToUp ) : \darkphp\logger\iDwLogger {
		return new self(
			$forThisObject
			,$this->getLevelLog()
			,$unitToUp
			,$this
		);
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

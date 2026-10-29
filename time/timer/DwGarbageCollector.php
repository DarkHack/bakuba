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

namespace bakuba\kernel\time\timer;
//use bakuba\kernel\time\timer\DwGarbageCollector as DwGarbageCollector;

// Perplexity production :
final class DwGarbageCollector extends \darkphp\DwObject {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
//				,\darkphp\source\DwSource::classNameToFirstSectionName( $this->getClassName() )
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	
    private static $lastCount = 0;
    private static $lastTime = 0.0;
	
	/**
	 * Déclenche explicitement le garbage collector PHP et journalise l'effet.
	 * Peut être appelé par un timer Bakuba, ou à chaque tick/supervision.
	 */
	public function collect() : int {
		$before = gc_collect_cycles();
		// Second appel pour éviter les leftovers, bugs connus PHP 7.4/xdebug
		$after = gc_collect_cycles();
		$cycles = $before + $after;
		self::$lastCount += $cycles;
		self::$lastTime = microtime(true);
		
		//if ($cycles > 0)
			$this->log->trace( 'Cycles collectés: '.$cycles.' (total: '.self::$lastCount.')' );
		
		return $cycles;
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
	
    /**
     * Expose la dernière collecte pour monitoring/stat.
     */
    public static function getLastCount(): int { return self::$lastCount; }
    public static function getLastTime(): float { return self::$lastTime; }
}
?>
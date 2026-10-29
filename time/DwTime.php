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

namespace bakuba\kernel\time;
//use bakuba\kernel\time\DwTime as DwTime;

/*
 * time():int
 *  retourne l'heure courante, mesurée en secondes depuis le début de l'époque UNIX, (1er janvier 1970 00:00:00 GMT).
 * -----
 * microtime(bool $as_float = false): string|float
 *  microtime() retourne le timestamp Unix, avec les microsecondes.
 * -----
 */

class DwTime {
	
	//private static $useHighResolution = null;
    /** @internal */
    const MICROSECONDS_PER_SECOND = 1000000;
    
    // DwTime::fSecondeTofMicroSeconde( float $fSeconde ) : float 
	public static function fSecondeTofMicroSeconde( float $fSeconde ) : float {
		$fMicroSeconde = $fSeconde * self::MICROSECONDS_PER_SECOND;
		$fMicroSeconde = $fMicroSeconde > \PHP_FLOAT_MAX
			? \PHP_FLOAT_MAX
			: floatval( $fMicroSeconde )
		;
		return $fMicroSeconde;
	}
	public static function fSecondeToiMicroSeconde( float $fSeconde ) : int {
		$iMicroSeconde = intval( $fSeconde * self::MICROSECONDS_PER_SECOND ,10 );
		$iMicroSeconde =  $iMicroSeconde > \PHP_INT_MAX
			? \PHP_INT_MAX
			: (int) $iMicroSeconde
		;
		return $iMicroSeconde;
	}
	public static function fMicroSecondeTofSeconde( float $fMicroSeconde ) : float {
		return floatval( $fMicroSeconde / self::MICROSECONDS_PER_SECOND );
	}
	public static function fMicroSecondeToiMicroSeconde( float $fMicroSeconde ) : int {
		$iMicroSeconde = intval( $fMicroSeconde ,10 );
		$iMicroSeconde =  $iMicroSeconde > \PHP_INT_MAX
			? \PHP_INT_MAX
			: (int) $iMicroSeconde
		;
		return $iMicroSeconde;
	}
	public static function fMicroSecondeToiSeconde( float $fMicroSeconde ) : int {
		$iSeconde = intval( $fMicroSeconde / self::MICROSECONDS_PER_SECOND ,10 );
		return (int) $iSeconde;
	}
	public static function iMicroSecondeTofSeconde( int $iMicroSeconde ) : float {
		$fSeconde = floatval( $iMicroSeconde / self::MICROSECONDS_PER_SECOND );
		$fSeconde = $fSeconde > \PHP_FLOAT_MAX
			? \PHP_FLOAT_MAX
			: floatval( $fSeconde )
		;
		return $fSeconde;
	}
	public static function iMicroSecondeToiSeconde( int $iMicroSeconde ) : int {
		$iSeconde = intval( $iMicroSeconde / self::MICROSECONDS_PER_SECOND ,10 );
		return (int) $iSeconde;
	}
	private $time = null;
	public function __construct( int $timeStampMicroSeconde = null ) {
		//if ( is_null( self::$useHighResolution ) )
		//	self::$useHighResolution = \function_exists('hrtime');
		if ( is_null( $timeStampMicroSeconde ) )
			$this->updateTime();
		else
			$this->time = $timeStampMicroSeconde;
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
		//parent::__destruct();
	}
	//*/
	public function reset() {
		$this->updateTime();
	}
	// return timestamp unix as float with microseconde
    public function updateTime() : int {
		//$this->time = self::$useHighResolution ? \hrtime(true) * 1e-9 : \microtime( true );
		///*
		$this->time 
			=  \bakuba\kernel\time\DwTime::fSecondeToiMicroSeconde(
				\microtime( true )
			);
		//*/
		/*
		$nanoSeconde = 
		$this->time 
			=  \bakuba\kernel\time\DwTime::fSecondeToiMicroSeconde(
				\hrtime(true) * 1e-9
			);
		//*/
		return $this->time;
    }
	// return timestamp unix as int microseconde
	public function getTime() : int {
		return $this->time;
	}
	/*
    public function __add(DwTime $argument2) {
        return new self( $this->time + $argument2->time );
    }
	
    public function __sub(DwTime $argument2) {
        return new self( $this->time - $argument2->time );
    }*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return ( $fqcn ? "//" :"" ).__CLASS__;
	}
}

?>

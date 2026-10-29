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
//use bakuba\kernel\time\timer\DwTimer as DwTimer;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
use \bakuba\kernel\time\DwTime as DwTime;
/**
 * The actual connection implementation for IDwTimerInterface
 *
 * This class should only be used internally, see IDwTimerInterface instead.
 *
 * @see IDwTimerInterface
 * @internal
 */
final class DwTimer
	extends \darkphp\DwObject
	implements iDwTimer {
	const MIN_INTERVAL = 0.000001;// 10^-6s => 1 ns
	
	private $iMicroSecondeInterval = null;
    private $periodic = null;

    /**
     * Constructor initializes the fields of the Timer
     *
     * @param float         $fSecondeInterval The interval after which this timer will execute, in seconds
     * @param DwCallBack    $callback The callback that will be executed when this timer elapses
     * @param bool          $periodic Whether the time is periodic
     */
    public function __construct(
    	float $fSecondeInterval
    	,DwCallBack $dwCallBack
    	,bool $periodic = false
    ) {
		parent::__construct( $dwCallBack );
			
		if ( $fSecondeInterval < self::MIN_INTERVAL)
			$fSecondeInterval = self::MIN_INTERVAL;
		
		$this->iMicroSecondeInterval 
			= DwTime::fSecondeToiMicroSeconde( $fSecondeInterval );
		$this->periodic = $periodic;
    }
    public function getInterval() : int {
        return $this->iMicroSecondeInterval;
    }
    public function setInterval(int $iMicroSecondeInterval ) : void {
		$this->iMicroSecondeInterval = $iMicroSecondeInterval;
	}
    public function printHandle() : string {
    	return $this->getDwCallBack()->printHandle();
    }
    public function callback( ) : bool {
		return $this->getDwCallBack()->callback();
    }
    public function getDwCallBack( ) : DwCallBack {
		return $this->parent;
    }

    public function isPeriodic() : bool {
        return $this->periodic;
    }
	public function serialize() : string {
		throw new \Exception( "[{$this->toString()}] is not serializable." );
    }
    public function __serialize() : array {
		throw new \Exception( "[{$this->toString()}] is not serializable." );
	}
    public function unserialize( $serialized ) : void {
		throw new \Exception( "[{$this->toString()}] is not unserializable." );
    }
    public function __unserialize( array $data ) : void {
		throw new \Exception( "[{$this->toString()}] is not unserializable." );
    }
	public function toString( $withParent = false ) : string {
		$subStr=parent::toString( $withParent );
		if ( \is_object( $this->parent ) )
			$subStr.="/{$this->printHandle()}";
		return $subStr;
	}
	public function __destruct() {
		//$this->log->trace( "." );
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
}

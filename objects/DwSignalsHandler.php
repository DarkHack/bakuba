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
//use bakuba\kernel\objects\DwSignalsHandler as DwSignalsHandler;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

/**
 * @internal
 */
final class DwSignalsHandler extends DwObject {
	static private $sLog = null;
	
	private $signals = array();
	private $pcntl = null;
	private $pcntlPoll = null;
	
	public function __construct() {
		$debugKey="{$this->getClassName()}";
		try {
			if (is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			parent::__construct( );
			$debugKey.="/{$this->getInstanceKey()}";
			
			$this->pcntl = \function_exists('pcntl_signal') && \function_exists('pcntl_signal_dispatch');
			$this->pcntlPoll = $this->pcntl && !\function_exists('pcntl_async_signals');
			
			// prefer async signals if available (PHP 7.1+) or fall back to dispatching on each tick
			if ($this->pcntl && !$this->pcntlPoll) {
				\pcntl_async_signals(true);
			}
		} catch(Exception $exception) {
			self::$sLog->error("[{$debugKey}] exception");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("[{$debugKey}] error");
			throw $error;
		}
	}

	public function __destruct( ) {
		try {	
			foreach($this->oldSignal as $signo => $handler)
				\pcntl_signal($signo, $handler);
			parent::__destruct();
			self::$sLog->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}	
	private $oldSignal = array();
	public function add( int $signo, DwCallBack $dwCallBack ) : bool {
		$return = false;
		try {
			if ($this->pcntl === false)
				throw new \BadMethodCallException('Event loop feature "signals" isn\'t supported by the "DwSignalsHandler"');
			
			if (!isset($this->signals[$signo])) {
				$this->signals[$signo] = array();
				$this->oldSignal[$signo] = \pcntl_signal_get_handler($signo);
				\pcntl_signal($signo, array($this, 'call'));
			}
			
			if (!\in_array($dwCallBack, $this->signals[$signo])) {
				$this->signals[$signo][] = $dwCallBack;
				$return=true;
			}
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
		return $return;
    }

    public function remove( int $signo, DwCallBack $dwCallBack) : bool
    {
		$return = false;
		try {
			if ( isset($this->signals[$signo])) {
				$index = \array_search($dwCallBack, $this->signals[$signo], true);
				unset($this->signals[$signo][$index]);
				$return=true;
				
				if (isset($this->signals[$signo]) && \count($this->signals[$signo]) === 0) {
					unset($this->signals[$signo]);
					\pcntl_signal($signo, \SIG_DFL);
				}
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
		return $return;
    }
    public function clear( DwCallBack $dwCallBack) : bool {
		$return = false;
		foreach( $this->signals as $signo => $signaux )
			$return |= $this->remove( $signo ,$dwCallBack );
		
		return $return;
    }

    public function call( int $signo, $siginfo )
    {
		try {
			if (!isset($this->signals[$signo])) {
				return;
			}
			foreach($this->signals[$signo] as $dwCallBack) {
				try {
					$dwCallBack->callback( $signo, $siginfo );
				} catch(Exception $exception_cb) {
					self::$sLog->error("exception_cb [".$this->getClassName()."/".$this->getInstanceKey()."]");
					self::$sLog->error($exception_cb);
					//throw $exception_cb;
				} catch(Error $error_cb) {
					self::$sLog->error("error_cb [".$this->getClassName()."/".$this->getInstanceKey()."]");
					self::$sLog->error($error_cb);
					//throw $error_cb;
				}
			}
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
    }
	
	public function count( int $signo ) : int {
		$return=0;
		try {
			if (!isset($this->signals[$signo])) 
				$return=-1;
			else
				$return=\count($this->signals[$signo]);
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
		return $return;
	}
	
	public function isEmpty() : bool {
		$return=true;
		try {
			$return=empty($this->signals);
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."/".$this->getInstanceKey()."]");
			throw $error;
		}
		return $return;
	}
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

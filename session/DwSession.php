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

namespace bakuba\kernel\session;
//use bakuba\kernel\session\DwSession as DwSession;

use \Exception as Exception;
use \Error as Error;

abstract class DwSession extends \darkphp\DwObject {
	private static $sLog = null;
	
	public function __construct( ) {
		$debugKey="{$this->getClassName()}";
		try {
			if ( is_null( self::$sLog ) )
				self::$sLog = \darkphp\logger\DwLogger::getLogger( __CLASS__ );
			parent::__construct();
			$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
			
			self::$sLog->info("[{$debugKey}]");
		} catch(\Exception $exception) {
			self::$sLog->error("[{$debugKey}] exception");
			throw $exception;
		} catch(\Error $error) {
			self::$sLog->error("[{$debugKey}] error");
			throw $error;
		}
	}
	private $ipChannels = array();
	protected $ipKeys = array();
	
	protected function newChannelKey() : bool {
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		$newChannelKey = false;
		try {
			$ipKey = $dwSessionChannel->getIpKey();
			
			if (!isset( $this->ipKeys[$ipKey] ) ) $this->ipKeys[$ipKey] = array();
			
			$channelKey = $dwSessionChannel->getChannelKey( );
			if ( is_null($channelKey) ) {
				$size = 3;
				while ( ( ! \is_null($channelKey) || isset( $this->ipChannels[ $channelKey ] ) ) && ( $size++ || true ) )
					$channelKey = \bakuba\kernel\randomizer\DwRandomizer::randomize( $size );
				
				$dwSessionChannel->setChannelKey($channelKey);
				$channelKey = $dwSessionChannel->getChannelKey();
			}
			$this->ipChannels[ $channelKey ] = $dwSessionChannel;
			
			if ( array_search($channelKey, $this->ipKeys[$ipKey]) === false )
				$this->ipKeys[$ipKey][] = $channelKey;
			
			//$dwSessionChannel = $this->readSession( $channelKey );	
			$isEdited = true;
		} catch(\Exception $exception) {
			self::$sLog->error("[{$debugKey}] exception");
			throw $exception;
		} catch(\Error $error) {
			self::$sLog->error("[{$debugKey}]  error");
			throw $error;
		}
		return $isEdited;
	}
	
	//public function createSession( DwSessionIpKey $dwSessionIpKey ) : DwSessionChannel 
	
	
	public function editSession( DwSessionChannel $dwSessionChannel ) : bool {
		$isEdited = false;
		try {
			$ipKey = $dwSessionChannel->getIpKey();
			
			if (!isset( $this->ipKeys[$ipKey] ) ) $this->ipKeys[$ipKey] = array();
			
			$channelKey = $dwSessionChannel->getChannelKey( );
			if ( is_null($channelKey) ) {
				$size=3;
				while ( ( $channelKey == null || isset( $this->ipChannels[ $channelKey ] ) ) && ( $size++ || true ) )
					$channelKey = \bakuba\kernel\randomizer\DwRandomizer::randomize( $size );
				$dwSessionChannel->setChannelKey($channelKey);
				$channelKey = $dwSessionChannel->getChannelKey();
			}
			$this->ipChannels[ $channelKey ] = $dwSessionChannel;
			
			if ( array_search($channelKey, $this->ipKeys[$ipKey]) === false )
				$this->ipKeys[$ipKey][] = $channelKey;
			
			//$dwSessionChannel = $this->readSession( $channelKey );	
			$isEdited = true;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $isEdited;
	}
	public function readSession( string $channelKey ) : ?DwSessionChannel {
		$dwSessionChannel = null;
		try {
			if ( isset( $this->ipChannels[ $channelKey ] ) )
				$dwSessionChannel = $this->ipChannels[ $channelKey ];
			else
				throw new Exception("no SessionChannel with key:$channelKey");
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $dwSessionChannel;
	}
	public function deleteSession( DwSessionChannel $dwSessionChannel ) : bool {
		$isDeleted = false;
		try {
			$channelKey = $dwSessionChannel->getChannelKey();
			$ipKey = $dwSessionChannel->getIpKey();
			if ( ! isset( $this->ipChannels[ $channelKey ] ) )
				$isDeleted &= false;
			else {
				unset($this->ipChannels[ $channelKey ]);
				$isDeleted = true;
			}
			$idToDelete = null;
			foreach( $this->ipKeys[$ipKey] as $id => $anChannelKey ) {
				if ( $channelKey == $anChannelKey ) {
					$idToDelete = $id;
					break;
				}
			}
			if ( ! isset($this->ipKeys[$ipKey][$idToDelete]) )
				$isDeleted &= false;
			else {
				$isDeleted &= true;
				unset($this->ipKeys[$ipKey][$idToDelete]);
				if (empty($this->ipKeys[$ipKey]))
					unset($this->ipKeys[$ipKey]);
			}
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $isDeleted;
	}
	public function listSession() : array {
		$sessions = array();
		try {
			$sessions = $this->ipChannels;
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $sessions;
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
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

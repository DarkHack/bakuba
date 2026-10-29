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

namespace bakuba\kernel\io\stream;
//use bakuba\kernel\io\stream\DwStream as DwStream;

use \Exception as Exception;
use \Error as Error;

use darkphp\DwObject as DwObject;
use bakuba\kernel\io\DwResource as DwResource;
use darkphp\logger\DwLogger as DwLogger;

class DwStream extends DwResource {
	protected $connected=false;
	protected static $PACKET_SIZE = 1024;
	
	private $withFlushFP = true;
	private $withCloseFP = true;
	/*
	public function __construct( $resource = null ) {
		parent::__construct( $resource );
	}
	//*/
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
		parent::__destruct();
	}
	//*/
	public function connect( $resource = null ) : bool {
		if ( ! is_null( $resource ) && is_resource( $resource ) ) {
			stream_set_blocking( $resource, 0);
			stream_set_read_buffer( $resource, 0);
			stream_set_write_buffer( $resource, 0);
		}
		parent::connect( $resource );
		return $this->isResource();
	}
	public function withoutClose() : void {
		$this->withCloseFP = false;
	}
	public function withoutFlush() : void {
		$this->withFlushFP = false;
	}
	
	public function withClose() : void {
		$this->withCloseFP = true;
	}
	public function withFlush() : void {
		$this->withFlushFP = true;
		
	}
	public function disconnect() : bool {
		$debugKey="{$this->getClassName()}/{$this->getInstanceKey()}";
		$return = false;
		try {
			if ( is_object( $this->Log ) )
				$this->Log->trace("[{$debugKey}]");
			
			if ( ! $this->isResource() )
				self::$sLog->warn("not connected.");
			else {
				$stream = $this->getResource();
				
				if ( $this->withFlushFP && ! fflush( $stream ) )
					throw new Exception("echec on fflush");
				
				if ( $this->withCloseFP ) 
					$return = fclose( $stream );
				else
					$return = true;
			}
			
			$return &= parent::disconnect();
			
		} catch(\Exception $exception) {
			$return = false;
			if ( is_object( $this->Log ) ) {
				$this->Log->error("[{$debugKey}] exception");
				$this->Log->error($exception);
			}
			//throw $exception;
		} catch(\Error $error) {
			$return = false;
			if ( is_object( $this->Log ) ) {
				$this->Log->error("[{$debugKey}] error");
				$this->Log->error($error);
			}
			//throw $error;
		}
		return $return;
	}
	public function receive( string $data = null ) : int {
		$stream = $this->getResource();
		
		if ( parent::receive( \fread( $stream, $this->packetRcvSize ) ) > 0 ) {
			$this->recvEnded = false;
			if ( \feof( $stream ) || $nbByte < $this->packetRcvSize )
				$this->readClose();
			
		} elseif ( $this->recvInactivity > 10 )
			$this->readClose();
		
	}
	public function send( ) : bool {
		$sended=false;
		
		$buf = substr( $this->sendBuf, $this->sendCpt, $this->packetSndSize );
		$len = strlen($buf);
		$nbSended = \fwrite( $this->getResource(), $buf, $len );
		
		//self::$sLog->trace("$nbSended/$len [".$this->getClassName()."/".$this->getInstanceKey()."]\r\n$buf");
		if ( $nbSended === FALSE ) {
			$this->writeClose();
			throw new \Exception( "echec on fwrite" );
		} else {
			$this->sended($nbSended);
			if ( $nbSended < $len )
				throw new \Exception( "echec on fwrite_send $nbSended < $len" );
			$sended=true;
		}
		
		return $sended;
	}
	/*
	public static $max_retries = 100;
	
    public static function getContent( $filePath )
    {
		$result = null;
		try {
			$retries = 0;
			$fp = @fopen($filePath, "r");
			if ($fp) {
				// keep trying to get a lock as long as possible
				do {
					if ($retries > 0)
						usleep(rand(1, 10000));
					
					$retries += 1;
				} while (!flock($fp, LOCK_SH | LOCK_NB ) and $retries <= self::$max_retries);
				// couldn't get the lock, give up
				if ( $retries < self::$max_retries ) {
					// got the lock, write the data
					$fileSize=filesize($filePath);
					if ($fileSize > 0 )
						$result = fread($fp, $fileSize);
					else
						$result = "";
					// release the lock
					flock($fp, LOCK_UN);
					fclose($fp);
					// success
				}
			}
		} catch( Exception $e ) {
			$result = null;
		}
		return $result;
	}
	// suggested mode 'a' for writing to the end of the file
    public static function putContent($filePath, $contents )
    {
		$result = false;
        $fp = fopen($filePath, "w");
        $retries = 0;
		
        if ($fp) {
			// keep trying to get a lock as long as possible
			do {
				if ($retries > 0) {
					usleep(rand(1, 10000));
				}
				$retries += 1;
			} while (!flock($fp, LOCK_EX | LOCK_NB ) and $retries <= self::$max_retries);
			
			// couldn't get the lock, give up
			if ( $retries < self::$max_retries ) {
				// got the lock, write the data
				fwrite($fp, $contents);
				// release the lock
				flock($fp, LOCK_UN);
				fclose($fp);
				// success
				$result = true;
			}
		}
		return $result;
    }
	public static function delete($filePath) {
		if (file_exists($filePath))
			unlink($filePath);
		return true;
	}
	*/
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
?>

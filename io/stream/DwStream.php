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

use bakuba\kernel\io\DwResourceBuffered as DwResourceBuffered;

class DwStream extends DwResourceBuffered {
	protected $packetRcvSize = 2048;
	protected $packetSndSize = 2048;
	
	private $withFlushFP = true;
	private $withCloseFP = true;
	/*
	public function __construct( \darkphp\DwObject $parent ,$resource = null ) {
		parent::__construct( $parent ,$resource  );
	}
	//*/
	public function connect( $resource = null ) : bool {
		if ( ! is_null( $resource ) && is_resource( $resource ) ) {
			\stream_set_blocking( $resource, FALSE );
			\stream_set_read_buffer( $resource ,$this->packetRcvSize );
			\stream_set_write_buffer( $resource ,$this->packetSndSize );
			\stream_set_timeout( $resource ,1 );
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
		$return = false;
		try {
			//$this->isFullTrace() and $this->log->trace(".");
			
			if ( ! $this->isResource() )
				$this->log->warn("not connected.");
			else {
				$stream = $this->getResource();
				
				if ( $this->withFlushFP && ! fflush( $stream ) )
					throw new \Exception("[{$this->toString()}] echec on fflush");
				
				if ( $this->withCloseFP ) 
					$return = fclose( $stream );
				else
					$return = true;
			}
			
			$return &= parent::disconnect();
			
		} catch( \Exception $exception ) {
			$return = false;
			$this->log->throwable( $exception );
			//throw $exception;
		} catch( \Error $error ) {
			$return = false;
			$this->log->throwable($error);
			//throw $error;
		}
		return $return;
	}
	public function receive( string $data = null ) : int {
		//$this->isFullTrace() and $this->log->trace( "." );
		$stream = $this->getResource();
		//\stream_set_blocking( $stream ,false );
		$readed = \fread( $stream, $this->packetRcvSize );
		$receiveCpt = parent::receive( $readed );
		if ( $receiveCpt > 0 ) {
			$this->recvEnded = false;
			$this->log->trace( $receiveCpt );
			$this->isFullTrace() and $this->log->trace( $readed );
			if ( \feof( $stream ) || $receiveCpt < $this->packetRcvSize )
				$this->readClose();
			
		} elseif ( $this->recvInactivity > 10 )
			$this->readClose();
		return $receiveCpt;
	}
	public function send( ) : bool {
		//$this->isFullTrace() and $this->log->trace( "." );
		$sended=false;
		
		$buf = \substr( $this->sendBuf, $this->sendCpt, $this->packetSndSize );
		$len = \strlen($buf);
		$nbSended = \fwrite( $this->getResource(), $buf, $len );
		
		//$this->isFullTrace() and $this->log->trace("$nbSended/$len \r\n$buf");
		if ( $nbSended === FALSE ) {
			$this->writeClose();
			throw new \Exception( "[".$this->toString()."] echec on fwrite.".\PHP_EOL."buf:'".$buf."'" );
		} else {
			$this->sended( $nbSended );
			$this->log->trace( "{$nbSended}/{$len}" );
			$this->isFullTrace() and $this->log->trace( $buf );
			if ( $nbSended < $len )
				$this->log->error( "echec on fwrite_send $nbSended < $len" );
			else
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
	///*
	public function __destruct() {
		//$this->isFullTrace() and $this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
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

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

namespace bakuba\kernel\io\stream\file;
//use bakuba\kernel\io\stream\file\DwFile as DwFile;
class DwFile extends \darkphp\DwObject {
	private $filePath = null;
	private $locking = false;
	public function __construct(
		string $filePath
		,bool $locking
	) {
		parent::__construct();
		$this->filePath = $filePath;
		$this->locking = $locking;
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
		parent::__destruct();
	}
	//*/
	
	
	public static $max_retries = 100;
	
    public static function getContent( $filePath )
    {
		$result = null;
		try {
			$retries = 0;
			if ( file_exists( $filePath ) ) {
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
			}
		} catch( Error | Exception $e ) {
			$result = null;
		}
		return $result;
	}
	// suggested mode 'a' for writing to the end of the file
    public static function putContent($filePath, $contents ) : bool
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
    
	/*
	public static function delete($filePath) {
		if (file_exists($filePath))
			unlink($filePath);
		return true;
	}
	//*/
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

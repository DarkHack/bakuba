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

namespace bakuba\kernel\agartha;
//use bakuba\kernel\agartha\DwInstall as DwInstall;

use darkphp\logger\iDwLogger as iDwLogger;

//\bakuba\kernel\agartha\DwInstall::isWindows()
class DwInstall {
	private static $sLog = null;
	//\bakuba\kernel\agartha\DwInstall::getLogger();
	public static function getLogger() : iDwLogger {
		return self::$sLog;
	}
	//\bakuba\kernel\agartha\DwInstall::setLogger( iDwLogger $iDwLogger )
	public static function setLogger( iDwLogger $iDwLogger ) : void {
		self::$sLog = $iDwLogger;
	}
	private function __construct( iDwLogger $iDwLogger ,array $argv = null ) {
		self::setLogger( $iDwLogger );
		self::getLocalDirectory( "/var" );
		self::getLocalDirectory( self::$bakubaData );
		try {
			$iDwLogger->info( "bakuba create" );
			$dwBakuba = \bakuba\kernel\agartha\DwBakuba::singleton( $iDwLogger ,$argv );
			
			
			$iDwLogger->trace( "bakuba run " );
			\bakuba\kernel\process\DwProcessManager::run( $dwBakuba );
			/*
				$iDwLogger->upTo(
					$this
					,"bakuba"
					,"DwBakuba"
				)
			);
			//*/
			//\bakuba\kernel\agartha\DwBakuba::singleton( self::$firstLogger );
			$iDwLogger->info("bakuba ended");
		} catch( \Exception $exception ) {
			$iDwLogger->error( $this );
			$iDwLogger->throwable( $exception );
			//throw $exceptionAppHttpRequest;
		} catch( \Error $error ) {
			$iDwLogger->error( $this );
			$iDwLogger->throwable( $error );
			//throw $errorAppHttpRequest;
		}
	}
	public function __destruct() {
		//$this->log->trace( "." );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
		//parent::__destruct();
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return __CLASS__;
	}
	// singleton class pattern:
	private static $instance = null;
	private static $winDrive = "c";
	private static $bakubaData = "./bakuba-data";
	//\bakuba\kernel\agartha\DwInstall::getBakubaDataDirectory()
	public static function getBakubaDataDirectory() : string {
		return self::getLocalDirectory( self::$bakubaData );
	}
	public static function singleton( iDwLogger $iDwLogger ,array $argv = null ) {
		null === self::$instance and self::$instance = new self( $iDwLogger ,$argv );
		return null;
	}
	private static $firstLogger = null;
	//\bakuba\kernel\agartha\DwInstall::isWindows()
	public static function isWindows() : bool {
		//return \DIRECTORY_SEPARATOR === '\\' ;// <= ! false true on linux
		return \strtoupper( \substr( \PHP_OS ,0 ,3) ) === 'WIN';
	}
	//\bakuba\kernel\agartha\DwInstall::getLocalDirectory()
	public static function getLocalDirectory( string $dir ) : string {
		if ( self::isWindows()
		&& ! \darkphp\string\DwString::startWith( $dir ,self::$winDrive.":" )
		)
			$localDir=self::$winDrive.":";
		else
			$localDir = "";
		
		$localDir.=$dir;
		if ( ! empty($localDir) && ! \is_dir( $localDir ) )
			if ( ! \mkdir( $localDir ) )
				if ( isset( self::$firstLogger ) && \is_object( self::$firstLogger ) )
					self::$firstLogger->error(" cannot create {$localDir}");
				else
					echo "\r\n[ ERROR ] cannot create {$localDir}\r\n";
		return $localDir;
	}
}
?>

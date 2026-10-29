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

namespace bakuba\kernel\test;
//use bakuba\kernel\test\test as test;

use \darkphp\DwObject as DwObject;
use \bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

abstract class test extends DwObject {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) ) {
			$topName = \darkphp\source\DwSource::classNameToTopName( $this->getClassName() );
			$sectionName = \darkphp\source\DwSource::classNameToSectionName( $this->getClassName() );
			$toRemove="bakuba\\";
			$classSection = \substr( $sectionName, \strlen( $toRemove ) ,\strlen( $sectionName ) - strlen( $toRemove )  );
			$sectionC = str_replace( "\\", "_", $classSection);
			$classSection = str_replace( "_test", "", $sectionC);
			$section = str_replace( "_", "/", $classSection);
			
			if ( empty( $section ) )
				$this->log = $iDwLogger->upTo( $this ,"_{$topName}" );
			else
				$this->log = $iDwLogger->upTo( $this ,$section ,$topName );
			
			$this->log->info( "{$sectionName} {$classSection} up {$section} {$topName}" );
		} else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	
	function __construct( DwObject $parent ) {
		parent::__construct( $parent );
		$this->log->trace( "." );
	}
	/*
	protected function getTestLogger(
		\darkphp\logger\iDwLogger $iDwLogger
		,string $anClassName
	) : \darkphp\logger\iDwLogger {
		$className = substr( $anClassName ,strrpos( $anClassName, '\\' ) + 1 );
		//echo "\r\n devrais:|{$className}|\r\n";
		
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$logger = $iDwLogger->upTo( $anClassName ,"{$className}.log" );
		else
			$logger = $iDwLogger->getLogger( $anClassName );
		
		$logger->trace( "." );
		return $logger;
	}
	//*/
	protected function tryTestFiles( array $filesToTest ) : array {
		$result = array();
		try {
			//$this->log->info( DwObject::listInstancesCreated() );
			foreach( $filesToTest as $fileToTest ) {
				$successFile = false;
				$this->log->info( "\r\n\t ['{$fileToTest}']");
				if ( ! DwAutoLoad::isFile( $fileToTest ) )
					$this->log->error(" echec no file for :'{$fileToTest}'" );
				else
					$successFile = $this->try( DwAutoLoad::getFile( "file://".$fileToTest ) );
				
				$result[ $fileToTest ] = $successFile;
				//$this->log->info( "\r\n\t result['{$fileToTest}']:".( $successFile ? "oki" : "echec" ) );
			}
		} catch( \Exception $exception ) {
			$this->log->throwable( $exception );
		} catch( \Error $error ) {
			$this->log->throwable( $error );
		}
		return $result;
	}
	protected function tryTestUnits( array $classesName ) : array {
		$resultTry=array();
		try {
			foreach( $classesName as $className ) {
				$resultTry[ $className ] = $this->tryTestUnit( $className );
			}
			//$this->log->trace( \debug_zval_dump( $toDestruct ) );
			//unset( $toDestruct );
			//$this->log->trace( \debug_zval_dump( 'toDestruct' ) );
		} catch( \Exception $exception ) {
			$this->log->throwable( $exception );
		} catch( \Error $error ) {
			$this->log->throwable( $error );
		}
		return $resultTry;
	}
	protected function tryClasses( array $classesName ) : array {
		$resultTry=array();
		try {
			foreach( $classesName as $className )
				$resultTry[ $className ] = $this->tryClass( $className );
		} catch( \Exception $exception ) {
			$this->log->throwable( $exception );
		} catch( \Error $error ) {
			$this->log->throwable( $error );
		}
		return $resultTry;
	}
	
	protected function try( string $phpCode ) : bool {
		$successTry=false;
		try {
			$this->log->info( eval( "?>".$phpCode ) );
			//$this->log->trace( \debug_zval_dump( $toDestruct ) );
			//unset( $toDestruct );
			//$this->log->trace( \debug_zval_dump( 'toDestruct' ) );
			$successTry=true;
		} catch( \Exception $exception ) {
			$this->log->throwable( $exception );
		} catch( \Error $error ) {
			$this->log->throwable( $error );
		}
		return $successTry;
	}
	protected function tryClass( string $className ) : bool {
		$successTry=false;
		try {
			//$this->log->info("\r\n\TEST :{$className}" );
			$testLogger = $this->log->getLogger( $className );
			$testLogger->info( DwObject::listInstancesCreated() );
			$testLogger->info( "\r\n\t lunch" );
			$className::test(
				$this->log
				,new $className( new class( $testLogger ) extends DwObject {
					public function __construct( $logger ) {
						$this->setDebug( $logger );
						parent::__construct( $this );
					}
				} )
			);
			//$this->log->trace( \debug_zval_dump( $toDestruct ) );
			//unset( $toDestruct );
			//$this->log->trace( \debug_zval_dump( 'toDestruct' ) );
			$successTry=true;
		} catch( \Exception $exception ) {
			$this->log->throwable( $exception );
		} catch( \Error $error ) {
			$this->log->throwable( $error );
		}
		return $successTry;
	}
	protected function tryTestUnit( $className ) : bool {
		$successTry=false;
		try {
			$this->log->info( DwObject::listInstancesCreated() );
			$this->log->info("\r\n\t TEST :{$className}" );
			new $className( $this );
			//$this->log->trace( \debug_zval_dump( $toDestruct ) );
			//unset( $toDestruct );
			//$this->log->trace( \debug_zval_dump( 'toDestruct' ) );
			$successTry=true;
		} catch( \Exception $exception ) {
			$this->log->throwable( $exception );
		} catch( \Error $error ) {
			$this->log->throwable( $error );
		}
		return $successTry;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

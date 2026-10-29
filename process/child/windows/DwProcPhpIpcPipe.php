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

namespace bakuba\kernel\process\child\windows;
//use bakuba\kernel\process\child\windows\DwProcPhpIpcPipe as DwProcPhpIpcPipe;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\process\DwProcess as DwProcess;
use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;

use bakuba\kernel\io\stream\DwStream as DwStream;
use \bakuba\kernel\io\socket\DwSocket as DwSocket;

class DwProcPhpIpcPipe extends DwProcPhpFifo {
	
	static private $kernelPhpDir="file://bakuba/kernel";
	//
	private static function getScript( string $scriptPath ) : ?string {
		return \bakuba\kernel\autoload\DwAutoLoad::getFile( self::$kernelPhpDir."/{$scriptPath}" );
	}
	private $bootloaderInit = false;
	private $relunchRead = false;
	public function __construct( \bakuba\kernel\process\DwResources $dwResources ) {
		//parent::__construct( $dwResources ,"php" ,false );
		parent::__construct( $dwResources ,"php -d extension=openssl -d allow_url_include=1" );
		//parent::__construct( $dwResources ,"php -d extension=openssl -d allow_url_include=1" ,false );
		//parent::__construct( $dwResources ,"cmd.exe /c php -d extension=openssl -d allow_url_include=1 2>&1" ,false );
		//parent::__construct( $dwResources ,"cmd.exe /c php -d extension=openssl -d allow_url_include=1" );
		$this->relunchRead=true;
		$this->bootloaderInit=true;
		$dwCallBack = new DwCallBack( array( &$this->log ,'trace' ) );
		
		$this->exec( "echo 'a';" ,$dwCallBack );
		/*
		$this->exec( "echo 'b';" ,$dwCallBack );
		$this->exec( "echo 'c';" ,$dwCallBack );
		$this->execWithoutPrepare( self::getScript( "autoload/standalone/startPipe.php" ) ,new DwCallBack( array( &$this, 'pipeStarted' ) ) );
		//*/
		$this->log->info(".");
	}
	public function pipeStarted() : void {
		$this->log->info(".");
		$phpBootCode = "";
		foreach(array(
			"source/DwSource.php"
			,"objects/DwString.php"
			,"autoload/iDwAutoLoad.php"
			,"autoload/DwAutoLoadBoot.php"
			,"autoload/standalone/DwAutoLoadPipe.php"
		) as $file )
			$phpBootCode.= self::getScript( $file );
		
		$phpBootCode.=\PHP_EOL."<?php echo '=============== startPipe.php ===========================\r\n';\bakuba\kernel\autoload\standalone\DwAutoLoadPipe::standalone( \"runBakuba\" ,\"booting_autoload\" ); ?>".\PHP_EOL;
		$this->exec( base64_encode( $phpBootCode )."\n" ,new DwCallBack( array( &$this, 'autoLoadPipeStarted' ) ) );
		//*/
	}
	public function autoLoadPipeStarted(  ) : void {
		$this->log->info(".");
	}
	public function pipeErrReaded( string $data ,bool $withoutRelunch = false ) : void {
		parent::pipeErrReaded( $data );
		$this->log->trace( $data );
	}
	/*
	public function execWithoutPrepare( string $cmdToExec ,DwCallBack $dwCallBack = null ) : void {
		$boundary=$this->generateBoundary();
		$this->addTask( $cmdToExec.$this->prepareCmdBoundary( $boundary ) ,$boundary ,$dwCallBack );
	}
	//*/
	///*
	public function __destruct() {
		$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

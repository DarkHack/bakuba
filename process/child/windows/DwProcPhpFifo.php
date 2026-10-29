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
//use bakuba\kernel\process\child\windows\DwProcPhpFifo as DwProcPhpFifo;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\process\DwProcess as DwProcess;
use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;

use bakuba\kernel\io\stream\DwStream as DwStream;
use \bakuba\kernel\io\socket\DwSocket as DwSocket;

class DwProcPhpFifo extends \bakuba\kernel\io\processPipes\aDwProcWinBlockingSafe {
	
	public function __construct( \bakuba\kernel\process\DwResources $dwResources ,string $cmdPhp = null ,bool $withErrPipe = true ) {
		if ( is_null( $cmdPhp ) ) $cmdPhp="php";
		parent::__construct( $dwResources ,$cmdPhp ,$withErrPipe );
		//$this->execWithoutPrepare( "echo off".\PHP_EOL );
	}
	protected function prepareCmdBoundary( string $boundary ) : string {
		return "echo ".'"'."{$boundary}".'"'.";";
	}
	protected function prepareExec( string $cmdToExec, string $boundary ) : string {
		$cmdToExec="<?php".\PHP_EOL."try { ".\PHP_EOL."{$cmdToExec}".\PHP_EOL.$this->prepareCmdBoundary( $this->getBundarySucess( $boundary ) ).\PHP_EOL.'} catch( Error $error ) {'.\PHP_EOL.$this->prepareCmdBoundary( $this->getBundaryEchec( $boundary ) ).\PHP_EOL.'} catch( Exception $exception ) {'.\PHP_EOL.$this->prepareCmdBoundary( $this->getBundaryEchec( $boundary ) ).\PHP_EOL."} ".\PHP_EOL."?>".\PHP_EOL;
		return $cmdToExec;
	}
	
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	public static function testToDo() : void {
		/*
		$dwProcPipe = new \bakuba\kernel\objects\DwProcPipe(
			$this
			,"php"
			,\darkphp\logger\DwLoggerLight::getLogger( $this )
		);
		for ($i=0;$i<10;$i++)
			$dwProcPipe->write("<? echo $i; ?>\r\n");
		$dwProcPipe->write("<? echo (5/0); ?>\r\n");
		$dwProcPipe->write("<? echo phpinfo(); ?>\r\n");
		$dwProcPipe->read( new DwCallBack( array( &$this, 'logProc' ) ) );
		//$dwProcFifo->exec( "<? echo 'coucou\r\n'; ?>",new DwCallBack( array( &$this, 'logProc' ) ,$dwProcFifo ,$logger ) );
		//$dwProcFifo->exec( "<? echo 'coucou\r\n'; ?>\r\n",new DwCallBack( array( &$this, 'logProc' ) ,$dwProcFifo ,$logger ) );
		*/
		//$logger=\darkphp\logger\DwLoggerLight::getLogger( $this );
		/*
		$dwProcFifo = new \bakuba\kernel\objects\DwProcFifo(
			$this
			,"php"
			,null
			,null
			,$logger
		);
		for(  $cpt=0;$cpt<16;$cpt++)
			$dwProcFifo->exec(  "<?php echo '$cpt'; ?>",new DwCallBack( array( &$this, 'logProc' ) ,$dwProcFifo ,$logger ) );
		//*/
	}
	/*
	public function logProc( $dwProcFifo ,$log ,$data ) {
		$log->trace( "receive data" );
		$log->trace( $data );
		//$dwProcFifo->exec( "<?php echo 'procend'; ?>",new DwCallBack( array( &$this, 'logProc' ) ,$logger ) );
		//$dwProcFifo->exec( "echo logprocend",new DwCallBack( array( &$this, 'logProc' ) ,$logger ) );
		return empty( $data );
	}
	//*/
}
?>

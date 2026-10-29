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
//use bakuba\kernel\process\child\windows\DwProcCscript as DwProcCscript;

use \Exception as Exception;
use \Error as Error;
use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

use bakuba\kernel\process\DwProcess as DwProcess;
use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\process\DwProcessResources as DwProcessResources;

use bakuba\kernel\io\stream\DwStream as DwStream;
use \bakuba\kernel\io\socket\DwSocket as DwSocket;

class DwProcCscript extends \bakuba\kernel\io\processPipes\aDwProcWinBlockingSafe {
	
	public function __construct(
		\bakuba\kernel\process\child\windows\DwCscript $dwCscript
	) {
		//parent::__construct( $dwCscript->getMyResources() ,"cmd /q /v:on 2>&1", false );
		$jscriptFolder = \bakuba\kernel\agartha\DwInstall::getLocalDirectory( '/var/bakuba/kernel/process/child/windows/JScript' );
		$this->setDebug( $dwCscript->getMyLog() );
		parent::__construct(
			$dwCscript->getMyResources()
			,"C:\Windows\System32\cscript.exe $jscriptFolder\pipe.js //Nologo 2>&1"
			,false
		);
		//parent::__construct( $dwCscript ,"C:\Windows\System32\cscript.exe $jscriptFolder\pipe.js //Nologo" );
		//$this->execWithoutPrepare( "WScript.Echo('coucou');" );
		//$this->exec("var env = WScript.CreateObject(\"WScript.Shell\").Environment(\"PROCESS\");for (var e = new Enumerator(env); !e.atEnd(); e.moveNext()) { WScript.Echo(e.item()); } ".\PHP_EOL );
	}
	
	protected function prepareCmdBoundary( string $boundary ) : string {
		return "WScript.Echo( ".\json_encode( $boundary )." );";
	}
	// With Perplexity Production :
	public function execWithoutPrepare( string $strExecute ,DwCallBack $dwCallBack = null ) : void {
		// Supprimer les commentaires multi-lignes /* ... */
		$code = \preg_replace('!/\*.*?\*/!s', '', $strExecute);
		// Supprimer les commentaires mono-ligne //
		$code = \preg_replace('!//.*!', '', $code);
		// Optionnel: Suppression lignes vides et cleanup
		$code = \preg_replace('/^\s*[\r\n]/m', '', $code);
		
		$boundary=$this->generateBoundary();
		//$this->log->info( $boundary );
		$this->addTask( \base64_encode( $code.$this->prepareCmdBoundary( $boundary ) )."---MYSEPARATOR---" ,$boundary ,$dwCallBack );
	}
	
	protected function prepareExec( string $cmdToExec, string $boundary ) : string {
		$cmdPrepared="try { ".\PHP_EOL."{$cmdToExec} ".\PHP_EOL." WScript.Echo( ".\json_encode( $this->getBundarySucess( $boundary ) )." );".\PHP_EOL." } catch (e) { WScript.Echo( ".\json_encode( $this->getBundaryEchec( $boundary ) )."); }".\PHP_EOL;
		return $cmdPrepared;
	}
	public function exec( string $cmdToExec ,DwCallBack $dwCallBack = null ) : void {
		$boundary=$this->generateBoundary();
		$this->addTask( \base64_encode( $this->prepareExec( $cmdToExec ,$boundary ) )."---MYSEPARATOR---" ,$boundary ,$dwCallBack );
	}
	///*
	public function __destruct() {
		//$this->log->trace( "." );
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

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

namespace bakuba\kernel\process\child\posix;
//use bakuba\kernel\process\child\posix\DwShell as DwShell;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwShell extends \darkphp\DwObject {
	
	private $dwProcShell = null;
	public function __construct( \bakuba\kernel\process\DwResources $dwResources ) {
		parent::__construct( $dwResources );
	}
	protected function getDwProcShell() : DwProcShell {
		if ( ! isset( $this->dwProcShell ) || \is_null( $this->dwProcShell ) ) {
			$this->dwProcShell = new DwProcShell( $this->parent );
			$this->dwProcShell->onTaskEnded( new DwCallBack( array( &$this ,'tasksEnded' ) ) );
			$this->initScript( $this->dwProcShell );
		}
		return $this->dwProcShell;
	}
	public function tasksEnded() : void {
		$dwProcShell = $this->dwProcShell;
		$this->dwProcShell = null;
		$dwProcShell->shutdown();
		$this->log->trace( "proc shell ended" );
	}
	public function exec( string $cmdToExec ,DwCallBack $dwCallBack = null ) : void {
		$this->getDwProcShell()->exec( $cmdToExec ,$dwCallBack );
	}
	public function initScript( DwProcShell $dwProcShell ) : void {
		$dwProcShell->execWithoutPrepare( "#!/bin/sh".\PHP_EOL );
		$dwProcShell->execWithoutPrepare( sDwShell::getScript("which/base").\PHP_EOL );
		$dwProcShell->execWithoutPrepare( sDwShell::getScript("lib-sh/shLog").\PHP_EOL );
		$dwProcShell->execWithoutPrepare( sDwShell::getScript("lib-sh/shPipe").\PHP_EOL );
		$dwProcShell->execWithoutPrepare( sDwShell::getScript("lib-sh/shJson").\PHP_EOL );
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

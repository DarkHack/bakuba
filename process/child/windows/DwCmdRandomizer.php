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
//use bakuba\kernel\process\child\windows\DwCmdRandomizer as DwCmdRandomizer;

class DwCmdRandomizer extends DwCmd {
	/*
	public function __construct( \bakuba\kernel\process\child\windows\DwWinAgent $dwWinAgent ) {
		parent::__construct( $dwWinAgent );
		//$this->exec( "echo nextTask" );
		//$this->exec(
		//	self::getScript("randomize/randomize.cmd")
		//	, new \bakuba\kernel\objects\DwCallBack( array( &$this, 'stringLoaded' ) )
		//);
	}
	//*/
	protected function createDwProcCmd() : DwProcCmd {
		$this->log->trace( "." );
		$dwProcCmd = parent::createDwProcCmd();
		$dwProcCmd->execWithoutPrepare( self::getScript("string/lib.cmd") );
		return $dwProcCmd;
	}
	public function random( int $length , \bakuba\kernel\objects\DwCallBack $dwCallBack ) {
		//$this->log->trace( $dwCallBack );
		$dwProcCmd = $this->getDwProcCmd();
		$dwProcCmd->execWithoutPrepare(
			"set \"length=$length\"\n".self::getScript("randomize/randomize.cmd")
		);
		$dwProcCmd->exec( "echo %randomsAsciiStr%", $dwCallBack );
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

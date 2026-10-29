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
//use bakuba\kernel\process\child\posix\DwShellRandomizer as DwShellRandomizer;

class DwShellRandomizer extends DwShell {
	
	public static function getRandomCache( int $size = 1500 ) : string {
		$scriptContent="";
		$dwRandomizer = \bakuba\kernel\randomizer\DwRandomizer::getRandomizer();
		if ( \is_object( $dwRandomizer ) ) {
			$scriptContent.="\$cmdPrintf \"";
			$scriptContent.=$dwRandomizer->random( $size );
			$scriptContent.="\"| shPipeWrite \$shRandomizePipe;\n";
		}
		return $scriptContent;
	}
	public function initScript( DwProcShell $dwProcShell ) : void {
		parent::initScript( $dwProcShell );
		$dwProcShell->execWithoutPrepare( sDwShell::getScript( "shRandomize/shRandomize.sh" ) );
	}
	
	public function random( int $length , \bakuba\kernel\objects\DwCallBack $dwCallBack ) {
		$this->log->trace( $dwCallBack );
		$dwProcShell = $this->getDwProcShell();
		$dwProcShell->exec( "shRandomizeASCCI \"$length\"", $dwCallBack );
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

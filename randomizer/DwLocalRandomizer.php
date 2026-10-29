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

namespace bakuba\kernel\randomizer;
//use bakuba\kernel\shell\DwShellRandomizer as DwShellRandomizer;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\randomizer\DwRandomizer as DwRandomizer;

use bakuba\kernel\process\DwProcessShell as DwProcessShell;

use bakuba\kernel\application\session\cryptoShell\DwJsonCrypto as DwJsonCrypto;

class DwLocalRandomizer extends DwShellPerm {
	
	public static function getRandomCache( int $size = 1500 ) : string {
		$scriptContent="";
		$dwRandomizer = DwRandomizer::getRandomizer();
		if ( is_object( $dwRandomizer ) ) {
			$scriptContent.="\$cmdPrintf \"";
			$scriptContent.=$dwRandomizer->random( $size );
			$scriptContent.="\"| shPipeWrite \$shRandomizePipe;\n";
		}
		return $scriptContent;
	}
	
	public static function getInitScript() : string {
		$scriptContent = DwShell::getInitScript();
		$scriptContent .= DwShell::getScript("shRandomize/shRandomize.sh")."\n";
		$scriptContent .= self::getRandomCache();
		
		return $scriptContent;
	}
	
	public static function random(
		int $length
		,DwProcessShell $dwProcessShell= null
		,DwCallBack $dwCallBack = null
	) : ?string {
		$debugKey=self::getStaticClassName();
		$randomPass = null; 
		$scriptContent = "shRandomizeASCCI \"$length\"";
		if ( is_null( $dwProcessShell ) || is_null($dwCallBack) )
			$randomPass = DwShell::exec(
				self::getInitScript().$scriptContent." 2>/dev/null "
			);
		else
			$dwProcessShell->shellRandomizerStart( $scriptContent , $dwCallBack );
		return $randomPass;
	}
	public function init( ) : void {
		parent::init();
		$this->start( self::getInitScript() );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

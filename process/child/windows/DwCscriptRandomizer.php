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
//use bakuba\kernel\process\child\windows\DwCscriptRandomizer as DwCscriptRandomizer;

class DwCscriptRandomizer extends DwCscript {
	
	protected function createDwProcCscript() : DwProcCscript {
		$dwProcCscript = parent::createDwProcCscript();
		$dwProcCscript->execWithoutPrepare( self::getScript("randomize/randomize.js") );
		return $dwProcCscript;
	}
	public function random( int $length , \bakuba\kernel\objects\DwCallBack $dwCallBack ) {
		//$this->log->trace( $dwCallBack );
		$this->exec( "WScript.Echo( jsRandomAscii( $length ) );", $dwCallBack );
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

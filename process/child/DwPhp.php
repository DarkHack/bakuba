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
//use bakuba\kernel\process\child\windows\DwPhp as DwPhp;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwPhp extends \darkphp\DwObject {
	static private $kernelPhpDir="file://bakuba/kernel";
	
	public static function getScript( string $scriptPath ) : ?string {
		return \bakuba\kernel\autoload\DwAutoLoad::getFile( self::$kernelPhpDir."/{$scriptPath}" );
	}
	
	private $dwProcPhp = null;
	public function __construct( 
		\bakuba\kernel\process\child\windows\DwWinAgent $dwWinAgent
	) {
		parent::__construct( $dwWinAgent );
	}
	protected function createDwProcPhp() : DwProcPhp {
		$dwProcPhp = new DwProcPhp( $this );
		/*
		$dwProcPhp->onTaskEnded( new DwCallBack( array( &$this ,'tasksEnded' ) ) );
		$dwProcPhp->onTimeoutCmd( new DwCallBack( array( &$this ,'taskTimeOut' ) ) );
		$logPath = \bakuba\kernel\agartha\DwInstall::getLocalDirectory( $this->log->getLevelLog() );
		$dwProcPhp->execWithoutPrepare( "var jsLogFile=\"{$logPath}/jsLog.log\";" );
		$dwProcPhp->execWithoutPrepare( self::getScript("lib-js/jsLog.js") );
		$dwProcPhp->execWithoutPrepare( self::getScript("lib-js/jsObject.js") );
//		$dwProcPhp->execWithoutPrepare( self::getScript("lib-js/jsArray.js") );
		$dwProcPhp->execWithoutPrepare( self::getScript("lib-js/jsString.js") );
		$dwProcPhp->execWithoutPrepare( self::getScript("lib-js/jsBase.js") );
		$dwProcPhp->execWithoutPrepare( self::getScript("lib-js/jsJson.js") );
		//*/
		return $dwProcPhp;
	}
	protected function getDwProcPhp() : DwProcPhp {
		if ( !isset( $this->dwProcPhp ) || \is_null( $this->dwProcPhp ) )
			$this->dwProcPhp = $this->createDwProcPhp();
		return $this->dwProcPhp;
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

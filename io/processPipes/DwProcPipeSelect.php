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

namespace bakuba\kernel\io\processPipes;
//use bakuba\kernel\io\processPipes\DwProcPipeSelect as DwProcPipeSelect;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\time\DwChrono as DwChrono;

class DwProcPipeSelect extends \bakuba\kernel\io\stream\DwStreamSelect {
	private $parentInstanceKey=null;
	///*
	public function __construct( \bakuba\kernel\process\DwResources $parent ,DwProcPipe $dwProcPipe ) {
		$this->parentInstanceKey=$dwProcPipe->getInstanceKey();
		parent::__construct( $parent );
		$this->log->trace( "." );
	}
	private $toggleRW = false;
	public function messaging_select( array &$dwResourcesRead, array &$dwResourcesWrite, array &$dwResourcesExcept ) : int {
		$empty = array();
		if ( $this->toggleRW )
			$nbReady = parent::messaging_select( $empty, $dwResourcesWrite, $dwResourcesExcept );
		else
			$nbReady = parent::messaging_select( $dwResourcesRead, $empty, $dwResourcesExcept );
		$this->log->trace( ( $this->toggleRW ? "Write:" : "Read:" ).strval( $nbReady ) );
		$this->toggleRW=!$this->toggleRW;
		return $nbReady;
	}
	//*/
	/*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	//*/
	public function toString( $withParent = false ) : string {
		return parent::toString( $withParent )."|".$this->parentInstanceKey."|";
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

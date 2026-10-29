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

namespace bakuba\kernel\process;
//use bakuba\kernel\process\DwProcessResources as DwProcessResources;

abstract class DwProcessResources extends DwProcessBase {
	private $dwResources = null;
	public function setDwResources( DwResources $dwResources ) : void {
		$this->dwResources = $dwResources;
	}
	///*
	protected function __construct( self $parentProcess ) {
		/*
		if ( \is_object( $parentProcess ) )
			if ( \is_object( $parentProcess->dwResources ) )
				$this->dwResources = $parentProcess->dwResources;
		//*/
		parent::__construct( $parentProcess );
		if ( \is_null( $this->dwResources ) )
			$this->dwResources = new DwResources( $this );
	}
	protected function init() : void {
		parent::init( );
	}
	public function getDwResources() : DwResources {
		return $this->dwResources;
	}
	protected function getDwLocalAgent() : child\DwLocalAgent {
		return $this->dwResources->getDwLocalAgent();
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
		return $fqcn ? 
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__
			: __CLASS__
		;
	}
}

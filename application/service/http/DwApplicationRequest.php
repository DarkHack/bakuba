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

namespace bakuba\kernel\application\service\http;
//use bakuba\kernel\application\service\http\DwApplicationRequest as DwApplicationRequest;

use bakuba\kernel\services\http\DwHttpServiceRequest as DwHttpServiceRequest;

class DwApplicationRequest extends \bakuba\kernel\application\service\DwApplicationRequest {
	
	public function getDwHttpServiceRequest( ) : DwHttpServiceRequest {
		return $this->getDwServiceRequest();
	}

	public function __construct(
		DwHttpServiceRequest $dwHttpServiceRequest
		,\darkphp\DwObject $dwParent = null
	) {
		parent::__construct( $dwHttpServiceRequest ,$dwParent ); 
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		//$this->addtoSerialize( "staticFilesRequest" );
	}
	protected $staticFilesRequest = null;
	public function getStaticFilesRequest( ) : ?array { 
		return $this->staticFilesRequest;
	}
	public function setStaticFilesRequest( array $staticFilesRequest ) : void { 
		$this->staticFilesRequest = $staticFilesRequest;
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

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

namespace bakuba\kernel\session\application;
//use bakuba\kernel\session\application\DwApplicationSessionConnexion as DwApplicationSessionConnexion;

class DwApplicationSessionConnexion
	extends \bakuba\kernel\application\aDwApplicationConnexion 
	{
	///*
	public function __construct(
		aDwApplicationSession $aDwApplicationSession
		,\bakuba\kernel\io\ipc\DwIpcMsg $dwIpcMsg
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) {
		parent::__construct(
			$dwApplicationSession
			,$dwIpcMsg
			,$dwApplicationRequest
		);
	}
	//*/
	
	public function getDwSessionManager() : \bakuba\kernel\session\DwSessionManager {
		return $this->parent->getDwSessionManager();
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

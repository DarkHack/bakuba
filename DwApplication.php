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

namespace bakuba\kernel;
//use bakuba\kernel\DwApplication as DwApplication;

class DwApplication extends \bakuba\kernel\application\service\http\controller\aDwApplicationController {
	/*
	public function init( ) : void {
		parent::init();
	}
	//*/
	/*
	public function __construct(
		\bakuba\kernel\process\DwProcessIpc $parentProcess
		,string $domain
		,string $urlApp
	) {
		parent::__construct( $parentProcess, $domain, $urlApp );
	}
	
	public function createApplicationSession(
		\bakuba\kernel\application\session\DwSessionIpKey $dwSessionIpKey
	) : \bakuba\kernel\application\DwApplicationSession {
		$dwApplicationSession = parent::createApplicationSession( $dwSessionIpKey );
		// 1200 s => 60 x 200 => out in 200mn
		$dwApplicationSession->setTimeout(12000);
		return $dwApplicationSession;
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

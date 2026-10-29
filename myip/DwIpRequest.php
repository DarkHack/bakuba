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

namespace bakuba\kernel\myip;
//use bakuba\kernel\myip\DwIpRequest as DwIpRequest;

use bakuba\web\session\DwSessionChannel as DwSessionChannel;

use bakuba\kernel\io\ipc\DwIpcKey as DwIpcKey;
class DwIpRequest {
	
	private $dwSessionChannel = null;
	public function getDwSessionChannel( ) : DwSessionChannel {
		return $this->dwSessionChannel;
	}
	
	public function __construct(
		DwSessionChannel $dwSessionChannel
	) {
		$this->dwSessionChannel = $dwSessionChannel;
	}
	private $dwIpcKey = null;
	public function getDwIpcKey( ) : DwIpcKey {
		return $this->dwIpcKey;
	}
	public function setDwIpcKey( DwIpcKey $dwIpcKey ) : void {
		$this->dwIpcKey = $dwIpcKey;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		//return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
		return __CLASS__;
	}
}
?>

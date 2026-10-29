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

namespace bakuba\kernel\io\socket\dns;
//use bakuba\kernel\io\socket\dns\DwSocket as DwSocket;

class DwSocket extends \bakuba\kernel\io\socket\DwSocket {
    protected $packetRcvSize = 4096;
    protected $packetSndSize = 4096;
	/*
	public function __construct( \darkphp\DwObject $parent ,\bakuba\kernel\network\DwInterface $dwInterface ,$sock ) {
		//$this->enableFullTrace();
		parent::__construct( $parent ,$dwInterface ,$sock );
		//$this->log->trace(".");
	}
	//*/
}
?>

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

namespace bakuba\kernel\agartha;
//use bakuba\kernel\agartha\iDwAgent as iDwAgent;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

interface iDwAgent {
	public function getUUID( ) : string;
	/*
	public function scanInterfaces( DwCallBack $dwCallBack ) : void;
	public function random( int $length , DwCallBack $dwCallBack ) : void;
	public function listCiphers( DwCallBack $dwCallBack ) : void;
	public function listDigests( DwCallBack $dwCallBack ) : void;
	public function initMessage( DwCallBack $dwCallBack ,array $ciphers = null ,array $digests = null ) : void;
	//*/
	//public function encrypt( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : void;
	//public function decrypt( DwCallBack $dwCallBack ,$jsonMessage ) : void;
}
?>

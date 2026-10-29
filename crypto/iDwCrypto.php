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

namespace bakuba\kernel\crypto;
// use bakuba\kernel\crypto\iDwCrypto as iDwCrypto;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;

interface iDwCrypto {
	public function listCiphers( DwCallBack $dwCallBack ): void;
	public function listDigests( DwCallBack $dwCallBack ): void;
	
	public function initMessage( DwCallBack $dwCallBack ,array $ciphers ,array $digests ): void;
	public function encrypt( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : void;
	public function decrypt( DwCallBack $dwCallBack ,DwJsonCrypto $dwJsonCrypto ) : void;
	
	public function generateSaltKeyIv( DwCallBack $dwCallBack ,string $cipher ,string $pass ) : void;
	//public function commonCiphers( array $ciphers ) : array;
	//public function commonDigests( array $digests ) : array;
}
?>

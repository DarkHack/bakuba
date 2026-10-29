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

namespace darkphp\serializable;
//use darkphp\serializable\iDwSerializable as iDwSerializable;

interface iDwSerializable extends iDwNoSerializable ,\Serializable {
	public function __serialize() : array;
	public function serialize() : string;
	public function __unserialize( array $serialized ) : void;
	public function unserialize( $serialized ) : void;
	public function toString( $withParent = false ) : string ;
}
?>

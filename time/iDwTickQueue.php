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

namespace bakuba\kernel\time;
//use bakuba\kernel\time\iDwTickQueue as iDwTickQueue;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

/**
 * @internal
 */
interface iDwTickQueue extends \darkphp\iDwObject {
	//public function getDwTickQueueChild() : self;
	//public function getDwTickQueueChild( \darkphp\DwObject $parent ) : self;
	//public function reset();
    public function addTick( DwCallBack $dwCallBack ) : void;
    public function cancelCallBack( DwCallBack $dwCallBack ) : bool;
	public function isEverSchudeled( DwCallBack $dwCallBack ) : bool;
	public function count() : int;
	//public function dequeue() : DwCallBack;
    //public function tick() : void;
	public function hasTick() : bool;
	//public static function run( self $iDwTickQueue ) : void;
}
?>

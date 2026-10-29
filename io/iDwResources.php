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

namespace bakuba\kernel\io;
//use bakuba\kernel\io\iDwResources as iDwResources;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

interface iDwResources {
	public function read( iDwResource $dwResource ) : void;
	public function write( iDwResource $dwResource ) : void;
	
	public function readed( iDwResource $dwResourcesReaded ) : void;
	public function writed( iDwResource $dwResourcesWrited ) : void;
	public function init( DwResourceSelect $dwResourceSelect ) : void;
	public function addSignal( int $signo ) : void;
}

?>

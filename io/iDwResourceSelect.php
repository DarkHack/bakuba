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
//use bakuba\kernel\io\IDwResourceSelect as IDwResourceSelect;

//use \Exception as Exception;
//use \Error as Error;

//use darkphp\logger\DwLogger as DwLogger;
//use darkphp\DwObject as DwObject;

//use bakuba\kernel\io\IDwResource as IDwResource;
interface iDwResourceSelect {
	
	public function check() : bool;
	public function messaging_select( array &$dwResourcesRead, array &$dwResourcesWrite, array &$dwResourcesExcept ) : int;
	
	//protected function resourceRead( IDwResource $dwResource ) : void;
	public function read( array &$dwResourcesRead, array &$dwResourcesExcept ) : array;
	public function readEnd( array $dwResourcesReaded ) : void;
	
	//protected function resourceWrite( IDwResource $dwResource ) : void;
	public function write( array &$dwResourcesWrite, array &$dwResourcesExcept ) : array;
	public function writeEnd( array $dwResourcesWrited ) : void;
	
}

?>

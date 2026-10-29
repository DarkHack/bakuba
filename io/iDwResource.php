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
//use bakuba\kernel\io\iDwResource as iDwResource;

interface iDwResource {
	public function checkActivity() :bool;
	
	public function isResource() : bool ;
	public function isResourceDefined() : bool;
	public function getResource();
	public function connect( $resource = null ) : bool;
	public function disconnect( ) : bool;
	
	public function readReady() : bool ;
	
	public function readEnd() : bool ;
	
	public function writeEnd() : bool;
	
	//public function close( ) ;
	//public function close( ) : bool;
	
}

?>

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
//use bakuba\kernel\io\iDwResourceBuffered as iDwResourceBuffered;

interface iDwResourceBuffered extends iDwResource {
	public function getDwChronoResource() : DwChronoResource;
	
	public function clearRecvBuf() : void ;
	
	public function getRcvSize() : int;
	public function getRecvCpt() : int ;
	public function getRecvInactivity() : int ;
	
	public function receive( string $data = null ) : int ;
	public function read( ) : ?string;
	
//	public function send( ) : bool;
	public function write( string $response ) :void;
	
	public function forward(  ) : void;
	
	
	//public function close( ) ;
	//public function close( ) : bool;
}

?>

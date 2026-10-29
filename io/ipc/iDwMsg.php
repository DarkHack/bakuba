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

namespace bakuba\kernel\io\ipc;
//use bakuba\kernel\io\ipc\iDwMsg as iDwMsg;

interface iDwMsg {
	public function getDstKey() : DwIpcKey;
	public function getSrcKey() : DwIpcKey;
	public function getId() : string;
	public function getRid() : ?string;
	public static function response( iDwMsg $iDwMsg ) : iDwMsg;
	/*
	public function __construct( DwIpcKey $dstKey ,string $mid = null );
	public function forwardTo( DwIpcKey $dstKey );
	public function source( DwIpcKey $srcKey );
	public function connect( $queue = null ) : bool;
	public function disconnect() : bool;
	//private function retreiveMessage( ) : DwMessageQueuePacket;
	public function retreiveMessages() : array;
	public function receivePacket( DwMessageQueuePacket $dwMessageQueuePacket ) : void;
	
	public function receive( ) : void ;
	public function write( string $response ) ;
	public function rewrite( ) ;
	public function send() : bool ;
	//private function tickMessage( ) : string;
	public function stats() : int ;
	public function getClassName( bool $fqcn=null ) : string ;
	public static function getStaticClassName( bool $fqcn=null ) : string ;
	//*/
}

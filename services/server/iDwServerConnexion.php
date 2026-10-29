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

namespace bakuba\kernel\services\server;
//use bakuba\kernel\services\server\iDwServerConnexion as iDwServerConnexion;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\network\DwIp as DwIp;
use bakuba\kernel\network\DwPort as DwPort;

interface iDwServerConnexion  {
	
	//private function getDwResourceIpConnexion() : \bakuba\kernel\io\DwResourceIpConnexion;
	
	public function getDwServer() : DwServer;
	public function getDwSessionIpKey() : \bakuba\kernel\session\DwSessionIpKey;
	public function getDwSessionIp() : \bakuba\kernel\session\DwSessionIp;
	
	public function isSended() : bool;
	public function ipcSend(
		\bakuba\kernel\process\DwProcessIpc $dwProcessDestination
		,$objectToSerialize
		,DwCallBack $dwCallBackSended = null
	) : void;
	public function ipcHandle( $objectUnserialized ) : void;
	//*/
	// DwSocketConnexion->request => 
	public function request() : void;
	public function throwIpcError( \bakuba\kernel\io\ipc\DwIpcMsgError $dwIpcMsgError ) : void;
	
	public function resourceResponse( $objectUnserialized ) : void;
	
	public function read() : ?string ;
	public function getSrvIp() : DwIp ;
	public function getSrvPort() : DwPort ;
	public function getCltIp() : DwIp ;
	public function getCltPort() : ?DwPort ;
}

?>

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

namespace bakuba\kernel\application\service\session;
//use bakuba\kernel\application\service\session\iDwSessionChannel as iDwSessionChannel;
//use bakuba\kernel\session\iDwSessionChannel as iDwSessionChannel;

interface iDwSessionChannel
	extends \bakuba\kernel\application\session\iDwSessionChannel {
	protected function addSessionIp( DwSessionIpKey $dwSessionIpKey ) : bool ;
	public function getChannelKey() ;
	public function hasIpSrv( string $ipSrv ) : bool;
	public function hasIpClt( $ipClt ) : bool;
	public function hasIpKey( $ipKey ) : bool;
	public function getIpsKey() : ?array;
	
	public function searchSession(
		\bakuba\kernel\services\http\DwHttpServiceRequest $dwHttpServiceRequest
		,\bakuba\kernel\objects\DwCallBack $dwCallBack
	) : void;
}

?>

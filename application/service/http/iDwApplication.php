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

namespace bakuba\kernel\application\service\http;
//use bakuba\kernel\application\service\http\iDwApplication as iDwApplication;

interface iDwApplication extends \bakuba\kernel\application\service\iDwApplication {
	
	public function httpServiceConnect(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,DwApplicationRequest $dwApplicationRequest
	) : iDwApplicationConnexion;
	
	public function appRegistered(
		\bakuba\kernel\services\http\DwHttpdResponse $dwHttpdResponse
	) : void;
	
	public function getDomain() : string;
	public function addDomain( string $domain ) : void;
	public function getOtherDomains() : ?array;
	
	public function getDwServerProcess() : \bakuba\kernel\services\application\DwApplication;
	
	public function rootShortcutInit(
		\bakuba\kernel\services\http\DwRootShortcut $dwRootShortcut
	) : void;
	public function shortcutInit(
		\bakuba\kernel\services\http\DwShortcut $dwShortcut
	) : void;
}

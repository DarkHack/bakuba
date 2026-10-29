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

namespace bakuba\kernel\application;
//use bakuba\kernel\application\iDwApplicationServer as iDwApplicationServer;

interface iDwApplicationServer extends iDwApplication {
	public function appRegistered( \bakuba\kernel\agartha\DwBakubaResponse $dwBakubaResponse ) : void;
	
	public function getDomain() : string;
	public function addDomain( string $domain ) : void;
	public function getOtherDomains() : ?array;
	
	public function getDwServerProcess() : \bakuba\kernel\services\server\DwServerProcess;
	
	public function rootShortcutInit( DwApplicationRootShortcut $dwApplicationRootShortcut ) : void;
	public function shortcutInit( DwApplicationShortcut $dwApplicationShortcut ) : void;
}

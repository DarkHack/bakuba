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

// bakuba kernel Api_Init
// Last Review : 08/03/2011
// For : Darknet
// Auteur : n.chapuis

global $myKernel,$Api_Debug;
if ($Api_Debug) echo "Api_Init : lunch kernel\n";
$staringKernel= new Api_Kernel();
$staringKernel->autoload("Application");
if (class_exists("Application")) {
 $staringKernel->__destruct();
 unset($staringKernel);
 $myKernel=new Application();
}else $myKernel=$staringKernel;
if ($Api_Debug) echo "Api_Init : lunch alive\n";

function ApiInclude($toInclude) {
 Api_Function_Start("ApiInclude",Array("include"=>$toInclude));
 global $myKernel;
 $retour=$myKernel->ApiInclude($toInclude);
 Api_Function_Stop();
 return $retour;
}
function ApiRead($toInclude) {
 Api_Function_Start("ApiRead",Array("read"=>$toInclude));
 global $myKernel;
 $retour=$myKernel->ApiRead($toInclude);
 Api_Function_Stop();
 return $retour;
}
?>
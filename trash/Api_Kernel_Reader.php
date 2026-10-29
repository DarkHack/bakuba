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

// bakuba kernel Api_Kernel_Reader
// Last Review : 08/03/2011
// For : Darknet
// Auteur : n.chapuis
class Api_Kernel_Reader extends Api_Kernel {
 public function __construct() {
  $this->class_start("Api_Kernel_Reader");
  parent::__construct();
  $this->methode_stop();
 }
 public function __destruct() {
  $this->methode_start("__destruct");
  $retour=eval(parent::__destruct());
  $this->methode_stop();
  return $retour;
 }
 public function GetSrcXml($haveToCreate=false) {
  $this->methode_start("GetSrcXml");
  $this->InitSrcXml($haveToCreate);
  $this->methode_stop();
  return $this->srcXml;
 }
 public function GetBinXml($haveToCreate=false) {
  $this->methode_start("GetBinXml");
  $this->InitBinXml($haveToCreate);
  $this->methode_stop();
  return $this->binXml;
 }
}
?>
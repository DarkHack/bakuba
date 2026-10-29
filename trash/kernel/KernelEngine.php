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


/**
 * bakuba kernel KernelEngine
 *
 * @package bakuba
 * @subpackage bakuba.kernel
 *
 * @author $Author: chapuis $
 * @version $Revision: 136 $
 *
 */
class KernelEngine {

 private $log_erreur_file="";
 protected $Api_Engine_String_To_Clean="";

 /**
  * construct the engine object
  */
 public function __construct() {
  global $Api_folder_root,$Api_folder_bin,$Api_folder_sys;
  $this->log_erreur_file="$Api_folder_root/$Api_folder_bin/$Api_folder_sys/erreur_engine";
  //echo "log_erreur_file :{$this->log_erreur_file}<br/>\n";
 }
 /**
  * destruct the class
  */
 public function __destruct() {
  $this->Api_Engine_String_To_Clean="foreach(\$this as \$toDestruct => \$value){if (\$toDestruct!=\"Api_Engine_String_To_Clean\") unset(\$this->\$toDestruct);}return \$this->Api_Engine_String_To_Clean;";
  $retour=eval($this->Api_Engine_String_To_Clean);
  return $retour;
 }
}
//end class Api_Engine
?>
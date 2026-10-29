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
 * Test of KernelEngine
 *
 * @package bakuba
 * @subpackage bakuba.kernel
 *
 * @author $Author: chapuis $
 * @version $Revision: 136 $
 *
 */

include_once dirname(__FILE__)."/../../kernel/KernelEngine.php";
class test {
 public function __construct() {
  echo "construct test<br/>\n";
 }
 public function __destruct() {
  echo "destruct test<br/>\n";
 }
}
class KernelEngineTest extends KernelEngine {
 private $objTest=false;
 /**
  * (non-PHPdoc)
  * @see KernelEngine::__destruct()
  */
 public function __construct() {
  $this->objTest=new test();
  parent::__construct();
 }
 /**
  * (non-PHPdoc)
  * @see KernelEngine::__destruct()
  */
 public function __destruct() {
  $retour=eval(parent::__destruct());
  return $retour;
 }
}
//end class KernelEngineTest


$kernelEngineTest=new KernelEngineTest();
unset($kernelEngineTest);
echo "oki<br/>\n";
$kernelEngineTest=new KernelEngineTest();
echo "oki<br/>\n";
$kernelEngineTest=new KernelEngineTest();
?>
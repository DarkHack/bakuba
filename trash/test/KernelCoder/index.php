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

$kernel_folder=dirname(__FILE__)."/../../kernel";
include_once "$kernel_folder/KernelEngine.php";
include_once "$kernel_folder/KernelCoder.php";

//header("Content-type: text/xml");
$kernelCoder = new KernelCoder("puis une autre cle");
$content="";
for($i=0;$i<55000;$i++)
 $content.="un message comme un autre :$i<br/>";//file_get_contents("$kernel_folder/KernelEngine.php")."<br/>\n".file_get_contents("$kernel_folder/KernelRapport.php")."<br/>\n".file_get_contents("$kernel_folder/KernelCoder.php")."<br/>\n";
//$content=$content.$content.$content.$content.$content.$content.$content.$content.$content.$content;
//echo $content.$content.$content.$content.$content;
//echo "<br/>\n<br/>\nencrypte  :<br/>\n";
echo "contenu a coder taille :".strlen($content)."<br/>";
$encrypted=$kernelCoder->darkEncoder($content);
echo "contenu crypter taille :".strlen($encrypted)."<br/>";
$uncrypted=$kernelCoder->darkDecoder($encrypted);
echo "encrypted ".strlen($encrypted)." :<br/>";//.$encrypted."<br/>\n";
echo "uncrypted ".strlen($uncrypted)." :<br/>";//Message decoder :".$uncrypted."<br/>\n";
if (strlen($uncrypted)!=0)
echo "ratio :".strlen($encrypted)/strlen($uncrypted);
unset($kernelCoder);
//echo $kernelCoder->decoder($encrypted);

//header("Content-type: text/xml");
//echo KernelRapport::get();

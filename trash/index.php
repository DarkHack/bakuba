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

ob_start();
system("echo '---------------------------------------------------------------------------' >> ../.bin/update_api.log");
system("echo 'update of kernel' >> ../.bin/update_api.log");
system("svn update . --username \"chapuis\" --password \"\" --non-interactive . >> ../.bin/update_api.log");
$versKernel=system("svn info .| grep \"vision :\" | cut -d' ' -f 3");
system("echo 'update of api' >> ../.bin/update_api.log");
system("svn update . --username \"chapuis\" --password \"\" --non-interactive ../ >> ../.bin/update_api.log");
$versApi=system("svn info ../| grep \"vision :\" | cut -d' ' -f 3");
system("echo 'update of bakuba' >> ../.bin/update_api.log");
system("svn update . --username \"chapuis\" --password \"\" --non-interactive ../../ >> ../.bin/update_api.log");
$versKuba=system("svn info ../../| grep \"vision :\" | cut -d' ' -f 3");
ob_end_clean();
require_once(dirname(__FILE__)."/../Api_Tools.php");
$updateKernel=true;
$updateApi=true;
$updateKuba=true;

$myFileVersion = new Api_File("myVersionFile");
$contentFileVersion=$myFileVersion->read();
if (strlen($contentFileVersion)>0)
 $xmlFileVersion=simplexml_load_string($contentFileVersion);
else
 $xmlFileVersion=new SimpleXMLElement("<version/>");
if (isset($xmlFileVersion->versKernel))
 if ($xmlFileVersion->versKernel==$versKernel)
  $updateKernel=false;
if (isset($xmlFileVersion->versApi))
 if ($xmlFileVersion->versApi==$versApi)
  $updateApi=false;
if (isset($xmlFileVersion->versKuba))
 if ($xmlFileVersion->versKuba==$versKuba)
  $updateKuba=false;

$xmlFileVersion->versKernel=$versKernel;
$xmlFileVersion->versApi=$versApi;
$xmlFileVersion->versKuba=$versKuba;
$myFileVersion->write($xmlFileVersion->asXml());
if ($updateKernel){
 //echo "update kernel.<br/>\n";
 global $Api_folder_root,$Api_folder_bin,$Api_folder_sys;
 if (is_file("$Api_folder_root/$Api_folder_bin/starter"))
  unlink("$Api_folder_root/$Api_folder_bin/starter");
 if (is_file("$Api_folder_root/$Api_folder_bin/$Api_folder_sys/kernel.crypt"))
  unlink("$Api_folder_root/$Api_folder_bin/$Api_folder_sys/kernel.crypt");
 //require_once(dirname(__FILE__)."/Api_Compiler.php");
 die("update kernel needed\n");
}
//}
//if ($updateApi)
// die("update api needed");
if ($updateKuba){
 //echo "update kuba needed\n";
}
// die("update kuba needed");
// init sender
$mySender=new Api_Sender($_GET);// Api_Rapport_Save("rapport.xml");
if ($mySender->toSend!="")
 echo $mySender->toSend;
else {
?>
<html>
 <head><style type="text/css">a img {border:none;}</style></head>
 <body style="background-color:#000000;color:#FFFFFF;">
  <h1><center>noyau de bakuba</center></h1>
  <center><a href="http://www.darkweb.fr"><img src="http://bakuba.darkweb.fr/logo.png" width="250px"/></a></center>
 </body>
</html>
<?php }
if (!is_dir("rapport")) mkdir("rapport");
Api_Rapport_Save("rapport/_".date("d_m_Y_H_i_s_").getmypid()."_index.xml");
?>
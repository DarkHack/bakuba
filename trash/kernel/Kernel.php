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

// bakuba kernel Api_Kernel
// Last Review : 08/04/2011
// For : Darknet
// Auteur : n.chapuis
class Kernel extends KernelErreur {
 private $debug=false;
 protected $srcPath;
 protected $srcXml;
 protected $binPath;
 protected $binXml;
 
 public function __construct(SimpleXmlElment $debug=null,$srcPath = "source.crypt",$binPath = "binary.crypt" ) {
  //echo "kernel gone alive";
  parent::__construct();
  //echo "kernel attributs of construct()\n";
  $this->srcPath=$srcPath;
  $this->binPath=$binPath;
  $this->debug=$debug;
  //echo "kernel gone alive";
  $this->checkPid();
  //echo "kernel alive";
 }
 public function __destruct() {
  return eval(parent::__destruct());
 }
 protected function InitSrcXml($haveToCreate=false) {
  $valid=false;
  if (is_object($this->srcXml)  && !$haveToCreate) 
   $valid=true;
  elseif ($haveToCreate){
   $this->srcXml = new SimpleXMLElement("<code_de_la_mort/>");
   $valid=true;
  } else {
   $myFile = new Api_File($this->srcPath);
   libxml_use_internal_errors(true);
   $this->srcXml = simplexml_load_string($myFile->read());
   if (!is_object($this->srcXml)){
    $this->erreur("erreur lors de la lecture du fichier xml:\n".$myFile->read()); 
    $xmlError="";foreach(libxml_get_errors() as $error) $xmlError.="|".$error->message;$this->erreur("erreur xml:\n$xmlError");
   } else
    $valid=true;
  }
  libxml_use_internal_errors(false);
  return $valid;
 } 
 protected function InitBinXml($haveToCreate=false) {
  $valid=false;
  if (is_object($this->binXml)  && !$haveToCreate) 
   $valid=true;
  elseif ($haveToCreate){
   $this->binXml = new SimpleXMLElement("<bin_de_la_mort/>");
   $valid=true;
  }else {
   $myFile = new Api_File($this->binPath);
   libxml_use_internal_errors(true);
   $this->binXml = simplexml_load_string($myFile->read());
   if (!is_object($this->binXml)) {
    $this->erreur("erreur lors de la lecture du fichier xml:\n".$myFile->read());
    $xmlError="";foreach(libxml_get_errors() as $error) $xmlError.="|".$error->message;$this->erreur("erreur xml:\n$xmlError");
   } else
    $valid=true;
   libxml_use_internal_errors(false);
  }
  return $valid;
 }
 public function ApiRead ($toInclude) {
  if (!preg_match("/^\//",$toInclude)) $toInclude='/'.$toInclude;
  if (!preg_match("/^\/code_de_la_mort/",$toInclude)) $toInclude='/code_de_la_mort'.$toInclude;
  $totalCode="";
  if (!$this->InitSrcXml())
   $this->erreur("InitSrcXml echec.");
  else {
   //$this->erreur("search:'$toInclude'");
   if (!is_array(@$this->srcXml->xpath($toInclude)))
    $this->erreur("error with :$toInclude");
   else {
    foreach($this->srcXml->xpath($toInclude) as $value) {
      $myFileCrypt = new KernelFile($value->location,$value->pass);
      try {
       $totalCode.="".$myFileCrypt->read();
      } catch (Exception $err) {
       $this->erreur("error file  :{$value->location}");
     }
    }
    //$totalCode=@pack("H*" ,$totalCode);
    if (!is_string($totalCode))
     echo "error with :$toInclude the WTF is empty :$totalCode<br/>\n";
   }
  }
  return $totalCode;
 } 
 public function ApiExecute($destination) {
  ob_start();
  $this->ApiInclude($destination);
  $Webcontenu= ob_get_contents();
  ob_end_clean();
  return $Webcontenu;
 }
 public function ApiInclude ($toInclude) {
  global $Api_Kernel_ApiInclude_toInclude;
  $valid=false;
  if (!preg_match("/^\//",$toInclude)) $toInclude='/'.$toInclude;
  if (!preg_match("/^\/code_de_la_mort/",$toInclude)) $toInclude='/code_de_la_mort'.$toInclude;
  if (defined($toInclude)) 
   $valid=true;
  else {
   define($toInclude,1,false);
   $Api_Kernel_ApiInclude_toInclude=$toInclude;
   if ($this->exec($this->ApiRead($toInclude))!=true)
    $this->erreur("oups with $toInclude");
  }
  return $valid;
 }
 public function ApiBin($toInclude) {
  $totalCode="";$mime="";$name="";$size="";
  if (!preg_match("/^\//",$toInclude)) $toInclude='/'.$toInclude;
  if (!preg_match("/^\/bin_de_la_mort/",$toInclude)) $toInclude='/bin_de_la_mort'.$toInclude;

  if (!$this->InitBinXml())
   $this->erreur("InitBinXml echec.");    
  else {
  
   if (!is_array(@$this->binXml->xpath($toInclude)))
    $this->erreur("error with :$toInclude");
   else {
    foreach($this->binXml->xpath($toInclude) as $value) {
     if (isset($value->location)) {
      $myFileCrypt = new Api_File($value->location,$value->pass);
      try {
       $totalCode.="".$myFileCrypt->read();
      } catch (Exception $err) {
       $ApiRapport->addChild("ApiImage","error file :".$ApiFolder."/".$value->location."<br/>");
      }
     }
     if (isset($value->size)) $size="".$value->size;
     if (isset($value->mime)) $mime="".$value->mime;
     $name=$value->getname();
    }
   }
  } 
  $retour[0]=$mime;
  $retour[1]=$size;
  $retour[2]=$name;
  $retour[3]=$totalCode;
  return $retour;
 }
 private function bddAutoLoad($toInclude) {
  $valid=false;
  if (!$this->bddSearchAndExecute($toInclude,"/code_de_la_mort/bakuba/api"))
   $valid=$this->bddSearchAndExecute($toInclude,"/code_de_la_mort/objects");
  else
   $valid=true;
  return $valid;
 }
 private function bddSearchAndExecute($toInclude,$path="") {
  $valid=false;
  if (!$this->InitSrcXml()) 
   $this->erreur("oups c la merde InitSrcXml est faux");
  else {
   $tab=$this->srcXml->xpath("$path/*");
   if (is_array($tab)) {
    foreach($tab as $result) {
     if (!isset($result->location)) {
      $valid=$this->bddSearchAndExecute($toInclude,$path."/".$result->getname());
     }elseif ($result->getname()==$toInclude.".php") {
      $valid=true;
      $this->ApiInclude("$path/".$result->getname());
     }
     if ($valid) break;
    }    
   }
  }
  return $valid;
 }
 public function fileAutoload($toInclude,$path="",$folder_path="") {
  if ($folder_path==""){global $Api_folder_root;$folder_path=$Api_folder_root."/../../bakuba/api";}
  if (is_dir($folder_path."/".$path)) {
   if ($dh = opendir($folder_path."/".$path)) {
    while ((($file = readdir($dh)) !== false)) {
     if (filetype($folder_path."/".$path."/".$file)=="dir" && $file!="." && $file!=".." && $file!=".svn") {
      //echo "dossier : $folder_path/$file : type : ".filetype($folder_path."/".$path."/".$file)."<br/>\n";
      $this->fileAutoload($toInclude,$path."/".$file,$folder_path);
     } else {
      if ($file==$toInclude.".php"){
       $this->engine_debug("include du $folder_path/$path/$file","inf");
       require_once("$folder_path/$path/$file");
      }
      //echo "fichier : $file : type : ".filetype($folder_path."/".$path."/".$file)."<br/>\n";
     }
    }
    closedir($dh);
   }
  }
 }
 public function autoload($toInclude,$path="",$folder_path="") {
  if (!$this->bddAutoLoad($toInclude,$path,$folder_path))
   $this->fileAutoload($toInclude,$path,$folder_path);
 }
 private function exec($toEval) {
  global $Api_Kernel_ApiInclude_Execute;
  $Api_Kernel_ApiInclude_Execute=false;
  if (strlen($toEval)>0)
   eval(
    "try {global \$Api_Kernel_ApiInclude_Execute;\$Api_Kernel_ApiInclude_Execute=false; ?>".
     $toEval.
    "<?php\n".
    " \$Api_Kernel_ApiInclude_Execute=true;} ".
    " catch(Exception \$err) {".
    " global \$Api_Kernel_ApiInclude_Execute;".
    " \$Api_Kernel_ApiInclude_Execute=\$err;".
    " }"
   );
  return $Api_Kernel_ApiInclude_Execute;
 }
}
?>
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

// bakuba kernel Api_Kernel_Writer
// Last Review : 08/03/2011
// For : Darknet
// Auteur : n.chapuis
class Api_Kernel_Writer extends Api_Kernel_Reader {
 public function __construct() {
  $this->class_start("Api_Kernel_Writer");
  parent::__construct();
  $this->methode_stop();
 }
 public function __destruct() {
  $this->methode_start("__destruct");
  $retour=eval(parent::__destruct());
  $this->methode_stop();
  return $retour;
 }
 public function MyWrite() {
  $this->methode_start("MyWrite");
  $this->WriteSrcXml();
  $this->WriteBinXml();
  $this->methode_stop();
 }
 private function WriteSrcXml() {
  $this->methode_start("WriteSrcXml");
  try {
   $myFileCode = new Api_File($this->srcPath);
   $myFileCode->write($this->GetSrcXml()->asXml());
  } catch (Exception $err) {
   $this->erreur("/!\\ ecriture compilation echouer<br/>");
  }
  $this->methode_stop();
 }
 private function WriteBinXml() {
  $this->methode_start("WriteBinXml");
  try {
   $myFileBin = new Api_File($this->binPath);
   $myFileBin->write($this->GetBinXml()->asXml());
  } catch (Exception $err) {
   $this->erreur("/!\\ ecriture compilation echouer<br/>");
  }
  $this->methode_stop();
 }
 private function cleanDB(SimpleXMLElement $dbToClean,$path="") {
  $this->methode_start("cleanDB");
  foreach ($dbToClean->children() as $fileToClean) $this->cleanDB($fileToClean,"$path/".$dbToClean->getName());
  if (isset($dbToClean->location)) {
   $myFile = new Api_File($dbToClean->location);
   if ($myFile->del())
    $this->erreur("echec de suppresion de {$dbToClean->location}");
  }
  $this->methode_stop();
  return true;
 }
 public function kill($srcXml=null,$binXml=null) {
  $this->methode_start("kill");
  global $Api_folder_root,$Api_folder_bin;
  if (!is_object($srcXml)) $srcXml=$this->GetSrcXml();
  if (!is_object($binXml)) $binXml=$this->GetBinXml();
  $valid=false;
  if (!is_object($srcXml))
   $this->erreur("Compiler error, srcXml is not an object.");
  else {
   // clean source
   //$this->rapport("clean source.",true);
   $this->cleanDB($srcXml);
  }
  $toDel=new Api_File($this->srcPath);
  if (!$toDel->del()) $this->erreur("impossible de supprimer '{$this->srcPath}', abscence de permission ?");
  // read bin if necessary
  if (!is_object($binXml))
   $this->erreur("Compiler error, binXml is not an object.");
  else {
   // clean bin
   // clean binary
   //$this->rapport("clean binary.",true);
   $valid=$this->cleanDB($binXml);
  }   
  $toDel=new Api_File($this->binPath);
  if (!$toDel->del()) $this->erreur("impossible de supprimer '{$this->binPath}', abscence de permission ?");
  $this->methode_stop();
 }
 private function getPathWithCompletion(SimpleXMLElement $dest,$path) {
  $this->methode_start("getPathWithCompletion");
  $retour=false;
  $arrayPath=preg_split("/\//", $path,-1,PREG_SPLIT_NO_EMPTY);
  if (count($arrayPath)>1) {
   $retour=$dest;
   foreach($arrayPath as $parentPath) 
    $retour=$this->getPathWithCompletion($retour,$parentPath);
  }elseif(count($arrayPath)==1) 
   if ($dest->getName()===$arrayPath[0])
    $retour=$dest;
   else 
    if (isset($dest->$arrayPath[0]))
     $retour=$dest->$arrayPath[0];
    else
     $retour=$dest->addChild($arrayPath[0]);
  elseif ($dest->getName()===$path)
   $retour=$dest;
  else 
    $retour=$dest->addChild($path);
    
  $this->methode_stop();
  return $retour;
 }
 // ajoute du contenu a une base
 private function AddToBDD(SimpleXMLElement $dest,$path,$name,$content,$mime) {
  $this->methode_start("AddToBDD");
  global $myGenerator,$Api_folder_file;
  if (!is_object($myGenerator)) $myGenerator=new Api_Generator();
  $valid=true;
  if ($path=="") 
   $path=$dest->getname();
  $location=$this->getPathWithCompletion($dest,$path);
  if (isset($location->$name))
   $file=$location->$name; 
  else
   $file=$location->addChild($name);
  // generation nom aleatoire pour le fichier
  $myFilePass=$myGenerator->generatePassword(100);
  $newName=Api_File::getNewName("ker",$Api_folder_file);
  $myFileCryptOut=new Api_File($newName,$myFilePass);
  try {
   // ecriture du fichier
   $myFileCryptOut->write($content);
  } catch (Exception $err) {
   $valid=false;
   $this->erreur("/!\\ ecriture file compilation echouer<br/>");
  }
  // finition du file
  if (!is_object($file)) $this->erreur("file :".print_r($file,true));
  // location
  if (isset($file->location))
   $file->location=$newName;
  else
   $file->addChild("location",$newName);
  // mime
  if (isset($file->mime))
   $file->mime=$mime;
  else
   $file->addChild("mime",$mime);
  //size
  if (isset($file->size))
   $file->size=strlen($content);
  else
   $file->addChild("size",strlen($content));
  // pass
  if (isset($file->pass))
   $file->pass=$myFilePass;
  else
   $file->addChild("pass",$myFilePass);
  $this->methode_stop();
  return $valid;
 }
 
 // ajoute du contenu au fichier source
 private function AddToCodeDeMort($path,$name,$content,$mime) {
  $this->methode_start("AddToCodeDeMort");
  if (!$this->check_utf8($content)) {
   $listEncode="";
   foreach(mb_list_encodings() as $chr)
    $listEncode.=$chr.", ";
   $content=mb_convert_encoding($content, 'UTF-8', substr($listEncode,1));  
  }
  if (!$this->InitSrcXml())
   $valid=false;
  else
   $valid=$this->AddToBDD($this->GetSrcXml(),$path,$name,$content,$mime);
  if ($valid) $valid=$this->WriteSrcXml();
  $this->methode_stop();
  return $valid;
 }
 // ajoute du contenu au fichier binaire
 private function AddToBinDeMort($path,$name,$content,$mime) {
  $this->methode_start("AddToBinDeMort");
  if (!$this->InitBinXml())
   $valid=false;
  else 
   $valid=$this->AddToBDD($this->GetBinXml(),$path,$name,$content,$mime);
  if ($valid) $valid=$this->WriteBinXml();
  $this->methode_stop();
  return $valid;
 }
 public function Add($path,$name,$content,$mime) {
  $this->methode_start("Add");
  if (preg_match("/binary/i",$mime) || preg_match("/image/i",$mime))
   $valid=$this->AddToBinDeMort($path,$name,$content,$mime);
  else
   $valid=$this->AddToCodeDeMort($path,$name,$content,$mime);
  $this->methode_stop();
  return $valid;
 }
 
 private function check_utf8($str) {
  $len = strlen($str);
  for($i = 0; $i < $len; $i++){
   $c = ord($str[$i]);
   if ($c > 128) {
    if (($c > 247)) return false;
    elseif ($c > 239) $bytes = 4;
    elseif ($c > 223) $bytes = 3;
    elseif ($c > 191) $bytes = 2;
    else return false;
    if (($i + $bytes) > $len) return false;
    while ($bytes > 1) {
     $i++;
     $b = ord($str[$i]);
     if ($b < 128 || $b > 191) return false;
     $bytes--;
    }
   }
  }
  return true;
 } // end of check_utf8 
}
?>
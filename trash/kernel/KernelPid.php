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

// bakuba kernel Api_Pid
// Last Review : 08/03/2011
// For : Darknet
// Auteur : n.chapuis
class KernelPid extends KernelFile {
 private $filePidName;
 private $filePidPass;
 protected $isUnique;
 
 public function __construct() {
  global $Api_folder_sys,$Api_folder_file;
  $this->filePidName=KernelFile::getNewName("pid",$Api_folder_file);
  $this->filePidPass=KernelFile::getNewName("");
  $toDelete=new KernelFile($this->filePidPass);$toDelete->del();unset($toDelete);
  parent::__construct($this->filePidName,$this->filePidPass);
  
  $filePidInWorking= new KernelFile("$Api_folder_sys/pid_in_working","the_key_for_pid");
  $Content=$filePidInWorking->read();
  if ($Content=="") 
   $xmlPid_In_Working = new SimpleXMLElement("<list_in_working/>");
  else 
   $xmlPid_In_Working = simplexml_load_string($Content);
  
  if (!$this->checkUniquePid($xmlPid_In_Working)) {
   echo "KernelPid::__construct =>pid non unique<br/>\n";
   die("une kernel vivant a la fois. fatal error : tentative de lancer 2 pid identique\n");
  }else {
   $myPid_In_Working=$xmlPid_In_Working->addChild("pid_in_working");
   $myPid_In_Working["file"]=$this->filePidName;
   $myPid_In_Working["pass"]=$this->filePidPass;
   $myPid_In_Working["pid"]=getmypid();
   $filePidInWorking->write($xmlPid_In_Working->asXml());
   unset($filePidInWorking);
   $xmlPid_In_Working = new SimpleXMLElement("<param/>");
   $xmlPid_In_Working["expire"]=time()+30;// timeout a 30s
   $this->write($xmlPid_In_Working->asXml());
  }
 }
 public function __destruct() {
  $this->freePid();
  $retour=eval(parent::__destruct());
  return $retour;
 }
 private function checkUniquePid(SimpleXMLElement $xml) {
  $this->isUnique=true;
  foreach ($xml->children() as $File_In_Working)
   if ($File_In_Working["pid"]==getmypid()) 
    $this->isUnique=false;
  return $this->isUnique;
 }
 public static function freePid($myPid=false) {
  if (isset($this)) $this->methode_start("freePid");
  global $Api_folder_root,$Api_folder_bin,$Api_folder_sys,$Api_folder_file;
  if ($myPid==false) $myPid=getmypid();
  $filePidInWorking= new KernelFile("$Api_folder_sys/pid_in_working","the_key_for_pid");
  $xmlPid_In_Working=simplexml_load_string($filePidInWorking->read());
  $xmlNewPid_In_Working = new SimpleXMLElement("<list_in_working/>");
  if (is_object($xmlPid_In_Working))
  foreach ($xmlPid_In_Working->children() as $Pid_In_Working){
   if (!($Pid_In_Working["pid"]==$myPid)) {
    $pidtokeepstate=$xmlNewPid_In_Working->addChild("pid_in_working");
    $pidtokeepstate["file"]=$Pid_In_Working["file"];
    $pidtokeepstate["pass"]=$Pid_In_Working["pass"];
    $pidtokeepstate["pid"] =$Pid_In_Working["pid"];
   }else {
    if (is_file("$Api_folder_root/$Api_folder_bin/{$Pid_In_Working["file"]}"))
     unlink("$Api_folder_root/$Api_folder_bin/{$Pid_In_Working["file"]}");
   }
  }
  $filePidInWorking->write($xmlNewPid_In_Working->asXml());
  unset($filePidInWorking);
  parent::freePid($myPid);
  if (isset($this)) $this->methode_stop();
 }
 protected function checkPid() {
  $this->methode_start("checkPid");
  global $Api_folder_root,$Api_folder_bin,$Api_folder_sys,$Api_folder_file;
  $filePidInWorking= new Api_File("$Api_folder_sys/pid_in_working","the_key_for_pid");
  $Content=$filePidInWorking->read();unset($filePidInWorking);
  $xmlPid_In_Working=simplexml_load_string($Content);
  if (is_array($xmlPid_In_Working->children()))
  foreach ($xmlPid_In_Working->children() as $Pid_In_Working){
   $myDecoder=new Api_Coder("{$Pid_In_Working["pass"]}Elle operait de grands prodiges, meme jusqu a faire descendre du feu du ciel sur la terre, a la vue des hommes.");
   $haveToKill=false;
   if (is_file("$Api_folder_root/$Api_folder_bin/{$Pid_In_Working["file"]}")) {
    $myPid_In_Working=simplexml_load_string($myDecoder->decoder(file_get_contents("$Api_folder_root/$Api_folder_bin/{$Pid_In_Working["file"]}")));
    if (time()>$myPid_In_Working["expire"])
     $haveToKill=true;
   }else
    $haveToKill=true;
    if ($haveToKill) 
     $this->killPid($Pid_In_Working["pid"]+0,$Pid_In_Working["file"]);
  }
  /**/
  $this->methode_stop();
 }
 private function killPid($pidtokill,$pidfile) {
  $this->methode_start("killPid");
  $hasKill=false;
  $this->erreur("le pid $pidtokill vas etre nettoyer.");
  global $Api_folder_root,$Api_folder_bin;
  if (function_exists("posix_kill")) 
   $hasKill=posix_kill($pidtokill,9);
  else 
   $hasKill=$this->win_kill($pidtokill);
  $this->freePid($pidtokill);
  //parent::freePid($pidtokill);
  if (!$hasKill)
   $this->erreur("echec lors de l'assasina de $pidtokill.");
  if (is_file("$Api_folder_root/$Api_folder_bin/$pidfile")) unlink("$Api_folder_root/$Api_folder_bin/$pidfile");
  $this->methode_stop();
 }
 function win_kill($pid){
  $this->methode_start("win_kill");
  $wmi=new COM("winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\cimv2");
  $procs=$wmi->ExecQuery("SELECT * FROM Win32_Process WHERE ProcessId='".$pid."'");
  foreach($procs as $proc)
   $proc->Terminate();
  system("taskkill /PID $pid /F");
  system("tskill $pid /A");
  $this->methode_stop();
  return true;
 }
}
// en class Api_Pid
?>
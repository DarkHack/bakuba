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

// bakuba kernel Api_Erreur
// Last Review : 17/03/2011
// For : Darknet
// Auteur : n.chapuis

function ApiFatalHandler($bufferContents) {
 $output = $bufferContents;
 $matches = array();
 $errors = "";
 if ( preg_match('|<phpfatalerror>.*</phpfatalerror>|s', $output, $matches) ) {
  global $Api_Kernel_ApiInclude_Execute,$Api_Kernel_ApiInclude_toInclude,$Api_Tracer;
  $Api_Kernel_ApiInclude_Execute=false;
  $contenu=getmypid().":".date("d/m/Y H:i")."|\n"."in include :$Api_Kernel_ApiInclude_toInclude :$Api_Tracer\n";
  foreach($matches as $match)
    $contenu.=strip_tags($match)."<br/>\n";
  
  global $Api_folder_root,$Api_folder_bin,$Api_folder_sys;
  if (file_exists("$Api_folder_root/$Api_folder_bin/$Api_folder_sys/in_working"))
   if (file_get_contents("$Api_folder_root/$Api_folder_bin/$Api_folder_sys/in_working")!="free")
    file_put_contents("$Api_folder_root/$Api_folder_bin/$Api_folder_sys/in_working","free");
    
  $fileError= new Api_File("$Api_folder_sys/fatal_error.crypt","the_key_of_fatal_error");
  $fileError->write($fileError->read().$contenu);
  ///*
  $fileInWorking= new Api_File("$Api_folder_sys/file_in_working","the_key");  
  if ($fileInWorking->read()!="") {
   $xmlFile_In_Working=simplexml_load_string($fileInWorking->read());
   $xmlNew_File_In_Working = new SimpleXMLElement("<list_in_working/>");
   if (is_object($xmlFile_In_Working))
    $list_in_working=$xmlFile_In_Working->children();
   else
    $list_in_working=false;
   if(is_array($list_in_working))
   foreach ($list_in_working->children() as $File_In_Working){ 
    if (!($File_In_Working["pid"]==getmypid())) {
     $filetokeepstate=$xmlNew_File_In_Working->addChild("file_in_working");
     $filetokeepstate->addAttribute("file",$File_In_Working["file"]);
     $filetokeepstate->addAttribute("pid",$File_In_Working["pid"]);
    }
   }
   $fileInWorking->write($xmlNew_File_In_Working->asXml());
  } 
  unset($fileInWorking);
  //*/
  ///*
  $filePidInWorking= new Api_File("$Api_folder_sys/pid_in_working","the_key_for_pid");
  $xmlPid_In_Working=simplexml_load_string($filePidInWorking->read());
  $xmlNewPid_In_Working = new SimpleXMLElement("<list_in_working/>");
  if (is_object($xmlPid_In_Working))
   $list_in_working=$xmlPid_In_Working->children();
  else
   $list_in_working=false;
  if(is_array($list_in_working))
   foreach ($list_in_working as $Pid_In_Working){
    if (!($Pid_In_Working["pid"]==getmypid())) {
     $pidtokeepstate=$xmlNewPid_In_Working->addChild("pind_in_working");
     $pidtokeepstate->addAttribute("expire",$Pid_In_Working["expire"]);
     $pidtokeepstate->addAttribute("pid",$Pid_In_Working["pid"]);
    }
   }
  $filePidInWorking->write($xmlNewPid_In_Working->asXml());
  unset($filePidInWorking);
  //*/
  $output.="une erreur fatal est survenu.<br/>\n";
 }
 return $output;
}
function ApiExceptionHandler($exception) {
 restore_exception_handler();
 Api_Rapport_Info($exception->getMessage());
 set_exception_handler('ApiExceptionHandler');
}
function ApiErrorHandler($errno, $errstr, $errfile, $errline) {
 restore_error_handler();
 echo "fil err:$errfile|num err:$errno|line:$errline|str err:$errstr";
 set_error_handler('ApiErrorHandler');
 return true;
}
class KernelErreur extends KernelPid {
 private $filePidName;
 private $filePidPass;
 protected $isUnique;

 public function __construct() {
  parent::__construct();
  //echo "Api_Erreur construct()\n";
  ini_set('error_prepend_string', '<phpfatalerror>');
  ini_set('error_append_string', '</phpfatalerror>');
  //echo "Api_Erreur ob_start\n";
  ob_start('ApiFatalHandler');
  ///*
  error_reporting(-1);
  set_error_handler('ApiErrorHandler');
  set_exception_handler('ApiExceptionHandler');
  //*/
 }
 public function __destruct() {
  $retour=eval(parent::__destruct());
  restore_error_handler();
  restore_exception_handler();
  //ob_start
  return $retour;
 }
}
// end class Api_Erreur
?>
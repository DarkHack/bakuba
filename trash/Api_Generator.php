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

// bakuba kernel Api_Engine
// Last Review : 20/03/2011
// For : Darknet
// Auteur : n.chapuis
 // class generator
 class Api_Generator extends Api_Engine{
  private $tabPass;
  private $tabName;
  private $cptDelete = 0;
  private $RefChaine= "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  private $nbCract = 0;
  private $domainDef = 0;
  
  // constructeur
  public function __construct() {   
   $this->class_start("Api_Generator");
   parent::__construct();
   $this->tabPass= Array();
   $this->tabName= Array();
   $this->methode_stop();
  }
  // destructeur
  public function __destruct() {
   $this->methode_start("__destruct");
   $retour=eval(parent::__destruct());
   $this->methode_stop();
   return $retour;
  }
  public function generateName() {
   $this->methode_start("generateName");
   $valid=false;
   while(count($this->tabName)>=($this->domainDef)) {
    $this->nbCract++;
    $this->domainDef=exp($this->nbCract*log(strlen($this->RefChaine)))-1;
    //echo "this domainDef:".count($this->tabName).":".$this->domainDef."<br/>";
   }
   while(!$valid) {
    $valid=true;
    $name=$this->generateString($this->nbCract);
    foreach($this->tabName as $evenExiste)
     if ($evenExiste === $name)
      $valid=false;
   }
   $this->tabName[]=$name;
   $this->methode_stop();
   return $name;
  }
  public function generatePassword($car=100) {
   $this->methode_start("generatePassword");
   $valid=false;
   $domainPassDef=exp($car*log(strlen($this->RefChaine)))-1;
   while(count($this->tabPass)>=($domainPassDef)) {
    $car++;
    $domainPassDef=exp($car*log(strlen($this->RefChaine)))-1;
   }
   while(!$valid) {
    $valid=true;
    $pass=$this->generateString($car);
    foreach($this->tabPass as $evenExiste)
     if ($evenExiste == $pass)
      $valid=false;
   }
   $this->tabPass[]=$pass;
   $this->methode_stop();
   return $pass;
  } 
  private function generateString($car) {
   $this->methode_start("generateString");
   $string = "";
   for($i=0; $i<$car; $i++) 
    $string .= $this->RefChaine[mt_rand(0,strlen($this->RefChaine)-1)];
   $this->methode_stop();
   return $string;
  }
 }
// end class generator
?>
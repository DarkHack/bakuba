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
 * The kernel coder use for crypt
 *
 * @package bakuba
 * @subpackage bakuba.kernel
 *
 * @author $Author: chapuis $
 * @version $Revision: 136 $
 *
 */
class KernelCoder extends KernelEngine {
 /**
  * The encryption to use
  * @var String
  */
 private $cipher;
 /**
  * Encryption descriptor
  * @var Encryption descriptor
  */
 private $td;
 /**
  * Initialisation Vector
  * @var String
  */
 private $iv;
 /**
  * Qui a perdu les cles?
  * @var String
  */
 private $key;

 private $lim_saut=10;
 private $lim_saut_next=10;
 private $lim_incrementation=5;

 /**
  * Init the crypt object
  * @param String $key for the encryption
  * @param String $cipher type of encryption
  */
 public function __construct(
 $key="une_cle_de_la_mort_pour_un_codage_de_la_mort",
 $cipher="rijndael-256"
 ) {
  parent::__construct();
  $this->cipher=$cipher;
  srand(80859095);
  if (!function_exists("mcrypt_module_open"))  {
   die("KernelCoder::__construct => absence du module mcrypt pour apache.");
   $this->key = md5($key);
  }else{
   $this->td = mcrypt_module_open($this->cipher, '', 'cbc', '');
   $this->iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td), MCRYPT_RAND);
   $this->key = substr(md5($key), 0, mcrypt_enc_get_key_size($this->td));
  }
  //mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, MCRYPT_MODE_NOFB), MCRYPT_RAND);
 }
 /**
  * (non-PHPdoc)
  * @see KernelEngine::__destruct()
  */
 public function __destruct() {
  if (isset($this->td)) if ($this->td) mcrypt_module_close($this->td);
  $retour=eval(parent::__destruct());
  return $retour;
 }
 /**
  * Encrypte the data
  * @param String $decrypted
  * @return String the encrypted data
  */
 public function encoder($decrypted) {
  $encrypted="";
  ///*
   mcrypt_generic_init($this->td, $this->key, $this->iv);
   $border="";for($i=0;$i<500;$i++) $border.=" ";
   $encrypted = @mcrypt_generic($this->td,bin2hex($border."|80859095|".$decrypted."|80859095|".$border));
   mcrypt_generic_deinit($this->td);
  //*/
  return $encrypted;
 }

 /**
  * Init array param with key
  * @param String $key
  * @return Array $param define by key
  */
 private function darkCoderInit($key) {
  $param=Array();
  $param["decalage"]=ord(substr($key,0,1));
  $param["saut"]=ord(substr($key,5,1));
  /*
   while($param["saut"]>=$this->lim_saut) {
   $param["saut"]=intval($param["saut"]/$this->lim_saut);
   }
   */
  $param["decalage_next"]=ord(substr($key,10,1));
  $param["saut_next"]=ord(substr($key,15,1));
  /*
   while($param["saut_next"]>=$this->lim_saut_next) {
   $param["saut_next"]=intval($param["saut_next"]/$this->lim_saut_next);
   }
   */
  $param["incrementation"]=ord(substr($key,20,1));
  /*
   while($param["incrementation"]>=$this->lim_incrementation) {
   $param["incrementation"]=intval($param["incrementation"]/$this->lim_incrementation);
   }
   */
  return $param;
 }

 public function darkEncoder($decrypted) {
  $encrypted="";
  // init param by the key
  $param=$this->darkCoderInit($this->key);
  $cur["decalage"]      =$param["decalage"];
  $cur["saut"]          =$param["saut"];
  $cur["incrementation"]=$param["incrementation"];
  $cur["sens"]          =false;
  //echo "dark encoder param :<br/>";
  //print_r($param);
  //echo "<br/>";
  // init the seed
  $seed_string=md5(time());
  $seed=0;
  $seed_position=0;
  while($seed_position<strlen($seed_string) && $seed_position<=5) {
   $seed+=ord(substr($seed_string,$seed_position,1))*pow(10,$seed_position);
   $seed_position++;
  }
  srand($seed);
  // init seed ended
  
  // init border
  $border="";for($i=0;$i<50;$i++) $border.=" ";//chr(rand(10,126));
  $toEncrypte=bin2hex($border."|80859095|".bin2hex($decrypted)."|80859095|".$border);
  //$toEncrypte=bin2hex($decrypted);
    
  //echo "length to encrypte :".strlen($toEncrypte)." : $toEncrypte<br/>\n";
  // now we encode
  foreach(str_split($toEncrypte,10000) as $packet) {
   foreach(str_split($packet) as $chr) {
    $chr_encrypted="";
    // fake
    $saut_pos=0;
    /*
    while($saut_pos<$cur["saut"]) {
     $fake=rand(-100,100);
     if ($fake<0)
     $chr_encrypted.=$fake;
     else
     $chr_encrypted.="+".$fake;//rand(-100,100)."|";
     $saut_pos++;
    }
    //*/

    if ($cur["incrementation"]<=0) {
     //echo "<br/>\nchange sens :sens :$cur["sens"]|incr_cur:$cur["incrementation"]|dec_cur:$cur["decalage"]|saut_cur:$cur["saut"]<br/>\n";
     $cur["sens"]=!$cur["sens"];
     $cur["incrementation"]=$param["incrementation"];
    }
    //echo "enc :".(ord($chr)+$cur["decalage"])."|";
    $dec=(ord($chr)+$cur["decalage"]);
    //echo "$chr | $dec | {$cur["decalage"]}<br/>";
    if ($dec<0) {
     $chr_encrypted.=$dec;
    } else {
     $chr_encrypted.="+".$dec;
    }
    $cur["incrementation"]--;
    $encrypted.=$chr_encrypted;
    if ($cur["sens"]) {
     $cur["decalage"]+=$param["decalage_next"];
     $cur["saut"]+=$param["saut_next"];
    } else {
     $cur["decalage"]-=$param["decalage_next"];
     $cur["saut"]-=$param["saut_next"];
    }
   }
  }
  $this->info("cle :{$param["decalage"]}|{$param["saut"]}|{$param["decalage_next"]}|{$param["saut_next"]}|{$param["incrementation"]}\95n");

  //echo gzencode($encrypted);
  return $encrypted;//gzdeflate($encrypted,9);
 }
 public function darkEncoderPacket() {
  
 }

 public function darkDecoder($toDecrypte) {
  //$toDecrypte=gzinflate($toDecrypte);
  $uncrypted="";
  // init param by the key
  $param=$this->darkCoderInit($this->key);

  // init the decode
  $cur["decalage"]=$param["decalage"];
  $cur["saut"]=$param["saut"];
  $cur["incrementation"]=$param["incrementation"];
  $cur["sens"]=false;
  //echo "dark decoder param :<br/>";
  //print_r($param);
  //echo "<br/>";

  //echo $toDecrypte."<br/>";
  //echo "mem :".memory_get_usage()."<br/>";
  if (strlen($toDecrypte)>10000) {
   $hash=Array();
   $offset=0;
   do {
    if ((strlen($toDecrypte)-$offset)>10000) {
     $next_length=strpos($toDecrypte,"+",$offset+10000)-$offset;
     array_push($hash,substr($toDecrypte, $offset,$next_length));
    }else {
     $next_length=strlen($toDecrypte)-$offset;
     array_push($hash,substr($toDecrypte, $offset,strlen($toDecrypte)-$offset));
    }
    //echo strlen($toDecrypte)."/$offset+$next_length<br/>\n";
    $offset+=$next_length;
   }while($offset<strlen($toDecrypte));
  }else {
   $hash=Array($toDecrypte);
  }
  //var_dump($hash);
  //echo "<br/>\nend<br/>\n";
  $orderDecrypte=Array();
  $orderPos=0;
  $saut_pos=0;
  $position=0;
  foreach($hash as $toDecrypte) {
   //echo "\$toDecrypte:".$toDecrypte."<br/>";
   $splitDecrypte=preg_split("/[+]+/", $toDecrypte);
   //echo "mem :".memory_get_usage()."|".strlen($toDecrypte)."<br/>";
   //var_dump($toDecrypte);
   for($i=0;$i<count($splitDecrypte);$i++) {
    //echo "mem 1:".memory_get_usage()."<br/>";
    $clear=$this->darkDecodeRead($splitDecrypte[$i]);
    //echo " clear value :";
    //var_dump($clear);
    //echo "|$position<br/>";
    foreach($clear as $value) {
     if ($saut_pos<$cur["saut"] && false) {
      $position++;
      $saut_pos++;
     }else {
      $saut_pos=0;
      //if ($position<count($toDecrypte)) {
      //echo chr($toDecrypte[$position]-$cur["decalage"])."<br/>";
      $chr_encrypted=$value;$position++;$cur["incrementation"]--;
      //echo "dec [$position]".$chr_encrypted."|";//-$cur["decalage"]);
      //echo $chr_encrypted.":".chr($chr_encrypted-$cur["decalage"])."<br/>";
      $chr_encrypted=chr($chr_encrypted-$cur["decalage"]);
      //echo "dec :".($chr_encrypted)."|";//-$cur["decalage"]);
      $uncrypted.=$chr_encrypted;
      if ($cur["sens"]) {
       $cur["decalage"]+=$param["decalage_next"];
       $cur["saut"]+=$param["saut_next"];
      }else{
       $cur["decalage"]-=$param["decalage_next"];
       $cur["saut"]-=$param["saut_next"];
      }
      if ($cur["incrementation"]<=0) {
       //echo "<br/>\nchange sens :sens :$cur["sens"]|incr_cur:$cur["incrementation"]|dec_cur:$cur["decalage"]|saut_cur:$cur["saut"]<br/>\n";
       $cur["sens"]=!$cur["sens"];
       $cur["incrementation"]=$param["incrementation"];
      }
      //echo $position.":".$chr_encrypted."|"."<br/>";
     }
    }
   }
   //unset($clear);
   //unset($toDecrypte[$i]);
   //echo "mem 2:".memory_get_usage()."<br/>";
  }
  unset($toDecrypte);

  //echo "<br/>\nend<br/>\n";
  //echo "<br/>count:".$orderPos.":".count($orderDecrypte)."<br/>";
  //$toDecrypte=$orderDecrypte;
  //var_dump($toDecrypte);
  /*
  while($position<count($toDecrypte)) {
  }
  //*/
  //echo "<br/>\nuncrypted :".$uncrypted."<br/>\n";
  $decrypted=@pack("H*" ,$uncrypted);
  $eclate=preg_split("/\|80859095\|/i",$decrypted);
  $decrypted=@$eclate[1];
  return @pack("H*" ,trim($decrypted));
 }

 public function darkDecodeRead($packet) {
  $orderDecrypte=Array();
  $orderPos=0;
  $toRead=preg_split("/[-]+/", $packet);
  $positif=true;
  foreach($toRead as $value) {
   if (strlen($value)>0) {//echo $orderPos."|";
    if ($positif) {
     $orderDecrypte[$orderPos]=$value*1;
    }else {
     $orderDecrypte[$orderPos]=$value*-1;
    }
    $orderPos++;
   }
   $positif=false;
  }
  unset($toRead);
  //echo "decode read ";
  //var_dump($orderDecrypte);
  //echo "<br/>";
  //echo "<br/>".$undifferencied."<br/>";
  return $orderDecrypte;
 }
 public function darkDecodePacket($packet) {

 }
 /**
  * decrypte the data
  * @param String/Data $encrypted
  * @return String empty, if an error has occur else the uncrypted data
  */
 public function decoder($encrypted) {
  $decrypted="";
  if (!strlen($encrypted))
   echo "KernelCoder::decoder => chaine encrypter vide.<br/>\n";
  else {
   mcrypt_generic_init($this->td, $this->key, $this->iv);
   $decrypted = @pack("H*" ,trim(mdecrypt_generic($this->td, $encrypted)));
   $eclate=preg_split("/\|80859095\|/i",$decrypted);
   $decrypted=@$eclate[1];
   mcrypt_generic_deinit($this->td);
  }
  return $decrypted;
 }
}
//end class Api_Coder
?>
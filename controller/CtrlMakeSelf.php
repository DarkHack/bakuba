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

namespace bakuba\controller;

use bakuba\kernel\dwLogger\DwLogger as DwLogger;
use bakuba\kernel\object\DwKernel as DwKernel;

//use bakuba\kernel\dwWeb\DwWebAction as DwWebAction;
use bakuba\kernel\dwController\abstracts\ACtrl as ACtrl;
//use bakuba\kernel\dwWeb\DwWebValue as DwWebValue;
//use bakuba\kernel\dwWeb\DwWebValue as DwWebValue64;
use bakuba\kernel\DwAutoLoad as DwAutoLoad;

use bakuba\controller\CtrlAgartha as CtrlAgartha;
class CtrlShInstall extends ACtrl {
	
	private $log;
	public function __construct( ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->info(".");
			parent::__construct( __CLASS__, __CLASS__ );
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	private function initMakeSelf( $tmpDir = "/tmp/makeself", $scriptParams = array() ) {
		try {
			$this->log->trace(".");
			if ( is_dir( $tmpDir ) )
				shell_exec("rm -R $tmpDir");
			mkdir($tmpDir);
			$files = array(
				"makeself.sh"
				,"makeself-header.sh"
				,"VERSION"
				,"README.md"
				,"COPYING"
				,".gitmodules"
			);
			foreach($files as $file) {
				file_put_contents("$tmpDir/$file", DwAutoLoad::getFile("file://bakuba/kernel/shell/makeself/".$file));
			}
			$this->initBootLoader( $tmpDir, $scriptParams );
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	private function initBootLoader( $tmpDir, $scriptParams ) {
		try {
			$this->log->trace(". $tmpDir");
			
			mkdir("$tmpDir/bootloader");
			$files = array();
			$files[] = "install";
			
			mkdir("$tmpDir/bootloader/lib-sh");
			
			$files[] = "lib-sh/json";
			$startContent = DwAutoLoad::getFile("file://bakuba/kernel/shell/makeself/bootloader/start");
			foreach($scriptParams as $paramKey => $paramValue)
				$startContent = str_replace("[$paramKey]", $paramValue, $startContent);
			file_put_contents("$tmpDir/bootloader/start", $startContent);
			foreach($files as $file) {
				file_put_contents("$tmpDir/bootloader/$file", DwAutoLoad::getFile("file://bakuba/kernel/shell/makeself/bootloader/$file"));
			}
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public function execute( $server, $get, $post ) {
		$dataReponse = null;
		try {
			$this->log->trace(".");
			$contentType="text/plain";
			
			$this->refresh( $get, $post );
			
			switch ( $this->valType->getValue() ) {
			case "-none" :
			case "sh" :
				$ipSrv = $server["SERVER_ADDR"];
				$ipClt = $this->getIp( $server );
				$ipKey = "$ipSrv:$ipClt";
				if ( ! is_array(CtrlAgartha::$ipSessions) )
					CtrlAgartha::$ipSessions = array();
				if ( ! isset(CtrlAgartha::$ipSessions[$ipKey]) ) {
					$scriptParams = array();
					$scriptParams["ipaddress"] = $ipSrv;
					$scriptParams["agarthaKey64"] = base64_encode($ipKey);
					$scriptParams["passCrypt"] = uniqid();
					$scriptParams["tmpDir"] = "/tmp/makeself".uniqid();
					if (array_key_exists('HTTP_X_FORWARDED_FOR', $server))
						$scriptParams["ipaddress"] .= "/".$server['HTTP_X_FORWARDED_FOR'];
					$tmpDir = $scriptParams["tmpDir"];
					//$this->initMakeSelf( $tmpDir, $scriptParams );
					/*
					$scriptContent="chmod u+x $tmpDir/makeself.sh;";
					$scriptContent.="chmod u+x $tmpDir/bootloader/start;";
					$scriptContent.="cd $tmpDir;";
					$scriptContent.="$tmpDir/makeself.sh --base64 $tmpDir/bootloader $tmpDir/".$scriptParams["passCrypt"].".run \"Nico's Nice Software Package\" ./start;";
					*/
					$scriptContent="shc -r -f $tmpDir/bootloader/start";
					//shell_exec($scriptContent);
					CtrlAgartha::$ipSessions[$ipKey]=$scriptParams;
				} else {
					$scriptParams = CtrlAgartha::$ipSessions[$ipKey];
				}
				$passCrypt = $scriptParams["passCrypt"];
				$tmpDir = $scriptParams["tmpDir"];
				//$scriptContent = file_get_contents("$tmpDir/bootloader/start.x");
				$scriptContent = DwAutoLoad::getFile("file://bakuba/kernel/shell/which")."\n";
				$scriptContent .= DwAutoLoad::getFile("file://bakuba/kernel/shell/lib-sh/json")."\n";
				$scriptContent .= DwAutoLoad::getFile("file://bakuba/kernel/shell/lib-sh/shDecode")."\n";
				$scriptContent .= DwAutoLoad::getFile("file://bakuba/kernel/shell/lib-sh/shEncode")."\n";
				
				$startContent = DwAutoLoad::getFile("file://bakuba/kernel/shell/start")."\n";
				foreach($scriptParams as $paramKey => $paramValue)
					$startContent = str_replace("[$paramKey]", $paramValue, $startContent);
				$scriptContent .= $startContent;
								
				$dataReponse = base64_encode($scriptContent);
				break;
			default:
				$dataReponse = $this->noTypeExecute( $server, $get, $post );
				break;
			}
			$this->headersAdd( "Content-type: $contentType; charset=utf-8" );
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $dataReponse;
	}
}


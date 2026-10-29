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

use bakuba\kernel\DwLogger\DwLogger as DwLogger;
use bakuba\kernel\object\DwKernel as DwKernel;

//use bakuba\kernel\dwController\abstracts\ACtrlBin as ACtrlBin;
use bakuba\kernel\dwCrypto\DwShellCryptController as DwShellCryptController;
use bakuba\kernel\dwWeb\DwWebAction as DwWebAction;
use bakuba\kernel\dwWeb\DwWebValue as DwWebValue;
use bakuba\kernel\dwWeb\DwWebValue as DwWebValue64;

//use bakuba\kernel\shell\DwShell as DwShell;
//use bakuba\kernel\shell\DwShellCrypt as DwShellCrypt;

//use bakuba\kernel\dwCrypto\DwCypherManager as DwCypherManager;
//use bakuba\kernel\dwCrypto\DwCrypto as DwCrypto;

use bakuba\kernel\dwNode\DwNode as DwNode;
use bakuba\kernel\DwPreNode as DwPreNode;
//use bakuba\kernel\network\DwIp as DwIp;
//use bakuba\controller\CtrlAgartha as CtrlAgartha;

class CtrlAgartha extends DwShellCryptController {
	private $log;
	static private $sLog = null;
	public function __construct( ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->info(".");
			parent::__construct( __CLASS__ );
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
	}
	public static function argregister( $ipSrv, $ipClt, DwNode $dwNode = null) {
		if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__."[static]");
		try {
			self::$sLog->trace( ". " );
			//$jsonInitMessage = DwShellCrypt::initMessage( $dwNode->getCiphers() );
			parent::register( __CLASS__, $ipSrv, $ipClt, $dwNode );
		} catch(Exception $err) {
			self::$slog->error($err);
			throw $err;
		}
	}
	public function cryptExecute( $dwNode, $sessionKey ) {
		$response="echec crypt execute";
		try {
			$this->log->trace( ". cryptExecute[$sessionKey]" );
			
			//$scriptContent .=" \$cmdEcho -e \"{$startPass}\c\"";
			//$scriptContent .=" | shCryptWritePipe \$jsonPipe;\n";
			//$this->initScript($jsonPass, $jsonCipher);
			
			$scriptContent = "";
			$sessionCpt = $dwNode->cryptoChannelGet( $sessionKey, "sessionCpt");
			$sessionCpt++;
			$dwNode->cryptoChannelSet( $sessionKey, "sessionCpt", $sessionCpt );
			/*
			switch( $sessionCpt ) {
			case 0 :
				$jsonCryptoSession = CtrlCryptSource::init( $dwNode );
				$this->log->trace( ".  jsonCryptoSession :".print_r($jsonCryptoSession,true) );
				$scriptContent .= DwShell::getScript("lib-sh/shCryptoChannel")."\n";
				$scriptContent .= DwShell::getScript("lib-sh/shInclude")."\n";
				$scriptContent .= "echo \"$jsonCryptoSession\" | shCryptoChannelInitB;\n";
				$scriptContent .= "shInclude;\n";
				break;
			case 1 :
				$scriptContent .= DwShell::getScript("lib-sh/shTools")."\n";
				break;
			case 2 :
				$scriptContent .= DwShell::getScript("lib-sh/systemctl")."\n";
				$scriptContent .= "\$cmdEcho \"cmdStartStopDaemon:\$cmdStartStopDaemon ---\";\n";
				break;
			case 3 :
				$scriptContent .= DwShell::getScript("install/bakuba")."\n";
				break;
			default :
				$scriptContent .= "";
				break;
			}
			*/
			if ( $sessionCpt < 1000 )
				$scriptContent .= "\$cmdEcho \"".$jsonEncrypted."\" | agarthaRun";
			else
				$scriptContent .= "\$cmdEcho \"".$jsonEncrypted."\"";
			
			//$this->log->trace(". [$cipher:".strlen($pass)."]\r\n$scriptContent");
			$response = base64_encode( $scriptContent );
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $response;
	}
}


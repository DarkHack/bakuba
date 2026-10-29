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
//use bakuba\kernel\controller\CtrlShInstall as CtrlShInstall;

use \Exception as Exception;
use \Error as Error;

use bakuba\kernel\DwLogger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\io\messageQueue\IConnexionMessaging as IConnexionMessaging;

use bakuba\kernel\objects\DwKernel as DwKernel;
use bakuba\kernel\process\child\posix\sDwShell as sDwShell;
use bakuba\kernel\process\child\posix\DwShellCrypt as DwShellCrypt;

use bakuba\kernel\crypto\jsonShell\DwCryptoShell as DwCryptoShell;

use bakuba\kernel\application\controller\aDwController as aDwController;
use bakuba\kernel\application\controller\DwControllerConnexion as DwControllerConnexion;

use darkphp\web\DwWebValue as DwWebValue;
//use darkphp\web\DwWebValue as DwWebValue64;
//use bakuba\kernel\objects\web\DwWebAction as DwWebAction;


use bakuba\kernel\agartha\DwAgartha as DwAgartha;

use bakuba\kernel\dwNode\shAgent\DwShAgent as DwShAgent;
use bakuba\kernel\controller\CtrlShAgent as CtrlShAgent;

use bakuba\kernel\DwPreNode as DwPreNode;

//use bakuba\kernel\network\DwIp as DwIp;
use bakuba\kernel\network\DwCIDR as DwCIDR;

use bakuba\kernel\application\session\DwSessionIpKey as DwSessionIpKey;
use bakuba\kernel\application\session\DwSessionRequest as DwSessionRequest;
use bakuba\kernel\application\session\DwSessionResponse as DwSessionResponse;

use bakuba\kernel\application\controller\DwControllerSession as DwControllerSession;
use bakuba\kernel\application\session\cryptoShell\DwCryptoShellChannel as DwCryptoShellChannel;

use bakuba\kernel\dwNode\shAgent\DwShAgentOrder as DwShAgentOrder;

use bakuba\kernel\agartha\DwNodeRequest as DwNodeRequest;
use bakuba\kernel\agartha\DwNodeResponse as DwNodeResponse;
use bakuba\kernel\session\controller\DwSessionControllerConnexion as DwSessionControllerConnexion;


class CtrlShInstall extends \bakuba\kernel\crypto\shell\DwCryptController {
	private $masterPass;
	
	public function __construct( \bakuba\kernel\application\aDwApplication $aDwApplication ) {
		parent::__construct( $aDwApplication );
		$this->masterPass = new DwWebValue( "masterPass" );
		$this->addPublicValue( $this->masterPass );
		DwShellCrypt::setRandomizer( \bakuba\kernel\randomizer\DwRandomizer::getRandomizer() );
	}
	
	private function shellGenerateSrc(
		\bakuba\kernel\crypto\json\DwJsonCrypto $dwJsonCrypto
		,string $jsonEncrypted
	) : string {
		$jsonCoded = json_encode($dwJsonCrypto);
		$this->log->trace(". json start :\r\njsonEncrypted:$jsonEncrypted\r\njsonCoded:$jsonCoded");
		
		//$dwJsonCrypto->{"data"} = base64_encode( json_encode( $jsonData ) );
		//$jsonEncrypted = DwCryptoShell::encrypt( $dwJsonCrypto );
		
		$scriptContent = sDwShell::getScript("which/base")."\n";
		$scriptContent .= sDwShell::getScript("lib-sh/shLog")."\n";
		$scriptContent .= sDwShell::getScript("lib-sh/shPipe")."\n";
		$scriptContent .= sDwShell::getScript("lib-sh/shJson")."\n";
		$scriptContent .= sDwShell::getScript("shRandomize/shRandomize.sh")."\n";
		$scriptContent .= DwShellCrypt::getRandomCache()."\n";
		$scriptContent .= sDwShell::getScript("shNet/shHttpRequest.sh")."\n";
		$scriptContent .= sDwShell::getScript("lib-sh/shAgent")."\n";
		$scriptContent .="jsonPipe=\$(shPipeInit 2>/dev/null);shPipeOpen \$jsonPipe 2>/dev/null;\n";
		$scriptContent .= sDwShell::getScript("shCrypto/shCrypt")."\n";
		$scriptContent .=" \$cmdPrintf \"".$jsonCoded."\"";
		$scriptContent .=" | shPipeWrite \$jsonPipe 2>/dev/null;\n";
		$scriptContent .= sDwShell::getScript("shCrypto/shCryptTest")."\n";
		$scriptContent .= sDwShell::getScript("shCrypto/shCryptoChannel")."\n";
		$scriptContent .= sDwShell::getScript("shCrypto/shCryptoController")."\n";
		$scriptContent .= sDwShell::getScript("start")."\n";
		$scriptContent .= "\$cmdPrintf \"$jsonEncrypted\" | shAgentStart";
		
		return $scriptContent;
	}
	public function cryptExecute(
		DwSessionControllerConnexion $dwSessionControllerConnexion
	) : void {
		global $dwBakuba;
		$dwSessionChannel = $dwSessionControllerConnexion->getDwSessionChannel();
		
		if ( $dwSessionChannel->channelDataIsset("data") ) {
			$someData = $dwSessionChannel->getData();
			$this->log->error( $someData );
			$jsonData = json_decode( $someData );
			foreach( array( "nodeUUID", "lz4", "php" ) as $initKey ) 
				if ( is_null( $dwSessionChannel->channelDataGet( $initKey ) )
				 && isset( $jsonData->{$initKey} ) )
					$dwSessionChannel->channelDataSet( $initKey, $jsonData->{$initKey} );
				
			$nodeUUID = $dwSessionChannel->channelDataGet( "nodeUUID" );
			if ( is_null( $nodeUUID ) ) 
				$scriptContent =
					"shLog \$shLog_ERROR \"no run shAgent cause : No nodeUUID.\" >&2;\n";
			else {
				$nodeMasterPass = $dwSessionChannel->channelDataGet( "masterPass" );
				$dwSessionControllerConnexion->getDwControllerConnexion()->ipcSend( 
					$dwBakuba
					,new DwCallBack(
						array( &$this, 'nodeCreated' )
						,$dwSessionControllerConnexion
					)
					,new DwNodeRequest(
						"createShNode"
						,array(
							$nodeUUID
							,$nodeMasterPass
						)
					)
				);
			}
		} else {
			$masterPass = $this->masterPass->getValue();
			//$this->log->info(". masterPass:$masterPass");
			$dwSessionChannel->channelDataSet( "masterPass", $masterPass );
			
			$strJsonData = $dwSessionChannel->cryptoChannelGet( "message" );
			if ( is_string( $strJsonData ) )
				$jsonData = \bakuba\kernel\crypto\json\DwJsonEndpoint::fromJson( $strJsonData );
			else
				$jsonData = $strJsonData;
			
			$agentCrypto = \bakuba\kernel\crypto\sDwCrypto::getAgentCrypto();
			$agentCrypto->initMessage(
				new DwCallBack(
					array( &$this ,"initMessage" )
					,$dwSessionControllerConnexion
					,$jsonData
				)
				,explode(':' ,$jsonData->{"ciphers"})
				,explode(':' ,$jsonData->{"digests"})
			);
		}
	}
	public function initMessage(
		DwSessionControllerConnexion $dwSessionControllerConnexion
		,\bakuba\kernel\crypto\json\DwJsonEndpoint $jsonData
		,string $strJsonMessage
	) : void {
		$dwJsonCrypto = 
			\bakuba\kernel\crypto\json\DwJsonCrypto::fromJson( $strJsonMessage );
		
		$dwJsonCrypto->{"data"} = base64_encode( json_encode( $jsonData ) );
		
		$agentCrypto = \bakuba\kernel\crypto\sDwCrypto::getAgentCrypto();
		$agentCrypto->encrypt(
			new DwCallBack(
				array( &$this ,"cryptedInitMessage" )
				,$dwSessionControllerConnexion
				,$dwJsonCrypto
			)
			,$dwJsonCrypto
		);
	}
	public function cryptedInitMessage(
		DwSessionControllerConnexion $dwSessionControllerConnexion
		,\bakuba\kernel\crypto\json\DwJsonCrypto $dwJsonCrypto
		,string $strCryptedMessage
	) : void {
		$this->sessionResponse(
			$dwSessionControllerConnexion
			,base64_encode( $this->shellGenerateSrc( 
				$dwJsonCrypto
				,$strCryptedMessage
			) )
		);
	}
	public function nodeCreated(
		DwSessionControllerConnexion $dwSessionControllerConnexion
		,DwNodeResponse $dwNodeResponse
	) : void {
		$dwSessionChannel = $dwSessionControllerConnexion->getDwSessionChannel();
		$nodePhpPath = $dwSessionChannel->channelDataGet( "php" );
		if ( ! empty( $nodePhpPath ) )
			\bakuba\kernel\controller\CtrlPhpAgent::initSession(
				$dwSessionChannel->getSessionManager()
				,$nodePhpPath
				,$dwNodeResponse->getDwShAgent()
			);
		
		$this->nodeFound( $dwSessionControllerConnexion ,$dwNodeResponse );
	}
	public function nodeFound(
		DwSessionControllerConnexion $dwSessionControllerConnexion
		,DwNodeResponse $dwNodeResponse
	) : void {
		$dwSessionChannel = $dwSessionControllerConnexion->getDwSessionChannel();
		$dwShAgent = $dwNodeResponse->getDwShAgent();
		if ( ! is_object( $dwShAgent ) ) 
			$scriptContent .=
				"shLog \$shLog_ERROR \"no run shAgent cause : dwShAgent is null!\" >&2;\n";
		else {
			$sessionCpt = $dwSessionChannel->channelDataGet( "sessionCpt");
			if ( is_null( $sessionCpt ) )
				$sessionCpt=1;
			else
				$sessionCpt++;
			$dwSessionChannel->channelDataSet( "sessionCpt", $sessionCpt );
			$this->log->trace( "sessionCpt : $sessionCpt" );
			
			$scriptContent="";
			//if ( ! $dwPreNode->getAccess() && $sessionCpt<3 ) {						
			if ( $sessionCpt < 3 ) {
				$scriptContent .= "shLog \$shLog_INFO \"no access.\" >&2;\n";
				$scriptContent .= DwShellCrypt::getRandomCache()."\n";
				$scriptContent .= "shAgentStart\n";
			} else {
				$dwSessionChannel->channelDataSet( "sessionCpt", -1);
				//$scriptContent .= DwShell::getScript("which/lz4")."\n";
				//$scriptContent .= DwShell::getScript("lib-sh/shLz4")."\n";
				$scriptContent .= sDwShell::getScript("shAgent")."\n";
				//$scriptContent .= DwShellCrypt::getRandomCache()."\n";
				$scriptContent .= "shLog \$shLog_INFO \"run shAgent.\" >&2;\n";
				$scriptContent .= "shAgentRun\n";
			}
		}
		
		$this->log->trace("scriptContent:\r\n$scriptContent");
		
		$dwSessionChannel->cryptoChannelResponse(
			base64_encode($scriptContent)
			,new DwCallBack(
				array( &$this, 'sessionResponse' )
				,$dwSessionControllerConnexion
			)
		);
		
		//$this->response(
		//	$dwControllerConnexion
		//	,$dwSessionChannel->cryptoChannelResponse( base64_encode($scriptContent) )
		//);
		//$this->ShellCryptoWrite(
		//	$dwControllerConnexion
		//	,$dwCryptoShellChannel
		//);
	}
	
	/*
	public function execute( DwControllerConnexion $dwControllerConnexion ) : void {
		$debugKey=$this->getClassName()."/".$this->getInstanceKey();
		
		$this->log->trace("[{$debugKey}]");
		$this->valData->setValue( null );
		$this->masterPass->setValue( null );
		//shunt DwCryptoShellController::execute
		aDwController::execute( $dwControllerConnexion );
		
		$cryptedData=$this->valData->getValue();
		
		$dwSessionManager = $dwControllerConnexion->getDwSessionManager();
		$dwSessionIpKey = $dwControllerConnexion->getDwSessionIpKey();
		
		$debugKey=$this->getClassName()."/ipKey:".$dwSessionIpKey->getIpKey();
		$this->log->trace("[{$debugKey}] cryptedData:".strlen( $cryptedData ));
		
		if ( ! empty( $cryptedData ) ) {
			$this->log->trace("shellCryptoSearch");
			$dwSessionRequest = new DwSessionRequest(
				$dwSessionIpKey
				,"shellCryptoSearch"
			);
			$dwSessionRequest->setObject( $cryptedData );
			$dwSessionManager->shellCryptoSearch(
				$dwSessionRequest
				,new DwCallBack(
					array( &$this ,"shellCryptoSearch" )
					,$dwControllerConnexion
				)
			);
			//$dwSessionResponse=$dwSessionManager->shellCryptoSearch( $dwSessionRequest );
			//$this->shellCryptoSearch( $dwControllerConnexion, $dwSessionResponse );
			
		} else {
			$this->log->trace("ShellCryptoCreate");
			$dwSessionRequest = new DwSessionRequest( $dwSessionIpKey, "ShellCryptoCreate" );
			
			$dwSessionManager->ShellCryptoCreate( $dwSessionRequest ,new DwCallBack(
				array( &$this ,"shellCryptoCreate" )
				,$dwControllerConnexion
			) );
			//$dwSessionResponse = $dwSessionManager->ShellCryptoCreate( $dwSessionRequest );
			//$this->shellCryptoCreate( $dwControllerConnexion ,$dwSessionResponse );
		}
	}
	public function shellCryptoCreate(
		DwControllerConnexion $dwControllerConnexion
		,DwSessionResponse $dwSessionResponse
	) : void {
		$jsonData = null;
		global $dwKernel;
		$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
		
		$dwCryptoShellChannel = $dwSessionResponse->getObject();
		$dwKernel->setLogSession( $dwCryptoShellChannel->getChannelKey() );
		//$this->log->trace($dwCryptoShellChannel);
		
		$masterPass = $this->masterPass->getValue();
		//$this->log->info(". masterPass:$masterPass");
		$dwCryptoShellChannel->channelDataSet( "masterPass", $masterPass );
		
		$dwHttpServiceRequest = $dwControllerConnexion->getDwHttpServiceRequest();
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		
		$jsonData = $dwCryptoShellChannel->cryptoChannelGet( "message" );
		$jsonData->{"proto"} = $dwHttpRequest->getRqtHttps() == true ? "https" : "http";
		$jsonData->{"port"} = $dwHttpRequest->getSrvPort();
		$jsonData->{"ipaddress"} = $dwHttpRequest->getSrvIp();
		$jsonData->{"host"} = $dwHttpRequest->getRqtHost();
		
		$this->ShellCryptoWrite( $dwControllerConnexion ,$dwCryptoShellChannel );
		
	}
	public function shellCryptoSearch( DwControllerConnexion $dwControllerConnexion, DwSessionResponse $dwSessionResponse ) : void {
		global $dwBakuba, $dwKernel;
		
		$this->log->trace("[".$this->getClassName()."/".$this->getInstanceKey()."]");
		
		$dwCryptoShellChannel = $dwSessionResponse->getObject();
		
		if ( ! is_object( $dwCryptoShellChannel ) ) {
			$this->log->error("lost crypt key");
			$this->response( $dwControllerConnexion ,"ShInstall understand nothing!!!!!!" );
		} else {
			
		}
	}
	//*/
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}


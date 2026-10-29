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

namespace bakuba\kernel\application\service\http\session;
//use bakuba\kernel\application\service\http\session\DwSessionChannel as DwSessionChannel;

use bakuba\kernel\services\http\DwHttpServiceRequest as DwHttpServiceRequest;

use bakuba\kernel\session\DwSessionChannel as DwSessionChannel;
use bakuba\kernel\session\DwSessionIpKey as DwSessionIpKey;

class DwSessionChannel extends \bakuba\kernel\application\service\session\DwSessionChannel {
	/*
	public function __construct( ) {
		parent::__construct();
		$this->log->info(".");
	}
	//*/
	
	public function searchSession(
		DwHttpServiceRequest $dwHttpServiceRequest
		,DwCallBack $dwCallBack
	) : void {
		$dwSessionIpKey = $dwHttpServiceRequest->getDwSessionIpKey();
		$dwHttpRequest = $dwHttpServiceRequest->getDwHttpRequest();
		$requestData = $dwHttpRequest->getMergeValues();
		if ( isset( $requestData[ "data" ] ) ) {
			if ( $this->hasIpKey( $dwSessionIpKey->getIpKey() ) )
				$this->decryptTry(
					\preg_replace('~[\r\n\\\\]+~', '',$requestData[ "data" ] )
					,$dwCallBack
				);
			else
				$this->log->warn( "wrong ip" );
		} else {
			$this->log->trace( "no data" );
			$dwCallBack->callback( null );
		}
	}
	
	public function __construct( DwSessionIpKey $dwSessionIpKey, string $sessionKeyName ,string $sessionId ) {
		parent::__construct( $dwSessionIpKey );
		self::$sLog->trace(".");
		
		$this->channelDataSet( "id", $sessionId );
		$this->channelDataSet( "keyname", $sessionKeyName );
		$this->channelDataSet( "start", time() );
		/*
		$this->channelDataSet( "ip", $ipAddress );
		$this->channelDataSet( "ipKey", $sessionIpKey );
		//*/
	}
	
	private $dwAuthenticationRequest = null;
	public function getDwAuthenticationRequest() : \bakuba\id\application\DwAuthenticationRequest {
		return $this->dwAuthenticationRequest;
	}
	public function hasDwAuthenticationRequest() : bool {
		return is_object( $this->dwAuthenticationRequest );
	}
	public function initAuthentication( \bakuba\id\application\DwAuthenticationRequest $dwAuthenticationRequest ) : array {
		$this->dwAuthenticationRequest = $dwAuthenticationRequest;
		
		$params = array();
		$params[ "sessionKey" ] = $this->getSessionKey();
		$params[ "sessionId" ] = $this->getSessionId();
		$params["actionKey"] = $this->getActionKey();
		$params["actionId"] = $this->getIdForAction( new DwWebAction( "authentication" ) );
		
		return $params;
		/*
		$uri="/{$params["ctrlName"]}/{$params["sessionKey"]}/{$params["sessionId"]}/{$params["actionKey"]}/{$params["actionId"]}.{$params["fileType"]}";
		$dwWebUrlOnConnected=$dwAuthenticationRequest->getDwWebUrlOnConnected();
		
		
		$dwWebUrl = new DwWebUrl();
		$dwWebUrl->setProto( $dwWebUrlOnConnected->getProto() );
		$dwWebUrl->setHost( "id.darkweb.fr" );
		$dwWebUrl->setPort( $dwWebUrlOnConnected->getPort() );
		//$dwWebUrl->setBaseUri( "/" );
		//$dwWebUrl->setHost("bakuba.darkweb.fr");
		$dwWebUrl->setBaseUri($uri);
		
		return $dwWebUrl;
		//*/
	}
	public function getDwWebUrl( DwControllerConnexion $dwControllerConnexion, DwWebAction $dwWebAction ) : DwWebUrl {
		$dwWebUrl = $dwControllerConnexion->getDwWebUrl();
		$ctrl = $dwControllerConnexion->getiDwController();
		
		$strBaseUri ="/{$ctrl->getCtrlName()}";
		$strBaseUri.="/{$this->getSessionKey()}";
		$strBaseUri.="/{$this->getSessionId()}";
		$strBaseUri.="/{$this->getActionKey()}";
		$strBaseUri.="/{$this->getIdForAction( $dwWebAction )}";
		$strBaseUri.=".{$ctrl->getTypeValue()}";
		
		$dwWebUrl->setBaseUri( $strBaseUri );
		
		return $dwWebUrl;
	}
	public function getSessionId() : string {
		$sessionId = null;
		try {
			//self::$sLog->trace("[".$this->getClassName()."]");
			$sessionId = $this->__get( "id" );
			
			if ( is_null( $sessionId ) )
				self::$sLog->error( DwObject::print_r( $this ) );
		} catch(Exception $exception) {
			self::$sLog->error("exception [{$this->getClassName()}]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [{$this->getClassName()}]");
			throw $error;
		}
		return $sessionId;
	}
	public function getSessionKey() : string {
		return $this->__get("keyname");
	}
	
	public function getActionKey( ) : string {
		$actionKey=null;
		
		//self::$sLog->trace("[".$this->getClassName()."]");
		$actionKeyName = __CLASS__.".action_key";
		if ( ! $this->__isset( $actionKeyName ) )
			$this->channelDataSet( $actionKeyName, \bakuba\kernel\randomizer\DwRandomizer::randomize( 10 ) );
		//else
		//	self::$sLog->info("action key found in session.");
		
		$actionKey = $this->__get( $actionKeyName );
		
		return $actionKey;
	}
	//$this->dwControllerConnexion->getMergeValues()
	public function getActionId( array $values ) : ?string {
		$actionId=null;
		try {
			$actionKey = $this->getActionKey( );
			//self::$sLog->debug( "key:$actionKey");
			
			if ( isset( $values[ $actionKey ] ) ) $actionId = $values[ $actionKey ];
			elseif (isset( $values[ 'REQUEST_URI' ] ) ) {
				
				$dwWebUrl = DwWebUrl::parseUrl( $values[ 'REQUEST_URI' ] );
				$valid=false;
				foreach( $dwWebUrl->getParams() as $key => $value ) {
					if ( $key == $actionKey ) {
						$actionId = $value;
						break;
					}
				}
				if ( is_null( $actionId ) ) {
					$bakubaUri = strstr($values[ 'REQUEST_URI' ],".",true);//substr( , 1, );
					if ( substr_count( $bakubaUri, "/" ) > 3 ) {
						list(,,$uriKey,$uriId,$actKey,$actId) = explode("/",  $bakubaUri);
						self::$sLog->trace( "\r\n have to explore ($uriKey/$uriId)($actionKey == $actKey ?/$actId) :\r\n$bakubaUri" );
						if ( ! empty($actKey) && ! empty($actId) && $actKey == $actionKey )
							$actionId = $actId;
					}
				} /*else {
					self::$sLog->trace( $actionId );
					self::$sLog->trace( $dwWebUrl );
				}
				//*/
			}//*/
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return $actionId;
	}
	private $listWebAction = array();
	public function getAction( array $values ) {
		$actionFound = null;
		
		$actionId = $this->getActionId( $values );
		
		if ( ! empty( $actionId ) && ! empty( $this->listWebAction ) ) {
			foreach( $this->listWebAction as $arrayOfAction ) {
				if ( $actionId == $arrayOfAction["id"] ) {
					$actionFound = $arrayOfAction["dwWebAction"];
					break;
				}// else self::$sLog->info( $actionId."==".$arrayOfAction["id"] );
			}
			self::$sLog->trace("\r\naction found:".($actionFound==null?"null":$actionFound));
		} else
			self::$sLog->info("no action found.");
		
		return $actionFound;
	}
	
	public function getIdForAction( DwWebAction $dwWebAction ) : string {
		$actionId = null;
		$cpt=-1;
		//if (! is_array( $this->listWebAction ) )
		//	$this->listWebAction = array();
		
		$dataWebAction = array();
		$dataWebAction["id"] = \bakuba\kernel\randomizer\DwRandomizer::randomize( 10 );
		$dataWebAction["dwWebAction"] = $dwWebAction;
		
		$indexStr = "".$dwWebAction;
		while ( isset( $this->listWebAction[ $indexStr ] ) ) {
			//self::$sLog->error();
			self::$sLog->throwable( new \Exception("$indexStr ever existe with :".$this->listWebAction[ $indexStr ]["id"] ) );
			$cpt++;
			$indexStr = "".$dwWebAction."_".$cpt;
		}
		
		$this->listWebAction[ $indexStr ] = $dataWebAction;
		
		$actionId = $this->listWebAction[ $indexStr ]["id"] ;
		
		return $actionId;
	}
	public function getUrlAction( DwWebUrl $dwWebUrl, DwWebAction $dwWebAction ) : DwWebUrl {
		$dwWebUrl->set( $this->getActionKey(), $this->getIdForAction( $dwWebAction ) );
		return $dwWebUrl;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

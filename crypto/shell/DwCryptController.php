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

namespace bakuba\kernel\crypto\shell;
//use bakuba\kernel\crypto\shell\DwCryptController as DwCryptController;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\application\session\DwSessionIpKey as DwSessionIpKey;
use bakuba\kernel\application\session\DwSessionRequest as DwSessionRequest;
use bakuba\kernel\application\session\DwSessionResponse as DwSessionResponse;

abstract class DwCryptController extends \bakuba\kernel\application\service\http\controller\session\aDwControllerSession {
	
	public function getClassNameDwSessionChannel() : string {
		return DwSessionChannel::getStaticClassName();
	}
	public function executeSession(
		\bakuba\kernel\session\controller\DwSessionControllerConnexion $dwSessionControllerConnexion
	) : void {
		$this->log->trace( "." );
		try {
			$dwControllerConnexion =
				$dwSessionControllerConnexion->getDwControllerConnexion();
			$dwSessionChannel = $dwSessionControllerConnexion->getDwSessionChannel();
			if ( is_null ( $dwSessionChannel ) ) {
				$this->log->error( "lost crypt key" );
				$this->response(
					$dwControllerConnexion
					,"lost crypt key!"
				);
			} else {
				$channelKey = $dwSessionChannel->getChannelKey();
				$clearResponse = null;
				
				$nextPacket = $dwSessionChannel->nextPacket();
				if ( is_null( $nextPacket ) )
					$this->cryptExecute( $dwSessionControllerConnexion );
				else {
					// NEXT FRAGMENT
					$cmd="\$cmdPrintf \"$nextPacket\" | shAgentFragment\n";
					$clearResponse = base64_encode( $cmd );
					$dataResponse = $dwSessionChannel->cryptoChannelResponse(
						$clearResponse
						,new DwCallBack(
							array( &$this, 'sessionResponse' )
							,$dwSessionControllerConnexion
						)
						,true
					);
					if ( ! is_null( $dataResponse ) ) {
						$this->sessionResponse(
							$dwSessionControllerConnexion
							,$dataResponse
						);
						//$this->response( $dwControllerConnexion ,$dataResponse );
						//$this->ShellCryptoWrite(
						//	$dwControllerConnexion
						//	,$dwSessionChannel
						//);
					}
				}
			}
		} catch(\Exception $exception_crypt) {
			$this->log->error("exception/cryptExecute");
			$this->response(
				$dwControllerConnexion
				,"exception/cryptExecute"
			);
			$this->log->throwable($exception_crypt);
			//throw $exception_crypt;
		} catch(\Error $error_crypt) {
			$this->log->error("error/cryptExecute");
			$this->response(
				$dwControllerConnexion
				,"error/cryptExecute"
			);
			$this->log->throwable($error_crypt);
			//throw $err;
		}
	}
	/*
	public function execute( 
		\bakuba\kernel\application\controller\DwControllerConnexion $dwControllerConnexion
	) : void {
		try {
			$this->log->trace(".");
			$this->valData->setValue( null );
			$dwSessionIpKey = $dwControllerConnexion->getDwSessionIpKey();
			parent::execute( $dwControllerConnexion );
			
			//$contentType="text/plain";
			//$dwControllerConnexion->headersAdd( "Content-type: $contentType; charset=utf-8" );
			
			$cryptedData=$this->valData->getValue();
			//$cryptedData = substr(base64_decode($raw64Content),5);
			
			$this->log->trace("cryptedData:".strlen( $cryptedData ));
			if ( empty( $cryptedData ) ) {
				$this->log->error("no cryptedData:".strlen( $cryptedData ));
				$this->response( $dwControllerConnexion ,"echec request 0.");
			} else {
				$this->log->trace("shellCryptoSearch");
				$dwSessionManager = $dwControllerConnexion->getDwSessionManager();
				$dwSessionRequest = new DwSessionRequest(
					$dwSessionIpKey
					,"shellCryptoSearch"
				);
				$dwSessionRequest->setObject( $cryptedData );
				$dwSessionResponse = $dwSessionManager->shellCryptoSearch(
					$dwSessionRequest
					,new DwCallBack(
						array( &$this ,"shellCryptoSearch" )
						,$dwControllerConnexion
					)
				);
				if ( is_object( $dwSessionResponse ) )
					$this->shellCryptoSearch( $dwControllerConnexion, $dwSessionResponse );
				
			}
		} catch(\Exception $exception) {
			$this->log->error("exception");
			$this->response( $dwControllerConnexion ,"exception" );
			throw $exception;
		} catch(\Error $error) {
			$this->log->error("error");
			$this->response( $dwControllerConnexion ,"error" );
			throw $error;
		}
	}
	public function shellCryptoSearch(
		\bakuba\kernel\application\controller\DwControllerConnexion $dwControllerConnexion
		,DwSessionResponse $dwSessionResponse
	) : void {
		try {
			$this->log->trace( "." );
			
			$dwSessionChannel = $dwSessionResponse->getObject();
			
			if ( ! is_object( $dwSessionChannel ) ) {
				$this->log->error( "lost crypt key" );
				$this->response(
					$dwControllerConnexion
					,"dwSessionChannel lost crypt key!"
				);
			} else {
			}
		} catch(\Exception $exception) {
			$this->log->error("exception");
			$this->response($dwControllerConnexion ,"exception");
			throw $exception;
		} catch(\Error $error) {
			$this->log->error("error");
			$this->response($dwControllerConnexion ,"error");
			throw $error;
		}
	}
	public function responsed(
		\bakuba\kernel\application\controller\DwControllerConnexion $dwControllerConnexion
		,DwSessionChannel $dwSessionChannel
		,string $channelResponse
	) {
		$channelKey = $dwSessionChannel->getChannelKey();
		$this->log->trace("channelResponse:\r\n$channelResponse");
		$this->ShellCryptoWrite( $dwControllerConnexion ,$dwSessionChannel );
		$this->response( $dwControllerConnexion ,$channelResponse );
	}
	public function ShellCryptoWrite(
		\bakuba\kernel\application\controller\DwControllerConnexion $dwControllerConnexion
		,DwSessionChannel $dwSessionChannel
	) : void {
		try {
			$channelKey = $dwSessionChannel->getChannelKey();
			$this->log->trace(".");
			
			$dwSessionManager = $dwControllerConnexion->getDwSessionManager();
			$dwSessionIpKey = $dwControllerConnexion->getDwSessionIpKey();
			$dwSessionRequest = new DwSessionRequest( $dwSessionIpKey, "ShellCryptoWrite" );
			$dwSessionRequest->setObject( $dwSessionChannel );
			$dwSessionResponse = $dwSessionManager->ShellCryptoWrite( $dwSessionRequest );
			
			$this->log->trace( $dwSessionResponse );
		} catch(\Exception $exception) {
			$this->log->error("exception");
			throw $exception;
		} catch(\Error $error) {
			$this->log->error("error");
			throw $error;
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
?>

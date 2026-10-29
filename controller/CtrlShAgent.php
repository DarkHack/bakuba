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
//use bakuba\kernel\controller\CtrlShAgent as CtrlShAgent;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

//use bakuba\kernel\dwNode\DwNode as DwNode;
//use bakuba\kernel\DwPreNode as DwPreNode;
use bakuba\kernel\dwNode\shAgent\DwShAgent as DwShAgent;

use bakuba\kernel\application\controller\DwControllerConnexion as DwControllerConnexion;

use bakuba\kernel\session\controller\DwSessionControllerConnexion as DwSessionControllerConnexion;

use bakuba\kernel\agartha\DwNodeRequest as DwNodeRequest;
use bakuba\kernel\agartha\DwNodeResponse as DwNodeResponse;

class CtrlShAgent 
	extends \bakuba\kernel\crypto\shell\DwCryptController {
	
	public function cryptExecute(
		DwSessionControllerConnexion $dwSessionControllerConnexion
	) : void {
		global $dwBakuba;
		$debugKey="{$this->getStaticClassName()}/{$this->getInstanceKey()}";
		$dwSessionChannel = $dwSessionControllerConnexion->getDwSessionChannel();
		
		$this->ipcSend( 
			$dwSessionControllerConnexion->getDwControllerConnexion()
			,$dwBakuba
			,new DwCallBack(
				array( &$this, 'nodeFound' )
				,$dwSessionControllerConnexion
			)
			,new DwNodeRequest(
				"getNode"
				,array( $dwSessionChannel->channelDataGet( "nodeUUID" ) )
			)
		);
	}
	public function nodeFound(
		DwSessionControllerConnexion $dwSessionControllerConnexion
		,DwNodeResponse $dwNodeResponse
	) : void {
		$debugKey="{$this->getStaticClassName()}/{$this->getInstanceKey()}";
		$dwSessionChannel = $dwSessionControllerConnexion->getDwSessionChannel();
		$dwShAgent = $dwNodeResponse->getDwShAgent();
		
		$data = $dwSessionChannel->getData();
		$this->log->trace( ". nodeFound data:\r\n".$data );
		$readyNextOrder = false;
		try {
			$jsonObj = json_decode( $data );
			if ( ! is_object( $jsonObj ) )
				$readyNextOrder = true;
			elseif ( ! isset( $jsonObj->{"end"} ) || ! isset( $jsonObj->{"orderId"} ) ) {
				$this->log->error( "no end or orderId defined data:\r\n$data" );
				$readyNextOrder = true;
			} else {
				$dataKey = "data".$jsonObj->{"orderId"};
				$dataResult = $dwSessionChannel->channelDataGet( $dataKey );
				$dataResult.= base64_decode( $jsonObj->{"result"} );
				$dwSessionChannel->channelDataSet( $dataKey, $dataResult );
				if ( $jsonObj->{"end"} == "true" ) {
					$dwShAgent->executeEnd(
						$this
						,$dwSessionControllerConnexion
						,$dwSessionChannel->channelDataGet( "nodeUUID" )
						,base64_decode( $jsonObj->{"orderId"} )
						,$dataResult
					);
					$dwSessionChannel->channelDataUnset( $dataKey );
					$readyNextOrder = true;
				}
				$logResult = "";
				foreach( $jsonObj as $key => $data64 ) {
					$logResult.="$key:".base64_decode($data64)."\n";
				}
				$this->log->trace( ". json decode :\n".$logResult );
			}
		} catch(\Exception $exception_exec) {
			$this->log->error("[{$debugKey}] exception");
			$this->log->throwable($exception_exec);
			//throw $exception_exec;
		} catch(\Error $error_exec) {
			$this->log->error("[{$debugKey}] error");
			$this->log->throwable($error_exec);
			//throw $error_exec;
		}
			
		$scriptContent = "";
		$scriptContent .= \bakuba\kernel\shell\DwShellCrypt::getRandomCache()."\n";
		//if ( is_null ( $dwShAgent->getPhpAgentPid() ) {
		//}
		if ( ! $readyNextOrder ) {
			$scriptContent .= "\$cmdPrintf \"ENDED no readyNextOrder........ \" \n";
		} elseif ( is_object($dwShAgent) && $dwShAgent->hasOrder() ) {
			$dwShAgentOrder = $dwShAgent->nextOrder();
			if ( ! is_object($dwShAgentOrder) ) 
				$scriptContent .= "\$cmdPrintf \"ENDED dwShAgentOrder not object \" \n";
			else {
				$this->log->trace(
					"send order :{$dwShAgentOrder->getOrderId()}\n"
					.$dwShAgentOrder->{"order"}
				);
				$objJson = json_decode("{}");
				$objJson->{"orderId"} = base64_encode($dwShAgentOrder->getOrderId());
				if ( $dwShAgentOrder->useShAgentExecutePipe() ) {
					$objJson->{"eval"} = base64_encode($dwShAgentOrder->{"order"});
				} else {
					$scriptContent .= $dwShAgentOrder->{"order"}."\n";
					
					$objJson->{"eval"} = base64_encode(
						"echo ' order ".$dwShAgentOrder->getOrderId()." end'"
					);
				}
				$scriptContent .= "echo \"".base64_encode( json_encode( $objJson ) )."\" | shAgentExecute \n";
			}
		} else {
			$sessionCpt = $dwSessionChannel->channelDataGet( "sessionCpt");
			$sessionCpt++;
			$dwSessionChannel->channelDataSet( "sessionCpt", $sessionCpt );
		
			if ( $sessionCpt < 5 )
				$scriptContent .= "shAgentRun \n";
			elseif ( $sessionCpt < 50 )
				$scriptContent .= "sleep 120;shAgentRun \n";
			else
				$scriptContent .= "\$cmdPrintf \"ENDED\" \n";
		}
		
		$dwSessionChannel->cryptoChannelResponse(
			base64_encode($scriptContent)
			,new DwCallBack(
				array( &$this, 'sessionResponse' )
				,$dwSessionControllerConnexion
			)
		);
		//$this->response(
		//	$dwControllerConnexion
		//	,$dwSessionChannel->cryptoChannelResponse(
		//		base64_encode( $scriptContent )
		//	)
		//);
	}
			
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}


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

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwKernel as DwKernel;
use bakuba\kernel\objects\web\DwWebAction as DwWebAction;
use bakuba\kernel\application\controller\DwController as DwController;
use darkphp\web\DwWebValue as DwWebValue;
use darkphp\web\DwWebValue as DwWebValue64;
use bakuba\kernel\DwAutoLoad as DwAutoLoad;
use bakuba\kernel\dwCrypto\DwCypherManager as DwCypherManager;
use bakuba\kernel\dwCrypto\DwCrypto as DwCrypto;

use bakuba\kernel\DwPreNode as DwPreNode;
use bakuba\kernel\dwNode\DwNodeApplication as DwNodeApplication;

class CtrlStarter extends DwController {
	static private $ipSessions = array();
	
	private $log;
	private $valPreNodeKey = null;
	private $valCiphers = null;
	
	public function __construct( ) {
		try {
			$this->log = DwLogger::getLogger(__CLASS__);
			$this->log->info(".");
			parent::__construct( __CLASS__, __CLASS__ );
			
			$this->valPreNodeKey = new DwWebValue( "preNodeKey" );
			$this->addPublicValue( $this->valPreNodeKey );
			
			$this->valCiphers = new DwWebValue64( "ciphers" );
			$this->addPublicValue( $this->valCiphers );
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
			
			$this->valPreNodeKey->setValue( null );
			$this->valCiphers->setValue( null );
			$this->refresh( $get, $post );
			
			switch ( $this->valType->getValue() ) {
			case "php" :
				$ipSrv = $server["SERVER_ADDR"];
				$ipClt = $this->getIp( $server );
				$ipKey = "$ipSrv:$ipClt";
				
				$valPreNodeKey = $this->valPreNodeKey->getValue();
				$valCiphers = $this->valCiphers->getValue();
				
				if ( is_null( $valPreNodeKey ) ) {
					if ( is_null( $valCiphers ) )
						$dataReponse = "refused no one cipher read.";
					else {
						if ( ! isset(self::$ipSessions[ $ipKey ]) )
							self::$ipSessions[ $ipKey ] = array();
						
						$dwPreNode = DwPreNode::createPreNode( $ipSrv, $ipClt, $valCiphers );
						
						self::$ipSessions[ $ipKey ][ $dwPreNode->getPreNodeKey() ] = $dwPreNode;
						
						$dwCltStarterParam = array( 
							'ipSrv' => $dwPreNode->getIpSrv()
							, 'ipClt' => $dwPreNode->getIpClt()
							, 'preNodeKey' => $dwPreNode->getPreNodeKey()
						);
						$contentType="text/x-php";
						$sourceStarter  = "<?php ";
						$sourceStarter .= "include( \"data://text/plain;base64,".base64_encode( DwAutoLoad::getSource( "bakuba\kernel\DwCltStarter" ) )."\");";
						$sourceStarter .= "use bakuba\kernel\DwCltStarter as DwCltStarter;";
						$sourceStarter .= "(new DwCltStarter(\"".base64_encode( json_encode( $dwCltStarterParam ) )."\"))->lunch();";
						$sourceStarter .= "?>";
						$dataReponse = base64_encode( $sourceStarter );
					}
				} else {
					if (! isset ( self::$ipSessions[ $ipKey ][ $valPreNodeKey ] ) )
						$dataReponse = "refused no session";
					else {
						$dwPreNode = DwPreNode::getPreNode( $valPreNodeKey );//self::$ipSessions[ $ipKey ][ $valPreNodeKey ];
						
						if ( ! $dwPreNode->getAccess() )
							$dataReponse = "wait";
						else {
							$contentType="text/x-php";
							$sourceStarter  = "<?php ";
							$sourceStarter .= "include( \"data://text/plain;base64,".base64_encode(DwAutoLoad::getSource("bakuba\kernel\dwCrypto\DwCypherManager"))."\");";
							$sourceStarter .= "include( \"data://text/plain;base64,".base64_encode(DwAutoLoad::getSource("bakuba\kernel\dwCrypto\DwCrypto"))."\");";
							$sourceStarter .= "include( \"data://text/plain;base64,".base64_encode(DwAutoLoad::getSource("bakuba\kernel\dwCrypto\DwCryptoChannel"))."\");";
							$sourceStarter .= "include( \"data://text/plain;base64,".base64_encode(DwAutoLoad::getSource("bakuba\kernel\dwCrypto\DwCryptControllerClient"))."\");";
							$sourceStarter .= "include( \"data://text/plain;base64,".base64_encode(DwAutoLoad::getSource("bakuba\kernel\DwCltAutoLoad"))."\");";
							$sourceStarter .= "?>";
							
							$dwNode = DwNodeApplication::createNode( $dwPreNode );
							$paramsStart = array();
							$paramsStart['source'] = $sourceStarter;
							$paramsStart['initCryptSource'] = CtrlCryptSource::init( $dwNode );
							$paramsStart['initNode'] = CtrlNode::init( $dwNode );
							
							$dataReponse = base64_encode( json_encode( $paramsStart ) );
						}
					}
				}
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
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

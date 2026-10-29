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

namespace bakuba\kernel\crypto\php;
//use bakuba\kernel\crypto\php\DwCryptController

use \Exception as Exception;
use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\dwController\abstracts\aDwController as aDwController;

use bakuba\kernel\dwNode\DwNode as DwNode;

use bakuba\kernel\dwCrypto\DwCryptoChannel as DwCryptoChannel;

use bakuba\kernel\network\DwIp as DwIp;

abstract class DwCryptController
	extends \bakuba\kernel\session\controller\DwControllerSession {
	
	public function getClassNameDwSessionChannel() : string {
		return DwSessionChannel::getStaticClassName();
	}
	
	static private $ipSessions = array();
	
	public static function init( DwNode $dwNode ) {
		$b64DataInit = null;
		$ipKey = $dwNode->getIpSrv().":".$dwNode->getIpClt();
		
		if ( ! isset( self::$ipSessions[ $ipKey ] ) ) self::$ipSessions[ $ipKey ] = array();
		
		$ctrlCryptKey = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( self::$ipSessions[ $ipKey ] );
		
		$dataInit = array();
		$dataInit["initCrypto"] = DwCryptoChannel::initA( $dwNode->getCiphers() );
		$dataInit["ctrlCryptKey"] = $ctrlCryptKey;
		$dataInit["ipSrv"] = $dwNode->getIpSrv();
		$dataInit["ipClt"] = $dwNode->getIpClt();
		$dataInit["dwNode"] = $dwNode;
		
		self::$ipSessions[ $ipKey ][ $ctrlCryptKey ] = $dataInit;
		$b64DataInit = base64_encode( json_encode( $dataInit ) );
		//die( "init :".print_r( array( "b64" => $b64DataInit, "dataInit" =>  $dataInit, "json" => json_encode( $dataInit ) ), true )  );
		
		return $b64DataInit;
	}
	/*
	public function __construct( $className ) {
		parent::__construct( __CLASS__.$className, __CLASS__.$className );
		
		$this->valData = new \darkphp\web\DwWebValue( "data" );
		$this->addPublicValue( $this->valData );
		
		$this->log->info(".");
	}
	//*/
	public function executeSession(
		\bakuba\kernel\session\controller\DwSessionControllerConnexion $dwSessionControllerConnexion
	) : void {
		$this->log->error( "." );
	}
	public function binExecute( $server, $get, $post, $raw64Content ) {
		global $dwKernel;
		$keySession = null;
		$cryptReponse = null;
		try {
			$dataReponse = "";
			$this->log->trace(". raw64:$raw64Content");
			$contentType="text/plain";
			
			$this->valData->setValue( null );
			$this->refresh( $get, $post );
			
			switch ( $this->valType->getValue() ) {
			case "php" :
				$ipSrv = $server["SERVER_ADDR"];
				$ipClt = $this->getIp( $server );
				if ( ! DwIp::ipCheck("$ipClt", "$ipSrv/24") )
					$ipSrv = "";
				
				$ipKey = "$ipSrv:$ipClt";
				if ( strlen( $raw64Content ) > 0 )
					$messageJson64 = base64_decode($raw64Content);
				else
					$messageJson64 = $this->valData->getValue();
				/*
				foreach( $post as $dataGramKey => $dataGramValue )
				 $messageJson64.=$dataGramKey.$dataGramValue;
				 */
				if ( strlen($messageJson64) <= 0 )
					throw new Exception(" no messageJson64:$messageJson64 raw:$raw64Content \r\n".print_r(array($raw64Content, $get, $post, $server),true));
				
				$messageJson = base64_decode( $messageJson64 );
				if ( strlen($messageJson) <= 0 )
					throw new Exception(" no messageJson in messageJson64 :$messageJson64");
				
				$dataRequest = json_decode( $messageJson );
				if (  !is_object($dataRequest) )
					throw new Exception(" no dataRequest in messageJson :$messageJson");
				
				if ( ! isset( $dataRequest->ctrlCryptKey ) )
					throw new Exception(" no ctrlCryptKey in dataRequest :".print_r($dataRequest,true ) );
				
				if (! isset ( self::$ipSessions[ $ipKey ][ $dataRequest->ctrlCryptKey ] ) ) {
					$this->log->error("lost session ".json_encode($dataRequest) );
					$dataReponse = "no session ";//.print_r( array( "dataRequest" => $dataRequest, "b64" => $this->valData->getValue() ), true );
				} else {
					$contentType="text/x-php";
					$dwNode = self::$ipSessions[ $ipKey ][ $dataRequest->ctrlCryptKey ]["dwNode"];
					$keySession = self::$ipSessions[ $ipKey ][ $dataRequest->ctrlCryptKey ]["initCrypto"]["keySession"];
					if ( ! isset($keySession) || ! $dwNode->getAccess() ) 
						$dataReponse = "<?php echo 'access refused".DwCryptoChannel::decode( $keySession,  $dataRequest->message )."'; ?>";
					else
						$dataReponse = $this->executeCrypt( $dwNode, DwCryptoChannel::decode( $keySession,  $dataRequest->message ) );
				}
				break;
			default:
				$dataReponse = $this->noTypeExecute( $server, $get, $post );
				break;
			}
			$this->headersAdd( "Content-type: $contentType; charset=utf-8" );
		} catch(Exception $err) {
			$dataReponse = "fatal error";
			$this->log->error($err);
			throw $err;
		}
		try {
			if ( $keySession != null )
			 $cryptReponse = DwCryptoChannel::encode( $keySession,  $dataReponse );
			else
			 $cryptReponse = $dataReponse;
		} catch(Exception $err) {
			$cryptReponse = "";
			$this->log->error($err);
			throw $err;
		}
		return $cryptReponse;
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

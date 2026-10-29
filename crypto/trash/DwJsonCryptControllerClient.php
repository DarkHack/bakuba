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

namespace bakuba\kernel\dwCrypto;
// use bakuba\kernel\dwCrypto\DwJsonCryptControllerClient as DwJsonCryptControllerClient;

use \Exception as Exception;
use \Error as Error;

use bakuba\kernel\objects\serialize\IDwSerializable as IDwSerializable;

use bakuba\kernel\crypto\json\DwJsonCrypto as DwJsonCrypto;
use bakuba\kernel\crypto\json\DwJsonEndpoint as DwJsonEndpoint;

use bakuba\kernel\crypto\jsonPhp\DwCryptoPhp as DwCryptoPhp;

class DwJsonCryptControllerClient {
	
	private $uri = "";
	private $hostname = "";
	private $cryptoSessions = array();
	private $requestCpt = 0;
	public function cryptoChannelSet( $attributeName, $attributeValue ) {
		$this->cryptoSessions[ $attributeName ] = $attributeValue;
	}
	
	public function cryptoChannelGet( $attributeName ) {
		$cryptoSession = null;
		
		if ( ! isset($this->cryptoSessions[$attributeName]) )
			$cryptoSession= null;
		else
			$cryptoSession = $this->cryptoSessions[$attributeName];
		
		return $cryptoSession;
	}
	
	public function __construct( DwJsonEndpoint $jsonData, $className = "PhpAgent" ) {
		echo __CLASS__.":\r\n{$jsonData}\r\n";
		$ciphers = DwCryptoPhp::commonCiphers( $jsonData->{"ciphers"} );
		$digests = DwCryptoPhp::commonDigests( $jsonData->{"digests"} );
		$this->cryptoChannelSet( "ciphers" ,$ciphers );
		$this->cryptoChannelSet( "digests" ,$digests  );
		
		// [ 'ipSrv', 'ipClt' , 'key', 'crypto' ]
		$dwJsonCryptoNext = DwCryptoPhp::initMessage( $this->cryptoChannelGet( "ciphers" ) ,$this->cryptoChannelGet( "digests" ) );
		$dwJsonCryptoNext->{"ciphers"} = $ciphers;
		$dwJsonCryptoNext->{"digests"} = $digests;
		$this->cryptoChannelSet( "nextMessage" ,$dwJsonCryptoNext );
		$this->cryptoChannelSet( "response" ,$jsonData );
		$this->hostname =$jsonData->{"host"};
		$this->uri= $jsonData->{"proto"}.'://'.$jsonData->{"ipaddress"}.':'.$jsonData->{"port"}.'/'.$className;
		
	}

	public function request( $clearData ) {
		$jsonNextMessage = $this->cryptoChannelGet( "nextMessage" );
		$this->cryptoChannelSet("message", $jsonNextMessage );
		$jsonNextMessage->{"data"}=base64_encode($clearData);
		$responseMessage = $this->cryptoChannelGet( "response" );
		$responseMessage->{"data"}=base64_encode(json_encode( $jsonNextMessage ));
		
		$response = DwCryptoPhp::encrypte( $responseMessage );
		
		$sslOptions = array(
			'verify_peer'   => false
			,'SNI_enabled'	=> true
			,'SNI_server_name' => $this->hostname //'bakuba.darkweb.fr'
			,'peer_name'	=> $this->hostname //'bakuba.darkweb.fr'
			,'cafile'		=> '/etc/darksrv/ssl/_.darkweb.fr/GandiStandardSSLCA2.pem'
			,'verify_depth'	=> 5
		);
		// RFC 
		//$content = http_build_query( array( 'data' => $data64 ) );
		/*
		$cptRqt = 0;
		while( file_exists("/tmp/rqt64_$cptRqt") ) $cptRqt++;
		file_put_contents("/tmp/rqt64_$cptRqt", $data64 );
		//*/
		$eol = "\r\n";
		$data = '';
		$mime_boundary=md5(time());
		
		$data .= '--' . $mime_boundary . $eol;
		$data .= 'Content-Disposition: form-data; name="data"' . $eol . $eol;
		$data .= $response . $eol;
		$data .= '--' . $mime_boundary . $eol;
		//$data .= 'Content-Disposition: form-data; name="somefile"; filename="filename.ext"' . $eol;
		//$data .= 'Content-Type: text/plain' . $eol;
		//$data .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
		//$data .= chunk_split(base64_encode("Some file content")) . $eol;
		//$data .= "--" . $mime_boundary . "--" . $eol . $eol; // finish with two eol's!!
		$HttpParams = array(
			'method'  => 'POST'
			,'header'  => 'Content-type: multipart/form-data; boundary='.$mime_boundary.$eol."Host: ".$this->hostname.$eol
			,'content' => $data
		);
		$cryptSrcResponse=false;
		$this->requestCpt++;
		for( $cptTry = 1 ;$cryptSrcResponse === false && $cptTry<=10 ;$cptTry++ ) {
			if ( $cptTry > 0 )
				echo __CLASS__."[pid:".getmypid()."]request:{$this->requestCpt} try :$cptTry\r\n";
			try {
				$cryptSrcResponse = file_get_contents( $this->uri, false, stream_context_create( array(
//					'socket' => array( 'bindto' => $this->ipClt.':0' )
					'ssl' => $sslOptions
					,'http' => $HttpParams
				)));
			} catch(Exception $exception) {
				echo "exception [".$this->getClassName()."]\r\n{$exception}\r\n";
			} catch(Error $error) {
				echo "error [".$this->getClassName()."]\r\n{$error}\r\n";
			}
		}
		
		$jsonMessage =  $this->cryptoChannelGet( "message" );
		$jsonMessage->{"data"}=$cryptSrcResponse;
		
		$jsonRequest = DwJsonCrypto::fromJson( base64_decode( DwCryptoPhp::decrypte( $jsonMessage ) ) );
		
		$cryptSrcPlainText = $jsonRequest->{"data"};
		//$cryptSrcPlainText = $data;
		//$this->cryptoChannelSet( "data", $data);
		$this->cryptoChannelSet( "nextMessage", DwCryptoPhp::initMessage( $this->cryptoChannelGet( "ciphers" ) ,$this->cryptoChannelGet( "digests" ) ) );
		$this->cryptoChannelSet( "response", $jsonRequest );
		//echo "to unserialize:\r\n$cryptSrcPlainText\r\n";
		return unserialize( $cryptSrcPlainText );
	}	
}
?>

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
// use bakuba\kernel\dwCrypto\DwShellCryptControllerClient as DwShellCryptControllerClient;

use \Exception as Exception;
use \Error as Error;

use bakuba\kernel\objects\serialize\IDwSerializable as IDwSerializable;

use bakuba\kernel\application\session\cryptoShell\DwJsonCrypto as DwJsonCrypto;
use bakuba\kernel\application\session\cryptoShell\DwJsonEndpoint as DwJsonEndpoint;
use bakuba\kernel\application\session\cryptoShell\DwCryptoShellChannel as DwCryptoShellChannel;


class DwShellCryptControllerClient {
	
	private $uri = "";
	private $hostname = "";
	private $cryptoSessions = array();
	
	public function cryptoChannelSet( $attributeName ,$attributeValue ) {
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
		$this->cryptoChannelSet( "ciphers", $jsonData->{"ciphers"} );
		$this->cryptoChannelSet( "digests", $jsonData->{"digests"} );
		// [ 'ipSrv', 'ipClt' , 'key', 'crypto' ]
		$this->cryptoChannelSet( "nextMessage", DwCryptoShellChannel::initMessage( $this->cryptoChannelGet( "ciphers" ) ,$this->cryptoChannelGet( "digests" ) ) );
		$this->cryptoChannelSet( "response" ,$jsonData );
		$this->hostname =$jsonData->{"host"};
		$this->uri= $jsonData->{"proto"}.'://'.$jsonData->{"ipaddress"}.':'.$jsonData->{"port"}.'/'.$className;
		
	}

	public function request( $clearData ) {
		$jsonNextMessage = $this->cryptoChannelGet( "nextMessage" );
		$this->cryptoChannelSet("message", $jsonNextMessage );
		$jsonNextMessage->{"data"}=$clearData;
		$responseMessage = $this->cryptoChannelGet( "response" );
		$responseMessage->{"data"}=base64_encode(json_encode( $jsonNextMessage ));
		
		$response = DwCryptoShellChannel::encrypt( $responseMessage );
		
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
		$cryptSrcResponse = file_get_contents( $this->uri, false, stream_context_create( array(
//			'socket' => array( 'bindto' => $this->ipClt.':0' )
			'ssl' => $sslOptions
			,'http' => $HttpParams
		)));
		
		$jsonMessage =  $this->cryptoChannelGet( "message" );
		//self::$sLog->debug("jsonMessage:".print_r($jsonMessage,true));
		$jsonMessage->{"data"}=$cryptSrcResponse;
		
		$jsonRequest = DwJsonCrypto::fromJson( base64_decode( DwCryptoShellChannel::decrypt( $jsonMessage ) ) );
		
		
		$cryptSrcPlainText = $jsonRequest->{"data"};
		//$cryptSrcPlainText = $data;
		//$this->cryptoChannelSet( "data", $data);
		$this->cryptoChannelSet( "nextMessage", DwCryptoShellChannel::initMessage( $this->cryptoChannelGet( "ciphers" ) ,$this->cryptoChannelGet( "digests" ) ) );
		$this->cryptoChannelSet( "response", $jsonRequest );
		
		return $cryptSrcPlainText;
	}	
}
?>

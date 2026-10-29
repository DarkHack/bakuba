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

namespace  bakuba\kernel\io\socket\http;
//use bakuba\kernel\io\socket\http\DwSocketServerDefaultSsl as DwSocketServerDefaultSsl;

use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

//use bakuba\kernel\io\socket\DwSocket as DwSocket;
//use bakuba\kernel\io\socket\DwSocketSelect as DwSocketSelect;

use bakuba\kernel\services\server\DwServerConnexion as DwServerConnexion;
//use bakuba\kernel\io\socket\DwSocket as DwSocket;

//use bakuba\kernel\process\server\IDwServer as IDwServer;

class DwSocketServerDefaultSsl extends DwSocketServer {
	private $dwStreamSelect = null;
	//private $sslContext = null;
	
	public function __construct(
		\bakuba\kernel\io\stream\DwStreamSelect $dwStreamSelect
		,\bakuba\kernel\io\socket\DwSocketSelect $dwSocketSelect
		,\bakuba\kernel\objects\DwCallBack $onConnect
		,\bakuba\kernel\network\DwPort $dwPort
		,int $protocole = \SOL_TCP
	) {
		$this->dwStreamSelect = $dwStreamSelect;
		
		$cryptoMethods= \STREAM_CRYPTO_METHOD_TLS_SERVER | \STREAM_CRYPTO_METHOD_TLSv1_2_SERVER;
		if ( defined( "STREAM_CRYPTO_METHOD_TLSv1_3_SERVER" ) )
			$cryptoMethods|=\STREAM_CRYPTO_METHOD_TLSv1_3_SERVER;
			
		$localSslCert = \bakuba\kernel\agartha\DwInstall::getLocalDirectory( '/etc/darksrv/ssl/darkweb.fr' );
		\stream_context_set_default( [
			'ssl' => [
				'local_cert'        => "{$localSslCert}/darkweb.fr.pem"
				,'local_pk'         => "{$localSslCert}/darkweb.fr.key" // si séparée
//				,'passphrase'        => 'motdepasse' // si clé chiffrée
				,'verify_peer'       => false 
				,'allow_self_signed' => false 
				,'crypto_method' => $cryptoMethods
				,'SNI_server_name'	 => 'darkweb.fr'// optionnel, SNI
			]
		] );
		parent::__construct( $dwSocketSelect ,$onConnect ,$dwPort ,$protocole );
	}
	/*
	public function init(  ) : void {
		parent::init();
	}
	//*/
	/*
	protected function createDwSocket( $sock ) : DwSocket {
		 return new DwSocket(
			$this
			,$this->dwInterface
			,$sock
		);
	}
	//*/
	public function newSocketConnection( \bakuba\kernel\io\socket\DwSocket $dwSocket ) : void {
		$this->log->info( "." );
		// Lire quelques octets du flux TCP brut mais ne pas les perdre
		$firstBytes = \socket_recv( $dwSocket->getResource(), $buf, 5, MSG_PEEK);
		$this->log->info( bin2hex( $buf ) );
		// MSG_PEEK permet de lire sans retirer du buffer !
     
		if ($buf && ord($buf[0]) === 0x16) { // handshake SSL
			$this->log->info( 'found handshake' );
			$this->newSocketConnectionSsl( $dwSocket );
		} else {
			$this->log->info( "Try Clear Stream :" );
			parent::newSocketConnection( $dwSocket );
		}
	}
	// Ssl enabled with Perplexity Production :
	public function newSocketConnectionSsl( \bakuba\kernel\io\socket\DwSocket $dwSocket ) : void {
		$this->log->info( '.' );
		$dwStreamSocket = new \bakuba\kernel\io\stream\DwStreamSocket( $this ,$this->dwInterface ,\socket_export_stream( $dwSocket->getResource() ) );
		$dwStreamSocket->connectedReadPeer();
		$this->newTrySsl( $dwStreamSocket );
	}
		
	public function newTrySsl( \bakuba\kernel\io\stream\DwStreamSocket $dwStreamSocket ,int $cpt = 0 ) : void {
		$this->log->info( '.' );
		$result = null;
		
		$cryptoMethods= \STREAM_CRYPTO_METHOD_TLS_SERVER | \STREAM_CRYPTO_METHOD_TLSv1_2_SERVER;
		if ( defined( "STREAM_CRYPTO_METHOD_TLSv1_3_SERVER" ) )
			$cryptoMethods|=\STREAM_CRYPTO_METHOD_TLSv1_3_SERVER;
		
		$result = \stream_socket_enable_crypto(
			$dwStreamSocket->getResource()// sa peu changer quelque chose si je met $sock au lieux de $stream ici?
			,true
			,$cryptoMethods
		);
		$this->log->info( "ssl error string" );
		$this->log->info( \openssl_error_string() );
		$this->log->info( "ssl error get last" );
		$this->log->info( \error_get_last() );
		if ( $result === 0 ) {
			$this->log->info( "ReTry SSL :".print_r( $result ,true ) );
			$dwStreamSocket->enableListen();// enable listen for no read action
			if ( $cpt > 20 ) {
				$this->log->info( "ReTry SSL : STOP after $cpt tentatives..." );
				$dwStreamSocket->disableListen();
				new DwServerConnexion(
					$this->parent
					,new \bakuba\kernel\io\stream\DwStreamSocketIpConnexion (
						$this->dwStreamSelect
						,$dwStreamSocket
						,$this->dwPort
						,$dwStreamSocket->getDwPort()
					) 
				);
			}
			else
				$this->getProcDwResources()->addTimer(
					0.2
					,new DwCallBack(
						array( &$this ,"newTrySsl" )
						,$dwStreamSocket
						,$cpt++
					)
				);
		} else {
			$dwStreamSocket->disableListen();
			if ( $result === true ) {
				$this->log->info( "Try SSL :".print_r( $result ,true ) );
			} else {
				$this->log->info( "Not SSL :".print_r( is_null( $result ) ? "null" : $result, true ) );
			}
			new DwServerConnexion(
				$this->parent
				,new \bakuba\kernel\io\stream\DwStreamSocketIpConnexion (
					$this->dwStreamSelect
					,$dwStreamSocket
					,$this->dwPort
					,$dwStreamSocket->getDwPort()
				) 
			);
		}
	}
	
	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
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

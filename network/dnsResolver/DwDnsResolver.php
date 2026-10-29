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

namespace bakuba\kernel\network\dnsResolver;
//use bakuba\kernel\network\dnsResolver\DwDnsResolver as DwDnsResolver;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use bakuba\kernel\objects\DwCallBack as DwCallBack;
use darkphp\DwObject as DwObject;

use bakuba\kernel\process\DwProcessIpc as DwProcessIpc;

use \bakuba\kernel\protocole\dns\DwDnsRequest as DwDnsRequest;
use bakuba\kernel\network\dnsResolver\DwApplication as DwDnsResolverProcess;

class DwDnsResolver extends DwObject {
	private $dwDnsBinaryDumper = null;
	private $dwDnsParser = null;
	private $dwPortDnsSrv = null;
	
	private $dwSocketClient = null;
	private function getDwSocketSelect() : \bakuba\kernel\io\socket\DwSocketSelect {
		return $this->parent;
	}
	
	public function __construct( \bakuba\kernel\process\DwResources $dwResources ,$ipDnsSrv = '127.0.0.1' ,$dwInterface = null ) {
		parent::__construct( $dwResources->getSocketSelect() );
		if ( is_null( $dwInterface ) )
			$dwInterface = new \bakuba\kernel\network\DwInterface( $this ,'clientIface' );
		
		$this->dwDnsBinaryDumper = new \bakuba\kernel\protocole\dns\DwDnsBinaryDumper();
		$this->dwDnsParser = new \bakuba\kernel\protocole\dns\DwDnsParser();
		$dwIpDnsSrv = \bakuba\kernel\network\DwIp::parseIp( $ipDnsSrv ,$dwInterface );
		$this->dwPortDnsSrv = $dwIpDnsSrv->getPort( 53 );
		
		$this->dwSocketClient = new \bakuba\kernel\io\socket\DwSocketClient(
			$this->getDwSocketSelect()
			,DwCallBack::emptyCB()
			,$this->dwPortDnsSrv
			,\SOL_UDP
		);
	}
	public function dnsResolvRequest( DwCallBack $dwCallBack ,DwDnsRequest $dwDnsRequest ) : void {
		$this->dnsSocketSend(
			$dwCallBack
			,$dwDnsRequest->getDwDnsMessage()
		);
	}
	public function dnsResolv( DwCallBack $dwCallBack ,$domain ,$type = 'A' ) : void {
		$this->log->trace( "[{$type}] {$domain}" );
		$this->dnsSocketSend(
			$dwCallBack
			,\bakuba\kernel\protocole\dns\DwDnsMessage::createRequestForQuery(
				new \bakuba\kernel\protocole\dns\DwDnsQuery(
					$domain
					,\bakuba\kernel\protocole\dns\DwDnsMessage::parseStrType( $type )
					,\bakuba\kernel\protocole\dns\DwDnsMessage::CLASS_IN
				)
			)
		);
		
	}
	private function dnsSocketSend( DwCallBack $dwCallBack ,\bakuba\kernel\protocole\dns\DwDnsMessage $DwDnsMessage ) {
		$dwSocket = $this->dwSocketClient->connection();
		
		$anLogger = $this->log;
		
		$dwSocket->write( $this->dwDnsBinaryDumper->toBinary( $DwDnsMessage ) );
		$socketSelect = $this->getDwSocketSelect();
		
		$dwSocket->setOnWrited( new DwCallBack(
			function () use ( $socketSelect, $dwSocket ,$dwCallBack ,$anLogger ) {
				//$anLogger->trace('socket writed');
				$dwSocket->setOnReaded( new DwCallBack(
					function ( \bakuba\kernel\io\socket\DwSocket $dwSocket ) use( $dwCallBack ,$anLogger ) {
						$data = $dwSocket->read();
						//$anLogger->trace( 'socket readed:'.strlen( $data ).'/'.\bin2hex( $data ) );
						try {
							//$dnsMessage = $this->dwDnsParser->parseMessage( $data );
							//$anLogger->trace( \print_r( $dnsMessage ,true ) );
							//$dwCallBack->callback( $dnsMessage );
							$dwCallBack->callback( $this->dwDnsParser->parseMessage( $data ) );
						} catch( \Exception $anException ) {
							$anLogger->error( \darkphp\error\DwException::exceptionToString( $anException ) );
						} catch( \Error $anError ) {
							$anLogger->error( \darkphp\error\DwException::exceptionToString( $anError ) );
						}
					}
					,$dwSocket
				) );
				$socketSelect->socketRead( $dwSocket ); 
			}
		) );
		
		$socketSelect->socketWrite( $dwSocket );
	}
	static private $dwDnsResolver = null;
	//\bakuba\kernel\network\dnsResolver\DwDnsResolver::setDnsResolver( $dwDnsResolver );
	public static function setDnsResolver( self $dwDnsResolver ) : void {
		self::$dwDnsResolver = $dwDnsResolver;
	}
	public static function getDnsResolver() : ?self {
		return self::$dwDnsResolver;
	}
	
	//\bakuba\kernel\network\dnsResolver\DwDnsResolver::resolv( $dwCallBack ,$domain ,$type = 'A' );
	public static function resolv( DwCallBack $dwCallBack ,$domain ,$type = 'A' ) : void {
		self::$dwDnsResolver->dnsResolv( $dwCallBack ,$domain ,$type );
	}
	
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

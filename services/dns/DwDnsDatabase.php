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

namespace bakuba\kernel\services\dns;
//use bakuba\kernel\services\dns\DwDnsDatabase as DwDnsDatabase;

//use \bakuba\kernel\protocole\dns\DwDnsRecord as DwDnsRecord;
use \bakuba\kernel\protocole\dns\DwDnsMessage as DwDnsMessage;
use \bakuba\kernel\protocole\dns\DwDnsResponse as DwDnsResponse;
use \bakuba\kernel\protocole\dns\DwDnsRequest as DwDnsRequest;

use bakuba\kernel\network\DwInterface as DwInterface;
use bakuba\kernel\network\DwIp as DwIp;
use bakuba\kernel\network\DwPort as DwPort;

class DwDnsDatabase extends \darkphp\DwObject {
	/*
	private static $dnsDataBase=[
		'mynode.darkweb.fr' => [
			DwDnsMessage::TYPE_A => '1.2.3.4'
			,DwDnsMessage::TYPE_AAAA => 'fec0:1:2:3::4'
			,DwDnsMessage::TYPE_NS => [ 'mynode.darkweb.fr' ,'darkweb.fr' ]
		]
		,'darkweb.fr' => [
			DwDnsMessage::TYPE_A => '1.2.3.4'
			,DwDnsMessage::TYPE_AAAA => '4:3:2::1'
			,DwDnsMessage::TYPE_CNAME => [ 'testns.darkweb.fr.' ]
			,DwDnsMessage::TYPE_SPF => ['v=spf1 -all']
			,DwDnsMessage::TYPE_TXT => [ 'hello', 'world' ]
			,DwDnsMessage::TYPE_PTR => 'testns.darkweb.fr.'
			,DwDnsMessage::TYPE_MX => ['priority' => 0, 'target' => '']
			,DwDnsMessage::TYPE_SRV => [
				'priority' => 10 ,'weight' => 20
				,'port' => 8080 ,'target' => 'testns'
			]
			,DwDnsMessage::TYPE_CAA => ['flag' => 0, 'tag' => 'issue', 'value' => 'letsencrypt.org']
			,DwDnsMessage::TYPE_SSHFP => ['algorithm' => 1, 'type' => '1', 'fingerprint' => '69ac090c']
		]
		,'1.2.3.4.in-addr.arpa' => [
			DwDnsMessage::TYPE_PTR => 'mynode.darkweb.fr.'
		]
		,
	];
	
	public function getRecord( string $domain, int $type ) {
		$answerData = null;
		if ( isset ( self::$dnsDataBase[ $domain ] ) && isset( self::$dnsDataBase[ $domain ][ $type ] ) )
			$answerData = self::$dnsDataBase[ $domain ][ $type ];
		
		return $answerData;
	}
	//*/
	///*
	public function __construct( \darkphp\DwObject $dwObject ) {
		parent::__construct( $dwObject );
		$this->addDomain( 'mynode.darkweb.fr' );
	}
	//*/
	private function getIpIdx( DwIp $dwIp ) : int {
		$ipIdx = null;
		switch( $dwIp->getClassName() ) {
			case \bakuba\kernel\network\DwIp6::getStaticClassName() :
				$ipIdx=DwDnsMessage::TYPE_AAAA;
				break;
			case \bakuba\kernel\network\DwIp4::getStaticClassName() :
				$ipIdx=DwDnsMessage::TYPE_A;
				break;
			default :
				throw new \Exception("unknow dwIp:{$dwIp->getClassName()}");
				break;
		}
		return $ipIdx;
	}
	private $myDatabase = array();
	public function addIp( DwIp $dwIp ) {
		$type = $this->getIpIdx( $dwIp );
		$dwCIDR = $dwIp->getDwCIDR();
		$ipAddress=$dwIp->toString();
		$dwInterface = $dwCIDR->getDwInterface();
		$this->addRecord( $dwInterface ,$type ,$ipAddress );
		$ipReversed = $dwIp->revDNS();
		
		$this->addDomain( $ipReversed );
		//$this->addRecord( $dwInterface ,DwDnsMessage::TYPE_PTR ,$ipReversed );
		
		$this->log->info( $ipReversed );
		//$this->log->info( $ifaceDb );
	}
	private function addRecord( DwInterface $dwInterface ,int $type ,string $data ) {
		$ifaceDb = $this->dbGetForIface( $dwInterface );
		$ifaceName = $dwInterface->getIfaceName();
		if ( ! isset( $this->myDatabase[ $ifaceName ][ $type ] ) )
			$this->myDatabase[ $ifaceName ][ $type ]= $data;
		else {
			if ( is_array( $this->myDatabase[ $ifaceName ][ $type ] ) )
				$this->myDatabase[ $ifaceName ][ $type ][] = $data;
			else {
				$anData = $this->myDatabase[ $ifaceName ][ $type ];
				$this->myDatabase[ $ifaceName ][ $type ]=array( $anData ,$data );
			}
		}
	}
	private function dbGetForIface( DwInterface $dwInterface ) : array {
		$ifaceName = $dwInterface->getIfaceName();
		if ( ! isset( $this->myDatabase[ $ifaceName ] ) ) {
			$this->myDatabase[ $ifaceName ] = array(
				DwDnsMessage::TYPE_SOA => [
					'mname' => 'mynode.darkweb.fr.' ,'rname' => 'mynode.darkweb.fr.'
					,'serial' => 2018060501
					,'refresh' => 10800
					,'retry' => 3600
					,'expire' => 605800
					,'minimum' => 3600
				]
				,DwDnsMessage::TYPE_NS => [ 'mynode.darkweb.fr' ,'darkweb.fr' ]
			);
			
		
		}
		return $this->myDatabase[ $ifaceName ];
	}
	private $myDomains = array();
	//$dwDnsDatabase->addRecord( $domain ,array( DwDnsMessage::TYPE_A => '10.90.6.209' ,DwDnsMessage::TYPE_AAAA => 'fec0:10:90:6::209' ,DwDnsMessage::TYPE_NS => [ 'mynode.darkweb.fr' ,'darkweb.fr' ] ) );
	public function addDomain( string $domain ) {
		$this->log->info( $domain );
		$this->myDomains[]=$domain;
	}
	
	// With Perplexity Production :
	public function getIfaceRecord( DwInterface $dwInterface ,string $domain ,int $type ) {
		$ifaceDb = $this->dbGetForIface( $dwInterface );
		$answerData = null;
		// On saute la gestion du point final éventuel (fully qualified .)
		$cleanDomain = rtrim($domain, '.');
		// Cherche si l'un des domaines déclarés est un **suffixe** ("se termine par") du domaine demandé
		$matched = false;
		foreach ($this->myDomains as $baseDomain) {
				if (substr_compare($cleanDomain, $baseDomain, -strlen($baseDomain)) === 0) {
					$matched = true;
					break;
				}
		}
		// Nettoyage du domaine s'il commence par un "."
////		if (substr($domain, count($domain ) - 2, 1) === ".") $domain = substr($domain, 0, count($domain ) - 1 );
//		if ( array_search( ( substr($domain, -1, 1) === "." ? substr($domain, 0, -1) : $domain ) ,$this->myDomains ) == null )
		if (!$matched)
			$this->log->info( "domain not found:{$domain}" );
		else {
			//$this->log->info( "domain found[{$type}]:{$domain}" );
			switch( $type ) {
			case DwDnsMessage::TYPE_A :
			case DwDnsMessage::TYPE_AAAA :
				if ( isset( $ifaceDb[ $type ] ) )
					$answerData = $ifaceDb[ $type ];
				$this->log->info( $answerData );
				break;
			case DwDnsMessage::TYPE_PTR :
				if ( ! is_null( $answerData ) && ! is_array( $answerData ) )
					$answerData = array( $answerData );
				foreach( $this->myDomains as $domain ) {
					if ( is_null( $answerData ) )
						$answerData=array($domain);
					else
						$answerData[]=$domain;
				}
				//$this->log->info( $answerData );
					
				break;
			case DwDnsMessage::TYPE_SRV :
			default :
				break;
			}
			
		}
//			else
//				$answerData = $this->getRecord( $domain, $type );
//		} else
//			$answerData = $this->getRecord( $domain, $type );
		return $answerData;
	}
	
	public function query( DwDnsRequest $dwDnsRequest ) : DwDnsResponse {
			$dwDnsResponse = new DwDnsResponse( $dwDnsRequest );
			$dwDnsResponse->setRcode( DwDnsMessage::RCODE_OK );
			// --------------------------------------------------
			$dwCltIp = $dwDnsRequest->getCltIp();
			$dwCIDR = $dwCltIp->getDwCIDR();
			$dwInterface = $dwCIDR->getDwInterface();
			
			$request = $dwDnsRequest->getDwDnsMessage();
			//$this->log->info( \print_r( $request, true ) );
			foreach( $request->questions as $dwDnsQuery ) {
				/*
				$dwDnsQueryname = $dwDnsQuery->name.( substr( $dwDnsQuery->name ,-1 ,1 ) === "." ? "" : "." );
				$dwDnsQuery->name = $dwDnsQueryname;
				$this->log->trace( $dwDnsQueryname );
				//*/
				//$this->log->trace( \print_r( $dwDnsQuery ,true ) );
				$isWapdRequest = ( "wpad" == strtolower( substr( $dwDnsQuery->name,0, strpos( $dwDnsQuery->name, "." ) ) ) );
				//$this->log->trace( "{$dwDnsQuery->name}:[".( $isWapdRequest ? "is wapd" :"not wapd" )."]".strtolower( substr( $dwDnsQuery->name,0, strpos( $dwDnsQuery->name, "." ) ) ) );
				//$isWpadRequest = preg_match('/^wpad(\.|$)/i', $dwDnsQuery->name);// Perplexity Production
				
				if ( DwDnsMessage::TYPE_NS == $dwDnsQuery->type || $isWapdRequest ) {
					$answerA = $this->getIfaceRecord( $dwInterface ,$isWapdRequest ? "darkweb.fr." : $dwDnsQuery->name ,DwDnsMessage::TYPE_A );
					$answerAAAA = $this->getIfaceRecord( $dwInterface ,$isWapdRequest ? "darkweb.fr." : $dwDnsQuery->name ,DwDnsMessage::TYPE_AAAA );
					//if ( ! is_null( $answerA ) && ! is_null( $answerAAAA ) ) {
						$dwDnsResponse->addQuestion( $dwDnsQuery );
						if ( ! is_null( $answerA ) )
							$dwDnsResponse->addAnswerData(
								$dwDnsQuery
								,$answerA
								,DwDnsMessage::TYPE_A
							);
						if ( ! is_null( $answerAAAA ) )
							$dwDnsResponse->addAnswerData( 
								$dwDnsQuery
								,$answerAAAA
								,DwDnsMessage::TYPE_AAAA
							);
					//}
				} else {
					$answerData = $this->getIfaceRecord( $dwInterface ,$dwDnsQuery->name ,$dwDnsQuery->type );
					if ( is_null( $answerData ) )
						$this->log->trace( $answerData );
					else {
						$dwDnsResponse->addQuestion( $dwDnsQuery );
						$dwDnsResponse->addAnswerData( $dwDnsQuery ,$answerData );
					}
					//$this->log->trace( gettype($answerData) );
					//$this->log->trace( $answerData );
				}
			}
			//$this->log->trace( $dwDnsResponse->toString() );
			return $dwDnsResponse;
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

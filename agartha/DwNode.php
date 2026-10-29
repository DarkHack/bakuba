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

namespace bakuba\kernel\agartha;
//use bakuba\kernel\agartha\DwNode as DwNode;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\process\DwProcessShell as DwProcessShell;

use bakuba\kernel\network\DwInterface as DwInterface;
use bakuba\kernel\network\DwPort as DwPort;

use bakuba\kernel\shell\DwShell as DwShell;

use bakuba\kernel\dwNode\shAgent\DwShAgent as DwShAgent;
use bakuba\kernel\dwNode\shAgent\DwShAgentOrder as DwShAgentOrder;

class DwNode extends \darkphp\serializable\aDwSerializable {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
//				,\darkphp\source\DwSource::classNameToFirstSectionName( $this->getClassName() )
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
				,$this->getUUID()
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	
	protected $upTime = "";
	protected $masterPass = "";
	protected $dwShAgent = null;
	protected $dwPhpAgent = null;
	protected $interfaces = array();
	protected $uuid = null;
	
	protected $domains = array();
	public function addDomain( string $domain = "DarkWeb.fr" ) {
		$domain = strtolower( $domain );
		if ( array_search( $domain,$this->domains ) === false ) {
			$candidates = explode(".",$domain);
			$candidates = array_reverse($candidates);
			$topLevelDomain = $candidates[1].".".$candidates[0];
			$this->domains[]=$domain;
			if ( count($candidates)==2 ) {
				$serviceKeyFile = "/etc/darksrv/ssl/{$topLevelDomain}/{$topLevelDomain}.key";
				$servicePemFile = "/etc/darksrv/ssl/{$topLevelDomain}/{$topLevelDomain}.pem";
			} else  {
				$serviceKeyFile = "/etc/darksrv/ssl/{$topLevelDomain}/_.{$topLevelDomain}.key";
				$servicePemFile = "/etc/darksrv/ssl/{$topLevelDomain}/_.{$topLevelDomain}.pem";
			}
			$this->addApache2AddToConf(
				"proxy"
				,"https"
				,$domain
				,"/"
				,$serviceKeyFile
				,$servicePemFile
			);
		}
	}
	private $dwPort = null;
	public function bindApache2ToDwPort( DwPort $dwPort ) {
		$this->dwPort = $dwPort;
	}
	private $apache2SrvConf = array();
	// $dwNode->addApache2AddToConf( $serviceType = "proxy"
	public function addApache2AddToConf(
		string $serviceType = "proxy"
		,string $serviceProto = "https"
		,string $serviceDomain = "darkweb.fr"
		,string $servicePath = "/"
		,string $serviceKeyFile = "/etc/darksrv/ssl/darkweb.fr/darkweb.fr.key"
		,string $servicePemFile = "/etc/darksrv/ssl/darkweb.fr/darkweb.fr.pem"
		,string $serviceData = null
	) {
		$this->apache2SrvConf[] = array(
			$serviceType
			,$serviceProto
			,$serviceDomain
			,$servicePath
			,$serviceData
			,$serviceKeyFile
			,$servicePemFile
		);
		$this->reloadApache2();
	}
	private function reloadApache2() {
		if ( is_object( $this->serviceHttpd ) ) {
			if ( is_object( $this->dwPort ) ) {
				foreach( $this->apache2SrvConf as $key => $serviceConf ) {
					if ( is_null( $serviceConf[4] ) ) {
						$this->apache2SrvConf[$key][4] = "http://".$this->dwPort->getIp().":".$this->dwPort->getPort();
					}
				}
			}
			$this->serviceHttpd->configure( $this->apache2SrvConf );
		}
	}
	
	private $myAgent = null;
	public function __construct(
		\bakuba\kernel\agartha\DwAgartha $parent
		,\bakuba\kernel\agartha\iDwAgent $myAgent
		,string $masterPass = null
	) {
		$this->myAgent = $myAgent;
		parent::__construct( $parent );
		$this->upTime = microtime();
		$this->masterPass = $masterPass;
		$this->log->trace(".");
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		//$this->addtoSerialize("upTime");
		//$this->addtoSerialize("masterPass");
		$this->addtoSerialize("dwShAgent");
		$this->addtoSerialize("dwPhpAgent");
		//$this->addtoSerialize("interfaces");
		$this->addtoSerialize("uuid");
	}
	public function getUUID( ) {
		return $this->myAgent->getUUID();
	}
	public function getDwFirewallScript() : string {
		$shFirewall = "#!/bin/sh\n";
		$shFirewall .="# Close standard input file descriptor\n";
		$shFirewall .="exec 0<&-\n";
		$shFirewall .="# Close standard output file descriptor\n";
		$shFirewall .="exec 1<&-\n";
		$shFirewall .="# Close standard error file descriptor\n";
		$shFirewall .="exec 2<&-\n";
		$shFirewall .="# Open standard output as \$LOG_FILE file for read and write.\n";
		$shFirewall .="exec 1<>/var/log/dwFirewall.log\n";
		$shFirewall .="# Redirect standard error to standard output\n";
		$shFirewall .="exec 2>&1\n";
		$shFirewall .="printenv >> /var/log/dwFirewallPrintenv.log 2>&1\n";
		$shFirewall .="env >> /var/log/dwFirewallEnv.log 2>&1\n";
		$shFirewall .="set >> /var/log/dwFirewallSet.log 2>&1\n";
		$shFirewall .= DwShell::getScript("which/base")."\n";
		$shFirewall .= DwShell::getScript("lib-sh/shLog")."\n";
		$shFirewall .= "shLog \$shLog_TRACE \"Firewall start \$*\" >&2\n";
		$shFirewall .= DwShell::getScript("which/ipTools")."\n";
		$shFirewall .= DwShell::getScript("shNet/shIpTools")."\n";
		$shFirewall .= DwShell::getScript("shNet/shIpToolsTest")."\n";
		return $shFirewall;
	}
	
	public function createShAgent() : DwShAgent {
		$dwShAgent = DwShAgent::createShAgent( $this );
		
		$dwShAgent->fileWrite(
			new DwCallBack( array( &$this ,'endOP' ) )
            ,"/etc/network/if-up.d/dwFirewall"
            ,$this->getDwFirewallScript()
		);
		$dwShAgent->addOrder( DwShAgentOrder::create(
			new DwCallBack( array( &$this ,'serviceList' ) )
			,"ls /etc/bakuba"
		) );
		$this->setShAgent($dwShAgent);
		
		return $dwShAgent;
	}
	public function endOP( ) {
    }
	
	private $serviceHttpd = null;
	private $serviceNamedExt = null;
	private $serviceNamedInt = null;
	private $serviceDhcpd = null;
	private $serviceDhcpd6 = null;
	private $serviceMiniupnpd = null;
	private $serviceOpenvpn = null;
	private $serviceInTftfpd = null;
	public function serviceList( string $services ) {
		global $dwBakuba;
		foreach( explode( "\n" ,$services ) as $service ) {
			switch( $service ) {
			case "httpd" :
				$this->serviceHttpd = \bakuba\kernel\apache2\ServiceHttpd::create( $this );
				$this->reloadApache2();
				break;
			case "named-ext" :
			case "named-int" :
			case "dhcpd" :
			case "dhcpd6" :
			case "miniupnpd" :
			case "openvpn" :
			case "in.tftpd" :
			default :
			break;					
			}
		}
	}
	public function setShAgent( DwShAgent $dwShAgent ) {
		$this->dwShAgent = $dwShAgent;
	}
	public function getShAgent() {
		return $this->dwShAgent;
	}
	public function getMasterPass() {
		return $this->masterPass;
	}
	public function setPhpAgent( DwPhpAgent $dwPhpAgent ) {
		$this->dwPhpAgent = $dwPhpAgent;
	}
	public function getPhpAgent() {
		return $this->dwPhpAgent;
	}
	public function getUpTime() {
		return $this->upTime;
	}
	/*
	private $bakuba;
	public function getBakuba() {
		return $this->bakuba;
	}
	*/
	private static $nodes = array();
	public static function getNodes( ) : array {
		return self::$nodes;
	}
	public function isMaster() {
		return true;//is_null($this->dwControllerClient) ;
	}
	public function haveParent() {
		return false;//$this->dwControllerClient != null;
	}
	// =========================================== NETWORK
	///*
	private function createInterface( string $ifaceName="lo" ) : DwInterface {
		$this->log->warn( $ifaceName );
		$dwInterface = null;
		if ( isset( $this->interfaces[ $ifaceName ] ) ) 
			$dwInterface = $this->interfaces[ $ifaceName ];
		else {
			$dwInterface = new DwInterface( $this->parent ,$ifaceName );
			//$this->insert( $dwInterface );
			$this->interfaces[ $ifaceName ] = $dwInterface;
		}
		return $dwInterface;
	}
	//*/
	public function getInterface( $ifaceNum ) : DwInterface {
		if ( ! isset( $this->interfaces[ $ifaceNum ] ) ) {
			$dwInterface = $this->createInterface( $ifaceNum );
			$this->interfaces[ $ifaceNum ] = $dwInterface;
		}
		return $this->interfaces[ $ifaceNum ];
	}
	public function getInterfaceByName( $ifName ) : DwInterface {
		$dwInterface = null;
		$ifaceName = \trim( $ifName );
		//$this->log->info( "'$ifaceName'/'$ifName'" );
		foreach( $this->interfaces as $anDwInterface )
			if ( $anDwInterface->getIfaceName() == $ifaceName ) {
				$dwInterface = $anDwInterface;
				break;
			} //else 
			//	$this->log->info( "'$ifaceName' != '".$anDwInterface->getIfaceName()."'" );
		return $dwInterface;
	}
	public function getInterfaces() {
		return $this->interfaces;
	}
	private $onIpv4Found = array();
	public function onFoundIp4( DwCallBack $dwCallBack ) {
		$this->onIpv4Found[] = $dwCallBack;
	}
	
	private $onGwIpv4Found = array();
	public function onFoundIp4Gw( DwCallBack $dwCallBack ) {
		$this->onGwIpv4Found[] = $dwCallBack;
	}
	private $onIpv6Found = array();
	public function onFoundIp6( DwCallBack $dwCallBack ) {
		$this->onIpv6Found[] = $dwCallBack;
	}
	
	private $onGwIpv6Found = array();
	public function onFoundIp6Gw( DwCallBack $dwCallBack ) {
		$this->onGwIpv6Found[] = $dwCallBack;
	}
	private $onDNS4Found = array();
	public function onFoundDns4( DwCallBack $dwCallBack ) {
		$this->onDNS4Found[] = $dwCallBack;
	}
	private $onDNS6Found = array();
	public function onFoundDns6( DwCallBack $dwCallBack ) {
		$this->onDNS6Found[] = $dwCallBack;
	}
	
	public function scanInterfaces() {
		$this->log->info(".");
		if ( \method_exists( $this->myAgent, 'scanRoutes' ) ) {
			$this->myAgent->scanInterfaces( new DwCallBack( array( &$this, "foundWinInterface" ) ) );
			$this->myAgent->scanRoutes( new DwCallBack( array( &$this, "foundIp" ) ) );
		} else {
			$this->myAgent->scanInterfaces( new DwCallBack( array( &$this, "foundIp" ) ) );
		}
		$this->myAgent->scanDns( new DwCallBack( array( &$this, "foundDns" ) ) );
	}
	public function foundWinInterface( $ifaceNum ,$ifaceName ,$ifaceMetric ,$ifaceMtu ,$ifaceState ) {
		$dwInterface = new \bakuba\kernel\network\DwInterfaceWin( $this->parent ,\trim($ifaceName) );
		$dwInterface->setIfaceIdx( $ifaceNum );
		$dwInterface->setMetric( $ifaceMetric );
		$dwInterface->setMtu( $ifaceMtu );
		$dwInterface->setState( $ifaceState );
		$this->interfaces[ $ifaceNum ] = $dwInterface;
		$this->log->info( $dwInterface );
	}
	public function foundDns( array $listDnsFounded, bool $ipv6 ) {
		$this->log->trace( $ipv6 ? "ipv6" : "ipv4" );
		//$this->log->trace( $listDnsFounded );
		foreach( $listDnsFounded as $dnsFounded ) {
			//$this->log->trace( $dnsFounded );
			$dwInterface = $this->getInterfaceByName( $dnsFounded['name'] );
			if ( isset( $dnsFounded['prefix'] ) ) $dwInterface->setPrefix( $dnsFounded['prefix'] );
			if ( $ipv6 ) {
				if ( isset( $dnsFounded['type'] ) ) 
					$dwInterface->setConf6Type( $dnsFounded['type'] );
				foreach( $dnsFounded['server'] as $ip6dns )
					$dwInterface->addDns6Server( $ip6dns );
			}
			else {
				if ( isset( $dnsFounded['type'] ) ) 
					$dwInterface->setConfType( $dnsFounded['type'] );
				foreach( $dnsFounded['server'] as $ip4dns )
					$dwInterface->addDnsServer( $ip4dns );
			}
				
			//$this->log->info( $dnsFounded );
			//$this->log->info( $dnsFounded['server'] );
		}
	}
	public function overrideDns( \bakuba\kernel\network\DwPort $dwPort, \bakuba\kernel\objects\DwCallBack $callback) {
		$this->log->trace( $dwPort );
		$this->myAgent->overrideDns( $dwPort ,$callback );
	}
	public function foundIp(
		string $ifaceName
		,string $ipResult
		,bool $isIpGw = false
		,bool $isIpv6 = false
	) {
		$ipResult=\trim( $ipResult );
		if ( empty( $ipResult ) || 0 == \strlen( $ipResult ) )
			$this->log->error( "'$ifaceName' no ipResult:$ipResult" );
		else {
			$dwInterface = $this->getInterface( $ifaceName );
			$this->log->info( $ifaceName );
			$this->log->info( $ipResult );
			$dwIp = null;
			if ( $isIpv6 ) {
				if ( $isIpGw ) {
					$dwCidr6 = $dwInterface->getCIDR( $ipResult );
					if ( \is_null( $dwCidr6 ) )
						$dwCidr6 = $dwInterface->createCIDR6( $ipResult."/64" );
					$dwIp = $dwCidr6->addGw( $ipResult );
					foreach( $this->onGwIpv6Found as $dwCallBack ) 
						$dwCallBack->callback( $dwInterface ,$dwIp );
				} else {
					foreach( \explode( ' ',$ipResult ) as $anIpResult ) {
						if ( empty( $anIpResult ) || \strlen( $anIpResult ) == 0 )
							continue;
						list( $ip, $mask ) = \explode( '/', $anIpResult );
						$dwCidr6 = $dwInterface->getCIDR( $ip );
						if ( \is_null( $dwCidr6 ) )
							$dwCidr6 = $dwInterface->createCIDR6( $anIpResult );
						$dwIp = $dwCidr6->getIp( $ip );
						foreach( $this->onIpv6Found as $dwCallBack ) 
							$dwCallBack->callback( $dwInterface ,$dwIp );
					}
				}
			} else {
				if ( $isIpGw ) {
					$dwCidr = $dwInterface->getCIDR( $ipResult );
					if ( \is_null( $dwCidr ) )
						$dwCidr = $dwInterface->createCIDR( $ipResult."/24" );
					$dwIp = $dwCidr->addGw( $ipResult );
					foreach( $this->onGwIpv4Found as $dwCallBack ) 
						$dwCallBack->callback( $dwInterface ,$dwIp );
				} else {
					foreach( \explode( ' ',$ipResult ) as $anIpResult ) {
						if ( empty( $anIpResult ) || \strlen( $anIpResult ) == 0 )
							continue;
						list( $ip, $mask ) = \explode( '/', $anIpResult );
						$dwCidr = $dwInterface->getCIDR( $ip );
						if ( \is_null( $dwCidr ) )
							$dwCidr = $dwInterface->createCIDR( $ipResult );
						$dwIp = $dwCidr->getIp( $ip );
						foreach( $this->onIpv4Found as $dwCallBack ) 
							$dwCallBack->callback( $dwInterface ,$dwIp );
					}
				}
			}
		}
	}
	// ====================================== ORDERS ==============================
	/*
	private $orders = array();
	public function getOrders() {
		$servicesOrders = array();
		foreach( $this->getServices() as $dwService )
			$servicesOrders[$dwService->getServiceKey()] = $dwService->getOrders();
		return $servicesOrders;
	}
	public function executeOrders( $orders ) {
		foreach( $orders as $servicesKey => $servicesOrders ) {
			foreach( $this->getServices() as $dwService ) {
				if ( $dwService->getServiceKey() == $servicesKey )
					$dwService->executeOrders( $servicesOrders );
			}
		}
	}
	//*/
	// ======================================= GetClassName
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

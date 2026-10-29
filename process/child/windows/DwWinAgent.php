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

namespace bakuba\kernel\process\child\windows;
//use bakuba\kernel\process\child\windows\DwWinAgent as DwWinAgent;

use bakuba\kernel\network\DwIp6 as DwIp6;
use bakuba\kernel\network\DwIp4 as DwIp4;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwWinAgent extends \bakuba\kernel\process\child\DwLocalAgent {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		$className=$this->getClassName();
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
				,/*time()."-".*/\darkphp\source\DwSource::classNameToTopName( $className )
				,\darkphp\source\DwSource::classNameToTopName( $className )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	public function getMyLog() : \darkphp\logger\iDwLogger {
		return $this->log;
	}
	public function getMyResources() : \bakuba\kernel\process\DwResources {
		return $this->parent;
	}
	private $localShell;
	private $localRandomizer;
	private $localCscriptRandomizer;
	private $localCrypto;
	
	private $localUUID;
	public function getUUID( ) : string {
		return $this->localUUID;
	}
	
	public function __construct(
		\bakuba\kernel\process\DwResources $dwResources
	) {
		parent::__construct( $dwResources );
		$this->log->trace( \getenv() );
		
		$this->localShell = new DwCmd( $this );
		$this->localRandomizer = new DwCmdRandomizer( $this );
		$this->localCscriptRandomizer = new DwCscriptRandomizer( $this );
		$this->localCrypto = new DwCmdCrypto( $this );
		
		$this->localUUID="'not yet defined'";
		$this->localShell->exec('C:\Windows\System32\wbem\wmic.exe csproduct get UUID', new DwCallBack( array( &$this ,'hostUUIDreaded' ) ) );
	}
	public function hostUUIDreaded( string $data ): void {
		$uuid="";
		if ( preg_match(
			'/(([A-Z0-9]+\-){4}[A-Z0-9]+)/m'
			,$data
			,$matches
		) )// {
		//	$this->log->info( print_r( $matches ,true ) );
			$uuid = trim($matches[1]);
		//}
		
		$this->log->trace( $uuid );
		if ( $uuid == '') {
			$this->log->warn('echec on read host UUID');
			$this->localUUID="'echec defined'";
		} else
			$this->localUUID=$uuid;
	}
	public function overrideDns(\bakuba\kernel\network\DwPort $dwPort, \bakuba\kernel\objects\DwCallBack $callback) {
		//$dwIp = $dwPort->getIp(); // L'IP du serveur que tu veux mettre en primaire
		//$this->log->trace( $dwIp->getClassName() );
		if ( \bakuba\kernel\network\DwIp6::getStaticClassName() == $dwPort->getIp()->getClassName() )// {
		//	$this->log->trace( "[{$this->localUUID}] override6Dns " );
			$this->override6Dns( $dwPort , $callback );
		//} 
		else //{
			//$this->log->trace( "[{$this->localUUID}] override4Dns " );
			$this->override4Dns( $dwPort , $callback );
		//}
		
	}
	// Perplexity.ai contribution netsh command reconfigure dns interface windows
	private function override6Dns(\bakuba\kernel\network\DwPort $dwPort, \bakuba\kernel\objects\DwCallBack $callback) {
		$this->log->trace( "[{$this->localUUID}] =========== " );
		$dwIp = $dwPort->getIp(); // L'IP du serveur que tu veux mettre en primaire
		$ipAdress = $dwIp->getIpAddress();
		$dwCIDR = $dwIp->getDwCIDR();
		$dwInterface = $dwCIDR->getDwInterface();
		
		$this->log->trace( "[{$this->localUUID}] =========== DNS 6 " );
		$oldSrvDns = $dwInterface->getDns6Servers();
		$allCmdForMergeDns="";
		if ( isset( $oldSrvDns[0] ) && $oldSrvDns[0] != $ipAdress ) {
			$newSrvDns = array( /*'2001:4860:4860::8888' ,*/$ipAdress ); // L'adresse IP à mettre en primaire
			
			foreach( $oldSrvDns as $key => $dns6Server ) //{
				//$this->log->trace( "key:$key" );$this->log->trace( $dns4Server );
				if ( $ipAdress != $dns6Server )
					$newSrvDns[]=$dns6Server;
			//}
			
			$ifaceName = $dwInterface->getIfaceName(); // Nom de l'interface sous Windows
			$this->log->trace( $dwInterface->getIfaceIdx().":".$ifaceName );
			
			$anLogger = $this->log;
			$anLogger->trace( print_r( array(
				"oldSrvDns" => $oldSrvDns
				,"newSrvDns" => $newSrvDns
			) ,true ) );
			
			$idx=0;
			foreach( $newSrvDns as $ipSrvDns ) {
				$idx++;
				//$paramIface=\escapeshellarg( $ifaceName );
				if ( $idx == 1 )
					$cmdForMergeDns="netsh interface ipv6 set dnsservers \"{$ifaceName}\" static {$ipSrvDns}".\PHP_EOL;
//					$cmdForMergeDns="netsh interface ipv6 set dnsservers name={$paramIface} source=static address={$ipSrvDns} register=primary validate=yes";
//					$cmdForMergeDns="netsh interface ipv6 set dns name=\"{$ifaceName}\" static {$ipSrvDns} primary";
				else
					$cmdForMergeDns="netsh interface ipv6 add dnsservers \"{$ifaceName}\" {$ipSrvDns} {$idx}".\PHP_EOL;
//					$cmdForMergeDns="netsh interface ipv6 add dnsservers name={$paramIface} address={$ipSrvDns} index={$idx}";
//					$cmdForMergeDns="netsh interface ipv6 add dns name=\"{$ifaceName}\" {$ipSrvDns} index={$idx}";
				
				
				//$allCmdForMergeDns.=$cmdForMergeDns;
				
				//$debugMsg="[{$this->localUUID}] merge dns ip 6 '$ipSrvDns':{$cmdForMergeDns}".\PHP_EOL.\bin2hex( $cmdForMergeDns );
				$anLogger->trace( "[{$this->localUUID}] merge dns6 exec:{$cmdForMergeDns}" );
				//$this->log->trace( "${cmdForMergeDns}" );
				$this->log->trace( \shell_exec("${cmdForMergeDns}") );
				//$this->log->trace( \shell_exec('runas /user:Administrateur "${cmdForMergeDns}"') );
				//shell_exec('runas /user:Administrateur "netsh interface ipv6 set dnsservers \"Ethernet\" static 2001:4860:4860::8888"');
				///*
				
				//*/
			}
			/*
			$runTime = (new \DateTime('+1 minute'))->format('H:i');
			$tmpBatch = \sys_get_temp_dir();
			$regularIfaceName = \preg_replace('/[^a-zA-Z0-9_-]/', '_', $ifaceName );
			$batFile = "{$tmpBatch}".\T_NS_SEPARATOR."ns6Set-".$regularIfaceName.".bat";
			$debugMsg="[{$this->localUUID}] merge dns ip 6 '$ipSrvDns':{$batFile}".\PHP_EOL."{$allCmdForMergeDns}".\PHP_EOL.\bin2hex( $allCmdForMergeDns );
			\file_put_contents($batFile, $allCmdForMergeDns);
			
			$taskName="SetDNS-{$regularIfaceName}";
			$anLogger->trace( "{$taskName}:{$debugMsg}" );
			$this->localShell->exec( 'schtasks /Create /SC ONCE /TN '. $taskName .' /TR "cmd.exe /c ' . $batFile . '" /ST '.$runTime.' /RL HIGHEST /F' );
			$this->localShell->exec(
				"schtasks /Query /TN $taskName"
				,new DwCallBack(
					function( string $result) use ( $anLogger ) {
						$anLogger->trace( "check before run:{$result}" );
					}
				)
			);
			$this->localShell->exec( 'schtasks /Run /TN '. $taskName .'' );
			$this->localShell->exec(
				"schtasks /Query /TN $taskName"
				,new DwCallBack(
					function( string $result) use ( $anLogger ) {
						$anLogger->trace( "check after run:{$result}" );
					}
				)
			);
			$this->localShell->exec( 'schtasks /Delete /TN '. $taskName .' /F' );
			$this->localShell->exec(
				"schtasks /Query /TN $taskName"
				,new DwCallBack(
					function( string $result) use ( $anLogger ) {
						$anLogger->trace( "check after del:{$result}" );
					}
				)
			);
			//*/
		}
	}
	// Perplexity.ai contribution netsh command reconfigure dns interface windows
	private function override4Dns(\bakuba\kernel\network\DwPort $dwPort, \bakuba\kernel\objects\DwCallBack $callback) {
		$this->log->trace( "[{$this->localUUID}] =========== override4Dns " );
		$dwIp = $dwPort->getIp(); // L'IP du serveur que tu veux mettre en primaire
		$ipAdress=$dwIp->getIpAddress();
		$dwCIDR = $dwIp->getDwCIDR();
		$dwInterface = $dwCIDR->getDwInterface();
		
		$oldSrvDns = $dwInterface->getDnsServers();
		if ( isset( $oldSrvDns[0] ) && $oldSrvDns[0] != $ipAdress ) {
			$this->log->trace( "[{$this->localUUID}] =========== SWITCH DNS 4 " );
			$newSrvDns = array( $ipAdress ); // L'adresse IP à mettre en primaire
			foreach( $oldSrvDns as $dns4Server ) //{
				//$this->log->trace( "key:$key" );$this->log->trace( $dns4Server );
				if ( $ipAdress != $dns4Server )
					$newSrvDns[]=$dns4Server;
			//}
			
			$ifaceName = $dwInterface->getIfaceName(); // Nom de l'interface sous Windows
			$this->log->trace( $dwInterface->getIfaceIdx().":".$ifaceName );
			$anLogger = $this->log;
			$anLogger->trace( print_r( array(
				"oldSrvDns" => $oldSrvDns
				,"newSrvDns" => $newSrvDns
			) ,true ) );
			///*
			
			$idx=0;
			foreach( $newSrvDns as $ipSrvDns ) {
				$idx++;
				$debugMsg="[{$this->localUUID}] merge dns ip $ipSrvDns ";
				if ( $idx == 1 )
					$this->localShell->exec(
						"netsh interface ipv4 set dns name=\"{$ifaceName}\" static {$ipSrvDns} primary"
						,new DwCallBack(
							function( string $result) use ($anLogger ,$debugMsg) {
								$anLogger->trace( $debugMsg );
								$anLogger->trace( $result );
							}
						)
					);
				else
					$this->localShell->exec(
						"netsh interface ipv4 add dns name=\"{$ifaceName}\" {$ipSrvDns} index={$idx}"
						,new DwCallBack(
							function( string $result) use ($anLogger ,$debugMsg) {
								$anLogger->trace( $debugMsg );
								$anLogger->trace( $result );
							}
						)
					);
			}
		}
	}

	///*
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
	}
	//*/
	public function scanInterfaces( DwCallBack $dwCallBack ) : void {
		//$this->log->trace( \getenv() );
		$this->localShell->exec( "netsh int ipv4 show interfaces", new DwCallBack( array( &$this, 'interfaceScanned') ,$dwCallBack ) );
		$this->localShell->exec( "netsh int ipv6 show interfaces", new DwCallBack( array( &$this, 'interfaceScanned') ,$dwCallBack ) );
	}
	public function interfaceScanned( DwCallBack $dwCallBack ,string $result ) : void {
		$this->log->info( $result );
		\preg_match_all(
			'/[\ ]+([0-9]{1,3})[\ ]+([0-9]{1,3})[\ ]+([0-9]{1,10})[\ ]+(\w{1,12})[\ ]+(.*)/'
			,$result ,$matches ,PREG_SET_ORDER ,0
		);
		foreach( $matches as $dataIface ) {// num ,name ,metric ,mtu ,state
			//new \bakuba\kernel\network\DwInterfaceWin( )
			$dwCallBack->callback( $dataIface[1] ,$dataIface[5] ,$dataIface[2] ,$dataIface[3] ,$dataIface[4] );
		}
	}
	public function scanRoutes( DwCallBack $dwCallBack ) : void {
		$this->log->trace('.');
		$this->localShell->exec( "route print if", new DwCallBack( array( &$this, 'routeScanned') ,$dwCallBack ) );
	}
	public function routeScanned( DwCallBack $dwCallBack ,string $result ) : void {
		\preg_match_all(
			'/[\ ]+([0-9]*)\.{3}([a-f0-9]{0,2}[\ \.]+[a-f0-9]{0,2}[\ \.]+[a-f0-9]{0,2}[\ \.]+[a-f0-9]{0,2}[\ \.]+[a-f0-9]{0,2}[\ \.]+[a-f0-9]{0,2})[\ \.]{1,}(.*)[\ \n]*/'
			,$result ,$matches ,PREG_SET_ORDER ,0
		);
		foreach( $matches as $match ) {
			if ( ! isset( $match[1] ) || ! isset( $match[2] ) || ! isset( $match[3] ) )
				continue;
			$ifNum=\trim($match[1]);
			$ifMac=\trim($match[2]);
			$ifName=\trim($match[3]);
			$this->log->trace( "num:{$ifNum} name:{$ifName} mac:{$ifMac}" );
		}
		$this->localShell->exec( "netsh int ipv4 show route", new DwCallBack( array( &$this, 'routePrinted') ,$dwCallBack ) );
		$this->localShell->exec( "netsh int ipv6 show route", new DwCallBack( array( &$this, 'route6Printed') ,$dwCallBack ) );
	}
	public function routePrinted( DwCallBack $dwCallBack ,string $result ) : void {
		//$this->log->trace( $result );
		\preg_match_all(
			'/([A-Z].*)[ ]{6}([A-Z].*)[ ]{3}([0-9]+)[ ]+'.DwIp4::$ipv4RegExp.'[\/]([0-9]+)[ ]+([0-9]+)[ ]+(.*)[ \n]*/'
			,$result ,$matches ,PREG_SET_ORDER ,0
		);
		$ipToAdd = array();
		foreach( $matches as $match ) {
			if ( ! isset( $match[1] ) || ! isset( $match[2] ) || ! isset( $match[3] )  || ! isset( $match[4] ) || ! isset( $match[5] ) || ! isset( $match[6] ) || ! isset( $match[7] ) )
				continue;
			$routePublished=\trim($match[1]);
			$routeOwner=\trim($match[2]);
			$routeMetric=\trim($match[3]);
			$routeDest=\trim($match[4]);
			$routeMask=\trim($match[5]);
			$routeInterfaceNum=\trim($match[6]);
			$routeInterfaceName=\trim($match[7]);
			if ( ! isset( $ipToAdd[ $routeInterfaceNum ] ) )
				$ipToAdd[ $routeInterfaceNum ] = array( $routeDest ,$routeMask );
			$this->log->trace( "\r\n\tpublish:{$routePublished} owner:{$routeOwner} metric:{$routeMetric} dest:{$routeDest} mask:{$routeMask} numFace:{$routeInterfaceNum} nameFace:{$routeInterfaceName}" );
			if ( $routeMask == 32 ) {
				if ( \substr( $routeDest, -4 ) == ".255")
					continue;
				else {
					$this->getDwResources()->addTick(
						new DwCallBack(
							function ( DwCallBack $dwCallBack ,$ifaceNum ,$ipAddress ) {
								$dwCallBack->callback( $ifaceNum ,$ipAddress, false ,false );
							}
							,$dwCallBack
							,$routeInterfaceNum
							,"{$routeDest}/".$ipToAdd[ $routeInterfaceNum ][1]
						)
					);
				}
			} else {
				if ( $routeMask == 0 )
					$this->log->trace( "gw: {$routeDest} => {$routeInterfaceName}" );
				else
					$ipToAdd[ $routeInterfaceNum ][1]=$routeMask;
			}
		}
	}
	// With Perplexity Production :
	public function route6Printed( DwCallBack $dwCallBack ,string $result ) : void {
		//$this->log->trace( $result );
		
//			'/([A-Z].*)[ ]{6}([A-Z].*)[ ]{3}([0-9]+)[ ]+((([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])))[\/]([0-9]+)[ ]+([0-9]+)[ ]+(.*)[\n]*/'
		if (\preg_match_all(
			'/([A-Z].*)[ ]{6}([A-Z].*)[ ]{3}([0-9]+)[ ]+'.DwIp6::$ipv6RegExp.'[\/]([0-9]+)[ ]+([0-9]+)[ ]+(.*)[\n]*/'
			,$result ,$matches ,PREG_SET_ORDER ,0
		) ) {
			//$ipToAdd = array();
			foreach( $matches as $match ) {
				//$this->log->trace( $match );
				
				$routePublished=\trim($match[1]);
				$routeOwner=\trim($match[2]);
				$routeMetric=\trim($match[3]);
				
				$routeDest=\trim($match[4]);
				$routeMask=\trim($match[5]);
				
				$routeInterfaceNum=\trim($match[6]);
				$routeInterfaceName=\trim($match[7]);
				
				if ($routeMask != "128" && $routeMask != "64") {
					$this->log->warn( "Invalide ipv6 :".print_r( $match ,true ) );
					continue; // filtre les routes très spécifiques ou trop larges
				}
				//if ( ! isset( $ipToAdd[ $routeInterfaceNum ] ) )
				//	$ipToAdd[ $routeInterfaceNum ] = array( $routeDest ,$routeMask );
				$this->log->trace( "\r\n\tpublish:{$routePublished} owner:{$routeOwner} metric:{$routeMetric} dest:{$routeDest} mask:{$routeMask} numFace:{$routeInterfaceNum} nameFace:{$routeInterfaceName}" );
				
				$this->getDwResources()->addTick(
					new DwCallBack(
						function ( DwCallBack $dwCallBack ,$ifaceNum ,$ip6Address ) {
							$dwCallBack->callback( $ifaceNum ,$ip6Address, false ,true );
						}
						,$dwCallBack
						,$routeInterfaceNum
//						,"{$routeDest}/".$ipToAdd[ $routeInterfaceNum ][1]
						,"{$routeDest}/".( $routeDest	== '::1' ? '128' : '64' )
					)
				);
			}
		}
	}
	public function scanDns( DwCallBack $dwCallBack ) : void {
		$this->localShell->exec( "netsh interface ipv4 show dnsservers", new DwCallBack( array( &$this, 'dnsPrinted') ,$dwCallBack ,false ) );
		$this->localShell->exec( "netsh interface ipv6 show dnsservers", new DwCallBack( array( &$this, 'dnsPrinted') ,$dwCallBack ,true ) );
	}
	
	// With PerplexiTy Production
	public function dnsPrinted( DwCallBack $dwCallBack ,bool $ipv6 ,string $result ) : void {
		$rexExpIfName='(?:Configuration.*interface.*\ (.+)\ .)';
		$rexExpIfDns='(?:\ +Serveurs\ DNS.*\ (\w+).*\ +)((.*\n?){1,3})';
		$rexExpIfPrefix='(?:\ +Enregistrer.*suffixe\ \:\ +(.+))';
		
		$dnsFounded=array();
		foreach( \darkphp\string\DwString::explodeByTwoNewLine( $result ) as $ifaceStrGrp ) {
			//$this->log->trace( $ifaceStrGrp );
			$dataDns=array( 'server' => array() );
			
			$grpMatches=null;
			if ( preg_match_all(
				"/$rexExpIfName/m"
				,$ifaceStrGrp
				,$grpMatches
				,PREG_SET_ORDER
				,0
			) >= 0 ) {
				//$this->log->trace( "rexExpIfName:" .print_r( $grpMatches , true ) );
				foreach( $grpMatches as $matches ) {
					$dataDns['name']=$matches[1];
				}
			}
			$grpMatches=null;
			if ( preg_match_all(
				"/$rexExpIfDns/m"
				,$ifaceStrGrp
				,$grpMatches
				,PREG_SET_ORDER
				,0
			) >= 0 ) {
				//$this->log->trace( "rexExpIfDns:" .print_r( $grpMatches , true ) );
				foreach( $grpMatches as $matches ) {
					$dataDns['type']=$matches[1];
					if ( ! empty( $matches[2] ) ) {
						foreach ( explode( "\n" ,$matches[2] ) as $anLineWithIp ) {
							//$this->log->trace( "anLineWithIp:" .$anLineWithIp );
							if ( preg_match_all(
								$ipv6 ?
								'/'.DwIp6::$ipv6RegExp.'.*/m'
								:
								'/'.DwIp4::$ipv4RegExp.'.*/m'
								,$anLineWithIp
								,$matchesIP
								,PREG_SET_ORDER
								,0
							) >= 0 ) {
								//$this->log->trace( print_r( $matchesIP ,true ) );
								foreach( $matchesIP as $foundedIP ) {
									//$this->log->trace( "Dns SRV:".$foundedIP[1] );
									$dataDns['server'][]=$foundedIP[1];
								}
							}
						}
					}
				}
			}
			$grpMatches=null;
			if ( preg_match_all(
				"/$rexExpIfPrefix/m"
				,$ifaceStrGrp
				,$grpMatches
				,PREG_SET_ORDER
				,0
			) >= 0 ) {
				//$this->log->trace( "rexExpIfPrefix:" .print_r( $grpMatches , true ) );
				foreach( $grpMatches as $matches ) {
					$dataDns['prefix']=$matches[1];
				}
			}
			$dnsFounded[]=$dataDns;
		}
		//$this->log->trace( "dnsFounded:".print_r( $dnsFounded ,true ) );
		$this->getDwResources()->addTick(
			new DwCallBack(
				function ( DwCallBack $dwCallBack ,$dnsFounded,$ipv6 ) { 
					$dwCallBack->callback( $dnsFounded,$ipv6 );
				},$dwCallBack ,$dnsFounded,$ipv6
			)
		);
	}
	/*
	public function ipLinkListed( DwCallBack $dwCallBack ,string $result ) : void {
		foreach( \explode( "[{$this->localUUID}] " ,$result ) as $ifaceName ) {
			if ( ! empty( $ifaceName ) ) {
				$dwCallBack->addParam( $ifaceName );
				$this->scanIp( $dwCallBack ,$ifaceName );
			}
		}
	}
	private function scanIp(
		DwCallBack $dwCallBack
		,string $ifaceName
	) : void {
		$this->localShell->exec(
			"ip -4 addr show dev {$ifaceName} | grep inet | cut -d' ' -f6 | tr '\n' ' '"
			,new DwCallBack( array( &$this ,"foundIp4" ) ,$dwCallBack )
		);
		$this->localShell->exec(
			"ip -4 route show dev {$ifaceName} | grep via | cut -d' ' -f3 | tr -d ' ' | tr '\n' ' '"
			,new DwCallBack( array( &$this ,"foundIpGw4" ) ,$dwCallBack )
		);
		$this->localShell->exec(
			"ip -6 addr show dev {$ifaceName} | grep inet6 | cut -d' ' -f6 | tr '\n' ' '"
			,new DwCallBack( array( &$this ,"foundIp6" ) ,$dwCallBack )
		);
		$this->localShell->exec(
			"ip -6 route show | grep \"dev {$ifaceName} \" | grep via | cut -d' ' -f3,5 | tr ' ' '_' | tr '\n' ' '"
			,new DwCallBack( array( &$this ,"foundIpGw6" ) ,$dwCallBack )
		);
	}
	public function foundIp4( DwCallBack $dwCallBack ,string $result ) : void {
		foreach( explode(" ", $result) as $cidrWithIp )
			if (empty($cidrWithIp)) continue;
			else $dwCallBack->callback( $cidrWithIp );
	}
	public function foundIpGw4( DwCallBack $dwCallBack ,string $result ) : void {
		foreach( explode(" ", $result ) as $ip )
			if ( empty( $cidrWithIp ) ) continue;
			else $dwCallBack->callback( $ip ,true );
	}
	public function foundIp6( DwCallBack $dwCallBack ,string $result ) : void {
		foreach( explode(" ", $result) as $cidrWithIp )
			if (empty($cidrWithIp)) continue;
			else $dwCallBack->callback( $cidrWithIp ,false ,true );
	}
	public function foundIpGw6( DwCallBack $dwCallBack ,string $result ) : void {
		foreach( explode(" ", $result ) as $gwWithDev ) {
			if ( empty($gwWithDev) ) continue;
			else $dwCallBack->callback( $gwWithDev ,true ,true );
	}
	public function foundIp4Gw( 
		DwProcessShell $dwProcessShell
		,DwInterface $dwInterface
		,\bakuba\kernel\network\DwIp $viaDwIp
		,string $result
	) : void {
	}
	//*/
	private $buffCbRandom = array();
	public function random( int $length , DwCallBack $dwCallBack ) : void {
		//$this->log->trace( $dwCallBack );
		$len1 = intval($length / 2);
		$len2 = $length - $len1;
		$this->localRandomizer->random($len1, $dwCallBack);
		$this->localCscriptRandomizer->random($len2, $dwCallBack);
	}
	public function randomized( DwCallBack $dwCallBack ,string $data ) {
		$cbKey = $dwCallBack->getInstanceKey();
		if ( ! isset( $this->buffCbRandom[ $cbKey ] ) )
			$this->buffCbRandom[ $cbKey ]=$data;
		else {
			$totalData = $this->buffCbRandom[ $cbKey ].$data;
			unset( $this->buffCbRandom[ $cbKey ] );
			$dwCallBack->callback( $totalData );
			
		}
	}
	public function listCiphers( DwCallBack $dwCallBack  ) : void {
		$this->log->trace( "." );
		$this->localCrypto->listCiphers( $dwCallBack );
	}
	public function listDigests( DwCallBack $dwCallBack  ) : void {
		$this->log->trace( "." );
		$this->localCrypto->listDigests( $dwCallBack );
	}
	public function initMessage( DwCallBack $dwCallBack ,array $ciphers = null ,array $digests = null ) : void {
		$this->log->trace( "." );
		$this->localCrypto->initMessage( $dwCallBack ,$ciphers ,$digests );
	}
	public function encrypt( DwCallBack $dwCallBack ,\bakuba\kernel\crypto\json\DwJsonCrypto $dwJsonCrypto ) : void {
		$this->log->trace( "." );
		$this->localCrypto->encrypt( $dwCallBack ,$dwJsonCrypto );
	}
	public function decrypt( DwCallBack $dwCallBack ,\bakuba\kernel\crypto\json\DwJsonCrypto $jsonMessage ) : void {
		$this->log->trace( "." );
		$this->localCrypto->decrypt( $dwCallBack ,$jsonMessage );
	}
	
	private $isEverLunch = false;
	public function lunchAgent( DwPort $dwPort ) : void {
		if ( ! $this->isEverLunch ) {
			$this->isEverLunch = true;
			
			$this->log->debug("--- lunchAgent");
			$dwNode = $this->getNode();
			
			$timeout=600;
			$srvURL = $dwPort->getIp().":".$dwPort->getPort();
			
			$this->log->debug( "--- shAgent init at:$srvURL\r\n$shellCmd" );
			throw new \Exception('[TODO] VOIR CSCRIPT Agent....');
			
			\bakuba\kernel\shell\DwShell::exec($shellCmd);
			//\bakuba\kernel\shell\DwShell::exec("wget $wgetParam 2>&1");
		}
	}
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}
?>

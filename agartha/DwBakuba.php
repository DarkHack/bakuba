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
//use bakuba\kernel\agartha\DwBakuba as DwBakuba;

use bakuba\kernel\io\ipc\DwIpcMsg as DwIpcMsg;

use bakuba\kernel\network\DwInterface as DwInterface;
use bakuba\kernel\network\DwIp as DwIp;
use bakuba\kernel\network\DwPort as DwPort;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;

use bakuba\kernel\randomizer\DwProcess as DwRandomizerProcess;
use bakuba\kernel\network\dnsResolver\DwProcess as DwDnsResolverProcess;

// DwProcess
class DwBakuba extends \bakuba\kernel\process\DwMainProcess {
	public function setDebug(
		\darkphp\logger\iDwLogger $iDwLogger
	) : void {
		if ( \method_exists( $iDwLogger ,"upTo" ) )
			$this->log = $iDwLogger->upTo(
				$this
//				,\darkphp\source\DwSource::classNameToFirstSectionName( $this->getClassName() )
				,\darkphp\source\DwSource::classNameToTopName( $this->getClassName() )
			);
		else
			parent::setDebug( $iDwLogger );
		//$this->log->trace( "." );
	}
	// singleton class pattern:
	private static $instance = null;
	public static function singleton( \darkphp\logger\iDwLogger $iDwLogger = null ,array $argv = null ) {
		null === self::$instance and self::$instance = new self( $iDwLogger ,$argv );
		return self::$instance;
	}
	private $argv = null;
	private function __construct( \darkphp\logger\iDwLogger $iDwLogger = null ,array $argv = null ) {
		if ( \is_object( $iDwLogger ) ) $this->setDebug( $iDwLogger );
		else $this->setDebug( \darkphp\logger\DwLoggerLight::getLogger( $this ) );
		$this->argv=$argv;
		parent::__construct( $this ,"/" );
		
		$this->setDwResources( new \bakuba\kernel\process\DwResources( $this ) );
		//$this->setDwResources( new \bakuba\kernel\process\DwResources( $this ,\darkphp\logger\DwLoggerLight::getLogger( $this ) ) );//
		
		$this->addTick( new DwCallBack( array( &$this ,'createProcess' ) , $this ) );
	}
	
	private $dwAgartha = null;
	private $dwNode = null;
	private $dwLoPort = null;
	public function getDwLoPort() : DwPort {
		return $this->dwLoPort;
	}
	
	private $dwRandomizerProcess = null;
	public function getDwRandomizerProcess() : ?DwRandomizerProcess {
		return $this->dwRandomizerProcess;
	}
	private $dwDnsResolverProcess = null;
	public function getDwDnsResolverProcess() : ?DwDnsResolverProcess {
		return $this->dwDnsResolverProcess;
	}
	
	
	
	public function pingLunch() : void {
		$cols=null;
		$lines=null;
		foreach( $this->argv as $arg ) {
			$affectPos=strpos( $arg, '=' );
			$argName=$affectPos>0? substr( $arg ,0 ,$affectPos ) :$arg;
			$argValue=$affectPos>0?substr( $arg ,$affectPos+1 ) : "";
			echo "read:$argName'$argValue'".\PHP_EOL;
			switch( $argName ) {
				case 'debugFull' :
					$this->log->info( "have to enable full debug." );
					break;
				case '--cols' :
					$this->log->info( "cols:$argValue" );
					$cols=\intval($argValue);
					echo "cols:$cols'$argValue'".\PHP_EOL;
					break;
				case '--lines' :
					$this->log->info( "lines:$argValue" );
					$lines=\intval($argValue);
					break;
				default :
					$this->log->error( "unknow $argName" );
					break;
			}
		}
		//die( print_r( $this->argv ,true ) );
		if ( $cols > 0 ) {
			$dwCmdScreenMain = \bakuba\kernel\win32\DwCmdScreenMain::singleton( $this ,$cols ,29 );
			$logAppender = $dwCmdScreenMain->createAppender();
			$this->log->addAppender( $logAppender );
			\bakuba\kernel\agartha\DwInstall::setLogger( $this->log );
			$this->log->trace("initied");
		} else
			$this->lunchStdStream();
		
		
		//$debugFull = isset( $argv[1]) && $argv[1] == "debugFull"
	}
	public function lunchStdStream() {
		$fp = \fopen( "php://stdin", "r");
		if ( $fp === false )
			throw new \Exception(" echec on fopen:php://stdin");
		else {
			$streamSelect = $this->getDwResources()->getStreamSelect();
			$dwStream = new \bakuba\kernel\io\stream\DwStream( $this ,$fp );
			//$dwStream->withoutClose();
			$me = $this;
			$dwStream->setOnReaded( new DwCallBack(
				function ( \bakuba\kernel\io\stream\DwStream $dwStreamReaded ) use ( $streamSelect ,$me )  {
					$data = $dwStreamReaded->read();
					$dwStreamReaded->clearRecvBuf();
					
					// INFO FROM 'mode con' WINDOWS MODE ?
					if ( !empty( $data ) && preg_match( '/\s*Colonnes.{0,4}\s*(\d*)/m', $data ,$matches ) > 0 ) {
						$dwCmdScreenMain = \bakuba\kernel\win32\DwCmdScreenMain::singleton( $me ,\intval( $matches[1] ) ,30 );
						$logAppender = $dwCmdScreenMain->createAppender();
						$me->log->addAppender( $logAppender );
						\bakuba\kernel\agartha\DwInstall::setLogger( $me->log );
						$me->log->trace("initied");
					} else
						echo "\r\n END TO LISTEN OR EXECUTE ON STD STREAM... \r\n";
					/*
					$output=null;
					$retval=null;
					exec('mode con', $output, $retval);
					echo "Returned with status $retval and output:\n";
					$me->log->trace( $output );
					//*/
				}
				,$dwStream
			) );
			$streamSelect->streamRead( $dwStream  );
		}
	}
	private $dwApplicationServices=null;
	protected function init() : void {
		\set_time_limit( 0 );
		/*
		$this->addPeriodicTimer(
			200
			,new DwCallBack( array( new \bakuba\kernel\time\timer\DwGarbageCollector( $this ) ,'collect' ) )
		);
		//*/
		$this->log->trace( "// --------------- create Services Application //" );
		
		$dwApplicationServices=new \bakuba\kernel\process\child\DwProcessChild(
			$this
			,\bakuba\kernel\services\DwApplication::getStaticClassName()
		);
		$relunchProcessCB=new DwCallBack(
			array( &$this ,"relunchProcess")
			,$dwApplicationServices
		);
		//$dwApplicationServices->onClose( $relunchProcessCB );
		$this->addTick( $relunchProcessCB );
		/*
		$dwApplicationServices = \bakuba\kernel\application\service\aDwApplication::createApplicationService(
			$this
			,\darkphp\source\DwSource::classNameToSectionName(
				\bakuba\kernel\services\application\DwApplication::getStaticClassName()
			)
		);
		$this->getDwLocalAgent()->forkApp( );
		$relunchProcessCB=new DwCallBack(
			array( &$this ,"relunchProcess")
			,$dwApplicationServices
		);
		$dwApplicationServices->onClose( $relunchProcessCB );
		$this->addTick( $relunchProcessCB );
		$dwApplicationServices->onAlive( new DwCallBack( array( &$this, "serverProcessReady" ) ) );
		//$this->setDwApplicationServices( $dwApplicationServices );
		$this->dwApplicationServices=$dwApplicationServices;
		//*/
		parent::init();
		global $dwBakuba;
		$dwBakuba=$this;
		//$this->( $this );
		//$this->enableDebugMessageQueue();
		//$this->dwMessageQueueServer->enableDebug();
		//$this->enableDebugStream();
		
		$this->ipcRegisterHandle(
			DwBakubaRequest::getStaticClassName()
			,new DwCallBack( array( &$this ,'handleBakubaRequest' ) )
		);
		
		$this->dwAgartha = \bakuba\kernel\agartha\DwAgartha::singleton( $this );
		$this->ipcRegisterHandle(
			\bakuba\kernel\agartha\DwNodeRequest::getStaticClassName()
			,new DwCallBack( array( &$this->dwAgartha ,'handleNodeRequest' ) )
		);
		
		///*
		// Randomizer
		$this->log->trace("/-/ create Randomizer Process /-/");
		//$this->dwRandomizerProcess = new DwRandomizerProcess( $this ,\darkphp\logger\DwLoggerLight::getLogger( $this ) );
		//$this->dwRandomizerProcess = new DwRandomizerProcess( $this ,$this->log );
		
		$this->dwRandomizerProcess = new \bakuba\kernel\randomizer\DwProcess( $this );
		$this->addTick( new DwCallBack( array( &$this ,'createProcess' ) ,$this->dwRandomizerProcess ) );
		/*
		$relunchProcessCB=new DwCallBack(
			array( &$this ,"relunchProcess")
			,$this->dwRandomizerProcess
		);
		$this->dwRandomizerProcess->onClose( $relunchProcessCB );
		$this->addTick( $relunchProcessCB );
		//*/
		new \bakuba\kernel\randomizer\DwRandomizer( $this );
		
		$dwDnsResolverProcess = new \bakuba\kernel\network\dnsResolver\DwProcess( $this );
		$this->addTick( new DwCallBack( array( &$this ,'createProcess' ) ,$dwDnsResolverProcess ) );
		/*
		$relunchProcessCB=new DwCallBack(
			array( &$this ,"relunchProcess")
			,$dwDnsResolverProcess
		);
		$dwDnsResolverProcess->onClose( $relunchProcessCB );
		$this->addTick( $relunchProcessCB );
		//*/
		\bakuba\kernel\network\dnsResolver\DwDnsResolver::setDnsResolver( 
			new \bakuba\kernel\network\dnsResolver\DwDnsResolver( $this->getDwResources() )
		);
		//*/
		
		/*
		//echo __CLASS__." first shell\r\n";
		//*/
		/*
		$cpt=0;
		$dwCallBack = new DwCallBack(
				function() use( &$cpt ) {
					$cpt++;
				} 
		);
		$this->addPeriodicTimer(
			1/4000
			,$dwCallBack
		);
		$me=$this;
		$this->addTimer(
			1
			,new DwCallBack(
				function() use( &$me ,&$dwCallBack, &$cpt ) {
					$me->cancelCallBack( $dwCallBack );
					echo date("h:i:s")."coucou $cpt\r\n";
				} 
			)
		);
		//*/
		//$dwStreamInPipe = ;
		//$dwStreamOutPipe = ;
		
		//$me=$this;
		//$dwStream = new \bakuba\kernel\io\stream\DwStream( fopen('php://stdout', 'w') );
		/*
		$dwTimerEachS = $this->addPeriodicTimer(
			3
			,new DwCallBack( function () { //use (&$me ,&$dwStream) {
				$dataWrite = "=================== Tick:'".date("H:i:s u")."'\r";
				echo $dataWrite;
			})
		);
		//*/
		
		///*
		/*
		$dwStreamIn = new \bakuba\kernel\io\stream\DwStream( fopen('php://stdin', 'r') ,$this->log );
		$dwStreamIn->withoutClose();
		$dwStreamOut = new \bakuba\kernel\io\stream\DwStream( fopen('php://stdout', 'w') ,$this->log );
		$dwStreamOut->withoutClose();
		$onWrited = new DwCallBack( array( &$this, 'writedPipe' ) );
		$onReaded = new DwCallBack( array( &$this ,"receivePipe" ) ,$dwTimerEachS ,$onWrited ,$dwStreamOut );
		
		$onWrited->addParam( $onReaded );
		$onWrited->addParam( $dwStreamIn );
		
		$dwStreamOut->write("Bienvenue sur Bakuba(quit):");
		$dwStreamOut->setOnWrited( $onWrited );
		$this->streamWrite( $dwStreamOut );
		//*/
		
		$this->log->trace("end init");
	}
	/*
	public function receivePipe( $dwTimerEachS ,DwCallBack $onWrited ,DwStream $dwStreamOut ,DwStream $dwStreamIn ) : void {
		$this->log->trace("receivePipe");
		\fflush( $dwStreamIn->getResource() );
		$data = $dwStreamIn->read();
		if ( substr( $data ,0 ,4 ) == "quit" ) {
			$this->log->trace("quit ask");
			$this->cancelTimer( $dwTimerEachS );
		} else {
			$dwStreamOut->write("recieve '{$data}'");
			$dwStreamOut->setOnWrited( $onWrited );
			$this->streamWrite( $dwStreamOut );
		}
	}
	//*/
	/*
	public function writedPipe( DwCallBack $onReaded ,DwStream $dwStreamIn ,DwStream $dwStreamOut ) : void {
		$this->log->trace("pipe writed");
		\fflush( $dwStreamOut->getResource() );
		$dwStreamIn->clearRecvBuf();
		$dwStreamIn->setOnReaded( $onReaded );
		$this->streamRead( $dwStreamIn );
	}
	//*/
	/*
	public function start( ) : bool {
		$this->log->trace("start");
		$return = parent::start();
		return $return;
	}
	//*/
	public function initNode( string $nodePass = "nodePassNotDefined" ) : void {
		$this->log->trace( $nodePass );
		/*
		$dwServerProcess = $this->getDwServerProcess();
		$dwServerProcess->onAlive( new DwCallBack(
			array( &$this, "serverProcessReady" )
		) );
		$relunchProcessServerCB = new DwCallBack(
			array( &$this ,"relunchProcess")
			,$dwServerProcess
		);
		$dwServerProcess->onClose( $relunchProcessServerCB );
		$this->addTick( $relunchProcessServerCB );
		//*/
		//$this->createProcess( $dwServerProcess );
		
		//if ( is_null( $nodePass ) || empty( $nodePass ) )
		//	$nodePass = \bakuba\kernel\randomizer\DwRandomizer::randomize( 20 );
		\bakuba\kernel\crypto\sDwCrypto::start( $this->getDwLocalAgent() );
		// Create node
		$this->dwNode = $this->dwAgartha->createNode( $this->getDwLocalAgent() ,$nodePass );
		$this->dwNode->onFoundIp4( new DwCallBack( array( &$this ,'foundIp4' ) ) );
		$this->dwNode->onFoundIp4Gw( new DwCallBack( array( &$this, 'foundIpGw' ) ) );
		$this->dwNode->onFoundIp6( new DwCallBack( array( &$this, 'foundIp6' ) ) );
		//$this->dwNode->onFoundIp6Gw( new DwCallBack( array( &$this, 'foundIpGw' ) ) );
		/*
		$this->dwNode->onFoundDns4( new DwCallBack( array( &$this, 'foundDns4' ) ) );
		$this->dwNode->onFoundDns6( new DwCallBack( array( &$this, 'foundDns6' ) ) );
		//*/
		$this->dwNode->scanInterfaces( $this );
		$this->log->trace("end");
	}
	
	public function connectApplication(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\iDwApplicationConnexion {
		throw new \Exception( "no connectApplication in service app process." );
	}
	public function serviceConnectApp(
		\bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion
		,\bakuba\kernel\application\service\DwApplicationRequest $dwApplicationRequest
	) : \bakuba\kernel\application\service\iDwApplicationConnexion {
		throw new \Exception( "no connectApplication in service app process." );
	}
	private $ifaceFound = array();
	
	private $serverDwPorts = array();
	// $this->dwNode->onFoundIp4( new DwCallBack( array( &$this, 'foundIp4' ) ) );
	public function foundIp4( DwInterface $dwInterface ,DwIp $dwIp ) : void {
		$this->log->debug("[".$dwInterface->getIfaceName()."/".$dwIp->toString()."]");
		$ifaceName = $dwInterface->getIfaceName();
		if ( ! isset( $this->ifaceFound[ $ifaceName ] ) ) {
			$this->ifaceFound[ $ifaceName ]=$dwInterface;
			if ( $dwInterface->getIfaceName() == "lo" || $dwInterface->getIfaceName() == "Loopback Pseudo-Interface 1" ) {
				$this->initNetIpc( $dwInterface );
				/*
				$this->dwLoPort = $dwInterface->getRandomIpPort();
				$this->log->debug( "--- dwBakuba->createServer ".$this->dwLoPort->toString() );
				$this->dwNode->bindApache2ToDwPort( $this->dwLoPort );
				$dwPort = $this->dwLoPort;
				//*/
			}
		}
		
		$dwPort = $this->getDwHttpPort( $dwInterface, $dwIp );
		$this->serverDwPorts[] = $dwPort;
		if ( $this->serverProcessInitiated && is_object( $dwPort ) )
			$this->bindPortToServer( $dwPort );
		else
			throw new \Exception("server process not ready.");
		
		
		$dwPort = $this->getDwHttpsPort( $dwInterface, $dwIp );
		$this->serverDwPorts[] = $dwPort;
		if ( $this->serverProcessInitiated && is_object( $dwPort ) )
			$this->bindPortToSecureServer( $dwPort );
		else
			throw new \Exception("server process not ready.");
		
		$this->bindPortToDns(  $this->getDwDnsPort( $dwInterface, $dwIp ) );
	}
	public function foundIp6( DwInterface $dwInterface ,DwIp $dwIp ) : void {
		$this->log->debug( "[".$dwInterface->toString()."/".$dwIp->toString()."]" );
		
		$siteCidr =$dwInterface->getCIDR( $dwIp );
		if ( $dwInterface->getIfaceName() == "lo" || $siteCidr->inside( "fe80::" ) )
			return ;
		$dwPort = $this->getDwHttpPort( $dwInterface, $dwIp );
		$this->serverDwPorts[] = $dwPort;
		if ( $this->serverProcessInitiated && is_object( $dwPort ) )
			$this->bindPortToServer( $dwPort );
		else
			throw new \Exception("server process not ready.");
		
		$dwPort = $this->getDwHttpsPort( $dwInterface, $dwIp );
		$this->serverDwPorts[] = $dwPort;
		if ( $this->serverProcessInitiated && is_object( $dwPort ) )
			$this->bindPortToSecureServer( $dwPort );
		else
			throw new \Exception("server process not ready.");
		
		$this->bindPortToDns(  $this->getDwDnsPort( $dwInterface, $dwIp ) );
	}
	// $this->dwNode->onFoundIp4Gw( new DwCallBack( array( &$this, 'foundIpGw' ) ) );
	public function foundIpGw( DwInterface $dwInterface, DwIp $dwIp ) {
		$dwPort =  $this->getDwHttpPort( $dwInterface, $dwIp );
		$this->serverDwPorts[] = $dwPort;
		if ( $this->serverProcessInitiated )
			$this->bindPortToServer( $dwPort );
	}
	private $dwApplicationManager = null;
	public function getAppIp() : \bakuba\myip\DwApplication {
		return $this->dwApplicationManager->getAppIp();
	}
	public function getAppId() : \bakuba\id\DwApplication {
		return $this->dwApplicationManager->getAppId();
	}
	public function getAppContrat() : \bakuba\root\DwApplication {
		return $this->dwApplicationManager->getAppContrat();
	}
	private function addApache2Service(
		string $serviceType = "svn"
		,string $serviceProto = "https"
		,string $serviceDomain = "darkweb.fr"
		,string $serviceWebPath = "/svn"
		,string $serviceSvnPath = "pathSVN"
		,string $serviceKeyFile = "/etc/darksrv/ssl/darkweb.fr/darkweb.fr.key"
		,string $servicePemFile = "/etc/darksrv/ssl/darkweb.fr/darkweb.fr.pem"
	) {
		$dwNode = $this->getNode();
		$dwNode->addApache2AddToConf(
			$serviceType
			,$serviceProto
			,$serviceDomain
			,$serviceWebPath
			,$serviceKeyFile
			,$servicePemFile
			,$serviceSvnPath
		);
	}
	
	public function applicationCreated( $data ) {
		$this->log->debug( $data );
	}
	
	private function getDwDnsPort( DwInterface $dwInterface, DwIp $dwIp ) : DwPort {
		return $dwIp->getPort( 53 );
	}
	private function getDwHttpPort( DwInterface $dwInterface, DwIp $dwIp ) : DwPort {
		return $dwIp->getPort( 80 );
	}
	private function getDwHttpsPort( DwInterface $dwInterface, DwIp $dwIp ) : DwPort {
		return $dwIp->getPort( 443 );
	}
	public function getNode() : \bakuba\kernel\agartha\DwNode {
		return $this->dwNode;
	}
	//protected $dwServers = array();
	public function bindPortToServer( DwPort $dwPort ) {
		$this->log->trace("bind");
		
		$this->ipcSend(
			new DwCallBack( array( &$this ,"serverPortBinded" ), $dwPort )
			,$this->dwApplicationServices
			,new \bakuba\kernel\services\http\DwHttpdRequest(
				\bakuba\kernel\services\http\DwHttpdOrder::getOrder( \bakuba\kernel\services\http\DwHttpdOrder::bindPortToServer )
				, $dwPort
			)
		);
	}
	public function bindPortToSecureServer( DwPort $dwPort ) {
		$this->log->trace("bind");
		
		$this->ipcSend(
			new DwCallBack( array( &$this ,"serverPortBinded" ), $dwPort )
			,$this->dwApplicationServices
			,new \bakuba\kernel\services\http\DwHttpdRequest(
				\bakuba\kernel\services\http\DwHttpdOrder::getOrder( \bakuba\kernel\services\http\DwHttpdOrder::bindPortToSecureServer )
				, $dwPort
			)
		);
	}
	public function bindPortToDns( DwPort $dwPort ) {
		$this->log->trace("DNS bind");
		$dwIp = $dwPort->getIp();
		//if ( $dwIp->toString() == "::1" )
		//	return;
		$this->ipcSend(
			new DwCallBack( array( &$this ,"serverDnsPortBinded" ), $dwPort )
			,$this->dwApplicationServices
			,new \bakuba\kernel\services\dns\DwDnsdRequest(
				\bakuba\kernel\services\dns\DwDnsdOrder::getOrder( \bakuba\kernel\services\dns\DwDnsdOrder::bindPortToServer )
				,$dwPort
			)
		);
	}
	private $serverProcessInitiated = false;
	public function serverProcessReady( ) {
		$this->log->trace(PHP_EOL."/---/ serverProcessReady /---/");
		$this->log->trace( $this->serverDwPorts );
		$this->serverProcessInitiated=true;
		$this->getDwLocalAgent()->random( 15 ,new DwCallBack( array( &$this ,"initNode" ) ) );
		/*
		foreach( $this->serverDwPorts as $dwPort )
			$this->bindPortToServer( $dwPort );
		*/
	}
	public function serverDnsPortBinded(
		DwPort $dwPort
		,object $dwHttpdResponse
	) {
		$dwIp = $dwPort->getIp();
		$dwCIDR = $dwIp->getDwCIDR();
		$dwInterface = $dwCIDR->getDwInterface();
		$this->log->trace("overirde dns");
		$this->dwNode->overrideDns( $dwPort ,new DwCallBack( array( &$this ,"dnsOverrided" ) ) );
	}
	public function dnsOverrided( object $dwHttpdResponse ) {
		$this->log->trace( $dwHttpdResponse );
	}
	public function serverPortBinded(
		DwPort $dwPort
		,object $dwHttpdResponse
	) {
		$this->log->trace( $dwHttpdResponse );
		/*
		$dwIp = $dwPort->getIp();
		$dwCIDR = $dwIp->getDwCIDR();
		if ( $dwCIDR->getDwInterface()->getIfaceName() == "lo" )
			$this->getDwLocalAgent()->lunchAgent( $dwPort );
		//*/
	}
	
	private $ctpSrcRequest = array();
	
	///*
	public function handleBakubaRequest( \bakuba\kernel\process\DwProcessConnexion $dwProcessConnexion, DwBakubaRequest $dwBakubaRequest ) : void {
		$this->log->debug( $dwBakubaRequest );
		$dwIpcMsg = $dwProcessConnexion->getDwIpcMsg();
		$dwBakubaResponse = new DwBakubaResponse( "what???" );
		$dwCallBack = null;
		switch ( $dwBakubaRequest->getOrder() ) {
		case "getsource" :
			$srcKey = $dwIpcMsg->getSrcKey();
			$processAskId = $srcKey->getProcessId();
			$classAskName = $srcKey->getClassNameProcess();
			if ( ! isset( $this->ctpSrcRequest[ $processAskId ] ) )
				$this->ctpSrcRequest[ $processAskId ] = 0;
			$this->ctpSrcRequest[ $processAskId ]++;
			$filePath = $dwBakubaRequest->getObject();
			//if ( $this->logEnable )
			//	$this->log->info( "\r\n getsource[ $processAskId / ".$this->ctpSrcRequest[ $processAskId ]." / $classAskName ]:$filePath" );
			$dwBakubaResponse = new DwBakubaResponse( base64_encode( DwAutoLoad::getFile( $filePath ) ) );
			//if ( $this->logEnable )
			//	$this->log->info( "\r\n getsource[ $processAskId / ".$this->ctpSrcRequest[ $processAskId ]." / $classAskName ]\r\n$filePath" );
			$dwCallBack = new DwCallBack( array( &$this, "handleBakubaRequestAnswered" ) ,$filePath );
			break ;
		case "applicationList" :
			$dwBakubaResponse = new DwBakubaResponse( \serialize( $this->dwApplicationManager->getApplications() ) );
			break;
		case "stateProcess" :
			$dwBakubaResponse = new DwBakubaResponse( \serialize( $this->states() ) );
			break;
		case "applicationStart" :
			$dataApplication = $dwBakubaRequest->getObject();
			$appHost = $dataApplication[0];
			$appUrl = $dataApplication[1];
			$this->rootCreateApplication( $appUrl ,$appHost );
			$dwBakubaResponse = new DwBakubaResponse( "application created $appUrl:$appHost" );
			//$dwBakubaResponse = new DwBakubaResponse( "application not found $appUrl:$appHost" );
			//foreach($this->dwApplications as $dwApplication) {
			//	if ( $appHost == $dwApplication->getDomain() && $appUrl == $dwApplication->getUrlApp() ) {
			//		$this->createProcess( $dwApplication );
			//		$dwBakubaResponse = new DwBakubaResponse( "application created $appUrl:$appHost" );
			//	}
			//}
			break;
		/*
		case "dwApplicationSession" :
			$dwApplicationSession = $dwBakubaRequest->getObject();
			$dwApplication = $this->dwApplicationManager->getAppFromHost( $dwApplicationSession->getDomain() );
			$dwApplication->addSession( $dwApplicationSession );
			//$this->log->debug($dwApplicationSession);
			$dwBakubaResponse = new DwBakubaResponse( "dwApplicationSession created" );
			break;
		case "dwApplicationSessionClose" :
			$dwApplicationSession = $dwBakubaRequest->getObject();
			$dwApplication = $this->dwApplicationManager->getAppFromHost( $dwApplicationSession->getDomain() );
			$dwApplication->removeSession( $dwApplicationSession );
			//$this->log->debug($dwApplicationSession);
			$dwBakubaResponse = new DwBakubaResponse( "dwApplicationSession stopped" );
			break;
		case "getApplicationFromHost" :
			break;
		//*/
		case "addApache2Service" :
			$params = $dwBakubaRequest->getObject();
			$serviceType = $params[0];// "svn"
			$serviceProto = $params[1];// "https"
			$serviceDomain = $params[2];// "darkweb.fr"
			$serviceWebPath = $params[3];// "/svn"
			$serviceSvnPath = $params[4];// "pathSVN"
			$serviceKeyFile = $params[5];// "/etc/darksrv/ssl/darkweb.fr/darkweb.fr.key"
			$servicePemFile = $params[6];// "/etc/darksrv/ssl/darkweb.fr/darkweb.fr.pem"
			
			//	$this->log->info( "a2Sadd:{$serviceProto}://{$serviceDomain}{$serviceWebPath}" );
			
			$this->addApache2Service(
				$params[0]
				,$params[1]
				,$params[2]
				,$params[3]
				,$params[4]
				,$params[5]
				,$params[6]
			);
			
			$dwBakubaResponse = new DwBakubaResponse(
				"a2add: {$serviceProto}://{$serviceDomain}{$serviceWebPath}"
			);
			break;
		default :
			$dwBakubaResponse = new DwBakubaResponse(
				"unknow order:".$dwBakubaRequest->getOrder()
			);
			$this->log->trace( $dwBakubaRequest );
			break;
		}
		$dwProcessConnexion->ipcResponse(
			$dwBakubaResponse
			,$dwCallBack
		);
	}
	public function handleBakubaRequestAnswered( $filePath ,DwIpcMsg $dwIpcMsg ) : void {
		$dstKey = $dwIpcMsg->getDstKey();  
		$processAskId = $dstKey->getProcessId();
		$classAskName = $dstKey->getClassNameProcess();
		//$this->log->info( "[ $processAskId / ".$this->ctpSrcRequest[ $processAskId ]." / $classAskName ]:$filePath" );
	}
	//*/
	/*
	public function setAlive( bool $alive_state ) : void {
		//$this->log->debug( "setAlive" );
		parent::setAlive( $alive_state );
		if ( $alive_state ) {
		}
		
	}
	//*/
	//*/
	/*
	public function sendBakubaStart() {
		$this->sendStart();
	}
	//*/
	/*
	public function getServer( int $indexServer = 0 ) : DwServer {
		return $this->dwServers[ $indexServer ];
	}
	public function serversAdd( DwServer $dwServer ) : void {
		$this->log->trace("serversAdd");
		//$dwServer->disableFork();
		$this->dwServers[] = $dwServer;
		//$this->createProcess( $dwServer );
	}
	
	private $processInRunning = null;
	public function setProcessRunning( \bakuba\kernel\process\DwProcessManager $dwProcessManager ) {
		$this->processInRunning = $this->formatClassName( $dwProcessManager->getClassName() );
	}
	private function formatClassName( $className ) {
		//$this->log->trace("className:$className");
		$return = $className;
		$count = null;
		$fullClassName = str_replace('\\', '.', $className, $count);
		if ( is_null($fullClassName) ) 
			$return = $className;
		else {
			$nameSpaceClassName = preg_split("/\./", $fullClassName);
			if ( is_null($nameSpaceClassName) )
				$return = $fullClassName;
			else {
				if ( is_null( $count ) )
					$return = $fullClassName;
				else
					$return = $nameSpaceClassName[ $count ];
			}
		}
		//$this->log->trace("$return");
		return $return;
	}
	public function getProcessRunning( ) {
		return $this->processInRunning == null ?
			  $this->formatClassName( $this->getClassName() ) 
			: $this->processInRunning
		;
	}
	private $tabCidr = array();
	*/
	protected function sendStart( ) : void {
		$this->log->trace( "send start :{$this->toString()}" );
		
		$this->ipcSend(
			new DwCallBack( array( &$this ,"pinglunch" ) )
			,$this
			,new \bakuba\kernel\process\DwProcessRequest(
				\bakuba\kernel\process\DwProcessOrder::getOrder(
					\bakuba\kernel\process\DwProcessOrder::STARTED
				)
			)
		);
	}
	
	public function getFile( string $filePath ) : ?string {
		//$this->log->info("getFile: {$this->rootFolder}/{$filePath} ");
		return \bakuba\kernel\autoload\DwAutoLoad::getFile( "file://{$filePath}" );
	}
	public function getDwBakuba() : self {
		return $this;
	}
	public function __destruct() {
		$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ?
			parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ 
			: __CLASS__
		;
	}
}
?>

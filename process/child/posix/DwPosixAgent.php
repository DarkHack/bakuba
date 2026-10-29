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

namespace bakuba\kernel\process\child\posix;
//use bakuba\kernel\process\child\posix\DwPosixAgent as DwPosixAgent;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

class DwPosixAgent extends \bakuba\kernel\process\child\DwLocalAgent {
	private $localShell;
	private $localRandomizer;
	private $localCrypto;
	
	private $localUUID;
	public function getUUID( ) : string {
		return $this->localUUID;
	}
	public function __construct(
		\bakuba\kernel\process\DwResources $dwResources
	) {
		parent::__construct( $dwResources );
		$this->localShell = new DwShell( $this->getDwResources() );
		$this->localRandomizer = new DwShellRandomizer( $this->getDwResources() );
		$this->localCrypto = new DwShellCrypt( $this->getDwResources() );
		
		$this->localUUID="'not yet defined'";
		$this->localShell->exec('cat /etc/machine-id', new DwCallBack( array( &$this ,'hostUUIDreaded' ) );
	}
	public function hostUUIDreaded( string $output ): void {
		$uuid = trim($output);
		if ( $uuid == '') {
			$this->log->warn('echec on read host UUID');
			$this->localUUID="'echec defined'";
		} else
			$this->localUUID=$uuid;
	}
	public function scanInterfaces( DwCallBack $dwCallBack ) : void {
		$this->log->trace( "[{$this->localUUID}] ." );
		$this->localShell->exec(
			"ip link show | cut -d: -f 2 | grep '^ ' | tr -s '[:space:]' ' '"
			,new DwCallBack( array( &$this, 'ipLinkListed') ,$dwCallBack )
		);
	}
	public function ipLinkListed( DwCallBack $dwCallBack ,string $result ) : void {
		$result = \trim( $result );
		$this->log->info( "[{$this->localUUID}] result:".$result );
		foreach( \explode( " " ,$result ) as $ifaceName ) {
			if ( ! empty( $ifaceName ) )
				$this->scanIp( $dwCallBack->getClone( $ifaceName ) ,$ifaceName );
		}
	}
	private function scanIp(
		DwCallBack $dwCallBack
		,string $ifaceName
	) : void {
		$this->log->info( "[{$this->localUUID}] ".$ifaceName );
		$this->localShell->exec(
			"ip -4 addr show dev {$ifaceName} | grep inet | cut -d' ' -f6 | tr -d '[:blank:]' | tr -s '[:space:]' ' '"
			,new DwCallBack( array( &$this ,"foundIp4" ) ,$dwCallBack )
		);
		/*
		$this->localShell->exec(
			"ip -4 route show dev {$ifaceName} | grep via | cut -d' ' -f3 | tr -d '[:blank:]' | tr -s '[:space:]' ' '"
			,new DwCallBack( array( &$this ,"foundIpGw4" ) ,$dwCallBack )
		);
		//*/
		$this->localShell->exec(
			"ip -6 addr show dev {$ifaceName} | grep inet6 | cut -d' ' -f6 | tr -d '[:blank:]' | tr -s '[:space:]' ' '"
			,new DwCallBack( array( &$this ,"foundIp6" ) ,$dwCallBack )
		);
		/*
		$this->localShell->exec(
			"ip -6 route show dev {$ifaceName} | grep via | cut -d' ' -f3 | tr -d '[:blank:]' | tr -s '[:space:]' ' '"
			,new DwCallBack( array( &$this ,"foundIpGw6" ) ,$dwCallBack )
		);
		//*/
	}
	public function foundIp4( DwCallBack $dwCallBack ,string $result ) : void {
		$formedResult = \str_replace( "\n" ," " ,$result );
		foreach( \explode( ' ',$formedResult) as $subResult ) 
			$dwCallBack->callback( $subResult );
	}
	public function foundIpGw4( DwCallBack $dwCallBack ,string $result ) : void {
		$formedResult = \str_replace( "\n" ," " ,$result );
		foreach( \explode( ' ',$formedResult) as $subResult ) 
			$dwCallBack->callback( $subResult ,true );
	}
	public function foundIp6( DwCallBack $dwCallBack ,string $result ) : void {
		$formedResult = \str_replace( "\n" ," " ,$result );
		foreach( \explode( ' ',$formedResult) as $subResult ) 
			$dwCallBack->callback( $subResult ,false ,true );
	}
	public function foundIpGw6( DwCallBack $dwCallBack ,string $result ) : void {
		$formedResult = \str_replace( "\n" ," " ,$result );
		foreach( \explode( ' ',$formedResult) as $subResult ) 
			$dwCallBack->callback( $subResult ,true ,true );
	}
	
	public function foundIp4Gw( 
		DwProcessShell $dwProcessShell
		,DwInterface $dwInterface
		,\bakuba\kernel\network\DwIp $viaDwIp
		,string $result
	) : void {
	}
	
	public function scanDns( DwCallBack $dwCallBack ) : void {
		$this->localShell->exec( "cat /etc/resolv.conf", new DwCallBack( array( &$this, 'dnsPrinted') ,$dwCallBack ) );
	}
	public function dnsPrinted( DwCallBack $dwCallBack ,string $result ) : void {
		$this->log->trace( "[{$this->localUUID}] ".$result );
	}
	public function random( int $length , DwCallBack $dwCallBack ) : void {
		//$this->log->trace( "[{$this->localUUID}] ". $dwCallBack );
		$this->localRandomizer->random( $length, $dwCallBack );
	}
	public function listCiphers( DwCallBack $dwCallBack  ) : void {
		$this->log->trace( "[{$this->localUUID}] ." );
		$this->localCrypto->listCiphers( $dwCallBack );
	}
	public function listDigests( DwCallBack $dwCallBack  ) : void {
		$this->log->trace( "[{$this->localUUID}] ." );
		$this->localCrypto->listDigests( $dwCallBack );
	}
	public function initMessage( $ciphers ,$digests ,DwCallBack $dwCallBack ) : void {
		$this->log->trace( "[{$this->localUUID}] ." );
		$this->localCrypto->initMessage( $ciphers ,$digests ,$dwCallBack );
	}
	public function encrypt( DwJsonCrypto $dwJsonCrypto ,DwCallBack $dwCallBack ) : void {
		$this->log->trace( "[{$this->localUUID}] ." );
		$this->localCrypto->encrypt( $dwJsonCrypto ,$dwCallBack );
	}
	public function decrypt( $jsonMessage ,DwCallBack $dwCallBack ) : void {
		$this->log->trace( "[{$this->localUUID}] ." );
		$this->localCrypto->decrypt( $jsonMessage ,$dwCallBack );
	}
	///*
	public function __destruct() {
		parent::__destruct();
		//$this->log->trace( "[{$this->localUUID}] ." );
		unset( $this->localShell );
		unset( $this->localRandomizer );
		unset( $this->localCrypto );
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) 
				unset( $this->$key );
	}
	//*/
	
	private $isEverLunch = false;
	public function lunchAgent( DwPort $dwPort ) : void {
		if ( ! $this->isEverLunch ) {
			$this->isEverLunch = true;
			
			$this->log->debug("--- lunchAgent");
			$dwNode = $this->getNode();
			
			$timeout=600;
			$LD_LIBRARY_PATH="/lib/x86_64-linux-gnu";
			$srvURL = $dwPort->getIp().":".$dwPort->getPort();
			
			// wget --no-check-certificate -O - -q http://kernel.darkweb.fr/ShInstall | base64 -d | tee /tmp/startSh.sh | sh > /var/log/startSh.log 2>&1
			$wgetParam="--no-check-certificate -O - -q --content-on-error ";
			$wgetParam.=" -t 1 --timeout=$timeout --dns-timeout=$timeout --connect-timeout=$timeout --read-timeout=$timeout";
			$wgetParam.=" --header 'Host: kernel.darkweb.fr'";
			$wgetParam.=" $srvURL/ShInstall.sh?masterPass=".$dwNode->getMasterPass();
			
			//$shellRqtRelease="$(which wget) $wgetParam | $(which base64) -d | $(which sh) > /var/log/shAgent-".$dwPort->getIp()."-".$dwPort->getPort().".log 2>&1 &";
			//$shellRqt="$shellRqtRelease";
			$agentName="shAgent-".$dwPort->getIp()."-".$dwPort->getPort()."";
			$shellRqtDebug="\$(which wget) $wgetParam | \$(which tee) /tmp/$agentName.wget | \$(which base64) -d | \$(which sh) >>/var/log/{$agentName}.log 2>>/var/log/{$agentName}_err.log &";
			$shellRqt="$shellRqtDebug";
				
			$shellCmd="LD_LIBRARY_PATH=\$LD_LIBRARY_PATH:$LD_LIBRARY_PATH ";
			$shellCmd.="echo \"$shellRqt\" >>/var/log/{$agentName}.log ;sh -c \"$shellRqt\" && echo oki || echo echec >&- <&-";
			
			$this->log->debug( "--- shAgent init at:$srvURL\r\n$shellCmd" );
			
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

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

namespace bakuba\kernel\dwNode\shAgent;
// use bakuba\kernel\dwNode\shAgent\DwShAgent as DwShAgent;

use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\serializable\DwJson as DwJson;
use bakuba\kernel\extInc\log4php\renderers\LoggerRendererDefault as LoggerRendererDefault;

use bakuba\kernel\extInc\log4php\appenders\LoggerAppenderFilePid as LoggerAppenderFilePid;

use bakuba\kernel\application\session\cryptoShell\DwCryptoShellChannel as DwCryptoShellChannel;
use bakuba\kernel\network\DwPort as DwPort;

use bakuba\kernel\objects\DwCallBack as DwCallBack;
//use bakuba\kernel\dwNode\shAgent\DwShAgentOrder as DwShAgentOrder;

use bakuba\kernel\agartha\DwNode as DwNode;

use bakuba\kernel\apache2\ServiceHttpd as ServiceHttpd;
use bakuba\kernel\dwService\DwDhcpd as DwDhcpd;
use bakuba\kernel\dwService\DwMiniupnpd as DwMiniupnpd;
use bakuba\kernel\dwService\DwOpenVpn as DwOpenVpn;
use bakuba\kernel\dwService\DwInTftpd as DwInTftpd;
use bakuba\kernel\dwService\DwExtNamed as DwExtNamed;
use bakuba\kernel\dwService\DwIntNamed as DwIntNamed;

class DwShAgent extends \darkphp\serializable\aDwSerializable implements \bakuba\kernel\agartha\iDwAgent {
	
	public static function createShAgent( ) {
		return new self();
	}
	
	private function __construct() {
		parent::__construct();
	}
	
	protected $orders = array();
	protected $phpAgentPid = null;
	
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize("orders");
		$this->addtoSerialize("phpAgentPid");
	}
	
	public function setPhpAgentPid( $pid ) {
		$this->phpAgentPid=$pid;
	}
	public function getPhpAgentPid( ) {
		return $this->phpAgentPid;
	}
	
	public function compressAvailable() {
		return false;//! empty($this->cryptoChannelGet( "lz4" ));
	}
	public function fileRead( DwCallBack $dwCallBack ,string $file ) {
		$this->addOrder( DwShAgentOrder::create( $dwCallBack ,"\$cmdCat \"$file\";\n" ) );
	}
	public function fileWritePartiel( $params = null ) {
		
	}
	// $this->fileWrite( new DwCallBack( array($this, 'httpdRestart') ), $file, $content )
	public function fileWrite( DwCallBack $dwCallBack ,string $file ,string $content ) {
		$maxPacquetSize= 50000;
		if ( strlen( $content ) > $maxPacquetSize ) {
			
			$arrContent = str_split($content, $maxPacquetSize);
			$nbContent = count( $arrContent ) - 1;
			foreach( $arrContent as $id => $subContent ) {
				$totalFileContent="";
				//$totalFileContent="";
				if ( $id==0 )
					$totalFileContent.="\$cmdPrintf \"".base64_encode($subContent)."\" | \$cmdBase64 -d -i 2>/dev/null > \"$file\"; \n";
				else
					$totalFileContent.="\$cmdPrintf \"".base64_encode($subContent)."\" | \$cmdBase64 -d -i 2>/dev/null >> \"$file\"; \n";
				
				if ( $id < $nbContent )
					$this->addOrder( DwShAgentOrder::create(
						new DwCallBack( array( $this, 'fileWritePartiel' ) ,array( "id" => $id + 1, "total" => $nbContent ) )
						,$totalFileContent
					) );
				else
					$this->addOrder( DwShAgentOrder::create( $dwCallBack ,$totalFileContent ) );
			}
		} else {
			$content64=base64_encode($content);
			$cmdStr = "\$cmdPrintf \"$content64\" | \$cmdBase64 -d -i 2>/dev/null > \"$file\"";
			$this->addOrder( DwShAgentOrder::create( $dwCallBack ,$cmdStr ) );
		}
	}
	
	public function addCBOrder( DwCallBack $callback, $cmd ) {
		$this->addOrder( DwShAgentOrder::create( $callback ,$cmd ));
	}
	public function addOrder( DwShAgentOrder $dwShAgentOrder ) {
		$orderId = \bakuba\kernel\randomizer\DwRandomizer::progressiveUUID( $this->orders );
		$dwShAgentOrder->setOrderId($orderId);
		$this->orders[$orderId] = $dwShAgentOrder;
	}
	
	public function hasOrder( ) : bool {
		return count( $this->orders ) > 0;
	}
	public function nextOrder( ) : DwShAgentOrder {
		foreach( $this->orders as $orderId => $dwShAgentOrder ) {
			return $dwShAgentOrder;
		}
		return null;
	}
	public function executeEnd( 
		\bakuba\kernel\controller\CtrlShAgent $ctrl
		,\bakuba\kernel\session\controller\DwSessionControllerConnexion $dwSessionControllerConnexion
		,$nodeUUID
		,$orderId
		,$result
	) : void {
		global $dwBakuba;
		$dwSessionControllerConnexion->getDwControllerConnexion()->ipcSend( 
			$dwBakuba
			,new DwCallBack( array( &$this, 'executeSendedEnd' ) )
			,new \bakuba\kernel\agartha\DwNodeRequest(
				"executeNode"
				,array( $nodeUUID ,$orderId ,$result  )
			)
		);
		unset($this->orders[$orderId]);
	}
	public function executeSendedEnd( ) : void {
	}
	public function executeOrder( string $orderId, string $dataResponse ) : void {
		$dwShAgentOrder = $this->orders[$orderId];
		if ( ! is_object( $dwShAgentOrder ) )
			throw new Exception("no object dwShAgentOrder for :$orderId");
		else {
			$dwShAgentOrder->execute( $dataResponse );
		}
		unset($this->orders[$orderId]);
	}
	private $distUUID = null;
	public function getUUID( ) : string {
		return $this->distUUID;
	}
	public function setUUID( string $uuid ) : void {
		$this->distUUID=$uuid;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

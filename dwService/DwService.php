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

namespace bakuba\kernel\dwService;

use \Exception as Exception;
use \Error as Error;
use \boolean as boolean;

use darkphp\logger\DwLogger as DwLogger;

use darkphp\DwObject as DwObject;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\agartha\DwNode as DwNode;

//use bakuba\kernel\dwService\DwService as DwService;
class DwService extends DwObject {
	static private $sLog = null;
	protected static $services = null;
	
	public static function createService( DwNode $dwNode, $nom = "service", $folder = "/srv/service", $config_file  = "/etc/srvconf/service.conf" ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			if (self::$services == null ) self::$services = array();
			$dwNodeKey = $dwNode->getPreNodeKey();
			
			if ( ! isset( self::$services[ $dwNodeKey ] ) ) self::$services[ $dwNodeKey ] = array();
			
			do { $serviceKey = uniqid(); }while ( isset( self::$services[ $dwNodeKey ][ $serviceKey ] ) ) ;
			
			self::$sLog->trace("dwNodeKey:'$dwNodeKey', serviceKey:'$serviceKey', serviceName:'$nom'");
			
			self::$services[ $dwNodeKey ][ $serviceKey ] = new self( $dwNode, $serviceKey, $nom, $folder, $config_file );
			
			return self::$services[ $dwNodeKey ][ $serviceKey ];
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	public static function serviceFromStatus( DwNode $dwNode,  $status ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			
			if (self::$services == null ) self::$services = array();
			$dwNodeKey = $dwNode->getPreNodeKey();
			if ( ! isset( self::$services[ $dwNodeKey ] ) ) self::$services[ $dwNodeKey ] = array();
			
			self::$sLog->trace("dwNodeKey:'".$dwNodeKey."' ,serviceKey:'".$status->serviceKey."' ,serviceName:'".$status->nom."' ,create?".isset( self::$services[ $dwNodeKey ][ $status->serviceKey ] )?"yes":"no");
			
			if ( isset( self::$services[ $dwNodeKey ][ $status->serviceKey ] ) )
				$dwService = self::$services[ $dwNodeKey ][ $status->serviceKey ];
			else
				$dwService = new self( $dwNode, $status->serviceKey, $status->nom, $status->folder, $status->config_file );
			
			$dwService->setStatus( $status );
			self::$services[ $dwNodeKey ][ $status->serviceKey ] = $dwService;
			return self::$services[ $dwNodeKey ][ $status->serviceKey ];
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	public static function getServices( ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			
			if (self::$services == null ) self::$services = array();
			
			return self::$services;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return null;
	}
	
	protected $dwNode;
	// DwCrud::$_id
	public $nom;
	public $folder;
	public function getChrootFolder() {
		return $this->folder;
	}
	public $script_start;
	public $script_restart;
	public $script_status;
	public $script_stop;
	public $script_execute;
	public $config_file;
	public $serviceKey;
	public function getServiceKey() {
		return $this->serviceKey;
	}
	// construct with logger
	private $log;
	private $dwCallBackNop = null;
	public function __construct( DwNode $dwNode, $serviceKey, $nom, $folder, $config_file ) {
		try {
			parent::__construct();
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger(__CLASS__);
			self::$sLog->trace(".");
			
			$this->dwNode = $dwNode;
			$this->nom = $nom;
			$this->folder = $folder;
			$this->config_file = $config_file;
			$this->serviceKey = $serviceKey;
			
			$this->script_start="/etc/bakuba/$nom start";
			$this->script_restart="/etc/bakuba/$nom restart";
			$this->script_stop="/etc/bakuba/$nom stop";
			$this->script_status="/etc/bakuba/$nom status";
			$this->script_execute="/etc/bakuba/$nom execute";
			
			$this->dwCallBackNop = new DwCallBack( array( &$this, 'nop' ) );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	
	// $this->fileRead( new DwCallBack( array($this, 'httpdRestart') ), $file )
	public function fileRead( $file ,DwCallBack $dwCallBack ) {
		try {
			$dwShAgent = $this->dwNode->getShAgent();
			$dwShAgent->fileRead( $dwCallBack, $file );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	// $this->fileWrite( new DwCallBack( array($this, 'httpdRestart') ), $file, $content )
	public function fileWrite( string $file ,string $content ,DwCallBack $dwCallBack = null ) {
		try {	
			$dwShAgent = $this->dwNode->getShAgent();
			$dwShAgent->fileWrite( is_null( $dwCallBack ) ? $this->dwCallBackNop : $dwCallBack, $file, $content );
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function nop( ) {
	}
	public function execute( string $cmd ,DwCallBack $dwCallBack = null ) {
		try {
		
			$dwShAgent = $this->dwNode->getShAgent();
			$dwShAgent->addCBOrder( is_null( $dwCallBack ) ? $this->dwCallBackNop : $dwCallBack, $cmd );
			
		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function restart( DwCallBack $dwCallBack = null ) {
		try {
			self::$sLog->trace(".");
			$this->execute( $this->script_restart." 2>&1 " ,$dwCallBack );

		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return null;
	}
	public function start( DwCallBack $dwCallBack = null ) {
		try {
			self::$sLog->trace(".");
			$this->execute( $this->script_start." 2>&1 " ,$dwCallBack );

		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return null;
	}
	public function stop( DwCallBack $dwCallBack = null ) {
		try {
			self::$sLog->trace(".");
			$this->execute( $this->script_stop." 2>&1 " ,$dwCallBack );

		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
		return null;
	}
	
	private $filesLog = array();
	protected function addLogFile( $fileLog ) {
		try {
			$this->filesLog[] = $fileLog;

		} catch(Exception $exception) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $exception;
		} catch(Error $error) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $error;
		}
	}
	public function __destruct() {
		//$this->log->trace( "." );
		parent::__destruct();
		foreach( $this as $key => $toDestruct )
			if ( isset( $this->$key ) ) unset( $this->$key );
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
	
}

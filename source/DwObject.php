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

namespace bakuba\kernel;

use \Exception as Exception;
use bakuba\kernel\dwLogger\DwCltLogger as DwLogger;
//use darkphp\logger\DwLogger as DwLogger;
//use darkphp\DwObject as DwObject;

// DwObject; DwNode : $dwItem->set("&#xf102;");// house
// DwObject; DwInterface : $dwItem->set("&#xf325;");// network
// DwObject, DwCIDR : $dwItem->set("&#xf508;");// Calendar
// DwObject; DwIp : $dwItem->set("&#xf509;");// 4 in 1
// DwObject; DwPort : $dwItem->set("&#xf320;");// rtl editor

// DwObject; DwBakuba : $dwItem->set("&#xf11f;");// world
// DwObject; ACtrl : $dwItem->set("&#xf231;");// Point on map
// DwObject; DwApplication : $dwItem->set("&#xf508;");// Calendar
// DwObject; DwControllerManager : $dwItem->set("&#xf513;");// store
// DwObject; DwService : $dwItem->set("&#xf338;");// Business Man
// DwObject; DwServerA : $dwItem->set("&#xf338;");// business man
// DwObject; DwSocketServerMonoThread :	$dwItem->set("&#xf512;");// building
// DwObject; DwSocketServer : $dwItem->set("&#xf541;");// multi house
// DwObject; DwSocket : $dwItem->set("&#xf512;");// building
class DwObject {
	private static $sLog = null;	
	public static function getStaticClassName( $fqcn=false ) {
		$className = __CLASS__;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( $className."[static]" );
			self::$slog->trace(".");
			//$className= $fqcn ? parent::getClassName( $fqcn ).self::CLASS_SEPARATOR.$className : $className;
		} catch(Exception $err) {
			self::$slog->error($err);
			throw $err;
		}
		return $className;
	}
	public function getClassName( $fqcn=false ) {
		$className = __CLASS__;
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( $className."[static]" );
			self::$slog->trace(".");
			//$className= $fqcn ? parent::getClassName( $fqcn ).self::CLASS_SEPARATOR.$className : $className;
		} catch(Exception $err) {
			self::$slog->error($err);
			throw $err;
		}
		return $className;
	}
	static private $keys = array();
	private $instanceKey = null;
	public function __construct( ) {
		try {
			if ( is_null(self::$sLog) ) self::$sLog = DwLogger::getLogger( $this );
			self::$sLog->trace(".");
			$size = 1;
			do $this->instanceKey = $size<13 ? \substr( \strrev( \uniqid() ) ,0 ,$size ) : \uniqid();
			while( \in_array($this->instanceKey,self::$keys) && ( $size++ || true ) );
			
			self::$keys[]=$this->instanceKey;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
	}
	
	public function getInstanceKey() {
		return $this->instanceKey;
	}
	private $dwObjects = array();
	public function insert( DwObject $dwInstance ) {
		try {
			self::$sLog->trace(".");
			$dwInstance->setContainer($this);
			$this->dwObjects[]=$dwInstance;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
	}
	private $dwLinkObjects= array();
	public function link( DwObject $dwInstance ) {
		try {
			self::$sLog->trace(".");
			$this->dwLinkObjects[]=$dwInstance;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
	}
	public function toData() {
		$data = array();
		try {
			self::$sLog->trace(".");
			$data["className"] = $this->getClassName();
			$data["className-fqcn"] = $this->getClassName( true );
			$data["instance-key"] = $this->instanceKey;
			foreach($this->dwObjects as $dwObject) {
				if (!isset($data["members"])) $data["members"]=array();
				$data["members"][]=$dwObject->toData();
			}
			foreach($this->dwLinkObjects as $dwObject) {
				if (!isset($data["linked"])) $data["linked"]=array();
				$data["linked"][]=$dwObject->instanceKey;
			}
			
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return $data;
	}
	public function toJson() {
		$json = "echec to json";
		try {
			self::$sLog->trace(".");
			$json = json_encode($this->toData());
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
		return $json;
	}
	private $dwObjectContainer = null;
	public function getContainer() {
		return $this->dwObjectContainer;
	}
	public function setContainer( DwObject $dwInstance ) {
		try {
			self::$sLog->trace(".");
			if ( ! is_null( $this->dwObjectContainer ) ) 
				self::$sLog->warn("dwObjectContainer was set.");
			$this->dwObjectContainer = $dwInstance;
		} catch(Exception $err) {
			self::$sLog->error($err);
			throw $err;
		}
	}
	
	protected const CLASS_SEPARATOR = \darkphp\autoload\DwAutoLoadBoot::CLASS_SEPARATOR;
	
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return $fqcn ? "//".self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
	}
}

?>

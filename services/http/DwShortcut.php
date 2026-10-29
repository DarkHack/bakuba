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

namespace bakuba\kernel\services\http;
//use bakuba\kernel\services\http\DwShortcut as DwShortcut;

use \Exception as Exception;
use \Error as Error;

use darkphp\logger\DwLogger as DwLogger;
use darkphp\DwObject as DwObject;

use darkphp\web\DwWebUrl as DwWebUrl;
use bakuba\kernel\objects\DwCallBack as DwCallBack;

use  bakuba\kernel\services\protocole\http\DwHttpServiceRequest as DwHttpServiceRequest;
use  bakuba\kernel\services\DwServiceHttpResponse as DwServiceHttpResponse;

use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;
use bakuba\kernel\services\protocole\http\DwHttpResponse as DwHttpResponse;

abstract class DwShortcut extends \darkphp\serializable\aDwSerializable {
	//DwApplicationShortcut::extensionToContentType
	public static function extensionToContentType( string $extension ) : string {
		return \bakuba\kernel\protocole\http\DwHttp::extensionToContentType( $extension );
	}
	
	public function getDwWebUrl( ) : DwWebUrl {
		$dwWebUrl = new DwWebUrl();
		$dwWebUrl->setHost( $this->getDomain() );
		return $dwWebUrl;
	}
	public $aDwApplication = null;
	public function getDomains( ) : array {
		return $this->aDwApplication->getDomains();
	}
	protected function __construct( \bakuba\kernel\application\aDwApplication $aDwApplication ) {
		$this->aDwApplication = $aDwApplication;
		parent::__construct();
	}
	public function DwSerializableInit() {
		parent::DwSerializableInit();
		
		$this->addtoSerialize("aDwApplication");
		$this->addtoSerialize("shortcuts");
		$this->addtoSerialize("shortcutsToSend");
	}
	public $shortcuts = null;
	public function init( ) : void {
		$this->shortcuts = array();
		foreach( $this->shortcutsToSend as $shortCutData ) {
			if ( ! isset( $shortCutData[ "uri" ] ) )
				continue;
			$this->initShortCut( $shortCutData );
		}
	}
	protected function initShortCut( array $shortCutData ) : void {
		$uri = \strtolower( $shortCutData[ "uri" ] );
		//if ( isset( $this->shortcuts[ $uri ] ) )
		echo "------- DwApplicationShortcut uri[$uri]\r\n";
		
		$this->shortcuts[ $uri ] = array();
		
		if ( isset( $shortCutData[ "headers" ] ) ) 
			$this->shortcuts[ $uri ][ "headers" ] = $shortCutData[ "headers" ];
		
		$uriData = "";
		if ( isset( $shortCutData[ "file" ] ) ) {
			$uriData = $this->getFile( $shortCutData[ "file" ] );
		}elseif ( isset( $shortCutData[ "files" ] ) ) {
			foreach( $shortCutData[ "files" ] as $file ) {
				$uriData.= $this->getFile($file)."\r\n";
			}
		}elseif ( isset( $shortCutData[ "content" ] ) )
			$uriData = $shortCutData[ "content" ];
		
		$this->shortcuts[ $uri ][ "data" ]=$uriData;
	}
	public function getFromUri( string $uri ) : ?array {
		$shortcut = null;
		if ( isset( $this->shortcuts[ $uri ] ) )
			$shortcut = $this->shortcuts[ $uri ];
		return $shortcut;
	}
	public $shortcutsToSend = array();
	public function createContentShortcut(
		string $content
		,string $extension
		,string $uri = null
	) : string {
		
		if ( is_null($uri) ) $uri = "/".uniqid().".$extension";
		
		$this->shortcutsToSend[] = array(
			"uri" => $uri
			,"headers" => array(
				"Content-type" => self::extensionToContentType( $extension )
				,"Cache-Control" => "public, max-age=604800"//"max-age=604800"
			)
			,"content" => $content
		);
		
		return $uri;
	}
	public function createFileShortcut(
		string $file
		,string $extension
		,string $uri = null
	) : string {
		
		if ( is_null($uri) ) $uri = "/".uniqid().".$extension";
		
		$this->shortcutsToSend[] = array(
			"uri" => $uri
			,"headers" => array(
				"Content-type" => self::extensionToContentType( $extension )
				,"Cache-Control" => "public, max-age=604800"//"max-age=604800"
			)
			,"file" => $file
		);
		
		return $uri;
	}
	public function createFilesShortcut(
		array $files
		,string $extension
		,string $uri = null
	) : string {
		
		if ( is_null($uri) ) $uri = "/".uniqid().".$extension";
		
		$this->shortcutsToSend[] = array(
			"uri" => $uri
			,"headers" => array(
				"Content-type" => self::extensionToContentType( $extension )
				,"Cache-Control" => "public, max-age=604800"//"max-age=604800"
			)
			,"files" => $files
		);
		
		return $uri;
	}
	public function getClassName( bool $fqcn=null ) : string {
		return self::getStaticClassName( $fqcn );
	}
	public static function getStaticClassName( bool $fqcn=null ) : string {
		return __CLASS__;
	}
}
?>

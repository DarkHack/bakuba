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
//use bakuba\kernel\services\http\DwRootShortcut as DwRootShortcut;

use bakuba\kernel\objects\DwCallBack as DwCallBack;

use bakuba\kernel\randomizer\DwRandomizer as DwRandomizer;

use bakuba\kernel\autoload\DwAutoLoad as DwAutoLoad;
use bakuba\kernel\protocole\http\DwHttpResponse as DwHttpResponse;

class DwRootShortcut extends \darkphp\serializable\aDwSerializable {
	public static function extensionToContentType( string $extension ) : string {
		return \bakuba\kernel\protocole\http\DwHttp::extensionToContentType( $extension );
	}
    public function __construct( \darkphp\serializable\aDwNoSerializable $dwParentNoSerializable ) {
		parent::__construct( $dwParentNoSerializable );
	}
	private $shortcuts = null;
	public function init( ) : void {
		$this->shortcuts = array();
		foreach( $this->rootShortcutsToSend as $shortCutData ) {
			if ( ! isset( $shortCutData[ "uri" ] ) )
				continue;
			//$this->log->info( $shortCutData );
			$this->initRootShortCut( $shortCutData );
		}
	}
	private function initRootShortCut( array $shortCutData ) : void {
		$uri = $shortCutData[ "uri" ];
		
		if ( isset( $this->shortcuts[ $uri ] ) ) {
			//echo "$uri :uri ever exist!!!\r\n";
			throw new \Exception("uri[{$uri}] ever exist!!!");
		}
		
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
	public function getFile( $file ) {
		$content="";
		if ( \darkphp\string\DwString::endsWith( $file ,"css" ) ) {
			$cssContent = preg_replace('!/\*.*?\*/!s', '', DwAutoLoad::getFile( $file ));
			$dwCallBack = new DwCallBack( array( &$this ,"cssUrlExec" ), dirname( $file ) );
			$content = preg_replace_callback(
				'/url\(([^\)]*)\)/m'
				,array( &$dwCallBack, "callback" )
				,$cssContent
			);
		} else
			$content = DwAutoLoad::getFile( $file );
		
		return $content;
	}
						
	public function cssUrlExec( $folder ,$matches ) {
		$urlParam = "whoups";
		if ( isset( $matches[1] ) ) {
			$urlParam = preg_replace( '/[\"\']/' ,'' ,$matches[1] );
			if ( substr($urlParam, 0, 5 ) != "data:" ) {
				$dirName = dirname( $urlParam );
				$fileName = basename( $urlParam );
				$posChar = strrpos( $fileName, "#" );
				if ( $posChar > 0 )
					$fileName = substr( $fileName, 0,  $posChar );
				
				$posChar = strrpos( $fileName, "?" );
				if ( $posChar > 0 )
					$fileName = substr( $fileName, 0,  $posChar );
				
				$typeName = substr( $fileName, strrpos( $fileName, '.' ) + 1 );
				
				$uri = "/".DwRandomizer::randomize( 10 ).".$typeName";
				
				$this->initRootShortCut( array(
					"uri" => $uri
					,"headers" => array(
						"Content-type" => self::extensionToContentType( $typeName )
//						,"Cache-Control" => "public, max-age=604800"//"max-age=604800"
					)
					,"file" => $folder."/$dirName/$fileName"
				) );
				
				$urlParam = $uri;
				/*$this->dwSessionControllerConnexion->getDomainUrlForFile(
					 $this->folder."/$urlParam"
					,"$typeName"
				);*/
			}
		}
		return "url('$urlParam')";
	}
	
	public function getFromUri( string $uri ) : ?array {
		$shortcut = null;
		if ( isset( $this->shortcuts[ $uri ] ) )
			$shortcut = $this->shortcuts[ $uri ];
		return $shortcut;
	}
	
	private $rootShortcutsToSend = array();
	public function rootCreateContentShortcut(
		string $content
		,string $extension
		,string $uri = null
		,string $contentType = null
	) : string {
		
		if ( is_null($uri) ) $uri = "/".DwRandomizer::randomize( 10 ).".$extension";
		
		$this->rootShortcutsToSend[] = array(
			"uri" => $uri
			,"headers" => array(
				"Content-type" => is_null( $contentType ) ? self::extensionToContentType( $extension ) : $contentType
				,"Cache-Control" => "public, max-age=604800"//"max-age=604800"
			)
			,"content" => $content
		);
		
		return $uri;
	}
	public function rootCreateFileShortcut( string $file ,string $extension ,$uri = null ,string $contentType = null ) : string {
		if ( \is_null($uri) ) $uri = "/".DwRandomizer::randomize( 10 ).".$extension";
		
		$this->rootShortcutsToSend[] = array(
			"uri" => $uri
			,"headers" => array(
				"Content-type" => is_null( $contentType ) ? self::extensionToContentType( $extension ) : $contentType
				,"Cache-Control" => "public, max-age=604800"//"max-age=604800"
			)
			,"file" => $file
		);
		
		return $uri;
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
		//return $fqcn ? parent::getStaticClassName( $fqcn ).self::CLASS_SEPARATOR.__CLASS__ : __CLASS__;
		return __CLASS__;
	}
}
?>

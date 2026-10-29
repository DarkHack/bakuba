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

namespace bakuba\kernel\logger;
//use bakuba\kernel\logger\iDwLogger as iDwLogger;

interface iDwLogger /*extends \darkphp\iDwObject */{

	public function log(string $level, $message, $throwable = null);
	public function trace($message, $throwable = null);
	public function debug($message, $throwable = null);
	public function info($message, $throwable = null);
	public function warn($message, $throwable = null);
	public function error($message, $throwable = null);
	public function fatal($message, $throwable = null);
	
	public function throwable( $exception ) : void;	
	public function getLogger( $forThisObject ) : self;
	//public function addAppender( log4dw\appenders\aDwAppender $aDwAppender ) : void;
//	public static function throwable( $exception ) : void ;
//	public static function throwableToHttpMessage( $exception ) : string ;
//	public static function formatHttpBackTrace( array $backTrace ) : array ;
//	public static function throwableToMessage( $exception ) : string ;
//	public static function decodeFile( $file ) : string;	
//	public static function getDataCode( int $line, string $content) : array;
//	public static function formatBackTrace( array $backTrace ) : array;
//	static function print_r( $var ,$level = 0 ) : string ;
//	static function print_r_limit( $var ,$return = false ,$html = false ,$level = 0 ) : string;
//	static function printRLevel( $data ,$return = false ,$html = false ,$level = 1 ) : string ;
//	public static function getFolderInit( ) ;
//	public static function getDirLog( ) ;
//	public static function upLogDir( $dirToUp ) ;
//	public static function rootLogDir( $dirToRoot ) ;

}
?>

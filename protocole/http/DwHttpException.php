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

namespace bakuba\kernel\protocole\http;
//use bakuba\kernel\protocole\http\DwHttpException as DwHttpException;

use darkphp\DwObject as DwObject;

/**
 * DwHttpException class
 *
 * @package bakuba\kernel
 */
class DwHttpException extends \darkphp\error\DwException {
	//\bakuba\kernel\services\protocole\http\DwHttpException::throwableToHttpMessage();
	public static function throwableToHttpMessage( $exception ) : string {
		$throwToStr = "=================== throwableToHttpMessage\r\n";
		if ( \is_object( $exception ) && \method_exists( $exception, "getMessage" ) ) {
			//$throwToStr.= $exception->getMessage()."\r\n";
			$backTraceArray = $exception->getTrace();
			if ( \is_array( $backTraceArray ) ) {
				foreach( $backTraceArray as $index => $backTrace ) {
					$throwToStr.= DwObject::print_r(
						self::formatHttpBackTrace( $backTrace )
						, 7
					).\PHP_EOL;
				}
			}
		} else 
			$throwToStr.= DwObject::print_r(
				self::formatHttpBackTrace( \debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS ) )
				, 7
			).\PHP_EOL;
		return $throwToStr;
	}
	
	public static function formatHttpBackTrace( array $backTrace ) : array {
		$response = array();
		foreach( $backTrace as $index => $value ) {
			switch($index) {
			case 'args' :
				$response[$index] = count($value);
				break;
			case 'object' :
				$response[$index]=$value->getClassName();
				break;
			case 'type' :
			case 'line' :
			case 'class' :
			case 'function' :
				$response[$index] = $value;
				break;
			case 'file' :
			default :
				$response[$index] = "protected";
				break;
			}
		}
		return $response;
	}
	/*
	private static $ignoreError = array( "msg_send(): msgsnd failed: Resource temporarily unavailable" );
	public static function error_handle(int $errno, string $errstr, string $errfile = "file?", int $errline = -1, array $errcontext = null) : bool {
		
		if ( array_search( $errstr, self::$ignoreError ) === false ) {
			echo "===================  DwException->error_handle(".getmypid().")\r\n";
			echo "l:{$errline}[{$errno}]:$errstr"."\r\n";
			echo self::print_r( self::getDataCode( $errline ,self::decodeFile($errfile) ) )."\r\n";
			echo json_encode( $errcontext )."\r\n";
		}
		return false;
	}
	/*
	public function getMessage();                 // Exception message
    public function getCode();                    // User-defined Exception code
    public function getFile();                    // Source filename
    public function getLine();                    // Source line
    public function getTrace();                   // An array of the backtrace()
	
	public static function throwable_handle(\Throwable $exception) : void {
		try {
			echo "=================== throwable_handle\r\n";
			echo $exception->getMessage()."\r\n".$exception->getCode()."\r\n";
			echo DwObject::print_r( self::getDataCode($exception->getLine() ,self::decodeFile($exception->getFile())) ,5)."\r\n";
			echo DwObject::print_r( DwException::formatBackTrace( $exception->getTrace() ), 5)."\r\n";
		} catch(Exception $anException) {
			self::$sLog->error("exception [".$this->getClassName()."]");
			throw $anException;
		} catch(Error $anError) {
			self::$sLog->error("error [".$this->getClassName()."]");
			throw $anError;
		}
	}
	*/
	/*
	//\bakuba\kernel\logger\darkphp\error\DwException::exceptionToString();
	
	public static function throwableToMessage( $exception ) : string {
		$throwToStr = "=================== throwableToMessage\r\n";
		if ( is_object( $exception ) && method_exists( $exception, "getMessage" ) ) {
			$throwToStr.= $exception->getMessage()."\r\n".$exception->getCode()."\r\n";
			$throwToStr.= DwObject::print_r(
				DwException::getDataCode(
					$exception->getLine()
					,\bakuba\kernel\autoLoad\DwAutoLoad::decodeFile( $exception->getFile() )
				)
				,5
			)."\r\n";
			$backTraceArray = $exception->getTrace();
			if (is_array( $backTraceArray ) ) {
				$cpt=0;
				foreach( $backTraceArray as $index => $backTrace ) {
					$throwToStr.= DwObject::print_r( DwException::formatBackTrace( $backTrace ), 7 )."\r\n";
					$cpt++;
					if ( $cpt > 15 )
						break;
				}
			}
		} else 
			$throwToStr.= DwObject::print_r( self::formatBackTrace( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ), 7)."\r\n";
		return $throwToStr;
	}
	//*/
}


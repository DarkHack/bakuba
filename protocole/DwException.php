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

namespace bakuba\kernel\protocole;
//use bakuba\kernel\protocole\DwException as DwException;

use darkphp\DwObject as DwObject;

/**
 * DwException class
 * @package bakuba\kernel\protocole
 */
class DwException extends \darkphp\error\DwException {
	//bakuba\kernel\protocole\DwException::throwableToMessage( $exception );
	public static function throwableToMessage( $exception ) : string {
		$throwToStr = "=================== throwableToMessage\r\n";
		if ( is_object( $exception ) && method_exists( $exception, "getMessage" ) ) {
			$throwToStr.= $exception->getMessage()."\r\n".$exception->getCode()."\r\n";
			$throwToStr.= DwObject::print_r(
				self::getDataCode(
					$exception->getLine()
					,\bakuba\kernel\autoLoad\DwAutoLoad::decodeFile( $exception->getFile() )
				)
				,5
			).\PHP_EOL;
			$backTraceArray = $exception->getTrace();
			if ( \is_array( $backTraceArray ) ) {
				$cpt=0;
				foreach( $backTraceArray as $index => $backTrace ) {
					$throwToStr.= DwObject::print_r(
						self::formatBackTrace( $backTrace )
						, 7
					).\PHP_EOL;
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


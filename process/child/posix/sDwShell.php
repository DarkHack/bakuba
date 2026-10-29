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
//use bakuba\kernel\process\child\posix\DwShell as DwShell;

class sDwShell {
	static private $dirLog = null;
	static private $kernelShellDir="file://bakuba/kernel/process/child/posix/shell";
	
	public static function getInitScript() : string {
		$scriptContent = self::getScript("which/base")."\n";
		$scriptContent .= self::getScript("lib-sh/shLog")."\n";
		$scriptContent .= self::getScript("lib-sh/shPipe")."\n";
		$scriptContent .= self::getScript("lib-sh/shJson")."\n";
		return $scriptContent;
	}
	public static function getScript( string $scriptPath ) : ?string {
		$content = null;
		$content = \bakuba\kernel\autoload\DwAutoLoad::getFile( self::$kernelShellDir."/$scriptPath" );
		
		return \str_replace(
			array( '#!/bin/sh', '\r' )
			,''
			, $content
		);
	}
	public static function exec( string $scriptData ) : ?string {
		$debugMsg="";
		$resultcode = null;
		$lastExecResult = null;
		$dataOutput = array();
		$lastExecResult = @exec( $scriptData ,$dataOutput ,$resultcode );
		//$lastExecResult = @shell_exec( $scriptData );
		if ( is_string( $lastExecResult ) ) {
			if ( is_array( $dataOutput ) )
				$lastExecResult.=implode( '\n' ,$dataOutput );
		} else
			$lastExecResult = "resultCode:{$resultcode} :".implode( '\n' ,$dataOutput )."\r\nscript:".substr($scriptData,-85);
		/*if ( $resultcode !== 0 || ! is_string( $lastExecResult ) )
			$debugMsg.=;
		else
			$lastExecResult = implode( '\n' ,$dataOutput );
		*/
		$dirLog = self::getDirLog();
		if ( ! is_dir($dirLog) ) mkdir($dirLog);
			
		file_put_contents( "$dirLog/".time().".log",
			date("F j, Y, g:i a")."\r\n========================\r\n".$scriptData
			."\r\n========================\r\n".$lastExecResult
		);
//			."\r\n========================\r\n".print_r(debug_backtrace(),true)
//		);
		//self::$sLog->trace("\n{$lastExecResult}\n*************************************************");
		
		return $lastExecResult;
	}
	// DwShell::lz4Compress64($content);
	public static function lz4Compress64( string $content) : string {
		$lz4content64 = "echec compress lz4"; 
		$scriptContent = self::getInitScript();
		$scriptContent .= DwShell::getScript("which/lz4")."\n";
		$scriptContent .= DwShell::getScript("lib-sh/shLz4")."\n";
		$scriptContent .= "\$cmdPrintf \"".base64_encode($content)."\" | \$cmdBase64 -d -i 2>/dev/null | shLz4Compress64 \n";
		$lz4content64 = DwShell::exec($scriptContent);
		return $lz4content64;
	}
	// DwShell::lz4UnCompress64($compressedContent);
	public static function lz4UnCompress64( string $compressed64Content ) : string {
		$content = "echec uncompress lz4"; 
		$scriptContent = self::getInitScript();
		$scriptContent .= DwShell::getScript("which/lz4")."\n";
		$scriptContent .= DwShell::getScript("lib-sh/shLz4")."\n";
		$scriptContent .= "\$cmdPrintf \"$compressed64Content\" | shLz4UnCompress64 | \$cmdBase64 \n";
		$content = base64_decode( DwShell::exec($scriptContent) );
		return $content;
	}
}

?>

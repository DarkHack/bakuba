/*
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
// By Perplexity.ai Production :
var boundary = "---MYSEPARATOR---";
var buffer = "";

function base64decode(input) {
    var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var output = "";
    var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
    var i = 0;
    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
    while (i < input.length) {
        enc1 = keyStr.indexOf(input.charAt(i++));
        enc2 = keyStr.indexOf(input.charAt(i++));
        enc3 = keyStr.indexOf(input.charAt(i++));
        enc4 = keyStr.indexOf(input.charAt(i++));
        chr1 = ((enc1 << 2) | (enc2 >> 4)) & 255;
        chr2 = (((enc2 & 15) << 4) | (enc3 >> 2)) & 255;
        chr3 = (((enc3 & 3) << 6) | enc4) & 255;
        output += String.fromCharCode(chr1);
        if (enc3 != 64) output += String.fromCharCode(chr2);
        if (enc4 != 64) output += String.fromCharCode(chr3);
    }
    return output;
}

WScript.Echo("=== Start ===");
var cptTask=0;
while (!WScript.StdIn.AtEndOfStream) {
    var c = WScript.StdIn.Read(1);
    buffer += c;
    var idx = buffer.indexOf(boundary);
    while (idx > -1) {
        var codeBlock = buffer.substring(0, idx);
        if (codeBlock.length > 0) {
			cptTask++;
			var errMsg=null;
			var decodedBlock = null;
			if ( typeof jsLog !== 'undefined' )
				jsLog( jsLogLevels.INFO ,"[" + cptTask + "] task start" );
            try {
                decodedBlock = base64decode(codeBlock);
				//if ( typeof jsLog !== 'undefined' )
				//	jsLog( jsLogLevels.TRACE ,"[" + cptTask + "] " + decodedBlock );
				//WScript.Echo("--START BLOCK--");
				//WScript.Echo(decodedBlock);
				//WScript.Echo("--END BLOCK--");
                eval(decodedBlock);
            } catch(e) {
				var strDbgMsg = "EvalError: " + e.message;
				if (e.number)      strDbgMsg += " | number: " + e.number;
				if (e.name)        strDbgMsg += " | name: " + e.name;
				if (e.description) strDbgMsg += " | desc: " + e.description;
				if (e.lineNumber)  strDbgMsg += " | line: " + e.lineNumber;
				if (e.line)        strDbgMsg += " | line: " + e.line;
				if (e.fileName)    strDbgMsg += " | file: " + e.fileName;
    
				if ( typeof jsLog === 'undefined' )
					WScript.Echo( "EvalError: " + strDbgMsg );
				else {
					jsLog( jsLogLevels.ERROR ,"[" + cptTask + "] EvalError: " + strDbgMsg );
					jsLog( jsLogLevels.ERROR ,"[" + cptTask + "] " + decodedBlock );
				}
            }
			if ( typeof jsLog !== 'undefined' )
				jsLog( jsLogLevels.INFO ,"[" + cptTask + "] task ended" );
        }
        buffer = buffer.substring(idx + boundary.length);
        idx = buffer.indexOf(boundary);
    }
}
if (buffer.trim().length > 0) {
    try {
        var decodedBlock = base64decode(buffer.trim());
        eval(decodedBlock);
    } catch (e) {
        WScript.Echo("LastEvalError: " + e.message);
    }
}
WScript.Echo("=== Stop ===");

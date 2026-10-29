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
var jsStringDebug=false;
// Perplexity.ai production : (all functions)
function jsStringSplitLines( s ) {
    var arr = s.split(/\r?\n/);
    var out = [];
    for (var i = 0; i < arr.length; ++i) {
        if (arr[i] && arr[i].length > 0) out.push(arr[i]);
    }
    return out;
}
// Conversion string to hex in JavaScript
function jsStringStrToHex( str ) {
    var result = "";
    for (var i=0; i<str.length; i++) {
        result += ("0" + str.charCodeAt(i).toString(16)).slice(-2);
    }
    return result;
}
function jsStringTrim(str) {
    return typeof str==='string' ? str.replace(/^\s+|\s+$/g, '') : str;
}
function jsString64Filter( str ) {
    // Ne conserve que les caractères alphanumériques, +, =, /
    // Ajoute | si besoin
    return jsStringTrim( str.replace( /[^A-Za-z0-9+=/|]/g ,'' ) );
}
// UTF8 BOM Filtered 
function jsStringStrToB64ByActivX(str) {
	var jsLogfunc="jsStringStrToB64ByActivX( str )";
    var stream = new ActiveXObject("ADODB.Stream");
    stream.Type = 2; // adTypeText
    stream.Charset = "utf-8";
    stream.Open();
    stream.WriteText(str);
    // Revenir au début, changer en mode binaire
    stream.Position = 0;
    stream.Type = 1; // adTypeBinary

    // Lire le buffer binaire
    var bytes = stream.Read();
    stream.Close();
	// Patch universel : convertir tout buffer binaire ADODB en JS array
	try {
		if (typeof VBArray !== 'undefined' && typeof bytes.toArray === 'undefined') {
			bytes = new VBArray(bytes).toArray();
		}
	} catch (e) {
		// Si la conversion échoue ou que bytes est déjà nul, bytes=null
		bytes = null;
	}
	
	var data64Response=null;
	if (!bytes || typeof bytes != 'object')
		jsStringDebug && jsLog(jsLogLevels.ERROR, "[jsString.js/" + jsLogfunc + "] bytes null or not object or length <= 0 | str='" + str + "'")
	else {
		jsStringDebug && jsLog(jsLogLevels.ERROR, "[jsString.js/" + jsLogfunc + "] bytes:" + bytes.length );
		// Sauter le BOM si présent
		var bom = [0xEF, 0xBB, 0xBF];
		var skipBOM = true;
		for (var i = 0; i < 3; i++) {
			if (bytes[i] != bom[i]) skipBOM = false;
		}
		if (skipBOM) {
			jsStringDebug && jsLog( jsLogLevels.ERROR ,"[jsString.js/" + jsLogfunc + "] skipBOM" );
			// Remove BOM
			bytes = bytes.slice(3);
		} else 
			jsStringDebug && jsLog( jsLogLevels.ERROR ,"[jsString.js/" + jsLogfunc + "] no skipBOM needed" );
		
		// Passer à MSXML pour encoder en base64
		var xml = new ActiveXObject("MSXML2.DOMDocument.3.0");
		var elem = xml.createElement("tmp");
		elem.dataType = "bin.base64";
		elem.nodeTypedValue = bytes;
		data64Response=elem.text;
	}
    return data64Response;
}

function jsStringStrToB64(str) {
    // version texte sans ADODB: base64 ABC pour ascii only!
    var b64table = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    var out = "", i = 0, c1, c2, c3;
    while (i < str.length) {
        c1 = str.charCodeAt(i++) & 0xff;
        if (i == str.length) {
            out += b64table.charAt(c1 >> 2);
            out += b64table.charAt((c1 & 0x3) << 4);
            out += "==";
            break;
        }
        c2 = str.charCodeAt(i++);
        if (i == str.length) {
            out += b64table.charAt(c1 >> 2);
            out += b64table.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
            out += b64table.charAt((c2 & 0xF) << 2);
            out += "=";
            break;
        }
        c3 = str.charCodeAt(i++);
        out += b64table.charAt(c1 >> 2);
        out += b64table.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
        out += b64table.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
        out += b64table.charAt(c3 & 0x3F);
    }
    return jsStringTrim( out.replace(/[\x00-\x1F\x7F-\x9F\uFFFD]+$/g, '') );
}
function jsStringB64ToStr(b64) {
    var out = "", i = 0, c1, c2, c3, c4, charCode;
    var b64table = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    // Retire padding si présent
    b64 = b64.replace(/[^A-Za-z0-9+/]/g, "");
    while (i < b64.length) {
        c1 = b64table.indexOf(b64.charAt(i++));
        c2 = b64table.indexOf(b64.charAt(i++));
        c3 = b64table.indexOf(b64.charAt(i++));
        c4 = b64table.indexOf(b64.charAt(i++));
        charCode = (c1 << 2) | (c2 >> 4);
        out += String.fromCharCode(charCode);
        if (c3 != -1 && c3 != 64) {
            charCode = ((c2 & 15) << 4) | (c3 >> 2);
            out += String.fromCharCode(charCode);
        }
        if (c4 != -1 && c4 != 64) {
            charCode = ((c3 & 3) << 6) | c4;
            out += String.fromCharCode(charCode);
        }
    }
    out = jsStringTrim( out );
	// Remove trailing invalid characters (null, \x00, or non-ascii)
	out = out.replace(/[\x00-\x1F\x7F-\x9F]/g, '');
	// ou, si tu veux couper tout ce qui n'est pas visible après un char utile :
	out = out.replace(/[\uFFFD]+$/g, ''); // 
	return out;
}
function jsStringExtractBetweenBoundaries(str, boundary) {
	var jsLogfunc="jsStringExtractBetweenBoundaries( str length:" + str.length + ", " + boundary + " )";

    var regex = new RegExp(boundary + "([A-Za-z0-9+/=]+)" + boundary);
    var match = regex.exec(str);
	var result = null;
	if ( match )
		result = match[1].replace(/[\r\n]/g,"");
	else {
		var regex = new RegExp(boundary + "([A-Za-z0-9+/=\\s]+)" + boundary);
		var match = regex.exec(str);
		if ( match )
			result = match[1].replace(/[\r\n]/g,"");
		else {
			var regex = new RegExp(boundary + "\\s*([A-Za-z0-9+/=\\s]+)\\s*" + boundary,"m");
			//var regex = new RegExp(startBoundary + "([A-Za-z0-9+/=\\s]+)" + stopBoundary, "m");
			var match = regex.exec(str);
			if ( match )
				result = match[1].replace(/[\r\n]/g,"");
			else {
				var regex = new RegExp(boundary + "([A-Za-z0-9+/=\\r\\n]+)" + boundary);
				var match = regex.exec(str);
				if ( match )
					result = match[1].replace(/[\r\n]/g,"");
				else
					jsStringDebug && jsLog( jsLogLevels.ERROR ,"[jsString.js/" + jsLogfunc + "] no data boundary found" );
				
			}
		}
		
	}
	if (result) result = result.replace(/^[\\s\\r\\n]+|[\\s\\r\\n]+$/g, "");
    return jsStringTrim( result );
}
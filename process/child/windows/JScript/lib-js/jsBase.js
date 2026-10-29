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
// Perplexity.ai production :

function jsMergePath( processPath ,systemPath ) {
	// Découpe et nettoyage (evite doublons)
	var processParts = processPath.split(";");
	var systemParts  = systemPath.split(";");
	var allParts = [].concat(processParts);
	
	// Index rapide pour éviter doublons stricts (insensible à la casse)
	var partIndex = {};
	for (var i=0;i<processParts.length;i++) partIndex[processParts[i].toLowerCase()] = 1;
	
	for (var i=0; i<systemParts.length;i++) {
		var s = systemParts[i];
		if (!partIndex[s.toLowerCase()]) {
			allParts.push(s);
			partIndex[s.toLowerCase()] = 1;
		}
	}
	// Reconstruction du PATH (éventuellement limit length si win32)
	return allParts.join(";");
	// Optionnel : tronquer à X caractères si bug de Windows
	//ws.Environment("Process")("PATH") = mergedPath;
}

var jsRun_ws = new ActiveXObject("WScript.Shell");
jsLog( jsLogLevels.DEBUG ,"process PATH:" + jsRun_ws.Environment("Process")("PATH") );
jsLog( jsLogLevels.DEBUG ,"system PATH:" + jsRun_ws.Environment("System")("PATH") );

//jsLog(jsLogLevels.DEBUG, "process PATH final : " + ws.Environment("Process")("PATH"));
jsRun_ws.Environment("Process")("PATH") = jsMergePath( jsRun_ws.Environment("Process")("PATH") ,jsRun_ws.Environment("System")("PATH") );

jsLog( jsLogLevels.DEBUG ,"process new PATH:" + jsRun_ws.Environment("Process")("PATH") );

var jsRunDebug=false;
function jsRun(cmd, dataToPipe) {
    if  ( (typeof dataToPipe === "string" && dataToPipe.length > 0) || dataToPipe )
		var fullCmd = cmd;
	else
		var fullCmd = 'cmd.exe /c "' + cmd + '"';
		
    jsRunDebug && jsLog(jsLogLevels.TRACE, "jsRun:" + fullCmd);
    var exec = jsRun_ws.Exec(fullCmd);
    var output = "";

    // -- Écriture dans le pipe STDIN si dataToPipe est renseigné --
    if (typeof dataToPipe === "string" && dataToPipe.length > 0) {
		jsRunDebug && jsLog(jsLogLevels.TRACE, "jsRun write:" + dataToPipe.length);
        exec.StdIn.Write(dataToPipe);
        exec.StdIn.Close();
    }

    // -- Lecture classique du résultat --
    while (!exec.StdOut.AtEndOfStream)
        output += exec.StdOut.ReadLine() + "\n";

    jsRunDebug && jsLog(jsLogLevels.TRACE, "jsRun:" + output);
    return output;
}
function jsWhere(exeName) {
    // On cherche le chemin d'un exécutable en appelant 'where' (le which de Windows)
    // On retourne soit le premier chemin trouvé, soit vide si inexistant
	var lignes = jsStringSplitLines( jsRun( "where " + exeName ) );
	return (!lignes || lignes.length === 0 ? null : lignes[0] );
}
/*
var resultOpenSSL = jsRun("where openssl");
jsLog(jsLogLevels.DEBUG, "where openssl result:" + resultOpenSSL);
this.cmdOpenssl = jsWhere("openssl");
jsLog(jsLogLevels.DEBUG, "openssl:"+this.cmdOpenssl);

// Test présence des commandes
jsErrorOnMissing([ 'cmdOpenssl' ,'cmdBase64' ]);
//*/


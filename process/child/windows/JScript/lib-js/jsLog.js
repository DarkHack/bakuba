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
if ( typeof jsLogFile === "undefined" ) var jsLogFile="logger.log";
// Table de niveaux/labels/logs
var jsLogLevels = {
  NONE: 0,
  ERROR: 1,
  WARNING: 3,
  INFO: 4,
  DEBUG: 2,
  TRACE: 5
};

// Vérification de la présence des "cmd" (ici simulé par JS)
function jsErrorOnMissing(cmdList) {
    for (var i = 0; i < cmdList.length; ++i) {
        var cmd = cmdList[i];
        if ( typeof this[cmd] == "undefined" || this[cmd] === null ) {
            jsLog(jsLogLevels.ERROR, "jsErrorOnMissing missing :" + cmd);
            WScript.Quit(1);
        }
    }
}
var fso = new ActiveXObject("Scripting.FileSystemObject");

// WSH JavaScript (.js), exécuté avec cscript.exe
function jsLog(level, msg) {
  if ( jsLogFile === false ) return ;
  var titles = ["NONE", "ERREUR", "DEBUG", "WARNING", "INFO", "TRACE"];
  var now = new Date();
  var timeStr = now.getFullYear() + "/" +
                ("0" + (now.getMonth() + 1)).slice(-2) + "/" +
                ("0" + now.getDate()).slice(-2) + " " +
                ("0" + now.getHours()).slice(-2) + ":" +
                ("0" + now.getMinutes()).slice(-2) + ":" +
                ("0" + now.getSeconds()).slice(-2);
  var jsTitle = titles[level] || "UNKNOW";
  var fileErr = fso.OpenTextFile( jsLogFile, 8, true);
  fileErr.WriteLine("[" + jsTitle + " " + timeStr + "] " + msg);
  fileErr.Close();
//  WScript.Echo("[" + jsTitle + " " + timeStr + "] " + msg);
}

// Exemple d’utilisation
jsLog(jsLogLevels.DEBUG, "debug message");
jsLog(jsLogLevels.INFO, "info message");
jsLog(jsLogLevels.ERROR, "erreur fatale");

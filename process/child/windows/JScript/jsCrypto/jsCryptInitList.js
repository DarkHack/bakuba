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
// With Perplexity.ai production :

//var resultOpenSSL = jsRun("where openssl");
//jsLog(jsLogLevels.DEBUG, "where openssl result:" + resultOpenSSL);
this.cmdOpenssl = jsWhere("openssl");
jsCryptDebug && jsLog( jsLogLevels.DEBUG ,"[jsCryptInitList.js] openssl:" + this.cmdOpenssl );

jsErrorOnMissing([ 'cmdOpenssl' ]);

function jsCryptCmdListParse(opensslListCMD, removeTiret) {
    if (typeof removeTiret === 'undefined') removeTiret = false; // WSH default syntax
    jsCryptDebug && jsLog(jsLogLevels.TRACE, "[jsCryptInitList.js] opensslListCMD:");
    jsCryptDebug && jsLog(jsLogLevels.TRACE, opensslListCMD);

    // Découpage : split sur toute séquence blanche (y compris tab, CR/LF)
    var arr = (opensslListCMD && opensslListCMD.length > 0)
        ? opensslListCMD.split(/\s+/)
        : [];

    // Filtrer tout ce qui est vide ou ne contient QUE blanc/tab après trim
    var cleanArr = [];
    for (var i = 0; i < arr.length; i++) {
        if (arr[i] && arr[i].replace(/^\s+|\s+$/g, '').length > 0)
            cleanArr.push(arr[i]);
    }

    // Si removeTiret : on garde seulement les ciphers/digests avec '-' dans le nom
    if (removeTiret) {
        var filtered = [];
        for (var i = 0; i < cleanArr.length; i++) {
            if (cleanArr[i].indexOf('-') > -1)
                filtered.push(cleanArr[i].split('-').slice(1).join('-'));
        }
        cleanArr = filtered;
    }
    return cleanArr;
}


//jsRunDebug=true;
var jsCryptCiphersFound=jsCryptCmdListParse( jsRun( this.cmdOpenssl + " list-cipher-commands 2>nul" ) );
if ( ! jsCryptCiphersFound || jsCryptCiphersFound.length==0 )
	jsCryptCiphersFound=jsCryptCmdListParse( jsRun( this.cmdOpenssl + " enc -ciphers 2>nul" ) ,true );

if ( jsCryptCiphersFound && jsCryptCiphersFound.length > 0 )
	 jsCryptCiphers=jsCryptCiphersFound;

jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptInitList.js] jsCryptCiphers" );
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptInitList.js] totalCyphers:" + jsCryptCiphers.length );
//jsLog( jsLogLevels.DEBUG ,jsCryptCiphers.join( '.' ) );

//jsRunDebug=true;
jsCryptDebug=true;
var jsCryptDigestsFound=jsCryptCmdListParse( jsRun( this.cmdOpenssl + " list-message-digest-commands 2>nul" ) );
if ( ! jsCryptDigestsFound || jsCryptDigestsFound.length==0 )
	jsCryptDigestsFound=jsCryptCmdListParse( jsRun( this.cmdOpenssl + " list -digest-commands 2>nul" ) );

var jsCryptdigests=[];
if ( jsCryptDigestsFound && jsCryptDigestsFound.length > 0 )
	 jsCryptdigests=jsCryptDigestsFound;
 
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptInitList.js] jsCryptdigests" );
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptInitList.js] totalDigests:" + jsCryptdigests.length );
jsCryptDebug && jsLog( jsLogLevels.DEBUG ,jsCryptdigests.join( '.' ) );
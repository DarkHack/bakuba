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
var jsCryptDebug=true;

//cryptMessagePipe=$(shPipeInit  2>/dev/null )
//shPipeOpen $cryptMessagePipe 2>/dev/null
//cryptPassPipe=$(shPipeInit 2>/dev/null )
//shPipeOpen $cryptPassPipe 2>/dev/null

// ! ici, si on es bon, pourrais devenir variable, voir rolling a chaque message, lié à sa session !
var jsCryptJsonPass="pass";
var jsCryptJsonSalt="salt";
var jsCryptJsonCipher="cipher";
var jsCryptJsonCiphers="ciphers";
var jsCryptJsonIv="iv";
var jsCryptJsonKey="key";
var jsCryptJsonDigest="digest";
var jsCryptJsonDigests="digests";
var jsCryptJsonBundary="bundary";
var jsCryptJsonData="data";
// /! ici, si on es bon, pourrais devenir variable, voir rolling a chaque message, lié à sa session /!

// seed-ecb: echec on key 0 / REALY NOT WORK ON PORTABLE
// aes128: no key / NOT WORK ON PORTABLE
jsCryptCiphers=[ "aes-128-cbc", "aes-128-ecb" ];
//jsCryptdigests=[ 'md4', 'md5' ];

/*
jsCryptCiphers=[ "aes-128-cbc" ];
jsCryptdigests=[ 'md4' ];
//*/

jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js] jsCryptCiphers default:" + jsCryptCiphers.length );
//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js] jsCryptCiphers default:" + jsCryptCiphers.join(" ") );

//md4 md5 rmd160 sha sha1
//jsCryptdigests=[ "md4" ,"md5" ,"rmd160" ,"sha" ,"sha1" ];
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js] jsCryptdigests" );
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js] totalDigests:" + jsCryptdigests.length );
//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js] jsCryptCiphers default:" + jsCryptdigests.join(" ") );

// Perplexity Production :
function jsCryptListAnRandom( anList ) {
 // Choix aléatoire d'un algo depuis le tableau
 return anList[Math.floor(Math.random() * anList.length)];
}
var jsCryptTestCipher = jsCryptListAnRandom( jsCryptCiphers );
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js] test random cipher:" +jsCryptTestCipher  );

function jsCryptCipherIvSize( cipher ) {
	var jsLogfunc="jsCryptCipherIvSize( "+ cipher +" )";
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "]" );
	var cipherIvSize=null;
	if ( typeof jsCryptDbCiphersSize[ cipher ] === 'undefined' )
		jsLog( jsLogLevels.ERROR ,"[jsCrypt.js/" + jsLogfunc + "] no iv size for :" + cipher );
	else
		cipherIvSize = jsCryptDbCiphersSize[cipher];
	return cipherIvSize;
	/*
	for (var i = 0; i < jsCryptDbCiphersSize.length; i++) {
		
		var cipherData = jsCryptDbCiphersSize[i];
            if (cleanArr[i].indexOf('-') > -1)
                filtered.push(cleanArr[i].split('-').slice(1).join('-'));
        }
	//*/
}
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js] test iv cipher size :" + jsCryptCipherIvSize( jsCryptTestCipher ) );

// jsCryptInit()
function jsCryptInit() {
	//var jsLogfunc="shCryptInit()";
	//jsCryptDebug && jsLog( jsLogLevels.TRACE , jsLogfunc );
	var jsonCryptMessage = jsonInObjectWrite( "{}" ,jsCryptJsonBundary ,jsRandomAscii( 16 ) );
	jsonCryptMessage = jsonInObjectWrite( jsonCryptMessage ,jsCryptJsonPass ,jsRandomAscii( 10 ) );
	jsonCryptMessage = jsonInObjectWrite( jsonCryptMessage ,jsCryptJsonSalt ,jsRandomAscii( 8 ) );
	return jsonCryptMessage;
}
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"test jsCryptInit:" + jsCryptInit() );

// jsCryptInitCipher( cipher ,digest );
function jsCryptInitCipher( cipher ,digest ) {
	var jsLogfunc="jsCryptInitCipher( "+ cipher +" ,"+ digest + ")";
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "]");
	// generate pass 1ko, salt 8octet
	var jsonInit=jsonInObjectWrite( jsCryptInit() ,jsCryptJsonCipher ,cipher );
	jsonInit=jsonInObjectWrite( jsonInit ,jsCryptJsonDigest ,digest );
	// read iv/key size of ciphers
	var cipherIvSize=jsCryptCipherIvSize( cipher );
	if ( typeof cipherIvSize === "string" && cipherIvSize > 0 ) {
		var	jsLogfunc="jsCryptInitCipher( " + cipher + "/" + digest +"/iv:" + cipherIvSize + ")";
		var cipherKeySize=cipherIvSize;
		//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] cipherKeySize:" + cipherKeySize );
		
		jsonInit=jsonInObjectWrite( jsonInit ,jsCryptJsonKey ,jsRandomAscii( cipherKeySize ) );
		jsonInit=jsonInObjectWrite( jsonInit ,jsCryptJsonIv ,jsRandomAscii( cipherIvSize ) );
	}
	return jsonInit;
}
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"test jsCryptInit:" + jsCryptInitCipher( jsCryptTestCipher, jsCryptListAnRandom( jsCryptdigests ) ) );


// jsCryptInitMessage( listCiphers ,listDigests )
function jsCryptInitMessage( listCiphers ,listDigests ) {
	//var jsLogfunc="jsCryptInitMessage()";
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] totalDigests:" + jsCryptdigests.length );
	return jsCryptInitCipher ( jsCryptListAnRandom( listCiphers ), jsCryptListAnRandom( listDigests ) );
}
var jsCryptTestJsonMessage = jsCryptInitMessage( jsCryptCiphers, jsCryptdigests );
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"test jsCryptInit:" + jsCryptTestJsonMessage );

function jsCryptEncode( jsonMessage ) {
	var jsLogfunc="jsCryptEncode()";
	
	var cipher=jsonInObjectRead( jsonMessage ,jsCryptJsonCipher );
	var digest=jsonInObjectRead( jsonMessage ,jsCryptJsonDigest );
	var pass=jsonInObjectRead( jsonMessage ,jsCryptJsonPass );
	var salt=jsonInObjectRead( jsonMessage ,jsCryptJsonSalt );
	var iv= jsonInObjectRead( jsonMessage ,jsCryptJsonIv );
	var key= jsonInObjectRead( jsonMessage ,jsCryptJsonKey );
	var bundary= jsonInObjectRead( jsonMessage ,jsCryptJsonBundary );
	
	var jsLogfunc="jsCryptEncode( " + cipher + " ," + digest + ")";
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] bundary:" + bundary );
	
	var passSize=pass.length;
	var saltSize= salt === null ? 0 : salt.length;
	var ivSize= iv === null ? 0 : iv.length;
	var keySize= key === null ? 0 : key.length;
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] pass(" + passSize +") salt(" + saltSize + ") iv(" + ivSize + ") key(" + keySize + ")" );
	
	var mdOption=" -md " + digest +" ";
	var saltOption="";
	if ( saltSize <= 0 )
		saltOption=" -nosalt ";
	else {
		var saltHex=jsStringStrToHex( salt );
		// jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] saltHex:" + saltHex );
		saltOption=" -salt -S " + saltHex +" "
	}
	var ivKeyOption="";
	if ( ivSize > 0 && keySize > 0 ) {
		var ivHex= jsStringStrToHex( iv );
		var keyHex= jsStringStrToHex( key );
		var ivKeyOption="-K " + keyHex +" -iv " + ivHex;
		
		// jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] ivHex/keyHex:" + ivHex + "/" + keyHex );
	}
	var fso = new ActiveXObject("Scripting.FileSystemObject");
	var tempFile = fso.GetSpecialFolder(2) + "\\" + "jsCryptEnc_" + Math.floor( Math.random() * 1000 ) +  "-" + (new Date()).getTime() + ".tmp";
	var f = fso.CreateTextFile(tempFile, true);
	f.WriteLine(pass);
	f.Close();
	var shortPath = fso.GetFile(tempFile).ShortPath;
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] pass file:" + tempFile );
	var encrypt_cmd= this.cmdOpenssl + " enc -" + cipher + " -pass file:"+ shortPath +" " + ivKeyOption + " " + saltOption +" " + mdOption;
	//var encrypt_cmd= this.cmdOpenssl + " enc -" + cipher + " -pass pass:"+ pass +" " + ivKeyOption + " " + saltOption +" " + mdOption;
	
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] encrypt_cmd" + encrypt_cmd );
	
	jsStringDebug=true;
	var jsonMessageData = jsonInObjectRead( jsonMessage ,jsCryptJsonData ); 
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] jsonMessageData:" + jsonMessageData );
	var jsonMessageData64 = jsStringStrToB64( jsonMessageData );
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] jsonMessageData64:" + jsonMessageData64 );
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] jsonMessageData64 by ActivX:" + jsStringStrToB64ByActivX( jsonMessageData ) );
	
	var jsonMessageData64Filtered = jsString64Filter(  jsonMessageData64 );
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] jsonMessageData64Filtered:" + jsonMessageData64Filtered );
	
	var randStart = jsRandomAscii( 10 );
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] randStart:" + randStart );
	var randStop = jsRandomAscii( 10 );
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] randStop:" + randStop );
	
	var bundaryDataToEncode = randStart + bundary + jsonMessageData64Filtered + bundary + randStop;
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] bundaryDataToEncode:" + bundaryDataToEncode );
	
	var dataEncoded = jsRun( "echo " + bundaryDataToEncode + " | " + encrypt_cmd + " | "+ this.cmdOpenssl +" enc -base64" );
	fso.DeleteFile(tempFile);
	
	return jsString64Filter( dataEncoded );
}
jsCryptTestJsonMessage=jsonInObjectWrite( jsCryptTestJsonMessage ,jsCryptJsonData ,"coucou" );
jsCryptTestJsonMessage=jsonInObjectWrite( jsCryptTestJsonMessage ,jsCryptJsonPass ,"anPassWordLikeAllAnotherPassword" );
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"test jsCryptTestJsonMessage:" + jsCryptTestJsonMessage );

var b64Crypted = jsCryptEncode( jsCryptTestJsonMessage );
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"test encoded:" + b64Crypted );

function jsCryptDecode( jsonMessage ) {
	var jsLogfunc="jsCryptDecode()";
	
	var cipher=jsonInObjectRead( jsonMessage ,jsCryptJsonCipher );
	var digest=jsonInObjectRead( jsonMessage ,jsCryptJsonDigest );
	var pass=jsonInObjectRead( jsonMessage ,jsCryptJsonPass );
	var salt=jsonInObjectRead( jsonMessage ,jsCryptJsonSalt );
	var iv= jsonInObjectRead( jsonMessage ,jsCryptJsonIv );
	var key= jsonInObjectRead( jsonMessage ,jsCryptJsonKey );
	var bundary= jsonInObjectRead( jsonMessage ,jsCryptJsonBundary );
	
	var jsLogfunc="jsCryptDecode( " + cipher + " ," + digest + ")";
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] bundary:" + bundary );
	
	var passSize=pass.length;
	var saltSize= salt === null ? 0 : salt.length;
	var ivSize= iv === null ? 0 : iv.length;
	var keySize= key === null ? 0 : key.length;
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] pass(" + passSize +") salt(" + saltSize + ") iv(" + ivSize + ") key(" + keySize + ")" );
	
	var mdOption=" -md " + digest +" ";
	var saltOption="";
	if ( saltSize <= 0 )
		saltOption=" -nosalt ";
	else {
		var saltHex=jsStringStrToHex( salt );
		// jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] saltHex:" + saltHex );
		saltOption=" -salt -S " + saltHex +" "
	}
	var ivKeyOption="";
	if ( ivSize > 0 && keySize > 0 ) {
		var ivHex= jsStringStrToHex( iv );
		var keyHex= jsStringStrToHex( key );
		var ivKeyOption="-K " + keyHex +" -iv " + ivHex;
		
		// jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] ivHex/keyHex:" + ivHex + "/" + keyHex );
	}
	var fso = new ActiveXObject("Scripting.FileSystemObject");
	var tempFile = fso.GetSpecialFolder(2) + "\\" + "jsCryptDec_" + Math.floor( Math.random() * 1000 ) +  "-" + (new Date()).getTime() + ".tmp";
	var f = fso.CreateTextFile(tempFile, true);
	f.WriteLine(pass);
	f.Close();
	var shortPath = fso.GetFile(tempFile).ShortPath;
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] pass file:" + tempFile );
	var decrypt_cmd= this.cmdOpenssl + " enc -" + cipher + " -d -pass file:"+ shortPath +" " + ivKeyOption + " " + saltOption +" " + mdOption;
	//var decrypt_cmd= this.cmdOpenssl + " enc -" + cipher + " -d -pass pass:"+ pass +" " + ivKeyOption + " " + saltOption +" " + mdOption;
	
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] decrypt_cmd:" + decrypt_cmd );
	var dataEncoded64 = jsonInObjectRead( jsonMessage ,'data' );
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] dataEncoded64:" + dataEncoded64 );
	var dataEncoded64Filtered = jsString64Filter( dataEncoded64 );
	//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] dataEncoded64Filtered:" + dataEncoded64Filtered );
	
	var dataDecoded = jsRun( "echo " + dataEncoded64Filtered + " | "+ this.cmdOpenssl +" enc -base64 -d | " + decrypt_cmd  );
	fso.DeleteFile(tempFile);
	
	var resultDecoded = null;
	if ( typeof dataDecoded !== "string" || dataDecoded === null || dataDecoded.length<=0 )
		jsLog( jsLogLevels.ERROR ,"[jsCrypt.js/" + jsLogfunc + "] dataDecoded echec " + dataDecoded );
	else {
		var dataDecodedFiltered = jsString64Filter( dataDecoded );
		//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] dataDecoded(filtered):" + dataDecodedFiltered );
		var jsStringDebug=true;
		var dataUtil = jsStringExtractBetweenBoundaries( dataDecodedFiltered ,bundary );
		if ( typeof dataUtil !== "string" || dataUtil === null || dataUtil.length<=0 )
			jsLog( jsLogLevels.ERROR ,"[jsCrypt.js/" + jsLogfunc + "] echec jsStringExtractBetweenBoundaries('" + bundary + "') :" + dataDecodedFiltered );
		else {
			var clearB64Data= jsString64Filter( dataUtil );
			//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] clearB64Data:" + clearB64Data );
			resultDecoded=jsStringB64ToStr( clearB64Data );
			//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] resultDecoded:" + resultDecoded );
		}
	}
	return resultDecoded;
}

jsCryptTestJsonMessage=jsonInObjectWrite( jsCryptTestJsonMessage ,jsCryptJsonData ,b64Crypted );
//jsCryptDebug && jsLog( jsLogLevels.TRACE ,"test decoded:" + jsCryptTestJsonMessage );
jsCryptDebug && jsLog( jsLogLevels.TRACE ,"test decoded:" + jsCryptDecode( jsCryptTestJsonMessage ) );

function jsCryptListUnionCommon( lList ,rList ) {
	var jsLogfunc="jsCryptListUnionCommon( " + lList + " ," + rList + ")";
	jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] " );
	
	var fList=[];
	for (var i = 0; i < lList.length; i++) {
		for (var j = 0; j < rList.length; j++) {
			if ( lList[ i ] == rList[ j ] ) {
				fList.push( lList[ i ] );
				break;
			}
		}
	}
	jsCryptDebug && jsLog( jsLogLevels.TRACE ,"[jsCrypt.js/" + jsLogfunc + "] fList :".fList.join(" ") );
	
	return fList;
}

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
var jsCryptTestDebug=true;

//clearResponseData=jsCryptTestCipher( jsonMessage );
function jsCryptTestCipher( jsonMessage ) {
	var jsLogfunc="jsCryptTestCipher()";
	WScript.Sleep( 5000 ); // Pause de 50 * 100 ms
	//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "]" );
	//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "]"+jsonMessage );
	
	var cipher=jsonInObjectRead( jsonMessage ,jsCryptJsonCipher );
	var digest=jsonInObjectRead( jsonMessage ,jsCryptJsonDigest );
	var pass=jsonInObjectRead( jsonMessage ,jsCryptJsonPass );
	var salt=jsonInObjectRead( jsonMessage ,jsCryptJsonSalt );
	var iv= jsonInObjectRead( jsonMessage ,jsCryptJsonIv );
	var key= jsonInObjectRead( jsonMessage ,jsCryptJsonKey );
	//var bundary= jsonInObjectRead( jsonMessage ,jsCryptJsonBundary );
	var jsLogfunc="jsCryptTestCipher( " + cipher + " ," + digest + ")";
	//jsCryptDebug && jsLog( jsLogLevels.ERROR ,"[jsCrypt.js/" + jsLogfunc + "] bundary:" + bundary );
	
	var passSize=pass.length;
	var saltSize= salt === null ? 0 : salt.length;
	var ivSize= iv === null ? 0 : iv.length;
	var keySize= key === null ? 0 : key.length;
	
	var clearData = jsonInObjectRead( jsonMessage ,jsCryptJsonData );
	var dataSize=clearData.length;
	
	//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] data(" + dataSize + ") pass(" + passSize + ") salt:(" + saltSize + ") iv(" + ivSize + ") key(" + keySize + ")" );
	
	var cryptData= jsCryptEncode( jsonMessage );
	//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] cryptData:" + cryptData.length + " will try to decrypt" );
	
	//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] clearData:"+clearData );
	//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] cryptData:"+cryptData );
	var jsonMessageCrypt=jsonInObjectWrite ( jsonMessage ,jsCryptJsonData ,cryptData);
	return jsCryptDecode( jsonMessageCrypt );
}
function jsCryptTestCiphers( tCiphers ,tDigests ,testDataSize ) {
	var jsLogfunc="jsCryptTestCiphers( tCiphers:"+ tCiphers.length +" / tDigests:" + tDigests.length +" )";
	var testData= jsRandomAscii( typeof testDataSize==='undefined' ? 128 : testDataSize );
	jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] testData:"+testData );
	
	var resultDigestsCiphersTest=[];
	
	for ( var idxDigest=0 ; idxDigest<tDigests.length ; idxDigest++ ) {
		var testDigest=tDigests[ idxDigest ];
		var ciphersSalt=[];
		var ciphersNoSalt=[];
		var ciphersNoIVSalt=[];
		var ciphersNoIVNoSalt=[];
		var ciphersReject=[];
		
		for ( var idxCipher=0 ; idxCipher<tCiphers.length ; idxCipher++ ) {
			var testCipher=tCiphers[idxCipher];
			//var cipherIvSize=jsCryptCipherIvSize( testCipher );
			//var jsLogfunc="jsCryptTestCiphers( testCipher:"+ testCipher +"/iv:" + cipherIvSize + " )";
			var cipherAccept=0;
			var	jsonMessageInit= jsCryptInitCipher( testCipher ,testDigest );
			
			var cipher=jsonInObjectRead( jsonMessageInit ,jsCryptJsonCipher );
			var digest=jsonInObjectRead( jsonMessageInit ,jsCryptJsonDigest );
			var pass=jsonInObjectRead( jsonMessageInit ,jsCryptJsonPass );
			var salt=jsonInObjectRead( jsonMessageInit ,jsCryptJsonSalt );
			var iv= jsonInObjectRead( jsonMessageInit ,jsCryptJsonIv );
			var key= jsonInObjectRead( jsonMessageInit ,jsCryptJsonKey );
			//var bundary= jsonInObjectRead( jsonMessageInit ,jsCryptJsonBundary );
			var jsLogfunc="jsCryptTestCiphers( " + cipher + " ," + digest + ")";
			//jsCryptDebug && jsLog( jsLogLevels.ERROR ,"[jsCrypt.js/" + jsLogfunc + "] bundary:" + bundary );
			
			var passSize=pass.length;
			var saltSize= salt === null ? 0 : salt.length;
			var ivSize= iv === null ? 0 : iv.length;
			var keySize= key === null ? 0 : key.length;
			
			//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] pass:" + passSize + " salt:" + saltSize + " iv:" + ivSize );
			
			var jsonMessage=jsonInObjectWrite( jsonMessageInit ,jsCryptJsonData ,testData );
			var clearData=jsCryptTestCipher( jsonMessage );
			if ( clearData == testData ) {
				ciphersSalt.push( cipher );
				var cipherAccept=1;
				//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] accept " + cipher +" with salt" + ( ivSize > 0 ? " no iv compatible" : " and iv" ) );
			}// else
			//	jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] reject " + cipher +" with salt" + ( ivSize > 0 ? " no iv compatible" : " and iv" ) );
		
			//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "]  test without salt" );
			
			var jsonMessageWithoutSalt=jsonInObjectWrite ( jsonMessage ,jsCryptJsonSalt ,null );
			var clearData=jsCryptTestCipher( jsonMessageWithoutSalt );
			if ( clearData == testData ) {
				ciphersNoSalt.push( cipher );
				var cipherAccept=1;
				//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] accept " + cipher +" without salt" + ( ivSize > 0 ? " no iv compatible" : " with iv"  ) );
			}// else
			//	jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] reject " + cipher +" without salt" + ( ivSize > 0 ? " no iv compatible" : " with iv"  ) );
			if ( ivSize <= 0 ) {
				if ( cipherAccept <= 0 ) {
					ciphersReject.push( cipher );
					jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] reject " + cipher + " cause not work with/without salt" );
				}
			} else {
				//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] test with salt and without IV" );
				
				var jsonMessageWithoutIV=jsonInObjectWrite ( jsonMessage ,jsCryptJsonIv ,null );
				var jsonMessageWithoutIV=jsonInObjectWrite ( jsonMessageWithoutIV ,jsCryptJsonKey ,null );
				var clearData=jsCryptTestCipher( jsonMessageWithoutIV );
				if ( clearData == testData ) {
					ciphersNoIVSalt.push( cipher );
					var cipherAccept=1;
					//jsCryptTestDebug && jsLog( jsLogLevels.INFO ,"[jsCryptTest.js/" + jsLogfunc + "] accept " + cipher + " with salt and without IV" );
				}// else
				//	jsCryptTestDebug && jsLog( jsLogLevels.INFO ,"[jsCryptTest.js/" + jsLogfunc + "] reject " + cipher + " with salt and without IV" );
				
				//jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] test without salt and without IV" );
				var jsonMessageWithoutIVWithOutSalt=jsonInObjectWrite ( jsonMessageWithoutIV ,jsCryptJsonSalt ,null );
				
				var clearData=jsCryptTestCipher( jsonMessageWithoutIVWithOutSalt );
				
				if ( clearData == testData ) {
					ciphersNoIVNoSalt.push( cipher );
					var cipherAccept=1;
					//jsCryptTestDebug && jsLog( jsLogLevels.INFO ,"[jsCryptTest.js/" + jsLogfunc + "] accept " + cipher + " without salt and without IV" );
				}
				if ( cipherAccept <= 0 ) {
					ciphersReject.push( cipher );
					jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "] final reject " + cipher + " cause not work with/without salt with/wihtout iv" );
				}
			}
		}
		jsCryptTestDebug && jsLog( jsLogLevels.INFO ,"[jsCryptTest.js/" + jsLogfunc + "] result[dataSize:" + testData.length + "] " + testDigest +" Salt:" + ciphersSalt.length + " NoSalt:" + ciphersNoSalt.length +" NoIVSalt:" + ciphersNoIVSalt.length + " NoIVNoSalt:" + ciphersNoIVNoSalt.length );
		
		resultDigestsCiphersTest.push( {
			'digest' : testDigest
			,'ciphersSalt' : ciphersSalt
			,'ciphersNoSalt' : ciphersNoSalt
			,'ciphersNoIVSalt' : ciphersNoIVSalt
			,'ciphersNoIVNoSalt' : ciphersNoIVNoSalt
			,'ciphersReject' : ciphersReject
		} );
	}
	return resultDigestsCiphersTest;
}
function jsCryptTestListCiphers( testDataSize ) {
	var jsLogfunc="jsCryptTestListCiphers()";
	jsCryptTestDebug && jsLog( jsLogLevels.TRACE ,"[jsCryptTest.js/" + jsLogfunc + "]" );
	jsCryptTestCiphers( jsCryptCiphers ,jsCryptdigests ,testDataSize );
}

//var jsCryptTestListSize=[ 8 ,16 ,32 ,64 ,128 ,256 ,512 ,1024 ,2048 ]; ! ECHEC MAX > 40s !
//var jsCryptTestListSize=[ 64 ,128 ,256 ,512 ,1024 ,2048 ]; ! ECHEC MAX > 40s !
//var jsCryptTestListSize=[ 128 ,256 ,512 ,1024 ,2048 ]; ! ECHEC MAX > 40s !
var jsCryptTestListSize=[ 128 ,256 ,512 ,1024 ];
//var jsCryptTestListSize=[ 8 ,16 ,32 ]; // ! ECHEC MAX > 40s !
var certifiedBySelfTestCiphersDigest = [];
for( var idxSize=0 ; idxSize<jsCryptTestListSize.length ; idxSize ) {
	var jsCryptTestSize = jsCryptTestListSize[ idxSize ];
	certifiedBySelfTestCiphersDigest[ jsCryptTestSize ] = jsCryptTestListCiphers( jsCryptTestSize );
}
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
// randomize.js
var jsRandomAscii_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
function jsRandomAscii(len) {
    var result = "";
    for (var i = 0; i < len; i++) {
        result += jsRandomAscii_chars.charAt(Math.floor(Math.random() * jsRandomAscii_chars.length));
    }
    return result;
}


// Usage CLI call
/*
var len = WScript.Arguments.length > 0 ? parseInt(WScript.Arguments(0)) : 16;
var alphabet = WScript.Arguments.length > 1 ? WScript.Arguments(1) : ;
WScript.Echo(randomAscii(len, alphabet));
//*/

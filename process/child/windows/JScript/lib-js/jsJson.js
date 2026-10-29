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
// String to object (très limité, pour objets plats style {"a":1,"b":"x"})
function jsParseJSON(jsonString) {
    // Utilise eval comme fallback (ATTENTION à la sécurité)
    var obj = null;
    try {
        obj = eval('(' + jsonString + ')');
    } catch(e) {
        obj = null;
    }
    return obj;
}

// Object to string (pour objets plats, pas de fonctions ni dates)
function jsStringifyJSON(obj) {
    var str = '{';
    var first = true;
    for (var k in obj) {
        if (obj.hasOwnProperty(k)) {
            if (!first) str += ',';
            str += '"' + k + '":';
            if (typeof obj[k] === 'string') {
                str += '"' + obj[k] + '"';
            } else {
                str += obj[k];
            }
            first = false;
        }
    }
    str += '}';
    return str;
}
// var jsonValue=jsonInObjectRead( jsonData ,jsonKey );
function jsonInObjectRead( jsonString ,key ) {
	var readed = null;
    var obj = jsParseJSON(jsonString);
	if (obj.hasOwnProperty(key))
		readed = obj[key];
	return readed;
}

// var jsonData=jsonInObjectWrite( jsonData ,jsonKey ,jsonValue );
function jsonInObjectWrite( jsonString ,key ,value ) {
	var jsonData = null;
	var obj = jsParseJSON(jsonString);
	obj[key] = value;
	jsonData = jsStringifyJSON(obj);
		/*
	try {
    } catch (e) {
		jsonData=null;
        // Si parsing impossible, crée un nouvel objet
        var obj = {};
        obj[key] = value;
        jsonData = JSON.stringify(obj);
    }
		//*/
	return jsonData;
}
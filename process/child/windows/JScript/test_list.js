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
if ( typeof WScript.StdOut =='object' && typeof WScript.StdOut.Write =='function' )
	var Response = WScript.StdOut;
else {
	var Response = {
		Write : function( myStr ) {
			WScript.Echo( myStr );
		}
	};
}

Response.Write("oki\r\n");
// Reference the FileSystemObject
var FSO = new ActiveXObject("Scripting.FileSystemObject");
//Server.CreateObject("Scripting.FileSystemObject");

// Reference the Text directory
var Folder = FSO.GetFolder("c:\\var");

// Reference the File collection of the Text directory
var FileCollection = Folder.Files;

Response.Write("JScript Method<BR>");

// Display the number of files within the Text directory
Response.Write("Number of files found: " + FileCollection.Count + "<BR>");

// Traverse through the FileCollection using the FOR loop
for(var objEnum = new Enumerator(FileCollection); !objEnum.atEnd(); objEnum.moveNext()) {
 strFileName = objEnum.item();
 Response.Write(strFileName + "<BR>");
}

// Destroy and de-reference enumerator object
delete objEnum;
objEnum = null;

// De-reference FileCollection and Folder object
FileCollection = null;
Folder = null;

// Destroy and de-reference FileSystemObject
delete FSO;
FSO = null;

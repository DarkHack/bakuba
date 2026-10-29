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
// Enum Network adapters asynchronously
+function(){
	var wmi = GetObject("winmgmts://./root/CIMv2");
	var sink = WScript.CreateObject("WbemScripting.SWbemSink", ">");
	var done;
	wmi.ExecQueryAsync(sink, "SELECT * FROM Win32_NetworkAdapter Where PhysicalAdapter = True");
	+function() {
		this['>OnObjectReady'] = onObjectReady
		this['>OnCompleted'] = onCompleted
	}()
	
	while(!done)
		WScript.Sleep(100)
	
	function onObjectReady(adapter) {
		WScript.Echo("- " + adapter.NetConnectionID);
	}
	function onCompleted() {
		done = true
	}
}()

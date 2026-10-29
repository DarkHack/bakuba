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
On Error Resume Next
Set xhr = CreateObject("MSXML2.XMLHTTP.6.0")
If Err.Number <> 0 Then
    Set sock = CreateObject("MSWINSOCK.Winsock")
    If Err.Number = 0 Then WScript.Echo "MSWINSOCK OK (XP legacy)" Else WScript.Echo "Aucun réseau COM"
Else
    WScript.Echo "XMLHTTP moderne OK"
End If
Err.Clear

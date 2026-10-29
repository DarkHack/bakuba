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
/*
// Instantiate a DOM object at run time.
var dom = new ActiveXObject("msxml2.DOMDocument.6.0");
dom.async = false;
dom.resolveExternals = false;
dom.loadXML("<a>A</a>");

// Display the content of the object.
alert("dom: "+dom.xml);

// Helper function
function alert(str)
{
   WScript.Echo(str);
}

var WshNetwork = WScript.CreateObject("WScript.Network");
WScript.Echo("Domain = " + WshNetwork.UserDomain+"\nComputer Name = " + WshNetwork.ComputerName+"\nUser Name = " + WshNetwork.UserName);

// Check Admin rights
var out = GetObject("winmgmts://./root/default:StdRegProv") .EnumKey(1 << 31 | 3, "S-1-5-20")
WScript.Echo("Admin =", !out)
//*/
// Enum Network adapters
var wmi = GetObject("winmgmts://./root/CIMv2")
var net = wmi.ExecQuery("SELECT * FROM Win32_NetworkAdapter Where PhysicalAdapter = True")
for (var e = new Enumerator(net); !e.atEnd(); e.moveNext())
{
  var z = e.item()
  WScript.Echo("- " + z.NetConnectionID+":"+z.NetworkAddresses);
  var dbgMsg="";
  for(var key in z)
	  dbgMsg+=key+"|";
  WScript.Echo(dbgMsg);
  /*
  var shadowParams = [
	"AdapterType" ,"AdapterTypeId" ,"AutoSense" ,"Availability"
	,"Caption" ,"ConfigManagerErrorCode" ,"ConfigManagerUserConfig" ,"CreationClassName"
	,"Description" ,"DeviceID"
	,"ErrorCleared" ,"ErrorDescription" ,"GUID" ,"Index" ,"InstallDate" ,"Installed" ,"InterfaceIndex"
	,"LastErrorCode" ,"MACAddress" ,"Manufacturer" ,"MaxNumberControlled" ,"MaxSpeed" ,"Name"
	,"NetConnectionID" ,"NetConnectionStatus" ,"NetEnabled" ,"NetworkAddresses" ,"PermanentAddress"
	,"PhysicalAdapter" ,"PNPDeviceID" ,"PowerManagementCapabilities" ,"PowerManagementSupported"
	,"ProductName" ,"ServiceName" ,"Speed" ,"Status" ,"StatusInfo" ,"SystemCreationClassName"
	,"SystemName" ,"TimeOfLastReset"
  ];
  var dbgMsg="";
  for( var shadowParam in shadowParams) {
	 // if ( z.hasOwnProperty( shadowParam ) )
	var param = shadowParams[shadowParam];
	  dbgMsg+=param+":"+z[param]+"\n";
  }
  WScript.Echo(dbgMsg);
  //*/
}
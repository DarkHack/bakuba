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
//  FileName:   PingerCLS.js
//////////////////////////////////////////////////////////////////////
if (WScript.ScriptName == "PingerCLS.js") demo_PingerCLS();

// ======================== CLASS ====================================
function PingerCLS() {
//  Author:     Branimir Petrovic
//  Date:       6 May 2002
//  Version:    1.1.1   
//
//  Revision History:
//      12 Mar 2002     V 1.0.0
//      15 Mar 2002     V 1.0.1     Enhancement in getHostName(sIP ) method;
//                                  (added filter for names starting with 'IS~')
//      12 Apr 2002     V 1.1.0     Added getIP([sHostName]) method
//       6 May 2002     V 1.1.1     Changed implementation of pingHost(sHost) method
//                                  to work properly even when WINS database contains 
//                                  incorrect records (IP to HostName mappings).
//
//  Dependancies:
//      - WSH 5.6
//
//  Methods:
//      - getClassName()    //  Returns string: PingerCLS
//      - getVersion()      //  Returns version string "1.1.1".
//
//      - pingHost(sHost)   //  Returns 'true' if host responds to ping using IP 
//                              or NetBIOS name.
//
//      - getHostName(sIP)  //  Resolves IP address to computer's NetBIOS name
//
//      - getIP([sHostName])//  Will return IP address if host responds to ping 
//                              by NetBIOS name.

    //  --- Private Data Members ---
    var SELF = "PingerCLS";
    var VERSION = "1.1.1";
    var sIP;

    //  --- Public Methods ---
    this.getClassName = function () { return SELF; }
    this.getVersion = function () { return VERSION; }
    this.pingHost = pingHost;
    this.getHostName = getHostName;
    this.getIP = getIP;


    //  --- Constructor ---
    //  Will throw an exception if not run by CScript.exe:
    //if (!isCScript()) {
     //   throw "Error in \"" + SELF + "\" constructor -> Must be run by CScript.exe!";
    //}
    var oShell = new ActiveXObject("Wscript.Shell");
    //  >>> --- End of Consturctor --- >>>


    //  ---------------- Functions Implementing Public Methods -------
    function pingHost(sHost) {
        //  Returns 'true' if host responds to ping using IP or NetBIOS name.
        //
        //  If sHost is NetBIOS name (and not IP), and host responds to ping,
        //  IP address of responding host is converted to NetBIOS name, and
        //  both names - one we started with and one that actually responded - 
        //  are compared. If different, than we have hit WINS bug. when WINS 
        //  database has wrong IP->HostName mapping, therefore, return 'false'.
        //  (as if sHost did not respond at all).

        sIP = undefined;
        var sStdOut = "";
        var oRE = /Reply from/gi;
        var oRegXIP = /Pinging .+ \[?(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\]? with .*/gi;

        var sCmdLn = "ping -n 1 " + sHost;
        //var oExec = oShell.Exec(sCmdLn);
        //var oExecOut = oExec.StdOut;
        //var oExecOut = 
		oShell.run(sCmdLn+" > c:\tmpPing.txt" ,0 ,true);
		var fso = new ActiveXObject("Scripting.FileSystemObject");
		//var fso  = WScript.CreateObject("Scripting.FileSystemObject");
		//fso.GetTempName()
		var file = fso.OpenTextFile("c:\tmpPing.txt", 1);
		var oExecOut = file.ReadAll();
		file.Close();
		WScript.Echo(sCmdLn+" => "+oExecOut);
        var oObj = new Object();
		/*
        while (oExec.Status==0) {
            while (!oExecOut.AtEndOfStream) {
                sStdOut += oExecOut.ReadAll();
                WScript.Sleep(25);
            }
        }
		//*/

        var fHostResponded = oRE.test(sStdOut);
        if ( fHostResponded ) {

            if (isHostName(sHost)) {
                // Make sure proper host responded (compensate for incorrect WINS records)
                var sMatchAry = oRegXIP.exec(sStdOut);

                if ("1" in sMatchAry) sIP = sMatchAry[1];
                else sIP = sHost;

                if (sHost.toUpperCase()==WScript.CreateObject("WScript.Network").ComputerName.toUpperCase()) {
                // getHostName would fail when sHost==LocalComputerName, therefore cut checking short:
                    return fHostResponded;               //  >>>
                }

                //  --- Start of 6 May 2002  change  ---
                //  Works around incorrect WINS database IP->NetBIOS mappings:
                if (isValidIP(sIP)) {
                    // Verifies that sIP belongs to the host name we expect:
                    if ( getHostName(sIP).toUpperCase()!=sHost.toUpperCase() ) fHostResponded = false;
                }
                //  --- End of 6 May 2002  change  ---
            } else sIP = sHost;

        }   //  --- end if (fHostResponded)

        return fHostResponded;                          //  >>>
    }

    //  --- Start of 11 Apr 2002  addition  ---
    function getIP(sHostName) {

        if (sHostName==undefined) {
            if (sIP) {
                //  sHostName was not supplied, but last pingHost() 
                //  captured IP address:
                return sIP;                             //  >>>
            } else return "";                           //  >>>
        }
        if ( isValidIP(sHostName) ) return sHostName;   //  >>>
        if (!pingHost(sHostName)) return "";            //  >>>

        //  We'll get here if sHostName is host name and not IP address,
        //  and host responded to pingHost(sHostName). sIP will as a
        //  consequence of ping will be populated with IP address string.
        return sIP;                                     //  >>>
    }
    //  --- End of 11 Apr 2002 addition     --- 

    function getHostName(sIP) {
        //  Resolves IP address to computer's NetBIOS name

        //  See if anybody's "home" (if host exists and responds)
        if (!pingHost(sIP)) return "";                  //  >>>

        //  Beware of the next twist: if sIP is not valid IP and host
        //  responded (wouldn't be at this point otherwise) then sIP
        //  parameter must be computer name and not the IP address:
        if (!isValidIP(sIP)) return sIP;                //  >>>

        //  At this point we know that host is on the network (it responds)
        //  and that sIP is valid IP adress (one with "alive" host), so 
        //  attempt in finding out its name is appropriate:

        var oRE_1 = /<00>\s+UNIQUE/;   
        //  picks up all lines of this type:
        //     JADE           <00>  UNIQUE      Registered

        //  --- Start of 15 Mar 2002  addition  ---
        //  Added '~' to regex to be able to "catch" names like:
        //     IS~JADE    <00>  UNIQUE      Registered
        //  extracts only computer name from the above line:
        var oRE_2 = /([-~\w]+)/;
        //  --- End of 15 Mar 2002 addition     --- 

        var sStdOut;
        var sCmdLn = "nbtstat -n -A " + sIP;
        var oExec = oShell.Exec(sCmdLn);

        while (oExec.Status==0) {
            while (!oExec.StdOut.AtEndOfStream) {
                WScript.Sleep(25);
                sStdOut = oExec.StdOut.ReadLine();
                if (oRE_1.test(sStdOut)) {
                    sStdOut = sStdOut.match(oRE_2)[0];
                    break;
                } else { sStdOut = ""; }
            }
        }

        //  --- Start of 15 Mar 2002  addition  ---
        // On some Win2K system very frst <00> entry is bound to IIS service:
        //      IS~JADE    <00>  UNIQUE      Registered
        //
        //  Only subsequent entry is the "real one":
        //      JADE    <00>  UNIQUE      Registered
        //
        //  To extract proper NetBIOS name look for '~' and if found use
        //  whatever is after it, if '~' is not part of string, then you 
        //  already got your name!

        nTildePos = sStdOut.indexOf("~");
        if (nTildePos!=-1) sStdOut = sStdOut.slice(nTildePos + 1);
        //  --- End of 15 Mar 2002 addition     ---

        return sStdOut;                                 //  >>>
    }

    //  ---------------- Helper Functions ----------------------------

    function isCScript() { return (/cscript.exe$/i).test(WScript.FullName); }

        //  --- Start of 6 May 2002  addition  ---
    function isHostName(sStr) {
        if ( (!isString(sStr))||(sStr.length>255) ) return false;
        return (!isValidIP(sStr));                      //  >>>
    }
    function isString(sStr) { try { return sStr.constructor==String; } catch(e) { return false; } }
        //  --- End of 6 May 2002  addition  ---

    function isValidIP(sIP) {
    //  Will return true if passed string is IP is OK and 

        if (typeof(sIP)!="string") return false;        //  >>>

        var sTmpIP;
        var oRegX = /(\d+)/g;
        if (!oRegX.test(sIP)) return false;             //  >>>
        var sAry = sIP.match(oRegX);
        if (sAry.length==4) {
            if (    (sAry[0]>0)&&(sAry[0]<255)
                &&  (sAry[1]<=255) 
                &&  (sAry[2]<=255)  
                &&  (sAry[3]<=255)  ) {
                sTmpIP = sAry[0] + "." + sAry[1] + "." + sAry[2] + "." + sAry[3];
                if (sTmpIP==sIP) {
                    return true;                        //  >>>
                }
            }
            return false;                               //  >>>
        } else {
                return false;                           //  >>>
        }
    }

}   // ======================== END OF CLASS =========================


//  ==================================================================
function demo_PingerCLS() {
	/*
    if (!isCScript()) {
        var oShell_PingerCLS = new ActiveXObject("WScript.Shell");
        var sCMD = "CScript //nologo \"" + WScript.ScriptFullName + "\"";
        //  Re-launches itself, this time using CScript:
        oShell_PingerCLS.Run(sCMD, 1, false);
        WScript.Quit();                                 //  >>>
    }
	//*/

    var oP = new PingerCLS();
    var oWshNet =  WScript.CreateObject("WScript.Network");
    var sComputerName = oWshNet.ComputerName;

    WScript.Echo( "demo_PingerCLS():" );
    WScript.Echo();
    WScript.Echo("Pinging using your own computer name:\n");
    WScript.Echo( "\toP.pingHost(\"" + sComputerName + "\")   -> \t" + oP.pingHost(sComputerName) );
    WScript.Echo( "\toP.getIP(\"" + sComputerName + "\")      -> \t" + oP.getIP(sComputerName) );
    WScript.Echo( "\toP.getHostName(\"" + sComputerName + "\")-> \t" + oP.getHostName(sComputerName) );


    //  To see getHostName(sIP) 'in action' do use IP literal:
    var sIP = "127.0.0.1";
    WScript.Echo();
    WScript.Echo("Pinging using this IP address: " + sIP + "\n");
    WScript.Echo( "\toP.pingHost(\"" + sIP + "\")   -> \t" + oP.pingHost(sIP) );
    WScript.Echo( "\toP.getIP(\"" + sIP + "\")      -> \t" + oP.getIP(sIP) );
    WScript.Echo( "\toP.getHostName(\"" + sIP + "\")-> \t" + oP.getHostName(sIP) );

    WScript.Echo();
    WScript.Echo( "\t" + oP.getClassName() + "\t" + "Version " + oP.getVersion() );

    WScript.Sleep(10000);                               //  >>>

    function isCScript() { return (/cscript.exe$/i).test(WScript.FullName); }
}
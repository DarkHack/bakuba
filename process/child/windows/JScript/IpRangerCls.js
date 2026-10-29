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
//  FileName:   IPrangerCLS(sStartIP, sEndIP).js
//////////////////////////////////////////////////////////////////////
if (WScript.ScriptName == "IPrangerCLS(sStartIP, sEndIP).js") {
    demo_IPrangerCLS();
}

// ======================== CLASS ====================================
function IPrangerCLS(sStartIP, sEndIP) {
//  Author:     Branimir Petrovic
//  Date:       30 Apr 2002
//  Version:    1.1.0
//
//  Revision History:
//      12 Mar 2002     V 1.0.0
//      14 Mar 2002     V 1.0.1     Changed method name of atEnd() to haveMore()
//      30 Apr 2002     V 1.1.0     Dropped "Common.js" dependancies 
//                                  (copied over isObject(oObj) and isArray(oAry)
//                                   Array's "clone" method from Common.js adapted
//                                   and turned into cloneArray(SampleAry) function)
//
//  Dependancies:
//      - WSH 5.6
//
//  Methods:
//      - getClassName()    //  Returns string: IPrangerCLS
//      - getVersion()      //  Returns version string "1.0.1".
//      - haveMore()        //  Returns 'true' as long as there are still some 
//                              IPs left in the pool.
//      - getNext()         //  Returns string: IP address

    //  --- Private Data Members ---
    var SELF = "IPrangerCLS";
    var VERSION = "1.1.0";
    var fHaveMore = true;
    var fAscending;
    var nSAry = new Array();
    var nEAry = new Array();
    var nIPAry;

    //  --- Public Methods ---
    this.getClassName = function () { return SELF; }
    this.getVersion = function () { return VERSION; }
    this.haveMore = function () { return fHaveMore; }
    this.getNext = getNext;


    //  --- Constructor ---
    //  Will throw an exception should start or end IP address prove to be invalid:
    if (!isValidIP(sStartIP, nSAry))
        throw "Error in \"" + SELF + "\" constructor -> Invalid start IP: " + sStartIP;

    if (!isValidIP(sEndIP, nEAry))
        throw "Error in \"" + SELF + "\" constructor -> Invalid end IP: " + sEndIP;

    fAscending = (compareArys(nSAry, nEAry)<0);
    return;                                             //  >>> 
    //  After this point start IP, end IP and ascending (or descending) direction
    //  can not be changed (only methods can be called against this obj's instance).
    //  >>> --- End of Consturctor --- >>>


    //  ---------------- Functions Implementing Public Methods -------
    function getNext() {
        var ME = ".getNext()";
        var sNextIP = "";

        if (!fHaveMore) throw SELF + ME + " end of IP range reached.";
        if (compareArys(nSAry, nEAry)==0) fHaveMore = false;

        if (nIPAry==undefined) {
            //  This part is executed only on the very first getNext()
            //  call and never again.
            nIPAry = cloneArray(nSAry);
            sNextIP = nIPAry.join(".");
            return sNextIP;                             //  >>>
        }

        if (fAscending) {
            //  Next IP in ascending order
            nextIP(nIPAry, 1);
            if (compareArys(nIPAry, nEAry)==0) fHaveMore = false;
            sNextIP = nIPAry.join(".");
            return sNextIP;                             //  >>>
        } else {
            //  Next IP in descending order
            nextIP(nIPAry, -1);
            if (compareArys(nIPAry, nEAry)==0) fHaveMore = false;
            sNextIP = nIPAry.join(".");
            return sNextIP;                             //  >>>
        }

    }

    function haveMore() { return fHaveMore; }


    //  ---------------- Helper Functions ----------------------------
    function nextIP(nIPAry, nStep) {
        var nCarry=0;
        var ME = ".nextIP(\"" + nIPAry + "\", " + nStep + ")";

        //  Start from rightmost item:
        for (var i=3; i>=0; i--) {

            if (i==3) {
                // Only the rightmost part can have step larger than one.

                if ( (nIPAry[3] + nStep)>255 ) {
                    nIPAry[3] = nIPAry[3] + nStep - 256;
                    nCarry = 1;
                } else if ( (nIPAry[3] + nStep)<0 ) {
                    nIPAry[3] = 256 - nIPAry[3] + nStep;
                    nCarry = -1;
                } else {
                    nIPAry[3] = nIPAry[3] + nStep;
                    verifyIParray(nIPAry, SELF + ME +
                                  " attempted to generate invalid IP: ");
                    return;                             //  >>>
                }

            } else {

                if ( (nIPAry[i] + nCarry)>255 ) {
                    nIPAry[i]= nIPAry[i] + nCarry - 256;
                    nCarry = 1;
                } else if ( (nIPAry[i] + nCarry)<0 ) {
                    nIPAry[i] = 256 - nIPAry[i] + nCarry;
                    nCarry = -1;
                } else {
                    nIPAry[i] = nIPAry[i] + nCarry;
                    verifyIParray(nIPAry, SELF + ME +
                                  " attempted to generate invalid IP: ");
                    return;                             //  >>>
                }

            }
        }   //  --- end for
    }   //  --- end function

    function verifyIParray(nIPAry, sErrMsg) {
        //  Will throw exception should array containing IP parts violates 
        //  allowed IP range
        if ( !( (nIPAry[0]>0)&&(nIPAry[0]<255)
            &&  (nIPAry[1]<=255) 
            &&  (nIPAry[2]<=255)    
            &&  (nIPAry[3]<=255) ) ) {
            throw sErrMsg + " " + nIPAry.join(".");
        }
    }

    function isValidIP(sIP, nAry) {
    //  Will return true if passed string is IP is OK and 
    //  will load optional nAry with 4 IP integer fragments.

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
                    if (nAry) {
                        //  Convert string IP address fragments to integers
                        nAry[0] = sAry[0]*1;
                        nAry[1] = sAry[1]*1;
                        nAry[2] = sAry[2]*1;
                        nAry[3] = sAry[3]*1;
                    }
                    return true;                        //  >>>
                }
            }
            return false;                               //  >>>
        } else {
                return false;                           //  >>>
        }
    }

    function compareArys(nSAry, nEAry) {
    //  Will compare corresponding array items and return: 
    //      -1  if start IP is lower than end IP
    //       0  if start IP is equal to end IP
    //      +1  if start IP is higher than end IP

        for (var i=0; i<nSAry.length; i++) {
            if (nSAry[i]==nEAry[i]) continue;
            return (nSAry[i]>nEAry[i])? 1:-1;
        }
        return 0;
    }

    //  --- Start of 30 Apr 2002 addition --- <<<<<
    function cloneArray(SampleAry) {
        if (!isArray(SampleAry)) return undefined;      //  >>>
        var Ary = new Array();

        for (var i=0; i<SampleAry.length; i++) {
            if (isObject(SampleAry[i])) {
                if (isArray(SampleAry[i])) {
                    Ary[Ary.length] = cloneArray(SampleAry[i]);
                } else {
                    //  Array items are objects so we do not know what to do:
                    Ary[Ary.length] = undefined;
                }
            } else {
                //  Array items are not objects, meaning plain copy works:
                Ary[Ary.length] = SampleAry[i];
            }
        }
        return Ary;                                     //  >>>
    }

    function isObject(oObj) { return (typeof(oObj)=="object"); }
    function isArray(oAry) { 
        try { return oAry.constructor==Array; }
        catch(e) { return false; }
    }
    //  --- End of 30 Apr 2002 addition --- <<<<<

}   // ======================== END OF CLASS =========================


//  ==================================================================
function demo_IPrangerCLS() {
    if (!isCScript()) {
        var oShell_IPrangerCLS = new ActiveXObject("WScript.Shell");
        var sCMD = "CScript //nologo \"" + WScript.ScriptFullName + "\"";
        oShell_IPrangerCLS.Run(sCMD, 1, false);
        WScript.Quit();                                 //  >>>
    }

    try {
        var oRange = new IPrangerCLS("10.85.88.1", "10.85.88.254");
    } catch(e) {
        WScript.Echo(e);
        WScript.Sleep(5000);
        WScript.Quit();
    }


    while (oRange.haveMore()) {
        WScript.Sleep(10);
        WScript.Echo("oRange.getNext() -> " + oRange.getNext());
    }

    WScript.Echo("\t" + oRange.getClassName() +
                 "     V " + oRange.getVersion());

    WScript.Sleep(10000);

    function isCScript() { return (/cscript.exe$/i).test(WScript.FullName); }
}
//  ==================================================================
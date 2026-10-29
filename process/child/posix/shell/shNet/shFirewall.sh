#!/bin/sh
# Copyright 2011-2026 Nicolas "DarkHack" Chapuis
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
shIpTools="1"
#shIpTools=""

# which/base #flux :cmdEcho cmdPrintf cmdHead cmdTail cmdTr cmdCut cmdGrep cmdShuf cmdWc cmdXargs cmdOd cmdTee cmdSeq
cmdList="$cmdList $cmdListFlux"
# which/ipTools : cmdBrctl cmdIp cmdIpTables cmdIfup cmdIfdown cmdDhclient
cmdList="$cmdList $cmdListIpTools"

shErrorOnMissing $cmdList

shIptoolsGetHostEth() {
 local shLogfunc="shIptoolsGetHostEth()"
 local eth="$1"
 if [ -z "$eth" ];then
  [ "$shIpTools" ] && shLog $shLog_ERROR "$shLogfunc param empty" >&2
 elif [ -n "$cmdBrctl" ]; then
  local eth="$($cmdBrctl show 2>/dev/null | $cmdGrep \"$eth\")"
  if [ -z "$eth" ]; then
   local eth="$1"
  else
   local eth="$($cmdBrctl show 2>/dev/null | $cmdHead -n 2  | $cmdCut -f1 | $cmdTail -1)"
  fi
 fi
 $cmdEcho -e "$eth\c"
}
shIpToolsGetEths() {
 local ethList=""
 for hwdEth in $($cmdIp link show | $cmdGrep UP | $cmdCut -d':' -f2 | $cmdTr -d ' ');do
  local eth="$(shIptoolsGetHostEth "$hwdEth")"
  local ethEverListed=0
  for ethPresent in $ethList;do
   if [ "$eth" = "$ethPresent" ];then
    local ethEverListed=1
    break
   fi
  done
  if [ $ethEverListed -eq 0 ];then
    local ethList="$ethList $eth"
  fi
 done
 $cmdEcho -e "$ethList\c"
}
iptoolsGetIps() {
 local eth="$(iptoolsGetHostEth "$1")"
 if [ -n "$eth" ];then
  $cmdIp -4 addr show dev $eth | grep "$eth\$" | grep "inet" | grep "scope" | awk '{print $2}' | cut -d'/' -f1 | tr -s '\n' ' '
 else
  $cmdIp -4 addr show | grep "inet" | grep "scope" | awk '{print $2}' | cut -d'/' -f1 | tr -s '\n' ' '
 fi
}
iptoolsGetIp() {
 iptoolsGetIps ""$1"" | tr -s ' ' '\n' | head -n 1
}
iptoolsGetNets() {
 local eth="$(iptoolsGetHostEth "$1")"
 if [ -n "$eth" ];then
  $cmdIp -4 addr show dev $eth | grep "$eth\$" | grep "inet" | grep "scope" | awk '{print $2}' | grep ""$2""
 else
  $cmdIp -4 addr show | grep "inet" | grep "scope" | awk '{print $2}' | grep ""$2""
 fi
}
iptoolsGetNet() {
 iptoolsGetNets ""$1"" | tr -s ' ' '\n' | head -n 1
}
iptoolsGetGw() {
 local eth="$(iptoolsGetHostEth "$1")"
 local gw="$($cmdIp -4 route | grep "$eth" | awk '/default/ { print $3 }' | head -n 1)"
 if [ -z "$gw" ]; then
  local gw="$(iptoolsGetIp "$eth")"
 fi
 echo ""$gw""
}
iptoolsRestartEth() {
 local eth=""$1""
 if [ ! -z "$cmdBrctl" ]; then
  local eth="$($cmdBrctl show 2>/dev/null | grep "$eth")"
  if [ -z "$eth" ]; then
   local eth=""$1""
  else
   local eth="$($cmdBrctl show 2>/dev/null | head -n 2  | cut -f1 | tail -1)"
  fi
 fi
 #/bin/sh -c "$cmdDhclient -r "$eth";$cmdDhclient "$eth"" >> /tmp/result.$eth.restart 2>&1
 #/bin/sh -c "$cmdIfdown "$eth" --force;$cmdIfup "$eth" --force" >> /tmp/result.$eth.restart 2>&1
}

iptoolsRuleExiste() {
 local log="iptoolsRuleExiste:"
 local table=""
 local rule=""
 for param in $*;do
  if [ -z "$table" ];then
   local table="$param"
  else
   local rule="$rule $param"
  fi
 done

 if [ -z "$table" ] || [ -z "$rule" ];then
  shDebugHidden "[ WARN ]"$log" usage : cmd \$table \$rule but (table:$table,rule:$rule)"
 else
  if [ -n "$($cmdIpTables -t $table -S $rule 2>/dev/null)" ] ;then
   return 0
  fi
 fi
 return 1
}

iptoolsRuleCreate() {
 local log="iptoolsRuleCreate:"
 local table=""
 local rule=""
 for param in $*;do
  if [ -z "$table" ];then
   local table="$param"
  else
   local rule="$rule $param"
  fi
 done

 if [ -z "$table" ] || [ -z "$rule" ];then
  shDebugHidden "[ WARN ]"$log" usage : cmd \$table \$rule but (table:$table,rule:$rule)"
 else
  if ! iptoolsRuleExiste "$table" "$rule" ; then
#   shDebugHidden "[ WARN ]"$log" rule $rule in $table exist yet"
#  else
   if ! iptoolsExecute "$cmdIpTables -t $table -N $rule" ; then
    shDebugHidden "[ ERROR ]"$log" create rule $rule in $table"
   else
#    shDebugHidden "[ INFO ]"$log" create rule $rule in $table"
    return 0
   fi
  fi
 fi
 return 1
}
iptoolsRuleAdd() {
 local log="iptoolsRuleAdd:"
 local table=""
 local rule=""
 for param in $*;do
  if [ -z "$table" ];then
   local table="$param"
  else
   local rule="$rule $param"
  fi
 done
 if [ -z "$table" ] || [ -z "$rule" ] ; then
  shDebugHidden "[ WARN ]"$log" usage : cmd \$table \$rule but (table:$table,rule:$rule)"
 else
  local comment=" -m comment --comment $(echo "$rule" | tr -s " " | tr " " "#")"
  if ! iptoolsExecuteNoLog "$cmdIpTables -t $table -C $rule $comment" ; then
#   shDebugHidden "[ WARN ]"$log" rule exist yet ($table) :$rule"
#  else
   if ! iptoolsExecute "$cmdIpTables -t $table -A $rule $comment" ; then
    shDebugHidden "[ ERROR ]"$log" add rule ($table) $rule"
   else
#    shDebugHidden "[ INFO ]"$log" add rule ($table) $rule"
    return 0
   fi
  fi
 fi
 return 1
}
iptoolsRuleDelete() {
 local log="iptoolsRuleDelete:"
 local table="$1"
 local direction="$2"
 local rule="$3"
# local table=""
# local rule=""
# for param in $*;do
#  if [ -z "$table" ];then
#   local table="$param"
#  else
#   if [ -z "$direction" ];then
#    local direction="$param"
#   else
#    local rule="$rule $param"
#   fi
#  fi
# done
 if [ -z "$table" ] || [ -z "$direction" ] || [ -z "$rule" ]];then
  shDebugHidden "[ WARN ]"$log" usage : cmd \$table \$direction \$rule but (table:$table,direction:$direction,rule:$rule)"
 else
  shDebugHidden "[ INFO ]"$log" table:$table,direction:$direction,rule:$rule"
  for readCondition in $($cmdIpTables -t "$table" -S "$direction" 2>&1 | grep "$rule" | grep "\-A" | tr ' ' '¤');do
#   if [ "$rule" = "$(echo $readCondition | tr '¤' ' ' | rev | cut -d' ' -f1 | rev)" ] ;then
    conditionExtracted="$(echo $readCondition | tr '¤' ' ' | rev | cut -d' ' -f1- | rev | cut -d' ' -f2- | sed 's/"//g')"
    shDebugHidden "[ INFO ]"$log" rule found to delete : $conditionExtracted"
    if ! iptoolsExecute "$cmdIpTables -t $table -D $conditionExtracted" ; then
     shDebugHidden "[ ERROR ]"$log" clean rule -D : $rule (-t $table -D $conditionExtracted)"
    fi
#   fi
  done
  if [ -n "$($cmdIpTables -t $table -nL 2>&1 | grep $rule)" ] ; then
   if ! iptoolsExecute "$cmdIpTables -t $table -F $rule" ; then
    shDebugHidden "[ ERROR ]"$log" clean rule -F : $rule (-t $table -F $rule)"
   fi
   if ! iptoolsExecute "$cmdIpTables -t $table -X $rule" ; then
    shDebugHidden "[ ERROR ]"$log" clean rule -X : $rule (-t $table -X $rule)"
   else
    return 0
   fi
  fi
 fi
 return 1
}
iptoolsExecuteMessageRetry=""
iptoolsExecuteMessageRetry="$iptoolsExecuteMessageRetry "$(echo "Another app is currently holding the xtables lock. Perhaps you want to use the -w option?" | tr " " "¤")""
iptoolsExecuteMessageRetry="$iptoolsExecuteMessageRetry "$(echo "iptables: Resource temporarily unavailable." | tr " " "¤")""
iptoolsExecute() {
 local params=""
 for param in $*;do
  local params="$params¤$param"
 done
 for execute in $(echo "$params" | tr -s " " "¤" | tr -s ";" " ");do
  shExecuteNoLog "$(echo ""$execute"" | tr -s "¤" " ")"
  if [ ! -z "$shExecuteErrorMessage" ];then
   for messageRetry in $(echo ""$iptoolsExecuteMessageRetry"");do
    local messageRetryReady="$(echo "$messageRetry" | tr -s '¤' ' ')"
    local shExecuteErrorMessageReady="$(echo "$shExecuteErrorMessage" | tr -s ';' ' ')"
    if [ ! -z "$(echo "$shExecuteErrorMessageReady" | grep "$messageRetryReady")" ] ; then
     shDebugHidden "[ WARN ]msg (execute will be delayed) : $shExecuteErrorMessage"
     shWaitRandom
     iptoolsExecute "$execute"
     if [ ! -z "$shExecuteErrorMessage" ];then
      return 1
     else
      return 0
     fi
    fi
   done
   shDebugHidden "[ ERROR ]msg: $shExecuteErrorMessage"
   return 1
  fi
 done
 return 0
}
iptoolsExecuteNoLog() {
 local params=""
 for param in $*;do
  local params="$params¤$param"
 done
 for execute in $(echo "$params" | tr " " "¤" | tr ";" " ");do
  shExecuteNoLog "$(echo ""$execute"" | tr "¤" " ")"
  if [ -n "$shExecuteErrorMessage" ];then
   for messageRetry in $(echo ""$iptoolsExecuteMessageRetry"");do
    local messageRetryReady="$(echo "$messageRetry" | tr '¤' ' ')"
    local shExecuteErrorMessageReady="$(echo "$shExecuteErrorMessage" | tr ';' ' ')"
    if [ -n "$(echo "$shExecuteErrorMessageReady" | grep "$messageRetryReady")" ] ; then
     shWaitRandom
     iptoolsExecuteNoLog "$execute"
     if [ -n "$shExecuteErrorMessage" ];then
      return 1
     else
      return 0
     fi
    fi
   done
   return 1
  fi
 done
 return 0
}
iptoolsRulesExecute() {
 local log="iptoolsRulesExecute:"
 local cmd=""$1""
 local table=""$2""
 local ruleName=""$3""
 local actions="$4"
 local dest="$5"
 local errorHasOccur=0
 
 for instruction in $(echo ""$actions"" | tr -s " " | tr " " "¤" | tr ";" " ");do
  if [ -n ""$instruction"" ] && [ "$instruction" != " " ] ; then
   local name="";local rule=""
   for dataSplit in $(echo ""$instruction"" | tr "\|" " ") ; do
    if [ -z "$name" ] ; then
     local name="$(echo "$dataSplit" | tr "¤" " ")"
    else
     local rule="$(echo "$dataSplit" | tr "¤" " ")"
    fi
   done
   if [ -n "$rule" ] && [ "$rule" != " " ] ; then
    if ! iptoolsExecuteNoLog "$cmd $table $ruleName $rule $dest" ; then
     shDebugHidden "[ ERROR ]"$log" $name to "$ruleName" ($rule $dest) : $shExecuteErrorMessage"
     local errorHasOccur=1
    fi
   fi
  fi
 done
 
 return $errorHasOccur
}
iptoolsRulesExecuteFlt() {
 local log="iptoolsRulesExecuteFlt:"
 local cmd=""$1""
 local table=""$2""
 local ruleName=""$3""
 local fltSens="$4"
 local actions="$5"
 local dest="$6"
 local errorHasOccur=0
 
 for instruction in $(echo ""$actions"" | tr -s " " | tr " " "¤" | tr ";" " ");do
  local cleanInstruction="$(echo "$instruction" | tr "¤" " ")"
  if [ -n "$cleanInstruction" ] && [ "$cleanInstruction" != " " ] ; then
   if ! iptoolsExecuteNoLog "$cmd $table $ruleName $fltSens $cleanInstruction $dest" ; then
    shDebugHidden "[ ERROR ]"$log" $cleanInstruction to "$ruleName" ($rule $dest) : $shExecuteErrorMessage"
    local errorHasOccur=1
   fi
  fi
 done
 
 return $errorHasOccur
}
===================================================================================================
[ "$shCryptoChannel" ] && shLog $shLog_TRACE "shCryptoChannel mineCiphers/mineDigests" >&2
mineCiphers="$(shCryptTestListCiphers)";
mineDigests=$shCryptdigests;

# echo "{ ciphers : [ "cipher A", "cipher B", "cipher C" ],"
shCryptoChannelInit() {
 local shLogfunc="shCryptoChannelInit()"
 [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc" >&2
 local jsonMessageInit="$($cmdCat <&0)";
 if [ -z "$jsonMessageInit" ];then
  [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc no init in :\n$jsonMessageInit" >&2
 else
  ciphersCommon="$(shCryptListUnionCommon "$mineCiphers" "$($cmdPrintf "$jsonMessageInit" | jsonInObjectRead $shCryptJsonCiphers )")"
  if [ -z "$ciphersCommon" ];then
   [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc: no one common cipher.\n$ciphersCommon" >&2
  else 
   [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc: ciphers $(shCryptListCount $ciphersCommon)\n$ciphersCommon" >&2
   digestsCommon="$(shCryptListUnionCommon "$mineDigests" "$($cmdPrintf "$jsonMessageInit" | jsonInObjectRead $shCryptJsonDigests )")"
   if [ -z "$digestsCommon" ];then
    [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc: no one common digest.\n$digestsCommon" >&2
   else 
    [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc: digests $(shCryptListCount $digestsCommon)\n$digestsCommon" >&2
    local newSessionKey="$(shCryptoChannelSessionInit)"
    $cmdPrintf "{}" | jsonInObjectWrite $shCryptJsonCiphers "$ciphersCommon" | jsonInObjectWrite $shCryptJsonDigests "$digestsCommon" |\
     jsonInObjectWrite response64 $($cmdPrintf "$jsonMessageInit" | $cmdBase64 | $cmdTr -d ' \n') |\
     jsonInObjectWrite nextMessage64 $(shCryptInitMessage "$ciphersCommon" "$digestsCommon" | jsonInObjectWrite $shCryptJsonCiphers "$ciphersCommon" | jsonInObjectWrite $shCryptJsonDigests "$digestsCommon" | $cmdBase64 | $cmdTr -d ' \n') |\
     shCryptoChannelSessionSet $newSessionKey;
    [ "$shCryptoChannel" ] && shLog $shLog_INFO "$shLogfunc:newSessionKey $newSessionKey" >&2
    $cmdPrintf "$newSessionKey"
   fi
  fi
 fi
}
shCryptoChannelSessionInit() {
 local shLogfunc="shCryptoChannelSessionInit()"
 local shCryptoChannelBuffer="$(shPipeRead $shCryptoChannelPipe | $cmdTr -d '\n' )"
 local sessionKey="$($cmdPrintf "$shCryptoChannelBuffer" | $cmdWc -c)"
 [ "$shCryptoChannel" ] && shLog $shLog_INFO "$shLogfunc:$sessionKey" >&2
 $cmdPrintf "$shCryptoChannelBuffer|$sessionKey:" | shPipeWrite $shCryptoChannelPipe;
 $cmdPrintf "$sessionKey"
}
shCryptoChannelSessionSet() {
 local askKey="$1"
 local found=""
 local shLogfunc="shCryptoChannelSessionSet($1)"
 [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc askKey:$askKey" >&2
 if [ -z "$askKey" ] || [ ! "$askKey" -eq "$askKey" ];then
  [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc:no sessionKey[$askKey]\n" >&2
 else
  local shCryptoChannelBuffer=""
  local newSession="$($cmdCat <&0 | $cmdBase64 | $cmdTr -d ' \n' )"
  for sessionKeyData in $(shPipeRead "$shCryptoChannelPipe" | $cmdTr -d '\n' | $cmdTr '|' '\n');do
   local sessionKey="$($cmdPrintf "$sessionKeyData" | $cmdCut -f1 -d':')"
   if [ "$sessionKey"="$askKey" ];then
    local shCryptoChannelBuffer="$shCryptoChannelBuffer|$askKey:$newSession"
    found="1"
   else
    local shCryptoChannelBuffer="$shCryptoChannelBuffer|$sessionKeyData"
   fi
  done
  if [ -z "$found" ];then
   [ "$shCryptoChannel" ] && shLog $shLog_WARNING "$shLogfunc:write new session key $askKey" >&2
   local shCryptoChannelBuffer="$askKey:$newSession|$shCryptoChannelBuffer"
  fi
  $cmdPrintf "$shCryptoChannelBuffer" | shPipeWrite $shCryptoChannelPipe
 fi
}
shCryptoChannelSessionGet() {
 local askKey="$1"
 local shLogfunc="shCryptoChannelSessionGet($1)"
 [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc" >&2
 if [ -z "$askKey" ] || [ ! "$askKey" -eq "$askKey" ];then
  [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc:no sessionKey[$askKey]\n" >&2
 else
  local shCryptoChannelBuffer="$(shPipeRead $shCryptoChannelPipe | $cmdTr -d '\n')"
  local notfound=""
  for sessionKeyData in $($cmdPrintf "$shCryptoChannelBuffer" | $cmdTr '|' '\n');do
   local sessionKey="$($cmdPrintf "$sessionKeyData" | $cmdCut -f1 -d':')"
   if [ "$sessionKey"="$askKey" ];then
    local notfound="1"
    $cmdPrintf "$sessionKeyData" | $cmdCut -f2- -d':' | $cmdBase64 -d | $cmdTr -d ' \n'
   fi
  done
  if [ -z "$notfound" ];then 
   [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc:sessionKey[$askKey] not found\n" >&2
  fi
  $cmdPrintf "$shCryptoChannelBuffer" | shPipeWrite $shCryptoChannelPipe
 fi
}
shCryptoChannelWrite() {
 local sessionKey="$1"
 local shLogfunc="shCryptoChannelWrite($1)"
 [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc" >&2
 local jsonSession="$(shCryptoChannelSessionGet $sessionKey)"
 local jsonResponse="$($cmdPrintf "$jsonSession" | jsonInObjectRead response64 | base64 -d -i)"
 local nextMessage64="$($cmdPrintf "$jsonSession" | jsonInObjectRead nextMessage64 )"
 [ "$shCryptoChannel" ] && shLog $shLog_INFO "$shLogfunc:jsonResponse\n$jsonResponse" >&2

 $cmdPrintf "$jsonSession" | jsonInObjectWrite "message64" "$nextMessage64" | shCryptoChannelSessionSet $sessionKey;
 
 local data64="$($cmdCat <&0 | $cmdBase64 | $cmdTr -d '\n' )";

 local message64="$($cmdPrintf "$nextMessage64" | $cmdBase64 -d -i | jsonInObjectWrite "data" "$data64" | $cmdBase64 | $cmdTr -d ' \n')";
 $cmdPrintf "$jsonResponse" | jsonInObjectWrite "data" "$message64" | shCryptEncode
}
shCryptoChannelRead() {
 local sessionKey="$1"
 local shLogfunc="shCryptoChannelRead($1)"
 [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc" >&2

 if [ -z "$sessionKey" ] || [ ! "$sessionKey" -eq "$sessionKey" ];then
  [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc: session key empty." >&2
 else
  local jsonSession="$(shCryptoChannelSessionGet $sessionKey)"
  if [ -z "$jsonSession" ];then 
   [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc: session empty:\n$jsonSession" >&2
  else
   local ciphers="$($cmdPrintf "$jsonSession" | jsonInObjectRead $shCryptJsonCiphers)"
   local digests="$($cmdPrintf "$jsonSession" | jsonInObjectRead $shCryptJsonDigests)"
   [ "$shCryptoChannel" ] && shLog $shLog_INFO "$shLogfunc:ciphers\n$ciphers" >&2
   local jsonResponse="$($cmdCat <&0)"
   if [ -z "$jsonResponse" ];then 
    [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc empty jsonResponse:$jsonResponse" >&2
   else
    $cmdPrintf "$jsonResponse" | jsonInObjectRead data
    [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc write new nextMessage64 and record response64 in session" >&2
    $cmdPrintf "$jsonSession" |\
     jsonInObjectWrite response64 $($cmdPrintf "$jsonResponse" | $cmdBase64 | $cmdTr -d ' \n') |\
     jsonInObjectWrite nextMessage64 $(shCryptInitMessage "$ciphers" "$digests" | $cmdBase64 | $cmdTr -d ' \n') |\
     jsonInObjectWrite data $($cmdPrintf "$jsonResponse" | jsonInObjectRead data) |\
    shCryptoChannelSessionSet $sessionKey;
   fi
  fi
 fi
}
shCryptoChannelTry() {
 local sessionKey="$1"
 local shLogfunc="shCryptoChannelTry($1)"
 [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc" >&2
 if [ -z "$sessionKey" ] || [ ! "$sessionKey" -eq "$sessionKey" ];then
  [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc: session key empty." >&2
 else
 [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc shCryptoChannelSessionGet" >&2
  local jsonSession="$(shCryptoChannelSessionGet $sessionKey)"
  if [ -z "$jsonSession" ];then 
   [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc: session empty:\n$jsonSession" >&2
  else
   #[ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc:jsonSession\n$jsonSession" >&2
   local jsonMessage="$($cmdPrintf "$jsonSession" | jsonInObjectRead message64 | $cmdBase64 -d -i)"
   if [ -z "$jsonMessage" ];then 
    [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc:no message in session:\n$jsonMessage" >&2
   else
    #[ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc:jsonMessage\n$jsonMessage" >&2
    #$cmdPrintf "$jsonMessage" | jsonInObjectWrite data $($cmdCat <&0) | shCryptDecode | $cmdBase64 -d -i | $cmdTr -d ' \n' | shCryptoChannelRead $sessionKey
    local dataCrypted="$($cmdCat <&0)"
    if [ -z "$dataCrypted" ];then
     [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc:no data[$dataCrypted]\n" >&2
    else
     local jsonRead="$($cmdPrintf "$jsonMessage" | jsonInObjectWrite data $dataCrypted | shCryptDecode | $cmdBase64 -d -i | $cmdTr -d ' \n')"
     [ "$shCryptoChannel" ] && shLog $shLog_TRACE "$shLogfunc:decode\n$jsonRead" >&2
     if [ -z "$jsonRead" ];then
      [ "$shCryptoChannel" ] && shLog $shLog_ERROR "$shLogfunc:echec decode empty:\n$jsonRead" >&2
     else
      $cmdPrintf "$jsonRead" | shCryptoChannelRead $sessionKey
     fi
    fi
   fi
  fi
 fi
 #$cmdPrintf "coucou"
}

shCryptoChannelInitB() {
 local b64CryptoSession=""
 while read anData; do
  local b64CryptoSession="${anData}${b64CryptoSession}"
 done
# jsonCryptoSession=$($cmdPrintf "$b64CryptoSession" | $cmdBase64 -d -i 2>/dev/null);
 local jsonCryptoSession=$($cmdPrintf "$b64CryptoSession" | $cmdBase64 -d -i 2>/dev/null);
 $cmdPrintf "$jsonCryptoSession" | shCryptWritePipe $shCryptoChannelPipe
# $cmdPrintf "\\\n" | shCryptWritePipe $shCryptoChannelPipe
 $cmdPrintf "jsonCryptoSession $shCryptoChannelPipe ==================================================================================== $b64CryptoSession"
 $cmdPrintf "shCryptoChannelInitB:$jsonCryptoSession"
 $cmdPrintf "$jsonCryptoSession"
 $cmdPrintf "\\\n"
}
shCryptoChannelAsk() {
 local clearMessage=""
 while read anData; do
  local clearMessage="${anData}${b64CryptoSession}"
 done
 $cmdPrintf "ask : $clearMessage $shCryptoChannelPipe ===================================================================================="
 #
 jsonData="$(shCryptReadPipe $shCryptoChannelPipe)"
 $cmdPrintf "jsonData :\r\n$jsonData"
 $cmdPrintf "$jsonData" | json_sh -l
# local keySession="$(echo $jsonData | jsonInObjectRead keySession)"
# local firstMessage="$(echo $jsonData | jsonInObjectRead firstMessage)"
# local ciphers="$(echo $jsonData | jsonInObjectRead ciphers)"
 #keySession="$(shRandomizeASCCI 1000)"

}

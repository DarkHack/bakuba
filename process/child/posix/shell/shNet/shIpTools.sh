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

# which/base
# - cmdListFlux :cmdEcho cmdPrintf cmdHead cmdTail cmdTr cmdCut cmdGrep cmdShuf cmdWc cmdXargs cmdOd cmdTee cmdSeq
cmdList="$cmdList $cmdListFlux"
# which/ipTools
# - cmdListIpTools : cmdBrctl cmdIp cmdIpTables cmdIfup cmdIfdown cmdDhclient
cmdList="$cmdList cmdIp cmdIpTables cmdIfup cmdIfdown cmdDhclient"

cmdAwk="$(which awk)"
cmdList="$cmdList cmdAwk"

shErrorOnMissing $cmdList

#shIptoolsGetHostEth
#shIpToolsGetEths
#shIptoolsGetIps
#shIptoolsGetIp
#shIptoolsGetNets
#shIptoolsGetNet
#shIptoolsGetGw

#shIptoolsRestartEth() {
# local eth=""$1""
# if [ ! -z "$cmdBrctl" ]; then
#  local eth="$($cmdBrctl show 2>/dev/null | grep "$eth")"
#  if [ -z "$eth" ]; then
#   local eth=""$1""
#  else
#   local eth="$($cmdBrctl show 2>/dev/null | head -n 2  | cut -f1 | tail -1)"
#  fi
# fi
# #/bin/sh -c "$cmdDhclient -r "$eth";$cmdDhclient "$eth"" >> /tmp/result.$eth.restart 2>&1
# #/bin/sh -c "$cmdIfdown "$eth" --force;$cmdIfup "$eth" --force" >> /tmp/result.$eth.restart 2>&1
#}
shIptoolsGetGw() {
 local eth="$(shIptoolsGetHostEth "$1")"
 local gw="$($cmdIp -4 route | $cmdGrep "$eth" | $cmdAwk '/default/ { print $3 }' | $cmdHead -n 1)"
# if [ -z "$gw" ]; then
#  local gw="$(shIptoolsGetIp "$eth")"
# fi
 $cmdEcho -e "${gw}\c"
}
shIptoolsGetNet() {
 shIptoolsGetNets ""$1"" | $cmdTr -s ' ' '\n' | $cmdHead -n 1
}
shIptoolsGetNets() {
 local eth="$(shIptoolsGetHostEth "$1")"
 if [ -n "$eth" ];then
  $cmdIp -4 addr show dev $eth | $cmdGrep "$eth\$" | $cmdGrep "inet" | $cmdGrep "scope" | $cmdAwk '{print $2}' | $cmdGrep ""$2""
 else
  $cmdIp -4 addr show | $cmdGrep "inet" | $cmdGrep "scope" | $cmdAwk '{print $2}' | $cmdGrep ""$2""
 fi
}
shIptoolsGetIp() {
 shIptoolsGetIps ""$1"" | $cmdTr -s ' ' '\n' | $cmdHead -n 1
}
shIptoolsGetIps() {
 local eth="$(shIptoolsGetHostEth "$1")"
 if [ -n "$eth" ];then
  $cmdIp -4 addr show dev $eth | $cmdGrep "$eth\$" | $cmdGrep "inet" | $cmdGrep "scope" | $cmdAwk '{print $2}' | $cmdCut -d'/' -f1 | $cmdTr -s '\n' ' '
 else
  $cmdIp -4 addr show | $cmdGrep "inet" | $cmdGrep "scope" | $cmdAwk '{print $2}' | $cmdCut -d'/' -f1 | $cmdTr -s '\n' ' '
 fi
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

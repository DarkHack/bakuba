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

#shIptoolsGetHostEth
#shIpToolsGetEths
#shIptoolsGetIps
#shIptoolsGetIp
#shIptoolsGetNets
#shIptoolsGetNet
#shIptoolsGetGw

shLog $shLog_TRACE "Ip Tools Test"
for eth in $( shIpToolsGetEths ); do
 shLog $shLog_TRACE "connected eth:$eth"
 for ip in $( shIptoolsGetIps $eth );do
  shLog $shLog_TRACE "eth:$eth ip:$ip"
 done
 for net in $( shIptoolsGetNets $eth );do
  shLog $shLog_TRACE "eth:$eth net:$net"
 done
  shLog $shLog_TRACE "eth:$eth gw:$( shIptoolsGetGw $eth )"
done


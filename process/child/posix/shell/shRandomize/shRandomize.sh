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
shRandomizeDebug="1"
#shRandomizeDebug=""

cmdList="cmdSh cmdCat cmdTimeout cmdTr cmdXargs cmdCut cmdWget cmdBase64 cmdEcho cmdPrintf"
cmdList="$cmdList cmdRm cmdMktemp cmdMkfifo cmdOd"
cmdList="$cmdList cmdOpenssl"

#cmdList="$cmdList cmdStrings"
#cmdStrings="$(which strings)"

shErrorOnMissing $cmdList >&2

shRandomizePipe=$(shPipeInit 2>/dev/null)
shPipeOpen $shRandomizePipe 2>/dev/null

shString64Filter() {
 $cmdTr -cd '[:alnum:]+=/|' <&0
# $cmdStrings -e S -a <&0 | $cmdTr -cd '[:alnum:]+=/'

# for char in $( $cmdSed -e 's/\(.\)/\1\n/g' <&0 );do
#  local ascii=$( $cmdPrintf "%d" "'$char" 2>>/dev/null );
#  # valid: "=:61 ,/:47 ,+:43 ,|:124" 
#  # valid:0-9
#  # valid:A-Z
#  # valid:a-z
#  if [ "$ascii" -eq 61 ] || [ "$ascii" -eq 47 ] || [ "$ascii" -eq 43 ]\
#  || ( [ "$ascii" -ge 48 ] && [ "$ascii" -le 57 ] )\
#  || ( [ "$ascii" -ge 65 ] && [ "$ascii" -le 90 ] )\
#  || ( [ "$ascii" -ge 97 ] && [ "$ascii" -le 122 ] )\
#  ; then $cmdPrintf "$char";fi
##  else $cmdPrintf "[!$ascii/$char]" 1>&2; fi
# done
}
shRandomizeASCCI() {
 local shLogfunc="shRandomizeASCCI($1)";
 local length="$1";
 local randomStr="";
 local cpt=0;
 if [ ! $length -gt $cpt ];then [ "$shRandomizeDebug" ] && shLog $shLog_ERROR "$shLogfunc:no length" >&2
 else
  #local shRandomizeBuf=""
  local shRandomizeBuf="$(shPipeRead $shRandomizePipe )"
  local shRandomizeLength=$($cmdPrintf "$shRandomizeBuf" | $cmdWc -c)
  if [ $shRandomizeLength -gt 0 ];then
   if [ $shRandomizeLength -lt $length ];then
    [ "$shRandomizeDebug" ] && shLog $shLog_TRACE "$shLogfunc [not enought] shRandomizeLength:$shRandomizeLength length:$length" >&2
    local randomStr="$shRandomizeBuf"
    local newRandomizeBuf=""
   else
    [ "$shRandomizeDebug" ] && shLog $shLog_TRACE "$shLogfunc [enought] shRandomizeLength:$shRandomizeLength length:$length" >&2
    local randomStr="$($cmdPrintf $shRandomizeBuf | $cmdCut -b -${length} )";
    local newRandomizeBuf="$($cmdPrintf $shRandomizeBuf | $cmdCut -b $(( length + 1 ))- )"
   fi
   #if [ $cpt -gt 0 ];then local cpt=$(( cpt-1 ));fi
   $cmdPrintf "$newRandomizeBuf" | shPipeWrite $shRandomizePipe;
  fi
  local cpt=$($cmdPrintf "$randomStr" | $cmdWc -c)
  #[ "$shRandomizeDebug" ] && shLog $shLog_TRACE "$shLogfunc cpt:$cpt length:$length" >&2
  local toGenerate=$(( length - cpt ));
  #local toGenerate=$(( toGenerate - 1 ));
  while [ $toGenerate -gt 0 ];do
   type="$($cmdShuf -i 0-2 -n 1)"
   case "$type" in 
    0) local range="65-90";;
    1) local range="97-122";;
    2) local range="48-57";;
   esac
   local sizeGenerate="$($cmdShuf -i 1-$toGenerate -n 1)";
   #[ "$shRandomizeDebug" ] && shLog $shLog_TRACE "$shLogfunc range:$range sizeGenerate:$sizeGenerate/$toGenerate cpt:$cpt" >&2
   for randomOctet in $($cmdShuf -i $range -n $sizeGenerate );do
    local randomStr="$randomStr$( $cmdPrintf \\$($cmdPrintf '%o' $randomOctet) )"
   done
   #local cpt=$(( cpt + sizeGenerate ))
   local cpt=$($cmdPrintf "$randomStr" | $cmdWc -c)
   local toGenerate=$(( length - cpt ));
   #local toGenerate=$(( toGenerate - 1 ));
  done
 fi
 $cmdPrintf "$randomStr"
  [ "$shRandomizeDebug" ] && shLog $shLog_TRACE "$shLogfunc cpt:$cpt length:$length" >&2
}

#shCryptRandomASCCI() {
# local shLogfunc="shCryptRandomASCCI($1)"
# local length="$1"
# local randomStr="";
# local cpt=0;
# if [ ! $length -gt $cpt ];then [ "$shCryptDebug" ] && shLog $shLog_ERROR "$shLogfunc:no length" >&2
# else
#  [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc" >&2
#  while [ $length -gt $cpt ];do
#   type="$($cmdShuf -i 0-2 -n 1)"
#   case "$type" in 
#    0) local range="65-90";;
#    1) local range="97-122";;
#    2) local range="48-57";;
#   esac
#   randomStr="$randomStr$($cmdPrintf \\$($cmdPrintf '%03o' $($cmdShuf -i $range -n 1) ))"
#   local cpt=$(( cpt+1 ))
#  done
# fi
# $cmdPrintf "$randomStr"
#}

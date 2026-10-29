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
toSearch="$1"
echo search:$(echo $toSearch | tr 'ù' '\\')
for file in $(find . | grep -v .svn | tr ' ' '£');do
 file="$(echo $file | tr '£' ' ')"
 if [ -f "$file" ];then
  foundResult="$(cat "$file" | grep "$toSearch" | tr '\n' '£')"
  if [ -n "$foundResult" ];then
   echo "============================= $file"
   echo "$foundResult" | tr '£' '\n'
#   break
  fi
 elif [ -d "$file" ];then
  echo "dir:$file" >> /dev/null
 else
  echo"what?$file"
 fi
done

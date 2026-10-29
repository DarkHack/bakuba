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
#
# Create a distributable archive of the current version of Makeself

VER=`cat VERSION`
mkdir -p /tmp/makeself-$VER release
cp -a makeself* test README.md COPYING VERSION .gitmodules /tmp/makeself-$VER/

# create a temporary named pipe
PIPE=$(mktemp -u)
echo "pipe:$PIPE"
mkfifo $PIPE
# attach it to file descriptor 3
exec 3<>$PIPE
# unlink the named pipe
rm $PIPE
echo "coucou" >&3

./makeself.sh --ssl-encrypt --ssl-pass-src "fd:3" --notemp /tmp/makeself-$VER release/makeself-$VER.run "Makeself v$VER" echo "Makeself has extracted itself" 


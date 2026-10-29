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

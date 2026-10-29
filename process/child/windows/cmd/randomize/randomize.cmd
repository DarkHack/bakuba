REM Copyright 2011-2026 Nicolas "DarkHack" Chapuis
REM
REM Licensed under the Apache License, Version 2.0 (the "License");
REM you may not use this file except in compliance with the License.
REM You may obtain a copy of the License at
REM
REM     http://www.apache.org/licenses/LICENSE-2.0
REM
REM Unless required by applicable law or agreed to in writing, software
REM distributed under the License is distributed on an "AS IS" BASIS,
REM WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
REM See the License for the specific language governing permissions and
REM limitations under the License.
rem set "length=900"
set "buf="

set first=1
for /L %i in ( 1, 1, %length% ) do if !first! neq 1 ( set "buf=!buf!,!random!" ) else ( set first=0 && set "buf=!random!" )
set "randomsInt=!buf!"
set "buf="

set first=1
for %i in ( %randomsInt% ) do ( set /a "r=(%i*%dwAsciiLen%/32768)" && if !first! neq 1 ( set "buf=!buf!,!r!" ) else ( set first=0 && set "buf=!r!" ) )
set "randomsAsciiInt=!buf!"
set "buf="

set first=1
for %i in ( %randomsAsciiInt% ) do ( set "c=!dwAsciiStr:~%i,1!" && if !first! neq 1 ( set "buf=!buf!!c!" ) else ( set first=0 && set "buf=!c!" )  )
set "randomsAsciiStr=!buf!"
set "buf="

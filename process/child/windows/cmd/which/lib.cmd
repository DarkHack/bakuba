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

:: Which.cmd
:which  CommandName  [ReturnVar]
::
::  Determines the full path of the file that would execute if
::  CommandName were executed.
::
::  The result is stored in variable ReturnVar, or else it is
::  echoed to stdout if ReturnVar is not specified.
::
::  If no file is found, then an error message is echoed to stderr.
::
::  The ERRORLEVEL is set to one of the following values
::    0 - Success: A matching file was found
::    1 - CommandName is an internal command
::    2 - No file was found and CommandName is not an internal command
::    3 - Improper syntax - no CommandName specified
::
@echo off
setlocal disableDelayedExpansion

set "file=%~1"
setlocal enableDelayedExpansion

if not defined file (
  >&2 echo Syntax error: No CommandName specified
  exit /b 3
)


:: test for internal command
echo(!file!|findstr /i "[^abcdefghijklmnopqrstuvwxyz]" >nul || (
  set "empty=!temp!\emptyFolder"
  md "!empty!" 2>nul
  del /q "!empty!\*" 2>nul >nul
  setlocal
  pushd "!empty!"
  set path=
  (call )
  !file! /? >nul 2>nul
  if not errorlevel 9009 (
    >&2 echo "!file!" is an internal command
    popd
    exit /b 1
  )
  popd
  endlocal
)
:: test for external command
set "noExt="
if "%~x1" neq "" if "!PATHEXT:%~x1=!" neq "!PATHEXT!" set noExt="";
set "modpath=.\;!PATH!"
@for %%E in (%noExt%%PATHEXT%) do @for %%F in ("!file!%%~E") do (
  setlocal disableDelayedExpansion
  if not "%%~$modpath:F"=="" if not exist "%%~$modpath:F\" (
    endlocal & endlocal & endlocal
    if "%~2"=="" (echo %%~$modpath:F) else set "%~2=%%~$modpath:F"
    exit /b 0
  )
  endlocal
)
endlocal

>&2 echo "%~1" is not a valid command
exit /b 2

:: Credits:
:: Dave Benham :: forum thread :: StackOverflow answer

@echo off
set "php=%~dp0files\php.exe"
set "scriptDir=%~dp0"
set serverPort=8530

pushd "%scriptDir%"

title IMI Kurwica WSUS Proxy
echo Emulate: Windows Embedded Standard 7
echo.
echo Address of this local WSUS instance:
echo http://127.0.0.1:%serverPort%
echo.
echo Close this window to stop the server (may freeze)
echo.

:phprun
echo ----- PHP log -----
call "%php%" -c "%scriptDir%files\php.ini" -S 0.0.0.0:%serverPort% -t "%scriptDir%src"


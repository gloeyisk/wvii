@echo off

reg query HKU\S-1-5-19 1>nul 2>nul || (echo This script requires administrator privileges.&goto :TheEnd)

set key=HKEY_LOCAL_MACHINE\SOFTWARE\Policies\Microsoft\Windows\WindowsUpdate
echo.
reg delete %key% /v WUServer /f
reg delete %key% /v WUStatusServer /f
reg delete %key%\AU /v UseWUServer /f
reg delete HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\POSReady /f
net stop wuauserv 2>nul

:TheEnd
echo.
echo Press any key to exit.
pause >nul
goto :eof

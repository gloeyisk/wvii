@echo off
set server=127.0.0.1:8530

reg query HKU\S-1-5-19 1>nul 2>nul || (echo This script requires administrator privileges.&goto :TheEnd)

set key=HKEY_LOCAL_MACHINE\SOFTWARE\Policies\Microsoft\Windows\WindowsUpdate
echo.
reg add %key% /f /v WUServer /t REG_SZ /d "http://%server%/?"
reg add %key% /f /v WUStatusServer /t REG_SZ /d "http://%server%/?"
reg add %key%\AU /f /v UseWUServer /t REG_DWORD /d 1
reg add HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\POSReady /f /v Version /t REG_SZ /d "7.0"
net stop wuauserv 2>nul

:TheEnd
echo.
echo Press any key to exit.
pause >nul
goto :eof

@echo off
set server=127.0.0.1:8530

reg query HKU\S-1-5-19 1>nul 2>nul || (echo This script requires administrator privileges.&goto :TheEnd)

@cls
echo.
choice /C YN /N /M "This will remove and reset DataStore.edb, continue? [y/n]: "
if errorlevel 2 exit

set key=HKEY_LOCAL_MACHINE\SOFTWARE\Policies\Microsoft\Windows\WindowsUpdate
echo.
reg add %key% /f /v WUServer /t REG_SZ /d "http://%server%/?"
reg add %key% /f /v WUStatusServer /t REG_SZ /d "http://%server%/?"
reg add %key%\AU /f /v UseWUServer /t REG_DWORD /d 1
reg add HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\POSReady /f /v Version /t REG_SZ /d "7.0"
net stop wuauserv 2>nul
net stop TrustedInstaller 2>nul
del /f /q %SystemRoot%\SoftwareDistribution\DataStore\DataStore.edb 1>nul 2>nul
pushd %SystemRoot%\SoftwareDistribution\DataStore\Logs
rmdir /s /q . 1>nul 2>nul
popd

:TheEnd
echo.
echo Press any key to exit.
pause >nul
goto :eof

@setlocal DisableDelayedExpansion
@echo off
cd /d "%SystemRoot%\System32"
if not exist sle.dll exit /b 1
if not exist "%~dp0bbe.exe" exit /b 1

sc query wuauserv | find /i "STOPPED" || net stop wuauserv /y
sc query wuauserv | find /i "STOPPED" || sc stop wuauserv
set _wufile=wuaueng.dll
if exist wuaueng2.dll set _wufile=wuaueng2.dll

"%~dp0bbe.exe" -e "s/\x73\x00\x6C\x00\x63\x00\x2E\x00\x64\x00\x6C\x00\x6C\x00/\x73\x00\x6C\x00\x65\x00\x2E\x00\x64\x00\x6C\x00\x6C\x00/" -o wuaueng3.dll %_wufile%
reg add "HKLM\SYSTEM\CurrentControlSet\services\wuauserv\Parameters" /f /v ServiceDll /t REG_EXPAND_SZ /d ^%%SystemRoot^%%\System32\wuaueng3.dll

set "_sku="
for /f "tokens=2 delims==" %%# in ('"wmic OS Get OperatingSystemSKU /value" 2^>nul') do set "_sku=%%#"
if "%_sku%"=="" exit /b 1
set _EsuWU=0
for %%# in (
4 27 70 1 28 71 48 49 69 7 8 10 12 13 14 36 37 38 39 40 41 65
) do (
if %_sku% equ %%# set _EsuWU=1
)
if %_EsuWU% equ 1 exit /b 0

set "_ebak="
for /f "skip=2 tokens=2*" %%a in ('reg query "HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion" /v EditionID 2^>nul') do set "_ebak=%%b"
reg query "HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion" /v EditionID_bak 2>nul && for /f "skip=2 tokens=2*" %%a in ('reg query "HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion" /v EditionID_bak 2^>nul') do set "_ebak=%%b"
set _eid=Professional
for %%# in (5 26 47) do if %_sku% equ %%# set _eid=ProfessionalN
for %%# in (66 67 68) do if %_sku% equ %%# set _eid=ProfessionalE
if exist "%SystemRoot%\Servicing\Packages\Microsoft-Windows-Server-LanguagePack-Package*.mum" (set _eid=ServerStandard)
if defined _ebak reg add "HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion" /f /v EditionID_bak /d %_ebak%
reg add "HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion" /f /v EditionID /d %_eid%

exit /b 0

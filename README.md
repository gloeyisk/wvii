# Dummy WSUS Proxy

## Overview
- Allow Windows 7 Client to receive ESU updates for Windows Embedded Standard 7 through Windows Update

## How To Use
- Recommended to run Windows Update normally first
- Recommended to change Windows Update settings to "Never check for updates"
- Extract and place this pack into a folder with a simple path
- For initial scan, DataStore.edb must be reset and removed by execute/run `Add_wsus-and-Reset_DataStore.cmd`
- Execute `Run_wsus.cmd` and unblock Firewall access for PHP
- Open Windows Update and click "Check for updates"
- When finished checking and installing updates, close the PHP command window

## Remarks
- If you only want to register WSUS Proxy, execute `Add_wsus-only.cmd`
- If you want to remove WSUS Proxy, execute `Remove_wsus.cmd`
- You cannot remove WSUS configuration after initial scan and revert to use normal WU
  - WU metadata will be reset then, and ESU updates will not be detected
- Some old updates (superseded or installed) may re-offered
  - If your OS is already updated, ignore or hide those updates, and only install new updates released after 2023-01

## Alternative method after initial scan
- Run WSUS Proxy initial scan as explained above
- Keep WSUS configuration without running WSUS Proxy
- Open Windows Update and click on the link "check online for updates from Windows Update"
- First time scan will take some time to repopulate database
- Going forward, you can keep using the clickable link without running WSUS Proxy
- ESU metadata might change anytime (possibly before or during October 2023 Patch Tuesday)
  - If that occur when WSUS Proxy is not running, ESU updates will no longer be received
  - In that case, you must execute `Add_wsus-and-Reset_DataStore.cmd` and perform initial scan once again

## Short Explanations for `Run_wsus-Both.cmd`
- This variant emulate both update categories together: "Windows 7" and "Windows Embedded Standard 7"
- It allow to receive applicable updates from both, most of them will be duplicated (shared) for both
- It's mainly ment as experiment for testing, or to receive updates for other Microsoft products not supported by Embedded category, such as Microsoft Security Essentials

## Credits
- abbodi1406
- Dummy WSUS
- IMI Kurwica - WSUS Proxy mod

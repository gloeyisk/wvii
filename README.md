# Bypass ESU

## Overview
- A project to install Extended Security Updates for Windows 7 and Server 2008 R2
- It consists of three functions:   
  - Suppress ESU eligibility check for OS updates (including .NET 3.5.1)   
  - Bypass ESU validation for .NET 4 updates (4.5.2 up to 4.8)   
  - Patch WU engine to allow receiving ESU updates   

## Important Notes
- WU ESU Patcher   
Windows 7 Client: Allow to receive updates up to 2023-01   
Windows 7 Embedded: It has no effect and do not work   
Windows Server 2008 R2: Allow to receive updates up to 2024-01 (at least)   
- .NET 4 ESU Bypass   
It has incompatibility issue and may cause MSI or other programs to stop working   
Therefore, it is recommended to install it only when a new .NET 4 updates are available, then remove it after installing the updates   
If it fails to install .NET 4 updates, temporary disable Antivirus protection   
- Extract and place this pack into a folder with a simple path
- System Restart is required after installing WU ESU Patcher on live system
- Temporarily turn off Antivirus protection (if any), or exclude the extracted folder

## How To Use
- Run as Administrator for `LiveOS-Setup.cmd`  
- From the prompted menu, press the corresponding number for the desired option (recommended to choose Number 1)

## Credits
- abbodi1406
- Gamers-Against-Weed / superUser - haveSxS

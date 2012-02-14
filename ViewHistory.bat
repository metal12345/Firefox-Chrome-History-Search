@ ECHO OFF
set OLDDIR=%CD%
cd /d "%OLDDIR%\QuickPHP"


echo This will display the program's current history database.
pause

tskill QuickPHP 

ECHO Close QuickPHP from the system tray when finished. Ensure port 5723 is not accessable from outside.

START QUICKPHP.exe /start /startbrowser /minimized /PHPMaxSecs=0 /NoConfirm=true /ShowTrayIcon=true
pause

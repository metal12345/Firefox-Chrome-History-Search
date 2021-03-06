@ ECHO OFF
echo Warning, this will overwrite the program's current history files
echo with the latest history from your browser.
pause


tskill QuickPHP 
set OLDDIR=%CD%

echo ----------------------------------------------------------------
VER | FINDSTR /L "5.0" > NUL
IF %ERRORLEVEL% EQU 0 SET OSVersion=2000
VER | FINDSTR /L "5.1." > NUL
IF %ERRORLEVEL% EQU 0 SET OSVersion=XP
VER | FINDSTR /L "5.2." > NUL
IF %ERRORLEVEL% EQU 0 SET OSVersion=2003
VER | FINDSTR /L "6.0." > NUL
IF %ERRORLEVEL% EQU 0 SET OSVersion=Vista
VER | FINDSTR /L "6.1." > NUL
IF %ERRORLEVEL% EQU 0 SET OSVersion=7

IF %OSVersion%==Unknown (
	ECHO Unable to determine your version of Windows.
) ELSE (
	ECHO You appear to be using Windows %OSVersion%
)
echo ----------------------------------------------------------------



IF %OSVersion%==7 (
echo ----------------------------------------------------------------
	CD /d "%APPDATA%\Mozilla\Firefox\Profiles\*.default"
	IF EXIST places.sqlite (
		ECHO Copying Firefox history from "%APPDATA%\Mozilla\Firefox\Profiles\*.default"
		COPY "places.sqlite" "%OLDDIR%\html"
	) ELSE (
		ECHO Firefox history could not be found. Places places.sqlite in the "files" directory. See https://support.mozilla.org/en-US/kb/Profiles
	)
	cd /d "%OLDDIR%\QuickPHP"
echo ----------------------------------------------------------------
	CD /d "%LOCALAPPDATA%\Chromium\User Data\Default"
	IF EXIST History (
		ECHO Copying Chromium history from "%LOCALAPPDATA%\Chromium\User Data\Default"
		COPY "History" "%OLDDIR%\html"
		COPY "Favicons" "%OLDDIR%\html"
	) ELSE (
		ECHO Chromium history could not be found.
	)
	cd /d "%OLDDIR%\QuickPHP"
echo ----------------------------------------------------------------

	CD /d "%LOCALAPPDATA%\Google\Chrome\User Data\Default"
	IF EXIST History (
		ECHO Copying Google Chrome history from "%LOCALAPPDATA%\Google\Chrome\User Data\Default"
		COPY "History" "%OLDDIR%\html"
		COPY "Favicons" "%OLDDIR%\html"
	) ELSE (
		ECHO Google Chrome history could not be found.
	)
	cd /d "%OLDDIR%\QuickPHP"

echo ----------------------------------------------------------------
) ELSE (
echo ----------------------------------------------------------------
	CD /d "%APPDATA%\Mozilla\Firefox\Profiles\*.default"
	IF EXIST places.sqlite (
		ECHO Copying Firefox history from "%APPDATA%\Mozilla\Firefox\Profiles\*.default"
		XCOPY "places.sqlite" "%OLDDIR%\html"
	) ELSE (
		ECHO Firefox history could not be found. Places places.sqlite in the "files" directory. See https://support.mozilla.org/en-US/kb/Profiles
	)
	cd /d "%OLDDIR%\QuickPHP"

echo ----------------------------------------------------------------
	CD /d "%USERPROFILE%\Local Settings\Application Data\Chromium\User Data\Default"
	IF EXIST History (
		ECHO Copying Chromium history from "%USERPROFILE%\Local Settings\Application Data\Chromium\User Data\Default"
		XCOPY "History" "%OLDDIR%\html"
		XCOPY "Favicons" "%OLDDIR%\html"
	) ELSE (
		ECHO Chromium history could not be found.
	)
	cd /d "%OLDDIR%\QuickPHP"
echo ----------------------------------------------------------------

	CD /d "%USERPROFILE%\Local Settings\Application Data\Google\Chrome\User Data\Default"
	IF EXIST History (
		ECHO Copying Google Chrome history from "%USERPROFILE%\Local Settings\Application Data\Google\Chrome\User Data\Default"
		XCOPY "History" "%OLDDIR%\html"
		XCOPY "Favicons" "%OLDDIR%\html"
	) ELSE (
		ECHO Google Chrome history could not be found.
	)
	cd /d "%OLDDIR%\QuickPHP"
echo ----------------------------------------------------------------

)


ECHO Launching QuickPHP. Close QuickPHP from the system tray when finished. Ensure port 5723 is not accessable from outside.
START QUICKPHP.exe /start /startbrowser /minimized /PHPMaxSecs=0 /NoConfirm=true /ShowTrayIcon=true

pause

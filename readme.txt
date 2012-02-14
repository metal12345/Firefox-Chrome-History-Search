Installation
--------------------------------
Run FirstLaunch.bat
Run ViewHistory.bat if you want to launch the viewer without overwriting its current history file, 
Close QuickPHP from the system tray when finished. Ensure port 5723 is firewalled and not forwarded.


	IF FirstLaunch.bat can't find history
	--------------------------------
	Firefox: Put places.sqlite in ./html To find Firefox history files see https://support.mozilla.org/en-US/kb/Profiles.
	Chrome/Chromium: Put History and Favicons files in ./html To find Chrome history files see http://***********.


Paranoid Installation
---------------------------------

	Windows
	-----------------------------
	Download the barebones version of QuickPHP at http://www.zachsaw.com/?pg=quickphp_php_tester_debugger
	Download the Thread-Safe version of PHP 5.3 from http://windows.php.net/downloads/releases/php-5.3.10-Win32-VC9-x86.zip
	Unzip both to ./QuickPHP (don't overwrite ini files)
	Put places.sqlite in ./html for Firefox. 
	Put History and Favicons files in ./html for Chrome/Chromium
	Check the source for malicious code
	Run RunMe.bat or run ./QuickPHP/QuickPHP.exe, start the web server and visit localhost:5723
	
	Linux
	-----------------------------
	Get a webserver and use the contents of ./html 
	
	
	
Preventing Firefox History Deletion
----------------------------------
Firefox clears large portions of its database every so often.
To prevent this, go to the URL "about:config" in Firefox and create these settings if they don't already exist.
browser.history_expire_days = 9999999 
browser.history_expire_days_min = 9999999
browser.history_expire_sites = 9999999 


Credits
---------------------------------
about:me (SQL used for charts)
https://github.com/leibovic/about-me

PChart (PHP to draw charts)
http://www.pchart.net/

QuickPHP (Webserver)
http://www.zachsaw.com/?pg=quickphp_php_tester_debugger
Installation
--------------------------------
Run RunMe.bat
Close QuickPHP from the system tray when finished. Ensure port 5723 is firewalled and not forwarded.
If the batch cannot find Firefox/Chrome/Chromium history then put places.sqlite in ./html for Firefox. Put History and Favicons files in ./html for Chrome/Chromium. To find Firefox history files see https://support.mozilla.org/en-US/kb/Profiles. To find Chrome history files see ***********.


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

Credits
---------------------------------
about:me (SQL used for charts)
https://github.com/leibovic/about-me

PChart (PHP to draw charts)
http://www.pchart.net/

QuickPHP (Webserver)
http://www.zachsaw.com/?pg=quickphp_php_tester_debugger
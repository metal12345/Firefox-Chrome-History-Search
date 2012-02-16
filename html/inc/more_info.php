<?php
include("functions.php");
$firefox = '../'."places.sqlite";
$chromefavicons = '../'."Favicons";
$chromehistory = '../'."History";

	$chrome = sqlite_escape_string($_GET["chrome"]);
	if ($chrome == "1") {
		if (isset($_GET["q"])) {
			$db = new PDO('sqlite:'.$chromehistory);
			$db2 = new PDO('sqlite:'.$chromefavicons);
			$q = sqlite_escape_string($_GET["q"]);
			

			$dates = chromeDates($q, $db);
			echo '<b>On dates</b><br /> ';
			foreach($dates as $date) {
				echo ($date . "<br />");				
			}		
			
			$froms = chromeFrom($q, $db, $db2);
			if($froms != null ){
				echo '<b>From</b><br /> ';
			}
			foreach($froms as $from) {
				$from = (explode("|split|", $from));
				echo ($from[2] . "<a href='" . $from[0] . "'>" . $from[1] . "</a>" . "<br />" );				
			}	
			
			$tos = chromeTo($q, $db, $db2);
			if($tos != null ){
				echo '<b>Visited</b><br /> ';
			}
			foreach($tos as $to) {
				$to = (explode("|split|", $to));
				echo ($to[2] . "<a href='" . $to[0] . "'>" . $to[1] . "</a>" . "<br />" );				
			}	
		}
	}
	else{
		if (isset($_GET["q"])) {
			$db = new PDO('sqlite:'.$firefox);
			$q = sqlite_escape_string($_GET["q"]);
			
			$dates = firefoxDates($q, $db);
			echo '<b>On dates</b><br /> ';
			foreach($dates as $date) {
				echo ($date . "<br />");				
			}
			
			$tos = firefoxTo($q, $db);
			if($tos != null ){
				echo '<b>To</b><br /> ';
			}
			foreach($tos as $to) {
				$to = (explode("|split|", $to));
				echo ($to[2] . "<a href='" . $to[0] . "'>" . $to[1] . "</a>" . "<br />" );				
			}
			
			$froms = firefoxFrom($q, $db);
			if($froms != null ){
				echo '<b>From</b><br /> ';
			}
			foreach($froms as $from) {
				$from = (explode("|split|", $from));
				echo ($from[2] . "<a href='" . $from[0] . "'>" . $from[1] . "</a>" . "<br />" );				
			}
		}
	}
?>

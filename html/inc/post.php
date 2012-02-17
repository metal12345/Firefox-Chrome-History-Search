<?php
include("functions.php");

//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
	//USER EDITABLE//
		//Firefox database name
	$firefox = '../'."places.sqlite";
		//Chrome favicons database name
	$chromefavicons = '../'."Favicons";
		//Chrome history database name
	$chromehistory = '../'."History";
		//Name to export CSV to
	$csvFileName = rand(1,9999) . Uniqid() . ".csv";
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////

	//INITILIZE//
	$end_result = '';
	$numrows = 0;
	$whereTitle = $whereURL = $whereORURL = $whereORTitle = $tempArray = $csv = array();
	
	//INPUT//
		//Search string
	$searchArray = str_getcsv(rtrim(ltrim(sqlite_escape_string($_POST['search']))), " ", '"');
		//WHERE Title?
	$isTitle = sqlite_escape_string($_POST['isTitle']);
		//WHERE URL?
	$isUrl = sqlite_escape_string($_POST['isUrl']);
		//WHERE Hidden?
	$isHidden = sqlite_escape_string($_POST['isHidden']);
		//Order by?
	$orderBy = sqlite_escape_string($_POST['orderBy']);
		//Regex coulm
	$regexName= sqlite_escape_string($_POST['regexName']);
		//Regex pattern
	$regexPattern = sqlite_escape_string($_POST['regexPattern']);
	if($regexPattern == ""){$regexPattern = "^^";
	}
		//Results limit
	$limit = sqlite_escape_string($_POST['limit']);
	if($limit==0){
		$limit = "";
	}else{$limit = ("LIMIT ".$limit);}
		//Export to CSV?
	$isCSV = sqlite_escape_string($_POST['isCSV']);
	if($isCSV==1){
		echo '<a href="csv/' . $csvFileName . '">Download CSV</a><br />';
	}
		//Chrome or Firefox?
	$chrome = sqlite_escape_string($_POST['chrome']);

	
	//////////////////
	//Chrome Results//
	/////////////////
	if ($chrome == "1") {
		if(file_exists($chromefavicons) && file_exists($chromehistory)){
			$db = new PDO('sqlite:'.$chromehistory);
			$db2 = new PDO('sqlite:'.$chromefavicons);
			
		function regex($string, $pattern) {
			if(preg_match($pattern, $string)){
			return 1;
			}
			return 0;
		}
		$db->sqliteCreateFunction('regex', 'regex', 2);

		}else{ 
			echo "Make sure ".$chromehistory." and ". $chromefavicons ." are present.<br/>";
			echo "In Windows7 you can find this at C:\Users\**username**\AppData\Local\Google\Chrome\User Data\Default <br/>";
			echo "If using Chromium, C:\Users\**username**\AppData\Local\Chromium\User Data\Default <br/>";
			exit();	
		}

		$tempArray[0] = getSyntaxedWhere("urls.url", $searchArray);
		$tempArray[1] = getSyntaxedWhere("urls.title", $searchArray);
		$whereAll = my_join($tempArray, " or ");

		///////////////////
		//Chrome Query////
		//////////////////	
		$row = $db->prepare("
		SELECT distinct 
			
			urls.id id,
			urls.url url, 
			urls.title title,
			urls.hidden hidden,
			urls.visit_count visit_count,
			urls.typed_count typed,
			datetime((urls.last_visit_time- 11644473600000000)/ 1000000,'unixepoch') lastvisit
		FROM urls
		WHERE urls.hidden like '" . $isHidden . "'" .
			" and REGEX($regexName, '$regexPattern') like '1' " .
			" and (" . $whereAll . ") GROUP BY urls.id " .
		"ORDER BY " . $orderBy ." DESC " . $limit );
		$row->execute();
		foreach($row as $r) {
			$numrows++;
			
			$favicon = chromeFavicon($r['url'], $db2);
			
			$url = "<a href='" . $r['url'] . "'>";
			$displayUrl = $r['url'];
			$displayTitle = $r['title'];
			foreach($searchArray as $bold){
				if($bold == "OR"){continue;}
				$displayUrl = str_ireplace($bold, ('<span class="found">' . $bold . '</span>'), $displayUrl);
			}
			foreach($searchArray as $bold){
				if($bold == "OR"){continue;}
				$displayTitle = str_ireplace($bold, ('<span class="found">' . $bold . '</span>'), $displayTitle);
			}
			
			if($r['title']==""){
				$url = $url . $displayUrl . "</a>";
			}
			else{
				$url = $url . $displayTitle . "</a>";
			}


			$end_result .=
			'<div id="'. $r['id'] . '">' .
				'<li>' . 
					'<img src="img/search_ico.PNG" onclick="showInfo(' . "'" . $r['id'] . "'" . ')" >' .
					$favicon .
					'<div id="date">' . $r['lastvisit'] . " " . "</div>" .
					$url .
					'<div class="visits">' . "<br/>Visits: " . $r['visit_count'] .   
					'<div class="typed">' . "Typed: " .  $r['typed'] . '  </div>' . 
					'<div class="hidden">' . "Hidden: " .  $r['hidden'] . '  </div>' .   
					'<div id="info' . $r['id'] . '" class="info"></div>' . 					
				'</li>'.
			'</div>';
			
			array_push($csv, $favicon . "|split|" . $r['id'] . "|split|" . $r['lastvisit'] . "|split|" . $r['url'] . "|split|" . $r['title'] . "|split|" . $r['visit_count'] . "|split|" . $r['typed'] . "|split|" . $r['hidden']);
		}
		echo "$numrows results returned"; 
		echo $end_result;	
	}
	

	///////////////////
	//Firefox Results///
	//////////////////
	else{
		//DB
		if(file_exists($firefox)){
			$db = new PDO('sqlite:'.$firefox);
		}	
		else{ 
			echo "Make sure ".$firefox." is present.<br/>";
			echo "You can find this at %APPDATA%\Mozilla\Firefox\Profiles\**ProfileName**\places.sqlite <br/>";
			echo "If using Firefox Portable, FirefoxPortable\Data\profile\places.sqlite <br/>";
			exit();	
		}
		function regex($string, $pattern) {
			if(preg_match($pattern, $string)){
			return 1;
			}
			return 0;
		}
		$db->sqliteCreateFunction('regex', 'regex', 2);
		

		$tempArray[0] = getSyntaxedWhere("moz_places.url", $searchArray);
		$tempArray[1] = getSyntaxedWhere("moz_places.title", $searchArray);
		$whereAll = my_join($tempArray, " or ");
		
		///////////////////
		//Firefox Query////
		//////////////////
		$row = $db->prepare("
			SELECT distinct 
				moz_places.id id,
				moz_places.url url, 
				moz_places.title title, 
				moz_places.hidden hidden,
				moz_places.frecency frecency, 
				moz_places.visit_count visit_count,
				moz_places.typed typed,
				moz_favicons.data favicon, 
				datetime(moz_places.last_visit_date/1000000,'unixepoch') lastvisit
			FROM moz_places 
			left join moz_favicons on moz_places.favicon_id = moz_favicons.id
			WHERE moz_places.hidden like '" . $isHidden . "'" .
			" and REGEX($regexName, '$regexPattern') like '1' " .
			" and (" . $whereAll . ") GROUP BY moz_places.id " .
			"ORDER BY " . $orderBy ." DESC " . $limit );
		$row->execute();
		foreach($row as $r) {
			$numrows++;
			

			$url = "<a href='" . $r['url'] . "'>";
			$displayUrl = $r['url'];
			$displayTitle = $r['title'];
			foreach($searchArray as $bold){
				if($bold == "OR"){continue;}
				$displayUrl = str_ireplace($bold, ('<span class="found">' . $bold . '</span>'), $displayUrl);
			}
			foreach($searchArray as $bold){
				if($bold == "OR"){continue;}
				$displayTitle = str_ireplace($bold, ('<span class="found">' . $bold . '</span>'), $displayTitle);
			}
			
			if($r['title']==""){
				$url = $url . $displayUrl . "</a>";
			}
			else{
				$url = $url . $displayTitle . "</a>";
			}


			if($r['favicon']==""){
				$favicon = '<img src="img/favicon.ico' . '" />';
			}
			else{
				$favicon = '<img width="16" height="16" src="data:image/x-icon;base64,' . base64_encode( $r['favicon'] ) . '" />';
			}

			$end_result     .= 
			'<div id="'. $r['id'] . '">' .
				'<li>' . 
					'<img src="img/search_ico.PNG" onclick="showInfo(' . "'" . $r['id'] . "'" . ')" >' .
					$favicon .
					'<div id="date">' . $r['lastvisit'] . " " . "</div>" .
					$url .
					'<div class="visits">' . "<br/>Visits: " . $r['visit_count'] .   
					'<div class="typed">' . "Typed: " .  $r['typed'] . '  </div>' . 
					'<div class="hidden">' . "Hidden: " .  $r['hidden'] . '  </div>' .   
					'<div class="frecency">' . "Priority: " .  $r['frecency'] . '  </div>'  . 
					'<div id="info' . $r['id'] . '" class="info"></div>' . 
				'</li>'.
			'</div>';
			array_push($csv, $favicon . "|split|" . $r['id'] . "|split|" . $r['lastvisit'] . "|split|" . $r['url'] . "|split|" . $r['title'] . "|split|" . $r['visit_count'] . "|split|" . $r['typed'] . "|split|" . $r['hidden']);
		}
		echo "$numrows results returned"; 
		echo $end_result;
	}
	
	if($isCSV==1){
		$file = fopen("../csv/$csvFileName","w");
		foreach ($csv as $line)
		  {
		  fputcsv($file,explode('|split|',$line));
		  }
		fclose($file); 
	}

?>

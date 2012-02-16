<?php
include("functions.php");
	//VARIABLES
	$firefox = '../'."places.sqlite";
	$chromefavicons = '../'."Favicons";
	$chromehistory = '../'."History";
	$csvFileName = rand(1,9999) . Uniqid() . ".csv";
	//INITILIZE
	$end_result = '';
	$numrows = 0;
	$whereTitle = $whereURL = $whereORURL = $whereORTitle = $tempArray = $csv = array();
	
	//INPUT
	$searchArray = str_getcsv(rtrim(ltrim(sqlite_escape_string($_POST['search']))), " ", '"');
	$isTitle = sqlite_escape_string($_POST['isTitle']);
	$isUrl = sqlite_escape_string($_POST['isUrl']);
	$isHidden = sqlite_escape_string($_POST['isHidden']);
	$orderBy = sqlite_escape_string($_POST['orderBy']);
	
	$limit = sqlite_escape_string($_POST['limit']);
	if($limit==0){
		$limit = "";
	}else{$limit = ("LIMIT ".$limit);}
	
	$isCSV = sqlite_escape_string($_POST['isCSV']);
	if($isCSV==1){
		echo '<a href="csv/' . $csvFileName . '">Download CSV</a><br />';
	}
	
	$chrome = sqlite_escape_string($_POST['chrome']);


	if ($chrome == "1") {
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(file_exists($chromefavicons) && file_exists($chromehistory)){
			$db = new PDO('sqlite:'.$chromehistory);
			$db2 = new PDO('sqlite:'.$chromefavicons);
		}else{ 
			echo "Make sure ".$chromehistory." and ". $chromefavicons ." are present.<br/>";
			echo "In Windows7 you can find this at C:\Users\**username**\AppData\Local\Google\Chrome\User Data\Default <br/>";
			echo "If using Chromium, C:\Users\**username**\AppData\Local\Chromium\User Data\Default <br/>";
			exit();	
		}

		$temp = 0;
		if($isTitle=="1"){
			foreach($searchArray as $value) {
				if($value == "OR"){
					$temp = 1;continue;
				}
				//If term isn't preceded by OR, push to where array.
				if($temp == 0){
					//Exclude term if preceded by "-"
					if(stringBeginsWith($value,"-")){
						array_push($whereTitle, "(urls.title not LIKE '%" . substr($value, 1) . "%')");
					}else{
						array_push($whereTitle, "(urls.title LIKE '%" . $value . "%')");
					}	
				//Else push to whereOR array
				}else{
					//Exclude term if preceded by "-"
					if(stringBeginsWith($value,"-")){
						array_push($whereORTitle, "(urls.title not	LIKE '%" . substr($value, 1) . "%')");
					}else{
						array_push($whereORTitle, "(urls.title 	LIKE '%" . $value . "%')");
					}	
					$temp = 0;
				}		
			}
			$whereTitle = " (" . implode(' and ', $whereTitle) . ") ";
			$whereORTitle = " (" . implode(' or ', $whereORTitle) . ") ";
			$tempArray[0] = $whereTitle; $tempArray[1] = $whereORTitle;
			$whereTitle = " (" . my_join($tempArray, " and ") . ") ";
		}
		
		$temp = 0;
		if($isUrl=="1"){
			foreach($searchArray as $value) {
				if($value == "OR"){
					$temp = 1;continue;
				}
				//If term isn't preceded by OR, push to where array.
				if($temp == 0){
					//Exclude term if preceded by "-"
					if(stringBeginsWith($value,"-")){
						array_push($whereURL, "(urls.url not	LIKE '%" . substr($value, 1) . "%')");
					}else{
						array_push($whereURL, "(urls.url 	LIKE '%" . $value . "%')");
					}	
				//Else push to whereOR array
				}else{
					//Exclude term if preceded by "-"
					if(stringBeginsWith($value,"-")){
						array_push($whereORURL, "(urls.url not	LIKE '%" . substr($value, 1) . "%')");
					}else{
						array_push($whereORURL, "(urls.url 	LIKE '%" . $value . "%')");
					}	
					$temp = 0;
				}		
			}
			$whereURL = " (" . implode(' and ', $whereURL) . ") ";
			$whereORURL = " (" . implode(' or ', $whereORURL) . ") ";
			$tempArray[0] = $whereURL; $tempArray[1] = $whereORURL;
			$whereURL = " (" . my_join($tempArray, " and ") . ") ";
		}
		
		
		$tempArray[0] = $whereURL;
		$tempArray[1] = $whereTitle;
		$whereAll = my_join($tempArray, " or ");

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
		//QUERY
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
		WHERE urls.hidden like '" . $isHidden . "' 
			and (" . $whereAll . ") GROUP BY urls.id " .
		"ORDER BY " . $orderBy ." DESC " . $limit );
		$row->execute();
		foreach($row as $r) {
			$numrows++;
			
			$favicon = chromeFavicon($r['url'], $db2);
			
			$url = "<a href='" . $r['url'] . "'>";
			$displayUrl = $r['url'];
			$displayTitle = $r['title'];
			foreach($searchArray as $bold){
				if($value == "OR"){continue;}
				$displayUrl = str_ireplace($bold, ('<span class="found">' . $bold . '</span>'), $displayUrl);
			}
			foreach($searchArray as $bold){
				if($value == "OR"){continue;}
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
		echo '<br /><a href="test.csv">Download CSV</a>';
	}
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
		
		
		$temp = 0;
		if($isTitle=="1"){
			foreach($searchArray as $value) {
				if($value == "OR"){
					$temp = 1;continue;
				}
				//If term isn't preceded by OR, push to where array.
				if($temp == 0){
					//Exclude term if preceded by "-"
					if(stringBeginsWith($value,"-")){
						array_push($whereTitle, "(moz_places.title not LIKE '%" . substr($value, 1) . "%')");
					}else{
						array_push($whereTitle, "(moz_places.title LIKE '%" . $value . "%')");
					}	
				//Else push to whereOR array
				}else{
					//Exclude term if preceded by "-"
					if(stringBeginsWith($value,"-")){
						array_push($whereORTitle, "(moz_places.title not	LIKE '%" . substr($value, 1) . "%')");
					}else{
						array_push($whereORTitle, "(moz_places.title 	LIKE '%" . $value . "%')");
					}	
					$temp = 0;
				}		
			}
			$whereTitle = " (" . implode(' and ', $whereTitle) . ") ";
			$whereORTitle = " (" . implode(' or ', $whereORTitle) . ") ";
			$tempArray[0] = $whereTitle; $tempArray[1] = $whereORTitle;
			$whereTitle = " (" . my_join($tempArray, " and ") . ") ";
		}
		
		
		$temp = 0;
		if($isUrl=="1"){
			foreach($searchArray as $value) {
				if($value == "OR"){
					$temp = 1;continue;
				}
				//If term isn't preceded by OR, push to where array.
				if($temp == 0){
					//Exclude term if preceded by "-"
					if(stringBeginsWith($value,"-")){
						array_push($whereURL, "(moz_places.url not	LIKE '%" . substr($value, 1) . "%')");
					}else{
						array_push($whereURL, "(moz_places.url 	LIKE '%" . $value . "%')");
					}	
				//Else push to whereOR array
				}else{
					//Exclude term if preceded by "-"
					if(stringBeginsWith($value,"-")){
						array_push($whereORURL, "(moz_places.url not	LIKE '%" . substr($value, 1) . "%')");
					}else{
						array_push($whereORURL, "(moz_places.url 	LIKE '%" . $value . "%')");
					}	
					$temp = 0;
				}		
			}
			$whereURL = " (" . implode(' and ', $whereURL) . ") ";
			$whereORURL = " (" . implode(' or ', $whereORURL) . ") ";
			$tempArray[0] = $whereURL; $tempArray[1] = $whereORURL;
			$whereURL = " (" . my_join($tempArray, " and ") . ") ";
		}
		//$whereURL = getSyntaxedWhere("moz_places.url");
		
		$tempArray[0] = $whereURL;
		$tempArray[1] = $whereTitle;
		$whereAll = my_join($tempArray, " or ");
		
		
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
			WHERE moz_places.hidden like '" . $isHidden . 
			"' and (" . $whereAll . ") GROUP BY moz_places.id " .
			"ORDER BY " . $orderBy ." DESC " . $limit );
		$row->execute();
		foreach($row as $r) {
			$numrows++;
			

			$url = "<a href='" . $r['url'] . "'>";
			$displayUrl = $r['url'];
			$displayTitle = $r['title'];
			foreach($searchArray as $bold){
				if($value == "OR"){continue;}
				$displayUrl = str_ireplace($bold, ('<span class="found">' . $bold . '</span>'), $displayUrl);
			}
			foreach($searchArray as $bold){
				if($value == "OR"){continue;}
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


	if (!function_exists('str_getcsv')) {
		function str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = null, $eol = null) {
			$temp = fopen("php://memory", "rw");
			fwrite($temp, $input);
			fseek($temp, 0);
			$r = fgetcsv($temp, 4096, $delimiter, $enclosure);
			fclose($temp);
			return $r;
		}
	}

	

	
	

?>

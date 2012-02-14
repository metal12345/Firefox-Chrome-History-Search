<?php
//VARIABLES
$end_result = '';
$numrows = 0;

$where = array();
$whereTitle = array();
$whereURL = array();

//INPUT
$isTitle = sqlite_escape_string($_POST['isTitle']);
$isUrl = sqlite_escape_string($_POST['isUrl']);
$isHidden = sqlite_escape_string($_POST['isHidden']);
$orderBy = sqlite_escape_string($_POST['orderBy']);
$limit = sqlite_escape_string($_POST['limit']);
$word = rtrim(ltrim(sqlite_escape_string($_POST['search'])));
$word2 = str_getcsv($word, " ", '"');

if($limit ==0){
	$limit = "";
}else{$limit = ("LIMIT ".$limit);}

$chrome = sqlite_escape_string($_POST['chrome']);
if ($chrome == "1") {
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if (isset($_POST['search'])) {	
		$chromefavicons = "Favicons";
		$chromehistory = "History";
	
		if(file_exists($chromefavicons) && file_exists($chromehistory)){
			$db = new PDO('sqlite:'.$chromehistory);
			$db2 = new PDO('sqlite:'.$chromefavicons);
		}else{ 
			echo "Make sure ".$chromehistory." and ". $chromefavicons ." are present.<br/>";
			echo "In Windows7 you can find this at C:\Users\**username**\AppData\Local\Google\Chrome\User Data\Default <br/>";
			echo "If using Chromium, C:\Users\**username**\AppData\Local\Chromium\User Data\Default <br/>";
			exit();	
		}
	
		if($isTitle=="1"){
			foreach($word2 as $value) {
				if(stringBeginsWith($value,"-")){
					array_push($whereTitle, "(urls.title not	LIKE '%" . substr($value, 1) . "%')");
				}
				else{
					array_push($whereTitle, "(urls.title 	LIKE '%" . $value . "%')");
				}
			}
		}
		if($isUrl=="1"){
			foreach($word2 as $value) {
				if(stringBeginsWith($value,"-")){
					array_push($whereURL, "(urls.url not	LIKE '%" . substr($value, 1) . "%')");
				}
				else{
					array_push($whereURL, "(urls.url 	LIKE '%" . $value . "%')");
				}
			}
		}
		
		if($isTitle==1){
			$whereTitle = " (" . implode(' and ', $whereTitle) . ") ";
		}
		if($isUrl==1){
		$whereURL = " (" . implode(' and ', $whereURL) . ") ";
		}
		$where[0] = $whereURL;
		$where[1] = $whereTitle;
		$whereAll = implode(' or ', array_filter($where));
		echo $whereAll;

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
			///////////////FAVICONS///////////////////
			$rowIcon = $db2->prepare("
			SELECT
				favicons.image_data favicon, 
				icon_mapping.page_url page_url, 
				favicons.id, 
				icon_mapping.id, 
				icon_mapping.icon_id 
			FROM favicons 
			join icon_mapping on icon_mapping.icon_id = favicons.id
			where icon_mapping.page_url = '" . sqlite_escape_string($r['url']) . "'");
			$rowIcon->execute();
			$favicon = '<img src="favicon.ico' . '" />';
			foreach($rowIcon as $ir) {
				if($ir['favicon']==""){
					$favicon = '<img src="favicon.ico' . '" />';
				}
				else{
					$favicon = '<img width="16" height="16" src="data:image/x-icon;base64,' . base64_encode( $ir['favicon'] ) . '" />';
				}
			}
			///////////////X-FAVICONS//////////////////////
			
			
			$url = "<a href='" . $r['url'] . "'>";
			$displayUrl = $r['url'];
			$displayTitle = $r['title'];
			foreach($word2 as $bold){
				$displayUrl = str_ireplace($bold, ('<span class="found">' . $bold . '</span>'), $displayUrl);
			}
			foreach($word2 as $bold){
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
					$favicon .
					'<img src="search_ico.PNG" onclick="showInfo(' . "'" . $r['id'] . "'" . ')" >' .
					'<div id="date">' . $r['lastvisit'] . " " . "</div>" .
					$url .
					'<div class="visits">' . "<br/>Visits: " . $r['visit_count'] .   
					'<div class="typed">' . "Typed: " .  $r['typed'] . '  </div>' . 
					'<div class="hidden">' . "Hidden: " .  $r['hidden'] . '  </div>' .   
					'<div id="info' . $r['id'] . '" class="info"></div>' . 
				'</li>'.
			'</div>';
		}
		echo "$numrows results returned"; 
		echo $end_result;	
	}

}
else{
	if (isset($_POST['search'])) {
	
		$firefox = "places.sqlite";
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

		

		
		
		if($isTitle=="1"){
			foreach($word2 as $value) {
				if(stringBeginsWith($value,"-")){
					array_push($whereTitle, "(moz_places.title not	LIKE '%" . substr($value, 1) . "%')");
				}
				else{
					array_push($whereTitle, "(moz_places.title 	LIKE '%" . $value . "%')");
				}
			}
		}
		if($isUrl=="1"){
			foreach($word2 as $value) {
				if(stringBeginsWith($value,"-")){
					array_push($whereURL, "(moz_places.url not	LIKE '%" . substr($value, 1) . "%')");
				}
				else{
					array_push($whereURL, "(moz_places.url 	LIKE '%" . $value . "%')");
				}
			}
		}

		if($isTitle==1){
			$whereTitle = " (" . implode(' and ', $whereTitle) . ") ";
		}
		if($isUrl==1){
			$whereURL = " (" . implode(' and ', $whereURL) . ") ";
		}
		
		$where[0] = $whereURL;
		$where[1] = $whereTitle;
		$whereAll = implode(' or ', array_filter($where));
		
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
			foreach($word2 as $bold){
				$displayUrl = str_ireplace($bold, ('<span class="found">' . $bold . '</span>'), $displayUrl);
			}
			foreach($word2 as $bold){
				$displayTitle = str_ireplace($bold, ('<span class="found">' . $bold . '</span>'), $displayTitle);
			}
			
			if($r['title']==""){
				$url = $url . $displayUrl . "</a>";
			}
			else{
				$url = $url . $displayTitle . "</a>";
			}

			

			if($r['favicon']==""){
				$favicon = '<img src="favicon.ico' . '" />';
			}
			else{
				$favicon = '<img width="16" height="16" src="data:image/x-icon;base64,' . base64_encode( $r['favicon'] ) . '" />';
			}

			$end_result     .= 
			'<div id="'. $r['id'] . '">' .
				'<li>' . 
					'<img src="search_ico.PNG" onclick="showInfo(' . "'" . $r['id'] . "'" . ')" >' .
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
		}
		echo "$numrows results returned"; 
		echo $end_result;
	}
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
function my_filter($item)
{
    //return !empty($item); // Will discard 0, 0.0, '0', '', NULL, array() of FALSE
    //return !is_null($item); // Will only discard NULL
    return $item != "" && $item !== NULL; // Discards empty strings and NULL
}
function my_join($array)
{
    return implode(' or ',array_filter($array,"my_filter"));
} 
function stringBeginsWith($haystack, $beginning, $caseInsensitivity = false)
{
    if ($caseInsensitivity)
        return strncasecmp($haystack, $beginning, strlen($beginning)) == 0;
    else
        return strncmp($haystack, $beginning, strlen($beginning)) == 0;
}

function stringEndsWith($haystack, $ending, $caseInsensitivity = false)
{
    if ($caseInsensitivity)
        return strcasecmp(substr($haystack, strlen($haystack) - strlen($ending)), $haystack) == 0;
    else
        return strpos($haystack, $ending, strlen($haystack) - strlen($ending)) !== false;
}



/* Previous bolding PHP
			
	$bold           = '<span class="found">' . $word . '</span>';
	if($r['title']==""){
	$url = "<a href='" . $r['url'] . "'>" . str_ireplace($word, $bold, $r['url']) . "</a>";	
	}
	else{
	$url = "<a href='" . $r['url'] . "'>" . str_ireplace($word, $bold, $r['title']) . "</a>";	
	}
*/
?>

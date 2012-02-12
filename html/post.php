<?php

$chrome = sqlite_escape_string($_POST['chrome']);
#foreach ($_POST as $key => $value ){
#echo "<br>$key is checked";
#}
if ($chrome == "1") {
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if (isset($_POST['search'])) {	
		$chromefavicons = "Favicons";
		$chromehistory = "History";
	
		if(file_exists($chromefavicons) && file_exists($chromehistory)){
			$db = new PDO('sqlite:'.$chromehistory);
			$db2 = new PDO('sqlite:'.$chromefavicons);
		}	
		else{ 
			echo "Make sure ".$chromehistory." and ". $chromefavicons ." are present.<br/>";
			echo "In Windows7 you can find this at C:\Users\**username**\AppData\Local\Google\Chrome\User Data\Default <br/>";
			echo "If using Chromium, C:\Users\**username**\AppData\Local\Chromium\User Data\Default <br/>";
			exit();	
		}
	
		//DB

		//INPUTS
		$isTitle = sqlite_escape_string($_POST['isTitle']);
		$isUrl = sqlite_escape_string($_POST['isUrl']);
		$isHidden = sqlite_escape_string($_POST['isHidden']);
		$orderBy = sqlite_escape_string($_POST['orderBy']);
		$word = sqlite_escape_string($_POST['search']);
		//VARIABLES
		$end_result = '';
		$numrows = 0;
		$checked = array();
		if($isTitle=="1"){
			array_push($checked, "urls.title 	LIKE '%" . $word . "%'");
		}
		if($isUrl=="1"){
			array_push($checked, "urls.url 	LIKE '%" . $word . "%'");
		}
		if($isHidden=="1"){
			$isHidden="%";
		}else{$isHidden="0";}
		$where = implode(' or ', $checked);
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
			and (" . $where . ") GROUP BY urls.id " .
		"ORDER BY " . $orderBy ." DESC " );
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
			
			$bold = '<span class="found">' . $word . '</span>';
			if($r['title']==""){
			$url = "<a href='" . $r['url'] . "'>" . str_ireplace($word, $bold, $r['url']) . "</a>";	
			}
			else{
			$url = "<a href='" . $r['url'] . "'>" . str_ireplace($word, $bold, $r['title']) . "</a>";	
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

		//INPUT
		$isTitle = sqlite_escape_string($_POST['isTitle']);
		$isUrl = sqlite_escape_string($_POST['isUrl']);
		$isHidden = sqlite_escape_string($_POST['isHidden']);
		$orderBy = sqlite_escape_string($_POST['orderBy']);
		$word = sqlite_escape_string($_POST['search']);
		//VARIABLES
		$checked = array();
		$numrows = 0;
		$end_result = '';
		if($isTitle=="1"){
			array_push($checked, "moz_places.title 	LIKE '%" . $word . "%'");
		}
		if($isUrl=="1"){
			array_push($checked, "moz_places.url 	LIKE '%" . $word . "%'");
		}
		if($isHidden=="1"){
			$isHidden="%";
		}else{
			$isHidden="0";
		}
		$where = implode(' or ', $checked);

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
		"' and (" . $where . ") GROUP BY moz_places.id " .
		"ORDER BY " . $orderBy ." DESC " );
		$row->execute();
		foreach($row as $r) {
			$numrows++;
			$bold           = '<span class="found">' . $word . '</span>';

			if($r['title']==""){
			$url = "<a href='" . $r['url'] . "'>" . str_ireplace($word, $bold, $r['url']) . "</a>";	
			}
			else{
			$url = "<a href='" . $r['url'] . "'>" . str_ireplace($word, $bold, $r['title']) . "</a>";	
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

?>

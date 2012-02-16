<?php

	function getSyntaxedWhere($column){
		$temp = 0;
		$whereURL = array();
		$whereORURL = array();
		$whereORTitle = array();
		$tempArray = array();
		$searchArray = array();
		foreach($searchArray as $value) {
			if($value == "OR"){
				$temp = 1;continue;
			}
			//If term isn't preceded by OR, push to where array.
			if($temp == 0){
				//Exclude term if preceded by "-"
				if(stringBeginsWith($value,"-")){
					array_push($whereURL, "($column not	LIKE '%" . substr($value, 1) . "%')");
				}else{
					array_push($whereURL, "($column 	LIKE '%" . $value . "%')");
				}	
			//Else push to whereOR array
			}else{
				//Exclude term if preceded by "-"
				if(stringBeginsWith($value,"-")){
					array_push($whereORURL, "($column not	LIKE '%" . substr($value, 1) . "%')");
				}else{
					array_push($whereORURL, "($column 	LIKE '%" . $value . "%')");
				}	
				$temp = 0;
			}		
		}
		$whereURL = " (" . implode(' and ', $whereURL) . ") ";
		$whereORURL = " (" . implode(' or ', $whereORURL) . ") ";
		$tempArray[0] = $whereURL; $tempArray[1] = $whereORURL;
		return " (" . my_join($tempArray, " and ") . ") ";
	}

	function my_filter($item){
		//return !empty($item); // Will discard 0, 0.0, '0', '', NULL, array() of FALSE
		//return !is_null($item); // Will only discard NULL
		return $item != "" && $item !== NULL && $item !== " () " && $item !== "()" && $item !== array(); // Discards empty strings and NULL
	}
	
	function my_join($array, $string){
		return implode($string , array_filter($array,"my_filter"));
	} 

	function stringBeginsWith($haystack, $beginning, $caseInsensitivity = false){
		if ($caseInsensitivity)
			return strncasecmp($haystack, $beginning, strlen($beginning)) == 0;
		else
			return strncmp($haystack, $beginning, strlen($beginning)) == 0;
	}

	function stringEndsWith($haystack, $ending, $caseInsensitivity = false){
		if ($caseInsensitivity)
			return strcasecmp(substr($haystack, strlen($haystack) - strlen($ending)), $haystack) == 0;
		else
			return strpos($haystack, $ending, strlen($haystack) - strlen($ending)) !== false;
	}
	
	function chromeFavicon($url, $db2){
		//Favicons
		$rowIcon = $db2->prepare("
		SELECT
			favicons.image_data favicon, 
			icon_mapping.page_url page_url, 
			favicons.id, 
			icon_mapping.id, 
			icon_mapping.icon_id 
		FROM favicons 
		join icon_mapping on icon_mapping.icon_id = favicons.id
		where icon_mapping.page_url = '" . sqlite_escape_string($url) . "'");
		$rowIcon->execute();
		$favicon = '<img src="img/favicon.ico' . '" />';
		foreach($rowIcon as $ir) {
			if($ir['favicon']==""){
				$favicon = '<img src="img/favicon.ico' . '" />';
			}
			else{
				$favicon = '<img width="16" height="16" src="data:image/x-icon;base64,' . base64_encode( $ir['favicon'] ) . '" />';
			}
		}
		return $favicon;
	}
	
	function chromeDates($id, $db){
		$dates = array();
			$allDates = $db->query("
				SELECT distinct 
					urls.id,
					urls.url,
					urls.hidden,
					urls.typed_count,
					visits.from_visit,
					datetime((visits.visit_time - 11644473600000000)/ 1000000,'unixepoch') date

				FROM urls 
					left join visits 
					on urls.id = visits.url
				WHERE visits.url = '" . $id . "' ORDER BY date DESC");	
		foreach($allDates as $r) {
			array_push($dates, $r['date']);
		}
		return $dates;
	}

	function chromeTo($id, $db, $db2){
		$end_result = array();
			$row = $db->query("
				SELECT distinct
					v1.url,
					v2.url,
					v1.visit_time,
					urls.url TOURL,
					urls.title TOTITLE
				FROM visits v1
				join visits v2 on v1.id = v2.from_visit
				join urls on v2.url = urls.id
				WHERE v1.url = '" . $id . "' ORDER BY TOTITLE DESC");
		$countr = 0 ;
		foreach($row as $r) {
				$url = $r['TOURL'];
				if($r['TOTITLE']==""){
					$title = $r['TOURL'];	
				}
				else{
					$title = $r['TOTITLE'];	
				}
				/////////////GET FAVICON///////////////////////////////////////////////////////
				$favicon = chromeFavicon($r['TOURL'], $db2);
				///////////////////////////////////////////////////////////////////////////////////
				array_push($end_result, ($url . "|split|" . $title . "|split|" . $favicon) );
				$countr++;
		}
		return $end_result;
	}
	

	
	function chromeFrom($id, $db, $db2){
		$end_result = array();
			$row = $db->query("
				SELECT distinct
					v1.url,
					v2.url,
					v1.visit_time,
					urls.url TOURL,
					urls.title TOTITLE
				FROM visits v1
				join visits v2 on v1.id = v2.from_visit
				join urls on v1.url = urls.id
				WHERE v2.url = '" . $id . "' ORDER BY TOTITLE DESC");
		$countr = 0 ;
		foreach($row as $r) {
				$url = $r['TOURL'];
				if($r['TOTITLE']==""){
					$title = $r['TOURL'];	
				}
				else{
					$title = $r['TOTITLE'];	
				}
				/////////////GET FAVICON///////////////////////////////////////////////////////
				$favicon = chromeFavicon($r['TOURL'], $db2);
				///////////////////////////////////////////////////////////////////////////////////
				array_push($end_result, ($url . "|split|" . $title . "|split|" . $favicon) );
				$countr++;
		}
		return $end_result;
	}
	
	function firefoxDates($id, $db){
		$dates = array();
		$allDates = $db->query("
			SELECT distinct 
				moz_places.id,
				moz_places.url,
				moz_places.hidden,
				moz_places.typed,
				moz_historyvisits.visit_type,
				datetime(moz_historyvisits.visit_date/1000000,'unixepoch') date
			FROM moz_places 
				left join moz_historyvisits 
				on moz_places.id = moz_historyvisits.place_id
			WHERE moz_places.id = '".$id."' ORDER BY date DESC" );
		foreach($allDates as $r) {
			array_push($dates, $r['date']);
		}
		return $dates;
	}
	
	function firefoxFrom($id, $db){
		$end_result = array();
		$row = $db->query(" select  pb.url URL, pb.title TITLE, hb.visit_date, hb.visit_type, fb.data favicon
		from moz_historyvisits ha
		join moz_historyvisits hb on ha.from_visit = hb.id
		join moz_places pa on pa.id = ha.place_id
		join moz_places pb on pb.id = hb.place_id
		join moz_favicons fb on pb.favicon_id = fb.id 
		WHERE pa.id = '" . $id . "' ORDER BY TITLE DESC ");
		$countr = 0 ;
		foreach($row as $r) {
				$url = $r['URL'];
				if($r['TITLE']==""){
					$title = $r['URL'];	
				}
				else{
					$title = $r['TITLE'];	
				}
				if($r['favicon']==""){
					$favicon = '<img src="img/favicon.ico' . '" />';
				}
				else{
					$favicon = '<img src="data:image/x-icon;base64,' . base64_encode( $r['favicon'] ) . '" />';
				}
				array_push($end_result, ($url . "|split|" . $title . "|split|" . $favicon) );
				$countr++;
		}
		return $end_result;
	}
	
	
	function firefoxTo($id, $db){
		$end_result = array();
		$row = $db->query(" select  pb.url URL, pb.title TITLE, hb.visit_date, hb.visit_type, fb.data favicon
		from moz_historyvisits ha
		join moz_historyvisits hb on ha.id = hb.from_visit
		join moz_places pa on pa.id = ha.place_id
		join moz_places pb on pb.id = hb.place_id
		join moz_favicons fb on pb.favicon_id = fb.id 
		WHERE pa.id = '" . $id . "' ORDER BY TITLE DESC ");
		$countr = 0 ;
		foreach($row as $r) {
				$url = $r['URL'];
				if($r['TITLE']==""){
					$title = $r['URL'];	
				}
				else{
					$title = $r['TITLE'];	
				}
				if($r['favicon']==""){
					$favicon = '<img src="img/favicon.ico' . '" />';
				}
				else{
					$favicon = '<img src="data:image/x-icon;base64,' . base64_encode( $r['favicon'] ) . '" />';
				}
				array_push($end_result, ($url . "|split|" . $title . "|split|" . $favicon) );
				$countr++;
		}
		return $end_result;
	}
	

	/*Old
	
	function chromeTo($url, $db, $db2){
			$endResult = '';
			$raw = $db->query("
			SELECT distinct
				v1.url,
				v2.url,
				v1.visit_time,
				urls.url TOURL,
				urls.title TOTITLE
			FROM visits v1
			join visits v2 on v1.id = v2.from_visit
			join urls on v2.url = urls.id
			WHERE v1.url = '" . $url . "' ORDER BY TOTITLE DESC");
			$countr = 0;
			foreach($raw as $r) {
				if($countr == 0){$end_result = '<b>Visited</b><br />';$countr++;echo $end_result;}
				/////////////GET FAVICON///////////////////////////////////////////////////////
				$favicon = chromeFavicon($r['TOURL'], $db2);
				///////////////////////////////////////////////////////////////////////////////////
				if($r['TOTITLE']==""){
					$url = "<a href='" . $r['TOURL'] . "'>" . $r['TOURL'] . "</a>";	
				}
				else{
					$url = "<a href='" . $r['TOURL'] . "'>" . $r['TOTITLE'] . "</a>";	
				}
				$endResult .= ( $favicon . $url. "<br />");	
			}
			return $endResult;
	}
	
	function chromeFrom($url, $db, $db2){
			$endResult = '';
			$raw = $db->query("
				SELECT distinct
					v1.url,
					v2.url,
					v1.visit_time,
					urls.url TOURL,
					urls.title TOTITLE
				FROM visits v1
				join visits v2 on v1.id = v2.from_visit
				join urls on v1.url = urls.id
				WHERE v2.url = '" . $url . "' ORDER BY TOTITLE DESC");
			$countr = 0;
			foreach($raw as $r) {
				if($countr == 0){$end_result = '<b>From</b><br />';$countr++;echo $end_result;}
				/////////////GET FAVICON///////////////////////////////////////////////////////
				$favicon = chromeFavicon($r['TOURL'], $db2);
				///////////////////////////////////////////////////////////////////////////////////
				if($r['TOTITLE']==""){
					$url = "<a href='" . $r['TOURL'] . "'>" . $r['TOURL'] . "</a>";	
				}
				else{
					$url = "<a href='" . $r['TOURL'] . "'>" . $r['TOTITLE'] . "</a>";	
				}
				$endResult .= ( $favicon . $url. "<br />");	
			}
			return $endResult;
	}
	
	function chromeDatesOld($url, $db){
			$allDates = $db->query("
				SELECT distinct 
					urls.id,
					urls.url,
					urls.hidden,
					urls.typed_count,
					visits.from_visit,
					datetime((visits.visit_time - 11644473600000000)/ 1000000,'unixepoch') date

				FROM urls 
					left join visits 
					on urls.id = visits.url
				WHERE visits.url = '" . $url . "' ORDER BY date DESC");	
		$endResult = '<b>On dates</b><br /> ';
		foreach($allDates as $r) {
			$endResult .= ( $r['date'] . "<br />");
		}
		return $endResult;
	}
	
	
	function firefoxDatesOld($id, $db){
		$allDates = $db->query("
			SELECT distinct 
				moz_places.id,
				moz_places.url,
				moz_places.hidden,
				moz_places.typed,
				moz_historyvisits.visit_type,
				datetime(moz_historyvisits.visit_date/1000000,'unixepoch') date
			FROM moz_places 
				left join moz_historyvisits 
				on moz_places.id = moz_historyvisits.place_id
			WHERE moz_places.id = '".$id."' ORDER BY date DESC" );
		$endResult = '<b>On dates</b><br /> ';
		foreach($allDates as $r) {
			$endResult .= ( $r['date'] . "<br />");
		}
		return $endResult;
	}
		function firefoxFromOld($id, $db){
		$end_result = '';
		$row = $db->query(" select  pb.url URL, pb.title TITLE, hb.visit_date, hb.visit_type, fb.data favicon
		from moz_historyvisits ha
		join moz_historyvisits hb on ha.from_visit = hb.id
		join moz_places pa on pa.id = ha.place_id
		join moz_places pb on pb.id = hb.place_id
		join moz_favicons fb on pb.favicon_id = fb.id 
		WHERE pa.id = '" . $id . "' ORDER BY TITLE DESC ");
		$countr = 0 ;
		foreach($row as $r) {
				if($countr == 0){$end_result = '<b>From</b><br />';$countr++;}

				if($r['TITLE']==""){
					$url = "<a href='" . $r['URL'] . "'>" . $r['URL'] . "</a>";	
				}
				else{
					$url = "<a href='" . $r['URL'] . "'>" . $r['TITLE'] . "</a>";	
				}

				if($r['favicon']==""){
					$favicon = '<img src="favicon.ico' . '" />';
				}
				else{
					$favicon = '<img src="data:image/x-icon;base64,' . base64_encode( $r['favicon'] ) . '" />';
				}
				$end_result .= ( $favicon . $url. "<br />" );
		}
		return $end_result;
	}
	
	function firefoxToOld($id,$db){
		$end_result = '';
		$row = $db->query(" select  pb.url URL, pb.title TITLE, hb.visit_date, hb.visit_type, fb.data favicon
		from moz_historyvisits ha
		join moz_historyvisits hb on ha.id = hb.from_visit
		join moz_places pa on pa.id = ha.place_id
		join moz_places pb on pb.id = hb.place_id
		join moz_favicons fb on pb.favicon_id = fb.id 
		WHERE pa.id = '" . $id . "' ORDER BY TITLE DESC ");
		$countr = 0 ;
		foreach($row as $r) {
				if($countr == 0){$end_result = '<b>To</b><br />';$countr++;}

				if($r['TITLE']==""){
					$url = "<a href='" . $r['URL'] . "'>" . $r['URL'] . "</a>";	
				}
				else{
					$url = "<a href='" . $r['URL'] . "'>" . $r['TITLE'] . "</a>";	
				}

				if($r['favicon']==""){
					$favicon = '<img src="favicon.ico' . '" />';
				}
				else{
					$favicon = '<img src="data:image/x-icon;base64,' . base64_encode( $r['favicon'] ) . '" />';
				}
				$end_result .= ( $favicon . $url. "<br />" );
		}
		return $end_result;
	}
	*/
	
	
	
?>
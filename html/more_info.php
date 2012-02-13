<?php
$chrome = sqlite_escape_string($_GET["chrome"]);
if ($chrome == "1") {
///////////////////////////////CHROME///////////////////////////////////////////////////////////////
	if (isset($_GET["q"])) {
		$db = new PDO('sqlite:History');
		$db2 = new PDO('sqlite:Favicons');
		$q = sqlite_escape_string($_GET["q"]);
		
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
			WHERE visits.url = '" . $q . "' ORDER BY date DESC");	

		print('<b>On dates</b><br /> ');
		foreach($allDates as $r) {
			print($r['date'] . "<br />");
		}
		

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
			WHERE v2.url = '" . $q . "' ORDER BY TOTITLE DESC");
			$countr = 0;
		foreach($row as $r) {
			if($countr == 0){$end_result = '<b>From</b><br />';$countr++;echo $end_result;}
			/////////////GET FAVICON///////////////////////////////////////////////////////
			$favicon = '<img src="favicon.ico' . '" />';
			$rowIcon = $db2->prepare("
			SELECT
			favicons.image_data favicon, icon_mapping.page_url page_url, favicons.id, icon_mapping.id, icon_mapping.icon_id FROM favicons join
			icon_mapping on icon_mapping.icon_id = favicons.id
			where icon_mapping.page_url = '" . sqlite_escape_string($r['TOURL']) . "'");
			$rowIcon->execute();
			foreach($rowIcon as $ir) {//$icon = $ir['favicon'];
				if($ir['favicon']==""){
					$favicon = '<img src="favicon.ico' . '" />';
				}
				else{
					$favicon = '<img width="16" height="16" src="data:image/x-icon;base64,' . base64_encode( $ir['favicon'] ) . '" />';
				}	
			}
			///////////////////////////////////////////////////////////////////////////////////
			if($r['TOTITLE']==""){
				$url = "<a href='" . $r['TOURL'] . "'>" . $r['TOURL'] . "</a>";	
			}
			else{
				$url = "<a href='" . $r['TOURL'] . "'>" . $r['TOTITLE'] . "</a>";	
			}

			echo ( 
			$favicon .
			$url.
			"<br />");
		}							

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
		WHERE v1.url = '" . $q . "' ORDER BY TOTITLE DESC");
			$countr = 0;
			foreach($raw as $r) {
				if($countr == 0){$end_result = '<b>Visited</b><br />';$countr++;echo $end_result;}
				/////////////GET FAVICON///////////////////////////////////////////////////////
				$favicon = '<img src="favicon.ico' . '" />';
				$rowIcon = $db2->prepare("
					SELECT
						favicons.image_data favicon,
						icon_mapping.page_url page_url,
						favicons.id, icon_mapping.id,
						icon_mapping.icon_id 
					FROM favicons join
						icon_mapping on icon_mapping.icon_id = favicons.id
					WHERE icon_mapping.page_url = '" . sqlite_escape_string($r['TOURL']) . "'");
				$rowIcon->execute();
				foreach($rowIcon as $ir) {
					if($ir['favicon']==""){
						$favicon = '<img src="favicon.ico' . '" />';
					}
					else{
						$favicon = '<img width="16" height="16" src="data:image/x-icon;base64,' . base64_encode( $ir['favicon'] ) . '" />';
					}	
				}
				///////////////////////////////////////////////////////////////////////////////////
				if($r['TOTITLE']==""){
					$url = "<a href='" . $r['TOURL'] . "'>" . $r['TOURL'] . "</a>";	
				}
				else{
					$url = "<a href='" . $r['TOURL'] . "'>" . $r['TOTITLE'] . "</a>";	
				}
				print( $favicon . $url. "<br />");	
			}				
	}
}
////////////////////////////////FIREFOX/////////////////////////////////////////////////
else{
	if (isset($_GET["q"])) {
		$db = new PDO('sqlite:places.sqlite');
		$q = sqlite_escape_string($_GET["q"]);
		
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
			WHERE moz_places.id = '".$q."' ORDER BY date DESC" );
		print('<b>On dates</b><br /> ');
		foreach($allDates as $r) {
			print( $r['date'] . "<br />");
		}

		
		$row = $db->query(" select  pb.url URL, pb.title TITLE, hb.visit_date, hb.visit_type, fb.data favicon
		from moz_historyvisits ha
		join moz_historyvisits hb on ha.id = hb.from_visit
		join moz_places pa on pa.id = ha.place_id
		join moz_places pb on pb.id = hb.place_id
		join moz_favicons fb on pb.favicon_id = fb.id 
		WHERE pa.id = '" . $q . "' ORDER BY TITLE DESC ");
		$countr = 0 ;
		foreach($row as $r) {
				if($countr == 0){$end_result = '<b>Visited</b><br />';$countr++;echo $end_result;}

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
				print( $favicon . $url. "<br />" );
		}
		
		$row = $db->query(" select  pb.url URL, pb.title TITLE, hb.visit_date, hb.visit_type, fb.data favicon
		from moz_historyvisits ha
		join moz_historyvisits hb on ha.from_visit = hb.id
		join moz_places pa on pa.id = ha.place_id
		join moz_places pb on pb.id = hb.place_id
		join moz_favicons fb on pb.favicon_id = fb.id 
		WHERE pa.id = '" . $q . "' ORDER BY TITLE DESC ");
		$countr = 0 ;
		foreach($row as $r) {
				if($countr == 0){$end_result = '<b>From</b><br />';$countr++;echo $end_result;}

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
				print( $favicon . $url. "<br />" );
		}
		
	}
}
?>

<?PHP
/*Storage*/
/*
		$row = $db->query("	SELECT distinct
				moz_places.url TOURL,
				moz_places.title TOTITLE,
				place2.url FROMURL,
				place2.title FROMTITLE,
				moz_favicons.data favicon
			FROM moz_places 
				left join moz_historyvisits hist1
				on moz_places.id = hist1.place_id
				left join moz_historyvisits hist2 
				on hist1.from_visit = hist2.id
				join moz_places place2
				on hist2.place_id = place2.id
				join moz_favicons
				on moz_places.favicon_id = moz_favicons.id
			WHERE place2.id = '" . $q . "' ORDER BY TOTITLE DESC");
*/
?>

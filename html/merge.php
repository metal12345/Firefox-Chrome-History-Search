<?php

 try
 {
	#Initilize
	set_time_limit(12000);
	$db = new PDO('sqlite:places.sqlite');
	$db2 = new PDO('sqlite:places2.sqlite');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$intID=0;
	$intIDtemp=0;
	$intFAV=0;
	$intHISTORY=0;
	
	####Get max places.sqlite ID
	$resultMaxID = $db->query('SELECT MAX(moz_places.ID) FROM moz_places'); errDB($db);
	foreach($resultMaxID as $rowMaxID)
	{
		$intID = $rowMaxID['MAX(moz_places.ID)'];
		$intIDtemp = $intID;
	}
	
	####Get max places.sqlite favicon ID
	$resultMaxFavID = $db->query('SELECT MAX(moz_favicons.ID) FROM moz_favicons'); errDB($db);
	foreach($resultMaxFavID as $rowMaxFavID)
	{
		$intFAV = $rowMaxFavID['MAX(moz_favicons.ID)'];
	}
	
	$resultMaxHistoryID = $db->query('SELECT MAX(moz_historyvisits.ID) FROM moz_historyvisits'); errDB($db);
	foreach($resultMaxHistoryID as $rowMaxHistoryID)
	{
		$intHISTORY = $rowMaxHistoryID['MAX(moz_historyvisits.ID)'];
	}
	
	#####If GUID column is non existant (pre-firefox3?) create it to keep compatibility
	$resultAddColumn2DB1 = $db->query('ALTER TABLE main.moz_places ADD COLUMN last_visit_date INTEGER');
	$resultAddColumn2DB2 = $db2->query('ALTER TABLE main.moz_places ADD COLUMN last_visit_date INTEGER');
	$resultAddColumnDB1 = $db->query('ALTER TABLE main.moz_places ADD COLUMN guid TEXT');
	$resultAddColumnDB2 = $db2->query('ALTER TABLE main.moz_places ADD COLUMN guid TEXT');
	
	########For everything in places2.sqlite
	$resultSelectDB2 = $db2->query('SELECT * FROM moz_places');
	foreach($resultSelectDB2 as $rowSelectDB2) 
	{
		echo "<br /><hr /><br />";
		###########compare the title with places.sqlite
		$resultCountID = $db->query('SELECT count(moz_places.ID) FROM moz_places where moz_places.url = "' . $rowSelectDB2['url'] . '"');
		foreach($resultCountID as $rowCountID)	
		{
			$countID = $rowCountID['count(moz_places.ID)'];
			echo "<br />^^count:" . $countID . "row2id:" . $rowSelectDB2['id'] . "^^<br />";
			############if the title doesn't exists in places.sqlite, insert record from sqlite2 where ID = intID++ 
			if($countID==0){   
				$intID++;	
				$uniqueGUID = uniqid(("!fp" . $rowSelectDB2['id'] . "!"), false);
				$resultAddRecord = $db->prepare("INSERT INTO moz_places VALUES (?,?,?,?,?,?,?,?,?,?,?)");
				$resultAddRecord->bindParam(1, $intID, PDO::PARAM_INT);
				$resultAddRecord->bindParam(2, $rowSelectDB2['url'], PDO::PARAM_STR);
				$resultAddRecord->bindParam(3, $rowSelectDB2['title'], PDO::PARAM_STR);
				$resultAddRecord->bindParam(4, $rowSelectDB2['rev_host'], PDO::PARAM_STR);
				$resultAddRecord->bindParam(5, $rowSelectDB2['visit_count'], PDO::PARAM_INT);
				$resultAddRecord->bindParam(6, $rowSelectDB2['hidden'], PDO::PARAM_INT);
				$resultAddRecord->bindParam(7, $rowSelectDB2['typed'], PDO::PARAM_INT);
				$resultAddRecord->bindParam(8, $rowSelectDB2['favicon_id'], PDO::PARAM_INT);
				$resultAddRecord->bindParam(9, $rowSelectDB2['frecency'], PDO::PARAM_INT);
				$resultAddRecord->bindParam(10, $rowSelectDB2['last_visit_date'], PDO::PARAM_INT);
				$resultAddRecord->bindParam(11, $uniqueGUID, PDO::PARAM_STR);//GUID - Don't know how firefox gens this so I gen my own with a !fp!fromID prefix.
				$resultAddRecord->execute();
				echo "||" . $rowSelectDB2['id'] . " >>> " . $intID . "||<br />";
				
				#########search current sqlite2 ID for a favicon
				$resultFavDB2 = $db2->query('SELECT * FROM moz_favicons where moz_favicons.id = "' . $rowSelectDB2['favicon_id'] . '"');
				echo ('SELECT * FROM moz_favicons where moz_favicons.id = "' . $rowSelectDB2['favicon_id'] . '"');
				foreach($resultFavDB2 as $rowFavDB2){
					########If found 1 favicon...
					if($db2->query('SELECT * FROM moz_favicons where moz_favicons.id = "' . $rowSelectDB2['favicon_id'] . '"')->fetchColumn() > 0){ echo "found 1 icon <br />";
					
						##########search sqlite1 for the URL of the found favicon
						$resultsFoundIcon = $db->query('SELECT count(moz_favicons.id), id FROM moz_favicons where moz_favicons.url = "' . $rowFavDB2['url'] . '"');
						foreach($resultsFoundIcon as $rowFoundIcon)
						{
							##########If didn't find the URL of the icon in sqlite1...
							######insert the icon into sqlite1
							if($rowFoundIcon['count(moz_favicons.id)']==0){
								$intFAV++;
								echo "insert " . parseNull($rowFavDB2['url']) . " to " . $intFAV ;
								$resultAddIcon = $db->prepare("INSERT INTO moz_favicons VALUES (?,?,?,?,?)");
								$resultAddIcon->bindParam(1, $intFAV, PDO::PARAM_INT);
								$resultAddIcon->bindParam(2, $rowFavDB2['url'], PDO::PARAM_STR);
								$resultAddIcon->bindParam(3, $rowFavDB2['data'], PDO::PARAM_LOB);
								$resultAddIcon->bindParam(4, $rowFavDB2['mime_type'], PDO::PARAM_STR);
								$resultAddIcon->bindParam(5, $rowFavDB2['expiration'], PDO::PARAM_STR);
								$resultAddIcon->execute();			
								$db->query("UPDATE moz_places set favicon_id = '" . $intFAV . "' WHERE id = '" . $intID . "'");
								echo ("We didn't find an icon for newly inserted record ($intID) so we inserted its favicon at ($intFAV)");
							}
							##########If found the URL in sqlite1...
							######update moz_places.favicon_id to the proper ID where moz_places.id = $intID
							else{
								$db->query("UPDATE moz_places set favicon_id = '" . $rowFoundIcon['id'] . "' WHERE id = '" . $intID . "'");
								#print("UPDATE moz_places set favicon_id = '" . $rowFoundIcon['id'] . "' WHERE id = '" . $intID . "'" . "<br />");
								echo ("Look in sqlite2.favicon_id=".$rowSelectDB2['favicon_id']." we find take the URL and find where the favicon URL is in sqlite1 ->>".$rowFavDB2['url']."  so the record we're on ($intID) sets favicon_ID to " . $rowFoundIcon['id']);
							}//endif
							
						}//endfor 
					}else{echo "<br />didn't find 1 icon <br />";} //endif
				}//endfor	
			}//endif
		}//endfor
    }
	
	
	$placesResults = $db2->query("
	SELECT
		moz_historyvisits.id,
		moz_historyvisits.visit_date,
		moz_historyvisits.visit_type,
		placesfrom.url FROMURL,
		placesto.url TOURL

	FROM moz_historyvisits 
		INNER JOIN moz_places placesfrom on placesfrom.id = moz_historyvisits.from_visit
		INNER JOIN moz_places placesto on placesto.id = moz_historyvisits.place_id
	");
	
	########################This is done wrong, from_visit is the key to ID in moz_historyvisits############################
	foreach($placesResults as $rowPlaces) {
		$intHISTORY++;
		$session = "-1337";
		echo $rowPlaces['FROMURL'] . " !TO! ";
		echo $rowPlaces['TOURL'] . "<br />";
		
		#$resultFrom = $db->prepare("SELECT moz_historyvisits.id FROM moz_historyvisits where moz_historyvisits.from_visit = ? ");
		#$resultFrom->bindParam(1, $placesResults['id'], PDO::PARAM_STR);
		#$resultFrom->execute();
		#$rowFrom = $resultFrom->fetchColumn(0);
		
		$resultTo = $db->prepare("SELECT moz_places.id FROM moz_places where moz_places.url = ? ");
		$resultTo->bindParam(1, $rowPlaces['TOURL'], PDO::PARAM_STR);
		$resultTo->execute();
		$rowTo = $resultTo->fetchColumn(0);
		
		echo "WTF";
		echo $rowTo;

		
		$resultInsertHistory = $db->prepare("INSERT INTO moz_historyvisits VALUES (?,?,?,?,?,?)");
		$resultInsertHistory->bindParam(1, $intHISTORY, PDO::PARAM_INT);
		##$resultInsertHistory->bindParam(2, $rowFrom, PDO::PARAM_INT);
		$resultInsertHistory->bindParam(2, $session, PDO::PARAM_INT);
		$resultInsertHistory->bindParam(3, $rowTo, PDO::PARAM_INT);
		$resultInsertHistory->bindParam(4, $rowPlaces['visit_date'], PDO::PARAM_STR);
		$resultInsertHistory->bindParam(5, $rowPlaces['visit_type'], PDO::PARAM_INT);
		$resultInsertHistory->bindParam(6, $session, PDO::PARAM_INT);
		$resultInsertHistory->execute();
	}
	
	

################################# End 
echo "<br />CLOSED DB<br />";	# End
unset($db);						# End 
unset($db2);					# End 
$db = NULL;						# End 
$db2 = NULL;					# End 
################################# End 
}
catch(PDOException $e)
{
	print 'Exception : '.$e->getMessage();
	unset($db);
	unset($db2);
}

##################################
########### F u n c t i o n s ####
##################################
function parseNull($input){
	if($input == ""){return "null";}
	else{return "'".sqlite_escape_string($input)."'";}
}

function errDB($db){
	$array = ($db->errorInfo());
	if(empty($array) || $array[0] = "00000"){ 
		echo "";
	}
	else{
		print_r($array);
	}
}

##################################
########### S t o r a g e  #######
##################################
#$db2->query('UPDATE moz_places SET id = "' . $intID . '" where url = "' . $row2['url'] . '"');
#
#SELECT datetime(moz_historyvisits.visit_date/1000000,'unixepoch'), moz_places.url
#FROM moz_places, moz_historyvisits 
#WHERE moz_places.id = moz_historyvisits.place_id

/*
SELECT distinct 
moz_places.url, 
moz_places.title, 
moz_places.hidden,
moz_places.frecency, 
moz_places.visit_count,
--moz_historyvisits.visit_type,
moz_favicons.data favicon, 
datetime(moz_places.last_visit_date/1000000,'unixepoch') lastvisit, 
datetime(moz_historyvisits.visit_date/1000000,'unixepoch') date

FROM moz_places 
left join moz_historyvisits 
on moz_places.id = moz_historyvisits.place_id
left join moz_favicons 
on moz_places.favicon_id = moz_favicons.id

WHERE moz_places.hidden = '0'
order by lastvisit




SELECT 
moz_places.url TOURL,
moz_places.title TOTITLE,
place2.url FROMURL,
place2.title FROMTITLE

FROM moz_places 
left join moz_historyvisits hist1
on moz_places.id = hist1.place_id
left join moz_historyvisits hist2 
on hist1.from_visit = hist2.id
join moz_places place2
on hist2.place_id = place2.id

where place2.id = "1310" --Fill in ID to get where we went to


SELECT 
moz_places.url TOURL,
moz_places.title TOTIRLE,
place2.url FROMURL,
place2.title FROMTITLE

FROM moz_places 
left join moz_historyvisits hist1
on moz_places.id = hist1.place_id
left join moz_historyvisits hist2 
on hist1.from_visit = hist2.id
join moz_places place2
on hist2.place_id = place2.id

where moz_places.id = "1310" --Fill in ID to get where we came from




SELECT 
moz_places.title,
moz_inputhistory.input,
moz_inputhistory.use_count

FROM moz_places 
join moz_inputhistory
on moz_places.id = moz_inputhistory.place_id

where moz_places.id = '938' --Fill in this to get the input history for ID



SELECT 
 moz_bookmarks.id,
 moz_bookmarks.type,
 moz_bookmarks.parent,
 moz_bookmarks.position,
 moz_bookmarks.title,
 moz_keywords.keyword,
 moz_bookmarks.folder_type,
 moz_bookmarks.dateAdded,
 moz_bookmarks.lastModified
from moz_bookmarks
left join moz_keywords 
on moz_bookmarks.keyword_id = moz_keywords.id
where moz_bookmarks.parent = 1  --Fill in this to get the bookmarks from this parent

*/
?>



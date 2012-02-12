<?php   
 /* CAT:Line chart */
 
$db = new PDO('sqlite:../places.sqlite');
$row = $db->prepare("
SELECT COUNT(*) as num_visits, 
             strftime ('%Y', visit_date/1000/1000,'unixepoch','localtime') as year,
             strftime ('%m', visit_date/1000/1000,'unixepoch','localtime') as month,
             strftime('%d', visit_date/1000/1000, 'unixepoch', 'localtime') as day
FROM moz_historyvisits v LEFT JOIN moz_places h ON v.place_id = h.id 
WHERE v.visit_type NOT IN (0, 4) 
GROUP BY year,month,day ORDER BY year, month, day ASC");

//	$day[($r['day'])] = $r['num_visits'];
$day = array();
$month = array();
$count = 0;
$row->execute();
foreach($row as $r) {
	$day[$count] = $r['num_visits'];
	$month[$count] = $r['year'] . "." . $r['month'] . "." . $r['day'];
	$count++;
}

 /* pChart library inclusions */
 include("class/pData.class.php");
 include("class/pDraw.class.php");
 include("class/pImage.class.php");

 /* Create and populate the pData object */
 $MyData = new pData();  
 $MyData->addPoints($day,"hits");

 $MyData->setAxisName(0,"Hits");
 $MyData->addPoints($month,"Labels");
 $MyData->setSerieDescription("Labels","Months");
 $MyData->setAbscissa("Labels");
 $MyData->setAxisDisplay(0,AXIS_FORMAT_METRIC,1);


 /* Create the pChart object */
 $myPicture = new pImage(700,230,$MyData);

 /* Turn of Antialiasing */
 $myPicture->Antialias = FALSE;

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
 
 /* Write the chart title */ 
 $myPicture->setFontProperties(array("FontName"=>"fonts/Forgotte.ttf","FontSize"=>11));
 $myPicture->drawText(150,35,"Daily hits",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 $myPicture->setFontProperties(array("FontName"=>"fonts/pf_arma_five.ttf","FontSize"=>6));

 /* Define the chart area */
 $myPicture->setGraphArea(33,30,700,210);

 /* Draw the scale */
 $scaleSettings = array("LabelSkip"=>($count/12),"XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
 $myPicture->drawScale($scaleSettings);
 //"GridR"=>200,"GridG"=>200,"GridB"=>200

 /* Turn on Antialiasing */
 $myPicture->Antialias = TRUE;

 /* Draw the line chart */
 $myPicture->drawLineChart();

 /* Write the chart legend */
 $myPicture->drawLegend(540,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Render the picture (choose the best way) */
 $myPicture->Render("example5.png");   
  echo ("<img id='loadingIndicator' src='example5.png' alt='Loading...' />")
 
?>



<?php
$db = new PDO('sqlite:../places.sqlite');

$row = $db->prepare("
SELECT COUNT(*) as num_visits, 
              strftime('%m', visit_date/1000/1000, 'unixepoch', 'localtime') as month 
              FROM moz_historyvisits v LEFT JOIN moz_places h ON v.place_id = h.id 
              WHERE v.visit_type NOT IN (0, 4) 
              GROUP BY month ORDER BY month ASC");
$row->execute();
$hours['00']=0;$hours['01']=0;$hours['02']=0;$hours['03']=0;$hours['04']=0;$hours['05']=0;$hours['06']=0;$hours['07']=0;$hours['08']=0;$hours['09']=0;$hours[10]=0;$hours[11]=0;$hours[12]=0;
foreach($row as $r) {
	$hours[($r['month'])] = $r['num_visits'];
}


 /* CAT:Bar Chart */

 /* pChart library inclusions */
 

 /* Create and populate the pData object */
 $MyData = new pData();  
 $MyData->addPoints(array($hours['00'],$hours['01'],$hours['02'],$hours['03'],$hours['04'],$hours['05'],$hours['06'],$hours['07'],$hours['08'],$hours['09'],$hours[10],$hours[11],$hours[12]),"Visits per Month");
 $MyData->setAxisName(0,"Hits");
 $MyData->addPoints(array("January","February","March","April","May","June","July","August","September","October","November","December"),"Months");
 $MyData->setSerieDescription("Months");
 $MyData->setAbscissa("Months");
  $MyData->setAxisDisplay(0,AXIS_FORMAT_METRIC,1);

 /* Create the pChart object */
 $myPicture = new pImage(700,230,$MyData);

 /* Turn of Antialiasing */
 $myPicture->Antialias = FALSE;

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

 /* Set the default font */
 $myPicture->setFontProperties(array("FontName"=>"fonts/pf_arma_five.ttf","FontSize"=>7));

 /* Define the chart area */
 $myPicture->setGraphArea(30,40,700,200);

 /* Draw the scale */
 $scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
 $myPicture->drawScale($scaleSettings);

 /* Write the chart legend */
 $myPicture->drawLegend(580,12,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Turn on shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Draw the chart */
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 $settings = array("Gradient"=>TRUE,"GradientMode"=>GRADIENT_EFFECT_CAN,"DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"DisplayR"=>255,"DisplayG"=>255,"DisplayB"=>255,"DisplayShadow"=>TRUE,"Surrounding"=>10);
 
 
 $Config = array("DisplayValues"=>1, "AroundZero"=>1);
 $myPicture->drawBarChart($Config);
 
 $myPicture->drawText(150,35,"Monthly hits",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
 /* Render the picture (choose the best way) */
 $myPicture->Render("example1.png");   

echo ("<img id='loadingIndicator' src='example1.png' alt='Loading...' />")

?>

<?php
$db = new PDO('sqlite:../places.sqlite');

$row = $db->prepare("
SELECT COUNT(*) as num_visits, 
              strftime('%H', visit_date/1000/1000, 'unixepoch', 'localtime') as hour 
              FROM moz_historyvisits v LEFT JOIN moz_places h ON v.place_id = h.id 
              WHERE v.visit_type NOT IN (0, 4) 
              GROUP BY hour ORDER BY hour ASC");
$row->execute();

$hours = array();
$hours['00']=0;$hours['01']=0;$hours['02']=0;$hours['03']=0;$hours['04']=0;$hours['05']=0;$hours['06']=0;$hours['07']=0;$hours['08']=0;$hours['09']=0;$hours[10]=0;$hours[11]=0;$hours[12]=0;$hours[13]=0;$hours[14]=0;$hours[15]=0;$hours[16]=0;$hours[17]=0;$hours[18]=0;$hours[19]=0;$hours[20]=0;
foreach($row as $r) {
	$hours[($r['hour'])] = $r['num_visits'];
}

 /* Create and populate the pData object */
 $MyData = new pData();  
 $MyData->addPoints($hours,"Visits per Hour");
 $MyData->setAxisName(0,"Hits");
 $MyData->addPoints(array('12AM','1AM','2AM','3AM','4AM','5AM','6AM','7AM','8AM','9AM','10AM','11AM','12PM','1PM','2PM','3PM','4PM','5PM','6PM','7PM','8PM','9PM','10PM','11PM'),"Hours");
 $MyData->setSerieDescription("Hours");
 $MyData->setAbscissa("Hours");
 
 $MyData->setAxisDisplay(0,AXIS_FORMAT_METRIC,1);

 /* Create the pChart object */
 $myPicture = new pImage(700,230,$MyData);

 /* Turn of Antialiasing */
 $myPicture->Antialias = FALSE;

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

 /* Set the default font */
 $myPicture->setFontProperties(array("FontName"=>"fonts/pf_arma_five.ttf","FontSize"=>7));

 /* Define the chart area */
 $myPicture->setGraphArea(60,40,690,200);

 /* Draw the scale */
 $scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
 $myPicture->drawScale($scaleSettings);

 /* Write the chart legend */
 $myPicture->drawLegend(580,12,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Turn on shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Draw the chart */
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 $settings = array("Gradient"=>TRUE,"GradientMode"=>GRADIENT_EFFECT_CAN,"DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"DisplayR"=>255,"DisplayG"=>255,"DisplayB"=>255,"DisplayShadow"=>TRUE,"Surrounding"=>10);

 
 $Config = array("DisplayValues"=>1);
 $myPicture->drawBarChart($Config);

  $myPicture->drawText(150,35,"Hourly hits",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
 /* Render the picture (choose the best way) */
 $myPicture->Render("example2.png");   

echo ("<img id='loadingIndicator' src='example2.png' alt='Loading...' />")
 

?>

<?php
$db = new PDO('sqlite:../places.sqlite');

$row = $db->prepare("
SELECT rev_host as rev_host, SUM(visit_count) as visits 
	  FROM moz_places GROUP BY rev_host 
	  ORDER BY SUM(visit_count) DESC LIMIT 80");
$row->execute();

$site = array();
$visits = array();
foreach($row as $r) {
	array_push($visits, $r['visits']);
	array_push($site, strrev($r['rev_host']));
}


 /* Create and populate the pData object */
 $MyData = new pData();  
 
 $MyData->addPoints($visits,"Visits per Site");
 $MyData->setAxisName(0,"Hits");
 $MyData->addPoints($site,"Months");
 $MyData->setSerieDescription("Months");
 $MyData->setAbscissa("Months");
  $MyData->setAxisDisplay(0,AXIS_FORMAT_METRIC,1);

 /* Create the pChart object */
 $myPicture3 = new pImage(800,700,$MyData); 

 
 
 /* Turn of Antialiasing */
 $myPicture3->Antialias = FALSE;



 /* Set the default font */
 $myPicture3->setFontProperties(array("FontName"=>"fonts/pf_arma_five.ttf","FontSize"=>7));


 /* Draw the chart scale */ 
 $myPicture3->setGraphArea(150,30,700,700);
 $myPicture3->drawScale(array("YMargin"=>0,"CycleBackground"=>TRUE,"DrawSubTicks"=>TRUE,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10,"Pos"=>SCALE_POS_TOPBOTTOM)); //  

 /* Write the chart legend */
 $myPicture->drawLegend(570,215,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Turn on shadow computing */ 
 $myPicture3->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Draw the chart */
 $myPicture3->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 $settings = array("Gradient"=>TRUE,"GradientMode"=>GRADIENT_EFFECT_CAN,"DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"DisplayR"=>255,"DisplayG"=>255,"DisplayB"=>255,"DisplayShadow"=>TRUE,"Surrounding"=>10);

 
 
 
 $Config = array("DisplayValues"=>1);
 $myPicture3->drawBarChart($Config);

 
 /* Render the picture (choose the best way) */
 $myPicture3->Render("example3.png");   

echo ("<img id='loadingIndicator' src='example3.png' alt='Loading...' />")
 

?>


<?php
 
 
/*
--Shows sites most visited (rev_host) and number (visits)
SELECT rev_host as rev_host, SUM(visit_count) as visits 
	  FROM moz_places GROUP BY rev_host 
	  ORDER BY SUM(visit_count) DESC LIMIT 100

--Shows most visited sites (url,title) within rev_host
SELECT url, title, visit_count
	  FROM moz_places WHERE rev_host = :rh 
	  ORDER BY visit_count DESC LIMIT 5


--Shows hour  00 - 23 as num_visits
SELECT COUNT(*) as num_visits, 
              strftime('%H', visit_date/1000/1000, 'unixepoch', 'localtime') as hour 
              FROM moz_historyvisits v LEFT JOIN moz_places h ON v.place_id = h.id 
              WHERE v.visit_type NOT IN (0, 4) 
              GROUP BY hour ORDER BY hour ASC

			  
--Shows most visited sites WHERE hour = '01'			  
SELECT h.rev_host as rev_host, COUNT(v.id) as num_visits, 
              strftime('%H', v.visit_date/1000/1000, 'unixepoch', 'localtime') as hour 
              FROM moz_historyvisits v LEFT JOIN moz_places h ON v.place_id = h.id 
              WHERE hour = '01' AND v.visit_type 
              NOT IN (0, 4) AND rev_host IS NOT NULL 
              GROUP BY rev_host ORDER BY num_visits DESC LIMIT 20
			  
			  
--Shows number of downloads (number) combined filesize (size) grouped by day (0-6)	  
	  SELECT COUNT(*) as number, 
              ROUND(SUM(CAST(maxBytes AS FLOAT))/1048576, 2) as size, 
              strftime('%w', endTime/1000000, 'unixepoch', 'localtime') as day 
              FROM moz_downloads GROUP BY day ORDER BY day ASC
			  
--Shows largest downloads WHERE day = '1'
	  SELECT name, source, ROUND(CAST(maxBytes AS FLOAT)/1048576, 2) as size, 
              startTime/1000 as start, endTime/1000 as end, target, 
              strftime('%s', endTime/1000000, 'unixepoch', 'localtime') as time, 
              strftime('%w', endTime/1000000, 'unixepoch', 'localtime') as day 
              FROM moz_downloads WHERE day = '1' ORDER BY maxBytes DESC LIMIT 5
		
--Add up number for number of downloads. Add up mb for total downloaded. WHERE day = '1'		
	  SELECT COUNT(*) as number, 
              ROUND(SUM(CAST(maxBytes AS FLOAT))/1048576, 2) as mb, 
              strftime('%w', endTime/1000000, 'unixepoch', 'localtime') as day, 
              strftime('%m-%d-%Y', endTime/1000000, 'unixepoch', 'localtime') as date 
              FROM moz_downloads WHERE day = '0' GROUP BY date 
              ORDER BY endTime DESC
			  
--Total downloads (total) total MB (mb) average MB per DL (avg)
SELECT COUNT(*) as total, ROUND(SUM(maxBytes)/1048576, 2) as mb, 
              ROUND(AVG(maxBytes)/1048576, 2) as avg FROM moz_downloads 
              WHERE state = 1
			  
--Total number (count) per filetype (mimeType)
SELECT COUNT(*) as count, mimeType FROM moz_downloads 
              GROUP BY mimeType ORDER BY count DESC
	
--Show downloads (name, source, target,size,time) within mimeType	
		SELECT name, source, target, mimeType, 
                ROUND(CAST(maxBytes AS FLOAT)/1048576, 2) as size, 
                strftime('%s', endTime/1000000, 'unixepoch', 'localtime') as time 
                FROM moz_downloads
                WHERE mimeType = 'application/xml-dtd'
                ORDER BY endTime DESC	  
*/


?>


<?php   
/*
 // CAT:Surface chart/

 // pChart library inclusions /
 include("class/pData.class.php");
 include("class/pDraw.class.php");
 include("class/pImage.class.php");
 include("class/pSurface.class.php"); 

 
 

 
 

 // Create the pChart object /
 $myPicture = new pImage(400,400);

 // Create a solid background /
 $Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
 $myPicture->drawFilledRectangle(0,0,400,400,$Settings);

 // Do a gradient overlay /
 $Settings = array("StartR"=>194, "StartG"=>231, "StartB"=>44, "EndR"=>43, "EndG"=>107, "EndB"=>58, "Alpha"=>50);
 $myPicture->drawGradientArea(0,0,400,400,DIRECTION_VERTICAL,$Settings);
 $myPicture->drawGradientArea(0,0,400,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

 // Add a border to the picture /
 $myPicture->drawRectangle(0,0,399,399,array("R"=>0,"G"=>0,"B"=>0));
 
 // Write the picture title / 
 $myPicture->setFontProperties(array("FontName"=>"fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"pSurface() :: 2D surface charts",array("R"=>255,"G"=>255,"B"=>255));

 // Define the charting area /
 $myPicture->setGraphArea(20,40,380,380);
 $myPicture->drawFilledRectangle(20,40,380,380,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>20));

 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1));

 // Create the surface object /
 $mySurface = new pSurface($myPicture);

 // Set the grid size /
 $mySurface->setGrid(4,6);

 // Write the axis labels /
 $myPicture->setFontProperties(array("FontName"=>"fonts/pf_arma_five.ttf","FontSize"=>6));
 $mySurface->writeXLabels(array("Position"=>LABEL_POSITION_BOTTOM));
 $mySurface->writeYLabels();

 
 
 
$db = new PDO('sqlite:../places.sqlite');
$row = $db->prepare("
SELECT COUNT(*) as num_visits, 
             strftime ('%Y', visit_date/1000/1000,'unixepoch','localtime') as year,
             strftime ('%m', visit_date/1000/1000,'unixepoch','localtime') as month,
             strftime('%d', visit_date/1000/1000, 'unixepoch', 'localtime') as day
FROM moz_historyvisits v LEFT JOIN moz_places h ON v.place_id = h.id 
WHERE v.visit_type NOT IN (0, 4) 

AND year = '2011' and month = '03'

GROUP BY year,month,day ORDER BY year, month, day ASC");
//	$day[($r['day'])] = $r['num_visits'];
$day = array_fill(0, 39, 0);
$row->execute();
foreach($row as $r) {
	$day[  ltrim($r['day'],0)  ] = $r['num_visits'];
}

 
 // Add random values /
for($y=0; $y<=5; $y++) { 
	for($x=0; $x<=4; $x++) { 
		print("add ");
			print($x );
			print("," );
			print($y );
			print("|");
			print( (($day[($x+1)*($y+1)]))  );
		$mySurface->addPoint($y,$x, (($day[($x+1)*($y+1)])/5), $Force=TRUE ); 
	}
 }

 // Compute the missing points 
 $mySurface->computeMissing();

 // Draw the surface chart 
 $mySurface->drawSurface(array("Border"=>TRUE,"Surrounding"=>90));
//,"ShadeR1"=>0,"ShadeG1"=>0,"ShadeB1"=>0,"ShadeR2"=>255,"ShadeG2"=>255,"ShadeB2"=>255
 // Render the picture (choose the best way) 
 $myPicture->Render("example5.png");   
  echo ("<img id='loadingIndicator' src='example5.png' alt='Loading...' />")
  */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Firefox Search - History</title>
		<link rel="stylesheet" type="text/css" href="my.css">

		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script> 
		<script type="text/javascript">
		$(function() {
			$(".search_button").click(function() {
						$(".checkbox").hide();
				// Get typed value
				var searchString    = $("#search_box").val();
				
				if ($('#isTitle:checked').val() !== undefined) {
					var isTitleString    = $("#isTitle").val();
				}else{ var isTitleString = "0";}
				
				if ($('#isUrl:checked').val() !== undefined) {
					var isUrlString    = $("#isUrl").val();
				}else{ var isUrlString = "0";}
				
				if ($('#isHidden:checked').val() !== undefined) {
					var isHiddenString    = "1";
				}else{ var isHiddenString = "0";}
				if ($('#chrome:checked').val() !== undefined) {
					var chromeString    = "1";
				}else{ var chromeString = "0";}

				var orderBy = $("input[name='group2']:checked").val();
				$('#btn_get').val(orderBy);
			
				// Form queryString
				var data            = 'search='+ searchString + '&isTitle=' + isTitleString + '&isUrl=' + isUrlString + '&isHidden=' + isHiddenString + '&chrome=' + chromeString + '&orderBy=' + orderBy;
				// If searchString isn't empty
				if(searchString) {
					$.ajax({
						type: "POST",
						url: "post.php",
						data: data,
						beforeSend: function(html) { // happens before the call
							$("#results").html('');
							$("#searchresults").show();
							$(".word").html(searchString);
							$('.container').append('<div class="wait"><img id="loadingIndicator" src="loader.gif" alt="Loading..." /></div>');
					   },
					   success: function(html){ // happens after getting results
							$("#results").show();
							$("#results").append(html);
							$('.wait').remove();
					  }
					});
				}
				return false;
			});
		});
		</script>
		<script type="text/javascript">	
		function showInfo(str){	
			//Toggle Visibility///////////////////////
			if($("#info"+str).html() == ""){
			}
			else{
				$("#info"+str).toggle();
			}
			////////////////////////////////////////
			
			if ($('#chrome:checked').val() !== undefined) {
				var chromeString    = "1";
			}else{ var chromeString = "0";}
			
			if (str==""){
			  document.getElementById("txtHint").innerHTML="";
			  document.getElementById('info'+str).innerHTML='<div class="wait"><img id="loadingIndicator" src="infoloader.gif" alt="Loading..." /></div>';
			  return;
			}
			if (window.XMLHttpRequest){//IE7+, Firefox, Chrome, Opera, Safari
			  xmlhttp=new XMLHttpRequest();
			  document.getElementById('info'+str).innerHTML='<div class="wait"><img id="loadingIndicator" src="infoloader.gif" alt="Loading..." /></div>';
			}
			else{// IE6, IE5
			  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			  document.getElementById('info'+str).innerHTML='<div class="wait"><img id="loadingIndicator" src="infoloader.gif" alt="Loading..." /></div>';
			}
			xmlhttp.onreadystatechange=function(){
			  if (xmlhttp.readyState==4 && xmlhttp.status==200){
				document.getElementById('info'+str).innerHTML=xmlhttp.responseText;
				
				}
			}
			xmlhttp.open("GET","more_info.php?q="+str+'&chrome='+chromeString,true);
			xmlhttp.send();
	
			
		}
		</script>  
		<script>
		$(document).ready(function(){
			$(".checkbox").hide();
			$(".show_hide").show();
	
			$('.show_hide').click(function(){
				$(".checkbox").slideToggle();
			});	
		});
		</script>

		
	</head>
	<body>
		<div class="container">
			<div style="margin:20px auto; text-align: center; ">
			
				<form method="post" action="post.php">
					Chrome
					<input type="checkbox" name="chrome" id="chrome" value="0" class='chrome' />
					<input type="text" name="search" id="search_box" value="" class='search_box'/>
					<input type="submit" value="Go" class="search_button" /><br />
					 <a href="#" class="show_hide">Advanced Search / </a><a href="stat" class="">Stats</a><br />
					 
					<div class="checkbox">
						<div class="whereIt">
						Search in:
						<br><input type="checkbox" name="isTitle" id="isTitle" value="1" class='checkbox' checked />Title
						<br><input type="checkbox" name="isUrl" id="isUrl" value="1" class='checkbox' />URL
						<br><input type="checkbox" name="isHidden" id="isHidden" value="1" class='checkbox' />Hidden
						</div>
						<div class="orderIt">
							Order by:
							<br><input type="radio" name="group2" value="Title"> Title
							<br><input type="radio" name="group2" value="Url"> URL
							<br><input type="radio" name="group2" value="lastvisit" checked> Last Visit
							<br><input type="radio" name="group2" value="Frecency" > Priority
							<br><input type="radio" name="group2" value="visit_count" > Visit Count
							<br><input type="radio" name="group2" value="typed" > Typed
							<br><input type="radio" name="group2" value="hidden" > Hidden
						</div>
					</div>
				</form>
			</div>
			<div>
				<div id="searchresults">Search results for <span class="word"></span></div>  
				<ul id="results" class="update"></ul>
			</div>
		</div>
	</body>
</html>

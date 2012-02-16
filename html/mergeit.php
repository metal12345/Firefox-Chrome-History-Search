<form method="post" action="inc/merge.php">
	<b>WORK IN PROGRESS - This may corrupt your places.sqlite</b> <br />
	Known Problems: <br />
	Only handles history, not bookmarks/inputhistory/annos <br />
	Does not transfer SESSION numbers (unknown what this does) <br />
	Does not transfer Visited:/From: information <br />
	Does not transfer record GUIDs (useless for our purposes) <br />
	<br />
	Database to merge into
	<input type="text" name="db1" id="db1" value="places.sqlite" class='search_box'/>
	<br />
	Database to be merged
	<input type="text" name="db2" id="db2" value="places2.sqlite" class='search_box'/>
	<br />
	<input type="submit" value="SmashEm" class="search_button" /><br />
	<br />
	 <a href="index.php" class="">Search </a><a href="stat/index.php" class="">Stats</a><br />
</form>
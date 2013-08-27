<?php

		$player_id = $cacheFile->player_id;
		$publisher_id = $cacheFile->account_id;

		echo <<<BRIGHTCOVE
<script language="JavaScript" type="text/javascript" src="http://admin.brightcove.com/js/BrightcoveExperiences_all.js">
</script>
		 <object id="myExperience" class="BrightcoveExperience">
		 <param name="bgcolor" value="#FFFFFF" />
		 <param name="width" value="480" />
		 <param name="height" value="270" />
		 <param name="playerID" value="$player_id" />
		 <param name="publisherID" value="$publisher_id"/>
		 <param name="isVid" value="true" />
		 <param name="isUI" value="true" />
		 <param name="@videoPlayer" value="{$cacheFile->clip_id}" />
		 </object>

		  <div class="video-spacer"></div>

		  <style>
		    #transcript-panel { height:350px; }
		    #index-panel { height:350px; }
		    #searchbox-panel { height:350px; }
		    #search-results { height:177px; }
		    #audio-panel { height: 270px; width:670px; }
		    #header {height: 415px; }
		    #main {height: 350px; }
		  </style>
BRIGHTCOVE;

?>

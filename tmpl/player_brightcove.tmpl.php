<?php

		$player_id = $cacheFile->player_id;
		$publisher_id = $cacheFile->account_id;

		echo <<<BRIGHTCOVE
<script language="JavaScript" type="text/javascript" src="//sadmin.brightcove.com/js/BrightcoveExperiences_all.js">
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
     <param name="secureConnections" value="true" />
     <param name="secureHTMLConnections" value="true" />
		 </object>

		  <div class="video-spacer"></div>

		  <style>
		    #transcript-panel { height:550px; }
		    #index-panel { height:550px; }
		    #searchbox-panel { height:550px; }
		    #search-results { height:177px; }
		    #audio-panel { height: 270px; margin-left: 220px; }
		    #header {height: 415px; }
			#headervid {height: 380px; }
		    #main {height: 550px; }
		  </style>
BRIGHTCOVE;

?>
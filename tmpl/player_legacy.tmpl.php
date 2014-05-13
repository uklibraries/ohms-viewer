<?php

if($cacheFile->hasVideo == 1): 

		$player_id = '81922792001';
		$publisher_id = '73755470001';
		echo <<<BRIGHTCOVE
<script language="JavaScript" type="text/javascript" src="https://sadmin.brightcove.com/js/BrightcoveExperiences_all.js">
</script>
		 <object id="myExperience" class="BrightcoveExperience">
		 <param name="bgcolor" value="#FFFFFF" />
		 <param name="width" value="480" />
		 <param name="height" value="270" />
		 <param name="playerID" value="$player_id" />
		 <param name="publisherID" value="$publisher_id"/>
		 <param name="isVid" value="true" />
		 <param name="isUI" value="true" />
		 <param name="@videoPlayer" value="{$cacheFile->videoID}" />
<param name="secureConnections" value="true" />
<param name="secureHTMLConnections" value="true" />
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

else: ?>
<style>
    #transcript-panel { height:550px; }
    #index-panel { height:550px; }
    #searchbox-panel { height:550px; }
    #search-results { height:177px; }
    #audio-panel { height: 270px; }
    #header {height: auto; }
    #main {height: 550px; }
	div.centered { margin-left: 35px; }
</style>
<div class="centered">
	<?php
		echo '<a href="';
		if (isset($config['fileserver'])) {
                        $path = $cacheFile->file_name;
                        if ($path === ".mp3") {
                          $path = $cacheFile->media_url;
                        }
			echo '//'.$config['fileserver'].$path;
		} else {  
			echo $cacheFile->media_url;
		}
		echo '" id="subjectPlayer"></a>';
	?>
</div>
<?php endif; ?>

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
BRIGHTCOVE;

else: ?>
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
<style>
	#transcript-panel { height:550px; }
	#index-panel { height:550px; }
	#searchbox-panel { height:550px; }
	#search-results { height:177px; }
	#audio-panel { height: auto;  padding-top: 0px; padding-bottom: 20px; margin-bottom: 0px; }
	#header {height: auto; padding-bottom: 0px; }
	#headervid {height: auto; padding-bottom: 0px; }
	#main {height: 550px; }
	div.centered { margin-left: 35px; } 
</style>

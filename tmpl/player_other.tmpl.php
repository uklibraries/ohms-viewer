<?php

echo "<style>
              #transcript-panel { height:550px; }
              #index-panel { height:550px; }
              #searchbox-panel { height:550px; }
              #search-results { height:177px; }
              #audio-panel { height: 270px; }
              #header {height: " . $headerheight . "; }
              #main {height: 550px; }
			  div.centered { margin-left: 35px; }
            </style>";

$filepath = $cacheFile->media_url;
echo '<div class="centered">';
if($cacheFile->clip_format=='audio' || $cacheFile->clip_format=='audiotrans') {
	if(strpos($filepath,'http://') !== false || strpos($filepath,'https://') !== false) {
		echo '<a href="' . $filepath .'" id="subjectPlayer"></a>';
	} else {
		echo '<a href="http://' . $config['fileserver'] . $cacheFile->file_name .'" id="subjectPlayer"></a>';
	} 
} else {
	echo '<a href="' . $filepath . '" id="subjectPlayer" style="width: 444px;height: 250px;"></a>';
}
echo '</div>';
?>

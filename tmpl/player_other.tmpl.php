<?php

echo "<style>
              #transcript-panel { height:350px; }
              #index-panel { height:350px; }
              #searchbox-panel { height:350px; }
              #search-results { height:177px; }
              #audio-panel { height: 270px; width:670px; }
              #header {height: 415px; }
              #main {height: 350px; }
            </style>";

$filepath = $cacheFile->media_url;
echo '<div class="centered">';
if($cacheFile->clip_format=='audio' || $cacheFile->clip_format=='audiotrans') {
	if(strpos($filepath,'http://') !== false || strpos($filepath,'https://') !== false) {
		echo "<style>
        		      #header {height: 200px; }
        		      #audio-panel { height: 100px; width:670px; }              
			  </style>";
		echo '<a href="' . $filepath .'" id="subjectPlayer"></a>';
	} else {
		echo '<a href="http://' . $config['fileserver'] . $cacheFile->file_name .'" id="subjectPlayer"></a>';
	} 
} else {
	echo '<a href="' . $filepath . '" id="subjectPlayer" style="width: 444px;height: 250px;"></a>';
}
echo '</div>';
?>

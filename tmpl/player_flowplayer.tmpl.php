<?php

echo "<style>
              #transcript-panel { height:550px; }
              #index-panel { height:550px; }
              #searchbox-panel { height:550px; }
              #search-results { height:177px; }
              #audio-panel { height: auto;  padding-top: 0px; padding-bottom: 20px; margin-bottom: 0px; }
              #header {height: auto; padding-bottom: 0px; }
              #main {height: 550px; }
			  div.centered { margin-left: 35px; }
            </style>";

$filepath = $cacheFile->media_url;
echo '<div class="centered">';
if($cacheFile->clip_format=='audio') {
	if(strpos($filepath,'://') !== false) {
		echo '<a href="' . $filepath .'" id="subjectPlayer"></a>';
	} else {
		echo '<a href="//' . $config['fileserver'] . $cacheFile->file_name .'" id="subjectPlayer"></a>';
	} 
} else {
	echo '<a href="' . $filepath . '" id="subjectPlayer" style="width: 444px;height: 250px;"></a>';
}
echo '</div>';
?>

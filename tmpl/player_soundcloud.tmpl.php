<?php
//Set style values for SoundCloud player and page based on file format
$height = "180";
$width  =  "450";
$styleheight = "300";

echo '<style>';
echo  '#transcript-panel { height:350px; }';
echo  '#index-panel { height:350px; }';
echo  '#searchbox-panel { height:350px; }';
echo  '#search-results { height:230px; }';
echo  '#audio-panel { height: ' . $height . 'px; }';
echo  '#header {height: '.$styleheight.'px; }';
echo  '#main {height: 350px; }';
echo  '</style>';

$clipid=$cacheFile->clip_id;
$embedcode = str_replace('<iframe ', '<iframe id="soundcloud_widget"', $cacheFile->kembed);

if(isset($_GET['time']) && is_numeric($_GET['time']))
{
	$playScript = 'widget.play();';
	$extraScript = 'widget.seekTo(' . ($_GET['time'] * 1000) . ');';
}

echo <<<SOUNDCLOUD
	<div class="video" style="text-align: center;">
		<p>&nbsp;</p>
		{$embedcode}
		<script src="https://w.soundcloud.com/player/api.js"></script>
		<script>
			var widget = null;
			jQuery(document).ready(function() {
				widget = SC.Widget(document.getElementById('soundcloud_widget'));
				widget.bind(SC.Widget.Events.READY, function() {
					{$playScript}
				});
				widget.bind(SC.Widget.Events.PLAY, function() {
					console.log('track loaded!');
					{$extraScript}
				});
			});
		</script>
	</div>

		  <div class="video-spacer"></div>

		  <style>
		    #transcript-panel { height:550px; }
		    #index-panel { height:550px; }
		    #searchbox-panel { height:544px; }
		    #search-results { height:177px; }
		    #audio-panel { height: {$height}px; }
		    #header {height: 415px; }
			#headervid {height: auto; padding-bottom: 1px; }
		    #main {height: 550px; }
			#youtubePlayer {margin-left: 50px;}
			.video-spacer {height: 0px; }
		  </style>
SOUNDCLOUD;

?>
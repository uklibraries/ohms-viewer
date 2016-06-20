<?php

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

SOUNDCLOUD;

?>

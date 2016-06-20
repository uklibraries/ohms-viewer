<?php

$fileserver = $SERVER . '/audio/oralhist/';
$filepath = $cacheFile->media_url;

if(strpos($filepath, 'http://') !== false || strpos($filepath, 'https://') !== false)
{
	$linkToMedia = $filepath;
}
else
{
	$linkToMedia = '//' . $config['fileserver'] . $cacheFile->file_name;
}

$mediaFormat = 	substr($linkToMedia, -3, 3);
?>
<style type="text/css">
              #transcript-panel { height:550px; }
              #index-panel { height:550px; }
              #searchbox-panel { height:550px; }
              #audio-panel { height: auto;  padding-top: 0px; padding-bottom: 20px; margin-bottom: 0px; }
              #header {height: auto; padding-bottom: 0px; }
              #main {height: 550px; }
</style>
<div class="centered">
	<?php if($cacheFile->clip_format=='audio' || $cacheFile->clip_format=='audiotrans'): ?>
		<a href="<?php echo $linkToMedia?>" rel="<?php echo $mediaFormat?>" id="subjectPlayer" class="jp-jplayer"></a>
		<div id="jp_container_1" class="jp-audio" style="margin: auto;">
			<div class="jp-type-single">
				<div class="jp-gui jp-interface">
					<ul class="jp-controls">
						<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
						<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
						<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
						<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
						<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
						<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
					</ul>
					<div class="jp-progress">
						<div class="jp-seek-bar">
							<div class="jp-play-bar"></div>
						</div>
					</div>
					<div class="jp-volume-bar">
						<div class="jp-volume-bar-value"></div>
					</div>
					<div class="jp-time-holder">
						<div class="jp-current-time"></div>
						<div class="jp-duration"></div>
					</div>
				</div>
				<div class="jp-no-solution">
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

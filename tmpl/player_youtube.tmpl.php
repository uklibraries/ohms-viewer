<?php
$player_id = $interview->player_id;
$publisher_id = $interview->account_id;
$youtubeId = str_replace('http://youtu.be/', '', $interview->media_url);

$extraScript = '';
if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $extraScript = 'event.target.seekTo(' . (int)$_GET['time'] . ');';
}

echo <<<YOUTUBE
<div id="youtubePlayer"></div>
   <div class="video-spacer"></div>
<script type="text/javascript">
var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

var player;
var setTime = 0;
function onYouTubeIframeAPIReady() {
  player = new YT.Player('youtubePlayer', {
    height: '270',
    width: '480',
    videoId: '{$youtubeId}',
    startAt: setTime,
    events: {
      onReady: onPlayerReady
    }
  });

  function onPlayerReady(event) {
    event.target.playVideo();
    {$extraScript}
  }
}
</script>
YOUTUBE;

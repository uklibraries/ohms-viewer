<?php

$clipid = $interview->clip_id;
if ($interview->kembed == "" && $interview->media_url != "") {
    $height = ($interview->clip_format == 'audio' ? 95 : 279);
    $video_id = str_replace('https://vimeo.com/', '', str_replace('http://vimeo.com/', '', $interview->media_url));
    $embedcode = '<iframe id="vimeo_widget" src="https://player.vimeo.com/video/' . $video_id . '?color=ffffff&badge=0&portrait=false&title=false&byline=false" width="100%" maxwidth="100%" height="' . $height . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
} elseif ($interview->kembed != "") {
        $interview->kembed = preg_replace('/(width|height)=["\']\d*["\']\s?/', "", $interview->kembed);
        $embedcode = str_replace('<iframe ', '<iframe title="Video Player" id="vimeo_widget"', $interview->kembed);
}

if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $playScript = 'widget.play();';
    $extraScript = 'widget.setCurrentTime(' . ($_GET['time']) . ');';
} else {
    $playScript = '';
    $extraScript = '';
}
$height = ($interview->clip_format == 'audio' ? 95 : 279);

echo <<<VIMEO
<div class="video embed-responsive embed-responsive-16by9" style="width: 500px; height: {$height}px;margin-left: auto; margin-right: auto;">
  <p>&nbsp;</p>
  {$embedcode}
  <script src="https://player.vimeo.com/api/player.js"></script>
  <script>
var widget = null;
jQuery(document).ready(function () {
  widget = new Vimeo.Player(document.getElementById('vimeo_widget'));
  widget.on('ready', function(event) {
  {$playScript}
  {$extraScript}
});
if (exhibitMode){ 
widget.on('timeupdate', function(data) {
    if (data.seconds > endAt && endAt != null){
        widget.pause();
        endAt = null;
        exhibitIndex.trigger('click');
        endAt = null;
        exhibitIndex = null;
    }
    
  });
}
});


</script>
</div>
VIMEO;

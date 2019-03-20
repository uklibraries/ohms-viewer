<?php
$clipid=$interview->clip_id;
$embedcode = str_replace('<iframe ', '<iframe id="soundcloud_widget"', $interview->kembed);

if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $playScript = 'widget.play();';
    $extraScript = 'widget.seekTo(' . ($_GET['time'] * 1000) . ');';
} else {
    $playScript = '';
    $extraScript = '';
}

echo <<<SOUNDCLOUD
<div class="video">
  <p>&nbsp;</p>
  {$embedcode}
<script src="https://w.soundcloud.com/player/api.js"></script>
<script>
var widget = null;
jQuery(document).ready(function () {
  widget = SC.Widget(document.getElementById('soundcloud_widget'));
  widget.bind(SC.Widget.Events.READY, function () {
    {$playScript}
  });
  widget.bind(SC.Widget.Events.PLAY, function () {
    console.log('track loaded!');
    {$extraScript}
  });
  if (exhibitMode){ 
      widget.bind(SC.Widget.Events.PLAY_PROGRESS, function(data) {
        if (data.currentPosition > endAt * 1000 && endAt != null){
            widget.pause();
            exhibitIndex.trigger('click');
            endAt = null;
            exhibitIndex = null;
        }
      });
  }
});
</script>
</div>
SOUNDCLOUD;

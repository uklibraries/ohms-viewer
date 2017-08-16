<?php

$clipid = $interview->clip_id;
$embedcode = str_replace('<iframe ', '<iframe id="vimeo_widget"', $interview->kembed);

if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $playScript = 'widget.play();';
    $extraScript = 'widget.setCurrentTime(' . ($_GET['time'] * 1000) . ');';
} else {
    $playScript = '';
    $extraScript = '';
}

echo <<<VIMEO
<div class="video">
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
});
</script>
</div>
VIMEO;

<?php

$clipid = $interview->clip_id;
if ($interview->kembed == "" && $interview->media_url != "") {
    $media_url = $interview->media_url;
} else if ($interview->kembed != "") {
    preg_match('/src="([^"]+)"/', $interview->kembed, $match);
    $media_url = $match[1];
    if (strstr($media_url, ':443')) {
        $media_url = str_replace(':443', '', $media_url);
        $media_url = str_replace('//', 'https://', $media_url);
    }
}

if (($interview->avalon_target_domain) != '') {
    $domain = $interview->avalon_target_domain;
} else if (preg_match('/https?:\/\/([^\/]+)\//i', $media_url, $matches)) {
    $domain = $matches[0];
}

$embedcode = '<iframe id="avalon_widget" src="' . $media_url . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';

if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $playScript = "widget('play');";
    $extraScript = "widget('set_offset',{'offset':{$_GET['time']}});";
} else {
    $playScript = '';
    $extraScript = '';
}
$height = ($interview->clip_format == 'audio' ? 40 : 279);

echo <<<AVALON
<div class="video embed-responsive embed-responsive-16by9" style="width: 500px; height: {$height}px;margin-left: auto; margin-right: auto;">
  <p>&nbsp;</p>
  {$embedcode}
  <script>
    var widget = null;
    var offsetTime = 0;
    var target_domain = "{$domain}";
    widget = function(c, params) {
        var f = $('#avalon_widget');
        var command = params || {};
        command['command'] = c;
        f.prop('contentWindow').postMessage(command, target_domain);
    };
    // Receive commands from the server
    window.addEventListener('message', function(event) {
        var command = event.data.command;
        if (command == 'currentTime') {
            offsetTime = event.data.currentTime;
            if (exhibitMode) {
                if (offsetTime > endAt && endAt != null) {
                    widget('pause');
                    exhibitIndex.trigger('click');
                    endAt = null;
                    exhibitIndex = null;
                }
            }
        }
    });
    jQuery(document).ready(function() {
        setTimeout(function() {
            {
                $playScript
            } {
                $extraScript
            }
        }, 500);
    });
    $(window).load(function() {
        setInterval(function() {
            widget('get_offset');
        }, 500);
    });
  </script>  

</div>
AVALON;

<?php

$clipid = $interview->clip_id;
if ($interview->kembed == "" && $interview->media_url != "") {
    $media_url = $interview->media_url;
} else if ($interview->kembed != "") {
    preg_match('/src="([^"]+)"/', $interview->kembed, $match);
    $media_url = $match[1];
}

$parseDomain = parse_url($media_url);
$domain = "{$parseDomain['scheme']}://{$parseDomain['host']}";
$embedcode = '<iframe id="avalon_widget" src="' . $media_url . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';

if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $playScript = "widget('play');";
    $extraScript = "widget('set_offset',{'offset':{$_GET['time']}});";
} else {
    $playScript = '';
    $extraScript = '';
}
$height = ($interview->clip_format == 'audio' ? 95 : 279);

echo <<<AVALON
<div class="video embed-responsive embed-responsive-16by9" style="width: 500px; height: {$height}px;margin-left: auto; margin-right: auto;">
  <p>&nbsp;</p>
  {$embedcode}
  <script>
    var widget = null;
    var target_domain = "{$domain}";
    widget = function(c, params){
        var f = $('#avalon_widget');
        var command = params || {};
        command['command']=c;
        f.prop('contentWindow').postMessage(command,target_domain);
    };
    jQuery(document).ready(function () {
        setTimeout(function(){
            {$playScript}
            {$extraScript}
        },500);
    });
  </script>  
</script>
</div>
AVALON;

<?php

$clipid = $interview->clip_id;
$PARTNER_ID = $interview->account_id;
$UICONF_ID = $interview->player_id;
$embedcode = html_entity_decode($interview->kembed);

$matches = array();
preg_match("{/p/(?<partner_id>\d+)/}", $embedcode, $matches);
$partner_id = $matches['partner_id'];

$matches = array();
preg_match("{/uiconf_id/(?<uiconf_id>\d+)/}", $embedcode, $matches);
$uiconf_id = $matches['uiconf_id'];

$matches = array();
preg_match("{&entry_id=(?<entry_id>[^&]+)}", $embedcode, $matches);
$entry_id = $matches['entry_id'];

$matches = array();
preg_match("{https?\://[^/]+}", $embedcode, $matches);
$kalturaHost = $matches[0];

# XXX: This block requires further attention.
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
    $kalturaHost = str_replace('http:', 'https:', $kalturaHost);
} else {
    $kalturaHost = str_replace('https:', 'http:', $kalturaHost);
}

$height = ($interview->clip_format == 'audio' ? 95 : 279);

$extraScript = '';
$autoPlay = 'false';
if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $extraScript = 'kdp.kBind(\'mediaReady\', function(){
    window.kdp.sendNotification(\'doSeek\', ' . $_GET['time'] . ');
  })';
}
if (!empty($extraScript)) {
    $autoPlay = 'true';
}

$kalturaJS = "$kalturaHost/p/$partner_id/sp/{$partner_id}00/embedIframeJs/uiconf_id/$uiconf_id/partner_id/$partner_id";

echo <<<KALTURA
<div id="youtubePlayer">
    <div id="kaltura_player_embed" style="width: 500px; height: {$height}px;" class="embed-responsive embed-responsive-16by9"></div>
</div>
<script src="{$kalturaJS}"></script>
<script type="text/javascript">
kWidget.embed({
    'targetId': 'kaltura_player_embed',
    'wid': '_{$partner_id}',
    'uiconf_id' : '{$uiconf_id}',
    'entry_id' : '{$entry_id}',
    'flashvars': {
        'autoPlay': {$autoPlay},
        'externalInterfaceDisabled': false,
        'keyboardShortcuts': { 
	'shortSeekTime' : '15',
	'longSeekTime' : '15',
        }
    },
    'params': {
        'wmode': 'transparent'
    },
    readyCallback: function (playerId) {
          window.kdp = document.getElementById(playerId);
          {$extraScript}
          window.kdp.kBind("playerUpdatePlayhead.currentTime", function( data, id ){
          if (exhibitMode){ 
		    if (data > endAt && endAt != null){
		        kdp.sendNotification("doPause");
                        exhibitIndex.trigger('click');
                        endAt = null;
                        exhibitIndex = null;
            }
           }
	});
    }
});
</script>
<div class="video-spacer-kaltura"></div>
KALTURA;

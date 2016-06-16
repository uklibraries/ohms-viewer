<?php
//Set style values for Kaltura player and page based on file format

$clipid=$cacheFile->clip_id;
$PARTNER_ID = $cacheFile->account_id;
$UICONF_ID = $cacheFile->player_id;
$embedcode = html_entity_decode($cacheFile->kembed);

$matches = array();
preg_match("/\/p\/([0-9]+)\//", $embedcode, $matches);
$partner_id = $matches[1];

$matches = array();
preg_match("/\/uiconf_id\/([0-9]+)\//", $embedcode, $matches);
$uiconf_id = $matches[1];

$matches = array();
preg_match("/\&entry_id=(.*?)\&/", $embedcode, $matches);
$entry_id = $matches[1];

$matches = array();
preg_match("/https?\:\/\/[^\/]+/", $embedcode, $matches);
$kalturaURL = $matches[0];

# XXX: This block requires further attention.
if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
{
    $kalturaURL = str_replace('http:', 'https:', $kalturaURL);
}
else
{
    $kalturaURL = str_replace('https:', 'http:', $kalturaURL);
}

$height = ($cacheFile->clip_format == 'audio' ? 95 : 279);

$extraScript = '';
$autoPlay = 'false';
if(isset($_GET['time']) && is_numeric($_GET['time']))
{
    $extraScript = 'kdp.kBind(\'mediaReady\', function(){
		window.kdp.sendNotification(\'doSeek\', ' . $_GET['time'] . ');
	})';
}
if(!empty($extraScript)) {
    $autoPlay = 'true';
}

echo <<<KALTURA
		<div id="youtubePlayer">
			<div id="kaltura_player_embed"></div>
		</div>
		<script src="{$kalturaURL}/p/{$partner_id}/sp/{$partner_id}00/embedIframeJs/uiconf_id/{$uiconf_id}/partner_id/{$partner_id}"></script>
		<script type="text/javascript">
			kWidget.embed({
				'targetId': 'kaltura_player_embed',
				'wid': '_{$partner_id}',
				'uiconf_id' : '{$uiconf_id}',
				'entry_id' : '{$entry_id}',
				'flashvars':
				{
				  'autoPlay': {$autoPlay},
				  'externalInterfaceDisabled': false
				},
				'params':
				{
				  'wmode': 'transparent'
				},
				readyCallback: function( playerId ){
				  window.kdp = document.getElementById(playerId);
				  {$extraScript}
				}
			});
		</script>

		  <div class="video-spacer"></div>


KALTURA;
?>

<?php
//Set style values for Kaltura player and page based on file format
if ($cacheFile->clip_format == 'audio') {
    $height = "126";
    $width  =  "450";
    $styleheight = "300";
} else {
    $height = "300";
    $width  =  "500";
    $styleheight = "415";
}
echo '<style>';
echo  '#transcript-panel { height:350px; }';
echo  '#index-panel { height:350px; }';
echo  '#searchbox-panel { height:350px; }';
echo  '#search-results { height:230px; }';
echo  '#audio-panel { height: 270px; }';
echo  '#header {height: '.$styleheight.'px; }';
echo  '#main {height: 350px; }';
echo  '</style>';

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

echo <<<KALTURA
		<div id="youtubePlayer">
			<div id="kaltura_player_embed" style="width: 500px; height: 279px;"></div>
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
				  'autoPlay': true,
				  'externalInterfaceDisabled': false
				},
				'params':
				{
				  'wmode': 'transparent'
				},
				readyCallback: function( playerId ){
				  window.kdp = document.getElementById(playerId);
				}
			});
		</script>

		  <div class="video-spacer"></div>

		  <style>
		    #transcript-panel { height:550px; }
		    #index-panel { height:550px; }
		    #searchbox-panel { height:544px; }
		    #search-results { height:177px; }
		    #audio-panel { height: 270px; }
		    #header {height: 415px; }
			#headervid {height: auto; padding-bottom: 1px; }
		    #main {height: 550px; }
			#youtubePlayer {margin-left: 50px;}
			.video-spacer {height: 0px; }
		  </style>
KALTURA;
?>

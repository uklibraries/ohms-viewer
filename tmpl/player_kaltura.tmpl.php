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
$embedcode = preg_replace('/height=\"([0-9]+)\"/', 'height="250"', $embedcode);
$embedcode = preg_replace('/width=\"([0-9]+)\"/', 'width="432"', $embedcode);

echo <<<KALTURA
			<div id="youtubePlayer">{$embedcode}</div>

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
<script type="text/javascript">
	function jsCallbackReady(objectId)
	{
		window.kdp = document.getElementById(objectId);
		kdp.sendNotification('doPlay');
	}
</script>
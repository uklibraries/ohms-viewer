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
echo  '#audio-panel { height: 270px; width:670px; }';
echo  '#header {height: '.$styleheight.'px; }';
echo  '#main {height: 350px; }';
echo  '</style>';

$clipid=$cacheFile->clip_id;
$PARTNER_ID = $cacheFile->account_id;
$UICONF_ID = $cacheFile->player_id;
echo '<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="'. $height .'" width="'. $width.'" bgcolor="#000000" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:video" resource="http://kaltura.uky.edu/index.php/kwidget/cache_st/1/wid/_'.$PARTNER_ID.'/uiconf_id/'.$UICONF_ID.'/entry_id/'.$clipid.'" data="http://kaltura.uky.edu/index.php/kwidget/cache_st/1/wid/_'.$PARTNER_ID.'/uiconf_id/'.$UICONF_ID.'/entry_id/'.$clipid.'"><param name="allowFullScreen" value="true" /><param name="allowNetworking" value="all" /><param name="allowScriptAccess" value="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="&autoPlay=true" /><param name="movie" value="http://kaltura.uky.edu/index.php/kwidget/cache_st/1/wid/_'.$PARTNER_ID.'/uiconf_id/'.$UICONF_ID.'/entry_id/'.$clipid.'" /><a href="http://corp.kaltura.com">video platform</a> <a href="http://corp.kaltura.com/video_platform/video_management">video management</a> <a href="http://corp.kaltura.com/solutions/video_solution">video solutions</a> <a href="http://corp.kaltura.com/video_platform/video_publishing">video player</a> <a rel="media:thumbnail" href="http://kaltura.uky.edu/p/'.$PARTNER_ID.'/sp/'.$PARTNER_ID.'00/thumbnail/entry_id/'.$clipid.'/width/120/height/90/bgcolor/000000/type/2"></a> <span property="media:autostart" content="true"></span> <span property="media:width" content="300"></span><span property="media:height" content="126"></span></span> <span property="media:type" content="application/x-shockwave-flash"></span> </object>';

?>
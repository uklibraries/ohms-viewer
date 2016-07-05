<?php
$mediaFormat = substr($interview->file_name, -3, 3);
if (isset($config['fileserver'])) {
    $linkToMedia = $interview->file_name;
    if ($linkToMedia === ".mp3") {
        $linkToMedia = $interview->media_url;
    }
    $linkToMedia = '//' . $config['fileserver'] . $linkToMedia;
} else {
    $linkToMedia = $interview->media_url;
}

if ($interview->hasVideo == 1) {
    $player_id = '81922792001';
    $publisher_id = '73755470001';
    echo <<<BRIGHTCOVE
<script type="text/javascript" src="https://sadmin.brightcove.com/js/BrightcoveExperiences_all.js">
</script>
<object id="myExperience" class="BrightcoveExperience">
    <param name="bgcolor" value="#FFFFFF" />
    <param name="width" value="480" />
    <param name="height" value="270" />
    <param name="playerID" value="$player_id" />
    <param name="publisherID" value="$publisher_id"/>
    <param name="isVid" value="true" />
    <param name="isUI" value="true" />
    <param name="@videoPlayer" value="{$interview->videoID}" />
    <param name="secureConnections" value="true" />
    <param name="secureHTMLConnections" value="true" />
</object>
<div class="video-spacer"></div>
BRIGHTCOVE;

} else { ?>
    <div class="centered">
    <a href="<?php echo $linkToMedia?>" rel="<?php echo $mediaFormat?>" id="subjectPlayer" class="jp-jplayer"></a>
    <div id="jp_container_1" class="jp-audio">
        <div class="jp-type-single">
            <div class="jp-gui jp-interface">
                <ul class="jp-controls">
                    <li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
                    <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
                    <li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
                    <li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
                    <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
                    <li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
                </ul>
                <div class="jp-progress">
                    <div class="jp-seek-bar">
                        <div class="jp-play-bar"></div>
                    </div>
                </div>
                <div class="jp-volume-bar">
                    <div class="jp-volume-bar-value"></div>
                </div>
                <div class="jp-time-holder">
                    <div class="jp-current-time"></div>
                    <div class="jp-duration"></div>
                </div>
            </div>
            <div class="jp-no-solution">
                <span>Update Required</span>
                To play the media you will need to either update your browser
                to a recent version or update your <a 
                href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
            </div>
        </div>
    </div>
    </div>
    <?php 
}

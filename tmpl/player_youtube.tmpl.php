<?php

$player_id = $interview->player_id;
$publisher_id = $interview->account_id;
$youtubeId = "";
if ($interview->media_url != "") {
    $youtubeId = str_replace('https://youtu.be/', '', str_replace('http://youtu.be/', '', $interview->media_url));
} else {
    $kembed = explode(" ", $interview->kembed);
    foreach ($kembed as $k) {

        if (strpos($k, "src=") !== false) {
            $chr_map = array(
                'src' => "",
                '=' => "",
                '"' => "",
                "'" => "",
                'https://www.youtube.com/embed/' => "",
                'http://www.youtube.com/embed/' => ""
            );
            $youtubeId = str_replace(array_keys($chr_map), array_values($chr_map), $k);
            break;
        }
    }
}
$extraScript = '';
if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $extraScript = 'event.target.seekTo(' . (int)$_GET['time'] . ');';
}

echo <<<YOUTUBE
<div id="youtubePlayer"></div>
   <div class="video-spacer"></div>
<script type="text/javascript">
var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i) || navigator.userAgent.match(/WPDesktop/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};
var player;
var setTime = 0;
var videotime = 0;
var timeupdater = null;

function onYouTubeIframeAPIReady() {
    var screenSize = width = $('body').width();
    var padding = 30;
    var width = 500;
    var height = 280;
    if (screenSize < 530) {
        width = $('body').width() - padding;
        height = ($('body').width() - padding) * 0.56;
    }
    player = new YT.Player('youtubePlayer', {
        height: height,
        width: width,
        playerVars: {
            playsinline: 1
        },
        videoId: '{$youtubeId}',
        startAt: setTime,
        events: {
            onReady: onPlayerReady,
            onStateChange: onPlayerStateChange
        }
    });

    function onPlayerReady(event) {
        if (!isMobile.any())
            event.target.playVideo(); {
            $extraScript
        }

    }

    function onPlayerStateChange(event) {
        if (event.data == YT.PlayerState.PLAYING) {
            if (endAt != null  && exhibitMode) {
                function updateTime() {
                    var oldTime = videotime;
                    if (player && player.getCurrentTime) {
                        videotime = player.getCurrentTime();
                    }
                    if (videotime !== oldTime) {
                        onProgress(videotime);
                    }
                }
                timeupdater = setInterval(updateTime, 500);
            }
        }
    }

}

function onProgress(currentTime) {
    if (currentTime > endAt && endAt != null) {
        player.pauseVideo();
        clearInterval(timeupdater);
        exhibitIndex.trigger('click');
        endAt = null;
        exhibitIndex = null;
    }
}
</script>
YOUTUBE;

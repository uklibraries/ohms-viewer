<?php
$doc = new DOMDocument();
$doc->loadHTML($embedcode);
$xpath = new DOMXPath($doc);
$src_nodes = $xpath->query('//iframe/@src');
if ($src_nodes->length > 0) {
    $source_url = $src_nodes->item(0)->nodeValue;
    $parsed_url = parse_url($source_url);
    $pattern = '/\/p\/(\d+)\/.*\/uiconf_id\/(\d+)/';
    preg_match($pattern, $embedcode, $matches);

    $partner_id = $matches[1];
    $uiconf_id = $matches[2];

    $pattern = '/entry_id=([^&]+)/';
    preg_match($pattern, $embedcode, $matches);
    $entry_id = $matches[1];

    $kalturaJS = "https://{$parsed_url['host']}/p/{$partner_id}/embedPlaykitJs/uiconf_id/{$uiconf_id}?targetId=kaltura_player_embed&entry_id={$entry_id}";
    $extraScript = '';
    $autoPlay = 'false';
    if (isset($_GET['time']) && is_numeric($_GET['time'])) {
        $extraScript = 'window.kalturaPlayer.currentTime = ' . $_GET['time'] . ';window.kalturaPlayer.play();';
    }
    if (!empty($extraScript)) {
        $autoPlay = 'true';
    }
    ?>
    <div id="youtubePlayer">
        <div id="kaltura_player_embed" style="width: 500px; height: <?php echo $height; ?>px;" class="embed-responsive embed-responsive-16by9"></div>
    </div>
    <script type="text/javascript" src="<?php echo $kalturaJS ?>"></script>
    <script type="text/javascript">
        try {
            var kalturaPlayer = KalturaPlayer.setup({
                targetId: "kaltura_player_embed",
                provider: {
                    partnerId: <?php echo $partner_id; ?>,
                    uiConfId: <?php echo $uiconf_id; ?>
                },
                playback: {
                    autoplay: <?php echo $autoPlay; ?>,
                    muted:<?php echo $autoPlay; ?>,
                }
            });
            kalturaPlayer.loadMedia({entryId: '<?php echo $entry_id; ?>'});
            kalturaPlayer.ready().then(() => {
    <?php echo $extraScript; ?>
                kalturaPlayer.addEventListener('timeupdate', function () {
                    if (exhibitMode) {
                        if (kalturaPlayer.currentTime > endAt && endAt != null) {
                            kalturaPlayer.pause();
                            exhibitIndex.trigger('click');
                            endAt = null;
                            exhibitIndex = null;
                        }
                    }
                });
            });


        } catch (e) {
            console.error(e.message)
        }
    </script>
    <div class="video-spacer-kaltura"></div>

    <?php
}





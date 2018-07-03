<?php
date_default_timezone_set($config['timezone']);
$audioFormats = array('.mp3', '.wav', '.ogg', '.flac', '.m4a');
$filepath = $interview->media_url;
$rights = (string)$interview->rights;
$usage = (string)$interview->usage;
$acknowledgment = (string)$interview->funding;
$contactemail = '';
$contactlink = '';
$copyrightholder = '';
$protocol = 'https';
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
    $protocol = 'http';
}
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$baseurl = "$protocol://$host$uri";
$site_url = "$protocol://$host";
$extraCss = null;
if (isset($config[$interview->repository])) {
    $repoConfig = $config[$interview->repository];
    $contactemail = $repoConfig['contactemail'];
    $contactlink = $repoConfig['contactlink'];
    $copyrightholder = $repoConfig['copyrightholder'];
    if (isset($repoConfig['open_graph_image']) && $repoConfig['open_graph_image'] <> '') {
        $openGraphImage = $repoConfig['open_graph_image'];
    }
    if (isset($repoConfig['open_graph_description']) && $repoConfig['open_graph_description'] <> '') {
        $openGraphDescription = $repoConfig['open_graph_description'];
    }

    if (isset($repoConfig['css']) && strlen($repoConfig['css']) > 0) {
        $extraCss = $repoConfig['css'];
    }
}
$seriesLink = (string)$interview->series_link;
$collectionLink = (string)$interview->collection_link;
$lang = (string)$interview->translate;

$gaScript = null;
if (isset($repoConfig['ga_tracking_id'])) {
    $gaScript = <<<GASCRIPT
<script type="text/javascript">
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', '{$repoConfig['ga_tracking_id']}', '{$repoConfig['ga_host']}');
ga('send', 'pageview');
</script>
GASCRIPT;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
    <title><?php echo $interview->title; ?></title>
    <link rel="stylesheet" href="css/viewer.css?v1.3" type="text/css"/>
    <?php if (isset($extraCss)) { ?>
        <link rel="stylesheet" href="css/<?php echo $extraCss ?>" type="text/css"/>
    <?php }
    ?>
    <link rel="stylesheet" href="css/jquery-ui.toggleSwitch.css" type="text/css"/>
    <link rel="stylesheet" href="css/jquery-ui-1.8.16.custom.css" type="text/css"/>
    <link rel="stylesheet" href="css/font-awesome.css">
    <link rel="stylesheet" href="css/simplePagination.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="js/jquery-ui.toggleSwitch.js"></script>
    <script src="js/toggleSwitch.js?v1"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
    <script src="js/viewer.js"></script>
    <script src="js/jquery.simplePagination.js"></script>
    <meta property="og:title" content="<?php echo $interview->title; ?>"/>
    <meta property="og:url" content="<?php echo $baseurl ?>">
    <?php if (isset($openGraphImage)) { ?>
        <meta property="og:image" content="<?php echo "$site_url/$openGraphImage" ?>">
    <?php }
    ?>
    <?php if (isset($openGraphDescription)) { ?>
        <meta property="og:description" content="<?php echo "$openGraphDescription" ?>">
    <?php }
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
</head>
<body>
<script>
    var jumpToTime = null;
    if (location.href.search('#segment') > -1) {
        var jumpToTime = parseInt(location.href.replace(/(.*)#segment/i, ""));
        if (isNaN(jumpToTime)) {
            jumpToTime = 0;
        }
    }
</script>
<?php if (in_array(substr(strtolower($filepath), -4, 4), $audioFormats)) { ?>
<div id="header">
    <?php } else {
    ?>
    <div id="headervid">
        <?php }
        ?>
        <?php if (isset($config[$interview->repository])): ?>
            <img id="headerimg"
                 src="<?php echo $config[$interview->repository]['footerimg']; ?>"
                 alt="<?php echo $config[$interview->repository]['footerimgalt']; ?>"/>
        <?php endif;
        ?>
        <div class="center">
            <h1><?php echo $interview->title; ?></h1>
            <div id="secondaryMetaData">
                <div>
                    <strong><?php echo $interview->repository; ?></strong>
                    <span class="show-info"><i class="fa fa-lg fa-caret-right"></i></span>
                    <span class="hide-info"><i class="fa fa-lg fa-caret-down"></i></span>
                    <br/>
                    <span class="detail-metadata">

                                <?php
                                if (trim($interview->interviewer)) {
                                    echo "{$interview->interviewer}, Interviewer";
                                }
                                ?>
                        <?php
                        if (trim($interview->interviewer) && trim($interview->accession)) {
                            echo " | ";
                        }
                        ?>
                        <?php echo $interview->accession; ?><br/>

                        <?php if (isset($interview->collection_link) && (string)$interview->collection_link != '') { ?>
                            <a href="<?php echo $interview->collection_link ?>"><?php echo $interview->collection ?></a>
                        <?php } else {
                            ?>
                            <?php echo $interview->collection; ?>
                        <?php }
                        ?>
                        <?php
                        if (trim($interview->collection) && trim($interview->series)) {
                            echo " | ";
                        }
                        ?>
                        <?php if (isset($interview->series_link) && (string)$interview->series_link != '') { ?>
                            <a href="<?php echo $interview->series_link ?>"><?php echo $interview->series ?></a>
                        <?php } else {
                            ?>
                            <?php echo $interview->series; ?>
                        <?php }
                        ?>
                            </span>
                </div>
            </div>
            <div id="audio-panel">
                <?php include_once 'tmpl/player_' . $interview->playername . '.tmpl.php'; ?>
            </div>
        </div>
    </div>
    <div id="main">
        <?php if (!empty(trim($interview->user_notes))): ?>
            <div class="user_notes"><?php echo $interview->user_notes ?>
                <img src="imgs/button_close.png" onclick="$('.user_notes').slideToggle();"/>
            </div>
        <?php endif; ?>
        <div id="main-panels">
            <div id="searchbox-panel"><?php include_once 'tmpl/search.tmpl.php'; ?></div>
            <div id="content-panel">
                <div id="holder-panel"></div>
                <?php
                $indexDisplay = 'display:none';
                $transcriptDisplay = 'display:block';
                if ((isset($panel) && $panel == '1') || ($interview->hasIndex() && (!isset($panel) || $panel != '0'))) {
                    $indexDisplay = 'display:block';
                    $transcriptDisplay = 'display:none';
                }
                ?>
                <div id="index-panel" class="index-panel" style="<?php echo $indexDisplay; ?>">
                    <?php echo $interview->index; ?>
                </div>
                <div id="transcript-panel" class="transcript-panel" style="<?php echo $transcriptDisplay; ?>">
                    <?php echo $interview->transcript; ?>
                </div>

            </div>

        </div>
    </div>
    <div id="footer">
        <div id="footer-metadata">
            <?php if (!empty($rights)) { ?>
                <p><span></span></p><strong><a href="#" id="lnkRights">View Rights Statement</a></strong>
                <div id="rightsStatement"><?php echo $rights; ?></div>
            <?php } else {
                ?>
                <p><span></span></p><strong>View Rights Statement</strong>
            <?php }
            ?>
            <?php if (!empty($usage)) { ?>
                <p><span></span></p><strong><a href="#" id="lnkUsage">View Usage Statement</a></strong>
                <div id="usageStatement"><?php echo $usage; ?></div>
            <?php } else {
                ?>
                <p><span></span></p><strong>View Usage Statement</strong>
            <?php }
            ?>

            <?php if (!empty($acknowledgment)) { ?>
                <p><span></span></p><strong><a href="#" id="lnkFunding">Acknowledgment</a></strong>
                <div id="fundingStatement"><?php echo $acknowledgment; ?></div>
            <?php } else {
                ?>
                <p><span></span></p><strong>Acknowledgment</strong>
            <?php }
            ?>
            <?php if (!empty($collectionLink)) { ?>
                <p><span></span></p><strong>Collection Link:
                    <?php if (isset($interview->collection_link) && (string)$interview->collection_link != '') { ?>
                        <a href="<?php echo $interview->collection_link ?>"><?php echo $interview->collection ?></a>
                    <?php } else {
                        ?>
                        <?php echo $interview->collection; ?>
                    <?php }
                    ?>
                </strong>
            <?php }
            ?>
            <?php if (!empty($seriesLink)) { ?>
                <p><span></span></p>
                <strong>Series Link:
                    <?php if (isset($interview->series_link) && (string)$interview->series_link != '') { ?>
                        <a href="<?php echo $interview->series_link ?>"><?php echo $interview->series ?></a>
                    <?php } else {
                        ?>
                        <?php echo $interview->series; ?>
                    <?php }
                    ?>
                </strong>
            <?php }
            ?>
            <?php if (!empty($contactemail)) { ?>
            <p><span></span></p>
            <strong>Contact Us: <a href="mailto:<?php echo $contactemail ?>"><?php echo $contactemail ?></a> |
                <a href="<?php echo $contactlink ?>"><?php echo $contactlink ?></a>
            </strong>
            <?php }
            ?>
        </div>
        <div id="footer-copyright">
            <small id="copyright"><span>&copy; <?php echo Date("Y") ?></span><?php echo $copyrightholder ?></small>
        </div>
        <div id="footer-logo">
            <img alt="Powered by OHMS logo" src="imgs/ohms_logo.png" border="0"/>
        </div>
        <br clear="both"/>
    </div>
    <script src="js/jquery.jplayer.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.scrollTo-min.js"></script>
    <script src="js/viewer_<?php echo $interview->viewerjs; ?>.js?v=12"></script>
    <link rel="stylesheet" href="js/fancybox_2_1_5/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen"/>
    <link rel="stylesheet" href="skin/skin-dark/jplayer.dark.css" type="text/css" media="screen"/>
    <script src="js/fancybox_2_1_5/source/jquery.fancybox.pack.js?v=2.1.5"></script>
    <link rel="stylesheet"
          href="js/fancybox_2_1_5/source/helpers/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen"/>
    <script src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
    <script src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>
    <link rel="stylesheet"
          href="js/fancybox_2_1_5/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" type="text/css" media="screen"/>
    <script src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
    <script>
        $(document).ready(function () {

            jQuery('a.indexSegmentLink').on('click', function (e) {
                var linkContainer = '#segmentLink' + jQuery(e.target).data('timestamp');
                e.preventDefault();
                if (jQuery(linkContainer).css("display") == "none") {
                    jQuery(linkContainer).fadeIn(1000);
                } else {
                    jQuery(linkContainer).fadeOut();
                }

                return false;
            });
            jQuery('.segmentLinkTextBox').on('click', function () {
                jQuery(this).select();
            });
            if (jumpToTime !== null) {
                jQuery('div.point').each(function (index) {
                    if (parseInt(jQuery(this).find('a.indexJumpLink').data('timestamp')) == jumpToTime) {
                        jumpLink = jQuery(this).find('a.indexJumpLink');
                        jQuery('#accordionHolder').accordion({active: index});
                        jQuery('#accordionHolder-alt').accordion({active: index});
                        var interval = setInterval(function () {
                            <?php
                            switch ($interview->playername) {
                            case 'youtube':
                            ?>
                            if (player !== undefined &&
                                player.getCurrentTime !== undefined && player.getCurrentTime() == jumpToTime) {
                                <?php
                                break;
                                case 'brightcove':
                                ?>
                                if (modVP !== undefined &&
                                    modVP.getVideoPosition !== undefined &&
                                    Math.floor(modVP.getVideoPosition(false)) == jumpToTime) {
                                    <?php
                                    break;
                                    case 'kaltura':
                                    ?>
                                    if (kdp !== undefined && kdp.evaluate('{video.player.currentTime}') == jumpToTime) {
                                        <?php
                                        break;
                                        default:
                                        ?>
                                        if (Math.floor(jQuery('#subjectPlayer').data('jPlayer').status.currentTime) == jumpToTime) {
                                            <?php
                                            break;
                                            }
                                            ?>
                                            clearInterval(interval);
                                        } else {
                                            jumpLink.click();
                                        }
                                    }
                                ,
                                    500
                                );
                        jQuery(this).find('a.indexJumpLink').click();
                    }
                });
            }

            $(".fancybox").fancybox();
            $(".various").fancybox({
                maxWidth: 800,
                maxHeight: 600,
                fitToView: false,
                width: '70%',
                height: '70%',
                autoSize: false,
                closeClick: false,
                openEffect: 'none',
                closeEffect: 'none'
            });
            $('.fancybox-media').fancybox({
                openEffect: 'none',
                closeEffect: 'none',
                width: '80%',
                height: '80%',
                fitToView: true,
                helpers: {
                    media: {}
                }
            });
            $(".fancybox-button").fancybox({
                prevEffect: 'none',
                nextEffect: 'none',
                closeBtn: false,
                helpers: {
                    title: {type: 'inside'},
                    buttons: {}
                }
            });
            jQuery('#lnkRights').click(function () {
                jQuery('#rightsStatement').fadeToggle(400);
                return false;
            });
            jQuery('#lnkUsage').click(function () {
                jQuery('#usageStatement').fadeToggle(400);
                return false;
            });
            jQuery('#lnkFunding').click(function () {
                jQuery('#fundingStatement').fadeToggle(400);
                return false;
            });
        });
    </script>
    <script>
        var cachefile = '<?php echo $interview->cachefile; ?>';
    </script>
    <?php
    if (isset($gaScript)) {
        echo $gaScript;
    }
    ?>
</body>
</html>

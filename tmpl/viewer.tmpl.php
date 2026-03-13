<?php
date_default_timezone_set($config['timezone']);

$audioFormats = array('.mp3', '.wav', '.ogg', '.flac', '.m4a');
$filepath = $interview->media_url;
$mediaFormat = (strtolower($interview->clipsource) == "aviary") ? $interview->aviaryMediaFormat : substr($filepath, -4, 4);
$rights = (string) $interview->rights;
$usage = (string) $interview->usage;
$acknowledgment = (string) $interview->funding;
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
$exhibitMode = 0;
$printMode = 0;
if (isset($config['exhibit_mode']) && $config['exhibit_mode'] <> '') {
    $exhibitMode = $config['exhibit_mode'];
} else {
    $exhibitMode = 0;
}
if (isset($config['print_mode']) && $config['print_mode'] <> '') {
    $printMode = $config['print_mode'];
} else {
    $printMode = 0;
}

if (isset($config[$interview->repository])) {
    $repoConfig = $config[$interview->repository];
} else {
    // Fallback: Find the first nested array
    foreach ($config as $key => $value) {
        if (is_array($value)) {
            $repoConfig = $value;
            break;
        }
    }
}


if (isset($repoConfig)) {
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
$seriesLink = (string) $interview->series_link;
$collectionLink = (string) $interview->collection_link;
$lang = (string) $interview->translate;

$userNotes = trim($interview->user_notes);
$css = ['viewer.css', 'font-awesome.css', 'jquery-ui-1.8.16.custom.css', 'simplePagination.css',
    'jquery.multiselect.css', 'tipped.css', 'video-js.css'];
if (isset($extraCss)):
    $css[] = $extraCss;
endif;
$js = ['jquery.min.js', 'jquery-ui.min.js', 'jquery.multiselect.min.js', 'tipped.js',
    'jquery.simplePagination.js', 'video.min.js', 'jquery.easing.1.4.js', 'jquery.scrollTo-min.js',
    'popper.js', 'tooltip.js', 'echarts-en.min-421rc1.js', 'echarts-wordcloud2.min.js',
    'viewer.js', 'custom.js', 'viewer_' . $interview->viewerjs . '.js',
    'masonry.pkgd.min.js', 'imagesloaded.pkgd.min.js', 'visualization.js']
?>

<!DOCTYPE html>
<html lang="en" class="loading">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title><?php echo $interview->title; ?></title>

        <?php
        foreach ($css as $c):
            echo '<link rel="stylesheet" href="css/' . $c . '?v=' . $version . '" type="text/css" media="screen"/>';
        endforeach;
        foreach ($js as $j):
            echo '<script type="text/javascript" src="js/' . $j . '?v=' . $version . '"></script>';

        endforeach;
        ?>
        <link rel="stylesheet" href="js/fancybox/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen"/>
        <script src="js/fancybox/source/jquery.fancybox.pack.js?v=2.1.5"></script>
        <script src="js/fancybox/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>
        <link rel="stylesheet" href="js/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" type="text/css" media="screen"/>
        <script src="js/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
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
        <?php if (isset($repoConfig['ga_tracking_id'])) { ?>
            <!-- Google tag (gtag.js) -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $repoConfig['ga_tracking_id']; ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());

                gtag('config', '<?php echo $repoConfig['ga_tracking_id']; ?>');
            </script>
        <?php } ?>

    </head>
    <body>
        <?php
        if (preg_replace('/\xC2\xA0/', '', trim(html_entity_decode(strip_tags($interview->user_notes))))):
            ?>
            <div id="userNotesModal" title="User Notes">
                <div class="dialog-content">
                    <?php echo $interview->user_notes; ?>

                </div>
                <div class="dialog-footer">
                    <button id="btnOk" tabindex="0">OK</button>
                </div>
            </div>
        <?php endif; ?>
        <script>
            var currentLeftTab = '#about-tab-1';
            var currentRightTab = '#index-tab-2';
            var exhibitMode = <?php echo $exhibitMode; ?>;

            var endAt = null;
            var exhibitIndex = null;
            var jumpToTime = null;
            if (location.href.search('#segment') > -1) {
                jumpToTime = parseInt(location.href.replace(/(.*)#segment/i, ""));
                if (isNaN(jumpToTime)) {
                    jumpToTime = 0;
                }
            }
        </script>

        <div class="main-box-holder">
            <div class="main-box">
                <div class="left-side">
                    <div id="headervid">  
                        <div class="top-details">
                            <?php if ($printMode) {
                                ?> 
                                <a href="#" class="printCustom" ></a>
                            <?php } if (isset($repoConfig)): ?>
                                <img id="headerimg"
                                     src="<?php echo $repoConfig['footerimg']; ?>"
                                     alt="<?php echo $repoConfig['footerimgalt']; ?>"/>
                                 <?php endif;
                                 ?>
                            <h1 class="truncate"><?php echo $interview->title; ?></h1>

                            <div id="secondaryMetaData">
                                <div>
                                    <div class="detail-metadata truncate-collection">
                                        <?php
                                        echo $interview->collection;
                                        if (trim($interview->collection) && trim($interview->series)) {
                                            echo " | ";
                                        }
                                        echo $interview->series;
                                        ?>

                                    </div>
                                    <div class="detail-metadata truncate"><?php echo $interview->repository; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="audio-panel">
                        <?php include_once 'tmpl/player_' . $interview->playername . '.tmpl.php'; ?>
                    </div>

                    <div class="bottom-details">
                        <div id="searchbox-panel"><?php include_once 'tmpl/search.tmpl.php'; ?></div>
                        <div id="custom-tabs-left">
                            <ul>
                                <li><a href="#about-tab-1" class="tab-left-tab">About</a></li>
                                <?php if (!empty((string) $interview->index)): ?>
                                    <li><a href="#index-tab-1" class="tab-left-tab">Index <span class="count index_count d-none"></span></a></li>
                                <?php endif; ?>
                                <?php if (!empty((string) $interview->transcript)): ?>
                                    <li><a href="#transcript-tab-1" class="tab-left-tab">Transcript <span class="count transcript_count d-none"></span></a></li>
                                <?php endif; ?>
                                <?php if (count($interview->annotations) > 0): ?>
                                    <!-- These will be moved into dropdown via JS -->
                                    <li class="dropdown-tab"><a class="tab-left-tab" href="#wordcloud-tab-1" id="wordcloud-tab-1-head">Word Cloud</a></li>
                                    <?php if (count($interview->mapData) > 0): ?>
                                        <li class="dropdown-tab"><a class="tab-left-tab" href="#map-tab-1" id="map-tab-1-head">Map</a></li>
                                    <?php endif; ?>
                                    <li class="dropdown-tab"><a class="tab-left-tab" href="#timeline-tab-1">Timeline</a></li>
                                    <li class="dropdown-tab"><a class="tab-left-tab" href="#browser-tab-1">Browser</a></li>
                                <?php endif; ?>
                            </ul>
                            <div id="about-tab-1">
                                <div class="about-panel">
                                    <div><strong><a href="javascript://" class="about-attributes">Summary</a></strong>
                                        <p><?php echo $interview->description; ?></p>
                                    </div>
                                    <strong>Accession Number</strong>
                                    <p><?php echo $interview->accession; ?></p>
                                    <strong>Interviewee</strong>
                                    <p><?php echo "{$interview->interviewee}"; ?></p>
                                    <strong>Interviewer</strong>
                                    <p><?php echo $interview->interviewer; ?></p>

                                    <?php
                                    if (!empty((string) $interview->date)):
                                        echo '<strong>Interview Date</strong>';
                                        echo "<p>{$interview->date}</p>";
                                    endif;
                                    if (!empty((string) $interview->keyword)):
                                        $keywords = preg_replace('/\s*;\s*/', '; ', $interview->keyword);
                                        $keywords = trim($keywords);
                                        echo '<strong>Keywords</strong>';
                                        echo "<p>{$keywords}</p>";
                                    endif;
                                    if (!empty((string) $interview->subjects)):
                                        $subjects = preg_replace('/\s*;\s*/', '; ', $interview->subjects);
                                        $subjects = trim($subjects);
                                        echo '<strong>Subjects</strong>';
                                        echo "<p>{$subjects}</p>";
                                    endif;
                                    if (!empty($rights)):

                                        echo '<div><strong><a href="javascript://" class="about-attributes">View Rights Statement</a></strong>';
                                        echo "<p style='display:none';>{$rights}</p></div>";
                                    endif;
                                    if (!empty($usage)):

                                        echo '<div><strong><a href="javascript://" class="about-attributes">View Usage Statement</a></strong>';
                                        echo "<p style='display:none';>{$usage}</p></div>";
                                    endif;
                                    if (!empty($acknowledgment)):

                                        echo '<div><strong><a href="javascript://" class="about-attributes">Acknowledgment</a></strong>';
                                        echo "<p style='display:none';>{$acknowledgment}</p></div>";
                                    endif;

                                    if (!empty((string) $interview->language)):
                                        echo '<strong>Language</strong>';
                                        echo "<p>{$interview->language}</p>";
                                    endif;
                                    if (!empty((string) $interview->transcript_alt_lang)):
                                        echo '<strong>Language For Translation</strong>';
                                        echo "<p>{$interview->transcript_alt_lang}</p>";
                                    endif;
                                    ?>

                                </div>
                            </div>
                            <?php if (!empty((string) $interview->index)): ?>
                                <div id="index-tab-1">
                                    <div id="index-panel" class="index-panel">
                                        <?php echo $interview->index; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty((string) $interview->transcript)): ?>
                                <div id="transcript-tab-1">
                                    <div id="transcript-panel" class="transcript-panel">
                                        <?php if (count($interview->annotations) > 0): ?>
                                            <div class="data-layers">
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" id="toggle-layers-1" class="toggle-layers" data-layer="ttl1" name="toggle-layers" checked="checked">
                                                    <label for="toggle-layers-1" class="toggle-layers-label">View Data Layers</label>
                                                </div>
                                                <ul class="data-layers-list ttl1">
                                                    <li><span class="bdg-person"><i class="fa fa-eye" data-layer="bdg-person"></i> Person</span></li>
                                                    <li><span class="bdg-place"><i class="fa fa-eye" data-layer="bdg-place"></i> Place</span></li>
                                                    <li><span class="bdg-date"><i class="fa fa-eye" data-layer="bdg-date"></i> Date</span></li>
                                                    <li><span class="bdg-org"><i class="fa fa-eye" data-layer="bdg-org"></i> Org</span></li>
                                                    <li><span class="bdg-event"><i class="fa fa-eye" data-layer="bdg-event"></i> Event</span></li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                        <?php echo $interview->transcript; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php
                            if (count($interview->annotations) > 0):
                                $tab_tag = '1';
                                include 'tmpl/visualization.tmpl.php';
                            endif;
                            ?>
                        </div>
                    </div>




                </div>

                <div class="right-side">
                    <button class="toggle-sides"><img src="./imgs/toggle-btn-icon.png" /></button>
                    <div class="right-side-inner">
                        <div class="toolbar-right">
                            <?php if ($interview->translate == '1'): ?>
                                <div id="translate-toggle" class="<?php echo $toggleLanguageSwitch; ?>">
                                    <a href="#" class="translate-link <?php echo (($_GET['translate'] ?? null) == 1) ? 'active' : ''; ?>" id="translate-link" data-lang="<?php echo $interview->language ?>"
                                       data-translate="<?php $interview->transcript_alt_lang; ?>"
                                       data-toggleAvailable="<?php echo $toggleAvailable; ?>"
                                       data-linkto="<?php echo $interview->transcript_alt_lang ?>" data-default="<?php echo $interview->language ?>">
                                        <?php echo $interview->transcript_alt_lang ?></a>
                                    <a href="#" class="translate-link <?php echo (($_GET['translate'] ?? 0) == 0) ? 'active' : ''; ?>" id="translate-link" data-lang="<?php echo $interview->transcript_alt_lang ?>"
                                       data-translate="<?php $interview->language; ?>"
                                       data-toggleAvailable="<?php echo $toggleAvailable; ?>"
                                       data-linkto="<?php echo $interview->language ?>" data-default="<?php echo $interview->language ?>">
                                        <?php echo $interview->language ?></a>
                                </div>
                                <?php
                            endif;
                            ?>
                            <a href="#" class="refreshPage"></a>
                            <?php if ($printMode) {
                                ?> 
                                <a href="#" class="printCustom" ></a>
                            <?php } ?>
                        </div>
                        <?php if ($printMode) { ?>
                            <a href="#" class="printCustomMobile" ></a>
                        <?php } ?>

                        <div id="custom-tabs-right">
                            <ul>
                                <?php if (!empty((string) $interview->index)): ?>
                                    <li><a href="#index-tab-2" class="tab-right-tab">Index <span class="count index_count d-none"></span></a></li>
                                <?php endif; ?>
                                <?php if (!empty((string) $interview->transcript)): ?>
                                    <li><a href="#transcript-tab-2" class="tab-right-tab">Transcript <span class="count transcript_count d-none"></span></a></li>
                                <?php endif; ?>
                                <?php if (count($interview->annotations) > 0): ?>
                                    <!-- These will be moved into dropdown via JS -->
                                    <li class="dropdown-tab"><a href="#wordcloud-tab-2" class="tab-right-tab" id="wordcloud-tab-2-head">Word Cloud</a></li>
                                    <?php if (count($interview->mapData) > 0): ?>
                                        <li class="dropdown-tab"><a href="#map-tab-2" class="tab-right-tab" id="map-tab-2-head">Map</a></li>
                                    <?php endif; ?>

                                    <li class="dropdown-tab"><a class="tab-right-tab" href="#timeline-tab-2">Timeline</a></li>
                                    <li class="dropdown-tab"><a class="tab-right-tab" href="#browser-tab-2">Browser</a></li>
                                <?php endif; ?>
                            </ul>
                            <?php if (empty((string) $interview->index) && empty((string) $interview->transcript)): ?>
                                <div class="center-wrapper">
                                    <div class="message-box">
                                        No Transcript or Index Available.
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty((string) $interview->index)): ?>
                                <div id="index-tab-2">
                                    <div id="index-panel" class="index-panel">
                                        <?php echo $interview->index; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty((string) $interview->transcript)): ?>
                                <div id="transcript-tab-2">
                                    <div id="transcript-panel" class="transcript-panel">
                                        <?php if (count($interview->annotations) > 0): ?>
                                            <div class="data-layers">
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" id="toggle-layers-2" class="toggle-layers" data-layer="ttl2" name="toggle-layers" checked="checked">
                                                    <label for="toggle-layers-2" class="toggle-layers-label">View Data Layers</label>
                                                </div>
                                                <ul class="data-layers-list ttl2">
                                                    <li><span class="bdg-person"><i class="fa fa-eye" data-layer="bdg-person"></i> Person</span></li>
                                                    <li><span class="bdg-place"><i class="fa fa-eye" data-layer="bdg-place"></i> Place</span></li>
                                                    <li><span class="bdg-date"><i class="fa fa-eye" data-layer="bdg-date"></i> Date</span></li>
                                                    <li><span class="bdg-org"><i class="fa fa-eye" data-layer="bdg-org"></i> Org</span></li>
                                                    <li><span class="bdg-event"><i class="fa fa-eye" data-layer="bdg-event"></i> Event</span></li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                        <?php echo $interview->transcript; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php
                            if (count($interview->annotations) > 0):
                                $tab_tag = '2';
                                include 'tmpl/visualization.tmpl.php';
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="footer">
            <div id="footer-metadata">
                <?php
                if (!empty($rights)):

                    echo '<div><strong><a href="javascript://" class="about-attributes">View Rights Statement</a></strong>';
                    echo "<p style='display:none';>{$rights}</p></div>";
                endif;
                if (!empty($usage)):

                    echo '<div><strong><a href="javascript://" class="about-attributes">View Usage Statement</a></strong>';
                    echo "<p style='display:none';>{$usage}</p></div>";
                endif;
                if (!empty($acknowledgment)):

                    echo '<div><strong><a href="javascript://" class="about-attributes">Acknowledgment</a></strong>';
                    echo "<p style='display:none';>{$acknowledgment}</p></div>";
                endif;
                ?>


                <?php if (!empty($collectionLink)) { ?>
                    <p><span></span></p><strong>Collection Link:
                        <?php if (isset($interview->collection_link) && (string) $interview->collection_link != '') { ?>
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
                        <?php if (isset($interview->series_link) && (string) $interview->series_link != '') { ?>
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

        <div id="customPopover" class="popover">
            <?php echo $interview->groupAnnotations; ?>
        </div>







        <script>

            var playerNameJS = '<?php echo $interview->playername; ?>';
            var cachefile = '<?php echo $interview->cachefile; ?>';
            var initialLoad = true;
            $(document).ready(function () {
                setTimeout(() => {
                    $('html').removeClass('loading');
                }, 500);
//                                              

                if (jumpToTime !== null) {
                    let indexTab = '#index-tab-1';
                    if ($('.right-side').is(':visible')) {
                        indexTab = '#index-tab-2';
                    }
                    setTimeout(function () {
                        jQuery('a[href="' + indexTab + '"]').trigger("click")
                    }, 500);
                    jQuery('div.point').each(function (index) {
                        if (parseInt(jQuery(this).find('a.indexJumpLink').data('timestamp')) == jumpToTime && initialLoad == true) {
                            initialLoad = false;
                            jumpLink = jQuery(this).find('a.indexJumpLink');



                            jQuery(indexTab + ' .accordionHolder').accordion({active: index});
                            jQuery(indexTab + ' .accordionHolder-alt').accordion({active: index});



                            var interval = setInterval(function () {
                                switch (playerNameJS) {
                                    case "youtube":
                                        if (player !== undefined &&
                                                player.getCurrentTime !== undefined && player.getCurrentTime() == jumpToTime) {
                                            clearInterval(interval);
                                        } else {
                                            jumpLink.click();
                                        }
                                        break;

                                    case "brightcove":
                                        if (modVP !== undefined &&
                                                modVP.getVideoPosition !== undefined &&
                                                Math.floor(modVP.getVideoPosition(false)) == jumpToTime) {
                                            clearInterval(interval);
                                        } else {
                                            jumpLink.click();
                                        }
                                        break;

                                    case "kaltura":
                                        if (kdp !== undefined && kdp.evaluate('{video.player.currentTime}') == jumpToTime) {
                                            clearInterval(interval);
                                        } else {
                                            jumpLink.click();
                                        }
                                        break;
                                    case "vimeo":
                                        clearInterval(interval);
                                        break;
                                    default:
                                        if (typeof player !== 'undefined' && player !== null) {
                                            player.ready(function () {
                                                console.log('Player is ready');
                                                if (!player.paused()) {
                                                    clearInterval(interval);
                                                }
                                                // Move to a specific point (in seconds)
                                                var seekTime = jumpToTime; // example: 30 seconds
                                                player.currentTime(seekTime);

                                                // Optionally play after seeking
                                                player.play();
                                            });
                                        }
                                }
                            }, 500);

                            jQuery(this).find('a.indexJumpLink').click();
                        }
                    });
                }
                $(".fancybox").fancybox();
                if (!jQuery.curCSS) {
                    jQuery.curCSS = function (elem, name, force) {
                        return jQuery.css(elem, name, force);
                    };
                }
                if ($('#userNotesModal').length > 0) {
                    $("#userNotesModal").dialog({
                        autoOpen: true,
                        modal: true,
                        dialogClass: "notes-dialog",
                        closeOnEscape: false,
                        draggable: false,
                        resizable: false,
                        // Disable closing on backdrop click
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close").remove();
                            setTimeout("$('.dialog-footer').focus()", 500);
                            $(".ui-widget-overlay").bind("click", function () {
                                return false; // prevents closing
                            });
                            $("#btnOk").click(function () {
                                $("#userNotesModal").dialog("close");
                            });
                        }
                    });
                }
            });
        </script>


        <script type="text/javascript">
            $(document).ready(function () {
                let viewer = new Viewer();
                viewer.initialize();
                const visualization = new VisualizationJS();
                visualization.initialize(<?php echo isset($entity_rows) ? json_encode($entity_rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) : '[]'; ?>, <?php echo count($interview->mapData) > 0 ? json_encode($interview->mapData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '[]'; ?>);

            });

        </script>
    </body> 
</html>

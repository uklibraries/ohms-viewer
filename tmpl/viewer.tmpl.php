<?php
date_default_timezone_set($config['timezone']);
$audioFormats = array('.mp3', '.wav', '.ogg', '.flac', '.m4a');
$filepath = $interview->media_url;
$mediaFormat = (strtolower($interview->clipsource) == "aviary")? $interview->aviaryMediaFormat :substr($filepath,-4, 4);
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
$exhibitMode = 0;
$printMode = 0;
if (isset($config['exhibit_mode']) && $config['exhibit_mode'] <> '') {
    $exhibitMode = $config['exhibit_mode'];
}else {
    $exhibitMode = 0;
}
if (isset($config['print_mode']) && $config['print_mode'] <> '') {
    $printMode = $config['print_mode'];
}else {
    $printMode = 0;
}

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
$userNotes = trim($interview->user_notes); 
$heightAdjustmentClass= "";
if (!empty($userNotes)):
    $heightAdjustmentClass= "adjust_height";
endif;
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
    <link rel="stylesheet" href="css/viewer.css?v1.4.6" type="text/css"/>
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
    <script src="js/toggleSwitch.js?v1.16"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
    <script src="js/viewer.js"></script>
    <script type="text/javascript" src="js/tipped/tipped.js"></script>
    <link rel="stylesheet" href="css/tipped/tipped.css" type="text/css"/>
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
    var exhibitMode = <?php echo $exhibitMode; ?>;
    var endAt = null;
    var exhibitIndex = null;
    var jumpToTime = null;
    if (location.href.search('#segment') > -1) {
        var jumpToTime = parseInt(location.href.replace(/(.*)#segment/i, ""));
        if (isNaN(jumpToTime)) {
            jumpToTime = 0;
        }
    }
</script>
<?php if (in_array($mediaFormat, $audioFormats)) { ?> 
<div id="header">
    <?php } else {
    ?>
    <div id="headervid">  
        <?php }
        if($printMode){
        ?> 
        <a href="#" class="printCustom" ></a>
        <?php } if (isset($config[$interview->repository])): ?>
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

                        <?php if ((string)$interview->collection_link != '') { ?>
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
                        <?php if ((string)$interview->series_link != '') { ?>
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
    <div id="main" class="<?php echo  $heightAdjustmentClass; ?>">
        <?php  if($printMode){ ?>
        <a href="#" class="printCustomMobile" ></a>
        <?php } if (!empty($userNotes)): ?>
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
    <script src="js/jquery.easing.1.4.js"></script>
    <script src="js/jquery.scrollTo-min.js"></script>
    <script src="js/viewer_<?php echo $interview->viewerjs; ?>.js?v=0.6"></script>
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
    <script src="js/popper.js"></script>
    <script src="js/tooltip.js"></script>
    <script>
        var allToolTipInstances = {};
        $(document).ready(function () {
            $(".printCustom").click(function(){
                window.location.href="viewer.php?action=pdf&cachefile=" + cachefile + "";
            });
            $(".printCustomMobile").click(function(){
                window.open("viewer.php?action=pdf&cachefile=" + cachefile + "",'_blank');
            });
            $(".transcript-line").each(function(){
                var jumplinkElm = $(this).find('.jumpLink');
                var numberOfIntervalsInLine = jumplinkElm.length;
                if(numberOfIntervalsInLine > 1){
                    var marginToAdd = 13;
                    var totalMargin = 13 * numberOfIntervalsInLine;
                    jumplinkElm.each(function(index){
                        var currentMargin = totalMargin - (marginToAdd*(index+1));
                        $(this).css('margin-top',currentMargin);
                    });
                }
            });

        
             setTimeout(function(){
               var htmlTranscript = $('#transcript-panel').html().trim();
               var htmlIndex = $('#index-panel').html().trim();
               var isTranslate = $('#is_translate').val().trim();
                if ((htmlTranscript == "" || htmlTranscript.includes("No transcript")) && isTranslate == "0"){
                        $('.alpha-circle').hide();
                        $('#toggle_switch').attr("disabled", "disabled");
                        $('.slider.round').css("background-color", "#ccc");
                } else if (htmlIndex == "" && htmlTranscript != "" && isTranslate == "0"){
                        $('.alpha-circle').hide();
                        $('#toggle_switch').attr("disabled", "disabled");
                        $('.slider.round').css("background-color", "#ccc");
                } else if (htmlIndex == "" && htmlTranscript == "" && isTranslate == "0"){
                        $('.alpha-circle').hide();
                        $('#toggle_switch').attr("disabled", "disabled");
                        $('.slider.round').css("background-color", "#ccc");
                }else if ((htmlIndex == "" || htmlTranscript == "" || htmlTranscript.includes("No transcript")) && isTranslate == "1"){
                    $('.alpha-circle').hide();
                }
            },300);
            
            $('.footnoteTooltip').each(function(index,element){
                footnoteID = $(element).data('index');
                footnoteAttrId = $(element).attr("id");
                footnoteHtml = $('#'+footnoteID).parent().children('span').html();
                $(element).attr("data-tooltip",footnoteHtml);
                activatePopper(footnoteAttrId);
            });  
            $('.info-circle').each(function(index, element){
                activatePopperIndexTranscript(element.id,'i');
            });
            footnoteHover("bind");
            jQuery('a.indexSegmentLink').on('click', function (e) {
                var linkContainer = '#segmentLink' + jQuery(this).data('timestamp');
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
              jQuery('.copyButtonViewer').on('click', function () {
                                var text = jQuery(this).prev().val();
                                copyToClipboard(text);
                                
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
        function footnoteHover(state){
            if(state== "bind"){
                $( ".footnote-ref" ).bind("hover",
                function() {
                    var footnoteHtmlLength = $(this).find('.footnoteTooltip').attr("data-tooltip").length;
                    width = footnoteHtmlLength * 50 / 100;
                    if(footnoteHtmlLength > 130){
                        $('head').append("<style>.tooltip{ width: " + width + "px }</style>");
                    }else{
                        $('head').append("<style>.tooltip{ width: 130px; }</style>");
                    }
                }
            );
            }else if (state == "unbind"){
                $( ".footnote-ref" ).unbind("hover");
            }
        }
        function activatePopper(element) {
            var footnoteHtml = $("#" + element).data("tooltip");
            allToolTipInstances[footnoteAttrId] = new Tooltip($("#" + element), {
                title: footnoteHtml,
                trigger: "hover",
                placement: "bottom",
                html: true,
                eventsEnabled: true,
                modifiers: {
                    flip: {
                        behavior: ['left', 'right', 'top']
                    },
                    preventOverflow: {
                        boundariesElement: $('#transcript-panel'),
                    },
                },
            });
        }

        function activatePopperIndexTranscript(element,type) {
            if(type == 'i'){
                var timePoint = $("#" + element).data("time-point");
                var id = $("#" + element).data("marker-counter");
                var indexTitle = $("#" + element).data("index-title");
                var anchorHtml = "<div class='info-toggle' onclick=\"toggleRedirectTranscriptIndex(" + id + ",'transcript-to-index')\" >Segment: <b>" + indexTitle + "</b> " + timePoint + " </div>";
                Tipped.create('#' + element, anchorHtml, {
                size: 'large',
                    radius: true,
                    position: 'right'
                });
            }
        }
        function toggleRedirectTranscriptIndex(id, type){
            if(type == 'transcript-to-index'){
                $('#toggle_switch').trigger('click');
                setTimeout(function(){
                    $('.tpd-tooltip').hide();
                    $('#transcript-panel').hide();
                    $('#index-panel').show();
                    var currentIndex = $('#accordionHolder').accordion('option', 'active');
                    if(currentIndex != id || currentIndex ===  false){
                        jQuery('#accordionHolder').accordion({active: id});
                        jQuery('#accordionHolder-alt').accordion({active: id});
                    }
                },250);
            }else if(type == 'index-to-transcript'){
                $('#toggle_switch').trigger('click');
                setTimeout(function(){
                    $('.tpd-tooltip').hide();
                    $('#index-panel').hide();
                    $('#transcript-panel').show();
                    var container = $('#transcript-panel'),
                    scrollTo = $("#info_trans_"+id);
                    container.animate({
                        scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
                    });
                },250);
            }
        }
        function copyToClipboard(val){
            var dummy = document.createElement("textarea");
            document.body.appendChild(dummy);
            dummy.value = val;
            dummy.select();
            document.execCommand("copy");
            document.body.removeChild(dummy);
        }
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
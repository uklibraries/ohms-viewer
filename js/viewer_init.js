var jumpToTime = null;
if (location.href.search('#segment') > -1) {
    var jumpToTime = parseInt(location.href.replace(/(.*)#segment/i, ""));
    if (isNaN(jumpToTime)) {
        jumpToTime = 0;
    }
}

$(document).ready(function() {

    jQuery('a.indexSegmentLink').click(function (e) {
        e.preventDefault();
        var linkContainer = '#segmentLink' + jQuery(e.target).data('timestamp');
        if (jQuery(linkContainer).css("display") == "none") {
            jQuery(linkContainer).fadeIn(1000);
        } else {
            jQuery(linkContainer).fadeOut();
        }
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
                var interval = setInterval(function() {
                    var reset = false;
                    switch(playerName) {
                        case 'youtube':
                            if (player !== undefined && player.getCurrentTime !== undefined && player.getCurrentTime() == jumpToTime) {
                                reset = true;
                            }
                            break;
                        case 'brightcove':
                            if (modVP !== undefined && modVP.getVideoPosition !== undefined && Math.floor(modVP.getVideoPosition(false)) == jumpToTime) {
                                reset = true;
                            }
                            break;
                        case 'kaltura':
                            if (kdp !== undefined && kdp.evaluate('{video.player.currentTime}') == jumpToTime) {
                                reset = true;
                            }
                            break;
                        default:
                            if (Math.floor(jQuery('#subjectPlayer').data('jPlayer').status.currentTime) == jumpToTime) {
                                reset = true;
                            }
                            break;
                    }
                    if(reset) {
                        clearInterval(interval);
                    } else {
                        jumpLink.click();
                    }
                }, 500);

                jQuery(this).find('a.indexJumpLink').click();
            }
        });
    }

    $(".fancybox").fancybox();
    $(".various").fancybox({
        maxWidth    : 800,
        maxHeight   : 600,
        fitToView   : false,
        width       : '70%',
        height      : '70%',
        autoSize    : false,
        closeClick  : false,
        openEffect  : 'none',
        closeEffect : 'none'
    });
    $('.fancybox-media').fancybox({
        openEffect  : 'none',
        closeEffect : 'none',
        width       : '80%',
        height      : '80%',
        fitToView   : true,
        helpers     : {
            media : {}
        }
    });
    $(".fancybox-button").fancybox({
        prevEffect : 'none',
        nextEffect : 'none',
        closeBtn   : false,
        helpers    : {
            title   : { type : 'inside' },
            buttons : {}
        }
    });
    jQuery('#lnkRights').click(function() {
    jQuery('#rightsStatement').fadeToggle(400);
            return false;
    });
    jQuery('#lnkUsage').click(function() {
        jQuery('#usageStatement').fadeToggle(400);
        return false;
    });
});

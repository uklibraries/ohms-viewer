jQuery(function ($) {
    var loaded = false;


    $('#translate-link').click(function (e) {
        var urlIndexPiece = '';
        var re;
        e.preventDefault();
        var toggleAvailability = "";
        if ($('#translate-link').attr('data-toggleAvailable') == 'hide') {
            toggleAvailability = "&t_available=1";
        }
        if ($('#search-type').val() == 'Index') {
            var activeIndexPanel = $('#accordionHolder').accordion('option', 'active');
            if (activeIndexPanel !== false) {
                urlIndexPiece = '&index=' + activeIndexPanel;
            }
        }
        if ($('#translate-link').attr('data-lang') == $('#translate-link').attr('data-linkto')) {
            re = /&translate=(.*)/g;
            location.href = location.href.replace(re, '') + '&time=' + Math.floor(jQuery('#subjectPlayer').data("jPlayer").status.currentTime) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
        } else {
            re = /&time=(.*)/g;
            location.href = location.href.replace(re, '') + '&translate=1&time=' + Math.floor(jQuery('#subjectPlayer').data("jPlayer").status.currentTime) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
        }
    });

    if ($('#subjectPlayer')[0]) {
        var screenSize = $('body').width();
        var padding = 30;
        var width = 500;
        var height = 280;
        if (screenSize < 530) {
            width = $('body').width() - padding;
            height = ($('body').width() - padding) * 0.56;
        }
        jQuery.jPlayer.timeFormat.showHour = true;
        jQuery("#subjectPlayer").jPlayer({
            ready: function () {

                playerData = {};
                playerData.title = "Player";
                if (jQuery('#subjectPlayer').attr('clip-format') == 'video') {
                    playerData.poster = "/imgs/video_placeholder.jpg";
                    $('#subjectPlayer video').attr('playsinline', true);
                }
                playerData[jQuery('#subjectPlayer').attr('rel')] = jQuery('#subjectPlayer').attr('href');
                if ('time' in vars) {
                    jQuery(this).jPlayer("setMedia", playerData).jPlayer("play", vars.time * 1);
                } else {
                    jQuery(this).jPlayer("setMedia", playerData).jPlayer("stop");
                }
                $('#jp_poster_0').on('click', function () {
                    jQuery('#subjectPlayer').jPlayer("play");
                });

            },
            loadstart: function () {
                jQuery('#jp_container_1').show();
                jQuery('#jp-loading-graphic').show();
            },
            playing: function () {
                jQuery('#jp-loading-graphic').hide();
            },
            timeupdate: function (event) { // 4Hz
                if (exhibitMode) {
                    if (event.jPlayer.status.currentTime > endAt && endAt != null) {
                        $(this).jPlayer('pause');
                        exhibitIndex.trigger('click');
                        endAt = null;
                        exhibitIndex = null;
                        
                    }
                }
            },
            swfPath: "../swf/jplayer",
            solution: 'html, flash',
            supplied: jQuery('#subjectPlayer').attr('rel'),

            size: {
                width: "100%",
                height: "100%"
            }
        });
        $('#subjectPlayer > img:not([alt])').attr('alt', 'Player');

    }

    $('body').on('click', 'a.jumpLink', function (e) {
        e.preventDefault();
        jQuery('#subjectPlayer').jPlayer("play", $(e.target).data('timestamp') * 60);
    });
    $('body').on('click', 'a.indexJumpLink', function (e) {
        e.preventDefault();
        try {
            endAt = $(this).parent().parent().next().next().find('.indexJumpLink').data('timestamp');
            exhibitIndex = $(this).parents('div').prev();
        } catch (e) {
            endAt = null;
        }
        jQuery('#subjectPlayer').jPlayer("play", $(e.target).data('timestamp'));
        $('body').animate({scrollTop: 0}, 800);
    });

});

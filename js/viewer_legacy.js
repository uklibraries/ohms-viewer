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
        jQuery.jPlayer.timeFormat.showHour = true;
        jQuery("#subjectPlayer").jPlayer({
            ready: function () {
                playerData = {};
                playerData.title = "Player";
                playerData[jQuery('#subjectPlayer').attr('rel')] = jQuery('#subjectPlayer').attr('href');
                if ('time' in vars) {
                    jQuery(this).jPlayer("setMedia", playerData).jPlayer("play", vars.time * 1);
                } else {
                    jQuery(this).jPlayer("setMedia", playerData).jPlayer("play");
                }
            },
            loadstart: function () {
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
            swfPath: "swf",
            supplied: jQuery('#subjectPlayer').attr('rel'),
        });
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

//Brightcove code ======================
var bcExp;
var modVP;
var modExp;
var modCon;

function onTemplateLoaded(experienceID) {
    bcExp = brightcove.getExperience(experienceID);
    modVP = bcExp.getModule(APIModules.VIDEO_PLAYER);
    modExp = bcExp.getModule(APIModules.EXPERIENCE);
    modCon = bcExp.getModule(APIModules.CONTENT);
    modExp.addEventListener(BCExperienceEvent.TEMPLATE_READY, onTemplateReady);
    modExp.addEventListener(BCExperienceEvent.CONTENT_LOAD, onContentLoad);
    modCon.addEventListener(BCContentEvent.VIDEO_LOAD, onVideoLoad);
}

function onTemplateReady(evt) {
    //Empty
}

function onContentLoad(evt) {
    var currentVideo = modVP.getCurrentVideo();
    modCon.getMediaAsynch(currentVideo.id);
}

function onVideoLoad(evt) {
    if (modVP !== undefined) {
        modVP.loadVideo(evt.video.id);
    }
}

function goToAudioChunk(key, chunksize) {
    if (modVP !== undefined) {
        modVP.seek(key * chunksize * 60);
    }
}

function goToSecond(key) {
    if (modVP !== undefined) {
        modVP.seek(key);
    }
}

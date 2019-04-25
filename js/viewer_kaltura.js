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
            location.href = location.href.replace(re, '') + '&time=' + Math.floor(kdp.evaluate('{video.player.currentTime}')) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
        } else {
            re = /&time=(.*)/g;
            location.href = location.href.replace(re, '') + '&translate=1&time=' + Math.floor(parent.kdp.evaluate('{video.player.currentTime}')) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
        }
    });

    $('body').on('click', 'a.jumpLink', function (e) {
        e.preventDefault();
        var target = $(e.target);
        kdp.sendNotification("doPlay");
        kdp.sendNotification("doSeek", target.data('timestamp') * 60);
    });
    $('body').on('click', 'a.indexJumpLink', function (e) {
        e.preventDefault();
        var target = $(e.target);
        try {
            endAt = $(this).parent().parent().next().next().find('.indexJumpLink').data('timestamp');
            exhibitIndex = $(this).parents('div').prev();
        } catch (e) {
            endAt = null;
        }
        kdp.sendNotification("doPlay");
        kdp.sendNotification("doSeek", target.data('timestamp'));
        $('body').animate({scrollTop : 0},800);
    });



});

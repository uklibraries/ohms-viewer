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
            location.href = location.href.replace(re, '') + '&time=' + Math.floor(player.getCurrentTime()) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
        } else {
            re = /&time=(.*)/g;
            location.href = location.href.replace(re, '') + '&translate=1&time=' + Math.floor(player.getCurrentTime()) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
        }
    });

    $('body').on('click', 'a.jumpLink', function (e) {
        e.preventDefault();
        var target = $(e.target);
        if (player !== undefined && player.playVideo !== undefined) {
            player.playVideo();
            player.seekTo(target.data('timestamp') * 60);
        }
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
        if (player !== undefined && player.playVideo !== undefined) {
            player.playVideo();
            player.seekTo(target.data('timestamp'));
        }

        $('body').animate({scrollTop : 0},800);

    });

    function responsiveYoutubePlayer() {
        padding = 30;
        width = $('body').width();
        height = ($('body').width() - padding) * 0.56;
        if (width < 530) {
            player.setSize(width - padding, height);
        } else {
            player.setSize(500, 280);

        }
    }
    $(window).resize(function () {

        responsiveYoutubePlayer();
    });


});

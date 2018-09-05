jQuery(function ($) {
    var loaded = false;



    $('#translate-link').click(function (e) {
        var urlIndexPiece = '';
        var re;
        e.preventDefault();
        if ($('#search-type').val() == 'Index') {
            var activeIndexPanel = $('#accordionHolder').accordion('option', 'active');
            if (activeIndexPanel !== false) {
                urlIndexPiece = '&index=' + activeIndexPanel;
            }
        }
        var pos = parent.widget.getCurrentTime();
        if ($('#translate-link').attr('data-lang') == $('#translate-link').attr('data-linkto')) {
            re = /&translate=(.*)/g;
            location.href = location.href.replace(re, '') + '&time=' + Math.floor(pos) + '&panel=' + $('#search-type').val() + urlIndexPiece;
        } else {
            re = /&time=(.*)/g;
            location.href = location.href.replace(re, '') + '&translate=1&time=' + Math.floor(pos) + '&panel=' + $('#search-type').val() + urlIndexPiece;
        }
    });

    $('body').on('click', 'a.jumpLink', function (e) {
        e.preventDefault();
        var target = $(e.target);
        curPlayPoint = 0;
        curPlayPoint = target.data('timestamp');
        widget('set_offset',{'offset':curPlayPoint*60})
        widget('play');
    });
    $('body').on('click', 'a.indexJumpLink', function (e) {
        e.preventDefault();
        var target = $(e.target);
        try {
            endAt = $(this).parent().parent().next().next().find('.indexJumpLink').data('timestamp');
        } catch (e) {
            endAt = null;
        }
        curPlayPoint = 0;
        curPlayPoint = target.data('timestamp');
        widget('set_offset',{'offset':curPlayPoint})
        widget('play');
        $('body').animate({scrollTop: 0}, 800);
    });

});

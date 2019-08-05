var vars = [];
var hash;
var q = document.URL.split('?')[1];
if (q !== undefined) {
    q = q.split('&');
    for (var i = 0; i < q.length; i++) {
        hash = q[i].split('=');
        vars.push(hash[1]);
        vars[hash[0]] = hash[1];
    }
}

var preg_quote = function (str) {
    return (str + '').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/gi, "\\$1");
};

var prevSearch = {
    keyword: '',
    highLines: []
};

var prevIndex = {
    keyword: '',
    matches: []
};

//var clearSearchResults = function (e) {
//  if ((e.type == "keypress" && e.which == 13) || e.type == "click") {
//    e.preventDefault();
//    $('#search-results').empty();
//    $('#kw').val('');
//    $('span.highlight').removeClass('highlight');
//    $("#kw").prop('disabled', false);
//    $("#submit-btn").css("display", "inline-block");
//    $("#clear-btn").css("display", "none");
//  }
//};

var activeIndex = false;

if ('index' in vars) {
    activeIndex = parseInt(vars.index);
    if (isNaN(activeIndex)) {
        activeIndex = false;
    }
}
var firstTogglePerformed = false;
jQuery(document).ready(function ($) {
    // Added by AVPreserve - Start
    ts = new toggleSwitch();
    ts.initialize();
    // Added by AVPreserve - End
    $('#kw').on('focus', function (e) {
        if ($('#kw').val() === 'Keyword') {
            $('#kw').toggleClass('kw-entry');
            $('#kw').val('');
        }
    });
    $('#kw').on('blur', function (e) {
        if ($('#kw').val() === '') {
            $('#kw').toggleClass('kw-entry');
            $('#kw').val('Keyword');
        }
    });

    $('#kw').focus();

    $('#accordionHolder').accordion({
        autoHeight: false,
        collapsible: true,
        active: activeIndex,
        fillSpace: false,
        change: function (e, ui) {
            if (ui.newHeader.length > 0) {
                $('#index-panel').scrollTo($('.ui-state-active'), 800, {
                    easing: 'easeInOutCubic'
                });
            }
        }
    });
    $('.show-info').bind('click', function () {
        $('.show-info').hide();
        $('.hide-info').show();
        $('.detail-metadata').slideDown();


    });
    $('.hide-info').bind('click', function () {
        $('.hide-info').hide();
        $('.show-info').show();
        $('.detail-metadata').slideUp();

    });

    $('.search-hide-info').bind('click', function () {
        $(this).toggle();
        $('.search-show-info').toggle();
        $('.search-content').toggleClass('hide');
    });
    $('.search-show-info').bind('click', function () {
        $(this).toggle();
        $('.search-hide-info').toggle();
        $('.search-content').toggleClass('hide');

    });

    $(".jp-next").bind('click', function () {
        var currentProgress = Math.floor(jQuery('#subjectPlayer').data("jPlayer").status.currentTime);
        var futureProgress = currentProgress + 15;
        if (futureProgress <= 0) {
            $("#subjectPlayer").jPlayer("pause", 0);
        } else {
            jQuery('#subjectPlayer').jPlayer("play", futureProgress);
        }
    });
    $(".jp-previous").bind('click', function () {
        var currentProgress = Math.floor(jQuery('#subjectPlayer').data("jPlayer").status.currentTime);
        var futureProgress = currentProgress - 15;
        if (futureProgress <= 0) {
            $("#subjectPlayer").jPlayer("pause", 0);
        } else {
            $("#subjectPlayer").jPlayer("play", futureProgress);
        }
    });

    $('#print-pdf').click(function () {

        var to_print = '';
        if ($('#toggle_switch').is(":checked")) {
            to_print = 'index'
        }

        if (!$('#toggle_switch').is(":checked")) {
            to_print = 'transcript'
        }
        if (to_print === "index") {
            $('.ui-accordion-content').show();
            $('.ui-state-active').addClass('ui-state-default').removeClass('ui-state-active');
            window.print();
            $('.ui-accordion-content').hide();
        } else {
            window.print();
        }
    });

});
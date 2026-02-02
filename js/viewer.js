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

    $('.accordionHolder').accordion({
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

    $('#accordionHolderSearch').accordion({
        header: '> h3',
        collapsible: true,
        active: false,
        autoHeight: false,
    });

    



    // Disable default header clicks
    $('#accordionHolderSearch h3').off('click');

    // Toggle only on span click
    $('#accordionHolderSearch').on('click', '.toggle-span, .ui-icon-triangle-1-e, .ui-icon-triangle-1-s', function (e) {
        const $header = $(this).closest('h3');
        const index = $('#accordionHolderSearch h3').index($header);
        const $accordion = $('#accordionHolderSearch');
        const current = $accordion.accordion('option', 'active');

        $accordion.accordion('option', 'active', current === index ? false : index);
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


    $('#print-pdf').click(function () {

        var to_print = '';
        if ($('#toggle_switch').is(":checked")) {
            to_print = 'index';
        }

        if (!$('#toggle_switch').is(":checked")) {
            to_print = 'transcript';
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
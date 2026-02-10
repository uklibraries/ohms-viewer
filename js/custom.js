
function Viewer() {

    this.initialize = function () {
        const leftTab = localStorage.getItem("leftTab");
        const rightTab = localStorage.getItem("rightTab");

        setTimeout(function () {
            if (leftTab !== null && leftTab !== "") {
                $('a[href="' + leftTab + '"]').trigger("click");
                localStorage.removeItem("leftTab");
            }
            if (rightTab !== null && rightTab !== "") {
                $('a[href="' + rightTab + '"]').trigger("click");
                localStorage.removeItem("rightTab");
            }
        }, 500);




        $('.tab-left-tab').click(function () {
            currentLeftTab = $(this).attr('href');

        });
        $('.tab-right-tab').click(function () {
            currentRightTab = $(this).attr('href');

        });
        $('.refreshPage').click(function () {
            $('a[href="#about-tab-1"]').trigger("click");
            $('a[href="#index-tab-2"]').trigger("click");
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
        let url = new URL(window.location.href);
        let external = '';
        if (url.searchParams.has('external')) {
            external = '&external=true';
        }
        $(".printCustom").click(function () {
            window.location.href = "viewer.php?action=pdf&cachefile=" + cachefile + external + "";
        });
        $(".printCustomMobile").click(function () {
            window.open("viewer.php?action=pdf&cachefile=" + cachefile + external + "", '_blank');
        });

        $('.about-attributes').on('click', function (e) {
            e.preventDefault(); // prevent default anchor behavior

            $(this)
                    .closest('div')     // find parent container
                    .find('p')          // target the paragraph
                    .slideToggle();     // slide up/down toggle
        });
        switchViews();
        bindOldFootNotes();

        let indexJS = new IndexJS();

        indexJS.initialize();

    };
    this.footerNotes = function (event) {
        bindFootNoteHover(event)
    }

    const switchViews = function () {
        $(".toggle-sides").click(function () {
            let activeLeft, activeLeftLink, activeRight, activeRightLink = '';
            if ($("#custom-tabs-left ul li").hasClass("ui-tabs-selected ui-state-active")) {
                activeLeft = $("#custom-tabs-left ul li.ui-tabs-selected.ui-state-active a");
                activeLeftLink = activeLeft.attr("href");

            }
            if ($("#custom-tabs-right ul li").hasClass("ui-tabs-selected ui-state-active")) {
                activeRight = $("#custom-tabs-right ul li.ui-tabs-selected.ui-state-active a");
                activeRightLink = activeRight.attr("href");

            }
            if (activeLeftLink == '#about-tab-1')
                return;

            let switchLeftToRight = activeLeftLink.replace(/-1/g, "-2");
            let switchRightToLeft = activeRightLink.replace(/-2/g, "-1");
            $('a[href="' + switchLeftToRight + '"]').trigger("click");
            $('a[href="' + switchRightToLeft + '"]').trigger("click");


        });
    };
    const bindOldFootNotes = function () {
        $('.footnoteTooltip').each(function (index, element) {
            let footnoteID = $(element).data('index');
            let footnoteAttrId = $(element).attr("id");
            let footnoteHtml = $('#' + footnoteID).parent().children('span').html();

            $(element).attr("data-tooltip", footnoteHtml);
            footNotesTooltip('#transcript-tab-1', footnoteAttrId, footnoteHtml);
            footNotesTooltip('#transcript-tab-2', footnoteAttrId, footnoteHtml);
        });
        bindFootNoteHover("bind");
    }
    const bindFootNoteHover = function (state) {
        if (state == "bind") {
            $(".footnote-ref").bind("hover",
                    function () {
                        var footnoteHtmlLength = $(this).find('.footnoteTooltip').attr("data-tooltip").length;
                        width = footnoteHtmlLength * 50 / 100;
                        if (footnoteHtmlLength > 130) {
                            $('head').append("<style>.tooltip{ width: " + width + "px }</style>");
                        } else {
                            $('head').append("<style>.tooltip{ width: 130px; }</style>");
                        }
                    }
            );
        } else if (state == "unbind") {
            $(".footnote-ref").unbind("hover");
        }
    }
    const footNotesTooltip = function (tab, element, footnoteHtml) {

        new Tooltip($(tab + " #" + element), {
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
                    boundariesElement: $('#transcript-panel')
                }
            }
        });

    }
    const activateTruncateText = function () {
        document.querySelectorAll('.truncate').forEach(el => {

            if (el.scrollHeight > el.clientHeight) {
                new Tooltip(el, {
                    title: el.textContent.trim(),
                    placement: 'top',
                    trigger: 'hover',
                    html: false,
                    offset: 10
                });
            }
        });
        document.querySelectorAll('.truncate-collection').forEach(el => {

            if (el.scrollHeight > el.clientHeight) {
                new Tooltip(el, {
                    title: el.textContent.trim(),
                    placement: 'top',
                    trigger: 'hover',
                    html: false,
                    offset: 10
                });
            }
        });
    };
}

function IndexJS() {
    this.initialize = function () {
        $('#clear-btn').on('click', clearSearchResults);
        bindEvents();
        activateTranscriptPopup();
        $('#submit-btn').off('click').on('click', getIndexResults);
        $('#kw').off('keypress').on('keypress', getIndexResults);
        resetSearch();

    };

    const bindEvents = function () {
        $('a.indexSegmentLink').on('click', function (e) {
            e.preventDefault();
            $(this).parent().nextAll('.segmentLink').first().slideToggle();
            return false;
        });
        $('.segmentLinkTextBox').on('click', function () {
            $(this).select();
        });
        $('.copyButtonViewer').on('click', function () {
            var text = $(this).prev().val();
            copyToClipboard(text);
            $(this).attr('value', 'Copied');
            var button = $(this);
            setTimeout(function () {
                button.attr('value', 'Copy');
            }, 1500);
        });
        switchIndexToTranscript();
        $('.footnoteLink').click(function (e) {
            e.preventDefault();
            let container;
            let transcriptTab;
            if ($(this).closest('.right-side').length) {
                container = $('.right-side-inner');
                transcriptTab = '#transcript-tab-2';

            } else {
                container = $('.left-side');
                transcriptTab = '#transcript-tab-1';
            }
            $('a[href="' + transcriptTab + '"]').trigger("click");
            $('html, body').animate({scrollTop: 0}, 100);
            let linkTo = $(this).attr('href').replace('#', '.marker_');
            setTimeout(function () {
                scrollTo = $(transcriptTab + " " + linkTo);
                container.animate({
                    scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
                });
            }, 250);

        });

    };
    const  copyToClipboard = function (val) {
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = val;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
    }
    const activateTranscriptPopup = function () {
        $('.info-circle').each(function (index, element) {

            var timePoint = $("." + element.id).data("time-point");
            var id = $("." + element.id).data("marker-counter");
            var indexTitle = $("." + element.id).data("index-title");
            var anchorHtml = "<div class='info-toggle transcript-info-tipped' data-id='" + id + "' >Segment: <b>" + indexTitle + "</b> " + timePoint + " </div>";
            Tipped.create('.' + element.id, anchorHtml, {
                size: 'large',
                radius: true,
                position: 'right'
            });
        });
        //, .transcript-info-tipped
        $(document).on("click", ".transcript-info", function (e) {
            let id = $(this).data('id');
            $('.tpd-tooltip').hide();
            let container;
            let indexTab;
            if ($(this).closest('.right-side').length) {
                container = $('.left-side');
                indexTab = '#index-tab-1';

            } else if ($('.right-side').is(':visible')) {
                indexTab = '#index-tab-2';
                container = $('.right-side-inner');

            } else {
                container = $('.left-side');
                indexTab = '#index-tab-1';
            }
            $('a[href="' + indexTab + '"]').trigger("click");
            $('html, body').animate({scrollTop: 0}, 100);
            setTimeout(function () {
                var currentIndex = $(indexTab + ' .accordionHolder').accordion('option', 'active');
                if (currentIndex != id || currentIndex === false) {
                    jQuery(indexTab + ' .accordionHolder').accordion({active: id});
                    jQuery(indexTab + ' .accordionHolder-alt').accordion({active: id});
                }
            }, 250);
        });
    }
    const switchIndexToTranscript = function () {
        $('.mapIndexTranscript').click(function () {
            let type = $(this).data('type');
            let id = $(this).data('id');
            let container;
            let transcriptTab;
            if ($(this).closest('.right-side').length) {
                container = $('.left-side');
                transcriptTab = '#transcript-tab-1';

            } else if ($('.right-side').is(':visible')) {
                transcriptTab = '#transcript-tab-2';
                container = $('.right-side-inner');

            } else {
                container = $('.left-side');
                transcriptTab = '#transcript-tab-1';
            }
            $('a[href="' + transcriptTab + '"]').trigger("click");
            $('html, body').animate({scrollTop: 0}, 100);
            setTimeout(function () {
                scrollTo = $(transcriptTab + ">.transcript-panel>.info_trans_" + id);
                container.animate({
                    scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
                });
            }, 250);
        });
    };

    var getIndexResults = function (e) {
        var isTranslate = false;

        if ((e.type == "keypress" && e.which == 13) || e.type == "click") {
            e.preventDefault();
            var kw = $('#kw').val();
            $('span.highlight').removeClass('highlight');
            if (kw !== '') {
                if (prevIndex.matches.length !== 0) {
                    $.each(prevSearch.highLines, function (key, val) {
                        var section = $('#link' + val);
                        var synopsis = $('#tp_' + val).parent();
                        section.find('.highlight').contents().unwrap();
                        synopsis.find('.highlight').contents().unwrap();
                    });
                }
                if (document.URL.search('translate=1') != -1) {
                    isTranslate = true;
                }
                url = new URL(window.location.href);
                let external = '';
                if (url.searchParams.has('external')) {
                    external = '&external=true';
                }
                $(".index_paginate").html('');
                $(".index_paginate_info").html('');
                $("#kw").prop('disabled', true);
                $("#submit-btn").css("display", "none");
                $("#clear-btn").css("display", "inline-block");
                $.getJSON('viewer.php?action=index' + external + '&cachefile=' + cachefile + '&kw=' + kw + (isTranslate ? '&translate=1' : ''), function (data) {
                    var matches = [];
                    $('.index-search-results').empty();
                    $('#accordionHolderSearch').removeClass('d-none');
                    if (data.matches.length === 0) {
                        $('<ul/>').addClass('error-msg').html('<li>No results found.</li>').appendTo('.index-search-results');
                        $('.index_count').addClass('d-none');
                    } else {
                        $('.index_count').text(data.matches.length).removeClass('d-none');

                        prevSearch.keyword = data.keyword;
                        $.each(data.matches, function (key, val) {
                            matches.push('<li><a class="index-search-result search-result" href="#" data-linenum="' + val.time + '">' + val.shortline + '</a></li>');
                            prevIndex.matches.push(val.linenum);
                            var section = $('.index_link' + val.time);
                            var synopsis = $('a[name="tp_' + val.time + '"]').parent();
                            var re = new RegExp('(' + preg_quote(data.keyword) + ')', 'gi');
                            section.each(function () {
                                $(this).html($(this).text().replace(re, "<span class=\"highlight\">$1</span>"))
                            })
                            synopsis.find('span').each(function () {
                                $(this).html($(this).text().replace(re, "<span class=\"highlight\">$1</span>"));
                            });
                        });
                        $('<ul/>').addClass('nline').html(matches.join('')).appendTo('.index-search-results');
                        $('a.index-search-result').on('click', function (e) {
                            e.preventDefault();
                            var linenum;
                            var lineTarget;
                            let indexTab = '#index-tab-1';
                            let container = $('.left-side');
                            lineTarget = $(e.target);
                            linenum = lineTarget.data("linenum");
                            if ($('.right-side').is(':visible')) {
                                indexTab = '#index-tab-2';
                                container = $('.right-side-inner');
                            }
                            $('a[href="' + indexTab + '"]').trigger("click");
                            var line = $(indexTab + ' .index_link' + linenum);
                            $('html, body').animate({scrollTop: 0}, 100);
                            setTimeout(function () {
                                line.click();
                                let scrollTo = line;
                                container.animate({
                                    scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
                                }, 100, 'swing');

                            }, 250);

                        });
                        pagination('index');
                    }
                });
            }
            getSearchResults(e);
        }

    };
    var getSearchResults = function (e) {
        var isTranslate = false;

//        if ((e.type == "keypress" && e.which == 13) || e.type == "click") {
//            e.preventDefault();
        var kw = $('#kw').val();
        if (kw !== '') {
            if (prevSearch.highLines.length !== 0) {
                $.each(prevSearch.highLines, function (key, val) {
                    var line = $('#line_' + val);
                    var lineText = line.html();
                    line.find('.highlight').contents().unwrap();
                });
            }
            if (document.URL.search('translate=1') != -1) {
                isTranslate = true;
            }
            url = new URL(window.location.href);
            let external = '';
            if (url.searchParams.has('external')) {
                external = '&external=true';
            }
            $(".transcript_paginate").html('');
            $(".transcript_paginate_info").html('');

            $.getJSON('viewer.php?action=search' + external + '&cachefile=' + cachefile + '&kw=' + kw + (isTranslate ? '&translate=1' : ''), function (data) {
                var matches = [];
                $('.transcript-search-results').empty();

                $('.transcript_count').addClass('d-none');
                if (data.matches.length === 0) {
                    $('<ul/>').addClass('error-msg').html('<li>No results found.</li>').appendTo('.transcript-search-results');
                } else {
                    $('.transcript_count').text(data.matches.length).removeClass('d-none');

                    prevSearch.keyword = data.keyword;
                    $.each(data.matches, function (key, val) {
                        matches.push('<li><a class="search-result transcript-search-result" href="#" data-linenum="' + val.linenum + '">' + (key + 1) + ". " + val.shortline + '</a></li>');
                        prevSearch.highLines.push(val.linenum);
                        var line = $('.transcript_line_' + val.linenum);

                        if (/^((?!chrome|android).)*safari/i.test(navigator.userAgent) || navigator.userAgent.search("Firefox")) {
                            var re = new RegExp("(?![^<>]*(([\/\"']|]]|\b)>))(" + preg_quote(data.keyword) + ')', 'gi');
                        } else {
                            var re = new RegExp('(?<!</?[^>]*|&[^;]*)(' + preg_quote(data.keyword) + ')', 'gi');
                        }

                        var htmlArray = [];
                        line.find(".footnote-ref").each(function (index) {
                            htmlArray.push($(this).html());
                            $(this).html("[" + index + "]");
                        });

                        line.each(function () {
                            let lineText = $(this).html();
                            $(this).html(lineText.replace(re, function (str) {
                                return "<span class=\"highlight\">" + str + "</span>";
                            }));
                        });

                        line.find(".footnote-ref").each(function (index) {
                            $(this).html(htmlArray[index]);
//                            activatePopper($(this).find(".footnoteTooltip").attr("id"));
                        });
                        let viewer = new Viewer();
                        viewer.footerNotes('unbind');
                        viewer.footerNotes('bind');


                    });
                    $('<ul/>').addClass('nline').html(matches.join('')).appendTo('.transcript-search-results');
                    $('a.transcript-search-result').on('click', function (e) {
                        e.preventDefault();
                        var linenum;
                        var lineTarget;
                        let transcriptTab = '#transcript-tab-1';
                        let container = $('.left-side');
                        lineTarget = $(this);
                        var linenum;
//                        if (e.target.tagName == 'SPAN') {
//                            linenum = lineTarget.parent().data("linenum");
//                        } else {
                        linenum = lineTarget.data("linenum");
//                        }

                        if ($('.right-side').is(':visible')) {
                            transcriptTab = '#transcript-tab-2';
                            container = $('.right-side-inner');
                        }
                        $('a[href="' + transcriptTab + '"]').trigger("click");

                        var line = $(transcriptTab + ' .transcript_line_' + linenum);
                        $('html, body').animate({scrollTop: 0}, 100);
                        setTimeout(function () {
                            line.click();
                            let scrollTo = line;
                            container.animate({
                                scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
                            }, 100, 'swing');

                        }, 250);

                    });
                    pagination('transcript');
                }
            });
        }
//        }
    };
    var resetSearch = function () {
        kwval = $('#kw').val();
        if (kwval != 'Keyword' && kwval != '') {

            $('#search-results').empty();
            $('#accordionHolderSearch').accordion('option', 'active', false)
            $('.index_count').addClass('d-none');
            $('.transcript_count').addClass('d-none');
            $('.index-search-results').empty();
            $('.transcript-search-results').empty();
            $('#accordionHolderSearch').addClass('d-none');
            $("#kw").prop('disabled', false);
            $('span.highlight').removeClass('highlight');
            $("#submit-btn").css("display", "inline-block");
            $("#clear-btn").css("display", "none");
        }

    }
    var clearSearchResults = function (e) {
        if ((e.type == "keypress" && e.which == 13) || e.type == "click") {
            e.preventDefault();
            $('#search-results').empty();
            $('#kw').val('');
            $('span.highlight').each(function () {
                var txt = $(this).text();
                $(this).replaceWith(txt);
            });
            $('#accordionHolderSearch').accordion('option', 'active', false)
            $('.index_count').addClass('d-none');
            $('.transcript_count').addClass('d-none');
            $('.index-search-results').empty();
            $('.transcript-search-results').empty();
            $('#accordionHolderSearch').addClass('d-none');
            $('span.highlight').removeClass('highlight');
            $("#kw").prop('disabled', false);
            $("#submit-btn").css("display", "inline-block");
            $("#clear-btn").css("display", "none");
        }
    };

    var pagination = function (type) {

        var pageParts = $('.' + type + "-search-results .nline li");
        var numPages = pageParts.length;
        var perPage = 5;
        if (numPages <= 5) {
            $("." + type + "_paginate_info").text("Showing 1 - " + numPages + " of " + numPages);
        } else {
            $("." + type + "_paginate_info").text("Showing 1 - " + perPage + " of " + numPages);
        }

        pageParts.slice(perPage).hide();
//            $("." + type + "_paginate_info").text("Showing 1 - " + perPage + " of " + numPages);
        $("." + type + "_paginate").pagination({
            items: numPages,
            itemsOnPage: perPage,
            displayedPages: 0,
            pages: 0,
            edges: 0,
            prevText: "<img src='./imgs/arrow-square.webp' alt='Previous'>",
            nextText: "<img src='./imgs/arrow-square.webp' alt='Next'>",
            cssStyle: "compact-theme",
            onPageClick: function (pageNum) {
                var start = perPage * (pageNum - 1);
                var end = start + perPage;
                pageParts.hide().slice(start, end).show();
                var ending = end;
                var starting = start;
                if (end > numPages) {
                    ending = numPages;
                }
                if (start == 0) {
                    starting = 1;
                }
                $("." + type + "_paginate_info").text("Showing " + starting + " - " + ending + " of " + numPages);
            }
        });
//        }

    }
}
        
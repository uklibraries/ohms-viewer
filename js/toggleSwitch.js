

function toggleSwitch() {
    var viewName = ['Transcript', 'Index'];
    this.initialize = function () {
        var view = $('#search-type').val();
        if (typeof viewName[view] != 'undefined') {
            eval(viewName[view] + 'View')();
        }
        $('#clear-btn').on('click', clearSearchResults);
        $('#toggle_switch').bind('click', function () {
            if ($(this).is(":checked")) {
                IndexView();
            } else {
                TranscriptView();
            }

        });
    }
    var TranscriptView = function () {
        $('#search-type').val(0);
        $('#index-panel').hide();
        $('#transcript-panel').show();
        $('.user_notes').show();
        $('#search-legend .search-label').html('Search this Transcript');
        $('#submit-btn').off('click').on('click', getSearchResults);
        $('#kw').off('keypress').on('keypress', getSearchResults);
        if (!firstTogglePerformed) {
            $(".index-circle").each(function () {
                var indexTime = $(this).data("index-time");
                if ($("#info_trans_" + indexTime).length <= 0) {
                    $(this).hide();
                }
            });
            $(".info-circle").each(function () {
                var outerTop = $(this).offset().top;
                var outerId = this.id;
                $(".info-circle").each(function () {
                    var innerTop = $(this).offset().top;
                    var innerId = this.id;
                    if (innerId != outerId) {
                        if (outerTop == innerTop) {
                            $("#" + innerId).css("margin-top", "18px");
                        }
                    }
                });

            });
        }
        firstTogglePerformed = true;
        resetSearch();
    }
    var IndexView = function () {
        $('#search-type').val(1);
        $('.user_notes').show();
        $('#transcript-panel').hide();
        $('#index-panel').show();
        $('#search-legend .search-label').html('Search this Index');
        $('#submit-btn').off('click').on('click', getIndexResults);
        $('#kw').off('keypress').on('keypress', getIndexResults);
//        $('#index-panel').fadeIn();
        resetSearch();
    }
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
                $.getJSON('viewer.php?action=index&cachefile=' + cachefile + '&kw=' + kw + (isTranslate ? '&translate=1' : ''), function (data) {
                    var matches = [];
                    $('#search-results').empty();
                    if (data.matches.length === 0) {
                        $('<ul/>').addClass('error-msg').html('<li>No results found.</li>').appendTo('#search-results');
                    } else {
                        $("#kw").prop('disabled', true);
                        $("#submit-btn").css("display", "none");
                        $("#clear-btn").css("display", "inline-block");
                        prevSearch.keyword = data.keyword;
                        $.each(data.matches, function (key, val) {
                            matches.push('<li><a class="search-result" href="#" data-linenum="' + val.time + '">' + val.shortline + '</a></li>');
                            prevIndex.matches.push(val.linenum);
                            var section = $('#link' + val.time);
                            var synopsis = $('a[name="tp_' + val.time + '"]').parent();
                            var re = new RegExp('(' + preg_quote(data.keyword) + ')', 'gi');
                            section.html(section.text().replace(re, "<span class=\"highlight\">$1</span>"));
                            synopsis.find('span').each(function () {
                                $(this).html($(this).text().replace(re, "<span class=\"highlight\">$1</span>"));
                            });
                        });
                        $('<ul/>').addClass('nline').html(matches.join('')).appendTo('#search-results');
                        $('a.search-result').on('click', function (e) {
                            e.preventDefault();
                            var linenum;
                            var lineTarget;
                            lineTarget = $(e.target);
                            linenum = lineTarget.data("linenum");
                            var line = $('#link' + linenum);
                            $('#link' + linenum).click();
                            $('#index-panel').scrollTo(line, 800, {
                                easing: 'easeInSine'
                            });
                        });
                        pagination();
                    }
                });
            }
        }
    };
    var getSearchResults = function (e) {
        var isTranslate = false;

        if ((e.type == "keypress" && e.which == 13) || e.type == "click") {
            e.preventDefault();
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
                $.getJSON('viewer.php?action=search&cachefile=' + cachefile + '&kw=' + kw + (isTranslate ? '&translate=1' : ''), function (data) {
                    var matches = [];
                    $('#search-results').empty();
                    if (data.matches.length === 0) {
                        $('<ul/>').addClass('error-msg').html('<li>No results found.</li>').appendTo('#search-results');
                    } else {
                        $("#kw").prop('disabled', true);
                        $("#submit-btn").css("display", "none");
                        $("#clear-btn").css("display", "inline-block");
                        prevSearch.keyword = data.keyword;
                        $.each(data.matches, function (key, val) {
                            matches.push('<li><a class="search-result" href="#" data-linenum="' + val.linenum + '">' + (key + 1) + ". " + val.shortline + '</a></li>');
                            prevSearch.highLines.push(val.linenum);
                            var line = $('#line_' + val.linenum);
                            var lineText = line.html();
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

                            lineText = $('#line_' + val.linenum).html();
                            line.html(lineText.replace(re, function (str) {
                                return "<span class=\"highlight\">" + str + "</span>";
                            }));
                            line.find(".footnote-ref").each(function (index) {
                                $(this).html(htmlArray[index]);
                                activatePopper($(this).find(".footnoteTooltip").attr("id"));
                            });
                            footnoteHover("unbind");
                            footnoteHover("bind");
                        });
                        $('<ul/>').addClass('nline').html(matches.join('')).appendTo('#search-results');
                        $('a.search-result').on('click', function (e) {
                            e.preventDefault();
                            var linenum;
                            if (e.target.tagName == 'SPAN') {
                                linenum = $(e.target).parent().data("linenum");
                            } else {
                                linenum = $(e.target).data("linenum");
                            }
                            var line = $('#line_' + linenum);
                            $('#transcript-panel').scrollTo(line, 800, {
                                easing: 'easeInSine'
                            });
                            $('#transcript-panel-alt').scrollTo(line, 800, {
                                easing: 'easeInSine'
                            });
                        });
                        pagination();
                    }
                });
            }
        }
    };
    var resetSearch = function () {
        kwval = $('#kw').val();
        if (kwval != 'Keyword' && kwval != '') {

            $('#search-results').empty();

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
            $('span.highlight').removeClass('highlight');
            $("#kw").prop('disabled', false);
            $("#submit-btn").css("display", "inline-block");
            $("#clear-btn").css("display", "none");
        }
    };

    var pagination = function () {
        $("#search-results").prepend("<div id=\"paginate\"></div>");
        $("#search-results").prepend("<span id=\"paginate_info\"></span>");
        var pageParts = $(".nline li");
        var numPages = pageParts.length;
        var perPage = 5;
        if (numPages > 5) {
            pageParts.slice(perPage).hide();
            $("#paginate_info").text("Showing 1 - " + perPage + " of " + numPages);
            $("#paginate").pagination({
                items: numPages,
                itemsOnPage: perPage,
                displayedPages: 0,
                pages: 0,
                edges: 0,
                prevText: "<",
                nextText: ">",
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
                    $("#paginate_info").text("Showing " + starting + " - " + ending + " of " + numPages);
                }
            });
        }
    }
}



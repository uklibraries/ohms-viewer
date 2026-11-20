
// How far a scroll-to-word/marker jump needs to clear the frozen chrome on
// top of each pane. Left: the real, current height of the frozen
// header/player/search/tab-bar stack (window.frozenLeftHeight, kept in sync
// by Viewer.initialize below) — that stack's height varies with viewport
// width (the video's aspect-ratio) and whether the header is collapsed, so a
// fixed guess was either too small (content hidden behind it) or too large.
// Right: the tab bar's height, PLUS whatever per-view filter row is also
// frozen right below it on the currently active tab (e.g. "View Data
// Layers" on Transcript) — that row only exists on some views, so it has to
// be measured live rather than assumed away.
window.getFrozenClearance = function (container) {
    if (container && container.hasClass && container.hasClass('right-side-inner')) {
        const nav = document.querySelector('#custom-tabs-right > .ui-tabs-nav');
        let height = nav ? nav.offsetHeight : 54;
        const filterRow = document.querySelector(
                '#custom-tabs-right .ui-tabs-panel:not(.ui-tabs-hide) .data-layers, ' +
                '#custom-tabs-right .ui-tabs-panel:not(.ui-tabs-hide) .browser-filter, ' +
                '#custom-tabs-right .ui-tabs-panel:not(.ui-tabs-hide) .ww_timeline_filter_container'
        );
        if (filterRow) {
            height += filterRow.offsetHeight;
        }
        return height + 12;
    }
    return (window.frozenLeftHeight || 200) + 12;
};

function Viewer() {

    this.initialize = function () {
        const leftTab = localStorage.getItem("leftTab");
        const rightTab = localStorage.getItem("rightTab");

        setTimeout(function () {
            if (leftTab !== null && leftTab !== "") {
                $('a[href="' + leftTab + '"]').trigger("click");
                localStorage.removeItem("leftTab");
            } else if ($('a[href="#index-tab-1"]').length > 0 && $('a[href="#transcript-tab-1"]').length > 0) {
                $('a[href="#index-tab-1"]').trigger("click");
            }

            if (rightTab !== null && rightTab !== "") {
                $('a[href="' + rightTab + '"]').trigger("click");
                localStorage.removeItem("rightTab");
            } else if ($('a[href="#transcript-tab-2"]').length > 0) {
                $('a[href="#transcript-tab-2"]').trigger("click");
            }
        }, 500);

        const innerDiv = document.querySelector('.right-side-inner');
        const headerRight = document.querySelector('.right-side-header');
        // The real tab bar and filter rows are now frozen via CSS sticky
        // (see custom_default.css), so the old scroll-swapped ".scrolled"
        // header is no longer needed.

        // Keep the frozen left-side chrome (header, player, search box, tab bar)
        // stacked: each sticks directly below the previous one while the panel
        // content scrolls underneath.
        const frozenLeft = [
            document.querySelector('#headervid'),
            document.querySelector('.left-side #audio-panel'),
            document.querySelector('.left-side #searchbox-panel'),
            document.querySelector('.left-side #custom-tabs-left')
        ].filter(Boolean);
        if (frozenLeft.length > 1) {
            const syncFrozenLeftOffsets = function () {
                let offset = 0;
                frozenLeft.forEach(function (el) {
                    el.style.top = offset + 'px';
                    offset += el.offsetHeight;
                });
                // Exposed so the left map (visualization.js) can keep its
                // popups clear of this frozen stack when they open near the
                // top of the map.
                window.frozenLeftHeight = offset;
            };
            syncFrozenLeftOffsets();
            setTimeout(syncFrozenLeftOffsets, 800);
            window.addEventListener('resize', syncFrozenLeftOffsets);
            if (window.ResizeObserver) {
                const ro = new ResizeObserver(syncFrozenLeftOffsets);
                frozenLeft.forEach(function (el) {
                    ro.observe(el);
                });
            }

            // Collapse the header on scroll: first line of the title only,
            // no logo, no collection/series/repository lines. Hysteresis +
            // overflow-anchor:none (CSS) keep it from flickering at the edge.
            const leftSide = document.querySelector('.left-side');
            if (leftSide) {
                let headerCollapsed = false;
                leftSide.addEventListener('scroll', function () {
                    const st = leftSide.scrollTop;
                    if (!headerCollapsed && st > 40) {
                        headerCollapsed = true;
                        leftSide.classList.add('header-collapsed');
                    } else if (headerCollapsed && st < 10) {
                        headerCollapsed = false;
                        leftSide.classList.remove('header-collapsed');
                    }
                });
            }
        }

        // "Return to top" buttons for each scrolling section.
        const addReturnToTop = function (scrollEl, hostEl, sideClass) {
            if (!scrollEl || !hostEl || hostEl.querySelector('.return-to-top.' + sideClass)) {
                return;
            }
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'return-to-top ' + sideClass;
            btn.setAttribute('aria-label', 'Return to top');
            btn.innerHTML = '<i class="fa fa-arrow-up"></i>';
            btn.addEventListener('click', function () {
                // scrollTo({top, behavior:'smooth'}) is silently a no-op on
                // iOS Safari < 15.4 and some Android WebViews (the object
                // form isn't supported there) — that's why this button did
                // nothing on mobile. Feature-detect and fall back to a
                // manual scrollTop animation.
                if ('scrollBehavior' in document.documentElement.style) {
                    scrollEl.scrollTo({top: 0, behavior: 'smooth'});
                    return;
                }
                const start = scrollEl.scrollTop;
                if (start <= 0) {
                    return;
                }
                const startTime = performance.now();
                const duration = 300;
                const step = function (now) {
                    const progress = Math.min(1, (now - startTime) / duration);
                    scrollEl.scrollTop = start * (1 - progress);
                    if (progress < 1) {
                        requestAnimationFrame(step);
                    }
                };
                requestAnimationFrame(step);
            });
            hostEl.appendChild(btn);

            // Hidden by default; reveal on scroll-up and keep it shown until
            // the section is scrolled back near the top.
            let lastScroll = scrollEl.scrollTop;
            scrollEl.addEventListener('scroll', function () {
                const st = scrollEl.scrollTop;
                if (st <= 150) {
                    btn.classList.remove('visible');
                } else if (st < lastScroll - 2) {
                    btn.classList.add('visible');
                }
                lastScroll = st;
            });
        };
        addReturnToTop(document.querySelector('.left-side'),
                document.querySelector('.left-side'), 'return-to-top-left');
        addReturnToTop(document.querySelector('.right-side-inner'),
                document.querySelector('.right-side'), 'return-to-top-right');

        // Left side: the tab <ul> (#custom-tabs-left) and its panels
        // (#left-tab-content) are separate containers, so jQuery UI tabs can't
        // wire them. Handle show/hide manually off the nav links.
        const $leftContent = $('#left-tab-content');
        if ($leftContent.length) {
            const $leftNav = $('#custom-tabs-left > ul');
            $leftContent.children().addClass('ui-tabs-hide');

            const activateLeftTab = function (hash) {
                const $panel = hash ? $leftContent.children(hash) : $();
                if (!$panel.length) {
                    return;
                }
                $leftContent.children().addClass('ui-tabs-hide');
                $panel.removeClass('ui-tabs-hide');
                $leftNav.find('li').removeClass('ui-tabs-selected ui-state-active');
                $leftNav.find('a[href="' + hash + '"]').first().closest('li')
                        .addClass('ui-tabs-selected ui-state-active');
                // The panel was display:none until now, so anything that measured
                // its own size on init (Leaflet map, ECharts word cloud, Masonry
                // grid) needs a nudge once it's actually visible.
                setTimeout(function () {
                    window.dispatchEvent(new Event('resize'));
                }, 60);
            };
            window.activateLeftTab = activateLeftTab;

            $leftNav.on('click', 'a[href^="#"]', function (e) {
                e.preventDefault();
                activateLeftTab($(this).attr('href'));
            });

            // Initial selection mirrors the previous jQuery UI behaviour:
            // Index first, then Transcript, then About.
            let initialHash = '#about-tab-1';
            if ($leftContent.children('#index-tab-1').length) {
                initialHash = '#index-tab-1';
            } else if ($leftContent.children('#transcript-tab-1').length) {
                initialHash = '#transcript-tab-1';
            }
            activateLeftTab(initialHash);
        }

        const dataLayers = document.querySelector('.right-side-inner .data-layers');

        if (headerRight && dataLayers) {
            // Clone the original element
            const clone = dataLayers.cloneNode(true);

            // Create wrapper div
            const wrapper = document.createElement('div');
            wrapper.className = 'right-data-layer';

            // Append cloned element to wrapper
            wrapper.appendChild(clone);

            // Insert wrapper at the end of the header
            headerRight.appendChild(wrapper);
        }


        $(".custom-tabs a").on("click", function (e) {
            e.preventDefault(); // prevent default navigation

            // Get the class of the clicked link (like "index-tab" or "transcript-tab")
            var triggerClass = $(this).attr("class");

            // Find the tab link whose href matches the pattern (e.g., "#index-tab-2")
            var $tabLink = $('a[href$="' + triggerClass.replace("-tab", "-tab-2") + '"]');
            $('.tab-dropdown').removeClass('open active');

            if ($tabLink.length) {
                $tabLink.trigger("click"); // trigger click on the right tab
            }
        });


        $('.right-side-header .custom-tabs .tab-dropdown span').click(function () {
            $(this).closest('.tab-dropdown').toggleClass('open');
        });


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
        if (typeof OhmsBasemap !== 'undefined') {
            OhmsBasemap.bindGpsLinks();
        }
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

    $(document).ready(function() {
        function syncActiveTab() {
            // Find the <a> whose parent has ui-state-active
            var activeTab = $('a[data-tab]').filter(function() {
                return $(this).parent().hasClass('ui-state-active');
            }).first();

            if (activeTab.length) {
                var tabValue = activeTab.data('tab');
                console.log('Active tab:', tabValue);

                // Remove active class from all <a> with data-tab
                $('a[data-tab]').removeClass('ui-tabs-selected');

                // Add active class to all <a> with the same data-tab, including dropdown
                $('a[data-tab="' + tabValue + '"]').addClass('ui-tabs-selected');
            }

            $('.tab-dropdown').each(function() {
                // Check if any <a> inside has ui-tabs-selected class
                var hasActive = $(this).find('a').filter(function() {
                    return $(this).hasClass('ui-tabs-selected') || $(this).parent().hasClass('ui-tabs-selected');
                }).length > 0;

                if (hasActive) {
                    $(this).addClass('ui-tabs-selected');
                } else {
                    $(this).removeClass('ui-tabs-selected');
                }
            });
        }

        // Run on page load
        setTimeout(syncActiveTab, 1000);

        // Run on click for all <a> with data-tab (tabs + dropdown)
        $('a[data-tab]').on('click', function() {
            // Delay to wait for ui-state-active update
            setTimeout(syncActiveTab, 50);
        });

        
    });

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
                    scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop() - window.getFrozenClearance(container)
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
                    scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop() - window.getFrozenClearance(container)
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
                                    scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop() - window.getFrozenClearance(container)
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
                                scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop() - window.getFrozenClearance(container)
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
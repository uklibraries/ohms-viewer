function VisualizationJS() {
    var entityData;
    var mapData;
    var chart1;
    var chart2;
    var map1;
    var map2;
    var marker1;
    var marker2;
    const labelColors = {
        PERSON: '#dea590',
        PLACE: '#9aa6c1',
        DATE: '#e1be90',
        ORG: '#ced1ab',
        EVENT: '#c6a5ac'
    };
    this.initialize = function (entityRows, mapPoints) {
        setupDropdownTabs("#custom-tabs-left", "Visualization ▼", false);
        setupDropdownTabs("#custom-tabs-right");
        if (mapPoints.length > 0) {

            mapData = mapPoints;
            // Leaflet is created lazily, the first time its tab is actually
            // shown. Building it while the panel is still display:none
            // gives it a zero-size container to measure, and its tile grid
            // never fully recovers from that even after invalidateSize() /
            // fitBounds() once the panel becomes visible.
            $('#map-tab-1-head').click(function () {
                // panel is toggled visible by the click handler in custom.js;
                // wait a tick so the map measures its real size.
                setTimeout(function () {
                    if (!map1) {
                        [map1, marker1] = loadMap('map_area_1');
                        // Only "Place" is checked by default in the type
                        // filter (most entity types never carry a geo
                        // location), so hide any other type's markers to
                        // match what the filter already shows as checked.
                        applyMapFilter('1');
                    } else {
                        refreshMap(map1, marker1);
                    }
                }, 80);
            });
            $('#map-tab-2-head').click(function () {
                setTimeout(function () {
                    if (!map2) {
                        [map2, marker2] = loadMap('map_area_2');
                        applyMapFilter('2');
                    } else {
                        refreshMap(map2, marker2);
                    }
                }, 80);
            });
            $('#map_type_filter1, #map_type_filter2').on('change', function () {
                applyMapFilter($(this).data('id'));
            });
        }

        if (entityRows.length > 0) {
            browserTab();
            entityData = entityRows;
            wordCloudTab();
            $('#timeline_type_filter1, #timeline_type_filter2').on('change', applyTimelineFilter);
        }
        annotationPopup();

    };
    const annotationPopup = function () {

        $('.toggle-layers').change(function () {
            let layer = $(this).data('layer');
            let tab = '#transcript-tab-2';
            if (layer == 'ttl1') {
                tab = '#transcript-tab-1';
            }
            if ($(this).is(':checked')) {
                $('.data-layers-list.' + $(this).data('layer')).show();
                $(tab + ' .bdg-text').removeClass('bdg-text-disabled');
            } else {
                $('.data-layers-list.' + $(this).data('layer')).hide();
                $(tab + ' .bdg-text').addClass('bdg-text-disabled');
            }
        });

        $('.data-layers-list').on('click', '.fa-eye, .fa-eye-slash', function (e) {
            e.stopPropagation();
            const $icon = $(this);
            const $span = $icon.closest('span');
            const $parent = $icon.closest('.data-layers-list');
            let tab = '#transcript-tab-2';
            if ($parent.hasClass('ttl1'))
                tab = '#transcript-tab-1';
            $span.toggleClass('active-layer');
            if ($icon.hasClass('fa-eye')) {
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
                $(tab + ' .bdg-text.' + $icon.data('layer')).addClass('bdg-text-disabled');
            } else {
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
                $(tab + ' .bdg-text.' + $icon.data('layer')).removeClass('bdg-text-disabled');
            }
        });

        const $popoverBtn = $('.bdg-text, .pop-page-link');
        const $popover = $('#customPopover');
        let container;
        let transcriptTab;
        let marker;
        let map;
        let mapTab;
        $(document).on("click", ".bdg-text, .pop-page-link", function (e) {
//        $popoverBtn.on('click', function (e) {
            if (!$(this).hasClass('bdg-text-disabled')) {
                $popover.hide();
                e.stopPropagation(); // prevent immediate close
                const $clicked = $(this);
                let ref = $clicked.data('ref');
                $('.popover-body').addClass('d-none');
                if ($clicked.hasClass('pop-page-link')) {
                    scrollToTranscript(container, transcriptTab, ref);
                } else {
                    if ($(this).closest('.right-side').length) {
                        transcriptTab = '#transcript-tab-2';
                        mapTab = '#map-tab-1';
                        container = $('.right-side-inner');

                    } else {
                        container = $('.left-side');
                        transcriptTab = '#transcript-tab-1';
                        mapTab = '#map-tab-2';

                    }
                }
                let geoLocation = $.trim($clicked.data('geolocation'));
                if (geoLocation) {
                    const [lat, lng] = geoLocation.split(",").map(Number);
                    $('a[href="' + mapTab + '"]').trigger("click");
                    // map1/marker1 (or map2/marker2) are created lazily by
                    // that tab's own click handler, on its own setTimeout —
                    // wait for it before reading them.
                    setTimeout(function () {
                        map = (mapTab === '#map-tab-1') ? map1 : map2;
                        marker = (mapTab === '#map-tab-1') ? marker1 : marker2;
                        if (map && marker) {
                            highlightMarkerByLatLng(lat, lng, marker, map);
                        }
                    }, 150);

                }
                setTimeout(function () {
                    $('.transcript_' + ref).removeClass('d-none');
                    if ($popover.css('display') === 'block') {
                        $popover.hide();
                    } else {
                        // Re-measure now, right before showing: the click
                        // above can scroll/resize panes (map tab switch,
                        // header collapse, etc.), so a position captured at
                        // click time can be stale by the time we get here.
                        const $target = $clicked.hasClass('pop-page-link')
                                ? $(transcriptTab + ' .ref_' + ref)
                                : $clicked;
                        if (!$target.length) {
                            return;
                        }
                        const rect = $target[0].getBoundingClientRect();
                        $popover.show();
                        $popover.css({
                            top: rect.bottom + 12 + window.scrollY + 'px',
                            left: rect.left - 15 + window.scrollX + 'px'
                        });
                    }
                }, 700);



            }


        });
        $(document).on('click', function (e) {
            if (!$popover.is(e.target) && $popover.has(e.target).length === 0 && !$popoverBtn.is(e.target)) {
                $popover.hide();
            }
        });
        // The popover is absolutely positioned against the page, so it can't
        // follow its entity when the transcript scrolls inside its pane.
        // Hide it (and any inline entity note) as soon as scrolling starts.
        $('.right-side-inner, .left-side').on('scroll', function () {
            if ($popover.is(':visible')) {
                $popover.hide();
            }
        });
    };
    const highlightMarkerByLatLng = function (lat, lng, markers, map) {

//        markers.forEach(m => {
//            m._icon.querySelector('svg').setAttribute('fill', '#0033A0');
//        });
        const marker = findMarkerByLatLng(lat, lng, markers);
        if (marker) {
//            marker._icon.querySelector('svg').setAttribute('fill', '#000000');
            marker.openPopup();
            // Left map: stay exactly where it is — no recenter, no zoom.
            if (map !== map1) {
                map.flyTo(marker.getLatLng(), 12, {duration: 1.5});
            }
        }
    }
    const findMarkerByLatLng = function (lat, lng, markers) {
        const tolerance = 0.00001; // allows for tiny rounding differences
        return markers.find(m => {
            const pos = m.getLatLng();
            return Math.abs(pos.lat - lat) < tolerance && Math.abs(pos.lng - lng) < tolerance;
        });
    };
    // fitBounds() alone can pick a zoom low enough to fit a wide spread of
    // markers into a narrow (but tall) map column. At that zoom the world
    // map itself (256 * 2^zoom px tall) can end up shorter than the
    // container, and Web Mercator has no tiles past the poles — so the
    // extra container height renders as blank gray space. Never zoom out
    // past the point where the world is shorter than the container.
    const fitBoundsNoGap = function (map, bounds) {
        const targetZoom = map.getBoundsZoom(bounds);
        const size = map.getSize();
        const minZoomForHeight = Math.ceil(Math.log2(Math.max(size.y, 1) / 256));
        const zoom = Math.max(targetZoom, minZoomForHeight, map.getMinZoom());
        map.setView(bounds.getCenter(), zoom, {animate: false});
    };
    const refreshMap = function (map, markers) {
        if (!('CSS' in window && CSS.supports && CSS.supports('aspect-ratio', '1/1'))) {
            const w = document.getElementById('map').offsetWidth;
            document.getElementById('map').style.height = Math.max(320, Math.round(w * 9 / 16)) + 'px';
        }
        map.invalidateSize();
        if (markers.length) {
            const group = L.featureGroup(markers);
            // animate:false — an animated fitBounds right after switching to
            // the Map tab leaves the view mid-flight; Leaflet ignores marker
            // clicks while panning/zooming, so the first click after opening
            // the tab would appear to do nothing until the flight settled.
            fitBoundsNoGap(map, group.getBounds().pad(0.2));
        }
        // invalidateSize()/fitBounds() reposition the map and its markers via
        // JS, but the browser's own hit-testing for that area can stay stale
        // until something moves the mouse over it — which is exactly why
        // hovering a pin first makes the very next click land, but clicking
        // cold does not. Fire a synthetic mousemove over the map so the
        // hit-test region is refreshed before the user's first real click.
        const mapEl = map.getContainer();
        const rect = mapEl.getBoundingClientRect();
        mapEl.dispatchEvent(new MouseEvent('mousemove', {
            bubbles: true,
            clientX: rect.left + rect.width / 2,
            clientY: rect.top + rect.height / 2
        }));
    }
    const applyMapFilter = function (tabTag) {
        const map = (String(tabTag) === '1') ? map1 : map2;
        const markers = (String(tabTag) === '1') ? marker1 : marker2;
        if (!map || !markers)
            return;

        const selected = ($('#map_type_filter' + tabTag).val() || []).map(v => String(v).toUpperCase());

        const visible = [];
        markers.forEach(m => {
            const show = selected.includes(m.options.label);
            if (show) {
                if (!map.hasLayer(m))
                    m.addTo(map);
                visible.push(m);
            } else {
                if (map.hasLayer(m))
                    map.removeLayer(m);
            }
        });

        if (visible.length) {
            fitBoundsNoGap(map, L.featureGroup(visible).getBounds().pad(0.2));
        }
    };
    const esc = function (s) {
        return String(s).replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    }
    const loadMap = function (mapId) {
        const map = L.map(mapId).setView([20, 0], 2);
        const apiKey = (typeof mapApiKey !== 'undefined' && mapApiKey) ? mapApiKey : '';
        L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png?api_key=' + apiKey, {
            maxZoom: 20,
            attribution: ''
        }).addTo(map);
        const brandIcon = L.divIcon({
            className: 'custom-marker',
            html: `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="#0033A0">
          <path d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zM12 11.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
        </svg>
      `,
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
// Plot markers
        const markers = [];
        mapData.forEach(row => {
            const lat = parseFloat(row.lat), lng = parseFloat(row.lng);
            if (!Number.isFinite(lat) || !Number.isFinite(lng))
                return;

            const text = String(row.text || '');
            const first_ref = row.first_ref;
            const count = Number(row.count || 0);

            const popupHtml = `<strong class="map_highlight" data-ref="${first_ref}">${esc(text)}${count ? ' (' + count + ')' : ''}</strong>`;
            // Keep marker popups clear of the frozen header/tab bar sitting on
            // top of the map: map_area_1 (left) sits under the whole frozen
            // header/player/search/tab stack, map_area_2 (right) only under
            // the tab bar.
            const topPad = mapId === 'map_area_1' ? ((window.frozenLeftHeight || 0) + 20) : 60;
            const label = String(row.label || '').toUpperCase();
            const m = L.marker([lat, lng], {icon: brandIcon, ref: first_ref, label: label}).addTo(map).bindPopup(popupHtml, {
                autoPanPaddingTopLeft: L.point(20, topPad),
                autoPanPaddingBottomRight: L.point(20, 20)
            });
            m.on('click', function () {
                if (mapId !== 'map_area_1') {
                    // Center the pin in the VISIBLE map area — below the frozen
                    // header/tab-bar sitting on top of it — rather than the
                    // geometric center of the whole map div, so its popup opens
                    // somewhere the user can actually read it.
                    const targetZoom = 12;
                    const latlng = m.getLatLng();
                    const point = map.project(latlng, targetZoom);
                    const shifted = map.unproject(point.subtract([0, topPad / 2]), targetZoom);
                    map.flyTo(shifted, targetZoom, {duration: 1.5});
                }
                // Left map: stay exactly where it is — no recenter, no zoom.
                // Keep the map in its own pane; drive the transcript in the
                // other pane. map_area_1 is the left pane, map_area_2 the right.
                let container;
                let transcriptTab;
                if (mapId === 'map_area_1' && $('.right-side').is(':visible')) {
                    container = $('.right-side-inner');
                    transcriptTab = '#transcript-tab-2';
                } else {
                    container = $('.left-side');
                    transcriptTab = '#transcript-tab-1';
                }
                scrollToTranscript(container, transcriptTab, m.options.ref);
                setTimeout(function () {
                    $(transcriptTab + ' .bdg-text.ref_' + m.options.ref).trigger('click');
                }, 300);
            });
            markers.push(m);
        });

// Fit to markers
        if (markers.length) {
            const group = L.featureGroup(markers);
            fitBoundsNoGap(map, group.getBounds().pad(0.2));
        }
        return [map, markers];
    };
    function getBaseWordcloudOption(wordcloudData) {
        return {
            tooltip: {show: true, formatter: p => `${p.name} (${p.value})`},
            series: [{
                    type: 'wordCloud',
                    gridSize: 15,
                    sizeRange: [25, 50],
                    rotationRange: [0, 0],
                    shape: 'square',
                    drawOutOfBound: false,
                    textStyle: {
                        normal: {
                            fontFamily: 'Nunito, sans-serif',
                            color: function (params) {
                                return labelColors[params.data.labelType] || '#333';
                            }
                        },
                        emphasis: {
                            fontFamily: 'Nunito, sans-serif',
                            shadowColor: '#333'
                        }
                    },
                    data: wordcloudData
                }]
        };
    }

// Build data based on selected types for a given tab
    function buildFilteredWordcloudData(tabTag) {
        const selected = ($('#ww_type_filter' + tabTag).val() || []).map(v => String(v).toUpperCase());

        // If nothing selected => show none (change to "return entityData.map(...)" if you prefer show all)
        if (selected.length === 0)
            return [];

        return (entityData || [])
                .filter(a => selected.includes(String(a.label || '').toUpperCase()))
                .map(a => ({
                        name: a.text,
                        value: Math.floor(Math.random() * a.count) + a.count, // keep your logic
                        ref: a.first_ref,
                        labelType: String(a.label || '').toUpperCase()
                    }));
    }

    function applyWordcloudFilter(tabTag) {
        const chart = (String(tabTag) === '1') ? chart1 : chart2;
        if (!chart)
            return;

        const data = buildFilteredWordcloudData(tabTag);

        // Update only series data
        chart.setOption({
            series: [{data}]
        });

        chart.resize();
    }

    const resizeWordCloud = function () {
        // When switching tabs, resize the chart in that tab
        $('#wordcloud-tab-1-head').off('click.wordcloud').on('click.wordcloud', function () {
            if (chart1)
                chart1.resize();
        });

        $('#wordcloud-tab-2-head').off('click.wordcloud').on('click.wordcloud', function () {
            if (chart2)
                chart2.resize();
        });

        // Proper resize handler for both (doesn't overwrite)
        window.addEventListener('resize', function () {
            if (chart1)
                chart1.resize();
            if (chart2)
                chart2.resize();
        });
    };
    const wordCloudTab = function () {
// Init both charts
        chart1 = echarts.init(document.getElementById('wordcloud-1'));
        chart2 = echarts.init(document.getElementById('wordcloud-2'));

        // Set initial options with filtered data (defaults selected => shows all)
        chart1.setOption(getBaseWordcloudOption(buildFilteredWordcloudData(1)));
        chart2.setOption(getBaseWordcloudOption(buildFilteredWordcloudData(2)));

        // Bind filters (one handler for both selects)
        $('#ww_type_filter1, #ww_type_filter2')
                .off('change.wordcloud')
                .on('change.wordcloud', function () {
                    const tabTag = $(this).data('id'); // 1 or 2
                    applyWordcloudFilter(tabTag);
                });

        resizeWordCloud();

        // Click behavior (keep yours)
        chart1.on('click', function (params) {
            const ref = params?.data?.ref;
            if (!ref)
                return;

            let container;
            let transcriptTab;

            if ($('.right-side').is(':visible')) {
                transcriptTab = '#transcript-tab-2';
                container = $('.right-side-inner');
            } else {
                container = $('.left-side');
                transcriptTab = '#transcript-tab-1';
            }

            scrollToTranscript(container, transcriptTab, ref);
            showEntityPopover(transcriptTab, ref);
        });

        chart2.on('click', function (params) {
            const ref = params?.data?.ref;
            if (!ref)
                return;

            const container = $('.left-side');
            const transcriptTab = '#transcript-tab-1';
            scrollToTranscript(container, transcriptTab, ref);
            showEntityPopover(transcriptTab, ref);
        });

    };
    // Re-triggers the click on the entity's own transcript badge, which is
    // what actually opens the hover-over description box (see
    // annotationPopup's ".bdg-text, .pop-page-link" handler) — this is the
    // same mechanism the Map's marker click already uses (AVIARY-6041), so
    // Timeline/Word Cloud/Browser table/Browser gallery clicks land on the
    // same box via the same code path instead of a separate one. That
    // handler's popover logic doesn't depend on geolocation, so this works
    // for every NER entity type, geotagged or not.
    const showEntityPopover = function (transcriptTab, ref) {
        setTimeout(function () {
            $(transcriptTab + ' .bdg-text.ref_' + ref).trigger('click');
        }, 300);
    };
    const scrollToTranscript = function (container, transcriptTab, ref) {
        $('a[href="' + transcriptTab + '"]').trigger("click");
        $('html, body').animate({scrollTop: 0}, 100);
        setTimeout(function () {
            let scrollTo = $(transcriptTab + ">.transcript-panel .ref_" + ref);
            const offsetAdjustment = window.getFrozenClearance ? window.getFrozenClearance(container) : 200;
            container.animate({
            scrollTop: scrollTo.offset().top 
                    - container.offset().top 
                    + container.scrollTop() 
                    - offsetAdjustment
            }, 100, 'swing');
        }, 150);
    };
    const applyGridFilter = function () {
        let id = $(this).data('id');

        const $c = $('.grid-container' + id);
        const $it = $c.find('.grid-item' + id);
        const $lbl = $('#type_filter' + id);
        const $q = $('#browser_search' + id);
        const $sort = $('#sortDropdown' + id);

        if ($c.data('masonry')) {
            $c.masonry('destroy'); // Remove Masonry instance
            $c.removeData('masonry'); // Clear stored data
        }
        // normalize multiselect
        let sel = $lbl.val() || [];
        if (!Array.isArray(sel))
            sel = [sel];
        sel = sel
                .filter(v => v != null && v !== '')
                .map(v => String(v).toLowerCase().trim());

        const hasSel = sel.length > 0;
        const q = String($q.val() || '').toLowerCase().trim();
        const order = $sort.val();

        // add/get a no-results element just after the grid container
        let $nores = $('#noResultsGrid' + id);
        if ($nores.length === 0) {
            $nores = $('<div/>', {
                id: 'noResultsGrid' + id,
                class: 'no-results',
                text: 'No results found'
            }).hide();
            $c.after($nores);
        }

        // if nothing selected → hide all and show message
        if (!hasSel) {
            $it.hide();
            $nores.show();
            return;
        }

        // filter (uses data-label and data-text)
        let visibleCount = 0;
        $it.each(function () {
            const $el = $(this);

            // support multiple labels in data-label: "person, org" or "person|org" or "person/org"
            const lbls = String($el.data('label') || '')
                    .toLowerCase()
                    .split(/[,\|\/]+/)
                    .map(s => s.trim())
                    .filter(Boolean);

            const txt = String($el.data('text') || '').toLowerCase();

            const labelMatch = lbls.some(l => sel.includes(l));
            const textMatch = !q || txt.includes(q);

            const show = labelMatch && textMatch;
            $el.toggle(show);
            if (show)
                visibleCount++;
        });

        // toggle "no results"
        $nores.toggle(visibleCount === 0);

        // sort (uses data-count, data-text, data-label) then re-append
        const arr = $it.get().sort((a, b) => {
            const $a = $(a), $b = $(b);
            const ac = +$a.data('count') || 0, bc = +$b.data('count') || 0;
            const at = String($a.data('text') || '').toLowerCase();
            const bt = String($b.data('text') || '').toLowerCase();
            const al = String($a.data('label') || '').toLowerCase();
            const bl = String($b.data('label') || '').toLowerCase();

            switch (order) {
                case 'count-desc':
                    return (bc - ac) || at.localeCompare(bt);
                case 'count-asc':
                    return (ac - bc) || at.localeCompare(bt);
                case 'name-asc':
                    return at.localeCompare(bt) || al.localeCompare(bl);
                case 'name-desc':
                    return bt.localeCompare(at) || al.localeCompare(bl);
                case 'type-asc':
                    return al.localeCompare(bl) || at.localeCompare(bt);
                case 'type-desc':
                    return bl.localeCompare(al) || at.localeCompare(bt);
                default:
                    return 0;
            }
        });

        $(arr).appendTo($c);
        activateMasonary(id);
    };
    const applyFilters = function () {
        let id = $(this).data('id');

        const $table = $('#entityTable' + id);
        const $tbody = $table.find('tbody');
        const $rows = $tbody.find('tr');

        const searchText = ($('#browser_search' + id).val() || '').toLowerCase().trim();

        // MULTISELECT: normalize to a lowercased array
        let selectedTypes = $('#type_filter' + id).val() || [];
        if (!Array.isArray(selectedTypes))
            selectedTypes = [selectedTypes];
        selectedTypes = selectedTypes
                .filter(v => v != null && v !== '')
                .map(v => v.toString().toLowerCase().trim());

        // Add/get a "no results" element right after the table
        let $nores = $('#noResults' + id);
        if ($nores.length === 0) {
            $nores = $('<div/>', {
                id: 'noResults' + id,
                class: 'no-results',
                text: 'No results found'
            }).hide();
            $table.after($nores);
        }

        // If no types selected → show nothing
        if (selectedTypes.length === 0) {
            $rows.hide();
            $nores.show();
            return;
        }

        let visible = 0;

        $rows.each(function () {
            const $tr = $(this);

            // Full row text for search
            const rowText = $tr.text().toLowerCase();

            // Type column (index 1). Supports "PERSON, ORG" / "PERSON|ORG" / "PERSON/ORG"
            const rowTypeRaw = $tr.children('td').eq(1).text().toLowerCase();
            const rowTypes = rowTypeRaw
                    .split(/[,\|\/]+/) // commas, pipes, slashes
                    .map(s => s.trim())
                    .filter(Boolean);

            const matchesSearch = !searchText || rowText.indexOf(searchText) > -1;
            const matchesType = rowTypes.some(t => selectedTypes.includes(t));

            const show = matchesSearch && matchesType;
            $tr.toggle(show);
            if (show)
                visible++;
        });

        // Toggle "no results"
        $nores.toggle(visible === 0);
    };
    const browserTab = function () {
        $("#type_filter1, #type_filter2, #timeline_type_filter1, #timeline_type_filter2, #ww_type_filter1, #ww_type_filter2, #map_type_filter1, #map_type_filter2").multiselect({
            header: true,
            noneSelectedText: "Type",
            selectedList: 0,
            selectedText: function (numSelected, total, checkedItems) {
                return numSelected + " selected";
            },

            beforeopen: function () {
                var $select = $(this);
                var selectId = $select.attr('id');

                // Find the correct multiselect menu for this select
                var $dropdown = $('.ui-multiselect-menu').filter(function () {
                    return $(this).find('input[id^="ui-multiselect-' + selectId + '-"]').length > 0;
                }).first();

                if ($dropdown.length) {
                    // Create wrapper if not already there
                    if (!$select.parent().hasClass('multiselect-wrapper')) {
                        $select.wrap('<div class="multiselect-wrapper"></div>');
                    }

                    // Move dropdown into wrapper BEFORE it opens
                    $dropdown.appendTo($select.closest('.multiselect-wrapper'));
                }
            },

            open: function () {
                var $select = $(this);
                var selectId = $select.attr('id');

                var $dropdown = $('.ui-multiselect-menu').filter(function () {
                    return $(this).find('input[id^="ui-multiselect-' + selectId + '-"]').length > 0;
                }).first();

                if ($dropdown.length) {
                    // Optionally re-style
                    $dropdown.css({
                        position: 'absolute',
                        top: $select.outerHeight(),
                        left: 0,
                        // Above Leaflet's own control pane (.leaflet-top /
                        // .leaflet-bottom, z-index: 1000 by default) — the
                        // map filter's dropdown sits right next to the map,
                        // and equal z-index lets the map's zoom control win
                        // the tie and render on top of this menu.
                        zIndex: 2000,
                        // Leaflet's tile/marker panes are GPU-composited
                        // (translate3d). A plain z-index stacks correctly in
                        // the DOM/paint order, but Chromium can still
                        // composite an ordinary (non-layered) element behind
                        // a promoted one. Promoting this dropdown to its own
                        // layer too fixes that — without it, this menu is
                        // provably on top in the accessibility/hit-test tree
                        // yet visually renders behind the map.
                        transform: 'translateZ(0)'
                    });
                }
            }
        });

        // Timeline, Browser table, and Browser gallery: scroll to the entity
        // AND surface its hover-over description box, matching the Map's
        // marker-click behavior (AVIARY-6041). Kept separate from
        // .map_highlight below so that existing Map behavior — which
        // already opens the popover itself, from its own marker click
        // handler in loadMap() — is unchanged rather than doubled up.
        $(document).on("click", ".anno-row, .timeline_event, .grid-item", function (e) {
            e.preventDefault(); // optional, prevents default action
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
            const ref = $(this).data('ref');
            scrollToTranscript(container, transcriptTab, ref);
            showEntityPopover(transcriptTab, ref);
        });

        $(document).on("click", ".map_highlight", function (e) {
            e.preventDefault();
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
            scrollToTranscript(container, transcriptTab, $(this).data('ref'));
        });


//        $('.anno-row, .timeline_event, .grid-item, .map_highlight').click(function () {
//            
//        });
        $('#browser_search1, #browser_search2').on('keyup', applyFilters);
        $('#type_filter1, #type_filter2').on('change', applyFilters);
        $('#browser_search1, #browser_search2').on('keyup', applyGridFilter);
        $('#type_filter1, #type_filter2').on('change', applyGridFilter);

        $('#sortDropdown1, #sortDropdown2').on('change', applyGridFilter);
        $('#sortDropdown1, #sortDropdown2').on('change', function () {

            const value = $(this).val();
            if (!value)
                return;

            const [col, dir] = value.split('-'); // e.g. "id-asc"
            const isAsc = dir === 'asc';

            let colIndex = 0;
            if (col === 'type')
                colIndex = 1;
            if (col === 'name')
                colIndex = 2;

            const $tbody = $('#entityTable' + $(this).data('id') + ' tbody');
            const $rows = $tbody.find('tr').get();

            const toNumber = (txt) => {
                const n = parseFloat(String(txt).replace(/[^\d.-]/g, ''));
                return isNaN(n) ? 0 : n;
            };

            $rows.sort(function (rowA, rowB) {
                const aText = $(rowA).children('td').eq(colIndex).text().trim();
                const bText = $(rowB).children('td').eq(colIndex).text().trim();

                if (col === 'id') {
                    const a = toNumber(aText);
                    const b = toNumber(bText);
                    return isAsc ? a - b : b - a;
                }

                // default string sorting
                return isAsc
                        ? aText.localeCompare(bText, undefined, {numeric: true})
                        : bText.localeCompare(aText, undefined, {numeric: true});
            });

            // re-attach sorted rows
            for (const row of $rows) {
                $tbody.append(row);
            }
        });
        $('.grid-section').hide();
        $('.custom-toggle-icon .icon').on('click', function () {
            $(this).siblings('span').removeClass('active');
            $(this).addClass('active');
            let id = $(this).data('id');
            if ($(this).hasClass('list')) {
                $('.grid_' + id).hide();
                $('.list_' + id).show();
            } else {
                $('.list_' + id).hide();
                $('.grid_' + id).show();
                $('.grid_' + id).css('opacity', '0');
                setTimeout(function () {
                    activateMasonary(id);
                    $('.grid_' + id).css('opacity', '1');
                }, 300);
            }
        });
    };
    const activateMasonary = function (id) {
        var $container = $('.grid-container' + id);

// Only initialize Masonry if it hasn’t been initialized yet
        if (!$container.data('masonry')) {
            var $grid = $container.masonry({
                itemSelector: '.grid-item',
                columnWidth: '.grid-sizer',
                percentPosition: true,
                gutter: 15,
                horizontalOrder: false
            });
            $grid.imagesLoaded().progress(function () {
                $grid.masonry('layout');
            });
            $grid.imagesLoaded().always(function () {
                $grid.masonry('layout');
            });
        } else {
            // Already initialized — just trigger a layout refresh
            $container.masonry('layout');
        }
    }
    const setupDropdownTabs = function (containerSelector, dropdownLabel = "Visualization ▼", initTabs = true) {
        if (initTabs) {
            $(containerSelector).tabs();
        } else {
            // Left side: nav <ul> and its panels live in separate containers,
            // so jQuery UI tabs can't be used. Just apply the classes the
            // markup/CSS expect; show/hide is handled in custom.js.
            $(`${containerSelector} > ul`).addClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header");
            $(`${containerSelector} > ul > li`).addClass("ui-state-default ui-corner-top");
        }
        const $dropdownTabs = $(`${containerSelector} .ui-tabs-nav li.dropdown-tab`);
        if ($dropdownTabs.length === 0)
            return;
        const $dropdownContainer = $(`
                            <li class="dropdown-toggle-tab">
                                <div class="dropdown-toggle">${dropdownLabel}</div>
                                <ul class="dropdown-menu" style="display: none;"></ul>
                            </li>
                        `);
        $dropdownTabs.each(function () {
            $(this).appendTo($dropdownContainer.find(".dropdown-menu"));
        });
        $(`${containerSelector} .ui-tabs-nav`).append($dropdownContainer);
        $(`${containerSelector} .dropdown-toggle`).on("click", function (e) {
            e.stopPropagation();
            $(this).siblings(".dropdown-menu").toggle();
        });
        $(document).on("click", function () {
            $(`${containerSelector} .dropdown-menu`).hide();
        });
        $(`${containerSelector} .ui-tabs-nav li a`).on("click", function () {
            $(`${containerSelector} .dropdown-toggle`).removeClass('active');
        });
        $(`${containerSelector} .dropdown-menu a`).on("click", function () {
            const $li = $(this).parent();
//            $li.removeClass("ui-tabs-selected ui-state-active");
            $li.parent().prev().addClass('active');
            $li.parent().hide();
        });
    }
    const reflowTimeline = function ($timeline) {
        const $visible = $timeline.find('.timeline_container:visible');
        $visible.removeClass('left right');
        $visible.each(function (idx) {
            $(this).addClass(idx % 2 === 0 ? 'left' : 'right');
        });
    }
    const applyTimelineFilter = function () {
        let tabTag = $(this).data('id');
        const $wrap = $('#timeline-tab-' + tabTag);
        const $select = $('#timeline_type_filter' + tabTag);
        const $timeline = $wrap.find('.timeline').first();
        const selected = ($select.val() || []).map(v => String(v).toLowerCase());

        const $items = $timeline.find('.timeline_container');

        if (selected.length === 0) {
            $items.hide();
            reflowTimeline($timeline);
            return;
        }

        $items.each(function () {
            const type = String($(this).data('type') || '').toLowerCase();
            $(this).toggle(selected.includes(type));
        });

        reflowTimeline($timeline);
    }
}

            
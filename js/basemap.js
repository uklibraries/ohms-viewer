/*
 * Basemap shared by every Leaflet map in the viewer: the Map visualization
 * tab (js/visualization.js) and the lightbox opened from an index segment's
 * GPS link.
 *
 * Default: OpenFreeMap "Positron" vector tiles, rendered by MapLibre GL JS
 * inside Leaflet via @maplibre/maplibre-gl-leaflet. No API key needed.
 *
 * Deployers can override it in config.ini (passed in as `ohmsMapConfig` by
 * tmpl/viewer.tmpl.php):
 *   map_style_url            MapLibre style JSON URL
 *   map_attribution          attribution HTML for that style
 *   map_fallback_tile_url    raster {z}/{x}/{y} URL used when WebGL/MapLibre
 *                            is unavailable
 *   map_fallback_attribution attribution HTML for the fallback tiles
 */
var OhmsBasemap = (function () {
    'use strict';

    var DEFAULT_STYLE_URL = 'https://tiles.openfreemap.org/styles/positron';
    var OPENFREEMAP_ATTRIBUTION =
            '<a href="https://openfreemap.org" target="_blank" rel="noopener">OpenFreeMap</a> ' +
            '&copy; <a href="https://www.openmaptiles.org/" target="_blank" rel="noopener">OpenMapTiles</a> ' +
            'Data from <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>';
    var MAX_ZOOM = 20;
    var DEFAULT_GPS_ZOOM = 17;

    var webglSupported = null;

    function config() {
        return (typeof ohmsMapConfig === 'object' && ohmsMapConfig !== null) ? ohmsMapConfig : {};
    }

    function setting(name) {
        var value = config()[name];
        return typeof value === 'string' ? value.trim() : '';
    }

    function hasWebGL() {
        if (webglSupported === null) {
            try {
                // Same context lookup MapLibre itself does.
                var canvas = document.createElement('canvas');
                webglSupported = !!(window.WebGLRenderingContext &&
                        (canvas.getContext('webgl2') || canvas.getContext('webgl')));
            } catch (e) {
                webglSupported = false;
            }
        }
        return webglSupported;
    }

    function mapLibreAvailable() {
        return typeof maplibregl !== 'undefined' &&
                typeof L.maplibreGL === 'function' &&
                hasWebGL();
    }

    function styleUrl() {
        return setting('styleUrl') || DEFAULT_STYLE_URL;
    }

    // Attribution for the vector basemap. A configured value always wins;
    // otherwise any OpenFreeMap style gets the required OpenFreeMap /
    // OpenMapTiles / OpenStreetMap credit (its style JSON carries none).
    // For any other style, return null and let the bridge read the
    // attribution from the style's own sources.
    function vectorAttribution() {
        var configured = setting('attribution');
        if (configured) {
            return configured;
        }
        if (/^https?:\/\/tiles\.openfreemap\.org\//i.test(styleUrl())) {
            return OPENFREEMAP_ATTRIBUTION;
        }
        return null;
    }

    function addVectorLayer(map) {
        var options = {style: styleUrl()};
        var attribution = vectorAttribution();
        if (attribution) {
            // The bridge ignores the usual `attribution` option and reports
            // either this or the style's sources' attribution.
            options.attributionControl = {customAttribution: attribution};
        }
        var layer = L.maplibreGL(options);
        try {
            layer.addTo(map);
            return true;
        } catch (e) {
            // MapLibre throws from inside onAdd when it can't get a WebGL
            // context even though the check above passed (e.g. GPU
            // blocklisted). Undo the half-added layer by hand: removeLayer()
            // would call into the MapLibre map that was never created.
            if (layer._container && layer._container.parentNode) {
                layer._container.parentNode.removeChild(layer._container);
            }
            delete map._layers[L.stamp(layer)];
            if (window.console) {
                console.warn('OHMS Viewer: vector basemap unavailable, using fallback.', e);
            }
            return false;
        }
    }

    function addFallback(map) {
        var tileUrl = setting('fallbackTileUrl');
        if (tileUrl) {
            L.tileLayer(tileUrl, {
                maxZoom: MAX_ZOOM,
                maxNativeZoom: 19,
                attribution: setting('fallbackAttribution')
            }).addTo(map);
            return;
        }
        // No basemap at all: markers still work on the plain background;
        // say why the map is blank instead of failing silently.
        var notice = L.control({position: 'bottomleft'});
        notice.onAdd = function () {
            var div = L.DomUtil.create('div', 'ohms-basemap-notice');
            div.textContent = 'Base map unavailable in this browser';
            return div;
        };
        notice.addTo(map);
        L.DomUtil.addClass(map.getContainer(), 'ohms-basemap-missing');
    }

    return {
        MAX_ZOOM: MAX_ZOOM,

        // Options for L.map(). minZoom 1 and the lat-only maxBounds are what
        // maplibre-gl-leaflet recommends to keep the GL layer in sync with
        // Leaflet; maxZoom keeps fitBounds() on a single marker finite.
        mapOptions: function (extra) {
            return L.extend({
                minZoom: 1,
                maxZoom: MAX_ZOOM,
                maxBounds: [[180, -Infinity], [-180, Infinity]],
                maxBoundsViscosity: 1
            }, extra || {});
        },

        addTo: function (map) {
            if (!(mapLibreAvailable() && addVectorLayer(map))) {
                addFallback(map);
            }
            return map;
        },

        pinIcon: function () {
            return L.divIcon({
                className: 'custom-marker',
                html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="#0033A0">' +
                        '<path d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zM12 11.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>' +
                        '</svg>',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });
        },

        // Index segment GPS links (app/Ohms/Transcript.php) open the point
        // in a lightbox map. Delegated, so it also covers index markup that
        // is re-rendered after load.
        bindGpsLinks: function () {
            var self = this;
            $(document).off('click.ohmsGps').on('click.ohmsGps', 'a.ohms-gps-link', function (e) {
                e.preventDefault();
                if (typeof L === 'undefined' || typeof $.fancybox === 'undefined') {
                    return;
                }
                var $link = $(this);
                var lat = parseFloat($link.attr('data-lat'));
                var lng = parseFloat($link.attr('data-lng'));
                if (!isFinite(lat) || !isFinite(lng)) {
                    return;
                }
                var zoom = parseInt($link.attr('data-zoom'), 10);
                if (!isFinite(zoom)) {
                    zoom = DEFAULT_GPS_ZOOM;
                }
                zoom = Math.min(Math.max(zoom, 1), MAX_ZOOM);
                var label = $link.attr('data-has-label') === '1' ? $link.html() : (lat + ', ' + lng);
                var gpsMap = null;

                $.fancybox.open({
                    type: 'html',
                    content: '<div class="ohms-gps-map"></div>',
                    width: '80%',
                    height: '80%',
                    autoSize: false,
                    fitToView: true,
                    openEffect: 'none',
                    closeEffect: 'none',
                    afterShow: function () {
                        var el = this.inner.find('.ohms-gps-map')[0];
                        if (!el) {
                            return;
                        }
                        gpsMap = L.map(el, self.mapOptions()).setView([lat, lng], zoom);
                        self.addTo(gpsMap);
                        L.marker([lat, lng], {icon: self.pinIcon()})
                                .addTo(gpsMap)
                                .bindPopup('<strong>' + label + '</strong>')
                                .openPopup();
                    },
                    beforeClose: function () {
                        if (gpsMap) {
                            gpsMap.remove();
                            gpsMap = null;
                        }
                    }
                });
            });
        }
    };
})();

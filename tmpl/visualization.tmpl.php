
<div id="wordcloud-tab-<?php echo $tab_tag; ?>">
    <div class="ww_timeline_filter_container">
        <select id="ww_type_filter<?php echo $tab_tag; ?>" data-id="<?php echo $tab_tag; ?>" class="browser-type" multiple="multiple">

            <option value="person" selected="selected">Person</option>
            <option value="place" selected="selected">Place</option>
            <option value="date" selected="selected">Date</option>
            <option value="org" selected="selected">Org</option>
            <option value="event" selected="selected">Event</option>
        </select> 
    </div>
    <div id="wordcloud-<?php echo $tab_tag; ?>"></div> 

</div>
<?php if (count($interview->mapData) > 0): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


    <style>
        /* Full width; auto height via aspect ratio (fallback below) */
        #map_area_1, #map_area_2 {
            width: 100%;
            height: 100vh;              /* Full viewport height */
            max-height: 100vh;
            /*border-radius: 12px;*/
            margin: 0;
        }
        @media (min-width: 768px) {
            #map_area_1,
            #map_area_2 {
                height: 80vh;           /* Slightly shorter on large screens */
                margin: 12px 0;
            }
        }
        .leaflet-control-attribution {
            display:none;
        }
    </style>
    <div id="map-tab-<?php echo $tab_tag; ?>">
        <div id="map_area_<?php echo $tab_tag; ?>"></div>

    </div>
<?php endif; ?>

<div id="timeline-tab-<?php echo $tab_tag; ?>">
    <div class="ww_timeline_filter_container">
        <select id="timeline_type_filter<?php echo $tab_tag; ?>" data-id="<?php echo $tab_tag; ?>" class="browser-type" multiple="multiple">

            <option value="person" selected="selected">Person</option>
            <option value="place" selected="selected">Place</option>
            <option value="date" selected="selected">Date</option>
            <option value="org" selected="selected">Org</option>
            <option value="event" selected="selected">Event</option>
        </select> 
    </div>
    <div class="timeline">
        <?php
        $unique = [];
        $filtered = [];

        foreach ($interview->timeline as $item) {
            // Key combines date + wiki.name
            $key = $item['date'] . '|' . ($item['wiki']['name'] ?? '');

            if (!isset($unique[$key])) {
                $unique[$key] = true;
                $filtered[] = $item;
            }
        }

// Now $filtered has duplicates removed
        $timeline = $filtered;
        foreach ($timeline as $i => $item) {
            $sideClass = ($i % 2 === 0) ? 'left' : 'right';
            ?>
            <div data-type="<?php echo strtolower($item['label']); ?>" class="<?php echo strtolower($item['label']) ?> timeline_container container <?php echo $sideClass; ?>">
                <div class="content">
                    <strong><?php echo htmlspecialchars($item['date']); ?></strong>
                    <div class="org timeline_event" data-ref="<?php echo $item['ref']; ?>"><?php echo htmlspecialchars((string) $item['label']); ?>: <?php echo htmlspecialchars((string) $item['wiki']['name']); ?></div>
                    <p><?php
                        if (!empty($item['wiki']['description_1'])):
                            echo htmlspecialchars((string) $item['wiki']['description_1']);
                        else:
                            echo htmlspecialchars((string) $item['wiki']['description_2']);
                        endif;
                        ?></p>
                    <?php if (!empty($item['wiki']['url1'])): ?>
                        <a href="<?php echo htmlspecialchars($item['wiki']['url1']); ?>" target="_blank">Wikipedia</a>
                    <?php endif; ?>
                </div>

            </div>
            <?php
        }
        ?>

    </div>
</div>
<div id="browser-tab-<?php echo $tab_tag; ?>">
    <div class="browser-filter">
        <div class="custom-toggle-icon">
            <span class="icon list active" data-id="<?php echo $tab_tag; ?>">List</span>
            <span class="icon grid" data-id="<?php echo $tab_tag; ?>">Grid</span>
        </div>

        <select id="type_filter<?php echo $tab_tag; ?>" data-id="<?php echo $tab_tag; ?>" class="browser-type" multiple="multiple">

            <option value="person" selected="selected">Person</option>
            <option value="place" selected="selected">Place</option>
            <option value="date" selected="selected">Date</option>
            <option value="org" selected="selected">Org</option>
            <option value="event" selected="selected">Event</option>
        </select> 
        <select id="sortDropdown<?php echo $tab_tag; ?>" data-id="<?php echo $tab_tag; ?>" class="browser-sort">
            <option value="">Sort</option>
            <option value="count-asc">Count ↑</option>
            <option value="count-desc">Count ↓</option>
            <option value="type-asc">Type ↑</option>
            <option value="type-desc">Type ↓</option>
            <option value="name-asc">Entity Name ↑</option>
            <option value="name-desc">Entity Name ↓</option>

        </select>
        <div class="browser-search">
            <input type="text" id="browser_search<?php echo $tab_tag; ?>" class="browser-search" data-id="<?php echo $tab_tag; ?>" placeholder="Search">
            <button class="by-voice">Voice</button>
        </div>
    </div>




    <div class="list-section list_<?php echo $tab_tag; ?>">
        <?php
        $stats = [];
        foreach ($interview->annotations as $a) {
            $label = $a['meta']['label'] ?? '';
            $text = $a['text'] ?? '';
            $ref = isset($a['ref']) ? (int) $a['ref'] : PHP_INT_MAX;

            if ($label === '' || $text === '')
                continue;

            if (!isset($stats[$label][$text])) {
                $stats[$label][$text] = [
                    'count' => 0,
                    'first_ref' => $ref,
                ];
            }

            $stats[$label][$text]['count']++;
            if ($ref < $stats[$label][$text]['first_ref']) {
                $stats[$label][$text]['first_ref'] = $ref;
            }
        }

// (Optional) flatten to sort by frequency desc then label/text asc
        $entity_rows = [];
        foreach ($stats as $label => $texts) {
            foreach ($texts as $text => $meta) {
                $entity_rows[] = [
                    'label' => $label,
                    'text' => $text,
                    'count' => $meta['count'],
                    'first_ref' => $meta['first_ref'],
                ];
            }
        }

        usort($entity_rows, function ($a, $b) {
            // frequency desc
            if ($a['count'] !== $b['count'])
                return $b['count'] <=> $a['count'];
            // then label asc, then text asc
            return [$a['label'], $a['text']] <=> [$b['label'], $b['text']];
        });
        ?>

        <table id="entityTable<?php echo $tab_tag; ?>" class="browser-table">
            <thead>
                <tr>
                    <th>Count</th>
                    <th>Type</th>
                    <th>Text/Entity Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entity_rows as $r): ?>
                    <tr class="anno-row"
                        data-ref="<?= htmlspecialchars((string) $r['first_ref'], ENT_QUOTES, 'UTF-8') ?>">
                        <td style="text-align:right;"><?= (int) $r['count'] ?></td>
                        <td><?= htmlspecialchars($r['label'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($r['text'], ENT_QUOTES, 'UTF-8') ?></td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


    </div>
    <div class="grid-section grid_<?php echo $tab_tag; ?>">
        <div class="grid-container grid-container<?php echo $tab_tag; ?>">
            <div class="grid-sizer"></div>
            <?php foreach ($interview->grid as $grid): ?>
                <div class="grid-item grid-item<?php echo $tab_tag; ?> bdg-<?php echo strtolower($grid['label']) ?>" data-ref="<?php echo (int) $grid['first_ref'] ?>" data-label="<?php echo strtolower($grid['label']) ?>"
                     data-text="<?php echo htmlspecialchars($grid['text'], ENT_QUOTES) ?>"
                     data-count="<?php echo (int) $grid['count'] ?>">
                    <img src="<?= $grid['thumbnail_url'] ?: 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' ?>" style="<?= empty($grid['thumbnail_url']) ? 'height:0;' : '' ?>" alt="Thumbnail" loading="lazy" decoding="async">
                    <div class="caption"><?php echo "{$grid['text']} ({$grid['count']})"; ?></div>
                </div>
            <?php endforeach; ?> 

        </div>

    </div>
</div>



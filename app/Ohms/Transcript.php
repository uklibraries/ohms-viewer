<?php

namespace Ohms;

use Laracasts\Transcriptions\Transcription;

class Transcript {

    private $transcript;
    private $chunks;
    private $transcriptHTML;
    private $showFooter = false;
    private $annotatedTerms = array();
    private $index;
    private $indexHTML;
    private $language;
    private $vtt;

    public function __construct($transcript, $timecodes, $index, $translate = false, $lang = '', $vtt = false) {
        $this->transcript = (string) $transcript;
        $this->index = $index;
        $this->chunks = $timecodes;
        $this->language = $lang;
        $this->vtt = $vtt;
        $vtt == true ? $this->formatTranscriptVtt() : $this->formatTranscript();
        $this->formatIndex($translate);
    }
    public function getVTT(){
        return $this->formatTranscriptVtt();
    }
    public function getTranscriptHTML() {
        if (isset($this->transcriptHTML)) {
            return $this->transcriptHTML;
        }
    }

    public function getTranscript() {
        if (isset($this->transcript)) {
            return $this->transcript;
        }
    }

    public function getIndexHTML() {
        if (isset($this->indexHTML)) {
            return $this->indexHTML;
        }
    }

    private function formatIndex($translate) {
        $serverHttps = filter_input(INPUT_SERVER, 'HTTPS', FILTER_SANITIZE_ENCODED, array('options' => array('default' => @$_SERVER['HTTPS'])));
        $serverHttpHost = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_FULL_SPECIAL_CHARS, array('options' => array('default' => $_SERVER['HTTP_HOST'])));
        $serverRequestUri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_FULL_SPECIAL_CHARS, array('options' => array('default' => $_SERVER['REQUEST_URI'])));

        if (!empty($this->index)) {
            if (count($this->index->point) == 0) {
                $this->indexHTML = '';
                return;
            }
            $indexHTML = "<div class=\"accordionHolder\">\n";
            foreach ($this->index->point as $point) {
                $timePoint = $this->formatTimepoint($point->time);
                $synopsis = $translate ? $point->synopsis_alt : $point->synopsis;
                $partial_transcript = $translate ? $point->partial_transcript_alt : $point->partial_transcript;
                $keywords = $translate ? $point->keywords_alt : $point->keywords;
                $subjects = $translate ? $point->subjects_alt : $point->subjects;

                $title = $translate ? $point->title_alt : $point->title;
                $formattedTitle = trim($title, ';');
                $protocol = 'https';
                if (!isset($serverHttps) || $serverHttps != 'on') {
                    $protocol = 'http';
                }
                $host = $serverHttpHost;
                $uri = $serverRequestUri;
                $directSegmentLink = "$protocol://$host$uri#segment{$point->time}";
                $nlPartialTranscript = nl2br($partial_transcript);
                $nlSynopsis = nl2br($synopsis);

                $keywords = explode(';', $keywords);
                asort($keywords);
                $subjects = explode(';', $subjects);
                asort($subjects);
                $formattedKeywords = implode('; ', $keywords);
                $formattedSubjects = implode('; ', $subjects);
                $time = (int) $point->time;

                $indexText = "";
                if (!empty($nlPartialTranscript) && trim($nlPartialTranscript) != "") {
                    $indexText .= '<p><strong>Partial Transcript:</strong> <span>' . $nlPartialTranscript . '</span></p>';
                }
                if (!empty($nlSynopsis) && trim($nlSynopsis) != "") {
                    $indexText .= '<p><strong>Segment Synopsis:</strong> <span>' . $nlSynopsis . '</span></p>';
                }
                if (!empty($formattedKeywords) && trim($formattedKeywords) != "") {
                    $indexText .= '<p><strong>Keywords:</strong> <span>' . $formattedKeywords . '</span></p>';
                }
                if (!empty($formattedSubjects) && trim($formattedSubjects) != "") {
                    $indexText .= '<p><strong>Subjects:</strong> <span>' . $formattedSubjects . '</span></p>';
                }


                /**
                 * MultiValued Fields. GPS Points
                 */
                $gpsHTML = '';
                $gpsPoints = $point->gpspoints;
                if (empty($gpsPoints)) {
                    $point->gpspoints[0]->gps = $point->gps;
                    $point->gpspoints[0]->gps_zoom = (empty($point->gps_zoom) ? '17' : $point->gps_zoom);
                    $point->gpspoints[0]->gps_text = $point->gps_text;
                    $point->gpspoints[0]->gps_text_alt = $point->gps_text_alt;
                }
                $gpsPoints = $point->gpspoints;
                $gpsCounter = 0;
                foreach ($gpsPoints as $singleGpsPoint) {

                    $gps = $singleGpsPoint->gps;
                    $zoom = (empty($singleGpsPoint->gps_zoom) ? '17' : $singleGpsPoint->gps_zoom);
                    $gps_text = $translate == 1 ? $singleGpsPoint->gps_text_alt : $singleGpsPoint->gps_text;

                    if (trim($gps) <> '') {
                        if ($gpsCounter <= 0)
                            $gpsHTML .= ""
                                    . '<div style=" clear: both; "></div>'
                                    . "<div class='multiGPSSection'>";

                        $gpsHTML .= '<strong>GPS:</strong> <a class="fancybox-media nblu" href="' . htmlentities(str_replace(' ', '', 'http://maps.google.com/maps?ll=' . $gps . '&t=m&z=' . $zoom . '&output=embed')) . '">';
                        if ($gps_text <> '') {
                            $gpsHTML .= nl2br($gps_text);
                        } else {
                            $gpsHTML .= 'Link to map';
                        }
                        $gpsHTML .= '</a><br/><strong>Map Coordinates:</strong> ' . $gps . '<br/>';

                        if (count($gpsPoints) > 1 && $gpsCounter < (count($gpsPoints) - 1)) {
                            $gpsHTML .= '<div class="separator"></div>';
                        }
                        if ($gpsCounter == count($gpsPoints) - 1)
                            $gpsHTML .= "</div>";
                    }
                    $gpsCounter++;
                }


                /**
                 * MultiValued Fields. Hyper links.
                 */
                $hyperlinkHTML = '';
                $hyperlinks = $point->hyperlinks;
                if (empty($hyperlinks)) {
                    $point->hyperlinks[0]->hyperlink = $point->hyperlink;
                    $point->hyperlinks[0]->hyperlink_text = $point->hyperlink_text;
                    $point->hyperlinks[0]->hyperlink_text_alt = $point->hyperlink_text_alt;
                }
                $hyperlinks = $point->hyperlinks;
                $hyperlinkCounter = 0;
                foreach ($hyperlinks as $singleHyperlinks) {

                    $hyperlink = $singleHyperlinks->hyperlink;
                    $hyperlink_text = $translate ? $singleHyperlinks->hyperlink_text_alt : $singleHyperlinks->hyperlink_text;
                    if (trim($hyperlink) <> '') {
                        if ($hyperlinkCounter <= 0)
                            $hyperlinkHTML .= ""
                                    . '<div style=" clear: both; "></div>'
                                    . "<div class='multiGPSSection'>";

                        $hyperlinkHTML .= '<strong>Hyperlink:</strong> <a class="fancybox nblu" rel="group" target="_new" href="' . $hyperlink . '">' . nl2br($hyperlink_text) . '</a><br/>';

                        if (count($hyperlinks) > 1 && $hyperlinkCounter < (count($hyperlinks) - 1)) {
                            $hyperlinkHTML .= '<div class="separator"></div>';
                        }
                        if ($hyperlinkCounter == count($hyperlinks) - 1)
                            $hyperlinkHTML .= "</div>";
                    }

                    $hyperlinkCounter++;
                }

                $indexHTML .= <<<POINT
<span><a href="#" class="index_link{$point->time}" id="link{$point->time}">{$timePoint} - {$formattedTitle}</a></span>
<div class="point">
  <p style="margin-bottom:1.2em;">
   <a class="indexJumpLink" href="#" data-timestamp="{$point->time}">Play segment</a>
   <div class="options"><span title="View transcript" id="info_index_{$time}" data-index-time="{$time}" data-id="{$time}" data-type="index-to-transcript" class="mapIndexTranscript alpha-circle index-circle"></span>
   <a title="Share Segment" class="indexSegmentLink" href="javascript:void(0);" data-timestamp="{$point->time}"><span class="segm-circle segment-circle"></span></a></div>
   <br clear="both" />
  </p>
  <div class="segmentLink" id="segmentLink{$point->time}" style="width:100%">
    <strong>Direct segment link:</strong>
    <br />
    <a href="{$directSegmentLink}">{$directSegmentLink}</a><input type="hidden" class="hiddenLink" value="{$directSegmentLink}"><input type="button" value="Copy" class="copyButtonViewer" />
  </div>
  <div class="synopsis"><a name="tp_{$point->time}"></a>
    {$indexText}
    {$gpsHTML}
    {$hyperlinkHTML}
  </div>
</div>
POINT;
            }

            $this->indexHTML = $indexHTML . "</div>\n";
        }
    }

    private function mapIndexSegmentWithTranscript($time_data, $counter) {
        $markerHtml = '';
        foreach ($this->index->point as $singlePoint) {
            $time = (int) $singlePoint->time;
            if ($time >= $time_data['start_time_seconds'] && $time < $time_data['end_time_seconds']) {
                $display_time = $this->formatTimePoint($time);
                $indexMarkerTitle = (string) $singlePoint->title;
                // Generate marker HTML
                $markerHtml = sprintf(
                        '<span id="info_trans_%s" data-time-point="%s" data-id="%d" data-marker-counter="%d" data-marker-id="%s" data-index-title="%s" data-id="%s" data-type="transcript-to-index" class="transcript-info alpha-circle info-circle info_trans_%s"></span>',
                        $time,
                        $display_time,
                        $counter,
                        $counter,
                        $time,
                        htmlspecialchars($indexMarkerTitle, ENT_QUOTES),
                        $counter,
                        $time
                );
                $counter++;
                break;
            }
        }
        return [$markerHtml, $counter];
    }

    /** Optional getter */
    public function getAnnotatedTerms(): array {
        return $this->annotatedTerms;
    }

    /**
     * Parse <annotation ...>...</annotation> blocks into an index keyed by ref.
     * Returns: [ '12' => ['ref'=>'12','number'=>'12','time'=>'410.84', ... , '_inner'=>'COVID'], ... ]
     */
    private function buildAnnotationIndex(string $footNotesText): array {
        $index = [];

        // Match <annotation ref="12" ...>INNER</annotation>
        $pattern = '/<annotation\s+([^>]*\bref="(\d+)"[^>]*)>(.*?)<\/annotation>/s';

        if (preg_match_all($pattern, $footNotesText, $all, PREG_SET_ORDER)) {
            foreach ($all as $m) {
                $attrsStr = $m[1];   // full attributes string
                $ref = $m[2];   // captured ref
                $inner = $m[3];   // inner text (keep as-is)
                // Extract all key="value" pairs from attributes
                $attrs = [];
                if (preg_match_all('/(\w[\w\-]*)="(.*?)"/', $attrsStr, $pairs, PREG_SET_ORDER)) {
                    foreach ($pairs as $p) {
                        $attrs[$p[1]] = $p[2];
                    }
                }

                // Ensure ref present and store inner as special key
                $attrs['ref'] = $ref;
                $attrs['_inner'] = $inner;

                $index[$ref] = $attrs;
            }
        }

        return $index;
    }

    /** Escape attribute values safely for HTML attributes */
    private function escapeAttr(string $v): string {
        return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Convert camelCase / PascalCase / snake_case to kebab-case for data-* keys */
    private function toKebab(string $k): string {
        $k = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $k);
        $k = preg_replace('/[\s_]+/', '-', $k);
        return strtolower($k);
    }

    /**
     * Convert a flat array of attributes to data-* attributes.
     * Example: ['time' => '410.84', 'startOffset' => '20'] =>
     *   data-time="410.84" data-start-offset="20"
     * Skips the special key "_inner".
     */
    private function toDataAttributes(array $attrs): string {
        $parts = [];
        foreach ($attrs as $k => $v) {
            if ($k === '_inner')
                continue;
            $dk = 'data-' . $this->toKebab($k);
            $parts[] = $dk . '="' . $this->escapeAttr((string) $v) . '"';
        }
        return implode(' ', $parts);
    }

    /**
     * FINAL: Two-pass formatter
     * 1) Build transcript HTML and collect all notes (keep <c.N> intact)
     * 2) Build annotation index from combined notes
     * 3) Post-process transcriptHTML: replace <c.N>…</c> with span+footnote
     * 4) Render footnotes block
     */
    private function formatTranscriptVtt() {
        $transcription = Transcription::load($this->transcript);

        $notesBuffer = '';  // collect all NOTE ANNOTATIONS content across lines
        $this->annotatedTerms = [];
        $this->transcriptHTML = '';

        $line_key = 0;
        $counter = 0;

        // Regex for <v speaker>inner</v>
//    $vPattern = '/<v\s*([^>]*)>(.*?)<\/v>/s';
        $vPattern = '/<v\s*([^>]*)>(.*?)(<\/v>|$)/s';
        // ---------- PASS 1: render lines, collect notes, keep <c.N> unchanged ----------
        foreach ($transcription->lines() as $line) {
            $line_key += 1;

            $time_data = $this->split_and_convert_time($line->timestamp->__toString());

            [$markerHtml, $counter] = $this->mapIndexSegmentWithTranscript($time_data, $counter);
            $to_minutes = $time_data['start_time_seconds'] / 60;
            $display_time = $this->formatTimePoint($time_data['start_time_seconds']);

            $html = $markerHtml . '<span class="transcript-line"><p>';
            $html .= "<a href=\"#\" data-timestamp=\"{$to_minutes}\" data-chunksize=\"1\" class=\"jumpLink nblu\">{$display_time}</a>";

            // Speaker (safe)
            if (preg_match($vPattern, $line->body, $m)) {
                $speaker = htmlspecialchars($m[1] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                if ($speaker !== '') {
                    $html .= "<span class=\"speaker\">{$speaker}: </span>";
                }
            }

            // Extract notes if present; DO NOT replace <c.N> yet
            $body = $line->body;
            if (str_contains($body, 'NOTE TRANSCRIPTION END')) {
                $last_point = explode('NOTE TRANSCRIPTION END NOTE ANNOTATIONS BEGIN NOTE', $body, 2);
                $body = str_replace('NOTE TRANSCRIPTION END', '', $last_point[0]);
                if (isset($last_point[1])) {
                    // accumulate notes
                    $notesBuffer .= str_replace('NOTE ANNOTATIONS END', '', $last_point[1]);
                }
            }

            // Remove <v> wrapper but keep inner content
            $body = preg_replace($vPattern, '$2', $body);

            // Keep original <c.N> tokens for Pass 2
            $html .= "<span id='line_{$line_key}' class='transcript_line_{$line_key}'>{$body}</span>";
            $html .= "</p></span>";

            $this->transcriptHTML .= $html;
        }

        // ---------- PASS 2: build index and replace <c.N> with span + footnote ----------
        $annotationIndex = $this->buildAnnotationIndex($notesBuffer);
        
        $this->transcriptHTML = preg_replace_callback(
                '/<c\.(\d+)>(.*?)<\/c>/s',
                function ($m) use ($annotationIndex) {

                    $ref = $m[1];
                    $word = $m[2];

                    // Find attributes for this ref; fall back to minimal set
                    $attrs = $annotationIndex[$ref] ?? ['ref' => $ref, 'text' => $word];

                    // Visible text: prefer annotation text, else the captured word
                    $visible = $word;
                    $text = $attrs['wiki_name'] ?? $attrs['text'];
                    // Classes: bdg-text + bdg-{wiki_label lower}

                    $wikiLabel = strtolower((string) ($attrs['wiki_label'] ?? ($attrs['label'] ?? '')));
                    $cls = 'bdg-text' . ($wikiLabel ? ' bdg-' . preg_replace('/[^a-z0-9\-]+/', '-', $wikiLabel) : '');

                    // Build data-* attributes for all annotation attributes
                    $dataAttrs = $this->toDataAttributes($attrs);
                    $showAnnotation = true;
                    $footnoteHtml = '';
                    if (($attrs['annotationType'] ?? '') === 'conventional' || empty($attrs['annotationType'])) {
                        $showAnnotation = false;
                        $cls = $cls . ' no-annotation';
                    }
                    // Collect term (line/time are unknown in post-pass; set nulls)
                    if ($showAnnotation === true) {
                        $this->annotatedTerms[] = [
                            'ref' => (string) ($attrs['ref'] ?? $ref),
                            't_text' => $visible,
                            'text' => strip_tags($text),
                            'line' => null,
                            'start_time_s' => null,
                            'meta' => array_diff_key($attrs, array_flip(['ref', '_inner'])),
                        ];
                    }
                else {
                    $this->showFooter = true;
                    // Footnote link (kept exactly as before)
                    $footnoteHtml = '<span class="footnote-ref">' .
                            '<a class="marker_sup marker_sup' . $this->escapeAttr($ref) . '" name="sup' . $this->escapeAttr($ref) . '"></a>' .
                            '<a data-tooltip="Test" href="#footnote' . $this->escapeAttr($ref) . '" data-index="footnote' . $this->escapeAttr($ref) . '" id="footnote_' . $this->escapeAttr($ref) . '" class="super_footnotes footnote' . $this->escapeAttr($ref) . ' footnoteLink footnoteTooltip nblu bbold">' . $this->escapeAttr($ref) . '</a>' .
                            '<span></span>' .
                            '</span>';
                }
                    

                    // Final replacement: span token + footnote link

                    return
                            '<span class="' . $this->escapeAttr($cls) . ' ref_' . $this->escapeAttr($ref) . '" data-ref="' . $this->escapeAttr($ref) . '" ' . $dataAttrs . '>' .
                            $visible .
                            '</span>' .
                    $footnoteHtml;
                },
                $this->transcriptHTML
        );

        // ---------- PASS 3: render footnotes block ----------
    if ($notesBuffer !== '' && $this->showFooter == true) {
        $foot_notes = '<div class="footnotes-container"><div class="label-ft">NOTES</div>';

        foreach ($annotationIndex as $ref => $attrs) {
            
            $num   = $attrs['number'] ?? $ref;
            $inner = $attrs['target_sub_id_text'] ?? '';
//            echo '<pre>'; print_r($attrs);exit;
            // Convert [[link]]x[[/link]] to <a>
            $inner = preg_replace('/\[\[link\]\](.*?)\[\[\/link\]\]/', '<a href="$1">$1</a>', $inner);

            $foot_notes .=
                '<div><a name="footnote' . $this->escapeAttr($ref) . '" id="footnote' . $this->escapeAttr($ref) . '" class="marker_footnote marker_footnote' . $this->escapeAttr($ref) . '"></a> ' .
                '<a class="footnoteLink nblu" href="#sup' . $this->escapeAttr($ref) . '">' . $this->escapeAttr((string)$num) . '.</a> ' .
                '<span class="content" id="line_' . $this->escapeAttr((string)$ref) . '">' . $inner . '<p></p><p></p></span></div>';
        }

        $foot_notes .= '</div>';
        $this->transcriptHTML .= $foot_notes;
    }
    }

    private function formatTranscript() {
        // iconv("UTF-8", "ASCII//IGNORE", $this->transcript);
        if (strtolower($this->language) == 'arabic')
            $this->transcriptHTML = $this->transcript;
        else
            $this->transcriptHTML = $this->transcript;

        if (strlen($this->transcriptHTML) == 0) {
            return;
        }
        # quotes
        $this->transcriptHTML = preg_replace('/\"/', "&quot;", $this->transcriptHTML);
        # paragraphs
        $this->transcriptHTML = preg_replace('/Transcript: */', "", $this->transcriptHTML);
        # highlight kw
        # take timestamps out of running text
        $this->transcriptHTML = preg_replace("/{[0-9:]*}/", "", $this->transcriptHTML);
        $this->transcriptHTML = preg_replace('/(.*)\n/msU', "<p>$1</p>\n", $this->transcriptHTML);
        # grab speakers
        $this->transcriptHTML = preg_replace(
                '/<p>[[:space:]]*([A-Z-.\' ]+:)(.*)<\/p>/', "<p><span class=\"speaker\">$1</span>$2</p>", $this->transcriptHTML
        );
        $this->transcriptHTML = preg_replace('/<p>[[:space:]]*<\/p>/', "", $this->transcriptHTML);
        $this->transcriptHTML = preg_replace('/<\/p>\n<p>/ms', "\n", $this->transcriptHTML);
        $this->transcriptHTML = preg_replace('/<p>(.+)/U', "<p class=\"first-p\">$1", $this->transcriptHTML, 1);
        $chunkarray = explode(":", $this->chunks);
        $chunksize = empty($chunkarray[0]) ? 1 : $chunkarray[0];
        $chunklines = array();
        if (count($chunkarray) > 1) {
            $chunkarray[1] = preg_replace('/\(.*?\)/', "", $chunkarray[1]);
            $chunklines = explode("|", $chunkarray[1]);
        }
        (empty($chunklines[0])) ? $chunklines[0] = 0 : array_unshift($chunklines, 0);
        # insert ALL anchors
        $this->transcriptHTML = str_replace(array('[[footnotes]]', '[[/footnotes]]'), '', $this->transcriptHTML);
        $transcript = explode('[[note]]', $this->transcriptHTML);
        $transcriptOnly = $transcript[0];
        $itlines = explode("\n", $transcript[0]);
        unset($transcript[0]);

        foreach ($chunklines as $key => $chunkline) {
            $intervalChunksize = $key * $chunksize;
            $stamp = $intervalChunksize; // . ":00";
            $anchor = <<<ANCHOR
<a href="#" data-timestamp="{$intervalChunksize}" data-chunksize="{$chunksize}" class="jumpLink nblu">{$this->formatTimePoint($stamp * 60)}</a>
ANCHOR;
            $itlines[$chunkline] = $anchor . $itlines[$chunkline];
        }
        $this->transcriptHTML = "";
        $noteNum = 0;
        $supNum = 0;
        $lastKey = 0;

        /**
         * Steps for Formulation.
         */
        $totalWords = str_word_count(strip_tags($transcriptOnly));
        $lKeyChunkLines = count($chunklines) - 1;
        $approxDurationSecs = $lKeyChunkLines * (60 * $chunksize);

        /**
         * Approximate words per seconds. 
         */
        $approxWordsPerSec = round(($totalWords / ($approxDurationSecs + (700 * $chunksize))), 2);

        $wordCountPerLine = 0;
        $currentSyncSlotSecs = 0;
        $nextSyncSlotSecs = 60 * $chunksize;
        $placedMarkers = array();
        $currentMarkerTimeSecs = 0;
        $currentMarkerTitle = "";
        $markerCounter = 0;
        $foundkey = 0;
        $placeIndexMarker = false;
        foreach ($itlines as $key => $line) {

            $markerHtml = "";
            if (strstr($line, '[[footnote]]') !== false) {
                $line = preg_replace(
                        '/\[\[footnote\]\]([0-9]+)\[\[\/footnote\]\]/', '<span class="footnote-ref"><a name="sup$1"></a><a href="#footnote$1" data-index="footnote$1" id="footnote_$1" class="footnote$1 footnoteLink footnoteTooltip nblu bbold">[$1]</a><span></span></span>', $line
                );
            }


            $indexisChanging = false;
            if (in_array($key, $chunklines)) {
                $foundkey = array_search($key, $chunklines);
                $currentSyncSlot = $foundkey * $chunksize;
                if ($chunksize != 1) {
                    $currentSyncSlotSecs = $currentSyncSlot * (60);
                } else {
                    $currentSyncSlotSecs = $currentSyncSlot * (60 * $chunksize);
                }
                $nextSyncSlotSecs = $currentSyncSlotSecs + (60 * $chunksize);
                $wordCountPerLine = 0;
            } else {
                if (in_array($key + 1, $chunklines)) {
                    $indexisChanging = true;
                }
            }

            foreach ($this->index->point as $singlePoint) {
                $time = (int) $singlePoint->time;
                if ($time >= $currentSyncSlotSecs && $time < $nextSyncSlotSecs && !in_array($time, $placedMarkers) && !$placeIndexMarker) {
                    $timeDiffSyncAndIndexSecs = $time - $currentSyncSlotSecs;
                    $wordsToMove = round($approxWordsPerSec * $timeDiffSyncAndIndexSecs);

                    $placeIndexMarker = true;
                    $placedMarkers[] = $time;
                    $currentMarkerTimeSecs = $time;
                    $currentMarkerTitle = (string) $singlePoint->title;
                    $placed = false;
                    break;
                }
            }

            $wordCountPerLine = str_word_count(strip_tags($line)) + $wordCountPerLine;
            if ($placeIndexMarker && !$placed) {
                $timeinm = $currentMarkerTimeSecs / 60;
//                if ($wordsToMove <= $wordCountPerLine || $indexisChanging) {
                $placed = true;
                $placeIndexMarker = false;
                $wordsToMove = 0;

                $timePoint = $this->formatTimePoint($currentMarkerTimeSecs);
                $markerHtml = '<span id="info_trans_' . $currentMarkerTimeSecs . '" data-id="' . $markerCounter . '" data-time-point="' . $timePoint . '" data-marker-counter="' . $markerCounter . '" data-marker-id="' . $currentMarkerTimeSecs . '" data-index-title="' . $currentMarkerTitle . '" class="transcript-info alpha-circle old-info-circle info-circle info_trans_' . $currentMarkerTimeSecs . '"></span>';
                $markerCounter++;
//                }
            }

            if (trim($line) == "" && $key == count($itlines) - 1) {
                $this->transcriptHTML .= "";
            } else {
                $this->transcriptHTML .= "$markerHtml<span class='transcript-line' id='line_$key' class='transcript_line_{$key}'>$line</span>\n";
            }
            $lastKey = $key;
        }
        if (count($transcript) > 0) {
            $footnotesContainer = '<div class="footnotes-container"><div class="label-ft">NOTES</div>';
            foreach ($transcript as $note):
                $noteNum += 1;
                $note = str_replace('[[/note]]', '', $note);
                $matches = array();
                preg_match('/\[\[link\]\](.*)\[\[\/link\]\]/', $note, $matches);
                $footnoteContent = '<span id="line_' . $lastKey . '" class="content transcript_line_' . $lastKey . '">' . $note . '</span>';
                if (isset($matches[1])) {
                    $footnoteLink = $matches[1];
                    $footnoteText = preg_replace('/\[\[link\]\](.*)\[\[\/link\]\]/', '', $note);
                    $footnoteContent = '<span id="line_' . $lastKey . '" class="content transcript_line_' . $lastKey . '"><a class="footnoteLink nblu" href="' . $footnoteLink . '" target="_blank">' . $footnoteText . '</a></span>';
                }
                $lastKey++;
                $note = '<div><a name="footnote' . $noteNum . '" id="footnote' . $noteNum . '"></a>
                    <a class="footnoteLink nblu" href="#sup' . $noteNum . '">' . $noteNum . '.</a> ' . $footnoteContent . '</div>';
                $footnotesContainer .= $note;

            endforeach;
            $this->transcriptHTML .= "$footnotesContainer</div>";
        }
    }

    private function formatShortline($line, $keyword) {
        $shortline = preg_replace("/.*?\s*(\S*\s*)($keyword.*)/i", "$1$2", $line);
        $shortline = preg_replace("/($keyword.{30,}?).*/i", "$1", $shortline);
        $shortline = preg_replace("/($keyword.*\S)\s+\S*$/i", "$1", $shortline);
        $shortline = preg_replace("/($keyword)/mis", "<span class='highlight'>$1</span>", $shortline);
        $shortline = preg_replace('/\"/', "&quot;", $shortline);
        return $shortline;
    }

    private function quoteWords($string) {
        $q_kw = preg_replace('/\'/', '\\\'', $string);
        $q_kw = preg_replace('/\"/', "&quot;", $q_kw);
        return $q_kw;
    }

    private function quoteChange($string) {
        $q_kw = preg_replace('/\'/', "&#39;", $string);
        $q_kw = preg_replace('/\"/', "&quot;", $string);
        $q_kw = trim($q_kw);
        return $q_kw;
    }

    private function stripQuotes($text) {
        $unquoted = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $text);
        return $unquoted;
    }

    public function keywordSearch($keyword) {


        # quote kw for later
        $q_kw = $this->quoteWords($keyword);

        if ($this->vtt) {
            $foot_notes_text = '';
            $json = array('keyword' => $q_kw, 'matches' => []);
            $transcription = Transcription::load($this->transcript);
            $line_key = 0;
            foreach ($transcription->lines() as $line) {
                $line_key += 1;
                $body = $line->body;
                if (str_contains($body, 'NOTE TRANSCRIPTION END')) {
                    $last_point = explode('NOTE TRANSCRIPTION END NOTE ANNOTATIONS BEGIN NOTE', $body);
                    $body = $last_point[0];
                    $foot_notes_text = str_replace('NOTE ANNOTATIONS END', '', $last_point[1]);
                }


                if (preg_match("/{$this->fixAccents($keyword)}/i", $this->fixAccents($body), $matches)) {

                    $shortline = $this->formatShortline($body, $keyword);

                    $json['matches'][] = array('shortline' => $shortline, 'linenum' => $line_key);
                }
            }
            if (!empty($foot_notes_text)) {

                $pattern = '/<annotation ref="\d+">(.*?)<\/annotation>/';
                preg_match_all($pattern, $foot_notes_text, $matches);
                $extracted_text = $matches[1];
                foreach ($extracted_text as $line) {
                    $line_key += 1;
                    if (preg_match("/{$this->fixAccents($keyword)}/i", $this->fixAccents($line), $matches)) {

                        $shortline = $this->formatShortline($line, $keyword);

                        $json['matches'][] = array('shortline' => $shortline, 'linenum' => $line_key);
                    }
                }
            }

            return json_encode($json);
        } else {
            $json = "{ \"keyword\":\"$q_kw\", \"matches\":[";

            //Actual search
            $lines = explode("\n", $this->transcript);

            $startedFootNotes = 0;
            $startedFootNotesCount = 0;

            foreach ($lines as $lineNum => $line) {
                if (trim($line) == "[[footnotes]]") {
                    $startedFootNotes = 1;
                }
                if ($startedFootNotes) {
                    if ($startedFootNotesCount > 0 && (trim($line) == "[[footnotes]]" || trim($line) == "[[/footnotes]]" || trim($line) == "" || strpos($line, "[[note]]") === false)) {
                        unset($lines[$lineNum]);
                    }
                    $startedFootNotesCount++;
                }
            }

            $lines = array_values($lines);
            $totalLines = sizeof($lines);

            foreach ($lines as $lineNum => $line) {
                preg_match_all('/\[\[footnote\]\](.*?)\[\[\/footnote\]\]/', $line, $footnoteMatches);
                $lineMatched = preg_replace('/\[\[footnote\]\](.*?)\[\[\/footnote\]\]/', "", $line);
                if (isset($footnoteMatches[0]) && !empty($footnoteMatches)) {
                    $line = $lineMatched;
                }
                preg_match_all('/\[\[link\]\](.*?)\[\[\/link\]\]/', $line, $linkMatches);
                $linkMatched = preg_replace('/\[\[link\]\](.*?)\[\[\/link\]\]/', "", $line);
                if (isset($linkMatches[0]) && !empty($linkMatches)) {
                    $line = $linkMatched;
                }

                $line = str_replace(array("[[/link]]", "[[link]]", "[[/note]]", "[[note]]", "[[footnotes]]"), " ", $line);

                if (preg_match("/{$this->fixAccents($keyword)}/i", $this->fixAccents($line), $matches)) {

                    $shortline = $this->formatShortline($line, $keyword);

                    if (strstr($json, 'shortline')) {
                        $json .= ',';
                    }
                    $shortline = str_replace(array("[[footnote]]", "[[/footnote]]", "[[note]]", "[[footnotes]]", "[[/footnotes]]", "[[/note]]", "[[link]]", "[[/link]]"), " ", $shortline);
                    $json .= "{ \"shortline\" : \"$shortline\", \"linenum\": $lineNum }";
                }
            }

            return str_replace("\0", "", $json) . ']}';
        }
    }

    public function indexSearch($keyword, $translate) {
        if (!empty($keyword)) {
            $keyword = $q_kw = $this->stripQuotes($keyword);
            $q_kw = $this->quoteWords($keyword);
            $metadata = array(
                'keyword' => $q_kw,
                'matches' => array(),
            );
            foreach ($this->index->point as $point) {
                $synopsis = $translate ? $point->synopsis_alt : $point->synopsis;
                $keywords = $translate ? $point->keywords_alt : $point->keywords;
                $subjects = $translate ? $point->subjects_alt : $point->subjects;
                $time = $point->time;
                $title = $translate ? $point->title_alt : $point->title;
                $timePoint = floor($time / 60) . ':' . str_pad($time % 60, 2, '0', STR_PAD_LEFT);
                $gps = $point->gps;
                $hyperlink = $point->hyperlink;
                //                OHMS-88 Fix
                $partial_transcript = $point->partial_transcript;
                if (preg_match("/{$this->fixAccents($keyword)}/imsU", $this->fixAccents($synopsis)) > 0 || preg_match("/{$this->fixAccents($keyword)}/ismU", $this->fixAccents($title)) > 0 || preg_match("/{$this->fixAccents($keyword)}/ismU", $this->fixAccents($keywords)) > 0 || preg_match("/{$this->fixAccents($keyword)}/ismU", $this->fixAccents($subjects)) > 0 || preg_match("/{$this->fixAccents($keyword)}/ismu", $this->fixAccents($gps)) > 0 || preg_match("/{$this->fixAccents($keyword)}/ismu", $this->fixAccents($hyperlink)) > 0 || preg_match("/{$this->fixAccents($keyword)}/ismu", $this->fixAccents($partial_transcript)) > 0) {
                    //                OHMS-88 Fix ----> END
                    $metadata['matches'][] = array(
                        'time' => (string) $time,
                        'shortline' => $timePoint . ' - ' . $this->quoteChange($title),
                    );
                }
            }
        }
        return json_encode($metadata);
    }

    private function fixAccents($str) {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
        return str_replace($a, $b, $str);
    }

    private function formatTimePoint($time) {
        $hours = floor($time / 3600);
        $minutes = floor(($time - ($hours * 3600)) / 60);
        $seconds = $time - (($hours * 3600) + ($minutes * 60));

        $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);

        return "{$hours}:{$minutes}:{$seconds}";
    }

    private function split_and_convert_time($time_str) {
        // Ensure input contains the expected delimiter
        if (strpos($time_str, ' --> ') === false) {
            throw new InvalidArgumentException("Invalid time string format.");
        }

        list($start_time, $end_time) = explode(" --> ", $time_str);

        // Convert start time to seconds (with milliseconds)
        list($start_hours, $start_minutes, $start_seconds_ms) = explode(":", $start_time);
        list($start_seconds, $start_milliseconds) = explode(".", $start_seconds_ms);

        $start_time_seconds = ((int) $start_hours * 3600) + ((int) $start_minutes * 60) + (int) $start_seconds; // + ((int)$start_milliseconds / 1000);
        // Convert end time to seconds (with milliseconds)
        list($end_hours, $end_minutes, $end_seconds_ms) = explode(":", $end_time);
        list($end_seconds, $end_milliseconds) = explode(".", $end_seconds_ms);

        $end_time_seconds = ((int) $end_hours * 3600) + ((int) $end_minutes * 60) + (int) $end_seconds; // + ((int)$end_milliseconds / 1000);

        return [
            "start_time" => $start_time,
            "end_time" => $end_time,
            "start_time_seconds" => $start_time_seconds,
            "end_time_seconds" => $end_time_seconds,
        ];
    }
}

/* Location: ./app/Ohms/Transcript.php */
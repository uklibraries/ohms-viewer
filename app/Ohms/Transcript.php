<?php

namespace Ohms;

class Transcript {

    private $transcript;
    private $chunks;
    private $transcriptHTML;
    private $index;
    private $indexHTML;
    private $language;

    public function __construct($transcript, $timecodes, $index, $translate = false, $lang = '') {
        $this->transcript = (string) $transcript;
        $this->index = $index;
        $this->chunks = $timecodes;
        $this->language = $lang;
        $this->formatTranscript();
        $this->formatIndex($translate);
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
        if (!empty($this->index)) {
            if (count($this->index->point) == 0) {
                $this->indexHTML = '';
                return;
            }
            $indexHTML = "<div id=\"accordionHolder\">\n";
            foreach ($this->index->point as $point) {
                $timePoint = $this->formatTimepoint($point->time);
                $synopsis = $translate ? $point->synopsis_alt : $point->synopsis;
                $partial_transcript = $translate ? $point->partial_transcript_alt : $point->partial_transcript;
                $keywords = $translate ? $point->keywords_alt : $point->keywords;
                $subjects = $translate ? $point->subjects_alt : $point->subjects;

                $title = $translate ? $point->title_alt : $point->title;
                $formattedTitle = trim($title, ';');
                $protocol = 'https';
                if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
                    $protocol = 'http';
                }
                $host = $_SERVER['HTTP_HOST'];
                $uri = $_SERVER['REQUEST_URI'];
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
                    $gps_text = $_GET['translate'] == '1' ? $singleGpsPoint->gps_text_alt : $singleGpsPoint->gps_text;

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
<span><a href="#" id="link{$point->time}">{$timePoint} - {$formattedTitle}</a></span>
<div class="point">
  <p style="margin-bottom:1.2em;">
   <a class="indexJumpLink" href="#" data-timestamp="{$point->time}">Play segment</a>
   <span title="View transcript" id="info_index_{$time}" data-index-time="{$time}" onclick="toggleRedirectTranscriptIndex({$time}, 'index-to-transcript');" class="alpha-circle index-circle"></span>
   <a title="Share Segment" class="indexSegmentLink" href="javascript:void(0);" data-timestamp="{$point->time}"><span class="segm-circle segment-circle"></span></a>
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

    private function formatTranscript() {
        
        if (strtolower($this->language) == 'arabic')
            $this->transcriptHTML = $this->transcript;
        else
            $this->transcriptHTML = iconv("UTF-8", "ASCII//IGNORE", $this->transcript);
        
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
        $chunksize = (int) $chunkarray[0];
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
            $stamp =  $intervalChunksize . ":00";
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
                        '/\[\[footnote\]\]([0-9]+)\[\[\/footnote\]\]/', '<span class="footnote-ref"><a name="sup$1"></a><a href="#footnote$1" data-index="footnote$1" id="footnote_$1" class="footnoteLink footnoteTooltip nblu bbold">[$1]</a><span></span></span>', $line
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
                if ($wordsToMove <= $wordCountPerLine || $indexisChanging) {
                    $placed = true;
                    $placeIndexMarker = false;
                    $wordsToMove = 0;

                    $timePoint = $this->formatTimePoint($currentMarkerTimeSecs);
                    $markerHtml = '<span id="info_trans_' . $currentMarkerTimeSecs . '" data-time-point="' . $timePoint . '" data-marker-counter="' . $markerCounter . '" data-marker-id="' . $currentMarkerTimeSecs . '" data-index-title="' . $currentMarkerTitle . '" onclick="toggleRedirectTranscriptIndex(' . $markerCounter . ', \'transcript-to-index\');" class="alpha-circle info-circle"></span>';
                    $markerCounter++;
                }
            }

            if (trim($line) == "" && $key == count($itlines) - 1) {
                $this->transcriptHTML .= "";
            } else {
                $this->transcriptHTML .= "$markerHtml<span class='transcript-line' id='line_$key'>$line</span>\n";
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
                $footnoteContent = '<span id="line_' . $lastKey . '" class="content">' . $note . '</span>';
                if (isset($matches[1])) {
                    $footnoteLink = $matches[1];
                    $footnoteText = preg_replace('/\[\[link\]\](.*)\[\[\/link\]\]/', '', $note);
                    $footnoteContent = '<span id="line_' . $lastKey . '" class="content"><a class="footnoteLink nblu" href="' . $footnoteLink . '" target="_blank">' . $footnoteText . '</a></span>';
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

}

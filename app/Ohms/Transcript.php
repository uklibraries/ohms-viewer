<?php

namespace Ohms;

class Transcript {

    private $transcript;
    private $chunks;
    private $transcriptHTML;
    private $index;
    private $indexHTML;

    public function __construct($transcript, $timecodes, $index, $translate = false) {
        $this->transcript = (string) $transcript;
        $this->index = $index;
        $this->chunks = $timecodes;
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
                $gps = $point->gps;
                $zoom = (empty($point->gps_zoom) ? '17' : $point->gps_zoom);
                $gps_text = $translate ? $point->gps_text_alt : $point->gps_text;
                $hyperlink = $point->hyperlink;
                $hyperlink_text = $translate ? $point->hyperlink_text_alt : $point->hyperlink_text;
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
                $gpsHTML = '';
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
                if (trim($gps) <> '') {
                    # XXX: http
                    $mapUrl = htmlentities(
                            str_replace(
                                    ' ', '', 'http://maps.google.com/maps?ll=' . $gps . '&t=m&z=' . $zoom . '&output=embed'
                            )
                    );
                    $gpsHTML = '<br/><strong>GPS:</strong> <a    class="fancybox-media" href="' . $mapUrl . '">';
                    if (trim($gps_text) <> '') {
                        $gpsHTML .= $gps_text;
                    } else {
                        $gpsHTML .= 'Link to map';
                    }
                    $gpsHTML .= '</a><br/><strong>Map Coordinates:</strong> ' . $gps . '<br/>';
                }
                $hyperlinkHTML = '';
                if (trim($hyperlink) <> '') {
                    $hyperlinkHTML = <<<HYPERLINK
<br/>
<strong>Hyperlink:</strong>
<a class="fancybox" rel="group" target="_new" href="{$hyperlink}">{$hyperlink_text}</a><br/>
HYPERLINK;
                }
                $indexHTML .= <<<POINT
<span><a href="#" id="link{$point->time}">{$timePoint} - {$formattedTitle}</a></span>
<div class="point">
  <p>
    <a class="indexJumpLink" href="#" data-timestamp="{$point->time}">Play segment</a>
    <a class="indexSegmentLink" href="#" data-timestamp="{$point->time}">Segment link</a>
    <br clear="both" />
  </p>
  <div class="segmentLink" id="segmentLink{$point->time}" style="width:100%">
    <strong>Direct segment link:</strong>
    <br />
    <a href="{$directSegmentLink}">{$directSegmentLink}</a>
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
        $itlines = explode("\n", $transcript[0]);
        unset($transcript[0]);
        foreach ($chunklines as $key => $chunkline) {
            $stamp = $key * $chunksize . ":00";
            $anchor = <<<ANCHOR
<a href="#" data-timestamp="{$key}" data-chunksize="{$chunksize}" class="jumpLink">{$this->formatTimePoint($stamp * 60)}</a>
ANCHOR;
            $itlines[$chunkline] = $anchor . $itlines[$chunkline];
        }
        $this->transcriptHTML = "";
        $noteNum = 0;
        $supNum = 0;
        foreach ($itlines as $key => $line) {
            if (strstr($line, '[[footnote]]') !== false) {
                $line = preg_replace(
                        '/\[\[footnote\]\]([0-9]+)\[\[\/footnote\]\]/', '<span class="footnote-ref"><a name="sup' . ++$supNum . '"></a><a href="#footnote$1" data-index="footnote$1" class="footnoteLink footnoteTooltip">[$1]</a><span></span></span>', $line
                );
            }
            $this->transcriptHTML .= "<span class='transcript-line' id='line_$key'>$line</span>\n";
        }
        if (count($transcript) > 0) {
            $footnotesContainer = '<div class="footnotes-container"><div class="label-ft">FOOTNOTES</div>';
            foreach ($transcript as $note):
                $noteNum += 1;
                $note = str_replace('[[/note]]', '', $note);
                $matches = array();
                preg_match('/\[\[link\]\](.*)\[\[\/link\]\]/', $note, $matches);
                $footnoteContent = "<span class='content'>$note</span>";
                if (isset($matches[1])) {
                    $footnoteLink = $matches[1];
                    $footnoteText = preg_replace('/\[\[link\]\](.*)\[\[\/link\]\]/', '', $note);
                    $footnoteContent = '<span class="content"><a class="footnoteLink" href="' . $footnoteLink . '" target="_blank">' . $footnoteText . '</a></span>';
                }

                $note = '<div><a name="footnote' . $noteNum . '" id="footnote' . $noteNum . '"></a>
                    <a class="footnoteLink" href="#sup' . $noteNum . '">' . $noteNum . '.</a> ' . $footnoteContent . '</div>';
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
        $totalLines = sizeof($lines);
        foreach ($lines as $lineNum => $line) {
            if (preg_match("/{$this->fixAccents($keyword)}/i", $this->fixAccents($line), $matches)) {
                if ($lineNum < $totalLines - 1) {
                    $line .= ' ' . $lines[$lineNum + 1];
                }
                $shortline = $this->formatShortline($line, $keyword);
                if (strstr($json, 'shortline')) {
                    $json .= ',';
                }
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

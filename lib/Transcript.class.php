<?php
class Transcript {
  private $transcript;
  private $chunks;
  private $transcriptHTML;
  private $index;
  private $indexHTML;

  public function __construct($transcript, $timecodes, $index) {
    $this->transcript = (string)$transcript;
    $this->index = $index;
    $this->chunks = $timecodes;
    $this->formatTranscript();
    $this->formatIndex();
  }

  public function getTranscriptHTML() {
    if(isset($this->transcriptHTML)) {
      return $this->transcriptHTML;
    }
  }

  public function getTranscript() {
    if(isset($this->transcript)) {
      return $this->transcript;
    }
  }

  public function getIndexHTML() {
    if(isset($this->indexHTML)) {
      return $this->indexHTML;
    }
  }

  private function formatIndex() {
    if(!empty($this->index)) {
      if (count($this->index->point) == 0) {
        $this->indexHTML = '';
        return;
      }
      $indexHTML = "<div id=\"accordionHolder\">\n";
      foreach($this->index->point as $point) { 
	$timePoint = (floor((int)$point->time / 60)) . ':' . str_pad(((int)$point->time % 60), 2, '0', STR_PAD_LEFT);
	$synopsis = $point->synopsis;
	$partial_transcript = $point->partial_transcript;
	$keywords = $point->keywords;
	$subjects = $point->subjects;
	$gps = $point->gps;
	$gps_text = $point->gps_text;
	$hyperlink = $point->hyperlink;
	$hyperlink_text = $point->hyperlink_text;

	$indexHTML .= '<h3><a href="#" id="link' . $point->time . '">' . $timePoint . ' - ' . trim($point->title, ';') . "</a></h3>\n";
	$indexHTML .= '<div class="point">' . "\n";
	$indexHTML .= '<p><a class="indexJumpLink" href="#" data-timestamp="' . $point->time  . '">Play segment</a></p>';
    	$indexHTML .= '<div class="synopsis">';
    	$indexHTML .= '<a name="tp_' . $point->time . '"></a>';
    	$indexHTML .= '<p><strong>Partial Transcript:</strong><span>' . $partial_transcript . '</span></p><p><strong>Segment Synopsis:</strong><span> ' . $synopsis . '</span></p><p><strong>Keywords:</strong><span> ' . str_replace(';', '; ', $keywords) . '</span></p><p><strong>Subjects:</strong><span> ' . str_replace(';', ' ', $subjects) . '</span></p>'; 
    	if ($gps <> '') {
		$indexHTML .= '<br/><strong>GPS:</strong> <a  class="fancybox-media" href="' . htmlentities(str_replace(' ', '', 'http://maps.google.com/maps?ll='.$gps.'&t=m&z=10&output=embed')).'">';
	    	if ($gps_text <> '') { 
			$indexHTML .= $gps_text;
	    	} else {
			$indexHTML .= 'Link to map';
	    	}
	 	$indexHTML .= '</a><br/><strong>Map Coordinates:</strong> ' . $gps .'<br/>';	 	
    	}
    	if ($hyperlink <> '') $indexHTML .= '<br/><strong>Hyperlink:</strong> <a class="fancybox" rel="group" href="' . $hyperlink . '">' . $hyperlink_text . '</a><br/>';
    	$indexHTML .= '</div>';
    	$indexHTML .= "\n</div>\n";
      }
      $this->indexHTML = $indexHTML . "</div>\n";
    }
  }

  private function formatTranscript() {
    $this->transcriptHTML = $this->transcript;
    if (strlen($this->transcriptHTML) == 0) {
      return;
    }

    # quotes
    $this->transcriptHTML = preg_replace('/\"/', "&quot;", $this->transcriptHTML);

    # paragraphs
    $this->transcriptHTML = preg_replace('/Transcript: */',"",$this->transcriptHTML);

    # highlight kw

    # take timestamps out of running text
    $this->transcriptHTML = preg_replace("/{[0-9:]*}/","",$this->transcriptHTML);

    $this->transcriptHTML = preg_replace('/(.*)\n/msU',"<p>$1</p>\n",$this->transcriptHTML);

    # grab speakers
    $this->transcriptHTML = preg_replace('/<p>[[:space:]]*([A-Z-.\' ]+:)(.*)<\/p>/',"<p><span class=\"speaker\">$1</span>$2</p>",$this->transcriptHTML);

    $this->transcriptHTML = preg_replace('/<p>[[:space:]]*<\/p>/',"",$this->transcriptHTML);

    $this->transcriptHTML = preg_replace('/<\/p>\n<p>/ms',"\n",$this->transcriptHTML);

    $this->transcriptHTML = preg_replace('/<p>(.+)/U',"<p class=\"first-p\">$1",$this->transcriptHTML, 1); 

    $chunkarray = explode(":",$this->chunks);
    $chunksize = $chunkarray[0];
    $chunklines =array();
    if (count($chunkarray)>1) {
    	$chunkarray[1] = preg_replace('/\(.*?\)/',"",$chunkarray[1]);
    	$chunklines = explode("|", $chunkarray[1]);
     }
    (empty($chunklines[0])) ? $chunklines[0] = 0 : array_unshift($chunklines, 0);

    # insert ALL anchors
    $itlines = explode("\n",$this->transcriptHTML);
    foreach ($chunklines as $key => $chunkline) {
      $stamp = $key*$chunksize . ":00";
      $itlines[$chunkline] = '<a href="#" data-timestamp="' . $key . '" data-chunksize="' . $chunksize . '" class="jumpLink">' . $stamp . '</a>' . $itlines[$chunkline];
    }

    $this->transcriptHTML = "";
    foreach ($itlines as $key => $line) {
      $this->transcriptHTML .= "<span class='transcript-line' id='line_$key'>$line</span>\n";
    }
  }

  private function formatShortline($line, $kw) {
    $shortline = preg_replace("/.*?\s*(\S*\s*)($kw.*)/i","$1$2",$line);
    $shortline = preg_replace("/($kw.{30,}?).*/i","$1",$shortline);
    $shortline = preg_replace("/($kw.*\S)\s+\S*$/i","$1",$shortline);
    $shortline = preg_replace("/($kw)/mis","<span class='highlight'>$1</span>",$shortline);
    $shortline = preg_replace('/\"/', "&quot;", $shortline);

    return $shortline;
  }

  private function quoteWords($kw) {
    $q_kw = preg_replace('/\'/', '\\\'', $kw);
    $q_kw = preg_replace('/\"/', "&quot;", $q_kw);
    return $q_kw;
  }
  
  private function quoteChange($kw) {
    $q_kw = preg_replace('/\'/', "&#39;", $kw);
    $q_kw = preg_replace('/\"/', "&quot;", $kw);
	$q_kw = trim($q_kw);
    return $q_kw;
  }

  public function keywordSearch($kw) {
     # quote kw for later
    $q_kw = $this->quoteWords($kw);
    $json = "{ \"keyword\":\"$q_kw\", \"matches\":[";


    //Actual search
    $lines = explode("\n", $this->transcript);
    $totalLines = sizeof($lines);
    foreach ($lines as $lineNum => $line) {

      if (preg_match("/$kw/i", $line, $matches)) {
	if($lineNum < $totalLines-1) {
	  $line .= ' ' . $lines[$lineNum + 1]; 
	}
	$shortline = $this->formatShortline($line, $kw);
	if(strstr($json, 'shortline')) {
	  $json .= ',';
	}
	$json .= "{ \"shortline\" : \"$shortline\", \"linenum\": $lineNum }";
      }
    }

    return $json . ']}';
  }

  public function indexSearch($kw) {
    if(!empty($kw)){ 
      $q_kw = $this->quoteWords($kw);
      $json = "{ \"keyword\":\"$q_kw\", \"matches\":[";

      foreach($this->index->point as $point) {
	$synopsis = $point->synopsis;
	$keywords = $point->keywords;
	$subjects = $point->subjects;
	$time = $point->time;
	$title = $point->title;
	$timePoint = floor($time / 60) . ':' . str_pad($time % 60, 2, '0', STR_PAD_LEFT);

	if(preg_match("/{$kw}/imsU", $synopsis) > 0
	|| preg_match("/{$kw}/ismU", $title) > 0
	|| preg_match("/{$kw}/ismU", $keywords) > 0
	|| preg_match("/{$kw}/ismU", $subjects) > 0) {
	  if(strstr($json, 'time')) {
	    $json .= ', ';
	  }
	  $json .= '{ "time" :' . $time . ', "shortline" : "' . $timePoint . ' - ' . $this->quoteChange($title) . '" }';
	}
      }
    }

    return str_replace("\0", "", $json) . ']}';
  }
}
?>

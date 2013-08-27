<?php
/*
 *  Model for the XML Version3CacheFile
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link http://www.uky.edu
 * @license http://www.uky.edu
 */

require_once 'Transcript.class.php';

class Version3CacheFile {

  private static $InstanceVersion3 = NULL;
  public $Transcript;
  private $data;
  

  private function __construct($cachefile,$tmpDir,$viewerconfig) {
    if($cachefile) {
      if($myfile = file_get_contents("{$tmpDir}/$cachefile")) {


	libxml_use_internal_errors(true);
	$ohfile = simplexml_load_string($myfile);

	if(!$ohfile) {
	  $error_msg = "Error loading XML.\n<br />\n";
	  foreach(libxml_get_errors() as $error) {
	    $error_msg .= "\t" . $error->message;
	  }
	  throw new Exception($error_msg);
	}
      }else{
	throw new Exception("Invalid Version3CacheFile.");
      }
    }else{
      throw new Exception("Initialization requires valid Version3CacheFile.");
    }

    $this->data = array(
      'cachefile' => $cachefile,
      'title' => (string)$ohfile->record->title,
      'accession' => (string)$ohfile->record->accession,
      'chunks' => (string)$ohfile->record->sync,
      'time_length' => (string)$ohfile->record->duration,
      'collection' => (string)$ohfile->record->collection_name,
      'series' => (string)$ohfile->record->series_name,
      'fmt' => (string)$ohfile->record->fmt,
      'media_url' => (string)$ohfile->record->media_url,
      'file_name' => (string)$ohfile->record->file_name,
      'rights' => (string)$ohfile->record->rights,
      'usage' => (string)$ohfile->record->usage,
      'repository' => (string)$ohfile->record->repository
    );

    # temp fix for mp3 doubling
    $this->data['file_name'] = preg_replace("/\.mp3.mp3$/", ".mp3", $this->data['file_name']);
    $this->data['clipsource'] =  (string)$ohfile->record->mediafile->host;
    $this->data['account_id'] =  (string)$ohfile->record->mediafile->host_account_id;
    $this->data['player_id'] =  (string)$ohfile->record->mediafile->host_player_id;
    $this->data['clip_id'] =  (string)$ohfile->record->mediafile->host_clip_id;
    $this->Transcript = new Transcript($ohfile->record->transcript, $this->data['chunks'], $ohfile->record->index);
    $this->data['transcript'] = $this->Transcript->getTranscriptHTML();
    $this->data['index'] = $this->Transcript->getIndexHTML();

    // Video or audio-only
    $fmt_info = explode(":", $this->data['fmt']);
    if($fmt_info[0] == 'video') {
      $this->data['videoID'] = $fmt_info[1];
      $this->data['hasVideo'] = 1;
    } else {
      $this->data['hasVideo'] = (strstr(strtolower($this->data['file_name']), '.mp4')) ? 2 : 0;
      $this->data['videoID'] = NULL;
    }
    if(!$this->data['hasVideo'] && !(strstr(strtolower($this->data['file_name']), '.mp3')) ) {
      $this->data['file_name'] .= '.mp3';
      $this->data['videoID'] = NULL;
    }

   $players = explode(',',$viewerconfig['players']); 
   $player = strtolower($this->data['clipsource']);
    if (in_array($player, $players)) {
	    $this->data['viewerjs'] = $player;
	    $this->data['playername'] = $player;
     } else {
	    $this->data['viewerjs'] = 'flowplayer';
	    $this->data['playername'] = 'flowplayer';
    }

    // Interviewer, Interviewee
    $interviewer_info = $ohfile->record->interviewer;
	$pieces = array();
    foreach($interviewer_info as $part) {
          $pieces[] = $part;
    }
    $this->data['interviewer'] = implode($pieces, '');

    unset($this->cacheFile);
  }

  private function __clone() {
    //empty
  }

  public function __get($name) {
    if(array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }else{
      $trace = debug_backtrace();
      trigger_error('Undefined property ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
      return null;
    }
  }

  public function hasIndex() {
    return strlen($this->index) > 0;
  }

  public function getFields() {
    return array_keys($this->data);
  }

  public static function getInstanceVersion3($cachefile = NULL,$tmpDir,$viewerconfig) {
    if(!self::$InstanceVersion3) {
      self::$InstanceVersion3 = new Version3CacheFile($cachefile,$tmpDir,$viewerconfig);
    }
    return self::$InstanceVersion3;
  }

  public function toJSON() {
    $keys = array_keys($this->data);
    $pairs = array();
    foreach($keys as $key) {
      $pairs[] = "'{$key}':'{$this->data[$key]}'";
    }
    return '{' . implode(',', $pairs) . '}';
  }
}
?>

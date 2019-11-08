<?php namespace Ohms\Interview;

/*
 *  Model for the XML Version3CacheFile
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link http://www.uky.edu
 * @license http://www.uky.edu
 */

use Ohms\Transcript;
use Ohms\Utils;

class Version3
{
    private static $Instance = null;
    public $Transcript;
    private $data;
    private $xml = null;

    private function __construct($viewerconfig, $tmpDir, $cachefile)
    {
        if ($cachefile) {
            $this->xml = file_get_contents("{$tmpDir}/$cachefile");
            if ($this->xml) {
                libxml_use_internal_errors(true);
                $ohfile = simplexml_load_string($this->xml);

                if (!$ohfile) {
                    $error_msg = "Error loading XML.\n<br />\n";
                    foreach (libxml_get_errors() as $error) {
                        $error_msg .= "\t" . $error->message;
                    }
                    throw new Exception($error_msg);
                }
            } else {
                throw new Exception("Invalid Version3CacheFile.");
            }
        } else {
            throw new Exception("Initialization requires valid Version3CacheFile.");
        }
        
        $this->data = array(
            'cachefile' => $cachefile,
            'title' => (string)$ohfile->record->title,
            'accession' => (string)$ohfile->record->accession,
            'chunks' => (string)$ohfile->record->sync,
            'chunks_alt' => (string)$ohfile->record->sync_alt,
            'time_length' => (string)$ohfile->record->duration,
            'collection' => (string)$ohfile->record->collection_name,
            'series' => (string)$ohfile->record->series_name,
            'series_link' => (string)$ohfile->record->series_link,
            'fmt' => (string)$ohfile->record->fmt,
            'media_url' => (string)$ohfile->record->media_url,
            'file_name' => (string)$ohfile->record->file_name,
            'rights' => (string)$ohfile->record->rights,
            'usage' => (string)$ohfile->record->usage,
            'repository' => (string)$ohfile->record->repository,
            'kembed' => (string)$ohfile->record->kembed,
            'collection_link' => (string)$ohfile->record->collection_link,
            'series_link' => (string)$ohfile->record->series_link,
            'language' => empty($ohfile->record->language) ? 'English' : $ohfile->record->language,
            'transcript_alt_lang' => (string)$ohfile->record->transcript_alt_lang,
            'translate' => (string)$ohfile->record->translate,
            'funding' => (string)$ohfile->record->funding,'avalon_target_domain' => (string)$ohfile->record->mediafile->avalon_target_domain,
            'user_notes' => (string)$ohfile->record->user_notes,
            'collection_link' => (string)$ohfile->record->collection_link,
            'transcript_alt_raw' => (string)$ohfile->record->transcript_alt,
            'transcript_raw' => (string)$ohfile->record->transcript,
            
        );

        $collection_link = ($ohfile->record->collection_link != null) ? (string)$ohfile->record->collection_link : '';
        $series_link = ($ohfile->record->series_link != null) ? (string)$ohfile->record->series_link : '';

        if (!empty($collection_link)) {
            $this->data['collection'] = $this->graylink($this->data['collection'], $collection_link);
        }

        if (!empty($series_link)) {
            $this->data['series'] = $this->graylink($this->data['series'], $series_link);
        }

        # temp fix for mp3 doubling
        $this->data['file_name'] = preg_replace("/\.mp3.mp3$/", ".mp3", $this->data['file_name']);
        $this->data['clipsource'] =    (string)$ohfile->record->mediafile->host;
        $this->data['account_id'] =    (string)$ohfile->record->mediafile->host_account_id;
        $this->data['player_id'] =    (string)$ohfile->record->mediafile->host_player_id;
        $this->data['clip_id'] =    (string)$ohfile->record->mediafile->host_clip_id;
        $this->data['clip_format'] =    (string)$ohfile->record->mediafile->clip_format;
        $translate = $_GET['translate'];
        if ($translate == '1') {
            $this->data['chunks'] = (string)$ohfile->record->sync_alt;
            $transcript = $ohfile->record->transcript_alt;
        } else {
            $this->data['chunks'] = (string)$ohfile->record->sync;
            $transcript = $ohfile->record->transcript;
        }
        $this->Transcript = new Transcript($transcript, $this->data['chunks'], $ohfile->record->index, $translate, $this->data['language']);
        $this->data['transcript'] = $this->Transcript->getTranscriptHTML();
        $this->data['index'] = $this->Transcript->getIndexHTML();
        $this->data['index_points'] = $ohfile->record->index;

        // Video or audio-only
        $fmt_info = explode(":", $this->data['fmt']);
        if ($fmt_info[0] == 'video') {
            if (count($fmt_info) > 1) {
                $this->data['videoID'] = $fmt_info[1];
            }
            $this->data['hasVideo'] = 1;
        } else {
            $this->data['hasVideo'] = (strstr(strtolower($this->data['file_name']), '.mp4')) ? 2 : 0;
            $this->data['videoID'] = null;
        }
        if (!$this->data['hasVideo'] && !(strstr(strtolower($this->data['file_name']), '.mp3'))) {
            $this->data['file_name'] .= '.mp3';
            $this->data['videoID'] = null;
        }

        $players = explode(',', $viewerconfig['players']);
        $player = strtolower($this->data['clipsource']);
        
        if($player == 'aviary'){
            $this->data['media_url'] = Utils::getAviaryUrl($ohfile->record->media_url);
            $this->data['aviaryMediaFormat'] = Utils::getAviaryMediaFormat($this->data['media_url']);
            $player = 'other';
        }else{
            $this->data['media_url'] = $ohfile->record->media_url;
        }
       
        if (in_array($player, $players)) {
            $this->data['viewerjs'] = $player;
            $this->data['playername'] = $player;
        } else {
            $this->data['viewerjs'] = 'other';
            $this->data['playername'] = 'other';
        }

        // Interviewer, Interviewee
        $interviewer_info = $ohfile->record->interviewer;
        $pieces = array();
        foreach ($interviewer_info as $part) {
            $pieces[] = $part;
        }
        $this->data['interviewer'] = implode($pieces, '');
        
        $interviewee_info = $ohfile->record->interviewee;
        $piecese = array();
        foreach($interviewee_info as $parte) {
              $piecese[] = $parte;
        }
        $this->data['interviewee'] = implode($piecese, '');
        unset($this->cacheFile);
    }

    private function __clone()
    {
        //empty
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE
            );
            return null;
        }
    }

    public function hasIndex()
    {
        return strlen($this->index) > 0;
    }

    public function getFields()
    {
        return array_keys($this->data);
    }

    public static function getInstance($viewerconfig, $tmpDir, $cachefile = null)
    {
        if (!self::$Instance) {
            self::$Instance = new Version3($viewerconfig, $tmpDir, $cachefile);
        }
        return self::$Instance;
    }

    public function toJSON()
    {
        $keys = array_keys($this->data);
        $pairs = array();
        foreach ($keys as $key) {
            $pairs[] = "'{$key}':'{$this->data[$key]}'";
        }
        return '{' . implode(',', $pairs) . '}';
    }

    public function toXML()
    {
        return $this->xml;
    }

    private function graylink($label, $href)
    {
        return "<a href=\"{$href}\" target=\"_new\" class=\"graylink\">{$label}</a>";
    }
}

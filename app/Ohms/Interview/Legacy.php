<?php namespace Ohms\Interview;

/*
 *  Model for the XML LegacyCacheFile
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link http://www.uky.edu
 * @license http://www.uky.edu
 */

class Legacy
{
    private static $Instance = null;
    public $Transcript;
    private $data;

    private function __construct($viewerconfig, $tmpDir, $cachefile)
    {
        if ($cachefile) {
            if ($myfile = file_get_contents("{$tmpDir}/$cachefile")) {

                libxml_use_internal_errors(true);
                $ohfile = simplexml_load_string($myfile);

                if (!$ohfile) {
                    $error_msg = "Error loading XML.\n<br />\n";
                    foreach (libxml_get_errors() as $error) {
                        $error_msg .= "\t" . $error->message;
                    }
                    throw new Exception($error_msg);
                }
            } else {
                throw new Exception("Invalid LegacyCacheFile.");
            }
        } else {
            throw new Exception("Initialization requires valid LegacyCacheFile.");
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
            'repository' => (string)$ohfile->record->repository,
            'funding' => (string)$ohfile->record->funding,
            'avalon_target_domain' => (string)$ohfile->record->mediafile->avalon_target_domain,
        'user_notes' => (string)$ohfile->record->user_notes
        );

        # temp fix for mp3 doubling
        $this->data['file_name'] = preg_replace("/\.mp3.mp3$/", ".mp3", $this->data['file_name']);

        $this->Transcript = new Transcript($ohfile->record->transcript, $this->data['chunks'], $ohfile->record->index);
        $this->data['transcript'] = $this->Transcript->getTranscriptHTML();
        $this->data['index'] = $this->Transcript->getIndexHTML();

        // Video or audio-only
        $fmt_info = explode(":", $this->data['fmt']);
        if ($fmt_info[0] == 'video') {
            $this->data['videoID'] = $fmt_info[1];
            $this->data['hasVideo'] = 1;
        } else {
            $this->data['hasVideo'] = (strstr(strtolower($this->data['file_name']), '.mp4')) ? 2 : 0;
            $this->data['videoID'] = null;
        }
        if (!$this->data['hasVideo'] && !(strstr(strtolower($this->data['file_name']), '.mp3'))) {
            $this->data['file_name'] .= '.mp3';
            $this->data['videoID'] = null;
        }

        // Interviewer, Interviewee
        $interviewer_info = $ohfile->record->interviewer;

        $pieces = array();
        foreach ($interviewer_info as $part) {
            $pieces[] = $part;
        }
        $this->data['interviewer'] = implode($pieces, '');
        $this->data['viewerjs'] = 'legacy';
        $this->data['playername'] = 'legacy';

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
                'Undefined property ' .
                $name . ' in ' . $trace[0]['file'] .
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
            self::$Instance = new Legacy($cachefile, $tmpDir, $viewerconfig);
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
}

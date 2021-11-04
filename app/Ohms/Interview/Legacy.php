<?php
namespace Ohms\Interview;

use Exception;
use Ohms\Utils;
use Ohms\Transcript;

/**
 * Model for the XML LegacyCacheFile
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link      http://www.uky.edu
 * @license   https://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 */
class Legacy extends AbstractInterview
{
    /**
     * @param array       $viewerConfig
     * @param string|null $tmpDir
     * @param string|null $cacheFile
     * @throws Exception
     */
    protected function __construct(array $viewerConfig, ?string $tmpDir, ?string $cacheFile)
    {
        $xml = Utils::loadXMLFile($tmpDir, $cacheFile, get_class($this));

        $this->data = array(
            'cachefile'            => $cacheFile,
            'title'                => (string)$xml->record->title,
            'accession'            => (string)$xml->record->accession,
            'chunks'               => (string)$xml->record->sync,
            'time_length'          => (string)$xml->record->duration,
            'collection'           => (string)$xml->record->collection_name,
            'series'               => (string)$xml->record->series_name,
            'fmt'                  => (string)$xml->record->fmt,
            'media_url'            => (string)$xml->record->media_url,
            'file_name'            => (string)$xml->record->file_name,
            'rights'               => (string)$xml->record->rights,
            'usage'                => (string)$xml->record->usage,
            'repository'           => (string)$xml->record->repository,
            'funding'              => (string)$xml->record->funding,
            'avalon_target_domain' => (string)$xml->record->mediafile->avalon_target_domain,
            'user_notes'           => (string)$xml->record->user_notes,
        );

        // temp fix for mp3 doubling
        $this->data['file_name'] = preg_replace("/\.mp3.mp3$/", ".mp3", $this->data['file_name']);

        // build transcript
        $this->Transcript         = new Transcript($xml->record->transcript, $this->data['chunks'], $xml->record->index);
        $this->data['transcript'] = $this->Transcript->getTranscriptHTML();
        $this->data['index']      = $this->Transcript->getIndexHTML();

        // Video or audio-only
        $fmt_info = explode(":", $this->data['fmt']);
        if ($fmt_info[0] == 'video') {
            $this->data['videoID']  = $fmt_info[1];
            $this->data['hasVideo'] = 1;
        } else {
            $this->data['hasVideo'] = (strstr(strtolower($this->data['file_name']), '.mp4')) ? 2 : 0;
            $this->data['videoID']  = null;
        }
        if (!$this->data['hasVideo'] && !(strstr(strtolower($this->data['file_name']), '.mp3'))) {
            $this->data['file_name'] .= '.mp3';
            $this->data['videoID']   = null;
        }

        // Interviewer, Interviewee
        $interviewer_info = $xml->record->interviewer;

        $pieces = array();
        foreach ($interviewer_info as $part) {
            $pieces[] = $part;
        }
        $this->data['interviewer'] = implode('', $pieces);
        $this->data['viewerjs']    = 'legacy';
        $this->data['playername']  = 'legacy';

        unset($this->cacheFile);
    }
}

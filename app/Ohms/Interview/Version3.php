<?php
namespace Ohms\Interview;

use Exception;
use Ohms\Transcript;
use Ohms\Utils;

/**
 * Model for the XML Version3CacheFile
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link      http://www.uky.edu
 * @license   https://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 */
class Version3 extends AbstractInterview
{
    protected ?string $xml = null;

    /**
     * @param array       $viewerConfig
     * @param ?string     $tmpDir
     * @param string|null $cacheFile
     * @throws Exception
     */
    protected function __construct(array $viewerConfig, ?string $tmpDir, ?string $cacheFile)
    {
        $xml = Utils::loadXMLFile($tmpDir, $cacheFile, get_class($this));

        $this->data = array(
            'cachefile'           => $cacheFile,
            'title'               => (string)$xml->record->title,
            'accession'           => (string)$xml->record->accession,
            'chunks'              => (string)$xml->record->sync,
            'chunks_alt'          => (string)$xml->record->sync_alt,
            'time_length'         => (string)$xml->record->duration,
            'collection'          => (string)$xml->record->collection_name,
            'series'              => (string)$xml->record->series_name,
            'series_link'         => (string)$xml->record->series_link,
            'fmt'                 => (string)$xml->record->fmt,
            'media_url'           => (string)$xml->record->media_url,
            'file_name'           => (string)$xml->record->file_name,
            'rights'              => (string)$xml->record->rights,
            'usage'               => (string)$xml->record->usage,
            'repository'          => (string)$xml->record->repository,
            'kembed'              => (string)$xml->record->kembed,
            'collection_link'     => (string)$xml->record->collection_link,
            'series_link'         => (string)$xml->record->series_link,
            'language'            => empty($xml->record->language) ? 'English' : $xml->record->language,
            'transcript_alt_lang' => (string)$xml->record->transcript_alt_lang,
            'translate'           => (string)$xml->record->translate,
            'funding'             => (string)$xml->record->funding, 'avalon_target_domain' => (string)$xml->record->mediafile->avalon_target_domain,
            'user_notes'          => (string)$xml->record->user_notes,
            'collection_link'     => (string)$xml->record->collection_link,
            'transcript_alt_raw'  => (string)$xml->record->transcript_alt,
            'transcript_raw'      => (string)$xml->record->transcript,

        );

        $collection_link = ($xml->record->collection_link != null) ? (string)$xml->record->collection_link : '';
        $series_link     = ($xml->record->series_link != null) ? (string)$xml->record->series_link : '';

        if (!empty($collection_link)) {
            $this->data['collection'] = $this->graylink($this->data['collection'], $collection_link);
        }

        if (!empty($series_link)) {
            $this->data['series'] = $this->graylink($this->data['series'], $series_link);
        }

        // temp fix for mp3 doubling
        $this->data['file_name']   = preg_replace("/\.mp3.mp3$/", ".mp3", $this->data['file_name']);
        $this->data['clipsource']  = (string)$xml->record->mediafile->host;
        $this->data['account_id']  = (string)$xml->record->mediafile->host_account_id;
        $this->data['player_id']   = (string)$xml->record->mediafile->host_player_id;
        $this->data['clip_id']     = (string)$xml->record->mediafile->host_clip_id;
        $this->data['clip_format'] = (string)$xml->record->mediafile->clip_format;

        // translate
        $translate = $_GET['translate'];
        if ($translate == '1') {
            $this->data['chunks'] = (string)$xml->record->sync_alt;
            $transcript           = $xml->record->transcript_alt;
        } else {
            $this->data['chunks'] = (string)$xml->record->sync;
            $transcript           = $xml->record->transcript;
        }

        // build transcript
        $this->Transcript           = new Transcript($transcript, $this->data['chunks'], $xml->record->index, $translate, $this->data['language']);
        $this->data['transcript']   = $this->Transcript->getTranscriptHTML();
        $this->data['index']        = $this->Transcript->getIndexHTML();
        $this->data['index_points'] = $xml->record->index;

        // Video or audio-only
        $fmt_info = explode(":", $this->data['fmt']);
        if ($fmt_info[0] == 'video') {
            if (count($fmt_info) > 1) {
                $this->data['videoID'] = $fmt_info[1];
            }
            $this->data['hasVideo'] = 1;
        } else {
            $this->data['hasVideo'] = (strstr(strtolower($this->data['file_name']), '.mp4')) ? 2 : 0;
            $this->data['videoID']  = null;
        }
        if (!$this->data['hasVideo'] && !(strstr(strtolower($this->data['file_name']), '.mp3'))) {
            $this->data['file_name'] .= '.mp3';
            $this->data['videoID']   = null;
        }

        $players = explode(',', $viewerConfig['players']);
        $player  = strtolower($this->data['clipsource']);

        if ($player == 'aviary') {
            $this->data['media_url']         = Utils::getAviaryUrl($xml->record->media_url);
            $this->data['aviaryMediaFormat'] = Utils::getAviaryMediaFormat($this->data['media_url']);
            $player                          = 'other';
        } else {
            $this->data['media_url'] = $xml->record->media_url;
        }

        if (in_array($player, $players)) {
            $this->data['viewerjs']   = $player;
            $this->data['playername'] = $player;
        } else {
            $this->data['viewerjs']   = 'other';
            $this->data['playername'] = 'other';
        }

        // Interviewer, Interviewee
        $interviewer_info = $xml->record->interviewer;
        $pieces           = array();
        foreach ($interviewer_info as $part) {
            $pieces[] = $part;
        }
        $this->data['interviewer'] = implode('', $pieces);

        $interviewee_info = $xml->record->interviewee;
        $piecese          = array();
        foreach ($interviewee_info as $parte) {
            $piecese[] = $parte;
        }
        $this->data['interviewee'] = implode('', $piecese);
        unset($this->cacheFile);
    }

    /**
     * @return string
     */
    public function toXML(): string
    {
        return $this->xml;
    }

    /**
     * @param string $label
     * @param string $href
     * @return string
     */
    protected function graylink(string $label, string $href): string
    {
        return "<a href=\"{$href}\" target=\"_new\" class=\"graylink\">{$label}</a>";
    }
}

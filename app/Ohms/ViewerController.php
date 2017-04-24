<?php namespace Ohms;

use Ohms\Interview;

class ViewerController
{
    private $interview;
    private $interviewName;
    private $tmpDir;
    private $config;
    public function __construct($interviewName)
    {
        $this->config = parse_ini_file("config/config.ini", true);
        $this->interview = Interview::getInstance($this->config, $this->config['tmpDir'], $interviewName);
        $this->interviewName = $interviewName;
    }

    public function route($action, $kw, $interviewName)
    {
        switch($action) {
            case 'metadata':
                header('Content-type: application/json');
                echo $this->interview->toJSON();
                exit();
                break;
            case 'xml':
                header('Content-type: application/xml');
                echo $this->interview->toXML();
                exit();
                break;
            case 'transcript':
                echo $this->interview->getTranscript();
                break;
            case 'search':
                if (isset($kw)) {
                    echo $this->interview->Transcript->keywordSearch($kw);
                }
                exit();
                break;
            case 'index':
                if (isset($kw)) {
                    echo $this->interview->Transcript->indexSearch($kw);
                }
                exit();
                break;
            case 'all':
                break;
            default:
                $interview = $this->interview;
                $interviewName = $this->interviewName;
                $config = $this->config;
                include_once 'tmpl/viewer.tmpl.php';
                break;
        }
    }
}

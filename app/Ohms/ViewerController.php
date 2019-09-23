<?php namespace Ohms;

use Ohms\Interview;
use Ohms\CustomPdf;

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
            case 'pdf':
                CustomPdf::__prepare($this->interview,$this->config);
                exit();
            break;
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
                    $translate = ($_GET['translate'] == '1' ? 1 : 0);
                    echo $this->interview->Transcript->indexSearch($kw, $translate);
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

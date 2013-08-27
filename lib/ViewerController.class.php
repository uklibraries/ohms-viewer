<?php
session_start();
require_once 'lib/CacheFile.class.php';



class ViewerController {
  private $cacheFile;
  private $cacheFileName;
  private $tmpDir;
  private $config;
  public function __construct($cacheFileName) {
    $this->config = parse_ini_file("config/config.ini",true);
    $this->cacheFile = CacheFile::getInstance($cacheFileName,$this->config['tmpDir'],$this->config);
    $this->cacheFileName = $cacheFileName;

  }

  public function route($action, $kw, $cacheFileName) {
  
    switch($action) {
      case 'metadata':
	header('Content-type: application/json');
	echo $this->cacheFile->toJSON();
	exit();
	break;
      case 'transcript':
	echo $this->cacheFile->getTranscript();
	break;
      case 'search':

	  if(isset($kw)) {

	    echo $this->cacheFile->Transcript->keywordSearch($kw);
	  }

	exit();
	break;
      case 'index':
	  if(isset($kw)) {

	    echo $this->cacheFile->Transcript->indexSearch($kw);
	  }

	exit();
	break;
      case 'all':
	break;
      default:
	$cacheFile = $this->cacheFile;
	$cacheFileName = $this->cacheFileName;
     $config = $this->config;
	include_once 'tmpl/viewer.tmpl.php';
	break;
    }
  }
}

?>

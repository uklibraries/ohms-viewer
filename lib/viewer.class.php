<?php


function run_in_background($Command, $Priority = 0) {
    if($Priority) {
        shell_exec("nohup nice -n $Priority $Command 2> /dev/null > /dev/null &");
    } else {
        shell_exec("nohup $Command 2> /dev/null > /dev/null &");
    }
}

function is_process_running($PID)
{
    exec("ps $PID", $ProcessState);
    return(count($ProcessState) >= 2);
}

class ViewerController {
  public function __construct() {}
  public function route($action, $cacheFileName, $kw) {
    try {
      $cacheFile = CacheFile::getInstance($cacheFileName);
    }catch(Exception $e) {
      echo $e.msg;
    }

    switch($action) {
      case 'info':
	header('Content-type: application/json');
	echo $cacheFile->toJSON();
	exit();
	break;
      case 'player':
	include_once 'tmpl/player.tmpl.php';
	exit();
	break;
      case 'transcript':
	echo $cacheFile->transcript;
	break;
      case 'all':
	break;
      default:
	break;
    }
  }
}
      /*
     <script type="text/javascript">
	var cachefile = '<?php echo $cachefile; ?>';
	var pkw = '<?php echo $pkw; ?>'
      </script>
      */
?>

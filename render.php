<?php


require_once 'lib/ViewerController.class.php';

if(isset($_REQUEST['cachefile'])) {
  $kw = (isset($_REQUEST['kw'])) ? $_REQUEST['kw'] : NULL;
  $action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : NULL;
  $vController = new ViewerController($_REQUEST['cachefile']);
  $vController->route($action, $kw, $_REQUEST['cachefile']);
}else{
  header('HTTP/1.0 404 Not Found');
  //echo 'Error no action to take.';
  exit();
}

?>
<?php
use Ohms\ViewerController;

require_once 'app/init.php';

session_start();

if (!isset($_GET['translate'])) {
    $_GET['translate'] = '0';
}

if (isset($_REQUEST['cachefile'])) {
    $kw = (isset($_REQUEST['kw'])) ? $_REQUEST['kw'] : null;
    $action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : null;
    $vController = new ViewerController($_REQUEST['cachefile']);
    $vController->route($action, $kw, $_REQUEST['cachefile']);
} else {
    header('HTTP/1.0 404 Not Found');
    //echo 'Error no action to take.';
    exit();
}

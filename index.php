<?php

use Ohms\Utils;
use Ohms\ViewerController;

require_once 'app/init.php';

session_start();

if (!isset($_GET['translate'])) {
    $_GET['translate'] = '0';
}

if (!empty($_REQUEST['cachefile'])) {
    try {
        $kw          = $_REQUEST['kw'] ?? null;
        $action      = $_REQUEST['action'] ?? null;
        $vController = new ViewerController($_REQUEST['cachefile']);
        $vController->route($action, $kw);
    } catch (Exception $e) {
        Utils::die404();
        // Utils::die404($e->getMessage());
    }
} else {
    Utils::die404();
    // Utils::die404('Error no action to take.');
}

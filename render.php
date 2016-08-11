<?php

use Ohms\ViewerController;

require_once 'vendor/autoload.php';

session_start();

if (!isset($_GET['translate'])) {
	$_GET['translate'] = '0';
}

if (!isset($_REQUEST['cachefile']) || empty($_REQUEST['cachefile'])) {
	header('Content-Type: text/plain', true, 404);
	echo "Invalid request: missing cache file parameter.\n";
	exit();
}

try {
	$kw = (isset($_REQUEST['kw'])) ? $_REQUEST['kw'] : null;
	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : null;
	$vController = new ViewerController($_REQUEST['cachefile']);
	$vController->route($action, $kw, $_REQUEST['cachefile']);
} catch (Exception $e) {
	header('Content-Type: text/plain', true, 500);
	echo "Internal error.\n";
	echo "{$e->getCode()}: {$e->getMessage()}\n";
	echo $e->getTraceAsString();
}

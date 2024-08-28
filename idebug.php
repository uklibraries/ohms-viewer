<?php

/**
 * OHMS Viewer
 * @author     Nouman Tayyab <nouman@weareavp.com>
 * @copyright  Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @license    http://www.gnu.org/licenses/ GNU GENERAL PUBLIC LICENSE
 * @link       https://ams.americanarchive.org
 */
use Ohms\ViewerController;

ini_set('display_errors',1);
error_reporting(E_ALL);
require_once 'app/init.php';

session_start();

$translate = (int) filter_input(INPUT_GET, 'translate', FILTER_VALIDATE_INT, array('options' => array('default' => 0)));
$file = filter_input(INPUT_GET, 'cachefile');
$keyword = filter_input(INPUT_GET, 'kw');
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_ENCODED);
$external = filter_input(INPUT_GET, 'external', FILTER_VALIDATE_BOOLEAN, array('options' => array('default' => false)));

if (empty($file)):
    header('HTTP/1.0 404 Not Found');
    exit();
else:
    $vController = new ViewerController($file, $external, $translate);
    $vController->route($action, $keyword, $file);

endif;

/* Location: ./index.php */
<?php namespace Ohms;

/*
 *  Model for the XML CacheFile
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link http://www.uky.edu
 * @license http://www.uky.edu
 */

use Ohms\Interview\Legacy;
use Ohms\Interview\Version3;

class Interview
{
    public static function getInstance($config, $configtmpDir, $cachefile = null)
    {
        $viewerconfig = $config;
        $tmpDir = $configtmpDir;
        if ($cachefile) {
            if ($myxmlfile = file_get_contents("{$tmpDir}/$cachefile")) {
                libxml_use_internal_errors(true);
                $filecheck = simplexml_load_string($myxmlfile);

                if (!$myxmlfile) {
                    $error_msg = "Error loading XML.\n<br />\n";
                    foreach (libxml_get_errors() as $error) {
                        $error_msg .= "\t" . $error->message;
                    }
                    throw new Exception($error_msg);
                }
            } else {
                throw new Exception("Invalid CacheFile.");
            }
        } else {
            throw new Exception("Initialization requires valid CacheFile.");
        }

        $cacheversion = (string)$filecheck->record->version;
        if ($cacheversion=='') {
            return Legacy::getInstance($viewerconfig, $tmpDir, $cachefile);
        } else {
            return Version3::getInstance($viewerconfig, $tmpDir, $cachefile);
        }
    }
}

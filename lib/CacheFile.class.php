<?php
/*
 *  Model for the XML CacheFile
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link http://www.uky.edu
 * @license http://www.uky.edu
 */

class CacheFile {
	public static function getInstance($cachefile = NULL, $configtmpDir, $config) {
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
			}
			else {
				throw new Exception("Invalid CacheFile.");
			}
		}
		else {
			throw new Exception("Initialization requires valid CacheFile.");
		}

		$cacheversion = (string)$filecheck->record->version;
		if ($cacheversion=='') {
			require_once 'LegacyCacheFile.class.php';
			return LegacyCacheFile::getInstanceLegacy($cachefile, $tmpDir, $viewerconfig);
		}
		else {
			require_once 'Version3CacheFile.class.php';
			return Version3CacheFile::getInstanceVersion3($cachefile, $tmpDir, $viewerconfig);
		}
	}
}
?>

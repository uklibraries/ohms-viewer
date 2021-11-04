<?php
namespace Ohms;

use Exception;
use Ohms\Interview\Legacy;
use Ohms\Interview\Version3;
use Ohms\Interview\AbstractInterview;

/**
 * Model for the XML CacheFile
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link      http://www.uky.edu
 * @license   https://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 */
class Interview
{
    /**
     * @param array       $config
     * @param string      $tmpDir
     * @param string|null $cacheFile
     * @return AbstractInterview
     * @throws Exception
     */
    public static function getInstance(array $config, string $tmpDir, ?string $cacheFile = null): AbstractInterview
    {
        $xml = Utils::loadXMLFile($tmpDir, $cacheFile);

        if (!empty($xml->record->version)) {
            return Version3::getInstance($config, $tmpDir, $cacheFile);
        }
        return Legacy::getInstance($config, $tmpDir, $cacheFile);
    }
}

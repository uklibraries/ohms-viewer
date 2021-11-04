<?php
namespace Ohms;

use Exception;
use SimpleXMLElement;

/**
 * Utils Class, Contains commonly used utility Methods.
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link      http://www.uky.edu
 * @license   https://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 */
class Utils
{
    /**
     * Halt execution with a 404 error and optional message.
     *
     * @param string $message
     */
    public static function die404(string $message = '404 Not Found')
    {
        header('HTTP/1.0 404 Not Found', true);
        echo $message;
        exit();
    }

    /**
     * Parse seconds into HH:MM:SS.
     *
     * @param int $time
     * @return string
     */
    public static function formatTimePoint(int $time): string
    {
        $hours   = floor($time / 3600);
        $minutes = floor(($time - ($hours * 3600)) / 60);
        $seconds = $time - (($hours * 3600) + ($minutes * 60));

        $hours   = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);

        return "{$hours}:{$minutes}:{$seconds}";
    }

    /**
     * Get Parsed URL for Aviary player in particular.
     *
     * @param string $embed
     * @return string
     */
    public static function getAviaryUrl(string $embed): string
    {
        // Create DOM from URL or file
        $content  = file_get_html($embed);
        $mediaURL = "";
        if ($content != "") {

            $source = $content->find('source', 0);
            if ($source) {

                if ($source->src) {
                    $mediaURL = $source->src;
                }
            }
        }
        return $mediaURL;
    }

    /**
     * Get Aviary MediaFormat
     *
     * @param string $aviaryUrl
     * @return string
     */
    public static function getAviaryMediaFormat(string $aviaryUrl): string
    {
        $parsedUrl   = parse_url($aviaryUrl);
        $mediaFormat = pathinfo($parsedUrl['path'], PATHINFO_EXTENSION);
        $mediaFormat = (strtolower($mediaFormat) == 'mp4v') ? "mp4" : $mediaFormat;

        return $mediaFormat;
    }

    /**
     * @param string      $tmpDir
     * @param string|null $cacheFile
     * @param string      $cacheFileType
     * @return SimpleXMLElement|false
     * @throws Exception
     */
    public static function loadXMLFile(string  $tmpDir,
                                       ?string $cacheFile = null,
                                       string  $cacheFileType = '')
    {
        if ($cacheFile) {
            $path = "$tmpDir/$cacheFile";
            if (is_readable($path) && $contents = file_get_contents($path)) {

                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($contents);

                if (!$xml) {
                    $error_msg = "Error loading XML.\n<br />\n";
                    foreach (libxml_get_errors() as $error) {
                        $error_msg .= "\t".$error->message;
                    }
                    throw new Exception($error_msg);
                }

                return $xml;
            } else {
                throw new Exception("Invalid {$cacheFileType}CacheFile.");
            }
        } else {
            throw new Exception("Initialization requires valid {$cacheFileType}CacheFile.");
        }
    }

    /**
     * Determine whether currently in HTTP or HTTPS.
     *
     * @return string
     */
    public static function protocol(): string
    {
        $protocol = 'http';
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
            $protocol .= 's';
        }
        return $protocol;
    }

}

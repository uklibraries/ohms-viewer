<?php

namespace Ohms;

use simple_html_dom;

/**
 * Utils Class, Contains commonly used utility Methods. 
 */
class Utils {

    /**
     * Get Parsed URL for Aviary player in particular.
     * @param string $embed
     * @return string
     */
    public static function getAviaryUrl($embed) {

        // Create DOM from URL or file
        $content = file_get_html($embed);
        $mediaURL = "";
        if ($content != "") {

            $source = $content->find('source', 0);
            if ($source) {

                if ($source->src) {
                    $parsedUrl = parse_url($source->src);
                    $mediaURL = $parsedUrl['scheme'] . "://" . $parsedUrl['host'] . "" . $parsedUrl['path'];
                }
            }
        }
        return $mediaURL;
    }

}

?>
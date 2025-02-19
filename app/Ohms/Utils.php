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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $embed);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        // Create DOM from URL or file
        $content = str_get_html($response);
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
     * @param type $aviaryUrl
     * @return type
     */
    public static function getAviaryMediaFormat($aviaryUrl) {
        $parsedUrl = parse_url($aviaryUrl);
        $mediaFormat = pathinfo($parsedUrl['path'], PATHINFO_EXTENSION);
        $mediaFormat = (strtolower($mediaFormat) == 'mp4v') ? "mp4" : $mediaFormat;

        return $mediaFormat;
    }
    
    public static function formatTimePoint($time) {
        $hours = floor($time / 3600);
        $minutes = floor(($time - ($hours * 3600)) / 60);
        $seconds = $time - (($hours * 3600) + ($minutes * 60));

        $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);

        return "{$hours}:{$minutes}:{$seconds}";
    }
    
    public static function splitAndConvertTime($time_str) {
        // Split the input string into start and end times
        list($start_time, $end_time) = explode(" --> ", $time_str);

        // Convert start time to seconds
        list($hours, $minutes, $seconds_ms) = explode(":", $start_time);
        list($seconds, $milliseconds) = explode(".", $seconds_ms);
        $start_time_seconds = ($hours * 3600) + ($minutes * 60) + intval($seconds); //+ (intval($milliseconds) / 1000);

        return array(
            "start_time" => $start_time,
            "end_time" => $end_time,
            "start_time_seconds" => $start_time_seconds
        );
    }

}
/* Location: ./app/Ohms/Utils.php */
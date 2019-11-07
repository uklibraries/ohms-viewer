<?php

/*
 *  Model for the XML CacheFile
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link http://www.uky.edu
 * @license http://www.uky.edu
 */

namespace Ohms;

use TCPDF;

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        $this->SetFont('helvetica', '', 10);
        $inTitle = INTERVIEW_TITLE;
        $inRepo = INTERVIEW_REPO;
        $transcriptHtml = <<<EOD
                <div style="text-align:center;color:#797979;">
                    <span>$inTitle</span>
                <br>
                    <span>$inRepo</span>
                </div>
EOD;

        $this->writeHTML($transcriptHtml, true, false, true, false, '');
    }

    // Page footer
    public function Footer() {
        $this->SetFont('helvetica', '', 10);
        $inRepo = INTERVIEW_REPO_FOOTER;
        $pageNum = $this->PageNo();
        $transcriptHtml = "  ";
        if ((int) $pageNum > 1) {

            $contactUs = "";
            $contactEmail = CONTACT_EMAIL;
            $contactLink = CONTACT_LINK;

            if (CONTACT_EMAIL != "" || CONTACT_LINK != "") :
                $contactUs = <<<EOD
                    $contactEmail
                    <br>
                    $contactLink
                                
EOD;
            endif;

            $transcriptHtml = <<<EOD
                <div style="text-align:center;color:#797979;">
                    <span>$inRepo</span>
                    <br>
                    $contactUs
                </div>
EOD;
        }
        $this->writeHTML($transcriptHtml, true, false, true, false, '');
    }

}

class CustomPdf {

    private function __construct() {
        ini_set('memory_limit', '-1');
        set_time_limit(300);
    }

    /**
     * Prepare PDF file
     * 
     * @param object $cacheFile
     * @param array $config
     */
    public function __prepare($cacheFile, $config) {
        
        $repository = $cacheFile->repository;
        $copyRights = "";
        $tmpDir = $config['tmpDir'];
        if (isset($config[$repository]['copyrightholder']) && !empty($config[$repository]['copyrightholder'])) {
            $copyRights = "© " . $config[$repository]['copyrightholder'];
        }
        $contactEmail = "";
        if (isset($config[$repository]['contactemail']) && !empty($config[$repository]['contactemail'])) {
            $contactEmail = '<a style="font-size:10px;" href="mailto:' . $config[$repository]['contactemail'] . '"><b>' . $config[$repository]['contactemail'] . '</b></a>';
        }
        $contactLink = "";
        if (isset($config[$repository]['contactlink']) && !empty($config[$repository]['contactlink'])) {
            $contactLink = '<a style="font-size:10px;" href="' . $config[$repository]['contactlink'] . '"><b>' . $config[$repository]['contactlink'] . '</b></a>';
        }



        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('OHMS');
        $pdf->SetTitle($cacheFile->title);
        define('INTERVIEW_TITLE', $cacheFile->title);
        define('INTERVIEW_REPO', $cacheFile->repository);
        define('INTERVIEW_REPO_FOOTER', $copyRights);
        define('CONTACT_EMAIL', $contactEmail);
        define('CONTACT_LINK', $contactLink);

        $pdf->SetHeaderData('', '', '', '');
        $pdf->SetFooterData('', '', '', '');
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(20, PDF_MARGIN_TOP, 20);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(20);


        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setFontSubsetting(true);

        $pdf->SetFont('Helvetica', '', 12, '', true);
        $pdf->setTextShadow(array('enabled' => false, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));


        $pdf->AddPage();
        $interviewee = (!empty((string) $cacheFile->interviewee)) ? "$cacheFile->interviewee" : "";
        $interviewer = (!empty((string) $cacheFile->interviewer)) ? "$cacheFile->interviewer, Interviewer" : "";
        $accession = (!empty((string) $cacheFile->accession)) ? $cacheFile->accession : '';

        $spacer25 = '<div style="line-height: 25px;"> </div>';
        $spacer18 = '<div style="line-height: 18px;"> </div>';
        $spacer15 = '<div style="line-height: 15px;"> </div>';
        $spacer12 = '<div style="line-height: 12px;"> </div>';
        $spacer10 = '<div style="line-height: 10px;"> </div>';
        $templateDiv = '<div style="text-align:justify;font-size:10px; line-height: 10px;font-weight:bold;text-align:center;">';
        $templateDivL = '<div style="text-align:justify;font-size:10px; line-height: 10px;font-weight:bold;text-align:left;">';
        $templateDivCl = '</div>';
        $collection = "";
        $series = "";
        $seriesTop = "";

        if ((string) $cacheFile->collection_link != '' && (string) $cacheFile->collection != ''):
            $collection = $templateDiv . 'Collection Title: <a href="' . $cacheFile->collection_link . '"><b>' . $cacheFile->collection . '</b></a>' . $templateDivCl;
        elseif ((string) $cacheFile->collection != ''):
            $collection = $templateDiv . "Collection Title: " . $cacheFile->collection . $templateDivCl;
        endif;

        if ((string) $cacheFile->series_link != '' && (string) $cacheFile->series != ''):
            $series = $templateDiv . 'Series Title: <a href="' . $cacheFile->series_link . '"><b>' . $cacheFile->series . '</b></a>' . $templateDivCl;
        elseif ((string) $cacheFile->series != ''):
            $series = $templateDiv . "Series Title: " . $cacheFile->series . $templateDivCl;
        endif;

        if ((string) $cacheFile->series_link != '' && (string) $cacheFile->series != ''):
            $seriesTop = '<div style="text-align:justify;font-size:11px; line-height: 10px;font-weight:bold;text-align:left;">Series Link: <span style="font-weight:normal;font-size:10px;line-height: 16px;">' . "<a style='font-weight:bold;'  href='$cacheFile->series_link'><b>$cacheFile->series</b></a></span></div>";
        elseif ((string) $cacheFile->series != ''):
            $seriesTop = '<div style="text-align:justify;font-size:11px; line-height: 10px;font-weight:bold;text-align:left;">Series : <span style="font-weight:normal;font-size:10px;line-height: 16px;">' . $cacheFile->series . "</span></div>";
        endif;

        $contactUs = "";
        if ($contactLink != "" || $contactEmail != "") :
            $contactUs = <<<EOD
                <div style="text-align:justify;font-size:11px; line-height: 10px;font-weight:bold;text-align:left;">Contact us: 
                    $contactEmail
                    <span>              </span>
                    $contactLink
                </div>                 
EOD;
        endif;

        $rights = ((string) $cacheFile->rights != '') ? $templateDivL . 'Rights Statement: <span style="font-weight:normal;line-height: 16px;">' . ((string) $cacheFile->rights) . '</span></div>' : "";
        $usage = ((string) $cacheFile->usage != '') ? $templateDivL . 'Usage Statement: <span style="font-weight:normal;line-height: 16px;">' . ((string) $cacheFile->usage) . '</span></div>' : "";
        $userNotes = ((string) $cacheFile->user_notes != '') ? $templateDivL . 'User Note: <span style="font-weight:normal;line-height: 16px;">' . ((string) $cacheFile->user_notes) . '</span></div>' : "";

        $acknowledgment = ((string) $cacheFile->funding != '') ? '<div style="text-align:justify;font-size:11px; line-height: 10px;font-weight:bold;text-align:left;">Acknowledgment: <span style="font-weight:normal;font-size:10px;line-height: 16px;">' . ((string) $cacheFile->funding) . '</span></div>' : "";

        $copyRight = $copyRights;

        $firstPageHtml = <<<EOD
                $spacer25
                <div style="font-size:17px; font-weight:bold; text-align:center;">$cacheFile->title</div>
                <div style="text-align:justify;font-size:11px; line-height: 11px;font-weight:bold;text-align:center;">$cacheFile->repository</div>  
                $spacer15
                {$templateDiv}Interviewee: $interviewee{$templateDivCl} 
                {$templateDiv}Interviewer: $interviewer{$templateDivCl}
                {$templateDiv}$accession{$templateDivCl}
                $spacer12
                $collection
                $series
                $spacer18
                $rights
                $usage
                $spacer10
                $userNotes
                $spacer12
                $acknowledgment
                $seriesTop
                $contactUs
                $spacer18
                <div style="text-align:justify;font-size:9px; line-height: 10px;font-weight:bold;text-align:left;">$copyRight</div>
                        
EOD;

        $pdf->writeHTMLCell(0, 0, '', '', $firstPageHtml, 0, 1, 0, true, '', true);
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);
        /**
         * Index Points
         */
        if (isset($cacheFile->index_points->point) && count($cacheFile->index_points->point) > 0) {

            $indexs = self::getIndexHtml($cacheFile->index_points);
            $pdf->AddPage();
            $indexHtml = <<<EOD
                $spacer15
                <div style="font-size:14px; font-weight:bold; text-align:center;">INDEX</div>
                $indexs
EOD;
            $pdf->writeHTMLCell(0, 0, '', '', $indexHtml, 0, 1, 0, true, '', true);
        }


        /**
         * Transcript.
         */
        $sections = explode("\n", ((isset($_GET['translate']) && $_GET['translate'] == '1') ? $cacheFile->transcript_alt_raw : $cacheFile->transcript_raw));
        $syncPoints = $cacheFile->chunks;

        parse_str($_SERVER['QUERY_STRING']);
        $url = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']. "?cachefile=$cachefile";

        if (isset($sections) && count($sections) > 0) {

            $formattedTranscriptHtml = self::getFormattedTranscript($sections, $syncPoints, $url);

            $pdf->AddPage();

            $transcriptHtml1 = <<<EOD
               href="$url"
EOD;
            $formattedTranscriptHtml = str_replace("id='replaceStamp'", $transcriptHtml1, $formattedTranscriptHtml);


            $transcriptHtml = <<<EOD
                $spacer15
                <div style="font-size:12px; font-weight:bold; text-align:center;">TRANSCRIPT</div>
                    <div style="font-size:10;">
                $formattedTranscriptHtml
                    </div>
EOD;
            $pdf->writeHTMLCell(0, 0, '', '', $transcriptHtml, 0, 1, 0, true, '', true);
        }
        $cwdir = getcwd() . '/pdfs/';
        if (!is_dir($cwdir)) {
            @mkdir($cwdir);
        }
        if (!is_dir($cwdir)) {
            $cwdir = $tmpDir . '/pdfs/';
            @mkdir($cwdir);
            if (!is_dir($cwdir)) {
                echo "Unable to create pdfs folder [Permission Denied]. Please create 'pdfs' folder in main code repository.";
            }
        }
        $pdf->Output($cwdir . 'Interview-export' . time() . '.pdf', 'FD');
    }

    /**
     * Get Index HTML
     * 
     * @param array $indexPoints
     * @return string
     */
    public function getIndexHtml($indexPoints) {


        parse_str($_SERVER['QUERY_STRING']);
        $indexHTML = "";
        foreach ($indexPoints->point as $point) {

            $timePoint = self::getTimestamp($point->time);
            $synopsis = (isset($_GET['translate']) && $_GET['translate'] == '1') ? $point->synopsis_alt : $point->synopsis;
            $partial_transcript = (isset($_GET['translate']) && $_GET['translate'] == '1') ? $point->partial_transcript_alt : $point->partial_transcript;
            $keywords = (isset($_GET['translate']) && $_GET['translate'] == '1') ? $point->keywords_alt : $point->keywords;
            $subjects = (isset($_GET['translate']) && $_GET['translate'] == '1') ? $point->subjects_alt : $point->subjects;

            $time = (int) $point->time;
            $indexHTML .= '<div style="line-height: 0px;" nobr="true">';
            $title = $_GET['translate'] == '1' ? $point->title_alt : $point->title;
            $indexHTML .= '<div style="text-align:justify;font-size:10px; line-height: 5px;font-weight:bold;text-align:left;"><a  style="line-height: 15px;font-size:12px;font-weight:bold;" href="' . ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?cachefile=$cachefile" . '#segment' . $point->time . '" id="link' . $point->time . '"><b>' . $timePoint . ' - ' . trim($title, ';') . "</b></a></div>";
            $indexHTML .= '<div style="line-height: 10px;"> </div>';
            if (!empty($partial_transcript) && trim($partial_transcript) != "")
                $indexHTML .= '<div style=" line-height: 8px;" ><span style="line-height: 15px;font-size:10px;font-weight:bold;">Partial Transcript:</span> <span style="line-height: 15px;font-size:10px;">' . nl2br($partial_transcript) . '</span></div>';
            if (!empty($synopsis) && trim($synopsis) != "")
                $indexHTML .= '<div style="line-height: 8px;"><span style="font-size:10px;line-height: 15px;font-weight:bold;">Segment Synopsis:</span><span style="line-height: 15px;font-size:10px;"> ' . nl2br($synopsis) . '</span></div>';
            if (!empty($keywords) && trim($keywords) != "") {
                $keywords = explode(';', $keywords);
                asort($keywords);
                $indexHTML .= '<div style="line-height: 8px;"><span style="font-size:10px;line-height: 15px;font-weight:bold;">Keywords:</span><span style="font-size:10px;line-height: 15px;"> ' . implode('; ', $keywords) . '</span></div>';
            }
            if (!empty($subjects) && trim($subjects) != "") {
                $subjects = explode(';', $subjects);
                asort($subjects);
                $indexHTML .= '<div style="line-height: 8px;"><span style="font-size:10px;line-height: 15px;font-weight:bold;">Subjects:</span><span style="font-size:10px;line-height: 15px;"> ' . implode('; ', $subjects) . '</span></div>';
            }
            $indexHTML .= "</div>";
        }
        return $indexHTML;
    }

    /**
     * Get TimeStamp.
     * 
     * @param string $time
     * @return string
     */
    private static function getTimestamp($time) {
        $hours = floor($time / 3600);
        $minutes = floor(($time - ($hours * 3600)) / 60);
        $seconds = $time - (($hours * 3600) + ($minutes * 60));

        $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);

        return "{$hours}:{$minutes}:{$seconds}";
    }

    /**
     * Convert To Hours Mins format
     * 
     * @param string $time
     * @return array
     */
    private static function convertToHoursMins($time) {
        $hours = "00";
        $minutes = "00";
        if ($time > 0) {
            $hours = sprintf("%02d", floor($time / 60));
            $minutes = sprintf("%02d", ($time % 60));
        }
        return array('hours' => $hours, "minutes" => $minutes);
    }

    /**
     * Get the Formatted Cleaned Transcript HTML.
     * 
     * @param string $sections
     * @param array $syncPoints
     * @param string $url
     * @return string
     */
    private static function getFormattedTranscript($sections, $syncPoints, $url) {

        $intervalIncrement = 1;
        if (!empty($syncPoints)) {
            $syncSplit = explode(':|', $syncPoints);
            $intervalIncrement = $syncSplit[0];
            $syncing = str_replace($intervalIncrement . ':|', '', $syncPoints);

            $syncArray = explode('|', $syncing);
            foreach ($syncArray as $k => $v) {
                $points = explode('(', $v);
                $points[1] = str_replace(')', '', $points[1]);
                $syncArray[$k] = $points;
            }
        } else {
            $syncArray = array();
        }

        $tempMinForSecCounter = 0;
        $seconds = "00";
        $timestampCounter = 1;

        foreach ($syncArray as $points):

            $lineNo = $points[0];
            $wordNo = $points[1] - 1;


            $splittedWords = explode(' ', $sections[$lineNo]);
            $stamp = "[00:00:00]";
            if ($intervalIncrement == 0.50) {
                $hrmins = self::convertToHoursMins($tempMinForSecCounter);
                $hours = $hrmins['hours'];
                $mins = $hrmins['minutes'];
                $stamp = "[$hours:$mins:$seconds]";

                if ($seconds == "00") {
                    $seconds = '30';
                } else {
                    $tempMinForSecCounter++;
                    $seconds = '00';
                }

                $timestampCounter++;
            } else {
                $hrmins = self::convertToHoursMins($timestampCounter);
                $hours = $hrmins['hours'];
                $mins = $hrmins['minutes'];

                $stamp = "[$hours:$mins:00]";
                $timestampCounter = $timestampCounter + $intervalIncrement;
            }
            $sections[$lineNo] = "<p><span><a id='replaceStamp'><b>" . $stamp . '</b></a></span></p>' . $sections[$lineNo];

        endforeach;

        $formattedTranscriptHtml = implode("\n", $sections);

        # quotes
        $formattedTranscriptHtml = preg_replace('/\"/', "&quot;", $formattedTranscriptHtml);

        # paragraphs
        $formattedTranscriptHtml = preg_replace('/Transcript: */', "", $formattedTranscriptHtml);

        # highlight kw
        # take timestamps out of running text
        $formattedTranscriptHtml = preg_replace("/{[0-9:]*}/", "", $formattedTranscriptHtml);

        $formattedTranscriptHtml = preg_replace('/(.*)\n/msU', "<p>$1</p>\n", $formattedTranscriptHtml);

        # grab speakers
        $formattedTranscriptHtml = preg_replace('/<p>[[:space:]]*([A-Z-.\' ]+:)(.*)<\/p>/', "<p><span style=\"font-weight:bold;\">$1</span>$2</p>", $formattedTranscriptHtml);

        $formattedTranscriptHtml = preg_replace('/<p>[[:space:]]*<\/p>/', "", $formattedTranscriptHtml);

        $formattedTranscriptHtml = preg_replace('/<\/p>\n<p>/ms', "\n", $formattedTranscriptHtml);

        $formattedTranscriptHtml = preg_replace('/<p>(.+)/U', "<p class=\"first-p\">$1", $formattedTranscriptHtml, 1);



        $formattedTranscriptHtml = str_replace(array('[[footnotes]]', '[[/footnotes]]'), '', $formattedTranscriptHtml);
        $transcript = explode('[[note]]', $formattedTranscriptHtml);
        $formattedTranscriptHtml = $transcript[0];
        unset($transcript[0]);



        if (count($transcript) > 0) {
            $footnotesContainer = '<div class="footnotes-container"><div class="label-ft"><b>NOTES</b></div>';
            foreach ($transcript as $note):
                $noteNum += 1;
                $note = str_replace('[[/note]]', '', $note);
                $matches = array();
                preg_match('/\[\[link\]\](.*)\[\[\/link\]\]/', $note, $matches);
                $footnoteContent = '<span id="line_' . $lastKey . '" class="content">' . $note . '</span>';
                if (isset($matches[1])) {
                    $footnoteLink = $matches[1];
                    $footnoteText = preg_replace('/\[\[link\]\](.*)\[\[\/link\]\]/', '', $note);
                    $footnoteContent = '<span id="line_' . $lastKey . '" class="content"><a class="footnoteLink nblu" href="' . $footnoteLink . '" target="_blank">' . $footnoteText . '</a></span>';
                }
                $lastKey++;
                $note = '<div><a name="footnote' . $noteNum . '" id="footnote' . $noteNum . '" style="color:black;"></a>
                    <a class="footnoteLink nblu" href="#sup' . $noteNum . '" style="color:black;">' . $noteNum . '.</a> ' . $footnoteContent . '</div>';
                $footnotesContainer .= $note;

            endforeach;
            $formattedTranscriptHtml .= "$footnotesContainer</div>";
        }

        $formattedTranscriptHtml = preg_replace(
                '/\[\[footnote\]\]([0-9]+)\[\[\/footnote\]\]/', '<span class="footnote-ref"><a name="sup$1" style="color:black;"></a><a href="#footnote$1" data-index="footnote$1" id="footnote_$1" style="color:black;" class="footnoteLink footnoteTooltip nblu bbold">[$1]</a><span></span></span>', $formattedTranscriptHtml
        );
        $formattedTranscriptHtml = preg_replace("/\xEF\xBB\xBF/", "", $formattedTranscriptHtml);
        return $formattedTranscriptHtml;
    }

}

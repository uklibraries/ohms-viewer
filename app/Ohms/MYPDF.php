<?php
namespace Ohms;

use TCPDF;

/**
 * Extend the TCPDF class to create custom Header and Footer
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link      http://www.uky.edu
 * @license   https://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 */
class MYPDF extends TCPDF
{

    /**
     * Page header
     */
    public function Header(): void
    {
        $this->SetFont('helvetica', '', 10);
        $inTitle        = INTERVIEW_TITLE;
        $inRepo         = INTERVIEW_REPO;
        $transcriptHtml = <<<EOD
                <div style="text-align:center;color:#797979;">
                    <span>$inTitle</span>
                <br>
                    <span>$inRepo</span>
                </div>
EOD;

        $this->writeHTML($transcriptHtml, true, false, true, false, '');
    }

    /**
     * Page footer
     */
    public function Footer(): void
    {
        $this->SetFont('helvetica', '', 10);
        $inRepo         = INTERVIEW_REPO_FOOTER;
        $pageNum        = $this->PageNo();
        $transcriptHtml = "  ";
        if ((int)$pageNum > 1) {

            $contactUs    = "";
            $contactEmail = CONTACT_EMAIL;
            $contactLink  = CONTACT_LINK;

            if (CONTACT_EMAIL != "" || CONTACT_LINK != "") {
                $contactUs = <<<EOD
                    $contactEmail
                    <br>
                    $contactLink

EOD;
            }

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

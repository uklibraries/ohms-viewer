<?php
$lang = $interview->transcript_alt_lang;
if (isset($_GET['panel'])) {
    $panel = $_GET['panel'];
}
$transcript_option = 'selected="selected"';
$toggleSwitch = '';
$index_option = '';
if ((isset($panel) && $panel == '1') || ($interview->hasIndex() && (!isset($panel) || $panel != '0'))) {
    $transcript_option = '';
    $index_option = 'selected="selected"';
    $toggleSwitch = 'checked="checked"';
}
$isTranslate = "0";
if (isset($_GET['translate']) && $_GET['translate'] == '1') {
    $targetLanguage = $interview->language;
    $isTranslate = "1";
} else {
    $targetLanguage = $interview->transcript_alt_lang;  
}
$tAvailable = "";
if (isset($_GET['t_available']) && $_GET['t_available'] == '1') {
    $tAvailable = "1";
}

$toggleClass= "non-greyed-out";
$toggleAvailable = "";
$toggleAttr = "";
$toggleLanguageSwitch = "";

if((($interview->index == '' && ($interview->transcript != '' || strpos($interview->transcript, 'No transcript') === false)) 
        || ($interview->index != '' && ($interview->transcript == '' || strpos($interview->transcript, 'No transcript') !== false))) && $isTranslate == "0" || $tAvailable == "1"){
    $toggleClass = "greyed_out";
    $toggleAttr = 'disabled="diabled"';
    $toggleAvailable  = "hide";
    $toggleLanguageSwitch = "translate-toggle-preview";
}

if ($interview->hasIndex()) {
    $searchThisLabel = 'Index';
} else {
    $searchThisLabel = 'Transcript';
}
$toggleDisplay = "";
//if (!$interview->transcript)
//    $toggleDisplay = "display:none;";
?>

<input type="hidden" id="is_translate" value="<?php echo $isTranslate;?>">
<div id="search-toggle"  class="<?php echo $toggleAvailable;?>" >
    <span class="toggle-txt-info">Transcript</span>

    <div class="switch" style="<?php echo $toggleDisplay; ?>">
        <div style="display:none;">Toggle Index/Transcript View Switch.</div>
        <input class="switch-input" type="checkbox" title="Toggle Display Switch" id="toggle_switch" name="toggle_switch" <?php echo $toggleSwitch; echo $toggleAttr;?>>
        <label for="toggle_switch" class="switch-label">Toggle Index/Transcript View Switch.</label>
    </div>
    <span class="toggle-txt-info">Index</span>
    <label for="search-type" style="display:none;">Search Type</label>
    <select id="search-type" title="Search Type" style="display: none;">
        <option id="search-transcript" value="0" <?php echo $transcript_option ?>>Transcript</option>
        <option id="search-index" value="1" <?php echo $index_option ?>>Index</option>
    </select>
<!--    <button id="print-pdf" title="Print" class="print-btn"><i class="fa fa-print"></i> Print</button>-->
</div>

<?php if ($interview->translate == '1'): ?>
    <div id="translate-toggle" class="<?php echo $toggleLanguageSwitch; ?>">
        <a href="#" id="translate-link" data-lang="<?php echo $interview->language ?>"
           data-translate="<?php $interview->transcript_alt_lang; ?>"
           data-toggleAvailable="<?php echo $toggleAvailable;?>"
           data-linkto="<?php echo $targetLanguage ?>">Switch to
            <?php echo $targetLanguage ?></a>
    </div>
    <br/>
<?php
endif;
?>
<span id="alert"></span>
<form id="search-form" onSubmit="return false;" name="search-form" class="preview-search-form">
    <fieldset>
        <legend align="center" id="search-legend"><span class="search-label">Search this <?php echo $searchThisLabel ?></span>
            <span class="search-show-info"><i class="fa fa-lg fa-caret-right"></i></span>
            <span class="search-hide-info"><i class="fa fa-lg fa-caret-down"></i></span>
        </legend>
        <div class="search-content">
            <label for="kw" style="display:none;">Search keyword field</label>
            <input class="kw-empty" title="Search keyword field" id="kw" name="kw" size="30" value="Keyword"/>
            <button class="search-button" id="submit-btn">Go</button>
            <a href="#" class="searchclear-button" id="clear-btn">X</a>
            <div id="search-results"></div>
        </div>
    </fieldset>
</form>



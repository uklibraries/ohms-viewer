<?php
$lang = $interview->transcript_alt_lang;
if (isset($_GET['panel'])) {
    $panel = $_GET['panel'];
}
$transcript_option = 'selected="selected"';
$index_option = '';
if ((isset($panel) && $panel == 'Index') || ($interview->hasIndex() && (!isset($panel) || $panel != 'Transcript'))) {
    $transcript_option = '';
    $index_option = 'selected="selected"';
}
if (isset($_GET['translate']) && $_GET['translate'] == '1') {
    $targetLanguage = $interview->language;
} else {
    $targetLanguage = $interview->transcript_alt_lang;
}
if ($interview->hasIndex()) {
    $searchThisLabel = 'Index';
} else {
    $searchThisLabel = 'Transcript';
}
?>
<div id="search-toggle">
  <select id="search-type">
    <option id="search-transcript" <?php echo $transcript_option ?>>Transcript</option>
    <option id="search-index" <?php echo $index_option ?>>Index</option>
  </select>
</div>
<?php if($interview->translate == '1'): ?>
    <div id="translate-toggle">
      <a href="#" id="translate-link" data-lang="<?php echo $interview->language ?>"
         data-translate="<?php $interview->transcript_alt_lang; ?>"
         data-linkto="<?php echo $targetLanguage ?>">Switch to
         <?php echo $targetLanguage ?></a>
    </div>
    <br/>
    <?php
endif;
?>
<span id="alert"></span>
<form id="search-form" onSubmit="return false;" name="search-form">
  <fieldset><legend id="search-legend">Search This <?php echo $searchThisLabel ?></legend>
    <input class="kw-empty" id="kw" name="kw" size="30" value="Keyword" /><br />
    <a href="#" class="search-button" id="submit-btn">Search</a>
    <a href="#" class="searchclear-button" id="clear-btn">Clear</a>
  </fieldset>
</form>
<div id="search-results"></div>

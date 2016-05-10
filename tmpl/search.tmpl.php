<?php
	$lang = $cacheFile->__get('transcript_alt_lang');
?>
<div id="search-toggle">
  <select id="search-type">
    <?php if ($cacheFile->hasIndex()) { ?>
    <option id="search-transcript">Transcript</option>  
    <option id="search-index" selected="selected">Index</option>
    <?php } else { ?>
    <option id="search-transcript" selected="selected">Transcript</option>  
    <option id="search-index">Index</option>
    <?php } ?>
  </select>
</div>
<?php if($cacheFile->translate == '1'): ?>
    <div id="translate-toggle">
        <a href="#" id="translate-link" data-lang="<?php echo $cacheFile->language ?>" data-translate="<?php $cacheFile->transcript_alt_lang; ?>" data-linkto="<?php echo ((isset($_GET['translate']) && $_GET['translate'] == '1') ? $cacheFile->language : $cacheFile->transcript_alt_lang); ?>">Switch to <?php echo ((isset($_GET['translate']) && $_GET['translate'] == '1') ? $cacheFile->language : $cacheFile->transcript_alt_lang); ?></a>
    </div>
    <br />
<?php endif; ?>

<span id="alert"></span>

<form id="search-form" onSubmit="return false;" name="search-form">
    <?php if ($cacheFile->hasIndex()) { ?>
  <fieldset><legend id="search-legend">Search This Index</legend>
    <?php } else { ?>
  <fieldset><legend id="search-legend">Search This Transcript</legend>
    <?php } ?>
  <input class="kw-empty" id="kw" name="kw" size="30" value="Keyword" /><br />
  <a href="#" class="search-button" id="submit-btn">Search</a><a href="#" class="searchclear-button" id="clear-btn">Clear</a>
</fieldset>
</form>
<div id="search-results"></div>
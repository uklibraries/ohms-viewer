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

<span id="alert"></span>

<?php if(!empty($lang)): ?>
	<div id="language-toggle">
		<select id="language-type">
			<option id="language-english" selected="selected">English</option>
			<option id="language-alt"><?php echo $lang; ?></option>
		</select>
	</div>
<?php endif; ?>
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
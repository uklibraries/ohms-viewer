<?php
echo <<<BRIGHTCOVE
<script type="text/javascript" src="https://sadmin.brightcove.com/js/BrightcoveExperiences_all.js">
</script>
<object id="myExperience" class="BrightcoveExperience">
   <param name="bgcolor" value="#FFFFFF" />
   <param name="width" value="480" />
   <param name="height" value="270" />
   <param name="playerID" value="{$interview->player_id}"/>
   <param name="publisherID" value="{$interview->account_id}"/>
   <param name="isVid" value="true" />
   <param name="isUI" value="true" />
   <param name="@videoPlayer" value="{$interview->clip_id}"/>
   <param name="secureConnections" value="true" />
   <param name="secureHTMLConnections" value="true" />
</object>
<div class="video-spacer"></div>
BRIGHTCOVE;

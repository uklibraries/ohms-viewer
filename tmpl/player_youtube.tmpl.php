<?php
		$player_id = $cacheFile->player_id;
		$publisher_id = $cacheFile->account_id;
		$youtubeId = str_replace('http://youtu.be/', '', $cacheFile->media_url);

		echo <<<YOUTUBE
			<div id="youtubePlayer"></div>
			
		  <div class="video-spacer"></div>

		  <style>
		    #transcript-panel { height:550px; }
		    #index-panel { height:550px; }
		    #searchbox-panel { height:544px; }
		    #search-results { height:177px; }
		    #audio-panel { height: 270px; }
		    #header {height: 415px; }
			#headervid {height: auto; padding-bottom: 1px; }
		    #main {height: 550px; }
			#youtubePlayer {margin-left: 50px;}
			.video-spacer {height: 0px; }
		  </style>
			<script type="text/javascript">
				var tag = document.createElement('script');

				tag.src = "https://www.youtube.com/iframe_api";
				var firstScriptTag = document.getElementsByTagName('script')[0];
				firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
				
				var player;
				var setTime = 0;

				function onYouTubeIframeAPIReady() {
					player = new YT.Player('youtubePlayer', {
						height: '270',
						width: '480',
						videoId: '{$youtubeId}',
						startAt: setTime,
						events: {
							onReady: onPlayerReady
						}
					});
					
					function onPlayerReady(event)
					{
						event.target.playVideo();
					}
				}
			</script>
YOUTUBE;

?>
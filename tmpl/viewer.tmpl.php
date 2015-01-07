<?php
	date_default_timezone_set($config['timezone']);
	$audioFormats = array('.mp3', '.wav', '.ogg', '.flac', '.m4a');
	$filepath =$cacheFile->media_url;
	$rights = (string)$cacheFile->rights;
	$usage = (string)$cacheFile->usage;
	$contactemail = $config[$cacheFile->repository]['contactemail'];
	$contactlink = $config[$cacheFile->repository]['contactlink'];
	$copyrightholder = $config[$cacheFile->repository]['copyrightholder'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title><?php echo $cacheFile->title; ?></title>
    <link rel="stylesheet" href="css/viewer.css" type="text/css" />
    <link rel="stylesheet" href="css/<?php echo $config[$cacheFile->repository]['css'];?>" type="text/css" />
    <link rel="stylesheet" href="css/jquery-ui.toggleSwitch.css" type="text/css" />
    <link rel="stylesheet" href="css/jquery-ui-1.8.16.custom.css" type="text/css" />
    <link rel="stylesheet" href="css/font-awesome.css">
     <meta property="og:title" content="<?php echo $cacheFile->title; ?>" />
     <meta property="og:url" content="<?php echo "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]; ?>" />
<?php if (isset($config[$cacheFile->repository]['open_graph_image']) && $config[$cacheFile->repository]['open_graph_image'] <> '') { ?>
     <meta property="og:image" content="<?php echo "http://".$_SERVER["HTTP_HOST"].dirname($_SERVER["REQUEST_URI"])."/".$config[$cacheFile->repository]['open_graph_image'];?>" />
<?php } ?>
<?php if (isset($config[$cacheFile->repository]['open_graph_description']) && $config[$cacheFile->repository]['open_graph_description'] <> '') { ?>
     <meta property="og:description" content="<?php echo $config[$cacheFile->repository]['open_graph_description'];?>" />
<?php } ?>
  </head>
  <body>
	<script type="text/javascript">
		var jumpToTime = null;
		if(location.href.search('#segment') > -1)
		{
			var jumpToTime = parseInt(location.href.replace(/(.*)#segment/i, ""));
			if(isNaN(jumpToTime))
			{
				jumpToTime = 0;
			}
		}
	</script>
<?php if(in_array(substr(strtolower($filepath), -4, 4), $audioFormats)): ?>
    <div id="header">
<?php else: ?>
    <div id="headervid">
<?php endif; ?>
		<?php if(isset($config[$cacheFile->repository])): ?>
		<img width="77" src="<?php echo $config[$cacheFile->repository]['footerimg'];?>" alt="<?php echo $config[$cacheFile->repository]['footerimgalt'];?>" style="float: left;" />
		<?php endif; ?>
      <div class="center" style="width:860px;">
	<h1><?php echo $cacheFile->title; ?></h1>
	<h2 id="secondaryMetaData">
		<div style="margin-left: 80px;">
			<strong><?php echo $cacheFile->repository; ?></strong><br />
			<?php echo $cacheFile->interviewer; ?>, Interviewer | <?php echo $cacheFile->accession; ?><br />
			<?php echo $cacheFile->collection; ?> | <?php echo $cacheFile->series; ?>
		</div>
	</h2>
	<div id="audio-panel">
	  <?php include_once 'tmpl/player_'.$cacheFile->playername.'.tmpl.php'; ?>
	</div>
      </div>
    </div>
    <div id="main">
      <div id="main-panels">
	<div id="content-panel">
	  <div id="transcript-panel">
	    <?php echo $cacheFile->transcript; ?>
	  </div>
	  <div id="index-panel">
	    <?php echo $cacheFile->index; ?>
	  </div>
	</div>
	<div id="searchbox-panel"><?php include_once 'tmpl/search.tmpl.php'; ?></div>
      </div>
    </div>
    <div id="footer">
		<div style="float: left; text-align: left; width: 50%; margin-top: -12px;">
			<?php if(!empty($rights)): ?>
				<p><span><h3><a href="#" id="lnkRights">View Rights Statement</a></h3><div id="rightsStatement"><?php echo $rights; ?></div></span></p>
			<?php else: ?>
				<p><span><h3>View Rights Statement</h3></span></p>
			<?php endif; ?>
			<?php if(!empty($usage)): ?>
				<p><span><h3><a href="#" id="lnkUsage">View Usage Statement</a></h3><div id="usageStatement"><?php echo $usage; ?></div></span></p>
			<?php else: ?>
				<p><span><h3>View Usage Statement</h3></span></p>
			<?php endif; ?>
			<?php if(isset($cacheFile->collection_link) && (string)$cacheFile->collection_link != ''): ?>
				<p><span><h3>Collection Link: <a href="<?=$cacheFile->collection_link?>"><?=$cacheFile->collection?></a></h3></span></p>
			<?php endif; ?>
			<?php if(isset($cacheFile->series_link) && (string)$cacheFile->series_link != ''): ?>
				<p><span><h3>Series Link: <a href="<?=$cacheFile->series_link?>"><?=$cacheFile->series?></a></h3></span></p>
			<?php endif; ?>
			<p><span><h3>Contact Us: <a href="mailto:<?php echo $contactemail;?>"><?php echo $contactemail; ?></a> | <a href="<?php echo $contactlink; ?>"><?php echo $contactlink; ?></a></h3></span></p>
		</div>
		<div style="float: right; text-align: right; width: 50%;">
			<small id="copyright"><span>&copy; <?php echo Date("Y"); ?></span><?php echo $copyrightholder; ?></small>
		</div>
		<div style="float: right; color:white; margin-top: 10px; text-align:right;">
			<img alt="Powered by OHMS logo" src="imgs/ohms_logo.png" border="0"/>
	  </div>
		<br clear="both" />
      </div>
      <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
      <script type="text/javascript" src="js/jquery-ui.toggleSwitch.js"></script>
      <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
      <script type="text/javascript" src="js/jquery.jplayer.min.js"></script>
      <script type="text/javascript" src="js/jquery.easing.1.3.js"></script>
      <script type="text/javascript" src="js/jquery.scrollTo-min.js"></script>
      <script type="text/javascript" src="js/viewer_<?php echo  $cacheFile->viewerjs;?>.js"></script>
	<link rel="stylesheet" href="js/fancybox_2_1_5/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
	<link rel="stylesheet" href="skin/jplayer.blue.monday.css" type="text/css" media="screen" />
	<script type="text/javascript" src="js/fancybox_2_1_5/source/jquery.fancybox.pack.js?v=2.1.5"></script>

	<link rel="stylesheet" href="js/fancybox_2_1_5/source/helpers/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen" />
	<script type="text/javascript" src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
	<script type="text/javascript" src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>

	<link rel="stylesheet" href="js/fancybox_2_1_5/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" type="text/css" media="screen" />
	<script type="text/javascript" src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
	<script type="text/javascript">
	     $(document).ready(function() {
		   jQuery('a.indexSegmentLink').on('click', function(e) {
				var linkContainer = '#segmentLink' + jQuery(e.target).data('timestamp');

				e.preventDefault();
				if(jQuery(linkContainer).css("display") == "none")
				{
					jQuery(linkContainer).fadeIn(1000);
				}
				else
				{
					jQuery(linkContainer).fadeOut();
				}
				
				return false;
		   });
		   
		   jQuery('.segmentLinkTextBox').on('click', function() {
				jQuery(this).select();
			});
			
			if(jumpToTime !== null)
			{
				jQuery('div.point').each(function(index) {
					if(parseInt(jQuery(this).find('a.indexJumpLink').data('timestamp')) == jumpToTime)
					{
						jumpLink = jQuery(this).find('a.indexJumpLink');
						jQuery('#accordionHolder').accordion({active: index});
						var interval = setInterval(function() {
							<?php
								switch($cacheFile->playername):
									case 'youtube':
							?>
								if(player !== undefined && player.getCurrentTime !== undefined && player.getCurrentTime() == jumpToTime)
							<?php
										break;
									case 'brightcove':
							?>
								if(modVP !== undefined && modVP.getVideoPosition !== undefined && Math.floor(modVP.getVideoPosition(false)) == jumpToTime)
							<?php
										break;
									case 'kaltura':
							?>
								// Kaltura not implemented yet. Replace this with the right "if" statement at the appropriate time.
								if(true)
							<?php
										break;
									default:
							?>
								if(Math.floor(jQuery('#subjectPlayer').data('jPlayer').status.currentTime) == jumpToTime)
							<?php
										break;
								endswitch;
							?>
								{
									clearInterval(interval);
								}
								else
								{
									jumpLink.click();
								}
						}, 500);
						jQuery(this).find('a.indexJumpLink').click();
					}
				});
			}
						$(".fancybox").fancybox();
		  $(".various").fancybox({
		       maxWidth  : 800,
		       maxHeight : 600,
		       fitToView : false,
		       width          : '70%',
		       height         : '70%',
		       autoSize  : false,
		       closeClick     : false,
		       openEffect     : 'none',
		       closeEffect    : 'none'
		  });
		  $('.fancybox-media').fancybox({
		       openEffect  : 'none',
		       closeEffect : 'none',
		       width          : '80%',
		       height         : '80%',
		       fitToView : true,
		       helpers : {
		            media : {}
		       }
		  });
		  $(".fancybox-button").fancybox({
		       prevEffect          : 'none',
		       nextEffect          : 'none',
		       closeBtn       : false,
		       helpers        : {
		            title     : { type : 'inside' },
		            buttons   : {}
		       }
		  });
		  
		  jQuery('#lnkRights').click(function() {
			jQuery('#rightsStatement').fadeToggle(400);
			
			return false;
		  });

		  jQuery('#lnkUsage').click(function() {
			jQuery('#usageStatement').fadeToggle(400);
			
			return false;
		  });
	     });
	</script>
      <script type="text/javascript">
	var cachefile = '<?php echo $cacheFile->cachefile; ?>';
      </script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  <?php if(isset($config[$cacheFile->repository]['ga_tracking_id'])): ?>
  ga('create', '<?php echo $config[$cacheFile->repository]['ga_tracking_id']; ?>', '<?php echo $config[$cacheFile->repository]['ga_host']; ?>');
  ga('send', 'pageview');
  <?php endif; ?>

</script>
    </body>
  </html>

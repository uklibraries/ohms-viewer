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
    <div id="header">
      <div class="center" style="height:180px; width:960px;">
	<h1><?php echo $cacheFile->title; ?></h1>
	<h2 id="secondaryMetaData">
		<div style="margin-left: 80px;">
			<strong><?php echo $cacheFile->repository; ?></strong><br />
			<?php echo $cacheFile->collection; ?>, <?php echo $cacheFile->series; ?><br/>
			<?php echo $cacheFile->interviewer; ?>, Interviewer | <?php echo $cacheFile->accession; ?>
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
	<div id="searchbox-panel" class="<?=($cacheFile->clipsource == 'YouTube') ? 'youtube' : ''?>"><?php include_once 'tmpl/search.tmpl.php'; ?></div>
      </div>
    </div>
    <div id="footer">
		<div style="float: left; text-align: left; width: 50%; margin-top: -12px;">
			<p><span><h3><a href="#" id="lnkRights">View Rights Statement</a></h3><div id="rightsStatement"><?php echo $cacheFile->rights; ?></div></span></p>
			<p><span><h3><a href="#" id="lnkUsage">View Usage Statement</a></h3><div id="usageStatement"><?php echo $cacheFile->usage; ?></div></span></p>
			<p><span><h3>Contact Us: <a href="mailto:nunncenter@lsv.uky.edu">nunncenter@lsv.uky.edu</a> | <a href="http://www.nunncenter.org">http://www.nunncenter.org</a></h3></span></p>
		</div>
		<div style="float: right; text-align: right; width: 50%;">
			<small id="copyright"><span>&copy; 2012 Louie B. Nunn Center for Oral History</span><span>University of Kentucky Libraries</small>
		</div>
		<div style="float: right; color:white; margin-top: 10px; text-align:right;">
			<img alt="Powered by OHMS logo" src="imgs/ohms_logo.png" border="0"/>
	  </div>
		<br clear="both" />
      </div>
      <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
      <script type="text/javascript" src="js/jquery-ui.toggleSwitch.js"></script>
      <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
      <script type="text/javascript" src="js/flowplayer.min.js"></script>
      <script type="text/javascript" src="js/flowplayer.ipad.min.js"></script>
      <script type="text/javascript" src="js/jquery.easing.1.3.js"></script>
      <script type="text/javascript" src="js/jquery.scrollTo-min.js"></script>
      <script type="text/javascript" src="js/viewer_<?php echo  $cacheFile->viewerjs;?>.js"></script>
	<link rel="stylesheet" href="js/fancybox_2_1_5/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
	<script type="text/javascript" src="js/fancybox_2_1_5/source/jquery.fancybox.pack.js?v=2.1.5"></script>

	<link rel="stylesheet" href="js/fancybox_2_1_5/source/helpers/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen" />
	<script type="text/javascript" src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
	<script type="text/javascript" src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>

	<link rel="stylesheet" href="js/fancybox_2_1_5/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" type="text/css" media="screen" />
	<script type="text/javascript" src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
	<script type="text/javascript">
	     $(document).ready(function() {
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
    </body>
  </html>

<?php include 'parts/init.tmpl.php'; ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
		<title><?php echo $interview->title; ?></title>
		<link rel="stylesheet" href="css/viewer.css" type="text/css" />
		<?php if (isset($extraCss)) { ?>
			<link rel="stylesheet" href="css/<?php echo $extraCss ?>" type="text/css" />
		<?php } ?>
		<link rel="stylesheet" href="css/jquery-ui.toggleSwitch.css" type="text/css" />
		<link rel="stylesheet" href="css/jquery-ui-1.8.16.custom.css" type="text/css" />
		<link rel="stylesheet" href="css/font-awesome.css">
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui.toggleSwitch.js"></script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
		<script type="text/javascript" src="js/jquery.jplayer.min.js"></script>
		<script type="text/javascript" src="js/jquery.easing.1.3.js"></script>
		<script type="text/javascript" src="js/jquery.scrollTo-min.js"></script>
		<link rel="stylesheet" href="js/fancybox_2_1_5/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
		<link rel="stylesheet" href="skin/jplayer.blue.monday.css" type="text/css" media="screen" />
		<script type="text/javascript" src="js/fancybox_2_1_5/source/jquery.fancybox.pack.js?v=2.1.5"></script>
		<link rel="stylesheet" href="js/fancybox_2_1_5/source/helpers/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen" />
		<script type="text/javascript" src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
		<script type="text/javascript" src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>
		<link rel="stylesheet" href="js/fancybox_2_1_5/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" type="text/css" media="screen" />
		<script type="text/javascript" src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
		<script type="text/javascript" src="js/viewer.js"></script>
		<script type="text/javascript" src="js/viewer_<?php echo $interview->viewerjs; ?>.js"></script>
		<script type="text/javascript">				
			var cachefile = '<?php echo $interview->cachefile; ?>';
			var playerName = '<?php echo $interview->playername; ?>';
		</script>
		<script type="text/javascript" src="js/viewer_init.js"></script>
		<?php include 'parts/og.tmpl.php'; ?>
	</head>
	<body>
		
		<?php include 'parts/main.tmpl.php'; ?>
		<?php include 'parts/footer.tmpl.php'; ?>
		<?php include 'parts/ga.tmpl.php'; ?>
	</body>
</html>

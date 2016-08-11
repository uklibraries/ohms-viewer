<?php
date_default_timezone_set($config['timezone']);
$audioFormats = array('.mp3', '.wav', '.ogg', '.flac', '.m4a');
$filepath = $interview->media_url;
$rights = (string) $interview->rights;
$usage = (string) $interview->usage;
$contactemail = '';
$contactlink = '';
$copyrightholder = '';
$protocol = 'https';
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
	$protocol = 'http';
}
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$baseurl = "$protocol://$host$uri";
$extraCss = null;
if (isset($config[$interview->repository])) {
	$repoConfig = $config[$interview->repository];
	$contactemail = $repoConfig['contactemail'];
	$contactlink = $repoConfig['contactlink'];
	$copyrightholder = $repoConfig['copyrightholder'];
	if (isset($repoConfig['open_graph_image']) && $repoConfig['open_graph_image'] <> '') {
		$openGraphImage = $repoConfig['open_graph_image'];
	}
	if (isset($repoConfig['open_graph_description']) && $repoConfig['open_graph_description'] <> '') {
		$openGraphDescription = $repoConfig['open_graph_description'];
	}

	if (isset($repoConfig['css']) && strlen($repoConfig['css']) > 0) {
		$extraCss = $repoConfig['css'];
	}
}
$seriesLink = (string) $interview->series_link;
$collectionLink = (string) $interview->collection_link;
$lang = (string) $interview->translate;
?>
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
		
		<?php $class = in_array(substr(strtolower($filepath), -4, 4), $audioFormats) ? "header" : "headervid"; ?>
		<div id="<?php echo $class; ?>">				
			<?php if (isset($config[$interview->repository])): ?>
				<img id="headerimg" src="<?php echo $config[$interview->repository]['footerimg']; ?>" alt="<?php echo $config[$interview->repository]['footerimgalt']; ?>" />
			<?php endif; ?>
			<div class="center">
				<h1><?php echo $interview->title; ?></h1>
				<h2 id="secondaryMetaData">
					<div>
						<strong><?php echo $interview->repository; ?></strong><br />
						<?php echo $interview->interviewer; ?>, Interviewer | <?php echo $interview->accession; ?><br />
						<?php if (isset($interview->collection_link) && (string) $interview->collection_link != '') { ?>
							<a href="<?php echo $interview->collection_link ?>"><?php echo $interview->collection ?></a> |
						<?php } else { ?>
							<?php echo $interview->collection; ?> |
						<?php } ?>

						<?php if (isset($interview->series_link) && (string) $interview->series_link != '') { ?>
							<a href="<?php echo $interview->series_link ?>"><?php echo $interview->series ?></a>
						<?php } else { ?>
							<?php echo $interview->series; ?>
						<?php } ?>
					</div>
				</h2>
				<div id="audio-panel">
					<?php include_once 'tmpl/player_' . $interview->playername . '.tmpl.php'; ?>
				</div>
			</div>			
		</div>
		
		<div id="main">
			<div id="main-panels">
				<div id="content-panel">
					<div id="holder-panel"></div>
					<div id="transcript-panel" class="transcript-panel">
						<?php echo $interview->transcript; ?>
					</div>
					<div id="index-panel" class="index-panel">
						<?php echo $interview->index; ?>
					</div>
				</div>
				<div id="searchbox-panel"><?php include_once 'tmpl/search.tmpl.php'; ?></div>
			</div>
		</div>
		<?php include 'parts/footer.tmpl.php'; ?>
		<?php include 'parts/ga.tmpl.php'; ?>
	</body>
</html>

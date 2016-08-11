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

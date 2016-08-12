<?php

if (isset($config[$interview->repository])) {
    $repoConfig = $config[$interview->repository];
    if (isset($repoConfig['open_graph_image']) && $repoConfig['open_graph_image'] !== '') {
        $openGraphImage = $repoConfig['open_graph_image'];
    }
    if (isset($repoConfig['open_graph_description']) && $repoConfig['open_graph_description'] !== '') {
        $openGraphDescription = $repoConfig['open_graph_description'];
    }
}
?>

<meta property="og:title" content="<?php echo $interview->title; ?>" />
<meta property="og:url" content="<?php echo $baseurl ?>">

<?php if (isset($openGraphImage)): ?>
    <meta property="og:image" content="<?php echo "$baseurl/$openGraphImage" ?>">
<?php endif; ?>
<?php if (isset($openGraphDescription)): ?>
    <meta property="og:description" content="<?php echo "$openGraphDescription" ?>">
<?php endif; ?>

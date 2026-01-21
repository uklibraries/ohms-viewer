<?php //If we want to use vvt text as tracks then we can use this code in player_other.tmpl.php
    $translateEnabled = (filter_input(INPUT_GET, 'translate', FILTER_VALIDATE_INT) === 1);

    $tracks = [
        [
            'key' => 'primary',
            'has_vtt' => !empty((string) ($interview->vtt['primary'] ?? '')),
            'langName' => (string) ($interview->language ?? ''),
            'src' => $baseurl . '&action=vtt&lang=primary',
            'default' => !$translateEnabled, // default when translate is NOT enabled
        ],
        [
            'key' => 'alternate',
            'has_vtt' => !empty((string) ($interview->vtt['alternate'] ?? '')),
            'langName' => (string) ($interview->transcript_alt_lang ?? ''),
            'src' => $baseurl . '&action=vtt&lang=alternate',
            'default' => $translateEnabled, // default when translate IS enabled
        ],
    ]; 
    
?>
<?php if (strtolower($interview->clipsource) == 'aviary'): ?>
                <?php foreach ($tracks as $t): ?>
                    <?php if ($t['has_vtt'] && $t['langName'] !== ''): ?>
                        <track
                            kind="captions"
                            src="<?= htmlspecialchars($t['src'], ENT_QUOTES, 'UTF-8') ?>"
                            srclang="<?= htmlspecialchars(Ohms\Utils::languageAbbr($t['langName']), ENT_QUOTES, 'UTF-8') ?>"
                            label="<?= htmlspecialchars($t['langName'], ENT_QUOTES, 'UTF-8') ?>"
                            <?= $t['default'] ? 'default' : '' ?>
                            >
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>


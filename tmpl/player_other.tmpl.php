<?php
$fileserver = (isset($config['fileserver']) ? $config['fileserver'] : '');

$filepath = $interview->media_url;
if (strpos($filepath, 'http://') !== false || strpos($filepath, 'https://') !== false):
    $linkToMedia = $filepath;
else:
    $linkToMedia = 'http://' . $fileserver . $interview->file_name;
endif;

$validClipFormats = array('audio', 'audiotrans', 'video');
$clipFormat = $interview->clip_format;
if (strtolower($interview->clipsource) == "aviary"):
    $mediaFormat = $clipFormat == 'video' ? 'mp4' : 'mp3';
else:
    $mediaFormat = substr($linkToMedia, -3, 3);
endif;

if ($mediaFormat == 'mpga'):
    $mediaFormat = "mp3";
endif;

if ($clipFormat == 'audio'):
    ?>
    <div class="audio-player">
        <audio id="my_player" controls
               preload="auto"  class="audio video-js">
            <source src="<?php echo $linkToMedia ?>" type="audio/<?php echo $mediaFormat ?>" />

        </audio>
    </div>
    <?php
else:
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
    <div class="video-player">
        <video id="my_player" controls
               preload="auto"  class="video-js">
            <source src="<?php echo $linkToMedia ?>" type="video/<?php echo $mediaFormat ?>" />
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
        </video>
    </div>
<?php endif;

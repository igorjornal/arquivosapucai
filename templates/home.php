<?php
$GLOBALS['nl2p'] = fn (string $text) => implode('', array_map(fn ($p) => '<p>' . e(trim($p)) . '</p>', array_filter(preg_split("/\n\s*\n/", $text))));
?>
<?php foreach ($sections as $section) {
    $file = ROOT . '/templates/sections/' . $section['type'] . '.php';
    if (is_file($file)) {
        echo render_to_string('sections/' . $section['type'], ['section' => $section, 'home' => $home, 'nl2p' => $GLOBALS['nl2p']]);
    }
} ?>

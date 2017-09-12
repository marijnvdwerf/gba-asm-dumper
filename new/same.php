<?php


$gfx = new RecursiveDirectoryIterator('/Users/Marijn/Projects/pokeruby/graphics/');
$it = new RecursiveIteratorIterator($gfx);

$map = [];

foreach ($it as $file) {
    if (!($file instanceof SplFileInfo)) {
        continue;
    }

    $map[md5_file($file)] = (string)$file;
}


$files = glob('/Users/Marijn/Projects/pret/disasm/projects/firered/cache/*.bin');


$map2 = [];
echo '<li>';

foreach ($files as $file) {
    $hash = md5_file($file);

    if (isset($map[$hash])) {
        printf('<li><code>%s</code>', $map[$hash]);
        $map2[intval(basename($file, '.bin'), 16)] = substr($map[$hash], strlen('/Users/Marijn/Projects/pokeruby/graphics/'));
    }
}
echo '</li>';

file_put_contents('map.json', json_encode($map2, JSON_PRETTY_PRINT));

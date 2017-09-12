<?php

use PhpBinaryReader\BinaryReader;

define('FR_ADDR_MAP_GROUPS', 0x83526A8);
require '_common.php';
require '_rommap.php';
require 'stringreader.php';
require 'pokemon/species.php';

$_GET['project'] = 'leafgreen';


/** @var BinaryReader $rom */
$rom = $container['rom'];

$map = new RomMap2();
require_once '_dumpers.php';

$map->register(0x844e270, 'Data2', 'PokedexEntries');
$map->register(0x083c9af4, 'kWildMonHeaders', 'WildMonHeaders');
$map->register(0x083bf958, '', 'SpriteTemplate');
$map->register(0x083bf938, '', 'SpriteTemplate');
$map->register(0x083bf978, '', 'SpriteTemplate');
$map->register(0x083bf998, '', 'SpriteTemplate');
$map->register(0x083e0004, '', 'Menu', 6);
$map->register(0x0826cf6c, 'kInGameTrades', 'InGameTrade');



a
 $app$map->register(0x0816ccb0, '', 'EventScript');

$map->register(0x0841cfd4, '', 'Text');
$map->register(0x841CFF2, '', 'Text');
$map->register(0x841D014, '', 'Text');

$map->register(0x0825d794, 'kLevelUpLearnsets', 'List', 412, 'LevelUpLearnset', function ($i) use ($species) {
    if ($i == 0) {
        $i = 1;
    }

    $s = $species[$i];
    $s = explode('_', $s);
    $s = array_map(function ($s) {
        return ucfirst(strtolower($s));
    }, $s);
    $s = implode($s);

    return 'kLevelUpLearnset_' . $s;
});

$script = fopen(ROOT . '/out/' . $container['project'] . '-pokedex.s', 'w+');
$map->dump($rom, $script, 0x8000000, 0x8D00000);
fclose($script);

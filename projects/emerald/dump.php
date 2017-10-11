<?php

use PhpBinaryReader\BinaryReader;
use MarijnvdWerf\DisAsm\Dumper\RomMap as RomMap2;

$map->registerDumper('AnimList', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) use ($species) {
//    , function ($i, $offset) use ($species) {
//        return 'gAnimCmds_' . $species[$i];
//    });

    for ($i = 0; $i < 440; $i++) {
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), 'gAnims_' . $species[$i], 'AnimCmds2', $species[$i]));
    }
});


$map->registerUnboundedDumper('AnimCmds2', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $i = 0;
    while (true) {
        $pos = $rom->getPosition();

        $addr = $rom->readUInt32();
        if ($addr < 0x8000000 || $addr > 0x8D00000) {
            $rom->setPosition($pos);
            return;
        }

        fprintf($out, "    .4byte %s\n", $map->register($addr, 'gAnimCmd_' . $arguments[0] . '_' . $i, 'AnimCmd'));
        $i++;
        if ($map->hasLabel($rom->getPosition())) {
            return;
        }
    }
});

$map->register(0x8309aac, 'gMonAnimationsSpriteAnimsPtrTable', 'AnimList');
$map->register(0x830A18C, '', '');
$map->register(0x8305DCC, 'gUnknown_08305DCC', '');


$basePath = ROOT . '/out/' . $container['project'];
if (!file_exists($basePath)) {
    mkdir($basePath, 0777, true);
}

$script = fopen($basePath . 'data.s', 'w+');
$map->dump($rom, $script, 0x8000000, 0x9000000);
fclose($script);
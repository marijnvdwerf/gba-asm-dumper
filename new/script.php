<?php

use PhpBinaryReader\BinaryReader;
use MarijnvdWerf\DisAsm\Dumper\RomMap as RomMap2;

require '_common.php';
require 'stringreader.php';
require 'pokemon/species.php';

if ($argc !== 2) {
    die('No project specified');
}

$_GET['project'] = $argv[1];

/** @var BinaryReader $rom */
$rom = $container['rom'];

$map = new RomMap2();
require_once '_dumpers.php';

require_once $container['basepath'] . '/dump.php';

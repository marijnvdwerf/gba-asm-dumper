<?php


use MarijnvdWerf\DisAsm\FunctionTableEntry;
use MarijnvdWerf\DisAsm\InstructionSet;
use MarijnvdWerf\DisAsm\RomMap;
use PhpBinaryReader\BinaryReader;
use Pimple\Container;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

set_time_limit(0);
ini_set('memory_limit', '1024M');

$container = new Container();

$container['project'] = function () {
    if (!isset($_GET['project'])) {
        die('No project supplied');
    }

    $project = trim($_GET['project']);
    if (!file_exists(ROOT . '/projects/' . $project)) {
        die('Project doesn\'t exist');
    }

    return $project;
};

$container['basepath'] = function (Container $container) {
    return realpath(ROOT . '/projects/' . $container['project']);
};

$container['rom'] = function (Container $container) {
    $romPath = $container['basepath'] . '/rom.gba';
    $rom = fopen($romPath, 'rb');
    $tempPath = 'temp.rom';
    $fh = fopen($tempPath, 'wb+');
    fseek($fh, 0x8000000);
    fwrite($fh, fread($rom, filesize($romPath)));
    fclose($rom);
    fseek($fh, 0);

    return new BinaryReader($fh);
};

$container['functionMap'] = function (Container $container) {
    function parseline($line)
    {
        $parts = preg_split('/\s+/', $line);

        $fn = new FunctionTableEntry();
        $fn->name = $parts[0];
        $fn->segment = $parts[1];
        $fn->address = intval($parts[2], 16);
        $fn->size = intval($parts[3], 16);

        return $fn;
    }

    $db1 = file($container['basepath'] . '/functions.txt', FILE_IGNORE_NEW_LINES);
    /** @var FunctionTableEntry[] $db */
    $db = [];

    $overrides = [];
    if (file_exists($container['basepath'] . '/function-names.txt')) {
        $overrides = file($container['basepath'] . '/function-names.txt', FILE_IGNORE_NEW_LINES);
        $overrides = array_map(function ($a) {
            $a = trim($a);
            $parts = preg_split('/\s+/', $a);
            return $parts[0];
        }, $overrides);
    }

    foreach ($db1 as $i => $line) {
        $fn = parseline($line);
        $db[$fn->address] = $fn;

        if (!empty($overrides[$i])) {

            if ($overrides[$i] != $db[$fn->address]->name && !preg_match('/^(null)?sub_/', $overrides[$i])) {
                printf("MakeNameEx(0x%X, \"%s\", SN_PUBLIC);\n", $fn->address, $overrides[$i]);
                printf("<br/>");
            }
            $db[$fn->address]->name = $overrides[$i];
        }
    }
    ksort($db);

    if ($container['project'] == 'firered') {
        foreach ($db as $i => $entry) {
            if ($entry->address >= 0x81DBD34 && $entry->address < 0x81E05B0) {
                $entry->instructionSet = InstructionSet::ARM;
            }

            if ($entry->address < 0x80003A4) {
                $entry->instructionSet = InstructionSet::ARM;
            }
        }
    }

    if ($container['project'] === 'firered-1.1') {
        foreach ($db as $i => $entry) {
            if ($entry->address >= 0x81DBDA4 && $entry->address < 0x81E0620) {
                $entry->instructionSet = InstructionSet::ARM;
            }

            if ($entry->address < 0x80003A4) {
                $entry->instructionSet = InstructionSet::ARM;
            }
        }
    }

    if ($container['project'] === 'leafgreen') {
        foreach ($db as $i => $entry) {
            if ($entry->address >= 0x81DBD10 && $entry->address < 0x8300000) {
                $entry->instructionSet = InstructionSet::ARM;
            }

            if ($entry->address < 0x80003A4) {
                $entry->instructionSet = InstructionSet::ARM;
            }
        }
    }

    return $db;
};

$container['map'] = function (Container $container) {
    $map = new RomMap();

    foreach ($container['functionMap'] as $fn) {
        $map->addFunction($fn);
    }


    $names = file($container['basepath'] . '/names.txt', FILE_IGNORE_NEW_LINES);
    foreach ($names as $line) {
        if (empty(trim($line))) {
            continue;
        }
        list($name, $offset) = preg_split('|\s+|', $line);
        $map->addData(intval($offset, 16), $name);
    }

    return $map;
};

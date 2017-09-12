<?php

use MarijnvdWerf\DisAsm\Data;
use MarijnvdWerf\DisAsm\Disassembler;
use MarijnvdWerf\DisAsm\HtmlFormatter;
use MarijnvdWerf\DisAsm\LocalLongBranch;
use MarijnvdWerf\DisAsm\OffsetData;
use MarijnvdWerf\DisAsm\Output\AsmFormatter;
use MarijnvdWerf\DisAsm\Thumb\ConditionalBranch;
use MarijnvdWerf\DisAsm\Thumb\Instruction;
use MarijnvdWerf\DisAsm\Thumb\UnconditionalBranch;
use MarijnvdWerf\DisAsm\ThumbInstructionFormatter;
use PhpBinaryReader\BinaryReader;

require 'vendor/autoload.php';

set_time_limit(0);
ini_set('memory_limit', '1024M');


$files = [];

$sections = [];
$sectionsLn = file('pokerubydebug-sections.txt', FILE_IGNORE_NEW_LINES);
foreach ($sectionsLn as $line) {
    $line = preg_split('~\s+~', $line);

    $line[4] = preg_replace('~\((.*)\)$~', '', $line[4]);

    $sections[intval($line[2], 16)] = basename($line[4], '.o');
}
ksort($sections);
unset($sectionsLn);

$newSections = [];
foreach ($sections as $i => $name) {
    if (isset($newSections[$name])) {
        $newSections[$name] = min($newSections[$name], $i);
    } else {
        $newSections[$name] = $i;
    }
}
$sections = array_flip($newSections);

$romPath = '/Users/Marijn/Projects/pokeruby-other/pokeruby-de-debug.gba';
$rom = fopen($romPath, 'rb');
$tempPath = 'temp.rom';
$fh = fopen($tempPath, 'wb+');
fseek($fh, 0x8000000);
fwrite($fh, fread($rom, filesize($romPath)));
fclose($rom);
fseek($fh, 0);

$disassembler = new Disassembler();
$br = new BinaryReader($fh);

//header('Content-Type: text/plain; charset=utf-8');

class FunctionTableEntry
{
    public $name;
    public $segment;
    public $address;
    public $size;
    public $instructions = [];
}

function parseline($line)
{
    $parts = preg_split('/\s+/', $line);

    $fn = new \MarijnvdWerf\DisAsm\FunctionTableEntry();
    $fn->name = $parts[0];
    $fn->segment = $parts[1];
    $fn->address = intval($parts[2], 16);
    $fn->size = intval($parts[3], 16);

    return $fn;
}

$dbL = file('pokerubydebug.txt', FILE_IGNORE_NEW_LINES);


/** @var \MarijnvdWerf\DisAsm\FunctionTableEntry[] $db */
$db = [];
foreach ($dbL as $line) {
    $fn = parseline($line);
    $db[$fn->address] = $fn;
}
unset($dbL);

$data = [];


$nm = file('/Users/Marijn/Projects/pret/ruby/german/nm.txt', FILE_IGNORE_NEW_LINES);
foreach ($nm as $line) {
    if (preg_match('|^(.*?) [Tt] ([0-9A-Fa-f]{7})|', $line, $m)) {
        if (preg_match('|^_[0-9A-F]+$|', $m[1])) {
            continue;
        }

        $index = intval($m[2], 16);
        if ($index < 0x8000204) {
            continue;
        }

        if (!isset($db[$index])) {
            //  printf("0x%X %s\n", $index, $m[1]);
            continue;
        }

        $db[$index]->name = $m[1];
    } else if (preg_match('|^(.*?) [RrDdBb] ([0-9A-Fa-f]{7})|', $line, $m)) {

        $index = intval($m[2], 16);
        $data[$index] = $m[1];
    }
}

function findLabels(&$lines, &$i)
{
    $labels = [];

    $prevInstruction = null;
    foreach ($lines as $addr => $instruction) {
        if ($instruction instanceof LocalLongBranch ||
            $instruction instanceof ConditionalBranch ||
            $instruction instanceof UnconditionalBranch ||
            false
        ) {
            $labels[$instruction->address] = $i++;
        }

        if ($instruction instanceof OffsetData) {
            $labels[$instruction->value] = $i++;
        }

        if ($prevInstruction instanceof Instruction && $instruction instanceof Data) {
            $labels[$addr] = $i++;

            $labels[$addr - 1] = $i++;
            $lines[$addr - 1] = ".align\t2, 0";
            ksort($lines);
        }

        $prevInstruction = $instruction;
    }

    ksort($labels);
    return $labels;
}

$formatter = new ThumbInstructionFormatter();
$htmlFormatter = new HtmlFormatter();


class RomMap
{
    public $procedures = [];

    public function addFunction(\MarijnvdWerf\DisAsm\FunctionTableEntry $fn)
    {
        $this->procedures[$fn->address] = $fn;
    }

    public function getLabel($address)
    {
        global $map2;

        if (isset($this->procedures[$address])) {
            return $this->procedures[$address]->name;
        }


        if (isset($this->procedures[$address - 1])) {
            return $this->procedures[$address - 1]->name . '+1';
        }

        if (isset($map2[$address])) {
            return $map2[$address];
        }

        if (($address >= 0x2000000 && $address < 0x4000000) || ($address >= 0x8000000 && $address < 0x9000000)) {
            $lastName = null;
            $lastAddr = 0;
            foreach ($map2 as $mAddr => $mName) {
                if ($mAddr > $address) {
                    break;
                }
                $lastName = $mName;
                $lastAddr = $mAddr;
            }

            return $lastName . '+' . sprintf('0x%x', ($address - $lastAddr));
        }

        return null;
    }
}

$map = new \MarijnvdWerf\DisAsm\RomMap();

$usedData = [];

$jumpTables = [
    0x8011EC4 => 35,
    0x8028130 => 6,
    0x80C589C => 35,
    0x80C5B8C => 25,
    0x813771C => 5,
    0x801B7FC => 11,
    0x801B720 => 20,
    0x801BF64 => 48,
    0x801CBAC => 66,
    0x80A455C => 7,
    0x80A4598 => 16,
    0x80A4974 => 6,
    0x80A49C4 => 34,
];

$server = false;

if ($server) {
    echo <<<HTML
<!doctype html>
<html>
<head>
    <style>
        * {
            box-sizing: border-box;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        td, th {
            padding: 0;
        }

        .blob-num {
            width: 1%;
            min-width: 50px;
            padding-right: 10px;
            padding-left: 10px;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 12px;
            line-height: 20px;
            color: rgba(27, 31, 35, 0.3);
            text-align: right;
            white-space: nowrap;
            vertical-align: top;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none
        }

        .blob-num::before {
            content: attr(data-line-number)
        }

        .blob-code {
            position: relative;
            padding-right: 10px;
            padding-left: 10px;
            line-height: 20px;
            vertical-align: top
        }

        .blob-code-inner {
            overflow: visible;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 12px;
            color: #24292e;
            word-wrap: normal;
            white-space: pre
        }

        .error {
            background: orangered;
            color: #fff;
        }
    </style>
</head>
<body>
HTML;

}


foreach ($db as $i => $fn) {
    $br->setPosition($fn->address);

    try {
        if ($fn->address > 0x08000380 && $fn->address < 0x82027F8) {
            $fn->instructions = $disassembler->disassemble($br, $fn->address, $fn->address + $fn->size, $jumpTables);
        }
    } catch (Exception $e) {
        printf('<h4><code>0x%0X</code> %s</h4>' . PHP_EOL, $fn->address, $fn->name);
        echo '<pre><strong>' . $e->getMessage() . '</strong>' . PHP_EOL . PHP_EOL;
        echo $e->getTraceAsString() . '</pre>';
        var_dump($fn);
    }


    $map->addFunction($fn);

    if ($server && $fn->name === 'sub_8097078') {
        $i1 = 1;
        $labels = findLabels($fn->instructions, $i1);
        echo $htmlFormatter->formatTable($fn->instructions, $fn, $formatter, $br, $labels);
        die();
    }


    foreach ($fn->instructions as $line) {
        if ($line instanceof OffsetData) {
            continue;
        }

        if ($line instanceof Data) {
            $usedData[] = $line->value;
        }
    }
}


function get_next_function($i, $db)
{
    $db = array_reverse($db);

    $lastFn = null;
    foreach ($db as $fn) {
        if ($fn->address < $i) {
            break;
        }
        $lastFn = $fn;
    }

    return $lastFn;
}

$map2 = $data;

$fh = fopen('pokeruby-out.txt', 'w+');
$usedData = array_unique($usedData);
sort($usedData);
foreach ($usedData as $addr) {
    if (isset($data[$addr])) {
        fprintf($fh, "  %X %s", $addr, $data[$addr]);
    } else {
        fprintf($fh, "* %X %s", $addr, $map->getLabel($addr));
    }

    fwrite($fh, PHP_EOL);
}
fclose($fh);

$txtFormatter = new AsmFormatter();



$files = $sections;


ksort($files);


$fNames = [];

error_log('dumping files');
exec(sprintf('rm -Rf %s', escapeshellarg(realpath(__DIR__ . '/out'))));
if (file_exists('out')) {
    exec(sprintf('rm -Rf %s', realpath(__DIR__ . '/out')));
}
mkdir('out');
mkdir('out/asm');
mkdir('out/src');

$asmFh = null;
$cFh = null;
foreach ($map->procedures as $fn) {

    if (isset($files[$fn->address])) {
        if ($asmFh !== null) {
            fclose($asmFh);
            fclose($cFh);
        }


        $asmFh = fopen(__DIR__ . '/out/asm/' . $files[$fn->address] . '.s', 'w+');
        $cFh = fopen(__DIR__ . '/out/src/' . $files[$fn->address] . '.c', 'w+');

        $offset = 1;
    }

    if (count($fn->instructions) == 0) {
        continue;
    }

    $labels = findLabels($fn->instructions, $offset);
    ob_start();
    $txtFormatter->formatFunction($fn, $map, $formatter, $br, $labels);
    $instructions = ob_get_clean();
    $instructions = explode(PHP_EOL, $instructions);


    fprintf($asmFh, "\tthumb_func_start %s\n", $fn->name);
    fprintf($asmFh, "%s:\n", $fn->name);
    fprintf($asmFh, ".syntax divided\n");
    fprintf($asmFh, "%s", implode(PHP_EOL, $instructions));
    fprintf($asmFh, ".syntax unified\n");
    fprintf($asmFh, "	thumb_func_end %s\n", $fn->name);
    fprintf($asmFh, "\n");


    fprintf($cFh, "__attribute__((naked))\n");
    fprintf($cFh, "void %s()\n", $fn->name);
    fprintf($cFh, "{\n");
    fprintf($cFh, "    asm(\n");
    foreach ($instructions as $line) {
        fprintf($cFh, "        \"%s\\n\"\n", $line);
    }
    fprintf($cFh, "    );\n");
    fprintf($cFh, "}\n");
    fprintf($cFh, "\n");
}

file_put_contents('ld.txt', implode(PHP_EOL, array_map(function ($i) {
    return "\t\tasm/" . $i . '.o(.text);';
}, $fNames)));


fclose($cFh);
fclose($asmFh);
unlink($tempPath);
?>
</body>
</html>

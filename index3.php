<?php

use MarijnvdWerf\DisAsm\Data;
use MarijnvdWerf\DisAsm\Disassembler;
use MarijnvdWerf\DisAsm\HtmlFormatter;
use MarijnvdWerf\DisAsm\LocalLongBranch;
use MarijnvdWerf\DisAsm\OffsetData;
use MarijnvdWerf\DisAsm\Output\AsmFormatter;
use MarijnvdWerf\DisAsm\Output\Html\HtmlElement;
use MarijnvdWerf\DisAsm\Thumb\ConditionalBranch;
use MarijnvdWerf\DisAsm\Thumb\Instruction;
use MarijnvdWerf\DisAsm\Thumb\UnconditionalBranch;
use MarijnvdWerf\DisAsm\ThumbInstructionFormatter;

require 'new/_common.php';

$disassembler = new Disassembler();

/** @var \MarijnvdWerf\DisAsm\RomMap $map */
$map = $container['map'];

function findLabels(&$lines)
{
    $labels = [];

    $i = 1;

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

$data = [];

$jumpTables = [];

if (file_exists($container['basepath'] . '/config.php')) {
    include $container['basepath'] . '/config.php';
}


$br = $container['rom'];


$usedData = [];
$fnMap = [];
for ($i = 0; $i < count($map->procedures); $i++) {

    $fn = $map->getFunction($i);

    $br->setPosition($fn->address);

    if ($fn->instructionSet == \MarijnvdWerf\DisAsm\InstructionSet::THUMB) {
        //error_log(sprintf('0x%X %s', $fn->address, $fn->name));
        $fn->instructions = $disassembler->disassemble($br, $fn->address, $fn->address + $fn->size, $jumpTables);
    }

    foreach ($fn->instructions as $line) {
        if ($line instanceof OffsetData) {
            continue;
        }

        if ($line instanceof Data) {
            if (!isset($usedData[$line->value])) {
                $usedData[$line->value] = 0;
            }
            $usedData[$line->value]++;
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


if (false) {
    $i = 0;
    echo '<table>';
    error_log('===');
    $idc = fopen('/Users/Marijn/temp/script.idc', 'w+');
    fwrite($idc, "#include <idc.idc>\n");
    fwrite($idc, "\n");
    fwrite($idc, "static main() {\n");

    unset($db);

    while (true) {

        $fn = $map->getFunction($i);
        if ($fn === null) {
            break;
        }

        $i++;

        if (count($fn->instructions) == 0) {
            continue;
        }

        $tds = [
            new HtmlElement('td', ['class' => 'blob-num'], sprintf("%08X", $fn->address)),
            new HtmlElement('td', ['class' => 'blob-code blob-code-inner'], $fn->name),
        ];
        $tr = new HtmlElement('tr', [], $tds);
        echo $tr;

        if ($fn->instructionSet == \MarijnvdWerf\DisAsm\InstructionSet::ARM) {
            continue;
        }

        $lastAddr = max(array_keys($fn->instructions));
        $lastInstruction = $fn->instructions[$lastAddr];
        $lastSize = 2;
        if ($lastInstruction instanceof Data ||
            $lastInstruction instanceof \MarijnvdWerf\DisAsm\Thumb\LongBranch ||
            $lastInstruction instanceof LocalLongBranch
        ) {
            $lastSize = 4;
        }

        $nextAddr = ceil(($lastAddr + $lastSize) / 4) * 4;

        if ($map->getFunction($i)->address != $nextAddr) {
            fprintf($idc, "    MakeFunction(0x%X, BADADDR);\n", $nextAddr);
            $tds = [
                new HtmlElement('td', ['class' => 'blob-num', 'style' => 'color: #000'], sprintf("%04X <strong>%04X</strong>", $nextAddr >> 16, $nextAddr & 0xFFFF)),
                new HtmlElement('td'),
            ];

            $tr = new HtmlElement('tr', [], $tds);
            echo $tr;
        }

    }
    fwrite($idc, "}\n");
    fclose($idc);
    echo '</table>';
    error_log('^^^');

    die();
}

if (false) {
?>

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
<?php

$errorFns = [];

$htmlFormatter = new HtmlFormatter();
$instructionFormatter = new ThumbInstructionFormatter();
foreach ($map->getFunctions() as $fn) {
    if ($fn->instructionSet !== \MarijnvdWerf\DisAsm\InstructionSet::THUMB) {
        continue;
    }

    if (count($fn->instructions) == 0) {
        continue;
    }

    $errors = false;
    $out = $htmlFormatter->formatTable($fn->instructions, $fn, $instructionFormatter, $br, [], $errors);

    if ($errors) {
        echo '<h1>' . $fn->name . '</h1>';
        echo $out;
        $errorFns[] = $fn->address;

        foreach ($fn->instructions as $addr => $instruction) {
            if (!is_object($instruction) || get_class($instruction) !== Data::class) {
                continue;
            }

            if ($instruction->value > $fn->address && $instruction->value < $fn->address + $fn->size) {
                error_log(sprintf('0x%X => 0,', $addr));
            }
        }
    }
}

//  file_put_contents($container['basepath'] . '/errors.json', json_encode($errorFns, JSON_PRETTY_PRINT));
die();
}

if(false) {
    ksort($usedData);
    file_put_contents($container['basepath'] . '/data.json', json_encode($usedData, JSON_PRETTY_PRINT));
}

?>
<style>
    th.rotate {
        /* Something you can count on */
        height: 140px;
        white-space: nowrap;
        position: relative;
        padding: 0;
        min-width: 30px;
        border: none;
    }

    th.rotate > div {
        transform: rotate(315deg);
        transform-origin: 0 100%;
        width: auto;
        position: absolute;
        left: 100%;
        bottom: 0;
        background: #fff;

        border-bottom: 1px solid #ccc;
        padding: 0 10px;
        line-height: 18px;
    }

    table {
        display: block;
        width: 100%;
        overflow: auto;
    }

    table th {
        font-weight: 600;
    }

    table th,
    table td {
        padding: 6px 13px;
        border: 1px solid #dfe2e5;
    }

    table tr {
        background-color: #fff;
        border-top: 1px solid #c6cbd1;
    }

    table tr:nth-child(2n) {
        background-color: #f6f8fa;
    }
</style>
<?php

if (false) {

    $used = [];
    foreach ($ewram as $value) {
        $usages = 0;
        foreach ($map->procedures as $fn) {
            foreach ($fn->instructions as $instruction) {
                if ($instruction instanceof Data) {
                    if ($instruction->value == $value) {
                        $usages += 1;
                        break;
                    }
                }
            }
        }

        if ($usages > 1) {
            $used[] = $value;
        }
    }

    echo '<table>';
    echo '<tr>';
    echo '<td></td>';
    foreach ($used as $value) {
        if (isset($map2[$value])) {
            echo '<th class="rotate"><div><span>' . $map2[$value] . '</span></div></th>';
        } else {
            printf('<th class="rotate"><div><span>%X</span></div></th>', $value);
        }
    }
    echo '</tr>';

    /** @var \MarijnvdWerf\DisAsm\FunctionTableEntry $fn */
    foreach ($map->procedures as $fn) {
        echo '<tr>';
        echo '<td>' . $fn->name . '</td>';

        $offsets = [];
        foreach ($fn->instructions as $instruction) {
            if ($instruction instanceof Data) {
                $offsets[] = $instruction->value;
            }
        }

        foreach ($used as $value) {
            if (in_array($value, $offsets)) {
                echo '<td>X</td>';
            } else {
                echo '<td></td>';
            }
        }

        //  printf('<td colspan="%d"></td>', count($ewram));
        echo '</tr>';
    }

    echo '</table>';
}

if (false) {

    $used = $rodata;

    echo '<table>';
    echo '<tr>';
    echo '<td></td>';
    foreach ($used as $value) {
        if (isset($map2[$value])) {
            echo '<th class="rotate"><div><span>' . $map2[$value] . '</span></div></th>';
        } else {
            printf('<th class="rotate"><div><span><code>0x%X</code></span></div></th>', $value);
        }
    }
    echo '</tr>';

    /** @var \MarijnvdWerf\DisAsm\FunctionTableEntry $fn */
    foreach ($map->procedures as $fn) {
        echo '<tr>';
        echo '<td>' . $fn->name . '</td>';

        $offsets = [];
        foreach ($fn->instructions as $instruction) {
            if ($instruction instanceof Data) {
                $offsets[] = $instruction->value;
            }
        }

        foreach ($used as $value) {
            if (in_array($value, $offsets)) {
                echo '<td>X</td>';
            } else {
                echo '<td></td>';
            }
        }

        //  printf('<td colspan="%d"></td>', count($ewram));
        echo '</tr>';
    }

    echo '</table>';
}

if (false) {
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th></th>';
    /** @var \MarijnvdWerf\DisAsm\FunctionTableEntry $fn */
    foreach ($map->procedures as $fn) {
        echo '<th class="rotate"><div><span>' . $fn->name . '</span></div></th>';
    }
    echo '</tr>';
    echo '</thead>';

    echo '<tbody>';

    /** @var \MarijnvdWerf\DisAsm\FunctionTableEntry $fn */
    foreach ($map->procedures as $fn) {
        echo '<tr>';

        echo '<th>' . $fn->name . '</th>';

        $calledFns = [];
        foreach ($fn->instructions as $instruction) {
            if ($instruction instanceof \MarijnvdWerf\DisAsm\Thumb\LongBranch) {
                $calledFns[] = $instruction->address;
            }
        }
        /** @var \MarijnvdWerf\DisAsm\FunctionTableEntry $fn2 */
        foreach ($map->procedures as $fn2) {
            if (in_array($fn2->address, $calledFns)) {
                $i = array_search($fn2->address, $calledFns);
                unset($calledFns[$i]);
                echo '<td>X</td>';
            } else {
                echo '<td></td>';
            }
        }

        echo '<td>' . implode(', ', $calledFns) . '</td>';

        echo '</tr>';
    }

    echo '<tbody>';

    echo '</table>';
}

if (false) {
    echo '<pre>';
    foreach ($ewram as $i => $offset) {
        $size = 4;
        if (isset($ewram[$i + 1])) {
            $nextOffset = $ewram[$i + 1];
            $size = $nextOffset - $offset;
        }

        $typePrefix;
        $typeSuffix = '';
        if ($size == 4) {
            $typePrefix = 'void *';
            $typeSuffix = ' = NULL';
        } else if ($size == 3 || $size == 2) {
            $typePrefix = 'u16';
            $typeSuffix = ' = 0';
        } else if ($size == 1) {
            $typePrefix = 'u8';
            $typeSuffix = ' = 0';
        } else {
            $typePrefix = 'u8';
            $typeSuffix = '[' . $size . '] = { 0 }';
        }

        $name = (isset($map2[$offset]) ? $map2[$offset] : sprintf('_unk%X', $offset));

        printf("%s %s%s;\n", $typePrefix, $name, $typeSuffix);
    }
    echo '</pre>';
}

/*
usort($map, function ($lhs, $rhs) {
    return count($lhs->instructions) - count($rhs->instructions);
});
echo '<table>';
foreach ($map as $fn) {
    printf('<tr><td>%s</td><td>%d</td></tr>', $fn->name, count($fn->instructions));
}
echo '</table>';*/

$txtFormatter = new AsmFormatter();


error_log('dumping files');
if (!file_exists('out/')) {
    mkdir('out');
}


$currentFile = fopen('out/' . $container['project'] . '.asm', 'w+');

fwrite($currentFile, '	.include "asm/macros.inc"' . PHP_EOL . PHP_EOL);
$offset = 1;
foreach ($map->procedures as $fn) {
    if (count($fn->instructions) == 0) {
        continue;
    }


    $labels = findLabels($fn->instructions, $offset);
    fprintf($currentFile, "\tthumb_func_start %s\n", $fn->name);
    fprintf($currentFile, "%s:\n", $fn->name);

    ob_start();
    $txtFormatter->formatFunction($fn, $map, $formatter, $br, $labels, '.L'.$fn->name.'_');
    $asm = ob_get_clean();
    fwrite($currentFile, $asm);

    fprintf($currentFile, "	thumb_func_end %s\n", $fn->name);

    fprintf($currentFile, "\n");

    if($fn->name === 'sub_815F7F0') {
        fclose($currentFile);
        $currentFile = fopen('out/' . $container['project'] . '-lib.asm', 'w+');

        fwrite($currentFile, '	.include "asm/macros.inc"' . PHP_EOL . PHP_EOL);
    }

}


fclose($currentFile);
?>
</body>
</html>

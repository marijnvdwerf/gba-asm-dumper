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
use PhpBinaryReader\BinaryReader;

require 'vendor/autoload.php';

set_time_limit(0);

$romPath = '/Users/Marijn/Projects/pret/ruby/german/pokeruby_de.gba';
$romPath = '/Users/Marijn/Downloads/1279 - Beyblade VForce - Ultimate Blader Jam (U)(Evasion).gba';
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

    $fn = new FunctionTableEntry();
    $fn->name = $parts[0];
    $fn->segment = $parts[1];
    $fn->address = intval($parts[2], 16);
    $fn->size = intval($parts[3], 16);

    return $fn;
}

$db = file('beyblade.txt', FILE_IGNORE_NEW_LINES);

/** @var FunctionTableEntry[] $db */
$db = array_map('parseline', $db);

$db = array_filter($db, function (FunctionTableEntry $entry) {
    return true;

    if ($entry->address > 0x8200000) {
        return false;
    }

    if ($entry->address < 0x8010000) {
        return false;
    }
    if ($entry->address >= 0x0000000008064194 && $entry->address <= 0x0000000008064E30) {
        return false;
    }

    return true;
});

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


class RomMap
{
    public $procedures = [];

    public function addFunction(FunctionTableEntry $fn)
    {
        $this->procedures[$fn->address] = $fn;
    }

    public function getLabel($address)
    {
        global $map2;

        if (isset($map2[$address])) {
            return $map2[$address];
        }

        if (isset($this->procedures[$address])) {
            return $this->procedures[$address]->name;
        }


        if (isset($this->procedures[$address - 1])) {
            return $this->procedures[$address - 1]->name . ' + 1';
        }

        return null;
    }
}

$map = new RomMap();

$data = [];

$fnMap = [];
foreach ($db as $i => $fn) {
    if ($fn->address < 0x8040D18) {
        continue;
    }

    $br->setPosition($fn->address);

    $fnMap[$fn->address] = $fn->name;
    echo '<h4>' . $fn->name . '</h4>' . PHP_EOL;
    $fn->instructions = $disassembler->disassemble($br, $fn->address, $fn->address + $fn->size);

    $map->addFunction($fn);

    //    echo $htmlFormatter->formatTable($fn->instructions, $fn, $formatter, $br, $labels);


    foreach ($fn->instructions as $line) {
        if ($line instanceof OffsetData) {
            continue;
        }

        if ($line instanceof Data) {
            $data[] = $line->value;
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

$i = 0x8000000;
echo '<table>';
error_log('===');
while (false) {

    $fn = get_next_function($i, $db);
    if ($fn == null) {
        break;
    }


    if (count($fn->instructions) == 0) {
        continue;
    }

    if ($fn->address != $i) {
        $tds = [
            new HtmlElement('td', ['class' => 'blob-num', 'style' => 'color: #000'], sprintf("%04X <strong>%04X</strong>", $i >> 16, $i & 0xFFFF)),
            new HtmlElement('td'),
        ];

        $tr = new HtmlElement('tr', [], $tds);

        echo $tr;
        $i = $fn->address;
    }

    $tds = [
        new HtmlElement('td', ['class' => 'blob-num'], sprintf("%08X", $i)),
        new HtmlElement('td', ['class' => 'blob-code blob-code-inner'], $fn->name),
    ];

    $lastAddr = max(array_keys($fn->instructions));
    $lastInstruction = $fn->instructions[$lastAddr];
    $lastSize = 2;
    if ($lastInstruction instanceof Data ||
        $lastInstruction instanceof \MarijnvdWerf\DisAsm\Thumb\LongBranch ||
        $lastInstruction instanceof LocalLongBranch
    ) {
        $lastSize = 4;
    }


    $i = ceil(($lastAddr + $lastSize) / 4) * 4;
    if ($fn->name == 'sub_8065108') {
        $i -= 2;
    }

    $tr = new HtmlElement('tr', [], $tds);

    echo $tr;
}
echo '</table>';
error_log('^^^');

$values = array_count_values($data);


$map2b = file('beyblade-names.txt', FILE_IGNORE_NEW_LINES);
$map2 = [];
foreach ($map2b as $line) {
    list($name, $offset) = preg_split('|\s+|', $line);
    $map2[intval($offset, 16)] = $name;
}

$ewram = [];
ksort($values);
echo '<table>';
foreach ($values as $value => $count) {
    $block = [
        0x3000000 => '#00BCD4'
    ];

    $block = floor($value / 0x1000000);

    $attrs = ['class' => 'blob-num'];
    $color = null;
    if ($block == 3) {
        $attrs['style'] = 'border-left: 2px solid #2196F3';
        $ewram[] = $value;
    } else if ($block == 4) {
        $attrs['style'] = 'border-left: 2px solid #4CAF50';
    } else if ($block == 8) {
        $attrs['style'] = 'border-left: 2px solid #FFC107';
    }
    printf('<tr>');

    echo(new HtmlElement('td', $attrs, sprintf('%08x;', $value)));
    printf('<td class="blob-code blob-code-inner">%d</td>', $count);
    if (isset($map2[$value])) {
        printf('<td class="blob-code blob-code-inner">%s</td>', $map2[$value]);
    }
    if (isset($fnMap[$value - 1])) {
        printf('<td class="blob-code blob-code-inner">%s + 1</td>', $fnMap[$value - 1]);
    } else {
        echo '<td></td>';
    }

    echo '</tr>';
}

echo '</table>';

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


$files = [
    0x8000000 => '',
    0x8040D18 => '',
    0x804A388 => 'tutorial',
    0x80578C0 => 'bios',
    0x80578E0 => '',
    0x8057B80 => 'debug',
    0x8064194 => 'render',
    0x8064F38 => '',
    0x065C14 => 'call_via',
    0x8065C50 => '',
    0x8064f38 => '',
    0x806234c => 'sound',
    0x8062e70 => 'actor',
];


ksort($files);


$fNames = [];

error_log('dumping files');
exec(sprintf('rm -Rf %s', escapeshellarg(realpath(__DIR__ . '/out'))));
if (!file_exists('out')) {
    mkdir('out');

    $currentFile = null;
    foreach ($map->procedures as $fn) {
        if (count($fn->instructions) == 0) {
            continue;
        }

        if (false) {
            if (isset($files[$fn->address])) {
                if ($currentFile !== null) {
                    $asm = ob_get_clean();

                    file_put_contents('out/' . $currentFile, $asm);
                }


                $currentFile = sprintf('#%08x', $fn->address);
                if ($files[$fn->address] !== '') {
                    $currentFile .= '-' . $files[$fn->address];
                }
                $currentFile .= '.s';

                ob_start();
                $offset = 1;
            }
        } else {

            if (isset($files[$fn->address])) {
                $folder = sprintf('%x', $fn->address);
                if ($files[$fn->address] !== '') {
                    $folder .= '-' . $files[$fn->address];
                }
            }
            ob_start();
            echo '	.include "asm/common.inc"' . PHP_EOL . PHP_EOL;
            $offset = 1;
        }


        $labels = findLabels($fn->instructions, $offset);
        printf("\tthumb_func_start %s\n", $fn->name);
        printf("%s:\n", $fn->name);
        $txtFormatter->formatFunction($fn, $map, $formatter, $br, $labels);
        printf("	thumb_func_end %s\n", $fn->name);

        if (false) {

            printf("\n");
        } else {

            printf("\n.align 2, 0 @ Don't pad with nop.\n");

            $asm = ob_get_clean();

            if (!file_exists('out/' . $folder . '/')) {
                mkdir('out/' . $folder . '/');
            }

            $currentFile = sprintf('%x', $fn->address);
            if (substr($fn->name, 0, 4) !== 'sub_') {
                $currentFile .= '-' . $fn->name;
            }
            $currentFile .= '.s';
            file_put_contents('out/' . $folder . '/' . $currentFile, $asm);
            $fNames[] = $folder . '/' . $currentFile;
        }
    }

    $asm = ob_get_clean();

    //file_put_contents('out/' . $currentFile, $asm);

    file_put_contents('ld.txt', implode(PHP_EOL, array_map(function ($i) {
        return "\t\tasm/" . $i . '.o(.text);';
    }, $fNames)));

}
fclose($fh);
unlink($tempPath);
?>
</body>
</html>

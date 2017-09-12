<?php

namespace MarijnvdWerf\DisAsm\Output;

use MarijnvdWerf\DisAsm\Data;
use MarijnvdWerf\DisAsm\RomMap;
use MarijnvdWerf\DisAsm\ThumbInstructionFormatter;
use PhpBinaryReader\BinaryReader;

class AsmFormatter
{

    public function formatFunction($fn, RomMap $map, ThumbInstructionFormatter $formatter, BinaryReader $br, $labels, $labelPrefix)
    {
        $lines = $fn->instructions;
        $min = min(array_keys($lines));
        $max = max(array_keys($lines));
        $max = max($max, $fn->address + $fn->size - 1);

        $i = $min;

        $tableContent = [];

        foreach ($lines as $addr => $line) {
            if (isset($labels[$addr])) {
                echo $labelPrefix . $labels[$addr] . ':' . PHP_EOL;
            }

            if ($line instanceof Data) {

                if (isset($labels[$line->value])) {
                    $value = $labelPrefix . $labels[$line->value];
                } else {
                    $value = $map->getLabel($line->value);
                    if ($value == null) {
                        $value = sprintf('0x%x', $line->value);
                    }
                }


                printf("\t.word\t%s\n", $value);
            } else {
                printf("\t%s\n", $formatter->format($line, $map, $labels, $lines, $labelPrefix));
            }
        }
    }
}

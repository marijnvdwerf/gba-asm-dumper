<?php

namespace MarijnvdWerf\DisAsm;

use MarijnvdWerf\DisAsm\Output\Html\HtmlElement;
use MarijnvdWerf\DisAsm\Thumb\LongBranch;
use PhpBinaryReader\BinaryReader;

class HtmlFormatter
{

    public function formatTable($lines, $fn, ThumbInstructionFormatter $formatter, BinaryReader $br, $labels)
    {
        $min = min(array_keys($lines));
        $max = max(array_keys($lines));
        $max = max($max, $fn->address + $fn->size - 1);

        $i = $min;

        $tableContent = [];
        while ($i <= $max) {
            $size = 2;

            $rowContents = [];

            if (isset($labels[$i])) {
                $rowContents[] = new HtmlElement('td', [], $labels[$i]);
            } else {
                $rowContents[] = new HtmlElement('td');
            }

            $rowContents[] = new HtmlElement('td', ['class' => 'blob-num', 'data-line-number' => sprintf('%0X', $i)]);

            if (isset($lines[$i])) {
                $line = $lines[$i];

                if ($line instanceof Data) {
                    $text = sprintf(".4byte\t0x%X", $line->value);
                    $size = 4;
                } else {
                    $text = $formatter->format($line);
                    if ($line instanceof LongBranch || $line instanceof LocalLongBranch) {
                        $size = 4;
                    }
                }

                $rowContents[] = new HtmlElement('td', ['class' => 'blob-code blob-code-inner', 'id' => sprintf('L%0X', $i)], $text);

            } else {
                $br->setPosition($i);
                $var1 = $br->readUInt8();
                $var2 = $br->readUInt8();

                $classNames = ['blob-code', 'blob-code-inner'];
                if ($var1 !== 0 || $var2 !== 0) {
                    $classNames[] = 'error';
                }

                $rowContents[] = new HtmlElement(
                    'td',
                    ['class' => implode(' ', $classNames), 'id' => sprintf('L%0X', $i)],
                    sprintf('<code>%02x %02X</code>', $var1, $var2)
                );
            }

            $tableContent[] = new HtmlElement('tr', [], $rowContents);

            $i += $size;
        }

        return new HtmlElement('table', [], $tableContent);
    }
}

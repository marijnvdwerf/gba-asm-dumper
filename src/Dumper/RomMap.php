<?php

namespace MarijnvdWerf\DisAsm\Dumper;

use PhpBinaryReader\BinaryReader;

class RomMap
{
    /** @var RomMapEntry[] */
    private $names = [];

    private $dumpers = [];
    private $unboundedDumpers = [];
    private $padding = [];

    private $added;

    public function dump(BinaryReader $br, $out, $start, $end)
    {
        if ($out == null) {
            $out = STDOUT;
        }

        while (true) {
            $parsedBounded = false;
            $this->added = false;
            foreach ($this->names as $addr => $item) {
                if ($addr < 0x8000000) {
                    continue;
                }

                if ($item->data !== null) {
                    continue;
                }

                if (!isset($this->dumpers[$item->type])) {
                    continue;
                }

                try {
                    $br->setPosition($addr);
                    $stream = fopen('php://memory', 'r+');

                    //  printf("Dumping: %s\n", $item->type);
                    $result = call_user_func($this->dumpers[$item->type], $br, $this, $stream, $item->arguments);
                    rewind($stream);

                    $item->data = stream_get_contents($stream);
                    fclose($stream);
                    $item->size = $br->getPosition() - $addr;
                    if ($result === false) {
                        $item->size = 0;
                        $item->data = false;
                    }
                } catch (Throwable $t) {
                    $item->size = 0;
                    $item->data = false;
                }

                $parsedBounded = true;
            }

            if ($parsedBounded) {
                continue;
            }

            if ($this->added) {
                krsort($this->names);
            }

            $parsedUnbounded = false;
            foreach ($this->names as $addr => $item) {
                if ($addr < 0x8000000) {
                    continue;
                }

                if ($item->data !== null) {
                    continue;
                }

                if (!isset($this->unboundedDumpers[$item->type])) {
                    continue;
                }

                try {
                    $br->setPosition($addr);
                    $stream = fopen('php://memory', 'r+');
                    $result = call_user_func($this->unboundedDumpers[$item->type], $br, $this, $stream, $item->arguments);
                    rewind($stream);

                    $item->data = stream_get_contents($stream);
                    fclose($stream);
                    $item->size = $br->getPosition() - $addr;
                    if ($result === false) {
                        $item->size = 0;
                        $item->data = false;
                    }
                } catch (Throwable $t) {
                    $item->size = 0;
                    $item->data = false;
                }

                $parsedUnbounded = true;
            }

            if ($parsedUnbounded) {
                continue;
            }

            break;
        }

        ksort($this->names);
        foreach ($this->names as $addr => $item) {
            if ($item->type == 'EventScript') {
                $item->data = null;
                $item->size = 0;

                $br->setPosition($addr);
                $stream = fopen('php://memory', 'r+');
                $result = writeEventScript($br, $this, $stream, $item->arguments);
                rewind($stream);

                $item->data = stream_get_contents($stream);
                fclose($stream);
                $item->size = $br->getPosition() - $addr;
            }
        }


        $expected = $start;

        ksort($this->names);

        /** @var RomMapEntry $previous */
        $previous = null;
        foreach ($this->names as $addr => $name) {

            if ($addr < $expected) {
                continue;
            }

            $align = false;
            if ($addr !== $expected) {
                $size = $addr - $expected;


                if ($size < 4 && ($addr % 4 === 0)) {
                    if (!isset($this->padding[$expected])) {
                        $this->padding[$expected] = true;

                        $br->setPosition($expected);
                        for ($n = 0; $n < $size; $n++) {
                            if ($br->readUInt8() !== 0) {
                                $this->padding[$expected] = false;
                            }
                        }
                    }

                    $align = $this->padding[$expected];
                }

                if (!$align) {
                    //error_log(sprintf("%X", $expected - 0x8000000));
                    fprintf($out, "    .incbin \"baserom.gba\", 0x%X, 0x%X", $expected - 0x8000000, $size);
                    fprintf($out, "\n\n");
                }
            }

            if ($addr >= $end) {
                return;
            }

            if ($align) {
                fwrite($out, "    .align 2\n");
            }

            if (empty($name->label)) {
                fprintf($out, "unk_%X:: @ %X\n", $addr, $addr);
            } else if (substr($name->label, 0, 2) == '.L') {
                fprintf($out, "%s: \n", $name->label);
            } else {
                fprintf($out, "%s:: @ %X\n", $name->label, $addr);
            }

            if (!is_null($name->data) && $name->data !== false) {
                fprintf($out, "%s\n", $name->data);
            }

            $expected = $addr + $name->size;
            $previous = $name;
        }
    }

    public function getUnknown()
    {

        $expected = 0x8000000;

        $retval = [];
        $previousType = null;
        foreach ($this->names as $addr => $name) {

            $size = $addr - $expected;
            if ($size > 1) {
                if ($previousType == 'Text' || $previousType == 'TextJP') {
                    $retval[] = $expected;
                }
            }

            $previousType = $name->type;
            $expected = $addr + $name->size;
        }

        return $retval;
    }

    public function register($offset, $name, $type = null, ...$args)
    {
        // printf("\$map->register(0x%X, '%s');\n", $offset, $name);
        if ($offset > 0x8D00000) {
            global $container;
            /** @var \MarijnvdWerf\DisAsm\RomMap $map */
            $map = $container['map'];

            return $map->getLabel($offset);
        }

        if ($offset == 0) {
            return 'NULL';
        }

        if (isset($this->names[$offset])) {
            if ($this->names[$offset]->type != '' && $this->names[$offset]->label !== '') {
                return $this->names[$offset]->label;
            }

            if ($this->names[$offset]->label != '') {
                return $this->names[$offset]->label;
            }
        }

        if (empty($name)) {
            $typen = 'unk';
            if ($type !== null) {
                $typen = $type;
            }
            $name = sprintf("%s_%X", $typen, $offset);
        }

        $this->names[$offset] = new RomMapEntry($name, $type);
        $this->names[$offset]->arguments = $args;

        return $this->names[$offset]->label;
    }

    public function getLabel($getPosition)
    {
        if (isset($this->names[$getPosition])) {
            return $this->names[$getPosition]->label;
        }

        return null;
    }

    public function registerDumper($string, callable $string1)
    {
        $this->dumpers[$string] = $string1;
    }

    public function registerUnboundedDumper($string, callable $string1)
    {
        $this->unboundedDumpers[$string] = $string1;
    }

    public function hasLabel($offset)
    {
        return isset($this->names[$offset]);
    }
}

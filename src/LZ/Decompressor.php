<?php

namespace MarijnvdWerf\DisAsm\LZ;

use Exception;
use PhpBinaryReader\BinaryReader;

class Decompressor
{

    /** @var CacheItem[] $cache */
    private $cache = null;
    private $basepath;

    public function __construct($base)
    {
        if (!file_exists($base)) {
            mkdir($base, 0777, true);
        }

        $this->basepath = $base;
    }


    function decodeFile(BinaryReader $br)
    {
        $pos = $br->getPosition();

        $cacheFile = $this->basepath . '/cache.php';
        $filename = $this->basepath . '/cache/' . sprintf('%x.bin', $pos);

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        if ($this->cache === null) {
            if (file_exists($cacheFile)) {
                $this->cache = file_get_contents($cacheFile);
                $this->cache = unserialize($this->cache);
            } else {
                $this->cache = [];
            }
        }

        if (isset($this->cache[$pos])) {
            if (!$this->cache[$pos]->isLz) {
                throw new Exception('Not LZ');
            }

            if (file_exists($filename)) {
                $br->setPosition($pos + $this->cache[$pos]->compressedSize);
                return file_get_contents($filename);
            }
        }

        $data = null;
        $cache[$pos] = new CacheItem();

        try {
            $data = self::decodeFileImpl($br);
            file_put_contents($filename, $data);

            $cache[$pos]->isLz = true;
            $cache[$pos]->compressedSize = $br->getPosition() - $pos;
        } catch (Exception $e) {
            $data = null;
            $cache[$pos]->isLz = false;
            $br->setPosition($pos);
        }

        file_put_contents($cacheFile, serialize($cache));

        if(!$cache[$pos]->isLz) {
            throw new Exception('Not LZ');
        }

        return $data;
    }


    private static function decodeFileImpl(BinaryReader $br)
    {
        $pos = $br->getPosition();

        $header = $br->readUInt32();
        $br->setPosition($pos);
        $br->align();

        $remaining = $header >> 8;

        $headerCode = $br->readBytes(1);
        if ($headerCode !== "\x10") {
            throw new Exception('Wrong header code');
        }

        if ($remaining === 0) {
            throw new Exception('Zero-sized data');
        }

        self::logRow($headerCode, 'Header');

        $headerSize = $br->readBytes(3);
        self::logRow($headerSize, 'Size of the decompressed data (%x)', $remaining);
        $headerSize = $remaining;

        $blocksRemaining = 0;

        $dest = '';
        $destPos = 0;

        $blockHeader = 0;
        while ($remaining > 0) {
            if ($blocksRemaining !== 0) {
                if ($blockHeader & 0x80) {
                    // Compressed
                    $block = $br->readBytes(2);
                    $blockB = ord($block[1]) | ord($block[0]) << 8;
                    $disp = strlen($dest) - ($blockB & 0x0FFF) - 1;
                    $bytes = ($blockB >> 12) + 3;
                    self::logRow($block, "Compressed. Block header (disp: %d, bytes: %d, offset: %d)", $disp, $bytes, -($blockB & 0x0FFF) - 1);

                    while ($bytes-- && $remaining) {
                        $remaining -= 1;
                        $dest .= substr($dest, $disp, 1);

                        $disp++;
                    }
                } else {
                    // Uncompressed
                    $data = $br->readBytes(1);
                    $dest .= $data;

                    self::logRow($data, "Uncompressed data");
                    $remaining -= 1;
                }

                $blockHeader <<= 1;
                $blocksRemaining -= 1;
            } else {
                self::writeLog('<tr><td colspan="2"><hr/></td></tr>');
                $blockHeader = $br->readUInt8();
                $blocksRemaining = 8;

                self::logRow(chr($blockHeader), "New block header");
            }
        }

        if ($headerSize !== strlen($dest)) {
            throw new Exception('Wrong size');
        }

        return $dest;
    }

    private static function writeLog($a, $b = null, ...$args)
    {

    }

    private static function logRow($a, $b = null, ...$args)
    {

    }
}
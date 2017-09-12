<?php

use PhpBinaryReader\BinaryReader;

define('MON_GFX_FOOTPRINTS', 0x843FAB0);
require '_common.php';
require 'stringreader.php';

interface TileReaderInterface
{
    public function byteCount();

    public function defaultPalette($img);

    public function readTile(BinaryReader $br, $img, $x, $y);
}

class TileReader1Bpp implements TileReaderInterface
{

    public function byteCount()
    {
        return 8;
    }

    public function defaultPalette($img)
    {
        imagecolorallocatealpha($img, 0, 0, 0, 127); // 0
        imagecolorallocate($img, 0, 0, 0);
    }

    public function readTile(BinaryReader $br, $img, $tX, $tY)
    {
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x += 1) {
                imagesetpixel($img, $tX + $x, $tY + $y, $br->readBits(1));
            }
        }
    }
}

class TileReader4Bpp implements TileReaderInterface
{
    private $palette;

    public function __construct($palette)
    {
        $this->palette = $palette;
    }

    public function byteCount()
    {
        return 8 * 8 / 2;
    }

    public function defaultPalette($img)
    {
        imagecolorallocatealpha($img, 0, 0, 0, 127); // 0

        for ($i = 1; $i < 16; $i++) {
            $r = $g = $b = $i * 2;
            imagecolorallocate($img, round($r / 32 * 256), round($g / 32 * 256), round($b / 32 * 256));
        }
    }

    public function readTile(BinaryReader $br, $img, $tX, $tY)
    {
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $paletteIndex = $br->readUBits(4);
                imagesetpixel($img, $tX + $x, $tY + $y, $paletteIndex + $this->palette * 16);
            }
        }
    }
}

class TileReader8Bpp implements TileReaderInterface
{

    public function byteCount()
    {
        return 8 * 8;
    }

    public function defaultPalette($img)
    {
        imagecolorallocatealpha($img, 0, 0, 0, 127); // 0
        for ($r = 0; $r < 16; $r++) {
            for ($g = 0; $g < 16; $g++) {
                imagecolorallocate($img, $r * 16, $g * 16, 0);
            }
        }
    }

    public function readTile(BinaryReader $br, $img, $tX, $tY)
    {
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x += 1) {
                imagesetpixel($img, $tX + $x, $tY + $y, $br->readInt8());
            }
        }
    }
}

$reader4 = new TileReader4Bpp(0);
$reader8 = new TileReader8Bpp();
$reader1 = new TileReader1Bpp();

/** @var BinaryReader $rom */
$rom = $container['rom'];

class Item
{
    public $address;
    public $desc;
    public $group;


    public function __construct($address)
    {
        $this->address = $address;
    }

    public function __toString()
    {
        if ($this->desc !== null) {
            return $this->desc;
        }

        return get_class($this);
    }
}

class UnknownItem extends Item
{

}

class CompressedItem extends Item
{

}

class CompressedPaletteItem extends CompressedItem implements IPalette
{
}

class CompressedTileMap extends CompressedItem
{
    public $width;

    public function __construct($address, $width = 32)
    {
        parent::__construct($address);
        $this->width = $width;
    }
}

class CompressedImageItem4bpp extends CompressedItem
{
    public $width;
    public $palette;

    public function __construct($address, $width = null)
    {
        parent::__construct($address);
        $this->width = $width;
    }
}

class CompressedImageItem8bpp extends CompressedItem
{
    public $width;
    public $palette;

    public function __construct($address, $width)
    {
        parent::__construct($address);
        $this->width = $width;
    }
}

class CompressedImageItem1bpp extends CompressedItem
{
}

class ImageItem extends Item
{

    public $width;
    public $tiles;

    public function __construct($address, $width, $tiles)
    {
        parent::__construct($address);
        $this->width = $width;
        $this->tiles = $tiles;
    }
}

class ImageItem4bpp extends ImageItem
{
}

class ImageItem1bpp extends ImageItem
{
}

interface IPalette
{

}

class PaletteItem extends Item implements IPalette
{
    public $size;

    public function __construct($address, $sizes)
    {
        parent::__construct($address);
        $this->size = $sizes;
    }
}

class TileMapItem extends Item
{
    public $width;
    public $height;

    public function __construct($address, $width, $height)
    {
        parent::__construct($address);
        $this->width = $width;
        $this->height = $height;
    }
}

function register($item, ... $tags)
{
    global $todo;

    if (is_null($item)) {
        return;
    }

    if (!isset($todo[$item->address])) {
        $todo[$item->address] = $item;
        return $item;
    }

    if ($item instanceof UnknownItem) {
        throw new Exception('Redefining unknown item');
    }

    if ($todo[$item->address] instanceof UnknownItem) {
        $todo[$item->address] = $item;
        return $todo[$item->address];
    }

    if (get_class($item) !== get_class($todo[$item->address])) {
        throw new Exception(sprintf('Using different classes for %X (%s and %s)', get_class($item), get_class($todo[$item->address])));
    }


    if ($item instanceof CompressedImageItem4bpp) {
        if ($item->width != null) {
            $todo[$item->address]->width = $item->width;
        }

        if ($item->palette != null) {
            $todo[$item->address]->palette = $item->palette;
        }

        return $todo[$item->address];
    }

    if ($item instanceof CompressedPaletteItem || $item instanceof PaletteItem) {
        return;
    }

    if ($item instanceof CompressedTileMap) {
        return;
    }

    if ($item instanceof ImageItem4bpp) {
        return;
    }


    throw new Exception(sprintf('Unhandled class overlay (%s)', get_class($item)));
}

$todo = [];

$path = '/Users/Marijn/Projects/Firered/data/';

$finder = \Symfony\Component\Finder\Finder::create();
$finder->files()->in($path);

/** @var BinaryReader[] $brs */
$brs = [];

/** @var \Symfony\Component\Finder\SplFileInfo $file */
foreach ($finder as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES);
    $label = null;
    echo '<table>';
    foreach ($lines as $line) {
        $line = trim($line);

        if (preg_match('/^(.*)?:{1,2}/', $line, $m)) {
            $label = $m[1];
            continue;
        }

        if (!preg_match('/^\.incbin (.*?)\s*(@\s*(.*))?$/', $line, $m)) {
            continue;
        }

        $args = explode(',', $m[1]);
        $args = array_map('trim', $args);

        /** @var string $file */
        $file = json_decode($args[0]);
        $offset = isset($args[1]) ? intval($args[1], 0) : 0;
        $size = isset($args[2]) ? intval($args[2], 0) : -1;

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if ($ext !== 'gba') {
            continue;
        }

        if ($offset > 0x489C8C) {
            continue;
        }

        $type = null;

        if (isset($m[3])) {
            $type = trim($m[3]);

            if($type != 'FINDME') {
                continue;
            }
        }

        if (!isset($brs[$file])) {
            $brs[$file] = new BinaryReader(fopen('/Users/Marijn/Projects/Firered/' . $file, 'rb'));
        }

        $brs[$file]->setPosition($offset);


        $stringReader = new StringReader();

        /*$brs[$file]->setPosition(0x3FE9A9);
        while ($brs[$file]->getPosition() < 0x3FE9C4) {
            error_log(sprintf("0x%X", $brs[$file]->getPosition() + 0x8000000));
            $lines = $stringReader->readLines($brs[$file]);
            var_dump($lines);
        }
        die();*/

        /*
        try {
            $data = decodeFile($brs[$file]);

            if ($brs[$file]->getPosition() > $offset + $size + 32) {
                throw new Exception("Too big");
            }
        } catch (Exception $e) {
            $lines = $stringReader->readLines($brs[$file], StringReader::LANGUAGE_JAPANESE);

            if ($brs[$file]->getPosition() > $offset + $size) {
                continue;
            }

            echo '<tr>';
            printf('<th>%s</th>', $label);
            printf('<th>%s: %X</th>', $file, $offset);
            printf('<td>%s</td>', implode("<br/>", $lines));
            echo '</tr>';
        }

        continue;
        */



        try {
            $data = decodeFile($brs[$file]);

            if ($brs[$file]->getPosition() > $offset + $size + 32) {
                continue;
            }

            if (strlen($data > 0x800)) {
                continue;
            }

            if ($data === null) {
                continue;
            }

            echo '<tr>';
            printf("<th>%X [compressed]</th>\n", $offset);
            printf('<th>%s</th>', $file);
            printf('<th>%s</th>', $label);

            printf('<td>0x%X bytes</td>', strlen($data));
            printf('<td>%s</td>', dumpCompressedPalette($file, $offset, $data));
            printf('<td>%s</td>', dumpCompressedImage($offset, $data, '4bpp-lz', $reader4, 4));
            printf('<td>%s</td>', dumpCompressedImage($offset, $data, '8bpp-lz', $reader8));
            printf('<td>%s</td>', dumpCompressedMap($offset, $data));
            echo '</tr>';

        } catch (Exception asdsadsa$e) {

            $data = $brs[$file]->readBytes($size);
            if (($size % 0x20) === 0) {
                echo '<tr>';
                printf("<th>%X [raw]</th>\n", $offset);
                printf('<th>%s</th>', $file);
                printf('<th>%s</th>', $label);
                printf('<td>%s</td>', dumpCompressedPalette($file, $offset, $data));
                printf('<td>%s</td>', dumpCompressedImage($offset, $data, '4bpp', $reader4, 4));
                printf('<td>%s</td>', dumpCompressedImage($offset, $data, '8bpp', $reader8, 4));
                echo '</tr>';
            }
            // var_dump($e);
        }
    }

    echo '</table>';
}

die();


$usedData = json_decode(file_get_contents($container['basepath'] . '/data.json'), true);
foreach ($usedData as $addr => $uages) {
    if ($addr <= 0x0815f9b4) {
        continue;
    }

    if ($addr > 0x8D00000) {
        continue;
    }

    if (!isset($todo[$addr])) {
        $todo[$addr] = new UnknownItem($addr);
    }

}

if (!file_exists(dirname(__DIR__) . '/img')) {
    mkdir(dirname(__DIR__) . '/img');
}

class MapItem
{
    const TYPE_LZ = 0;
    const TYPE_IDENTIFIED = 1;
}

class Map
{
    private $width;
    private $height;
    private $img;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->width = (int)ceil(sqrt($end - $start));
        $this->height = (int)ceil(($end - $start) / $this->width);

        $this->img = imagecreate($this->width, $this->height);
        imagecolorallocate($this->img, 255, 0, 0);
        imagecolorallocate($this->img, 0, 255, 0);
        imagecolorallocate($this->img, 255, 255, 255);
        imagefill($this->img, 0, 0, 0);
    }

    public function mark($start, $size, $type)
    {
        return;

        $color = 2;
        if ($type == MapItem::TYPE_LZ) {
            $color = 1;
        }

        $x = $start - $this->start;
        $y = floor($x / $this->width);
        $x = $x % $this->width;


        $remaining = $size;
        $availableWidth = $this->width - $x;

        while ($remaining > 0) {
            $lw = min($availableWidth, $remaining);
            imagefilledrectangle($this->img, $x, $y, $x + $lw - 1, $y, $color);

            $y++;
            $x = 0;
            $remaining -= $lw;
            $availableWidth = $this->width;
        }

        imagepng($this->img, dirname(__DIR__) . '/out/map.png');
    }
}

$map = new Map(0x8d00000, 0x8EB0B20);

$fh = fopen(ROOT . '/out/todo.txt', 'w+');


$lastGroup = null;

$names = [];
if (file_exists($container['basepath'] . '/map.json')) {
    $names = json_decode(file_get_contents($container['basepath'] . '/map.json'), true);

    $names = array_combine(array_map('intval', array_keys($names)), array_values($names));
    ksort($names);
}

include 'html/head.php';
krsort($todo);

$todoBackup = $todo;

function getPalette(BinaryReader $br, $addr)
{
    global $todoBackup;

    if (!isset($todoBackup[$addr])) {
        throw new Exception('Invalid palette');
    }

    if (!($todoBackup[$addr] instanceof IPalette)) {
        throw new Exception('No palette');
    }


    $pos = $br->getPosition();

    $br->setPosition($addr);

    $pal = readPalette($br, $todoBackup[$addr]);

    $br->setPosition($pos);

    return $pal;
}

function readPalette(BinaryReader $br, IPalette $palette)
{

    $colors = [];


    if ($palette instanceof CompressedPaletteItem) {
        $br2 = new BinaryReader(decodeFile($br));

        while (!$br2->isEof()) {
            $color = $br2->readUInt16();
            $r = $color & 0b11111;
            $g = ($color >> 5) & 0b11111;
            $b = ($color >> 10) & 0b11111;

            $colors[] = [round($r / 32 * 256), round($g / 32 * 256), round($b / 32 * 256)];
        }
    } else if ($palette instanceof PaletteItem) {
        for ($i = 0; $i < $palette->size * 16; $i++) {
            $color = $br->readUInt16();
            $r = $color & 0b11111;
            $g = ($color >> 5) & 0b11111;
            $b = ($color >> 10) & 0b11111;

            $colors[] = [round($r / 32 * 256), round($g / 32 * 256), round($b / 32 * 256)];
        }
    }

    return $colors;
}

echo '<table>';
while (count($todo) > 0) {
    $item = array_pop($todo);

    if ($item->group !== $lastGroup) {
        echo '<tr><td colspan="7"><hr/></td></tr>';
    }
    $lastGroup = $item->group;

    echo '<tr>';
    printf('<td class="blob-num">%X</td>', $item->address);
    printf('<td>%s', $item);

    if (isset($names[$item->address])) {
        echo '<br /><code>' . $names[$item->address] . '</code>';
    }
    echo '</td>';

    $rom->setPosition($item->address);


    $span = 5;
    if ($item instanceof UnknownItem) {
        try {
            $file = decodeFile($rom);
            printf('<td>0x%X bytes</td>', strlen($file));
            printf('<td>%s</td>', dumpCompressedPalette($item->address, $file));
            printf('<td>%s</td>', dumpCompressedImage($item->address, $file, '4bpp-lz', $reader4, 8));
            printf('<td>%s</td>', dumpCompressedImage($item->address, $file, '8bpp-lz', $reader8));
            printf('<td>%s</td>', dumpCompressedMap($item->address, $file));

            $map->mark($item->address, $rom->getPosition() - $item->address, MapItem::TYPE_LZ);
            fprintf($fh, "%X (likely lz-compressed)\n", $item->address);
        } catch (Exception $e) {
            printf('<td colspan="3">%s</td>', $e->getMessage());

            $keys = array_keys($todo);
            $next = array_pop($keys);
            fprintf($fh, "%X (max size: 0x%x bytes)\n", $item->address, $next - $item->address);
        }
    } else if ($item instanceof CompressedItem) {
        try {
            $file = decodeFile($rom);
            if ($item instanceof CompressedPaletteItem) {
                printf('<td colspan="%d">%s</td>', $span, dumpCompressedPalette($item->address, $file));
            } else if ($item instanceof CompressedImageItem4bpp) {
                $attr = '';
                if ($item->width == null) {
                    $attr = ' style="background: cyan;"';
                }

                $suffix = '4bpp-lz';
                $palette = [];
                if ($item->palette !== null) {
                    $palette = getPalette($rom, $item->palette);
                    $suffix .= '-' . sprintf('p%x', $item->palette);
                }
                printf('<td colspan="%d" %s>%s</td>', $span, $attr, dumpCompressedImage($item->address, $file, $suffix, $reader4, $item->width, $palette));
            } else if ($item instanceof CompressedImageItem8bpp) {
                printf('<td colspan="%d">%s</td>', $span, dumpCompressedImage($item->address, $file, '8bpp-lz', $reader8, $item->width));
            } else if ($item instanceof CompressedTileMap) {
                printf('<td colspan="%d">%s</td>', $span, dumpCompressedMap($item->address, $file, $item->width));
            }

            $map->mark($item->address, $rom->getPosition() - $item->address, MapItem::TYPE_IDENTIFIED);
            $nextAddr = (int)(ceil($rom->getPosition() / 4) * 4);
            if (!isset($todo[$nextAddr])) {
                $todo[$nextAddr] = new UnknownItem($nextAddr);
                krsort($todo);
            }

        } catch (Exception $e) {
            printf('<td colspan="%d">%s</td>', $span, $e->getMessage());
        }
    } else if ($item instanceof ImageItem) {
        if ($item instanceof ImageItem1bpp) {
            printf('<td colspan="%d">%s</td>', $span, dumpImage($item->address, $rom, '1bpp', $reader1, $item->width, $item->tiles));
        } else if ($item instanceof ImageItem4bpp) {
            printf('<td colspan="%d">%s</td>', $span, dumpImage($item->address, $rom, '4bpp', $reader4, $item->width, $item->tiles));
        }

        $map->mark($item->address, $rom->getPosition() - $item->address, MapItem::TYPE_IDENTIFIED);
        $nextAddr = (int)(ceil($rom->getPosition() / 4) * 4);
        if (!isset($todo[$nextAddr])) {
            $todo[$nextAddr] = new UnknownItem($nextAddr);
            krsort($todo);
        }
    } else if ($item instanceof PaletteItem) {

        printf('<td colspan="%d">%s</td>', $span, dumpPalette($item->address, $rom, $item->size));

        $map->mark($item->address, $rom->getPosition() - $item->address, MapItem::TYPE_IDENTIFIED);
        $nextAddr = (int)(ceil($rom->getPosition() / 4) * 4);
        if (!isset($todo[$nextAddr])) {
            $todo[$nextAddr] = new UnknownItem($nextAddr);
            krsort($todo);
        }
    } else if ($item instanceof TileMapItem) {

        printf('<td colspan="%d">%s</td>', $span, dumpMap($item->address, $rom, $item->width, $item->height));

        $map->mark($item->address, $rom->getPosition() - $item->address, MapItem::TYPE_IDENTIFIED);
        $nextAddr = (int)(ceil($rom->getPosition() / 4) * 4);
        if (!isset($todo[$nextAddr])) {
            $todo[$nextAddr] = new UnknownItem($nextAddr);
            krsort($todo);
        }
    }

    echo '<tr>';
}
echo '</table>';


function dumpImage($addr, BinaryReader $br, $suffix, TileReaderInterface $tilereader, $width, $tilecount)
{
    $url = sprintf('/img/%x-%s.png', $addr, $suffix);
    $path = sprintf('%s/%s', dirname(__DIR__), $url);
    if (!file_exists($path)) {

        $img = imagecreate(8 * $width, ceil($tilecount / $width) * 8);
        $tilereader->defaultPalette($img);

        $x = 0;
        $y = 0;
        for ($i = 0; $i < $tilecount; $i++) {
            $tilereader->readTile($br, $img, $x, $y);

            $x += 8;

            if ($x == $width * 8) {
                $x = 0;
                $y += 8;
            }
        }

        imagepng($img, $path);
        imagedestroy($img);
    } else {
        $br->setPosition($br->getPosition() + $tilecount * $tilereader->byteCount());
    }

    return sprintf('<img src="%s"/>', $url);
}

function dumpMap($addr, BinaryReader $br, $width, $height)
{
    $url = sprintf('/img/%x-map.png', $addr);
    $path = sprintf('%s/%s', dirname(__DIR__), $url);
    $size = $width * $height;
    if (!file_exists($path)) {

        $img = imagecreate(8 * $width, 8 * $height);

        $scale = 255 / 0b1111111111;
        for ($i = 0; $i < 0b1111111111; $i++) {
            $c = round($i * 2);
            imagecolorallocate($img, $c, $c, $c);
        }
        imagefill($img, 0, 0, 0);

        $x = 0;
        $y = 0;
        for ($i = 0; $i < $size; $i++) {
            $screen = $br->readUInt16();
            $tile = $screen & 0b1111111111;
            $pal = $screen >> 12;
            $flipH = (bool)($screen & (0 << 10));
            $flipV = (bool)($screen & (0 << 11));

            imagefilledrectangle($img, $x, $y, $x + 7, $y + 7, $tile);

            $x += 8;

            if ($x == $width * 8) {
                $x = 0;
                $y += 8;
            }
        }

        imagepng($img, $path);
        imagedestroy($img);
    } else {
        $br->setPosition($br->getPosition() + $size * 2);
    }

    return sprintf('<img src="%s" width="%d" height="%d" async/>', $url, $width * 8, $height * 8);
}

function dumpCompressedMap($addr, $data, $width = 32)
{
    $size = strlen($data);
    if ($size <= 0x20) {
        return null;
    }

    $height = (int)ceil(($size / 2) / $width);

    if ($height > 512) {
        return null;
    }


    $url = sprintf('/img/%x-map-lz.png', $addr);
    $path = sprintf('%s/%s', dirname(__DIR__), $url);
    if (!file_exists($path)) {


        $img = imagecreate(8 * $width, 8 * $height);
        $br = new BinaryReader($data);

        $scale = 255 / 0b1111111111;
        for ($i = 0; $i < 0b1111111111; $i++) {
            $c = round($i * 2);
            imagecolorallocate($img, $c, $c, $c);
        }
        imagefill($img, 0, 0, 0);

        $x = 0;
        $y = 0;
        while (!$br->isEof()) {
            $screen = $br->readUInt16();
            $tile = $screen & 0b1111111111;
            $pal = $screen >> 12;
            $flipH = (bool)($screen & (0 << 10));
            $flipV = (bool)($screen & (0 << 11));

            imagefilledrectangle($img, $x, $y, $x + 7, $y + 7, $tile);

            $x += 8;

            if ($x == $width * 8) {
                $x = 0;
                $y += 8;
            }
        }

        imagepng($img, $path);
        imagedestroy($img);
    }

    return sprintf('<img src="%s" width="%d" height="%d" async/>', $url, $width * 8, $height * 8);
}

function dumpCompressedImage($addr, $data, $suffix, TileReaderInterface $tilereader, $width = 16, $palette = [])
{

    if ($width !== null) {
        return _dumpCompressedImage($addr, $data, $suffix . '-w' . $width, $tilereader, $width, $palette);
    }

    $out = [];

    foreach ([2, 4, 8, 16, 24, 32] as $width) {
        $out[] = _dumpCompressedImage($addr, $data, $suffix . '-w' . $width, $tilereader, $width, $palette);
    }

    return implode(PHP_EOL, $out);
}

function _dumpCompressedImage($addr, $data, $suffix, TileReaderInterface $tilereader, $width = 16, $palette = [])
{
    $size = strlen($data);
    if ($size % $tilereader->byteCount() !== 0) {
        return null;
    }

    $tiles = $size / $tilereader->byteCount();

    $url = sprintf('/img/%x-%s.png', $addr, $suffix);
    $path = sprintf('%s/%s', dirname(__DIR__), $url);
    if (!file_exists($path)) {
        $br = new BinaryReader($data);


        $dy = (int)(ceil($tiles / $width) * 8);
        $dx = (int)(8 * $width);
        $img = imagecreate($dx, $dy);
        if ($img === false) {
            error_log(sprintf('imagecreate(%d, %d) (%d)', $dx, $dy, $size));
            return false;
        }
        if (count($palette) === 0) {
            $tilereader->defaultPalette($img);
        } else {
            foreach ($palette as $color) {
                imagecolorallocate($img, $color[0], $color[1], $color[2]);
            }
        }
        $errorColor = imagecolorallocate($img, 255, 0, 0);


        for ($y = 0; $y < $dy; $y += 8) {
            for ($x = 0; $x < $dx; $x += 8) {
                if (!$br->isEof()) {
                    $tilereader->readTile($br, $img, $x, $y);
                } else {
                    imagefilledrectangle($img, $x, $y, $x + 7, $y + 7, $errorColor);
                }
            }
        }

        imagepng($img, $path);
        imagedestroy($img);
    }

    return sprintf('<img src="%s" async/>', $url);
}


function dumpCompressedPalette($file, $addr, $data)
{
    $size = strlen($data);
    if ($size % 32 !== 0) {
        return null;
    }

    if ($size > 16 * 16 * 2) {
        return null;
    }


    $url = sprintf('/img/%s-%x-pal-lz.png', md5($file), $addr);
    $path = sprintf('%s/%s', dirname(__DIR__), $url);
    if (!file_exists($path)) {
        $br = new BinaryReader($data);

        $rows = ceil($size / 32);
        $columns = 16;
        $img = imagecreate(8 * $columns, 8 * $rows);

        while (!$br->isEof()) {
            $color = $br->readUInt16();
            $r = $color & 0b11111;
            $g = ($color >> 5) & 0b11111;
            $b = ($color >> 10) & 0b11111;
            imagecolorallocate($img, round($r / 32 * 256), round($g / 32 * 256), round($b / 32 * 256));
        }


        for ($y = 0; $y < $rows; $y++) {
            for ($i = 0; $i < $columns; $i++) {
                imagefilledrectangle($img, $i * 8, $y * 8, $i * 8 + 7, $y * 8 + 7, $y * $columns + $i);
            }
        }

        imagepng($img, $path);
        imagedestroy($img);
    }

    return sprintf('<img src="%s" async/>', $url);
}


function dumpPalette($addr, BinaryReader $br, $rows)
{

    $url = sprintf('/img/%x-pal.png', $addr);
    $path = sprintf('%s/%s', dirname(__DIR__), $url);
    if (!file_exists($path)) {

        $columns = 16;
        $img = imagecreate(8 * $columns, 8 * $rows);

        for ($i = 0; $i < 16 * $rows; $i++) {
            $color = $br->readUInt16();
            $r = $color & 0b11111;
            $g = ($color >> 5) & 0b11111;
            $b = ($color >> 10) & 0b11111;
            imagecolorallocate($img, round($r / 32 * 256), round($g / 32 * 256), round($b / 32 * 256));
        }

        for ($y = 0; $y < $rows; $y++) {
            for ($i = 0; $i < $columns; $i++) {
                imagefilledrectangle($img, $i * 8, $y * 8, $i * 8 + 7, $y * 8 + 7, $y * $columns + $i);
            }
        }

        imagepng($img, $path);
        imagedestroy($img);
    } else {
        $br->setPosition($br->getPosition() + 32 * $rows);
    }

    return sprintf('<img src="%s" async/>', $url);
}

function writeLog($string, ...$args)
{

}

function logRow($a, $b = null, ...$args)
{

}

class CacheItem
{
    public $isLz = false;
    public $compressedSize = 0;
}

/** @var CacheItem[] $cache */
$cache = null;

function decodeFile(\PhpBinaryReader\BinaryReader $br)
{
    global $cache, $container;

    $pos = $br->getPosition();

    $cacheFile = $container['basepath'] . '/cache.php';
    $filename = $container['basepath'] . '/cache/' . sprintf('%x.bin', $pos);

    if (!file_exists(dirname($filename))) {
        mkdir(dirname($filename), 0777, true);
    }

    if ($cache === null) {
        if (file_exists($cacheFile)) {
            $cache = file_get_contents($cacheFile);
            $cache = unserialize($cache);
        } else {
            $cache = [];
        }
    }

    if (isset($cache[$pos])) {
        if (!$cache[$pos]->isLz) {
            throw new Exception('Not LZ');
        }

        if (file_exists($filename)) {
            $br->setPosition($pos + $cache[$pos]->compressedSize);
            return file_get_contents($filename);
        }
    }

    $data = null;
    $cache[$pos] = new CacheItem();

    try {
        $data = decodeFileImpl($br);
        file_put_contents($filename, $data);

        $cache[$pos]->isLz = true;
        $cache[$pos]->compressedSize = $br->getPosition() - $pos;
    } catch (Exception $e) {
        $data = null;
        $cache[$pos]->isLz = false;
    }

    file_put_contents($cacheFile, serialize($cache));

    return $data;
}


function decodeFileImpl(BinaryReader $br)
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

    logRow($headerCode, 'Header');

    $headerSize = $br->readBytes(3);
    logRow($headerSize, 'Size of the decompressed data (%x)', $remaining);
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
                logRow($block, "Compressed. Block header (disp: %d, bytes: %d, offset: %d)", $disp, $bytes, -($blockB & 0x0FFF) - 1);

                while ($bytes-- && $remaining) {
                    $remaining -= 1;
                    $dest .= substr($dest, $disp, 1);

                    $disp++;
                }
            } else {
                // Uncompressed
                $data = $br->readBytes(1);
                $dest .= $data;

                logRow($data, "Uncompressed data");
                $remaining -= 1;
            }

            $blockHeader <<= 1;
            $blocksRemaining -= 1;
        } else {
            writeLog('<tr><td colspan="2"><hr/></td></tr>');
            $blockHeader = $br->readUInt8();
            $blocksRemaining = 8;

            logRow(chr($blockHeader), "New block header");
        }
    }

    if ($headerSize !== strlen($dest)) {
        throw new Exception('Wrong size');
    }

    return $dest;
}

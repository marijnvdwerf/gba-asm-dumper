<?php

use MarijnvdWerf\DisAsm\Output\Html\HtmlElement;
use PhpBinaryReader\BinaryReader;
use Symfony\Component\Debug\Debug;

define('MON_GFX_FOOTPRINTS', 0x843FAB0);
require '_common.php';

Debug::enable();

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

    private $paths = [];
    public $name = null;

    public function __set($name, $value)
    {
        if ($name == 'path') {
            $this->addPath($value);
            return;
        }

        $this->$name = $value;
    }

    public function __get($name)
    {
        if ($name == 'path') {
            return implode(', ', $this->paths);
        }

        return $this->$name;
    }

    public function merge(Item $other)
    {
        $this->paths = array_merge($this->paths, $other->paths);
        $this->paths = array_unique($this->paths);
        natcasesort($this->paths);

        mergeItems($this, $other, 'group');
        mergeItems($this, $other, 'name');
    }

    public function addPath($path)
    {
        $this->paths[] = $path;
        $this->paths = array_unique($this->paths);
        natcasesort($this->paths);
    }

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

    public function merge(Item $other)
    {
        parent::merge($other);

        mergeItems($this, $other, 'palette');
        mergeItems($this, $other, 'width');
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
    public $palette = null;

    public function __construct($address, $width, $tiles)
    {
        parent::__construct($address);
        $this->width = $width;
        $this->tiles = $tiles;
    }

    public function withPalette($addr)
    {
        $this->palette = $addr;

        return $this;
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

function mergeItems(Item $base, Item $new, $field)
{
    if ($new->$field == null) {
        return;
    }

    if ($new->$field == $base->$field) {
        return;
    }


    if ($base->$field == null) {
        $base->group = $new->group;
        return;
    }

    error_log(sprintf('Non-matching %s for 0x%X', $field, $base->address));
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
        throw new Exception(sprintf('Using different classes for %X (%s and %s)', $item->address, get_class($item), get_class($todo[$item->address])));
    }

    $todo[$item->address]->merge($item);

    return $todo[$item->address];
}

/** @var Item[] $todo */
$todo = [];
/*
$files = glob('/Users/Marijn/Projects/FireRed/gfx/override/*.png');
foreach ($files as $file) {
    $name = basename($file, '.png');
    $name = explode('-', $name);

    $files = glob(sprintf('/Users/Marijn/Projects/FireRed/gfx/%s*.png', $name[0]));

    $files2 = glob(sprintf('/Users/Marijn/Projects/FireRed/gfx/redump/%s*.png', $name[0]));

    $files = array_merge($files, $files2);
    foreach ($files as $path) {
        unlink($path);
    }

    rename($file, '/Users/Marijn/Projects/FireRed/gfx/' . basename($file));
}*/

$files = glob('/Users/Marijn/Projects/FireRed/gfx/*.png');
foreach ($files as $file) {
    $name = basename($file, '.png');
    $name = explode('-', $name);

    $addr = intval(array_shift($name), 16);

    $format = array_shift($name);
    $lz = (reset($name) == 'lz');
    if ($lz) {
        array_shift($name);
    }

    $width = null;
    $palette = null;
    $namesLeft = [];
    foreach ($name as $i => $val) {
        if (preg_match('/^w(\d+)$/', $val, $m)) {
            $width = intval($m[1]);
            continue;
        }

        if (preg_match('/^p\s?([a-fA-F0-9]+)$/', $val, $m)) {
            $palette = intval($m[1], 16);
            continue;
        }

        $namesLeft[] = $val;
    }
    $name = $namesLeft;

    $item = null;

    if ($format == '4bpp' && $lz) {
        $item = new CompressedImageItem4bpp($addr, $width);
    } else if ($format == '8bpp' && $lz) {
        $item = new CompressedImageItem8bpp($addr, $width == null ? 8 : $width);
    } else if ($format == 'pal' && $lz) {
        $item = new CompressedPaletteItem($addr);
    } else if ($format == 'map' && $lz) {
        $item = new CompressedTileMap($addr);
    }

    if ($palette !== null) {
        $item->palette = $palette;
    }

    register($item);
}

for ($i = 0; $i < 3; $i++) {
    register(new PaletteItem(0x83D3740 + $i * 32, 1));
}

$files = glob('/Users/Marijn/Projects/FireRed/gfx/redump/*.png');
foreach ($files as $file) {
    $name = basename($file, '.png');
    $name = explode('-', $name);

    $addr = intval(array_shift($name), 16);

    if (isset($todo[$addr])) {
        $todo[$addr]->width = null;
    }
}

define('GROUP_POKEMON', 1);
define('GROUP_MAIL', 2);
define('GROUP_BATTLE_ANIMS', 3);
define('GROUP_ITEMS', 4);

define('GFX_ITEM_ICONS', 0x83D4294);
define('ADDR_MON_ICON_TABLE', 0x83D37A0);
define('ADDR_MON_ICON_PALETTE_TABLE', 0x83D3E80);
define('ADDR_MON_FRONT_PIC_TABLE', 0x82350AC);
define('ADDR_MON_BACK_PIC_TABLE', 0x823654C);
define('ADDR_MON_PALETTE_TABLE', 0x823730C);
define('ADDR_MON_SHINY_PALETTE_TABLE', 0x82380CC);

require 'pokemon/species.php';

$rom->setPosition(0x83D4294);
for ($i = 0; $i < 375; $i++) {
    $img = $rom->readUInt32();
    $pal = $rom->readUInt32();

    $ucItemname = explode('_', $items[$i]);
    $ucItemname = array_map(function ($n) {
        return ucfirst(strtolower($n));
    }, $ucItemname);
    $ucItemname = implode($ucItemname);

    if (!isset($todo[$img])) {
        $img = new CompressedImageItem4bpp($img, 3);
        $img->palette = $pal;
        $img->path = sprintf('items/%03d-%s', $i, strtolower($items[$i]));
        $img->name = sprintf('kItemSpriteSheet_%s', $ucItemname);
        $img->group = GROUP_ITEMS;
        register($img);
    }

    if (!isset($todo[$pal])) {
        $pal = new CompressedPaletteItem($pal);
        $pal->path = sprintf('items/%03d-%s', $i, strtolower($items[$i]));
        $pal->name = sprintf('kItemPalette_%s', $ucItemname);
        $pal->group = GROUP_ITEMS;
        register($pal);
    }
}

function getSpeciesName($idx, $type)
{
    global $species, $nationalDex;

    if ($idx == 0) {
        return null;
    }

    if ($idx == 412) {
        // Egg
        return $type . '_Egg';
    }


    $name = $species[$idx];
    $name = explode('_', $name);
    $name = array_map(function ($n) {
        return ucfirst(strtolower($n));
    }, $name);
    $name = implode($name);

    return sprintf('%s_%s', $type, $name);
}

function getSpeciesPath($idx, $type)
{
    global $species, $nationalDex;

    if ($idx == 0) {
        return 'pokemon/000-unknown/' . $type;
    }

    if ($idx == 412) {
        // Egg
        return 'pokemon/000-egg/' . $type;
    }

    $suffix = '';
    if ($idx == 201) {
        $suffix = '_a';
    }
    if ($idx >= 413) {
        // Unown

        $array = ['B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'Exclamation_Mark',
            'Question_Mark'];
        if (isset($array[$idx - 413])) {
            $suffix = '_' . strtolower($array[$idx - 413]);
            $idx = 201;
        } else {
            return null;
        }

    }

    return sprintf('pokemon/%03d-%s/%s%s', $nationalDex[$idx - 1], strtolower($species[$idx]), $type, $suffix);
}

for ($i = 0; $i < 440; $i++) {
    $rom->setPosition(ADDR_MON_FRONT_PIC_TABLE + $i * 8);
    $frontPic = $rom->readUInt32();
    $rom->readBytes(2);
    $frontPicSpecies = $rom->readUInt16();

    $rom->setPosition(ADDR_MON_BACK_PIC_TABLE + $i * 8);
    $backPic = $rom->readUInt32();
    $rom->readBytes(2);
    $backPicSpecies = $rom->readUInt16();

    $rom->setPosition(ADDR_MON_PALETTE_TABLE + $i * 8);
    $pal = $rom->readUInt32();
    $palSpecies = $rom->readUInt16();

    $rom->setPosition(ADDR_MON_SHINY_PALETTE_TABLE + $i * 8);
    $shinyPal = $rom->readUInt32();
    $shinyPalSpecies = $rom->readUInt16() - 0x1F4;

    $rom->setPosition(ADDR_MON_ICON_TABLE + $i * 4);
    $iconAddr = $rom->readUInt32();

    $rom->setPosition(ADDR_MON_ICON_PALETTE_TABLE + $i);
    $iconPal = $rom->readUInt8();


    $frontPic = new CompressedImageItem4bpp($frontPic, 8);
    $frontPic->group = GROUP_POKEMON;
    $frontPic->palette = $pal;
    $frontPic->path = getSpeciesPath($frontPicSpecies, 'front');
    $frontPic->name = getSpeciesName($frontPicSpecies, 'kMonFrontPic');

    $backPic = new CompressedImageItem4bpp($backPic, 8);
    $backPic->group = GROUP_POKEMON;
    $backPic->palette = $pal;
    $backPic->path = getSpeciesPath($backPicSpecies, 'back');
    $backPic->name = getSpeciesName($backPicSpecies, 'kMonBackPic');

    $pal = new CompressedPaletteItem($pal, 1);
    $pal->group = GROUP_POKEMON;
    $pal->path = getSpeciesPath($palSpecies, 'normal');
    $pal->name = getSpeciesName($palSpecies, 'kMonPalette');

    $shinyPal = new CompressedPaletteItem($shinyPal, 1);
    $shinyPal->group = GROUP_POKEMON;
    $shinyPal->path = getSpeciesPath($shinyPalSpecies, 'shiny');
    $shinyPal->name = getSpeciesName($shinyPalSpecies, 'kMonShinyPalette');

    $icon = new ImageItem4bpp($iconAddr, 4, 32);
    $icon->group = GROUP_POKEMON;
    $icon->path = getSpeciesPath($i, 'icon');
    $icon->palette = 0x83D3740 + $iconPal * 32;
    $icon->name = getSpeciesName($i, 'kMonIcon');

    registerMaybe($frontPic);
    registerMaybe($backPic);
    registerMaybe($pal);
    registerMaybe($shinyPal);
    registerMaybe($icon);
}

function registerMaybe(Item $item)
{
    global $todo;

    if (isset($todo[$item->address])) {
        error_log(sprintf('Warning: logging 0x%X twice.', $item->address));
        return;
    }

    register($item);
}


define('GFX_BALL_TILES', 0x0826056C);
define('GFX_BALL_PAL', 0x82605CC);
define('GFX_TRAINER_IMG', 0x823957C);
define('GFX_TRAINER_PAL', 0x8239A1C);

for ($i = 0; $i < 12; $i++) {
    $rom->setPosition(GFX_BALL_TILES + $i * 8);
    $img = $rom->readUInt32();

    $rom->setPosition(GFX_BALL_PAL + $i * 8);
    $pal = $rom->readUInt32();

    $img = new CompressedImageItem4bpp($img, 2);
    $img->palette = $pal;
    $img->group = 'ball-' . $i;

    $pal = new CompressedPaletteItem($pal);
    $pal->group = 'ball-' . $i;

    register($img);
    register($pal);
}


$trNames = [];
for ($i = 0; $i < 148; $i++) {

    $rom->setPosition(GFX_TRAINER_IMG + $i * 8);
    $img = $rom->readUInt32();

    $rom->setPosition(GFX_TRAINER_PAL + $i * 8);
    $pal = $rom->readUInt32();

    $img = new CompressedImageItem4bpp($img, 8);
    $img->palette = $pal;
    $pal = new CompressedPaletteItem($pal);


    $names = [];
    foreach ($trainerClassToPic as $class => $pic) {
        if ($pic == $i) {
            $names[] = $trainerClassToName[$class];
        }
    }

    $names = array_unique($names);

    $names = array_map(function ($n) use ($trainerNames) {
        return strtolower(preg_replace('/[^A-Z]+/', '_', $trainerNames[$n]));
    }, $names);
    $img->path = sprintf('trainers/%03d-%s/front', $i, implode("-", $names));
    $pal->path = sprintf('trainers/%03d-%s/palette', $i, implode("-", $names));

    $upperCC = explode('_', array_shift($names));
    $upperCC = array_map('ucfirst', $upperCC);
    $upperCC = implode($upperCC);
    $suffixes = ['', '_2', '_3', '_4', '_5', '_6', '_7', '_8', '_9', '_10'];
    $suffix = 0;
    while (in_array($upperCC . $suffixes[$suffix], $trNames)) {
        $suffix++;
    }

    $upperCC .= $suffixes[$suffix];
    $trNames[] = $upperCC;

    $img->name = sprintf('kTrainerFrontPic_%s', $upperCC);
    $pal->name = sprintf('kTrainerFrontPal_%s', $upperCC);

    register($img);
    register($pal);
}

$rom->setPosition(MON_GFX_FOOTPRINTS);
for ($i = 0; $i < 412; $i++) {
    $addr = $rom->readUInt32();
    $todo[$addr] = new ImageItem1bpp($addr, 2, 4);
    $todo[$addr]->path = getSpeciesPath($i, 'footprint');
    $todo[$addr]->name = getSpeciesName($i, 'kMonFootprint');
    $todo[$addr]->group = GROUP_POKEMON;
}

$rom->setPosition(0x83ADE18);
for ($i = 0; $i < 27; $i++) {
    $img = $rom->readUInt32();
    $pal = $rom->readUInt32();
    $map = $rom->readUInt32();

    $img = register(new CompressedImageItem4bpp($img, null));
    $img->palette = $pal;
    $img->path = sprintf('battle_anims/backgrounds/%02d', $i);
    $img->name = sprintf('kBattleAnimBackgroundImage_%02d', $i);

    $pal = new CompressedPaletteItem($pal);
    $pal->path = sprintf('battle_anims/backgrounds/%02d', $i);
    $pal->name = sprintf('kBattleAnimBackgroundPalette_%02d', $i);
    $map = new CompressedTileMap($map);
    $map->path = sprintf('battle_anims/backgrounds/%02d', $i);
    $map->name = sprintf('kBattleAnimBackgroundTilemap_%02d', $i);

    register($pal);
    register($map);
}


define('GFX_ATTACK_IMG', 0x83ACC08);
define('GFX_ATTACK_PAL', 0x83AD510);

$attackImgs = [];

$rom->setPosition(GFX_ATTACK_IMG);
for ($i = 0; $i < 289; $i++) {
    $offset = $rom->readUInt32();
    $size = $rom->readUInt16();
    $tag = $rom->readUInt16();


    $img = register(new CompressedImageItem4bpp($offset));
    $img->path = sprintf('battle_anims/sprites/%03d', $tag - 10000);
    $img->group = GROUP_BATTLE_ANIMS;
    $img->name = sprintf('kBattleAnimSpriteSheet_%03d', $tag - 10000);

    assert(!isset($attackImgs[$tag]));
    $attackImgs[$tag] = $img;
}

$rom->setPosition(GFX_ATTACK_PAL);
$palNames = [];
for ($i = 0; $i < 289; $i++) {
    $offset = $rom->readUInt32();
    $tag = $rom->readUInt16();
    $rom->readBytes(2);

    $pal = new CompressedPaletteItem($offset);
    $pal->group = GROUP_BATTLE_ANIMS;

    $tagName = sprintf('%03d', $tag - 10000);
    if (in_array($tagName, $palNames)) {
        $tagName .= '_B';
    }
    $palNames[] = $tagName;

    $pal->name = sprintf('kBattleAnimPalette_%s', $tagName);
    $pal->path = sprintf('battle_anims/sprites/%s', $tagName);
    register($pal);

    $attackImgs[$tag]->palette = $offset;
}

register(new ImageItem4bpp(0x8DC25A0, 4, 32));
register(new ImageItem4bpp(0x8DD2290, 4, 16));
register(new ImageItem4bpp(0x8E3A788, 4, 32))
    ->withPalette(0x83D3740);


$a2 = [
    0x8E9CBBC => 25,
    0x8E99118 => 4,
    0x8E7BB88 => 32,
    0x8E76F5C => 32,
    0x8D11BC4 => 120,
    0x8E7A8DC => 5,
    0x8E7AB38 => 4,
    0x8E9CF5C => 148,
    0x8E9F260 => 165,
    0x8D196C4 => 1,
    0x8EAEA80 => 161,
];
register(new ImageItem4bpp(0x8E985D8, 5, 90));

$types = new ImageItem4bpp(0x8E95DDC, 16, 256);
$types->palette = 0x8e95dbc;
register($types);


foreach ($a2 as $a => $b) {
    $todo[$a] = new ImageItem4bpp($a, $b, $b);
}

register(new ImageItem4bpp(0x8EAEA80, 16, 161));

register(new TileMapItem(0x8E9F1FC, 6, 3));
register(new TileMapItem(0x8EA0F00, 32, 32));
register(new TileMapItem(0x8E9E9FC, 32, 32));

register(new CompressedTileMap(0x8EB0ADC, 10));

//$todo[0xUnknownItem] = new CompressedTileMapItem(0xUnknownItem, 10);
register(new PaletteItem(0x8EAFEA0, 9));
register(new PaletteItem(0x8EAEA20, 3));
register(new PaletteItem(0x8EAE094, 1));
register(new PaletteItem(0x8EAD5E8, 1));
register(new PaletteItem(0x8EAB6C4, 16));

$todo[0x8EA1700] = new PaletteItem(0x8EA1700, 1);
$todo[0x8EAA9F0] = new PaletteItem(0x8EAA9F0, 1);
$todo[0x8E9CEDC] = new PaletteItem(0x8E9CEDC, 3);
$todo[0x8E9BF28] = new PaletteItem(0x8E9BF28, 1);

$todo[0x8E95D9C] = new PaletteItem(0x8E95D9C, 1);
$todo[0x8E95DBC] = new PaletteItem(0x8E95DBC, 1);
$todo[0x8E97DDC] = new PaletteItem(0x8E97DDC, 1);
$todo[0x8E97FE4] = new PaletteItem(0x8E97FE4, 1);
$todo[0x8E98024] = new PaletteItem(0x8E98024, 6);
$todo[0x8E9B3D0] = new PaletteItem(0x8E9B3D0, 1);

$todo[0x8E9B578] = new PaletteItem(0x8E9B578, 1);
$todo[0x8E9C3D8] = new PaletteItem(0x8E9C3D8, 1);
$todo[0x8E9C3F8] = new PaletteItem(0x8E9C3F8, 1);
$todo[0x8E9C418] = new PaletteItem(0x8E9C418, 1);
$todo[0x8E99D8C] = new PaletteItem(0x8E99D8C, 1);
$todo[0x8E9B310] = new PaletteItem(0x8E9B310, 6);


$trainerRed = new ImageItem4bpp(0x8E69EBC, 8, 8 * 8 * 5);
$trainerRed->palette = 0x8E76EBC;
register($trainerRed);

$trainerLeaf = new ImageItem4bpp(0x8E6C6BC, 8, 8 * 8 * 5);
$trainerLeaf->palette = 0x8E76EE4;
register($trainerLeaf);

$trainerBrendan = new ImageItem4bpp(0x8e72ebc, 8, 8 * 8 * 4);
$trainerBrendan->palette = 0x8E550A4;
register($trainerBrendan);

$trainerMay = new ImageItem4bpp(0x8e74ebc, 8, 8 * 8 * 4);
$trainerMay->palette = 0x8E553CC;
register($trainerMay);

$trainer3 = new ImageItem4bpp(0x8e6eebc, 8, 8 * 8 * 4);
$trainer3->palette = 0x8E76F0C;
register($trainer3);

$trainer4 = new ImageItem4bpp(0x8e70ebc, 8, 8 * 8 * 4);
$trainer4->palette = 0x8E76F34;
register($trainer4);

/*
 *  borg <unk_8E69EBC, 0x2800>
ROM:08239FA4                                         ; DATA XREF: load_pokemon_image_TODO+8Eo
ROM:08239FA4                                         ; ROM:off_810BC58o
ROM:08239FA4                 borg <unk_8E6C6BC, 0x12800>
ROM:08239FA4                 borg <unk_8E72EBC, 0x22000>
ROM:08239FA4                 borg <unk_8E74EBC, 0x32000>
ROM:08239FA4                 borg <unk_8E6EEBC, 0x42000>
ROM:08239FA4                 borg <unk_8E70EBC, 0x52000>
 */

register(new PaletteItem(0x8E9F220, 1));
register(new PaletteItem(0x8E9F240, 1));

register(new PaletteItem(0x8E7ABB8, 1));
register(new PaletteItem(0x8E9BD08, 1));
register(new PaletteItem(0x8E9CF3C, 1));

register(new PaletteItem(0x8EAAB18 + 0x20 * 0, 1));
register(new PaletteItem(0x8EAAB18 + 0x20 * 1, 1));
register(new PaletteItem(0x8EAAB18 + 0x20 * 2, 1));
register(new PaletteItem(0x8EAAB18 + 0x20 * 3, 1));

register(new ImageItem4bpp(0x8E9E1DC, 8, 64));

define('GROUP_GBA', 3);
$rom->setPosition(0x847A890);

$widths = [
    16,
    16,
    16,
    32,
    16,
    32,
];

for ($i = 0; $i < 6; $i++) {
    $img = $rom->readUInt32();
    $todo[$img] = new CompressedImageItem4bpp($img, $widths[$i]);
    $todo[$img]->group = GROUP_GBA;
    $todo[$img]->path = 'berry_program_update/' . $i;
    $todo[$img]->name = 'kBerryProgramUpdateTiles_' . $i;

    $map = $rom->readUInt32();
    $todo[$map] = new CompressedTileMap($map);
    $todo[$map]->group = GROUP_GBA;
    $todo[$map]->path = 'berry_program_update/' . $i;
    $todo[$map]->name = 'kBerryProgramUpdateTilemap_' . $i;

    $pal = $rom->readUInt32();
    $todo[$pal] = new PaletteItem($pal, 2);
    $todo[$pal]->group = GROUP_GBA;
    $todo[$pal]->path = 'berry_program_update/' . $i;
    $todo[$pal]->name = 'kBerryProgramUpdatePalette_' . $i;

    $todo[$img]->palette = $pal;
}
$todo[0x8EA5604] = new PaletteItem(0x8EA5604, 3);


define('GFX_MAIL', 0x083EE9C8);
$rom->setPosition(GFX_MAIL);
for ($i = 0; $i < 12; $i++) {
    $pal = $rom->readUInt32();
    $todo[$pal] = new PaletteItem($pal, 1);
    $todo[$pal]->group = GROUP_MAIL;

    $img = $rom->readUInt32();
    $todo[$img] = new CompressedImageItem4bpp($img, 64);
    $todo[$img]->palette = $pal;
    $todo[$img]->group = GROUP_MAIL;

    $img = $rom->readUInt32();
    $todo[$img] = new CompressedTileMap($img);
    $todo[$img]->group = GROUP_MAIL;

    $rom->readBytes(8);
}

register(new PaletteItem(0x8EAE488, 1));
register(new PaletteItem(0x8D11B84, 1));
register(new PaletteItem(0x8D11BA4, 1));

register(new PaletteItem(0x8D2FBB4, 1));
register(new PaletteItem(0x8E98004, 1));

register(new PaletteItem(0x8E99198, 3));
register(new PaletteItem(0x8E9986C, 3));
register(new PaletteItem(0x8E99F24, 1));
register(new PaletteItem(0x8E9B3D0, 1));
register(new PaletteItem(0x8E9C14C, 1));


register(new TileMapItem(0x8EA0700, 32, 32));
register(new PaletteItem(0x8EA1B68, 16));
register(new PaletteItem(0x8EA97F4, 16));
register(new PaletteItem(0x8EA9D88, 16));

register(new PaletteItem(0x8E9CB9C, 1));
register(new ImageItem4bpp(0x8E9E9DC, 1, 1));

register(new CompressedImageItem4bpp(0x8EAE548, 16));
register(new CompressedTileMap(0x8EAE900));
register(new PaletteItem(0x8EAE528, 1));

register(new PaletteItem(0x8EAEA00, 1));

register(new CompressedImageItem8bpp(0x8D0CA70, 3));

$usedData = json_decode(file_get_contents($container['basepath'] . '/data.json'), true);
foreach ($usedData as $addr => $uages) {
    if ($addr < 0x8D00000 || $addr > 0x8EB0ADC) {
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

foreach ($names as $addr => $name) {
    if (strpos($name, 'unknown_D1C060') !== false) {
        $names[$addr] = str_replace('.', '_', $name) . '.bin';
    }
}

$names[0x8D2D2F4] = 'battle_anims/sprites/substitute_back.4bpp';

include 'html/head.php';
krsort($todo);

$todoBackup = $todo;

function getPalette(BinaryReader $br, $addr)
{
    global $todoBackup;

    if (!isset($todoBackup[$addr])) {
        return [];
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
            $colors[] = $br2->readUInt16();
        }
    } else if ($palette instanceof PaletteItem) {
        for ($i = 0; $i < $palette->size * 16; $i++) {
            $colors[] = $br->readUInt16();
        }
    }

    return $colors;
}

$fhGfx = fopen($container['basepath'] . '/graphics.s', 'w+');
fwrite($fhGfx, "    .section .gfx_data, \"aw\", %progbits\n");

fwrite($fhGfx, "\n");
fwrite($fhGfx, "GfxData::\n");


//echo '<table>';
$table = [];
while (count($todo) > 0) {
    $trAttrs = [];
    $trContents = [];

    $item = array_pop($todo);

    if ($item->address < 0x8D00000 || $item->address >= 0x8EB0B20) {
        continue;
    }

    if (in_array($item->group, [GROUP_POKEMON, GROUP_ITEMS, GROUP_BATTLE_ANIMS, GROUP_MAIL, GROUP_GBA])) {
        //  continue;
    }


    $trContents[] = new HtmlElement('td', ['class' => 'blob-num'], sprintf('%X', $item->address));
    $nameTd = [(string)$item];


    $color = 'RGB(120, 200, 80)';

    $path = $item->path;
    if (!empty($path)) {
        $nameTd[] = new HtmlElement('br');
        $nameTd[] = new HtmlElement('code', ['class' => 'path'], $path);
        $color = '#ccc';
    }

    if (isset($names[$item->address])) {
        $nameTd[] = new HtmlElement('br');
        $nameTd[] = new HtmlElement('code', ['style' => 'background: ' . $color . '; color: white'], $names[$item->address]);
    }

    $trContents[] = new HtmlElement('td', [], $nameTd);

    $rom->setPosition($item->address);

    $name = null;
    $nameSuffix = null;
    $incbin = null;
    $incbinData = null;
    $incbinSrc = null;
    $imgs = null;
    $comment = sprintf('0x%X', $item->address);

    $span = 5;
    if ($item instanceof UnknownItem) {
        try {
            $file = decodeFile($rom);
            $trContents[] = new HtmlElement('td', [], sprintf('0x%X bytes', strlen($file)));
            $trContents[] = new HtmlElement('td', [], dumpCompressedPalette($item->address, $file));
            $trContents[] = new HtmlElement('td', [], dumpCompressedImage($item->address, $file, '4bpp-lz', $reader4, 8));
            $trContents[] = new HtmlElement('td', [], dumpCompressedImage($item->address, $file, '8bpp-lz', $reader8));
            $trContents[] = new HtmlElement('td', [], dumpCompressedMap($item->address, $file));

            $nextAddr = (int)(ceil($rom->getPosition() / 4) * 4);
            if (!isset($todo[$nextAddr])) {
                $todo[$nextAddr] = new UnknownItem($nextAddr);
                krsort($todo);
            }

            $map->mark($item->address, $rom->getPosition() - $item->address, MapItem::TYPE_LZ);
            fprintf($fh, "%X (likely lz-compressed)\n", $item->address);
        } catch (Exception $e) {
            $trContents[] = new HtmlElement('td', ['colspan' => 3], $e->getMessage());

            $keys = array_keys($todo);
            $next = array_pop($keys);
            fprintf($fh, "%X (max size: 0x%x bytes)\n", $item->address, $next - $item->address);
        }
    } else if ($item instanceof CompressedItem) {
        $comment .= ' (LZ)';
        try {
            $file = decodeFile($rom);
            if ($item instanceof CompressedPaletteItem) {
                $nameSuffix = 'Pal';
                $incbin = get_path($item, '.gbapal.lz');
                $incbinSrc = get_path($item, '.gbapal');
                $incbinData = $file;
                $trContents[] = new HtmlElement('td', ['colspan' => $span], dumpCompressedPalette($item->address, $file));
            } else if ($item instanceof CompressedImageItem4bpp) {
                $nameSuffix = 'Gfx';
                $incbin = get_path($item, '.4bpp.lz');
                $incbinSrc = get_path($item, '.4bpp');
                $incbinData = $file;
                $style = '';
                if ($item->width == null) {
                    $style = 'background: cyan;';
                }

                $suffix = '4bpp-lz';
                $palette = [];
                if ($item->palette !== null) {
                    $palette = getPalette($rom, $item->palette);
                    $suffix .= '-' . sprintf('p%x', $item->palette);
                } else {
                    //  $trContents[] = new HtmlElement('td', [], 'NO PALETTE');
                }
                $imgs = dumpCompressedImage($item->address, $file, $suffix, $reader4, $item->width, $palette);
                $trContents[] = new HtmlElement('td', ['colspan' => $span, 'style' => $style], $imgs);
            } else if ($item instanceof CompressedImageItem8bpp) {
                $nameSuffix = 'Gfx';
                $incbin = get_path($item, '.8bpp.lz');
                $incbinSrc = get_path($item, '.8bpp');
                $incbinData = $file;
                $palette = [];
                $suffix = '';
                if ($item->palette !== null) {
                    $palette = getPalette($rom, $item->palette);
                    $suffix .= '-' . sprintf('p%x', $item->palette);
                } else {
                    // $trContents[] = new HtmlElement('td', [], 'NO PALETTE');
                }
                $imgs = dumpCompressedImage($item->address, $file, '8bpp-lz' . $suffix, $reader8, $item->width, $palette);
                $trContents[] = new HtmlElement('td', ['colspan' => $span], $imgs);
            } else if ($item instanceof CompressedTileMap) {
                $nameSuffix = 'Bin';
                $incbin = get_path($item, '.bin.lz');
                $incbinSrc = get_path($item, '.bin');
                $incbinData = $file;
                $trContents[] = new HtmlElement('td', ['colspan' => $span], dumpCompressedMap($item->address, $file, $item->width));
            }

            if (isset($imgs) && $imgs instanceof HtmlElement) {
                $incbinData = file_get_contents(ROOT . $imgs->getAttribute('src'));
                $incbinSrc = get_path($item, '.png');
            }

            $map->mark($item->address, $rom->getPosition() - $item->address, MapItem::TYPE_IDENTIFIED);
            $nextAddr = (int)(ceil($rom->getPosition() / 4) * 4);
            if (!isset($todo[$nextAddr])) {
                $todo[$nextAddr] = new UnknownItem($nextAddr);
                krsort($todo);
            }

        } catch (Exception $e) {
            $trContents[] = new HtmlElement('td', ['colspan' => $span], $e->getMessage());
        }
    } else if ($item instanceof ImageItem) {
        $nameSuffix = 'Gfx';

        $suffix = '';
        $palette = [];
        if ($item->palette !== null) {
            $palette = getPalette($rom, $item->palette);
            $suffix .= '-' . sprintf('p%x', $item->palette);
        } else {
            //  $trContents[] = new HtmlElement('td', [], 'NO PALETTE');
        }

        if ($item instanceof ImageItem1bpp) {
            $incbin = get_path($item, '.1bpp');
            $imgs = dumpImage($item->address, $rom, '1bpp' . $suffix, $reader1, $item->width, $item->tiles);
            $trContents[] = new HtmlElement('td', ['colspan' => $span], $imgs);
        } else if ($item instanceof ImageItem4bpp) {
            $incbin = get_path($item, '.4bpp');
            $imgs = dumpImage($item->address, $rom, '4bpp' . $suffix, $reader4, $item->width, $item->tiles, $palette);
            $trContents[] = new HtmlElement('td', ['colspan' => $span], $imgs);
        }

        $byteCount = $rom->getPosition() - $item->address;

        //$incbinSrc = $incbin;
        //$rom->setPosition($item->address);
        //$incbinData = $rom->readBytes($byteCount);

        if (isset($imgs) && $imgs instanceof HtmlElement) {
            $incbinData = file_get_contents(ROOT . $imgs->getAttribute('src'));
            $incbinSrc = get_path($item, '.png');
        }

        $map->mark($item->address, $byteCount, MapItem::TYPE_IDENTIFIED);
        $nextAddr = (int)(ceil($rom->getPosition() / 4) * 4);
        if (!isset($todo[$nextAddr])) {
            $todo[$nextAddr] = new UnknownItem($nextAddr);
            krsort($todo);
        }
    } else if ($item instanceof PaletteItem) {
        $incbin = get_path($item, '.gbapal');
        $nameSuffix = 'Pal';

        $trContents[] = new HtmlElement('td', ['colspan' => $span], dumpPalette($item->address, $rom, $item->size));

        $byteCount = $rom->getPosition() - $item->address;
        $rom->setPosition($item->address);

        $incbinSrc = $incbin;
        $incbinData = $rom->readBytes($byteCount);

        $map->mark($item->address, $byteCount, MapItem::TYPE_IDENTIFIED);
        $nextAddr = (int)(ceil($rom->getPosition() / 4) * 4);
        if (!isset($todo[$nextAddr])) {
            $todo[$nextAddr] = new UnknownItem($nextAddr);
            krsort($todo);
        }
    } else if ($item instanceof TileMapItem) {
        $incbin = get_path($item, '.bin');
        $nameSuffix = 'Bin';

        $trContents[] = new HtmlElement('td', ['colspan' => $span], dumpMap($item->address, $rom, $item->width, $item->height));

        $byteCount = $rom->getPosition() - $item->address;
        $rom->setPosition($item->address);

        $incbinSrc = $incbin;
        $incbinData = $rom->readBytes($byteCount);

        $map->mark($item->address, $byteCount, MapItem::TYPE_IDENTIFIED);
        $nextAddr = (int)(ceil($rom->getPosition() / 4) * 4);
        if (!isset($todo[$nextAddr])) {
            $todo[$nextAddr] = new UnknownItem($nextAddr);
            krsort($todo);
        }
    }


    fwrite($fhGfx, "\n");
    fwrite($fhGfx, "    .align 2\n");

    $name = $item->name;
    if ($name === null) {
        $name = sprintf('kUnk%s_%X', $nameSuffix, $item->address);
    }

    if ($incbinData !== null) {
        $dir = dirname($incbinSrc);
        if (!file_exists(ROOT . '/out-gfx/' . $dir)) {
            mkdir(ROOT . '/out-gfx/' . $dir, 0777, true);
        }
        file_put_contents(ROOT . '/out-gfx/' . $incbinSrc, $incbinData);
    }

    fprintf($fhGfx, "%s:: @ %s\n", $name, $comment);

    if ($incbin !== null) {
        fprintf($fhGfx, "    .incbin \"graphics/%s\"\n", $incbin);
    } else {
        fprintf($fhGfx, "    .incbin \"graphics/%s\", 0x%X, 0x%X\n", 'baserom.gba', $item->address - 0x8000000, $rom->getPosition() - $item->address);
    }

    $row = new HtmlElement('tr', $trAttrs, $trContents);
    // echo $row;
    $table[] = $row;
}

echo(new HtmlElement('table', [], $table));
//echo '</table>';


function get_path(Item $item, $ext)
{
    global $names;

    $path = $item->path;

    if (!empty($path)) {
        $path = explode(', ', $path);
        $path = array_shift($path);
        return sprintf('%s%s', $path, $ext);
    }

    if (isset($names[$item->address])) {
        $rseName = $names[$item->address];
        $resExt = pathinfo($rseName, PATHINFO_EXTENSION);
        $name = substr($rseName, 0, -strlen($resExt) - 1);

        return sprintf('rs/%s%s', $name, $ext);
    }

    return sprintf('unknown/unknown_%x%s', $item->address, $ext);
}

function dumpImage($addr, BinaryReader $br, $suffix, TileReaderInterface $tilereader, $width, $tilecount, $palette = [])
{
    $url = sprintf('/img/%x-%s.png', $addr, $suffix);
    $path = sprintf('%s/%s', dirname(__DIR__), $url);
    if (!file_exists($path)) {
        $ext = '';
        if ($tilereader instanceof TileReader1Bpp) {
            $ext = '.1bpp';
        } else if ($tilereader instanceof TileReader4Bpp) {
            $palette = array_slice($palette, 0, min(16, count($palette)));
            $ext = '.4bpp';
        } else if ($tilereader instanceof TileReader8Bpp) {
            $ext = '.8bpp';
        }

        $data = $br->readBytes($tilereader->byteCount() * $tilecount);

        $tempPath = sys_get_temp_dir() . '/diasm' . $ext;
        file_put_contents($tempPath, $data);

        $gbagfx = '/Users/Marijn/Projects/pokeruby/tools/gbagfx/gbagfx';


        $cmd = escapeshellcmd($gbagfx) . ' ' . escapeshellarg($tempPath) . ' ' . escapeshellarg($path);

        if (count($palette) !== 0) {
            $palPath = sys_get_temp_dir() . '/diasm.gbapal';
            $fh = fopen($palPath, 'w+');
            foreach ($palette as $color) {
                fwrite($fh, pack('v', $color));
            }
            fclose($fh);

            $cmd .= ' -palette ' . escapeshellarg($palPath);
        }

        $cmd .= ' -width ' . $width;

        exec($cmd);

    } else {
        $br->setPosition($br->getPosition() + $tilecount * $tilereader->byteCount());
    }

    return new HtmlElement('img', ['src' => $url]);
}

function dumpImage_old($addr, BinaryReader $br, $suffix, TileReaderInterface $tilereader, $width, $tilecount, $palette = [])
{
    $url = sprintf('/img/%x-%s.png', $addr, $suffix);
    $path = sprintf('%s/%s', dirname(__DIR__), $url);
    if (!file_exists($path)) {

        $img = imagecreate(8 * $width, ceil($tilecount / $width) * 8);
        if (count($palette) === 0) {
            $tilereader->defaultPalette($img);
        } else {
            $first = true;
            foreach ($palette as $color) {
                imagecolorallocatealpha($img, $color[0], $color[1], $color[2], $first ? 127 : 0);
                $first = false;
            }
        }

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

    return new HtmlElement('img', ['src' => $url]);
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
        $width = min($width, strlen($data) / $tilereader->byteCount());
        return _dumpCompressedImage($addr, $data, $suffix . '-w' . $width, $tilereader, $width, $palette);
    }

    $out = [];

    foreach ([2, 4, 8, 16, 24, 32] as $width) {
        $width = min($width, strlen($data) / $tilereader->byteCount());
        $out[] = _dumpCompressedImage($addr, $data, $suffix . '-w' . $width, $tilereader, $width, $palette);
    }

    return $out;
}


function _dumpCompressedImage($addr, $data, $suffix, TileReaderInterface $tilereader, $width = 16, $palette = [])
{
    $size = strlen($data);
    if ($size % $tilereader->byteCount() !== 0) {
        return null;
    }

    $tiles = $size / $tilereader->byteCount();
    $width = min($width, $tiles);

    $url = sprintf('/img/%x-%s.png', $addr, $suffix);
    $path = sprintf('%s/%s', dirname(__DIR__), $url);
    if (!file_exists($path)) {
        $ext = '';
        if ($tilereader instanceof TileReader1Bpp) {
            $ext = '.1bpp';
        } else if ($tilereader instanceof TileReader4Bpp) {
            $palette = array_slice($palette, 0, min(16, count($palette)));
            $ext = '.4bpp';
        } else if ($tilereader instanceof TileReader8Bpp) {
            $ext = '.8bpp';
        }

        $tempPath = sys_get_temp_dir() . '/diasm' . $ext;
        file_put_contents($tempPath, $data);

        $gbagfx = '/Users/Marijn/Projects/pokeruby/tools/gbagfx/gbagfx';

        $cmd = escapeshellcmd($gbagfx) . ' ' . escapeshellarg($tempPath) . ' ' . escapeshellarg($path);


        if (count($palette) !== 0) {
            $palPath = sys_get_temp_dir() . '/diasm.gbapal';
            $fh = fopen($palPath, 'w+');
            foreach ($palette as $color) {
                fwrite($fh, pack('v', $color));
            }
            fclose($fh);

            $cmd .= ' -palette ' . escapeshellarg($palPath);
        }

        $cmd .= ' -width ' . $width;

        exec($cmd);
    }

    return new HtmlElement('img', ['src' => $url]);
}

function _dumpCompressedImage_old($addr, $data, $suffix, TileReaderInterface $tilereader, $width = 16, $palette = [])
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
        if (count($palette) === 0) {
            $tilereader->defaultPalette($img);
        } else {
            $first = true;

            if ($tilereader instanceof TileReader4Bpp) {
                $palette = array_slice($palette, 0, min(16, count($palette)));
            }

            foreach ($palette as $color) {
                imagecolorallocatealpha($img, $color[0], $color[1], $color[2], $first ? 127 : 0);
                $first = false;
            }
        }
        //$errorColor = imagecolorallocate($img, 255, 0, 0);


        for ($y = 0; $y < $dy; $y += 8) {
            for ($x = 0; $x < $dx; $x += 8) {
                if (!$br->isEof()) {
                    $tilereader->readTile($br, $img, $x, $y);
                } else {
                    //imagefilledrectangle($img, $x, $y, $x + 7, $y + 7, $errorColor);
                    echo '<li>Error while drawing ' . $path . '</li>';
                }
            }
        }

        imagepng($img, $path);
        imagedestroy($img);

        if ($tilereader instanceof TileReader4Bpp) {
            $imagick = new Imagick($path);
            $imagick->setImageDepth(4);
            $imagick->writeImage($path);
            $imagick->destroy();
        }
    }

    return new HtmlElement('img', ['src' => $url]);
}


function dumpCompressedPalette($addr, $data)
{
    $size = strlen($data);
    if ($size % 32 !== 0) {
        return null;
    }

    if ($size > 16 * 16 * 2) {
        return null;
    }


    $url = sprintf('/img/%x-pal-lz.png', $addr);
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

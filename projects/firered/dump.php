<?php

define('FR_ADDR_MAP_GROUPS', 0x83526A8);

use MarijnvdWerf\DisAsm\Dumper\RomMap as RomMap2;
use PhpBinaryReader\BinaryReader;

$codes = [];
$data = json_decode(file_get_contents(ROOT . '/new/script.json'));
foreach ($data as $type => $cmd) {
    //printf("%02X %s\n", (int)$type, $cmd->name);

    if (!isset($cmd->param_types)) {
        $cmd->param_types = [];
    }
    $codes[(int)$type] = $cmd;
}

$fh = fopen(ROOT . '/out/map_constants.inc', 'w+');

$lastGroup = -1;

fwrite($fh, "    .set cur_map_group, -1\n");
foreach ($mapNames as $offset => $name) {
    list($group, $map2) = explode('.', $offset);

    if ($group !== $lastGroup) {
        fwrite($fh, "\n    new_map_group\n");
    }
    $lastGroup = $group;
    fprintf($fh, "    map_group %s    @ %d.%d\n", str_pad($name, 32, ' ', STR_PAD_RIGHT), $group, $map2);
}

fclose($fh);

$rom->setPosition(FR_ADDR_MAP_GROUPS);
$map->register(FR_ADDR_MAP_GROUPS, 'kMapGroups', 'MapGroups');

$map->register(0x82D5AE0, 'PokemonCenter_2F_MapAttributes', 'MapAttributes', 'PokemonCenter_2F');
$map->register(0x82D5990, 'PokemonCenter_1F_MapAttributes', 'MapAttributes', 'PokemonCenter_1F');
$map->register(0x82D5BCC, 'PokeMart_MapAttributes', 'MapAttributes', 'PokeMart');
$map->register(0x8343FB8, 'Sevii_Harbor_MapAttributes', 'MapAttributes', 'Sevii_Harbor');
$map->register(0x827B014, 'kTilesetTiles_Uncompressed', 'Gfx2', 0x4F00);
$map->dump($rom, null, 0, 0);

$map->register(0x81BB8A7, 'Script2', 'EventScript');
$map->register(0x81c55c8, '', 'Text');
$map->register(0x81c55c9, 'Script3', 'Text');
$map->register(0x83DF7E8, 'kBerries', 'Berries');

$map->register(0x84145BC, 'kCredits', 'Credits');


$map->register(0x8452EB8, '', 'twoptr', 12);


$map->register(0x847A890, '', 'List', 18, '');
$map->register(0x8452CFC, '', 'List', 3, '');
$map->register(0x8452334, '', 'List', 4, '');
$map->register(0x8452344, '', 'List', 9, 'Text');
$map->register(0x845FA1C, '', 'List', 96, 'Text');
$map->register(0x845F89C, '', 'List', 96, 'Text');
$map->register(0x845F6BC, '', 'List', 96, 'Text');
$map->register(0x845F63C, '', 'List', 32, 'Text');

$map->register(0x847A860, '', 'SpriteTemplates', 2);

$map->register(0x847A79C, '', 'objtiles');
$map->register(0x847A7A4, '', 'objpals');


$map->register(0x8452004, '', 'unk1');
$map->register(0x84520BC, '', 'unk1');
$map->register(0x8452174, '', 'unk1');
$map->register(0x8456D34, '', 'unk1');
$map->register(0x8456DDC, '', 'unk1');
$map->register(0x8456E1C, '', 'unk1');
$map->register(0x8456E54, '', 'unk1');
$map->register(0x8456F04, '', 'unk1');
$map->register(0x8456F7C, '', 'unk1');

$map->register(0x845701C, '', 'unk1');
$map->register(0x8457094, '', 'List', 13, 'TextJP');
$map->register(0x845737C, '', 'List', 14, 'TextJP');
$map->register(0x845742C, '', 'List', 20, 'Text');
$map->register(0x8457754, '', 'List', 10, 'Text');

$map->register(0x8479C58, '', 'objtiles', 3);
$map->register(0x8479368, '', 'unk1');
$map->register(0x8479340, '', 'unk2');

$map->register(0x84790E8, '', 'twoptr', 4);
$map->register(0x8479108, '', 'twoptr', 5);
$map->register(0x8479130, '', 'twoptr', 7);
$map->register(0x8479168, '', 'twoptr', 6);


$map->register(0x8479060, '', 'List', 4, '');
$map->register(0x84790D8, '', 'List', 4, '');


$map->register(0x8479198, '', 'List', 4, '');

$map->registerDumper('unk3', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    for ($i = 0; $i < $arguments[0]; $i++) {

        $addr = $rom->readUint32();
        $count = $rom->readUint32();

        fprintf($out, "    .4byte %s, %d\n", $map->register($addr, '', 'borg', $count), $count);
    }
});

$map->register(0x8452C4C, '', 'unk3', 9);

$map->register(0x84520F4, '', 'ImgPal', 15, 'Gfx', 'Pal');
$map->register(0x8452254, '', 'unk12', 7);

$map->register(0x8478EC4, '', 'twoptr', 10);

$map->register(0x8478F1C, '', 'List', 57, 'Fn');


$map->register(0x845A574, '', 'objtiles');
$map->register(0x845A57C, '', 'objpals');

$map->register(0x8479688, '', 'LzGfx');
$map->register(0x83CD3A0, '', 'LzGfx');
$map->register(0x8441808, '', 'LzGfx');
$map->register(0x8459EC4, '', 'ptr');
$map->register(0x8459EE4, '', 'ptr');


$map->register(0x845F60C, '', 'List', 4, 'Text');
$map->register(0x845FBDC, '', 'objpals', 3);

$map->register(0x845FCB8, '', 'SpriteTemplate');

$map->register(0x8462EB4, '', 'List', 3, '');

$map->register(0x8462EF0, '', 'List', 3, 'Text');


$map->register(0x8462EFC, '', 'objtiles', 4);
$map->register(0x8462F1C, '', 'objpals', 2);

$map->register(0x8462F50, '', 'SpriteTemplates', 3);
$map->register(0x846302C, '', 'SpriteTemplates', 3);

$map->register(0x8463074, '', 'List', 6, 'Text');
$map->register(0x846308C, '', 'List', 19, 'Text');
$map->register(0x84630D8, '', 'List', 19, 'Text');
$map->register(0x8463124, '', 'List', 4, 'Text');

$map->register(0x8463140, '', 'List', 4, 'Fn');

$map->register(0x8463150, '', 'twoptr', 3);
$map->register(0x8478E18, '', 'objpals');
$map->register(0x8478DCC, '', 'objpals');

$map->register(0x8478D90, '', 'objpals', 3);

$map->register(0x8478CE0, '', 'AnimCmds');

$map->register(0x84755E8, '', 'List', 4, 'Text');
$map->register(0x8475648, '', 'List', 5, 'Text');

$map->register(0x8478E80, '', 'List', 5, '');
$map->register(0x8478E94, '', 'List', 5, 'Text');
$map->register(0x8463EC4, '', 'List', 4, 'Text');
$map->register(0x8463ED4, '', 'List', 7, 'Text');
//die();

function mkScript(RomMap2 $map, $offset)
{
    return $map->register($offset, sprintf('EventScript_%X', $offset), 'EventScript');
}

function mkString(RomMap2 $map, $offset)
{
    return $map->register($offset, sprintf('Text_%X', $offset), 'Text');
}

mkString($map, 0x81C55C9);

mkScript($map, 0x8161F2E);
mkScript($map, 0x8162AC6);
mkScript($map, 0x81646F6);
mkScript($map, 0x8164724);
mkScript($map, 0x8164724);
mkScript($map, 0x8164752);
mkScript($map, 0x8164780);
mkScript($map, 0x81647AE);
mkScript($map, 0x81647DC);
mkScript($map, 0x816480A);
mkScript($map, 0x8164833);
mkScript($map, 0x81646FC);
mkScript($map, 0x816472A);
mkScript($map, 0x8164758);
mkScript($map, 0x8164758);
mkScript($map, 0x8164786);
mkScript($map, 0x81647B4);
mkScript($map, 0x81647E2);
mkScript($map, 0x81647E2);
mkScript($map, 0x8164810);
mkScript($map, 0x8166B88);
mkScript($map, 0x8160516);
mkScript($map, 0x8162AD1);
mkScript($map, 0x8164702);
mkScript($map, 0x8164730);
mkScript($map, 0x816475E);
mkScript($map, 0x816478C);
mkScript($map, 0x81647BA);
mkScript($map, 0x81647E8);
mkScript($map, 0x8164816);
mkScript($map, 0x81BB968);
mkScript($map, 0x81A80FE);
mkScript($map, 0x81A749F);

mkScript($map, 0x8164708);
mkScript($map, 0x8164736);
mkScript($map, 0x8164764);
mkScript($map, 0x8164792);
mkScript($map, 0x81647C0);
mkScript($map, 0x81647EE);
mkScript($map, 0x816481C);
mkScript($map, 0x8165BF6);
mkScript($map, 0x816793C);
mkScript($map, 0x816A308);
mkScript($map, 0x816A7CA);
mkScript($map, 0x816AA8E);
mkScript($map, 0x816B137);
mkScript($map, 0x816B46C);
mkScript($map, 0x816C634);
mkScript($map, 0x816D739);
mkScript($map, 0x816E9FD);
mkScript($map, 0x816EB24);
mkScript($map, 0x816F04C);
mkScript($map, 0x816F8ED);
mkScript($map, 0x816FD0F);
mkScript($map, 0x8170BD1);
mkScript($map, 0x81717F3);
mkScript($map, 0x817187A);
mkScript($map, 0x8171D30);
mkScript($map, 0x8171E35);
mkScript($map, 0x816470E);
mkScript($map, 0x816473C);
mkScript($map, 0x8164798);
mkScript($map, 0x81647C6);
mkScript($map, 0x81647F4);
mkScript($map, 0x8164822);
mkScript($map, 0x8165C19);
mkScript($map, 0x816A30E);
mkScript($map, 0x816A7D0);
mkScript($map, 0x816AA94);
mkScript($map, 0x816B13D);
mkScript($map, 0x816B472);
mkScript($map, 0x816C63A);
mkScript($map, 0x816D73F);
mkScript($map, 0x816EA03);
mkScript($map, 0x816EB2A);
mkScript($map, 0x816F052);
mkScript($map, 0x816F8F3);
mkScript($map, 0x816FD15);
mkScript($map, 0x8170BD7);
mkScript($map, 0x81717F9);
mkScript($map, 0x8171880);
mkScript($map, 0x816A314);
mkScript($map, 0x816A7D6);
mkScript($map, 0x816AA9A);
mkScript($map, 0x816EB2A);
mkScript($map, 0x8171D36);
mkScript($map, 0x8171E3B);
mkScript($map, 0x81A74AB);
mkScript($map, 0x81A74AB);
mkScript($map, 0x81BB974);
mkScript($map, 0x816B143);
mkScript($map, 0x816B478);
mkScript($map, 0x816C640);
mkScript($map, 0x816D745);
mkScript($map, 0x816EA09);
mkScript($map, 0x816EB30);
mkScript($map, 0x816F058);
mkScript($map, 0x816F8F9);
mkScript($map, 0x816FD1B);
mkScript($map, 0x8170BDD);
mkScript($map, 0x81717FF);
mkScript($map, 0x8171886);
mkScript($map, 0x816476A);
mkScript($map, 0x816BAF5);
mkScript($map, 0x816FBB6);
mkScript($map, 0x8171349);
mkScript($map, 0x8171D3C);
mkScript($map, 0x8171E41);
mkScript($map, 0x81A74F2);
mkScript($map, 0x81BB621);
mkScript($map, 0x81C4E8F);
mkScript($map, 0x816FBC1);
mkScript($map, 0x817134F);
mkScript($map, 0x81C5552);


$text = <<<END
 0x08e69ebc                kUnkGfx_8E69EBC
                0x08e6c6bc                kUnkGfx_8E6C6BC
                0x08e6eebc                kUnkGfx_8E6EEBC
                0x08e70ebc                kUnkGfx_8E70EBC
                0x08e72ebc                kUnkGfx_8E72EBC
                0x08e74ebc                kUnkGfx_8E74EBC
                0x08e76ebc                kUnkPal_8E76EBC
                0x08e76ee4                kUnkPal_8E76EE4
                0x08e76f0c                kUnkPal_8E76F0C
                0x08e76f34                kUnkPal_8E76F34
                0x08e76f5c                kUnkGfx_8E76F5C
                0x08e7735c                kMonFootprint_OldUnownZ
                0x08e7737c                kUnkGfx_8E7737C
                0x08e77464                kUnkBin_8E77464
                0x08e77570                kUnkPal_8E77570
                0x08e77598                kUnkGfx_8E77598
                0x08e777a8                kUnkPal_8E777A8
                0x08e777e4                kUnkGfx_8E777E4
                0x08e77d90                kUnkPal_8E77D90
                0x08e77dcc                kUnkBin_8E77DCC
                0x08e7807c                kUnkGfx_8E7807C
                0x08e78684                kUnkBin_8E78684
                0x08e78934                kUnkPal_8E78934
                0x08e78974                kUnkPal_8E78974
                0x08e789b0                kUnkPal_8E789B0
                0x08e78a08                kUnkPal_8E78A08
                0x08e78a44                kUnkPal_8E78A44
                0x08e78a80                kUnkPal_8E78A80
                0x08e78ae0                kUnkPal_8E78AE0
                0x08e78b4c                kUnkPal_8E78B4C
                0x08e78b9c                kUnkPal_8E78B9C
                0x08e78be4                kUnkPal_8E78BE4
                0x08e78c28                kUnkPal_8E78C28
                0x08e78c78                kUnkPal_8E78C78
                0x08e78cb4                kUnkGfx_8E78CB4
                0x08e790c4                kUnkBin_8E790C4
END;

$text = explode("\n", $text);
foreach ($text as $line) {
    $line = trim($line);
    $line = preg_split('/\s+/', $line);
    $map->register(intval($line[0], 16), $line[1]);
}

$map->register(0x82350AC, 'kMonFrontPicTable', 'objtiles', 440);
$map->register(0x823654C, 'kMonBackPicTable', 'objtiles', 440);
$map->register(0x823730C, 'kMonPaletteTable', 'objpals', 440);
$map->register(0x82380CC, 'kMonShinyPaletteTable', 'objpals', 440);
$map->register(0x823957C, 'kTrainerFrontPicTable', 'objtiles', 148);
$map->register(0x8239A1C, 'kTrainerFrontPicPaletteTable', 'objpals', 148);


$map->register(0x83D37A0, 'kMonIconTable', 'List', 440, '');

$map->register(0x8248318, '', 'objtiles');
$map->register(0x8250A0C, '', 'objtiles');
$map->register(0x826046C, '', 'objtiles', 2);
$map->register(0x826047C, '', 'objpals', 2);
$map->register(0x83AD510, '', 'objpals', 289);
$map->register(0x83ACC08, '', 'objtiles', 289);


$map->register(0x83ADE18, '', 'ImgPalMap', 27);
$map->register(0x83D4294, '', 'twoptr', 752 / 2);

$map->register(0x8171C32, 'Unk_8171C32', 'EventScript');
$map->register(0x8171C38, 'Unk_8171C38', 'EventScript');
$map->register(0x8171C3E, 'Unk_8171C3E', 'EventScript');
$map->register(0x835B764, '', 'List', 22 * 2, 'Fn');
$map->register(0x843E9E8, '', 'EnvTable');
$map->register(0x83E04B0, '', 'Menus');
$map->register(0x835B5D8, 'kDoorAnimGraphicsTable', 'Doors');
$map->register(0x82528BC, 'kItemEffectTable', 'List', 163, 'ItemEffect');
$map->register(0x83D2A10, '', 'ImgMapPal2', 16, '');
$map->register(0x825062C, '', 'List', 39, 'MoveScript');
$map->register(0x83D353C, '', 'List', 38, 'Text');
$map->register(0x83FEA28, '', 'List', 18, 'Text');
$map->register(0x83A7344, '', 'twoptr', 9);
$map->register(0x83A7660, '', 'GfxList', 5, 0x80);
$map->register(0x83E267C, '', 'twoptr', 13);
$map->register(0x83E26E4, '', 'twoptr', 9);
$map->register(0x83A5208, '', 'twoptr', 4, '', '');
$map->register(0x082500CC, '', 'twoptr', 7, '', 'Text');
$map->register(0x825F7FC, '', 'twoptr', 3, 'Text', '');

$map->register(0x825F82C, '', 'List', 4, 'Text');

$map->register(0x083A719C, '', 'List', 5, '');
$map->register(0x83A71EC, '', 'List', 3, '');

$map->register(0x83AA654, '', 'GfxList', 8, 0x600);
$map->register(0x83AB874, '', 'GfxList', 8, 0x240);
$map->register(0x83ABDB4, '', 'GfxList', 5, 0x100);
$map->register(0x83AC1E8, '', 'GfxList', 4, 0x100);
$map->register(0x83AC5F8, '', 'GfxList', 4, 0x100);
$map->register(0x83AC7C8, '', 'GfxList', 2, 0xE0);
$map->register(0x83AC950, '', 'GfxList', 4, 0x80);
$map->register(0x83BF554, '', 'SubspriteTable');
$map->register(0x82603FC, '', 'SubspriteTable');
$map->register(0x8260404, '', 'SubspriteTable');
$map->register(0x82603A4, '', 'SubspriteTable', 4);
$map->register(0x82603C4, '', 'SubspriteTable', 2);

$map->register(0x83CBF0C, '', 'SubspriteTable');
$map->register(0x83CBF24, '', 'SubspriteTable');

$map->register(0x83F1C50, '', 'AnimCmds');
$map->register(0x83E7D24, '', 'struct_6');


$map->register(0x83E2504, '', 'SubspriteTable');
$map->register(0x83E250C, '', 'SubspriteTable', 3);
$map->register(0x083E2524, '', 'SubspriteTable');
$map->register(0x83E252C, '', 'SubspriteTable');
$map->register(0x83E7470, '', 'SubspriteTable');

$map->register(0x8261C58, '', 'objtiles');
$map->register(0x8261C60, '', 'objpals');

$map->register(0x826011C, '', 'objtiles');
$map->register(0x8260124, '', 'objtiles');
$map->register(0x826012C, '', 'objpals');
$map->register(0x8261D00, '', 'objpals');
$map->register(0x83BFB9C, '', 'objtiles', 4);
$map->register(0x83BFBBC, '', 'objpals', 3);
$map->register(0x83C65D4, '', 'objtiles');
$map->register(0x83C66CC, '', 'objtiles');
$map->register(0x83C67A8, '', 'objtiles');
$map->register(0x83C67B0, '', 'objtiles');
$map->register(0x83C67E8, '', 'objtiles');
$map->register(0x83C6854, '', 'objtiles');
$map->register(0x83C6870, '', 'objtiles');


$map->register(0x83C6790, '', 'SpriteTemplate');
$map->register(0x83C2BA4, '', 'LzBinList', 6);
$map->register(0x83C6AB8, '', 'List', 4, 'Text');

$map->register(0x83E6278, '', 'SpriteTemplate');
$map->register(0x83E5DDC, '', 'AffineAnimCmds');
$map->register(0x83E3AFC, '', 'AnimCmds');


$map->register(0x83E3B7C, '', 'SpriteTemplate');

$map->register(0x83F5738, '', 'light_level_transition_table');
$map->register(0x82606F4, 'kBallTemplates', 'SpriteTemplates', 12);
$map->register(0x840C0A4, 'kBallParticleTemplates', 'SpriteTemplates', 12);
$map->register(0x825DEF0, '', 'SpriteTemplates', 4);
$map->register(0x825DF50, '', 'SpriteTemplates', 6);
$map->register(0x83C7388, '', 'SpriteTemplate');
$map->register(0x826CDE4, '', 'SpriteTemplate');

$map->register(0x83E3ADC, '', 'SpriteTemplate');
$map->register(0x83E3B00, '', 'SpriteTemplates', 3);
$map->register(0x083E3264, '', 'SpriteTemplate');
$map->register(0x83E333C, '', 'SpriteTemplate');
$map->register(0x083E3194, '', 'SpriteTemplates', 2);
$map->register(0x83FA608, '', 'SpriteTemplates', 2);
$map->register(0x82602F8, '', 'SpriteTemplates', 4);
$map->register(0x82604BC, '', 'SpriteTemplates', 4);


$map->register(0x83A39F0, '', 'MapObjectSubspriteTables');
$map->register(0x083A3B80, '', 'MapObjectSubspriteTables');
$map->register(0x083A3850, '', 'MapObjectSubspriteTables');
$map->register(0x83A3890, '', 'MapObjectSubspriteTables');


$map->register(0x83A5208, '', 'twoptr', 4, '', '');
$map->register(0x83A5278, '', 'twoptr', 13, '', '');
$map->register(0x83EDEC0, '', 'WordLists', 27);
$map->register(0x843F27C, '', 'objpals', 1);
$map->register(0x841EED4, '', 'objpals', 2);
$map->register(0x841EEC4, '', 'objtiles', 2);
$map->register(0x8410CDC, '', 'ImgMapPal', 2, '');
$map->register(0x083A5330, '', 'List', 4, '');


$map->register(0x83CBE9C, '', 'objpals', 2);


$map->register(0x840BBC0, '', 'objtiles', 5);
$map->register(0x840BBE8, '', 'objpals', 4);

$map->register(0x8402228, '', 'twoptr', 4);
$map->register(0x8402208, '', 'twoptr', 3);
$map->register(0x84021E8, '', 'twoptr', 3);
$map->register(0x84021DC, '', 'List', 3, 'Text');
$map->register(0x83FFA94, '', 'twoptr', 1);


$map->register(0x83FF9F4, '', 'objtiles', 1);
$map->register(0x83FF9FC, '', 'objpals', 1);


$map->register(0x843F2AC, '', 'Messages');
$map->register(0x83F1B3C, '', 'LocationDescription');

$map->register(0x83A3698, '', 'npc_looping_info');

$fnlists = <<<FN
83CBE30 8
83FA368 18
83FA3B0 4
083FA3C0 3
083FA3CC 2
83C7294 4
83C2BC0 60
83C2CB0 4
83FED00 57
84020F8 57
83FB134 57
843ED88 3
8452F34 7
8452F50 2
8452F58 2
835B814 3
835B828 5
835B844 8
0835B864 4
835B890 4
835B8A0 3
835B8AC 1
835B8B0 4
835B8CC 16
847A230 21
835B95C 3
83A702C 6
083A705C 4
083A70BC 20
83E240C 5
83E2420 2
083E23E0 2
83E23D0 4
83E2394 5
083E2378 4
083E2354 4
083A710C 16
083A714C 8
083A716C 6
083A7184 4
083A7194 2
083A6018 7
083A6034 7
83A6054 11
83A6080 5
83A6094 7
83A60B4 7
83A60D4 3
83A60E0 5
83A60F4 5
83A6108 5
83A6120 5
83A6138 5
83A6150 5
83A6168 5
83A6180 5
83A6198 5
83A6404 2
83A63FC 2
83A63F0 3
83A63E4 3
83A63DC 2
83A63D4 2
83A63CC 2
83A63C8 1
83A63BC 3
83A6390 11
83A64A8 4
83A64B8 4
83A6384 3
83A6374 3
83A6364 3
83A6354 3
83A6344 3
83A6334 3
83A6324 3
83A6314 3
83A6304 3
83A62F4 3
83A62E4 3
83A62D4 3
83A62C4 3
83A62B4 3
83A62A4 3
83A6294 3
0x83A6284 3
0x83A6274 3
0x83A6268 3
0x83A6258 3
0x83A6248 3
0x83A6238 3
0x83A6228 3
0x83A6218 3
0x83A6208 3
0x83A61F8 4
0x84755A8 12
0x8475578 12
8471EDC 9
83A61B0 5
83A61C8 4
83A61E0 4
83F55A4 94
83A7310 8
83C7258 15
83C7248 4
83CBFD0 4
83CBFE0 4
83CBFF0 8
83CC034 7
83CC050 6
83CC068 7
83CC084 5
83CC098 3
83CC0A4 6
83CC0Bc 4
83CC0CC 5
83CC0E0 2
83CC0F0 2
83CC0F8 4
83CC110 3
83CC11C 7
83CC138 7
83CC154 5
83CC168 4
83CC178 9
83CC1D4 7
840C074 12
81E9F28 14
8250038 14
8250070 9
83DF0B4 2
83E7CD4 10
083E7CFC 5
083E7D10 3
83FA46C 10
83E2354 4
83FA4C4 7
83FA3D4 2
83FA3DC 6
83FA3F4 3
83FA414 7
83FA430 2
83FA438 3
83FA514 3
83FECE8 3
83FECE0 2
83FA520 5
083FA4E8 8
83FA464 2
83A709C 3
83CC244 3
08456948 43
84569F4 43
83CD908 6
83D346C 3
83E2954 2
845A880 10
846E34C 26
8463170 2
8464358 5
846436C 2
8464374 2
84658F0 16
8466F60 8
08467030 5
846B4AC 4
846B64C 9
846B670 9
FN;

$fnlists = explode("\n", $fnlists);
foreach ($fnlists as $line) {
    $line = explode(" ", $line);
    $map->register(intval($line[0], 16), '', 'List', intval($line[1]), 'Fn');
}


$map->register(0x846D8FC, '', 'twoptr', 10);
$map->register(0x83CC314, '', 'List', 7, 'Text');
$map->register(0x83E2280, '', 'List', 4, 'Text');
$map->register(0x846E328, '', 'List', 9, 'Text');
$map->register(0x826CEAC, '', 'AnimCmds', 1);

$map->register(0x0846FAAC, '', 'List', 5, 'Text');
$map->register(0x846FA9C, '', 'List', 4, 'Text');

$map->register(0x846F4B8, '', 'List', 6, 'Text');

$map->register(0x84639A4, '', 'AnimCmds');
$map->register(0x84639F4, '', 'AnimCmds');
$map->register(0x8463A7C, '', 'AnimCmds');
$map->register(0x8463AFC, '', 'AnimCmds');
$map->register(0x8463B40, '', 'AnimCmds');;
$map->register(0x84657E0, '', 'AffineAnimCmds');


$map->register(0x84642BC, '', 'borg', 7);
$map->register(0x8466DD0, '', 'borg', 3, 'Text');

$map->register(0x8466E78, '', 'unk1');
$map->register(0x8466EA8, '', 'unk1');
$map->register(0x8466E90, '', 'unk1');
$map->register(0x8466EC0, '', 'unk1');
$map->register(0x8466DE8, '', 'borg', 3, 'Text');

$map->register(0x8466E04, '', 'ptr');

$map->register(0x826CEB0, '', 'objtiles');

$map->register(0x843F274, '', 'objtiles');

$map->register(0x8467F58, '', 'objtiles');
$map->register(0x8467F60, '', 'objpals', 8);
$map->register(0x846AF78, '', 'objtiles', 3);
$map->register(0x846AF90, '', 'objpals', 2);
$map->register(0x846B42C, '', 'objtiles', 2);
$map->register(0x846B43C, '', 'objpals', 2);
$map->register(0x846D960, '', 'objtiles');
$map->register(0x846D968, '', 'objpals');
$map->register(0x846E0B0, '', 'objtiles', 5);
$map->register(0x846E0D8, '', 'objpals', 2);

$map->register(0x846E2D4, '', 'List', 3, 'Text');

$map->register(0x84795C0, '', 'SubspriteTable');

$map->register(0x846D9A8, '', 'List', 4, '');
$map->register(0x846D9D4, '', 'List', 4, '');

$map->register(0x8458758, '', 'List', 8, 'Text');
$map->register(0x84591B8, '', 'List', 9, 'Text');


$map->register(0x8466ED8, '', 'List', 4, 'Text');
$map->register(0x84644A8, '', 'objtiles');
$map->register(0x84644B0, '', 'objpals');
$map->register(0x84647FC, '', 'objtiles');
$map->register(0x826CF28, '', 'objtiles');


$map->register(0x845AF80, '', 'objpals');
$map->register(0x826CE7C, '', 'objtiles');

$map->register(0x84655B0, '', 'objtiles', 3);
$map->register(0x846F2F8, '', 'objtiles', 5);
$map->register(0x846F320, '', 'objpals', 4);
$map->register(0x8459588, '', 'Text');
$map->register(0x84595B0, '', 'Text');

$map->register(0x845ABEC, '', 'twoptr', 5, 'Text');

$map->register(0x845FB9C, '', 'objtiles', 8);

$map->register(0x8463564, '', 'List', 7, '');
$map->register(0x8463E60, '', 'List', 25, 'Text');

$map->register(0x84655C8, '', 'objpals', 8);
$map->register(0x8466C0C, '', 'twoptr', 5, '', '');

$map->register(0x84687A0, '', 'Text');
$map->register(0x8468720, '', 'unk13', 8);


// TODO: Figure out where these pointers come from
$ptrs = [0x84595F4, 0x845960C, 0x8468928, 0x08468958, 0x8468988, 0x84689B8, 0x84689E8, 0x8468A30, 0x8468A3C, 0x8468A6C,
    0x8468ACC, 0x8468B08, 0x8468B14, 0x8468B44,
    0x8468AFC,
];

$map->register(0x8488E28, '', 'ereader');
$map->register(0x8488EB5, '', 'ereader');
$map->register(0x8488EBD, '', 'ereader');
$map->register(0x84892B9, '', 'ereader');
$map->register(0x848903A, '', 'ereader');
$map->register(0x8489862, '', 'ereader');
$map->register(0x84894B9, '', 'ereader');
$map->register(0x8489689, '', 'ereader');


$map->register(0x8489C24, '', 'ptr', 1, 'VoiceGroup');

$map->register(0x8457838, '', 'List', 7, 'Text');

$map->register(0x845B9E0, '', 'List', 36 * 5, '');

$map->register(0x8467FB8, '', 'ImgMapPal3', 8, '');

$map->register(0x84644B8, '', 'SpriteTemplate');
$map->register(0x84647E4, '', 'SpriteTemplate');

$map->register(0x8468B6C, '', 'mevent', 8);
$map->register(0x8468BCC, '', 'mevent', 12);


$map->register(0x846437C, '', 'twoptr', 5, 'Text', '');

$map->register(0x8463FFC, '', 'List', 3, '');
$map->register(0x845A72C, '', 'List', 13, '');

$map->register(0x8457898, '', 'List', 2, 'Text');

$map->register(0x845AB64, '', 'struct_20', 11);
$map->register(0x8471E8C, '', 'unk14', 10);

$map->register(0x846F488, '', 'struct_19', 3);


$map->register(0x84A3054, '', 'VoiceGroup');
$map->register(0x84A3078, '', 'KeySplitTable');


/*
 * 	.byte \byte1
	.2byte \word1
	.byte \byte2, \byte3, \byte4, \byte5, \byte6, \byte7, \byte8, \byte9, \byte10, \byte11, \byte12, \byte13, \byte14
	.4byte \script
	.2byte \word2
	.byte \byte15, \byte16

 */

$rom->setPosition(0x8160450);
for ($i = 0; $i < 10; $i++) {
    $map->register($rom->readUInt32(), 'StdScript_' . $i, 'EventScript');
}


$usedData = json_decode(file_get_contents($container['basepath'] . '/data.json'), true);
foreach ($usedData as $addr => $uages) {
    if ($addr < 0x8160478) {
        continue;
    }

    // battle_anim_scripts (gUnknown_081C7160)
    if ($addr >= 0x81C68EC) {
        continue;
    }

    //$map->register($addr, 'UnknownItem_' . $addr, '');
}

$map->register(0x83528F4, '', 'MapConnections');

$map->register(0x8245EE0, 'kSpeciesNames', 'StringList', 412, 11);
$map->register(0x824FC40, 'kAbilityNames', 'StringList', 78, 13);
$map->register(0x8247094, 'kMoveNames', 'StringList', 355, 13);
$map->register(0x824F1A0, 'kTypeNames', 'StringList', 18, 7);

$map->register(0x823E558, 'kTrainerClassNames', 'StringList', 107, 13);
$map->register(0x83F847D, '', 'u8', 800, 16);
$map->register(0x83E22D0, '', 'u8', 6, 8);
$strings = [
    0x841E2EC,
    0x841E2EC,
    0x841E322,
    0x8444C34,
    0x84181E4,
    0x84181BE,
    0x84181B6,
    0x841E52D,
    0x841CD25,
    0x841CD25,
    0x8416202,
    0x841620C,
    0x841620E,
    0x8416210,
    0x8416213,
    0x8416217,
    0x841621F,
    0x8416221,
    0x8416223,
    0x8416226,
    0x8416238,
    0x841623B,
    0x83D94C1,
    0x83D9518,
    0x83D9576,
    0x83D95C7,
    0x83D962B,
    0x83D9687,
    0x83D96E1,
    0x83D9730,
    0x83D978D,
    0x83D97DE,
    0x83D9823,
    0x83D987A,
    0x83D98DC,
    0x83D9930,
    0x83D9998,
    0x83D99F2,
    0x83D9A51,
    0x83D9A9F,
    0x83D9AEF,
    0x83D9B58,
    0x83D9BB0,
    0x83D9BFE,
    0x83D9C3F,
    0x83D9C8C,
    0x83D9CD9,
    0x83D9D3C,
    0x83D9D9C,
    0x83D9DED,
    0x83D9E50,
    0x83D9EAC,
    0x83D9F0F,
    0x83D9F60,
    0x83D9FB4,
    0x83DA007,
    0x83DA06B,
    0x83DA0BE,
    0x83DA111,
    0x83DA159,
    0x83DA1B6,
    0x83DA20F,
    0x83DA262,
    0x83DA2C4,
    0x83DA31E,
    0x83FE9A9,
    0x83FE9AC,
    0x83FE9AF,
    0x83FE9B2,
    0x83FE9B5,
    0x83FE9B8,
    0x83FE9BB,
    0x83FE9BE,
    0x83FD81A,
    0x83FE81C,
    0x83FE98D,
    0x83FE9A9,
    0x8415FC8,
    0x8415FCF,
    0x841E76B,
    0x8415A6E,
    0x8415A77,
    0x8415A97,
    0x83DA369,
    0x83DA3BD,
    0x83DA416,
    0x83DA477,
    0x83DA4DC,
    0x83DA545,
    0x83DA5AB,
    0x83DA608,
    0x83DA66D,
    0x83DA6CD,
    0x83DA71E,
    0x83DA76C,
    0x8417693,
    0x8417926,
    0x84183F0,
    0x8418408,
    0x8418433,
    0x8418419,
    0x8418443,
    0x8418452,
    0x8418690,
    0x84186B0,
    0x84186CD,
    0x8418937,
    0x8418956,
    0x84189E0,
    0x84189EE,
    0x841AAEC,
    0x841AFA6,
    0x841AE8F,
    0x841B03F,
    0x841CBFD,
    0x841CC7B,
    0x841CD9F,
    0x841D078,
    0x841DF82,
    0x841DFA5,
    0x841DFAC,
    0x841DFBE,
    0x841DFC9,
    0x841E093,
    0x841E09F,
    0x841E0A5,
    0x841E200,
    0x841E1E9,
    0x841E20D,
    0x841E21E,
    0x841E234,
    0x841E325,
    0x841E3E3,
    0x841E3FB,
    0x841E405,
    0x841E414,
    0x841E493,
    0x841E481,
    0x841E4C0,
    0x841E4E2,
    0x841E50C,
    0x841E538,
    0x841E572,
    0x841E58D,
    0x841E5A4,
    0x841E5B9,
    0x841E5D2,
    0x841E6A1,
    0x841E6DC,
    0x841E717,
    0x841E741,
    0x841E7D1,
    0x841E7BC,
    0x841E7A3,
    0x841E794,
    0x841E7F2,
    0x841E823,
    0x841E866,
    0x841E88F,
    0x841E8BD,
    0x841E8E2,
    0x841E90C,
    0x841E92B,
    0x841E946,
    0x841E968,
    0x841E98F,
    0x841E9AB,
    0x841E9D3,
    0x841EA0D,
    0x841EA3F,
    0x841EA86,
    0x841EA6F,
    0x83D9473,

    0x84895E8,
    0x83D942C,
    0x8416BC3,
    0x8416BFB,
    0x8416C2A,
    0x8416C49,
    0x8416C8F,
    0x8416CAC,
    0x8416CC7,
    0x8416CEA,
    0x8416D17,
    0x8416D4F,
    0x8416D78,
    0x8416DB3,
    0x8416DC2,
    0x8416DF7,
    0x8416E6B,
    0x8416E84,
    0x8416EA4,
    0x8416EC6,
    0x8416F10,
    0x8416F27,
    0x8416F4E,
    0x8416F6F,
    0x8416F8C,
    0x8416F9A,
    0x8416FB2,
    0x8416FC7,
    0x8416FED,
    0x8417002,
    0x8417017,
    0x8417032,
    0x8417052,
    0x8417075,
    0x8417457,
    0x8417494,
    0x8417615,
    0x8417640,
    0x8417674,
    0x841767B,
    0x841768D,
    0x8417696,
    0x84176B8,
    0x84176CF,
    0x8417774,
    0x84178D0,
    0x84178DA,
    0x8417920,
    0x841778A,
    0x84177AC,
    0x84177C5,
    0x84177EE,
    0x8417806,
    0x8417830,
    0x8417858,
    0x841786B,
    0x84178A7,
    0x84178BE,
    0x8417B9F,
    0x8417BAC,
    0x8417BB6,
    0x8417BBE,
    0x8417BCB,
    0x8417BD3,
    0x8417DED,
    0x8417FBB,
    0x8417FC3,
    0x8417FCC,
    0x8417FD0,
    0x8418075,
    0x841825C,
    0x841826C,
    0x841827F,
    0x8418295,
    0x84182A7,
    0x84182B8,
    0x84182CE,
    0x84182DF,
    0x84182EC,
    0x84182FF,
    0x8418319,
    0x841832C,
    0x8418346,
    0x841835F,
    0x8418379,
    0x8418392,
    0x84183A0,
    0x84183BA,
    0x84183C5,
    0x84183DD,
    0x8418C1B,
    0x8418C83,
    0x8418CD9,
    0x8418E09,
    0x8418E47,
    0x8418E5C,
    0x8418E52,
    0x8418E69,
    0x8418E77,
    0x8418E8D,
    0x8418E95,
    0x8418E9E,
    0x8418EA7,
    0x8418EB0,
    0x8418EB5,
    0x8418EBC,
    0x8418EC3,
    0x841A64F,
    0x841A694,
    0x841A66E,
    0x841A6A5,
    0x841A6E1,
    0x841A732,
    0x841A76A,
    0x841A7B0,
    0x841A7DD,
    0x841A810,
    0x841A858,
    0x841A896,
    0x841A8D4,
    0x841A938,
    0x841A965,
    0x841A9A9,
    0x841A9D4,
    0x841AA01,
    0x841AA2B,
    0x841AA76,
    0x841AAAA,
    0x841ED2F,
    0x841ECF9,
    0x841ECD3,
    0x841EC99,
    0x841EC6A,
    0x841EC40,
    0x841EC12,
    0x841EBDE,
    0x841EBAA,
    0x841EB8E,
    0x841EB71,
    0x841EB46,
    0x841EB20,
    0x841EB01,
    0x841EAE7,
    0x841EAB7,
    0x841D050,
    0x841D058,
    0x841D0A8,
    0x841D0C0,
    0x841D13C,
    0x841D148,
    0x841AB29,
    0x841AB74,
    0x841AB8E,
    0x841C693,
    0x841C587,
    0x841AF0C,
    0x841AF3E,
    0x841AF6D,
    0x841B295,
    0x841CBA9,
    0x841CD58,
    0x841CD43,
    0x841CE1C,
    0x841CE24,
    0x841CC11,
    0x841CD7A,
    0x841CDBA,
    0x841CDEB,
    0x841CDD7,
    0x841CC42,
    0x841CC64,
    0x841CD03,
    0x841B073,
    0x841B285,
    0x841B064,
    0x841B306,
    0x841B315,
    0x841B31B,
    0x841B329,
    0x841B349,
    0x841B554,
    0x841B5B6,
    0x841B619,
    0x841B60E,
    0x841B83D,

    0x84162A9,
    0x84162B9,
    0x8416301,
    0x841630F,
    0x841632A,
    0x841633F,
    0x841635E,
    0x8416374,
    0x841638F,
    0x84163A7,
    0x84163BB,
    0x84163DB,
    0x84163F4,
    0x8416409,
    0x8416425,
    0x8416451,
    0x8416476,
    0x84164BE,
    0x8416513,
    0x8416537,
    0x841658C,
    0x841659E,
    0x84165D2,
    0x8416600,
    0x8416631,
    0x8416644,
    0x8416655,
    0x841665C,
    0x8416690,
    0x84166A7,
    0x84166D3,
    0x84166DB,
    0x84166E1,
    0x841670A,
    0x8416716,
    0x8416749,
    0x8416757,
    0x8416766,
    0x841678E,
    0x84167E7,
    0x8416842,
    0x8416861,
    0x841689E,
    0x84168F1,
    0x8416911,
    0x8416959,
    0x8416936,
    0x841697A,
    0x84169C2,
    0x84169C5,
    0x84169CD,
    0x84169D5,
    0x84169DC,
    0x84169F8,
    0x8416A1E,
    0x8416A3A,
    0x8416A55,
    0x8416A75,
    0x8416A98,
    0x8416ACB,
    0x8416AE2,
    0x8416BA6,
    0x8416B16,
    0x8416B3E,
    0x8416B64,
    0x8416B86,
    0x8418174,
    0x8418188,
    0x84181A4,
    0x84181C3,
    0x8418208,
    0x841821B,
    0x8418233,
    0x8418248,
    0x8419782,
    0x841979D,
    0x84197B8,
    0x84197ED,
    0x8419822,
    0x8419841,
    0x8419860,
    0x841988A,
    0x84198B4,
    0x84198D5,
    0x841992F,
    0x841996D,
    0x84199AB,
    0x84199F4,
    0x8419C0B,
    0x8419C13,
    0x8419C1D,
    0x8419C2A,
    0x8419C39,
    0x8419C45,
    0x8419C4D,
    0x8419C59,
    0x8419C62,
    0x8419C72,
    0x8419C7B,
    0x8419C82,
    0x8419C92,
    0x8419CA2,
    0x8419CA9,
    0x8419CB9,
    0x8419CDA,
    0x8419CE1,
    0x8419CE7,
    0x8419CED,
    0x8419CEF,
    0x8419CF7,
    0x8419CF8,
    0x8419CFD,
    0x8419D0A,
    0x8419D1A,
    0x8419D4F,
    0x8419D57,
    0x8419D66,
    0x8419D7D,
    0x8419D89,
    0x8419DCC,
    0x8419E52,
    0x8419E57,
    0x841A155,
    0x8419F54,
    0x841A16F,
    0x841A193,
    0x841A1CD,
    0x841A1E7,
    0x841A210,
    0x841A220,
    0x841A255,
    0x841A277,
    0x841A2B0,
    0x841A2E1,
    0x841D068,
    0x841A312,
    0x841A349,
    0x841D074,
    0x841A391,
    0x841D080,
    0x841A3DA,
    0x841D088,
    0x841A3FF,
    0x841A422,
    0x841D090,
    0x841D0A4,
    0x841A477,
    0x841A4C6,
    0x841A50B,
    0x841A566,
    0x841A59C,
    0x841A5D9,
    0x841A5FA,
    0x841D169,
    0x841D098,
    0x841DE9D,
    0x841DEF0,
    0x841DF05,
    0x841EE2B,
    0x841DF4C,
    0x841EDBD,
    0x841EDCA,
    0x841A60A,
    0x841ED50,
    0x841ED7B,
    0x841ED9C,
    0x841A632,
    0x841DF6B,
    0x841B510,
    0x841B3AA,
    0x841B3BE,
    0x841B32E,
    0x841B54B,
    0x841B541,
    0x841B535,
    0x841B516,
    0x841B684,
    0x841B68F,
    0x841B698,
    0x841B69E,
    0x841B747,
    0x841B76B,
    0x841CB3C,
    0x841CB41,
    0x841CBCA,
    0x841CBE4,
    0x841CB49,
    0x841CB52,
    0x841CB5A,
    0x841CB63,
    0x841B6B9,
    0x841B6D5,
    0x841B6DC,
    0x841B6E3,
    0x841B6EC,
    0x841B6FD,
    0x841B716,
    0x841B531,
    0x841B52B,
    0x841B524,
    0x841B51E,
    0x83D93C9,
    0x83FCA2C,
    0x83FCCE4,
    0x83FCCF8,
    0x83FCD0F,
    0x83FCD27,
    0x83FCD41,
    0x83FCD66,
    0x83FCD92,
    0x83FCD9F,
    0x83FD284,
    0x83FD297,
    0x83FD2AA,
    0x83FD2BF,
    0x83FD2D9,
    0x83FD30D,
    0x83FD366,
    0x83FD383,
    0x83FD397,
    0x83FD3B1,
    0x83FD3C7,
    0x83FD3E4,
    0x83FD3F7,
    0x83FD407,
    0x83FD41E,
    0x83FD43E,
    0x83FD44E,
    0x83FD45E,
    0x83FD466,
    0x83FD475,
    0x83FD47D,
    0x83FD488,
    0x83FD497,
    0x83FD4B5,
    0x83FD4CD,
    0x83FD4EB,
    0x83FD4FA,
    0x83FD50D,
    0x83FD522,
    0x83FD535,
    0x83FD545,
    0x83FD555,
    0x83FD55B,
    0x83FD560,
    0x83FD564,
    0x83FD569,
    0x83FD56D,
    0x83FD572,
    0x83FD576,
    0x83FD57B,
    0x83FD824,
    0x83FD8A2,
    0x83FD8AF,
    0x83FDAE2,
    0x83FDB92,
    0x83FDBEF,
    0x83FDC58,
    0x83FDC95,
    0x83FDCD2,
    0x83FDD23,
    0x83FE672,
    0x83FE688,
    0x83FE6B5,
    0x83FE6D0,
    0x83FE6D5,
    0x83FE6E6,
    0x83FE6FA,
    0x83FE714,
    0x83FE725,
    0x83FE747,
    0x83FE766,
    0x83FE76A,
    0x83FE770,
    0x83FE791,
    0x83FE7A0,
    0x83FE80C,
    0x83FE868,
    0x83FE874,
    0x83FE87B,
    0x83FE883,
    0x83FE982,
    0x83FE998,
    0x83FE9E4,
    0x83FE9FF,
    0x8415AA4,
    0x8415ACB,
    0x8415BFF,
    0x8415C42,
    0x8415C64,
    0x8415CE8,
    0x8415D2C,
    0x8415D48,
    0x8415D50,
    0x8415D60,
    0x8415D78,
    0x8415D8C,
    0x8415D93,
    0x8415D97,
    0x8415D9C,
    0x8415F8F,
    0x8415F98,
    0x8415F9B,
    0x8415FA0,
    0x8415FAD,
    0x8415FB3,
    0x8415FE8,
    0x8415FED,
    0x8415FF2,
    0x8416002,
    0x8416008,
    0x8416090,
    0x84160C8,
    0x84160B4,
    0x84160EC,
    0x84160F4,
    0x84160FC,
    0x8416104,
    0x841614B,
    0x841617A,
    0x8416181,
    0x8416188,
    0x8416190,
    0x84161EF,
    0x841D198,
    0x841623D,
    0x84594C4,
    0x8459504,
    0x8416262,
    0x84162BD,
    0x84162C4,
    0x84162F5,
    0x84162E8,
    0x84162DE,
    0x84162D3,
    0x84162CD,
    0x8415DB8,
    0x8415DC4,
    0x8415DCA,
    0x8415DD1,
    0x8415DD7,
    0x8415F3D,
    0x8415F4A,
    0x8415F51,
    0x8415F6C,
    0x83FE88F,
    0x8489887,
    0x84897EE,
    0x84897C1,
    0x848975C,
    0x84896FF,
    0x8489615,
    0x8489583,
    0x8489526,
    0x8489419,
    0x8489301,
    0x84891B0,
    0x8489063,
    0x8488FE3,
    0x8488F56,
    0x8488E55,
    0x8488DFD,
    0x8488DBD,
    0x8488D8E,
    0x8488D7C,
    0x8488D60,
    0x8488D2A,
    0x8488CF6,
    0x8488CCE,
    0x8488CA2,
    0x8488C70,
    0x8459378,
    0x845928C,
    0x8459250,
    0x8459238,
    0x84591DC,
    0x8458FE4,
    0x8458FC8,
    0x8458FBC,
    0x8458F9C,
    0x8458F04,
    0x8458ED0,
    0x8458E70,
    0x8458E10,
    0x8458DE8,
    0x8458DBC,
    0x8458D9C,
    0x8458D78,
    0x8458D54,
    0x8458D1C,
    0x8458CD4,
    0x8458B44,
    0x8458AB8,
    0x8458A98,
    0x84584C0,
    0x845847C,
    0x8458434,
    0x8457F90,
    0x8457E60,
    0x8457E44,
    0x8457E28,
    0x8457E0C,
    0x8457DB8,
    0x84578BC,
    0x8457854,
    0x84577BC,
    0x845777C,
    0x845771C,
    0x8457700,
    0x84576C4,
    0x84576AC,
    0x8457610,
    0x8457554,
    0x8457530,
    0x8457514,
    0x84574EC,
    0x84574C4,
    0x84574A0,
    0x845747C,
    0x8457264,
    0x8457234,
    0x84571E0,
    0x84571B8,

    0x8415A23,
    0x8415A21,
    0x8415A22,
    0x8415A31,
    0x8415A2C,
    0x8415A3C,
    0x8415A43,
    0x8415A49,
    0x8415A50,
    0x8415A58,
    0x8415A5C,
    0x8415A62,
    0x8415A36,
    0x841D118,
    0x841D124,
    0x841D14E,
    0x841D1B6,
    0x841D17E,
    0x841D18D,
    0x841D198,
    0x81A508A,
    0x81A5476,
    0x81A6D17,
    0x81A6D6D,
    0x81A6DDF,
    0x81A6E36,
    0x81A6EA4,
    0x81A6F0B,
    0x81A6F71,
    0x81A6FAB,
    0x81A6FF1,
    0x81A7031,
    0x81A7063,
    0x81A70A5,
    0x81A70D8,
    0x81A7108,
    0x81A7137,
    0x81A7175,
    0x81B2E6F,
    0x81B2FC9,
    0x81BC4CE,
    0x81BC50D,
    0x81BC54C,
    0x81C137C,
    0x81C13D6,
    0x81C1429,
    0x81C55C9,
    0x81C55EA,
    0x81C5625,
    0x81C5647,
    0x81C566A,
    0x81C574F,
    0x81C582D,
    0x81C59D5,
    0x81C5C78,
    0x81C5D06,
    0x81C5D12,
    0x81C5D4B,
    0x81C5DBD,
    0x81C5DEA,
    0x81C5E13,
    0x81C5E2E,
    0x81C5E91,
    0x81C5EB5,
    0x81C5EC5,
    0x81C5EF4,
    0x81B2DF8,
    0x81A5028,
    0x817732B,
    0x81A5103,
    0x81A51A3,
    0x81A5690,
    0x81A51D0,
    0x81A5133,
    0x81B2E1C,
    0x81BC35E,
    0x81C5758,
    0x81C575E,
    0x81C5762,
    0x81C5767,
    0x81C5875,
    0x81C576C,
    0x81C5771,
    0x81C58BA,
    0x81C5775,
    0x81C577A,
    0x81C5A04,
    0x81C58F9,
    0x81C577E,
    0x81C592B,
    0x81C5AEB,
    0x81C594F,
    0x81C5BB9,
    0x81C5783,
    0x81C5788,
    0x81C578C,
    0x81C5792,
    0x81C5797,
    0x81C579D,
    0x81C57A2,
    0x81C57A9,
    0x81C57AF,
    0x81C57B4,
    0x81C57B8,
    0x81C57BC,
    0x81C57C6,
    0x81C57D0,
    0x81C57D9,
    0x81C57E8,
    0x81C57F4,
    0x81C57FF,
    0x81C580A,
    0x81C5981,
    0x81C5814,
    0x81C581F,
    0x81C5828,
    0x81C57C2,
    0x81C57CB,
    0x81C57D4,
    0x81C57E0,
    0x81C57EE,
    0x81C57FA,
    0x81C5806,
    0x81C580F,
    0x81C5819,
    0x81C5823,
    0x81BC388,
    0x81BC3C7,
    0x81B3083,
    0x81B2E2E,
    0x81B30A9,
    0x81B2E48,
    0x81B2E58,
    0x81B30C1,
    0x81B30DC,
    0x81B2E6A,
    0x81B30FC,
    0x81A505B,
    0x81A5160,
    0x81A5446,
    0x81AF567,
    0x81C5F69,
    0x81C5FA7,
    0x81C601C,
    0x81C615A,
    0x81C61EA,
    0x81C6301,
    0x81C63F9,
    0x81C657A,
    0x81C6645,
    0x81C6787,
    0x81C686C,
    0x81955C7,
    0x81C684B,
    0x81C66CF,
    0x81C6637,
    0x81C6446,
    0x81C63A9,
    0x81C6202,
    0x81C6196,
    0x81C60FA,
    0x81C5FDC,
    0x81AF641,
    0x81AF6BA,
    0x81AF758,
    0x81AF83E,
    0x81AF7CB,
    0x81BCA95,
    0x81BCACB,
    0x81BCAF2,
    0x81BCB42,
    0x841B779,
    0x8246be5,
    0x83F8445,
    0x83F844F,
    0x83F8453,
    0x83F845D,
    0x8417FB9,
    0x84176CE,
    0x84169D9,
    0x84169D8,
    0x8416703,
    0x84166FF,
    0x84162FF,
    0x841622C,
    0x8416206,
    0x8416707,
    0x841B293,
    0x841B2A7,
    0x83FE7B6,
    0x83FE826,
    0x83FE88B,
    0x83FE9D4,
    0x8415F66,
    0x8418E8A,
    0x8418E8B,
    0x8415A8A,
    0x841E23A,
    0x841E23B,
    0x841E23C,
    0x841E23D,
    0x841E23E,
    0x841E23F,
    0x841E240,
    0x841E241,
    0x841E242,
    0x841E243,
    0x841E244,
    0x8415F9E,
    0x841E097,
    0x841E098,
    0x841E09B,
    0x841E0A4,
    0x841CB4E,
    0x841B6E1,
    0x841B53B,
    0x841B2E5,
    0x841B2DC,
    0x841A8DD,
];

$stringsJP = [
    0x43EDF4,
    0x418E3D,
    0x419CFA,
    0x41B3DA,
    0x41DE7E,
    0x41DE55,
    0x841E2F5,
    0x418204,
    0x3FD78A,
    0x415FA5,
    0x3FE7BF,
    0x3FE7C2,
    0x3FE7C5,
    0x418777,
    0x416233,
    0x841622F,
    0x418562,
    0x415A80,
    0x45721C,
    0x488DE1,
    0x457485,
    0x4592C8,
    0x459213,
    0x458EB0,
    0x458E51,
    0x458E9A,
    0x458E35,
    0x458CB4,
    0x458C80,
    0x41B79F,
    0x417FB2,
    0x417FB6,
    0x415CE0,
    0x418DC9,
    0x419913,
    0x418D99,
    0x419EC3,
    0x419F2D,
    0x419F10,
    0x419EF9,
    0x419EEA,
    0x418C12,
    0x419EB0,
    0x419EA0,
    0x418BF3,
    0x418BDA,
    0x418BC0,
    0x418B99,
    0x418B5C,
    0x418B77,
    0x415D22,
    0x415D1A,
    0x457309,
    0x4573E9,
    0x416146,
    0x416133,
    0x416565,
    0x4189BB,
    0x4189CB,
    0x4189AC,
    0x418998,
    0x419E90,
    0x419D02,
    0x4198F6,
    0x418E36,
    0x418D70,
    0x418B25,
    0x418B40,
    0x418C6F,
    0x418C62,
    0x418B1A,
    0x8418AF6,
    0x8418B02,
    0x8418B0D,
    0x418C55,
    0x418B17,
    0x8418C4F,
    0x8418C4A,
    0x8416067,
    0x8415FFF,
    0x8415CF6,
    0x8415D12,
    0x8415D0A,
    0x8415D05,
    0x8415D00,
    0x8415CFB,
    0x8415CD9,
    0x8415BAA,
    0x8415B44,
    0x8415B33,
    0x8418C45,
    0x8418AEB,
    0x8418A1D,
    0x8418982,
    0x8418925,
    0x841889D,
    0x8418A0C,
    0x8418915,
    0x8418970,
    0x8418C3F,
    0x8418ADF,
    0x8418C39,
    0x8418AD4,
    0x8418AAB,
    0x8418AC0,
    0x8418C2A,
    0x8418ACA,
    0x8262034,
    0x824F008,
    0x8231C48,
    0x8416822,
    0x84167D0,
    0x8416556,
    0x8416025,
    0x841610A,
    0x8416195,
    0x84161B7,
    0x8415D35,
    0x8415CED,
    0x8415C8F,
    0x8415B0E,
    0x83FE870,
    0x83FE779,
    0x841B798,
    0x841B3D0,
    0x8419E72,
    0x841ACF9,
    0x8419E42,
    0x8417FA9,
    0x8417933,
    0x8417688,
    0x84168D7,
    0x8416822,
    0x84167D0,
    0x8416556,
    0x84161CE,
    0x8416548,
    0x84167BB,
    0x8416800,
    0x8416888,
    0x84168BF,
    0x841699C,
    0x8416FDA,
    0x84175C1,
    0x8417683,
    0x84176EB,
    0x8417721,
    0x841775D,
    0x84178DF,
    0x841792E,
    0x841793F,
    0x8417A30,
    0x8417E68,
    0x8417E68,
    0x8417F9F,
    0x841E2DF,
    0x83F8440,
    0x825F83C,
    0x8416289,
    0x8415EBC,
    0x83BF52C,
    0x81C556D,
    0x81BD274,
    0x81BD0BC,
    0x81BCCBE,
    0x81BCB73,
    0x81BCB73,
    0x81BCB62,
    0x81BCB27,
    0x8197096,
    0x817313D,
    0x817552A,
    0x817642B,
    0x8176437,
    0x81767FC,
    0x81775B4,
    0x81775CD,
    0x81775DC,
    0x8178EC3,
    0x8179DA2,
    0x817A797,
    0x817A7E5,
    0x817A7F7,
    0x817A811,
    0x817CC69,
    0x817E842,
    0x817E87E,
    0x817E8B4,
    0x817EDF8,
    0x8180945,
    0x8181A3A,
    0x818849E,
    0x81884B3,
    0x81884B8,
    0x81A5351,
    0x81A53B2,
    0x81A5435,
    0x818A9A9,
    0x818AB73,
    0x818C384,
    0x81970FE,
    0x819710E,
    0x81906A6,
    0x8192987,
    0x8193E82,
    0x81952FB,
    0x81965A7,
    0x8197B6F,
    0x819912B,
    0x819A324,
    0x819A3C2,
    0x819AF1A,
    0x819AF26,
    0x819AFB0,
    0x819B123,
    0x819B4C7,
    0x819E01E,
    0x819E02A,
    0x819E890,
    0x81A3612,
    0x81A3778,
    0x81A37DB,
    0x81A37E8,
    0x81A37FB,
    0x81A3C71,
    0x81A55EA,
    0x81A56D2,
    0x81A5606,
    0x81A5C79,
    0x81A5C9F,
    0x81A5CCE,
    0x81A5CC3,
    0x81A5E05,
    0x81A63D6,
    0x81A6407,
    0x81A72A6,
    0x81B2E76,
    0x81BC403,
    0x81BC572,
    0x81BC906,
    0x81BCA7F,
    0x81BCA86,
    0x81A5CD3,
    0x81BF76B,
    0x81BFA28,
    0x81BFC9D,
    0x81BFECC,
    0x81BFD30,
    0x81BFD67,
    0x81BFF51,
    0x81BFDD7,
    0x81BFE0F,
    0x81BFE35,
    0x81BFFA1,
    0x81C3287,
    0x81C565A,
    0x81749F3,
    0x8188A07,
    0x8188BAF,
    0x818C39E,
    0x81BFD52,
    0x81BFE28,
    0x81BFE28,
    0x81BFE47,
    0x81BFF30,
    0x81BFF66,
    0x81BFFCE,
    0x81BFFFD,
    0x81C0DD4,
    0x81BFE58,
    0x81BFE70,
    0x81C003F,
    0x81C0079,
    0x81C00B6,
    0x81C00EF,
    0x81C011B,
    0x81C0159,
    0x81C0190,
    0x81C01B4,
    0x81C01FB,
    0x81C0243,
    0x81C0283,
    0x81C02CB,
    0x81C0309,
    0x81C0317,
    0x81C032B,
    0x81C034D,
    0x81C036C,
    0x81C0399,
    0x81C03B5,
    0x81C03D7,
    0x81C0407,
    0x81C0426,
    0x81C049D,
    0x81C04BB,
    0x81C04C9,
    0x81C04DC,
    0x81C0500,
    0x81C0523,
    0x81C054C,
    0x81C05A8,
    0x81C05ED,
    0x81C0629,
    0x81C064A,
    0x81C0662,
    0x81C069C,
    0x81C06A6,
    0x81C06DE,
    0x81C071B,
    0x81C073B,
    0x81C075F,
    0x81C0782,
    0x81C0799,
    0x81C07DF,
    0x81C07FB,
    0x81C0825,
    0x81C0888,
    0x81C089C,
    0x81C08D5,
    0x81C08FD,
    0x81C0948,
    0x81C0974,
    0x81C09A4,
    0x81C09DA,
    0x81C09DF,
    0x81C0A07,
    0x81C0A1A,
    0x81C0A4E,
    0x81C0B0B,
    0x81C0B29,
    0x81C0B73,
    0x81C0BE5,
    0x81C0C12,
    0x81C0C74,
    0x81C0CF5,
    0x81C0D16,
    0x81C0D32,
    0x81C0D66,
    0x81C0D8F,
    0x81ACD45,
    0x81C558D,
    0x81C55A4,
];

$rom->setPosition(0x845F63C);
for ($i = 0; $i < 32; $i++) {
    mkString($map, $rom->readUInt32());
}

$rom->setPosition(0x845F6BC);
for ($i = 0; $i < 96; $i++) {
    mkString($map, $rom->readUInt32());
}

$stringreader = new StringReader();
$rom->setPosition(0x845F89C);
for ($i = 0; $i < 96; $i++) {
    $offset = $rom->readUInt32();
    mkString($map, $offset);
    /*   $pos = $rom->getPosition();

       $rom->setPosition($offset);

       $lines = $stringreader->readLines($rom);
       printf("%d\t%s\n", $i, $lines[0]);

       $rom->setPosition($pos);*/
}
for ($i = 0; $i < 96; $i++) {
    mkString($map, $rom->readUInt32());
}

foreach ($strings as $addr) {
    mkString($map, $addr);
}

foreach ($stringsJP as $addr) {
    if ($addr < 0x8000000) {
        $addr += 0x8000000;
    }
    $map->register($addr, sprintf('TextJP_%x', $addr), 'TextJP');
}

$scripts = [
    0x816CA70,
    0x816CA4C,
    0x816C9C8,
    0x8165E5E,
    0x81BB459,
    0x81ACDB5,
    0x81ACD8D,
    0x81C1338,
    0x8171355,
    0x81BE420,
    0x81BE3D4,
    0x81BE38B,
    0x816AC62,
    0x8168D17,
    0x81A4EB4,
    0x81A4EC1,
    0x81A4EE9,
    0x81A4F21,
    0x81A4F3E,
    0x81A4F73,
    0x81A6481,
    0x81A654B,
    0x81A6843,
    0x81A6955,
    0x81A6AC8,
    0x81A6B0D,
    0x81A6C32,
    0x81A7606,
    0x81A760F,
    0x81A7618,
    0x81A7621,
    0x81A762A,
    0x81A7633,
    0x81A763C,
    0x81A7645,
    0x81A764E,
    0x81A7657,
    0x81A7660,
    0x81A7669,
    0x81A7672,
    0x81A767B,
    0x81A7684,
    0x81A768D,
    0x81A7696,
    0x81A769F,
    0x81A76A8,
    0x81A76B1,
    0x81A76BA,
    0x81A76C3,
    0x81A76CC,
    0x81A76D5,
    0x81A76DE,
    0x81A76E7,
    0x81A76F0,
    0x81A76F9,
    0x81A7702,
    0x81A77A0,
    0x81A7ADB,
    0x81A8CED,
    0x81A8D49,
    0x81A8D97,
    0x81A8DD8,
    0x81A8DFD,
    0x81BB8A7,
    0x81BB981,
    0x81BB992,
    0x81BB9A3,
    0x81BB9D4,
    0x81BB9F0,
    0x81BB9FC,
    0x81BBFD8,
    0x81BDF6B,
    0x81BE064,
    0x81BE16E,
    0x81BE2B7,
    0x81BE2FF,
    0x81BE564,
    0x81BF546,
    0x81BFB5F,
    0x81BFB65,
    0x81BFB87,
    0x81BFBAA,
    0x81BFBC5,
    0x81BFBD7,
    0x81C1361,
    0x81C549C,
    0x81C555B,
    0x81A6C05,
    0x81C5564,
    0x8160BA0,
    0x8165A5B,
    0x8165A65,
    0x8166DFE,
    0x8171BBF,
    0x81A6C0E,
    0x81A6C18,
    0x81A754B,
    0x81BB1E4,
    0x81AD008,
    0x81AD021,
    0x81A8E6F,
    0x81A7493,
];

foreach ($scripts as $addr) {
    mkScript($map, $addr);
}

$img8cppLz = [
    0x826701C
];

$rom->setPosition(0x845B098);
for ($i = 0; $i < 370; $i++) {
    mkString($map, $rom->readUInt32());
}

$map->register(0x83F5BA6, '', 'u16', 9, 3);
$map->register(0x83F5B94, '', 'u16', 9, 3);
$map->register(0x826D29E, '', 'u16', 8); // flags
$map->register(0x825E014, '', 'u16', 9);
$map->register(0x8464890, '', 'u8', 30, 2);
$map->register(0x843F39C, '', 'u8', 28, 7);
$map->register(0x83F5BB8, '', 'u8', 9);

$map->register(0x825DFF6, 'kHappinessEffectTable', 'u8', 10 * 3, 3);
$map->register(0x825DEAD, 'kFractionsStatBuffs', 's8', 31, 2);


$map->register(0x8459B60, 'kMoveTutorMoves', 'Moves', 15);
$map->register(0x83F571C, '', 'Moves', 13);


$map->register(0x81C68F4, 'gBattleAnims_Moves', 'MoveList', $moves);
$map->register(0x81C6E84, '', 'MoveList');
$map->register(0x81C6EA8, '', 'MoveList');
$map->register(0x81C6F18, '', 'MoveList');
$map->register(0x81D4B03, '', 'MoveAnim');
$map->register(0x81D555E, '', 'MoveAnim');
$map->register(0x81C76EC, '', 'MoveAnim');
$map->register(0x81D3238, '', 'MoveAnim');
$map->register(0x81CD51C, '', 'MoveAnim');
$map->register(0x824EFC4, '', 'u8', 32, 4);
$map->register(0x82507E8, '', 'Moves', 20);

$map->register(0x8260208, '', 'objtiles', 11);
$map->register(0x8260260, '', 'objpals', 2);


$map->register(0x825E032, '', 'u16', 21);
$map->register(0x82601BC, '', 'u16', 38, 2);


$map->register(0x825DECC, '', 'u8', 36, 2);

$map->register(0x825DE24, '', 's8', 125, 5);

$map->register(0x81D65A8, 'kBattleScriptsEffectsTable', 'MoveEffects');

$map->register(0x81D96AC, 'kFieldEffectScriptPointers', 'List', 70, 'FieldEffectScript', function ($index, $address) {
    return sprintf('FieldEffectScript_%02X', $index);
});

$map->register(0x081D99B0, '', 'List', 13, 'BattleScript');
$map->register(0x81D99E4, '', 'List', 6, 'BattleScript');
$map->register(0x81D99FC, '', 'List', 2, 'BattleScript');
$map->register(0x81D9A04, '', 'List', 4, 'BattleScript');

$map->register(0x81D9BF4, 'kBattleAIs', 'List', 32, 'BattleAI', function ($index, $address) {
    return sprintf('kBattleAI_%02X', $index);
});


$map->register(0x81E9F10, 'Data');
$map->register(0x844e850, 'Data2', 'PokedexEntries');
$map->register(0x844e850, 'kPokedexEntries', 'PokedexEntries');


$map->register(0x83EE8D0, '', 'OAM');
$map->register(0x83EE958, '', 'AnimCmds', 16);
$map->register(0x83E631C, '', 'AnimCmds');
$map->register(0x83E62CC, '', 'AnimCmds');

$map->register(0x83E6D78, '', 'AffineAnimCmds', 1);

$map->register(0x83CEA88, '', 'borg', 31);
$map->register(0x83A5158, '', 'objpals', 19);

$map->register(0x83F2490, '', 'WorldMap');
$map->register(0x83F2724, '', 'WorldMap');
$map->register(0x83F29B8, '', 'WorldMap');
$map->register(0x83F2C4C, '', 'WorldMap');

$map->register(0x846B6B4, '', 's8', 144, 48);

$map->register(0x843EC8C, '', 'u16', 3);
$map->register(0x8250848, 'sPickupItems', 's16', 32, 2);
$map->register(0x843EC92, '', 'asciilist', 11, 15);
$map->register(0x843ED37, '', 'asciilist', 5, 16);
$map->register(0x825E50C, 'kCRC16Table', 'u16', 256);
$map->register(0x841EF10, '', 'u16', 12);
$map->register(0x825E2F4, 'kSineDegreeTable', 'u16', 180);
$map->register(0x825E074, 'kSineDegreeTable', 'u16', 320);
$map->register(0x82539d4, 'kSpeciesIdToCryId', 'u16', 136);
$map->register(0x83D3E80, 'kMonIconPaletteIndices', 'u8', 440);


$map->register(0x82538a8, '', 'u8', 150);
$map->register(0x825393e, '', 'u8', 150);
$map->register(0x84795C8, '', 'u8', 144, 16);
$map->register(0x825E45C, '', 'u32', 32, 1);
$map->register(0x825265C, 'kSpindaSpotGraphics', 'SpindaSpots');
$map->register(0x83CC1CC, '', 'AffineAnimCmds');
$map->register(0x83CC23C, '', 'AffineAnimCmds');

$map->register(0x82504FC, 'gAccuracyStageRatios', 'u8', 52, 4);
$map->register(0x8250530, 'gCriticalHitChance', 'u16', 5);
$map->register(0x825053C, 'gStatusFlagsForMoveEffects', 'u32', 60, 1);


$map->register(0x83E5B88, '', 'SpriteTemplate');
$map->register(0x83CC330, '', 'List', 3, 'Text');
$map->register(0x83CC33C, '', 'List', 2, 'Text');
$map->register(0x83CC344, '', 'List', 2, 'Text');
$map->register(0x83CC34C, '', 'List', 2, 'Text');
$map->register(0x83CC354, '', 'List', 3, 'Text');
$map->register(0x8479560, '', 'List', 6, 'Text');
$map->register(0x8479578, '', 'List', 6, 'Text');
$map->register(0x8457A34, '', 'List', 2, 'Text');
$map->register(0x8457A3C, '', 'List', 1, 'Text');
$map->register(0x8457B04, '', 'List', 4, 'Text');
$map->register(0x8457BCC, '', 'List', 2, 'Text');
$map->register(0x8457E78, '', 'List', 4, 'Text');
$map->register(0x84580F4, '', 'List', 8, 'Text');
$map->register(0x8458230, '', 'List', 12, 'Text');
$map->register(0x8458314, '', 'List', 2, 'Text');
$map->register(0x84583B4, '', 'List', 2, 'Text');
$map->register(0x845842C, '', 'List', 2, 'Text');
$map->register(0x8458548, '', 'List', 2, 'Text');
$map->register(0x84585E8, '', 'List', 2, 'Text');
$map->register(0x84588BC, '', 'List', 8, 'Text');
$map->register(0x84589AC, '', 'List', 4, 'Text');
$map->register(0x8458A78, '', 'List', 8, 'Text');
$map->register(0x845933C, '', 'List', 2, 'Text');
$map->register(0x84594B0, '', 'List', 5, 'Text');
$map->register(0x8459580, '', 'List', 2, 'Text');
$map->register(0x845A2E8, '', 'List', 37, 'Text');
$map->register(0x0845A37C, '', 'List', 12, 'Text');

$map->register(0x0845A37C, '', 'List', 12, 'Text');
$map->register(0x83FE57C, 'BattleTextList_83FE57C');
$map->register(0x83FE5A0, 'BattleTextList_83FE5A0');
$map->register(0x83FE5AC, 'BattleTextList_83FE5AC');
$map->register(0x83FE5BC, 'BattleTextList_83FE5BC');
$map->register(0x83FE5C0, 'BattleTextList_83FE5C0');
$map->register(0x83FE5C4, 'BattleTextList_83FE5C4');
$map->register(0x83FE5C8, 'BattleTextList_83FE5C8');
$map->register(0x83FE5CC, 'BattleTextList_83FE5CC');
$map->register(0x83FE5F2, 'BattleTextList_83FE5F2');
$map->register(0x83FE622, 'BattleTextList_83FE622');
$map->register(0x83FE628, 'BattleTextList_83FE628');
$map->register(0x83FE654, 'BattleTextList_83FE654');
$map->register(0x83FE51E, 'BattleTextList_83FE51E');
$map->register(0x83FE528, 'BattleTextList_83FE528');
$map->register(0x83FE534, 'BattleTextList_83FE534');
$map->register(0x83FE538, 'BattleTextList_83FE538');
$map->register(0x83FE53C, 'BattleTextList_83FE53C');
$map->register(0x83FE540, 'BattleTextList_83FE540');
$map->register(0x83FE546, 'BattleTextList_83FE546');
$map->register(0x83FE54C, 'BattleTextList_83FE54C');
$map->register(0x83FE558, 'BattleTextList_83FE558');
$map->register(0x83FE562, 'BattleTextList_83FE562');
$map->register(0x83FE566, 'BattleTextList_83FE566');
$map->register(0x83FE56A, 'BattleTextList_83FE56A');
$map->register(0x83FE56E, 'BattleTextList_83FE56E');
$map->register(0x83FE572, 'BattleTextList_83FE572');
$map->register(0x83FE576, 'BattleTextList_83FE576');
$map->register(0x83FE588, 'BattleTextList_83FE588');
$map->register(0x83FE590, 'BattleTextList_83FE590');
$map->register(0x83FE5B0, 'BattleTextList_83FE5B0');
$map->register(0x83FE5B4, 'BattleTextList_83FE5B4');
$map->register(0x83FE5B8, 'BattleTextList_83FE5B8');
$map->register(0x83FE5D0, 'BattleTextList_83FE5D0');
$map->register(0x83FE5D4, 'BattleTextList_83FE5D4');
$map->register(0x83FE5DC, 'BattleTextList_83FE5DC');
$map->register(0x83FE5E0, 'BattleTextList_83FE5E0');
$map->register(0x83FE5E4, 'BattleTextList_83FE5E4');
$map->register(0x83FE5EE, 'BattleTextList_83FE5EE');
$map->register(0x83FE5FA, 'BattleTextList_83FE5FA');
$map->register(0x83FE61A, 'BattleTextList_83FE61A');
$map->register(0x83FE634, 'BattleTextList_83FE634');
$map->register(0x83FE638, 'BattleTextList_83FE638');
$map->register(0x83FE63E, 'BattleTextList_83FE63E');
$map->register(0x83FE644, 'BattleTextList_83FE644');
$map->register(0x83FE64A, 'BattleTextList_83FE64A');
$map->register(0x83FE650, 'BattleTextList_83FE650');
$map->register(0x83FE65C, 'BattleTextList_83FE65C');


$map->register(0x845A618, '', 'twoptr', 30);
$map->register(0x845A788, '', 'twoptr', 12);

$map->register(0x845A7E8, '', 'List', 9, 'Text');

$map->register(0x08457B80, '', 'List', 4, 'Text');
$map->register(0x8457C20, '', 'List', 2, 'Text');
$map->register(0x8458F94, '', 'List', 2, 'Text');
$map->register(0x8459998, '', 'List', 3, 'Text');
$map->register(0x8459B48, '', 'List', 6, 'Text');


$map->register(0x8479398, '', 'List', 19, 'Fn');
$map->register(0x84793E4, '', 'List', 19, 'Fn');
$map->register(0x8479430, '', 'List', 19, 'Fn');
$map->register(0x847947C, '', 'List', 19, 'Fn');
$map->register(0x84794C8, '', 'List', 16, 'Fn');
$map->register(0x8479508, '', 'List', 16, 'Fn');
$map->register(0x8479548, '', 'List', 6, '');
$map->register(0x845A42C, '', 'objtiles');
$map->register(0x845A434, '', 'objpals');
$map->register(0x8463218, '', 'objtiles');
$map->register(0x845A4EC, '', 'objtiles');
$map->register(0x845A474, '', 'objtiles');
$map->register(0x845A47C, '', 'objpals');
$map->register(0x83D41E4, '', 'objtiles');
$map->register(0x83D41EC, '', 'objtiles');
$map->register(0x83D41F4, '', 'objpals');
$map->register(0x83D4240, '', 'objtiles');
$map->register(0x83D4248, '', 'objpals');
$map->register(0x83E23C8, '', 'objpals');
$map->register(0x8417CD1, '', 'Text');
$map->register(0x8417CE6, '', 'Text');
$map->register(0x8417CFF, '', 'Text');
$map->register(0x8417D18, '', 'Text');
$map->register(0x8417D32, '', 'Text');

//$map->register(0x825F862, '', 'bin', 0x800, '4bpp');
//0x83F87A0 => 0x580
//0x83FA140 => 0x1E0,
//    0x8443D00:	0x2C0
//8468C98:	0x520
//8470B0C:	0x260
//8471A4C:	0x120
//8471B6C:	0x280
//
//$maps = [
//8260C30:	0x800
//8261430:	0x800
//826407C:	0x800
//]

$oams = [
    0x83AC9F0,
    0x83ACA10,
    0x83ACA48,
    0x83ACA68,
    0x83ACAA8,
    0x83ACAC0,
    0x83ACAD0,
    0x83ACB08,
    0x83ACB30,
    0x83ACB40,
    0x83ACB68,
    0x83ACB90,
    0x83ACBC8,

    0x83ACBD0,
    0x83ACB98,
    0x83ACB70,
    0x83ACB80,
    0x83ACB78,
    0x83ACB10,
    0x83ACAB0,
    0x83ACA70,
    0x83ACA78,
    0x83ACA50,
    0x83ACA58,
    0x83ACBF0,
    0x83ACBF8,
    0x83ACC00,
];

foreach ($oams as $o) {
    $map->register($o, '', 'OAM');
}


$tilemaps = [
    0x845BCB0 => 0x800,
    0x83E4874 => 0x1000,
    0x826AA5C => 0x1000,
    0x8269A5C => 0x1000,
    0x826601C => 0x1000,
    0x826985C => 0x100,
    0x826995C => 0x100,
    0x826407C => 0x800,

    0x84691D8 => 0x800,
    0x84699D8 => 0x800,
    0x8260C30 => 0x800,
    0x845C600 => 0x800,
    0x83F6468 => 0x800,
    0x8261430 => 0x800,
    0x83CDAB8 => 0x800,

    0x83FA784 => [30, 20],
    0x83FAC34 => [32, 20],
    0x8260834 => [15, 17],
    0x8260A32 => [15, 17],
    0x8452D08 => [18, 12],

    0x8459FC4 => [7, 2],
    0x8459FE0 => [7, 2],

    // fonts
    0x8217818 => 0x8000,
    0x81EAF00 => 0x4000,
    0x81EF100 => 0x4000,
    0x81FB300 => 0x4000,
    0x8232598 => 0x2000,
    0x846FB0C => 0x1000,
    0x8207500 => 0x8000,
    0x81FF300 => 0x8000,
    0x8227B30 => 0x8000,
    0x821F930 => 0x8000,
    0x820F618 => 0x8000,
    0x81F3100 => 0x8000,

    0x83CB810 => 0x280,
    0x83CBB30 => 0x280,
    0x83CB5F0 => 0x200,
    0x8460D94 => [5, 16],
    0x8460E34 => [5, 16],
    0x8479590 => [8, 2],
];

$img8bpp = [
    0x826701C => 0x2840,
    0x826267C => 0x1A00,
];

$rboxtemplates = [
    0x81EA144
];


$map->register(0x83CE778, '', 'u16', 9 * 4, 9);
$map->register(0x83CE6F8, '', 'u16', 8 * 4, 8);
$map->register(0x846F294, '', 'u8', 12 * 5, 12);

$map->register(0x83FEC94, '', 'u16', 28, 2);
$map->register(0x83D29D0, '', 'u16', 16 * 2, 2);
$map->register(0x83C6878, '', 'u16', 13 * 2, 2);
$map->register(0x84720FC, '', 'u8', 5 * 11, 11);

$map->register(0x8456C17, '', 'u8', 8);
$map->register(0x8456BE4, 'kLocationActivityMap', 'u8', 51);
$map->register(0x8456AA0, '', 'u8', 43);

$map->register(0x845C594, 'kMapsPokeMarts', 'u8', 20 * 2, 2);
$map->register(0x845C5BC, 'kMapsGyms', 'u8', 9 * 2, 2);
$map->register(0x845C5CE, 'kMapsCavesAndForests', 'u8', 3 * 16, 3);
$map->register(0x83CC1F0, '', 'u16', 0x24 / 2);

$map->register(0x83E3898, '', 'u16', 24); // FIXME: palette tags

$map->register(0x840C1C4, '', 'u16', 12); // FIXME: colors
$map->register(0x83FF564, '', 'u16', 24); // FIXME: colors
$map->register(0x83E30F0, '', 'u16', 6); // FIXME: colors
$map->register(0x84406C8, '', 'u16', 12); // FIXME: colors
$map->register(0x83F5844, '', 'Pal', 0x10);
$map->register(0x83F5854, '', 'Pal', 0x10);

$map->register(0x8463FDF, '', 'u8', 11);
$map->register(0x8463FD4, '', 'u8', 11);
$map->register(0x8463FEA, '', 'u8', 18);
$map->register(0x8463FCD, '', 'u8', 7);
$map->register(0x8463FC6, '', 'u8', 7);
$map->register(0x8463FC1, '', 'u8', 5);
$map->register(0x8463FBE, '', 'u8', 3);
$map->register(0x8463FB8, '', 'u8', 6);

$map->register(0x8463238, '', 'u16', 18);
$map->register(0x84020E6, '', 'u16', 9);
$map->register(0x84020DA, '', 'u16', 8);
$map->register(0x84020C4, '', 'u16', 11);


$map->register(0x83F5844, '', 'Pal');
$map->register(0x83F5854, '', 'Pal');
$oams = [
    0x8463B30,
    0x8463AEC,
    0x8463A14,
    0x84639AC,
    0x846398C,
];

foreach ($oams as $ptr) {
    $map->register($ptr, '', 'OAM');
}


$args = [
    0x83E3E60,
    0x83E3E80,
    0x83E3F1C,
    0x83E4128,
    0x83E4200,
    0x83E4410,
    0x83E700C,
    0x83E702C,
    0x83FF080,
    0x83FF130,
    0x83FF198,
    0x83FF1C0,
    0x83FF210,
    0x83FF290,
    0x83FF3D0,
    0x83FF52C,
    0x83FF65C,
    0x83FF7A8,
];
foreach ($args as $o) {
    $map->register($o, '', 'sub_80762D0arg');
}

$windowTemplateLists = [
    0x82345F0,
    0x8234618,
    0x8248330,
    0x82601A4,
    0x8261F2C,
    0x826D1BC,
    0x83A73BC,
    0x83CC2B8,
    0x83CD898,
    0x83CEA30,
    0x83EE9A4,
    0x83F1A60,
    0x83F6C68,
    0x83FFA44,
    0x840BBA8,
    0x840C5B0,
    0x840C5F0,
    0x840C5D0,
    0x840C610,
    0x8415A10,
    0x841EE68,
    0x841F42C,
    0x843F8B0,
    0x8451ECC,
    0x84530C4,
    0x84530E4,
    0x8453F98,
    0x8457198,
    0x8459988,
    0x845A010,
    0x845A050,
    0x845A090,
    0x845A0D0,
    0x845AA94,
    0x845FC04,
    0x8463190,
    0x8463F9C,
    0x84643B8,
    0x8464804,
    0x846483C,
    0x8466B20,
    0x8466D70,
    0x846FA7C,
    0x846D8E4,
    0x84792F0,
];

foreach ($rboxtemplates as $o) {
    $map->register($o, '', 'WindowTemplate');
}

foreach ($windowTemplateLists as $o) {
    $map->register($o, '', 'WindowTemplateList');
}

$map->register(0x83C6900, '', 'borg', 9);
$map->register(0x83C6948, '', 'borg', 9);
$map->register(0x83C6990, '', 'borg', 9);
$map->register(0x83C69D8, '', 'borg', 9);
$map->register(0x83C6A20, '', 'borg', 9);
$map->register(0x83C6A68, '', 'borg', 9);

$map->register(0x835B968, '', 'Pal', 0x200);
$map->register(0x835A988, 'DoorAnimTiles_23', 'Gfx2', 2 * 2 * 3 * 0x20);

$map->register(0x8479748, '', 'LzGfx');

$map->register(0x845A80C, 'kTMContainedMoves', 'Moves', 58);
$map->register(0x82345F0, '', 'WindowTemplate', 4);
$map->register(0x83FFACA, '', 'u16', 60, 10);
$img4bpp = [
    0x83558A8 => 0x4400,
    0x83990c8 => 0x40,
    0x8399108 => 0x40,
    0x8399148 => 0x40,

    0x8399288 => 0x80,
    0x8399308 => 0x80,
    0x8399708 => 0x80,
    0x839AF88 => 0x80,

    0x83E44F4 => [16 + 12, 1],
    0x835FBA8 => [4, 4],
    0x835FDA8 => [4, 4],
    0x835FFA8 => [4, 4],
    0x83601A8 => [4, 4],
    0x83603A8 => [4, 4],
    0x83605A8 => [4, 4],
    0x83607A8 => [4, 4],
    0x83609A8 => [4, 4],
    0x8360BA8 => [4, 4],
    0x8360DA8 => [4, 4],
    0x8360FA8 => [4, 4],
    0x83611A8 => [4, 4],
    0x83613A8 => [4, 4],
    0x83615A8 => [4, 4],
    0x83617A8 => [4, 4],
    0x83619A8 => [4, 4],
    0x8361BA8 => [4, 4],
    0x8361DA8 => [4, 4],

    0x8369F28 => [2, 4],
    0x836A028 => [2, 4],
    0x836A128 => [2, 4],

    0x83786A8 => [2, 4],
    0x83787A8 => [2, 4],
    0x83788A8 => [2, 4],
    0x83789A8 => [2, 4],
    0x8378AA8 => [2, 4],
    0x8378BA8 => [2, 4],

    0x8372AA8 => [2, 4],
    0x8372BA8 => [2, 4],
    0x8372CA8 => [2, 4],
    0x8372DA8 => [2, 4],
    0x8372EA8 => [2, 4],
    0x8372FA8 => [2, 4],
    0x83730A8 => [2, 4],
    0x83731A8 => [2, 4],
    0x83732A8 => [2, 4],


    0x838D5A8 => [2, 4],
    0x838D6A8 => [2, 4],
    0x838D7A8 => [2, 4],
    0x838D8A8 => [2, 4],
    0x838D9A8 => [2, 4],
    0x838DAA8 => [2, 4],
    0x838DBA8 => [2, 4],
    0x838DCA8 => [2, 4],
    0x838DDA8 => [2, 4],

    0x8264C1C => 0x200,
    0x83AC0C8 => 0x120,
    0x83ABC94 => 0x120,
    0x83AA074 => 0x5E0,
    0x8355588 => 0x320,
    0x8399C88 => 0x80,
    0x8456638 => 0x60,
    0x83D35DC => 0x80,
    0x8399388 => 0x80,
    0x8399408 => 0x80,
    0x81EA14C => 0x400,
    0x83CE2B8 => 0x180,
    0x83F8F60 => 0x1E0,
    0x83FA140 => 0x1E0,
    0x83EE028 => 0x800, // T:140
    0x83F87A0 => 0x580,
    0x8468C98 => 0x520,
    0x8471B6C => 0x280,
    0x84566A8 => 0x280,
    0x8470B0C => 0x260,
    0x841F1C8 => 0x240,

    0x826207C => [2, 24],

    0x81EA700 => [16, 4],
    0x8452F7C => [3, 2],

    0x8443D00 => [11, 2],
    0x8471A4C => 0x120,
    0x8464008 => 0x120,
    0x83F6388 => 0xC0,
    0x83CBA90 => 0x80,
];

$map->register(0x8452388, '', 'u8', 300, 30);
$map->register(0x83E272C, '', 'unk11', 16);

$pals = [
    // TODO: unsure
    0x83F7D60 => 0x6A0,
    0x83F5F50 => 0x160,

    0x83CDA98 => 0x20,
    0x83EF27C => 0x20,
    0x83EF29C => 0x20,
    0x83EF2BC => 0x20, // T:10 ?
    0x8452368 => 0x20,
    0x8459868 => 0x20, // T:10000
    0x845AA24 => 0x20,
    0x840C630 => 0x20,
    0x840C39C => 0x20,
    0x83F5804 => 0x20,
    0x83F5824 => 0x20,
    0x84636E0 => 0x20, // T:140
    0x8463720 => 0x20, // T:100
    0x8463B00 => 0x20, // T:150
    0x846B794 => 0x20,
    0x8464128 => 0x20,
    0x8463B44 => 0x20, // T:160
    0x83EE008 => 0x20, // T:140
    0x845AA44 => 0x20,

    0x83EF3A4 => 0x20,
    0x83EF2DC => 0xA0,
    0x8232578 => 0x20,
    0x8402260 => 0x20,
    0x8402630 => 0x20,
    0x8402D34 => 0x20,
    0x84048CC => 0x20,
    0x84053B4 => 0x60,
    0x8406634 => 0x20,
    0x8407430 => 0x40,
    0x83F6448 => 0x20,
    0x83F60B0 => 0x20,
    0x83F61D0 => 0x20,
    0x83E0F80 => 0x20,
    0x83E17A0 => 0x20,
    0x83CE738 => 0x20,
    0x83CE758 => 0x20,
    0x83CEA10 => 0x20,
    0x83CC2E4 => 0x20,
    0x83CD280 => 0x20,
    0x83CD2A0 => 0x20,
    0x83CD2C0 => 0x20,
    0x83CD2E0 => 0x20,
    0x83CD300 => 0x20,
    0x83CD320 => 0x20,
    0x83CD340 => 0x20,
    0x83CD360 => 0x20,
    0x83CBB10 => 0x20,
    0x83C2CE0 => 0x20,
    0x83C2D00 => 0x20,
    0x83C2D20 => 0x20,
    0x8234648 => 0x20,
    0x8234668 => 0x20,
    0x82506D0 => 0x20,
    0x8231EC4 => 0x20,
    0x8415954 => 0x40,
    0x841EE44 => 0x20,
    0x843F76C => 0x20,
    0x843F78C => 0x20,
    0x8443460 => 0x20,
    0x8453098 => 0x20,
    0x845963C => 0x20,
    0x845965C => 0x20,
    0x845AA64 => 0x20,
    0x845E540 => 0x20,
    0x845ED60 => 0x20,
    0x845DD20 => 0x20,
    0x845F580 => 0x20,
    0x8460568 => 0x80,
    0x8460ED4 => 0x40,
    0x84615FC => 0x40,
    0x8461CD4 => 0x40,
    0x84623AC => 0x40,
    0x8463308 => 0x20,
    0x846341C => 0x20,
    0x84636C0 => 0x20,
    0x8463700 => 0x20,
    0x8465930 => 0xA0,
    0x84664BC => 0x20,
    0x84664DC => 0x60,
    0x84665C0 => 0x60,
    0x8466D10 => 0x20,
    0x84691B8 => 0x20,
    0x846B7B4 => 0x20,
    0x846BBB0 => 0x20,
    0x846C8D8 => 0x20,
    0x84758A8 => 0x40,
    0x8415954 => 0x40,
    0x83EF23C => 0x20,
    0x83EF25C => 0x20,
    0x83CD380 => 0x20,
    0x84404C8 => 0x200,
    0x84406E0 => 0x200,
    0x8410E10 => 0x20,
    0x8411BF8 => 0x20,
    0x84129A0 => 0x20,
    0x8413318 => 0x20,
    0x8413854 => 0x20,
    0x8413D98 => 0x20,

    0x83F6206 => 0x160,
    0x83F60D0 => 0x100,

    0x846F4D0 => 0x200,
    0x826BB5C => 0x200,
    0x826BD5C => 0x200,
    0x826BF5C => 0x20,
    0x8471DEC => 0xA0,
];

foreach ($tilemaps as $off => $size) {
    if (is_array($size)) {
        $size = $size[0] * $size[1] * 2;
    }
    $map->register($off, '', 'bin', $size, 'TileMap');
}

foreach ($pals as $off => $size) {
    $map->register($off, '', 'bin', $size, 'Palette');
}

foreach ($img8bpp as $off => $size) {
    $map->register($off, '', 'bin', $size, 'Tiles8bpp');
}

foreach ($img4bpp as $off => $size) {
    if (is_array($size)) {
        $size = $size[0] * $size[1] * 32;
    }
    $map->register($off, '', 'bin', $size, 'Tiles4bpp');
}
$map->register(0x845C4B6, 'kHelpTopicVisibility', 'u8', 6 * 37, 6);

$map->register(0x8452c94, '', 'unk10', 24);
$map->register(0x83DFC9C, '', 'unk10', 45);
$map->register(0x83FEA70, '', 'u16', 118);
$map->register(0x8352f78, 'sTileBitAttributes', 'u8', 240);

$map->register(0x8410CF4, 'kCreditsScript', 'CreditsScript');
$map->register(0x81EA044, '', 'u8', 0x100);
$map->register(0x8231E2C, 'kDigits', 'u8', 16);
$map->register(0x8464448, '', 'u8', 12, 3);

$map->register(0x8231E3C, 'kPowersOfTen', 's32', 10, 1);
$map->register(0x8231E64, '_setBrailleFont', 'Text');
$map->register(0x8231E68, '_gotoLine2', 'Text');

$map->register(0x81EA64C, 'kDownArrowYCoords', 'u8', 0x4);
$map->register(0x8231C6D, '', 'u8', 24);
$map->register(0x8231C85, 'kCenterToCornerVecTable', 'u8', 12 * 2, 2);
$map->register(0x81EA6CC, 'kKeypadIcons', 'KeypadIcon');
$map->register(0x8464148, '', 'u16', 110, 2);
$map->register(0x8465616, '', 'u16', 96);
$map->register(0x826D1E4, '', 's8', 0x70);

$map->register(0x843EBD4, '', 's32', 25);
$map->register(0x826056C, '', 'objtiles', 12);
$map->register(0x82605CC, '', 'objpals', 12);
$map->register(0x840BF48, '', 'objtiles', 12);
$map->register(0x840BFA8, '', 'objpals', 12);

$map->register(0x83C6608, '', 's16', 78, 2);

$map->register(0x81EA650, 'kWindowVerticalScrollSpeeds', 'u8', 0x4);

$u8Arrays = [
    0x83CD960 => 0x60,
    0x83CD9C0 => 0x60,
    0x8451EE4 => 0x70,
    0x83E27AC => 7,
    0x83A6050 => 4,
    0x83A60B0 => 2,
    0x83A60D0 => 2,
    0x83A611C => 2,
    0x83A6134 => 2,
    0x83A614C => 2,
    0x83A6164 => 2,
    0x83A617C => 4,
    0x83A6194 => 4,
    0x83A61AC => 4,
    0x83A61C4 => 4,
    0x83A61D8 => 5,
    0x83A61F0 => 5,
    0x83A6214 => 4,
    0x83A6224 => 4,
    0x83A6234 => 4,
    0x83A6244 => 4,
    0x83A6254 => 4,
    0x83A6264 => 4,
    0x83A6280 => 4,
    0x83A6290 => 4,
    0x83A62A0 => 4,
    0x83A62B0 => 4,
    0x83A62C0 => 4,
    0x83A62D0 => 4,
    0x83A62E0 => 4,
    0x83A62F0 => 4,
    0x83A6300 => 4,
    0x83A6310 => 4,
    0x83A6320 => 4,
    0x83A6330 => 4,
    0x83A6340 => 4,
    0x83A6350 => 4,
    0x83A6360 => 4,
    0x83A6370 => 4,
    0x83A6380 => 4,

    0x83A64EC => 0x5,
    0x83A64F1 => 0x5,
    0x83A64F6 => 0x5,
    0x83A64FB => 0x5,
    0x83A6500 => 0x5,
    0x83A650A => 0x5,
    0x83A650F => 0x5,
    0x83A6514 => 0x5,
    0x83A6519 => 0x5,
    0x83A651E => 0x5,
    0x83A6523 => 0x5,
    0x83A6528 => 0x5,
    0x83A652D => 0x5,
    0x83A6532 => 0x5,
    0x83A6537 => 0x5,
    0x83A653C => 0x5,
    0x83A6541 => 0x5,
    0x83A6546 => 0x5,
    0x83A654B => 0x5,
    0x83A6550 => 0x5,
    0x83A6555 => 0x5,
    0x83A655A => 0x5,
    0x83A655F => 0x5,
    0x83A6564 => 0x5,
    0x83A6569 => 0x5,
    0x83A656E => 0x5,
    0x83A6573 => 0x5,
    0x83A6578 => 0x5,
    0x83A657D => 0x5,
    0x83A6582 => 0x5,
    0x83A6587 => 0x5,
    0x83A658C => 0x5,
    0x843F004 => 0x100,
    0x843F104 => 0x100,
    0x845A8AC => 0x100,
    0x8478F14 => 5,

    0x839FD0C => 0x51,
    0x839FD5D => 0x53,
    0x83AC970 => 0x20,

    0x84827AC => 0x8,
];

foreach ($u8Arrays as $offset => $c) {
    $map->register($offset, '', 'u8', $c);
}
$map->register(0x847A30E, '', 'u8', 24, 3);
$map->register(0x83A6591, '', 'u8', 8);
$map->register(0x83A6599, '', 'u8', 16, 4);
$map->register(0x83A65A9, '', 'u8', 16, 4);
$map->register(0x83AC990, '', 'u16', 28, 2);

//0x8478EA8 8
$map->register(0x8478EB0, '', 'u8', 10, 2);
$map->register(0x8478EBA, '', 'u8', 10, 2);

$map->register(0x847A2FE, '', 'u8', 16, 2);
$map->register(0x847A2EE, '', 'u8', 16, 2);
$map->register(0x847A2D2, '', 'u16', 14); // MUSIC
$map->register(0x847A2B4, 'kTrainerTowerRewardItems', 'Items', 15);
$map->register(0x847A284, '', 'u16', 24, 2);
$map->register(0x8479658, '', 'u8', 16, 4);

$map->register(0x84795B8, '', 'SubspriteTable');

$map->register(0x83F1E60, 'kMapLocationPositions', 'Coords16', 198);
$map->register(0x83F2178, 'kMapLocationSizes', 'Coords16', 198);
$map->register(0x83A64C8, 'kDirectionToVector', 'Coords16', 9);
$map->register(0x845fd54, 'kMonItemAnimationTable', 'Unk4');
$map->register(0x847A074, '', 'Unk5');
$map->register(0x824F220, 'kTrainerMoney', 'TrainerMoney');
$map->register(0x823A004, 'kEnemyMonElevation', 'EnemyMonElevation');
$map->register(0x83F5D58, '', 'Unk6');
$map->register(0x846B4BC, '', 'Unk7');
$map->register(0x8463580, 'kRegionMapCatchAreas', 'CatchAreas');
$map->register(0x824F050, 'kTypeEffectiveness', 'TypeEffectiveness');

$map->register(0x8443FC0, 'kPokedexOrder_Name', 'PokedexOrder', 411);
$map->register(0x84442F6, 'kPokedexOrder_Weight', 'PokedexOrder', 386);
$map->register(0x84445FA, 'kPokedexOrder_Height', 'PokedexOrder', 386);
$map->register(0x8443FC0, 'kPokedexOrder_Type', 'PokedexOrder', 411);
$map->register(0x84448FE, 'kPokedexOrder_Unk', 'PokedexOrder', 411);
$map->register(0x84448FE, 'kPokedexOrder_Unk', 'PokedexOrder', 411);

$map->register(0x8459B7E, 'kMoveTutorLearnsets', 'MoveTutorLearnsets');

$map->register(0x825EF0C, 'kEggMoves', 'EggMoves');

$map->register(0x845318C, 'kVSSeekerTrainers', 'TrainerEyeTrainer');

$map->register(0x83EE9C8, 'kMailGraphics', 'MailGraphics');

$map->register(0x826CF8C, 'kInGameTrades', 'InGameTrade');
$map->register(0x843FAB0, 'kMonFootprintTable', 'List', 413, '');

$map->register(0x83F6C84, '', 'List', 3, '');
$map->register(0x845AF58, '', 'objtiles', 5);
$map->register(0x8234598, '', 'borg', 5);
$map->register(0x8479D34, 'kDefaultFlags', 'DefaultFlags');


$map->register(0x8466c58, 'kRoamerLocations', 'RoamerLocations');
$map->register(0x84599B8, '', 'objtiles');
$map->register(0x84599C0, '', 'objpals');
$map->register(0x84599C8, '', 'objtiles');
$map->register(0x84599D0, '', 'objpals');

$map->register(0x8459A30, '', 'SpriteTemplate');
$map->register(0x846E160, '', 'List', 4, 'SpriteTemplate');


$map->register(0x8459AA8, '', 'AffineAnimCmds');

$map->register(0x846F470, '', 'SpriteTemplate');

$map->register(0x08456ACC, '', 'List', 3, 'Text');
$map->register(0x8456AD8, '', 'List', 3, 'Text');
$map->register(0x8456AE4, '', 'List', 3, 'Text');
$map->register(0x8456AF0, '', 'List', 51, 'Text');
$map->register(0x8456BBc, '', 'List', 10, 'Text');
$map->register(0x08456C20, '', 'List', 12, 'Text');
$map->register(0x8456C74, '', 'List', 23, 'Text');
$map->register(0x8457608, '', 'List', 2, 'Text');
$map->register(0x845767C, '', 'List', 2, 'Text');
$map->register(0x8457F80, '', 'List', 4, 'Text');

$map->register(0x84791A8, '', 'List', 4, 'Text');
$map->register(0x84791B8, '', 'List', 5, 'Text');
$map->register(0x84791CC, '', 'List', 7, 'Text');
$map->register(0x84791E8, '', 'List', 6, 'Text');

$map->register(0x84792D0, '', 'List', 4, '');


$map->register(0x8456C74, '', 'List', 23, 'Text');

$map->register(0x83FDF3C, 'kBattleStringsTable', 'BattleStringsTable');
$map->register(0x83F5BCC, '', 'List', 84, 'Text');
$map->register(0x824FB08, 'kAbilityDescriptions', 'List', 78, 'Text');
$map->register(0x83E06B8, '', 'List', 39, 'Text');
$map->register(0x83F5B44, '', 'List', 16, 'Text');
$map->register(0x83EDF98, '', 'List', 22, 'Text');
$map->register(0x83A7394, '', 'List', 9, 'Text');
$map->register(0x83FE7F4, '', 'List', 6, 'Text');
$map->register(0x83FE9C4, '', 'List', 4, 'Text');
$map->register(0x83FD5D0, '', 'List', 8, 'Text');
$map->register(0x83FD63C, '', 'List', 5, 'Text');
$map->register(0x83FA754, '', 'List', 2, '');
$map->register(0x83FA740, '', 'List', 5, '');
$map->register(0x83F1C94, '', 'List', 3, '');
$map->register(0x83F1C64, '', 'AnimCmds');
$map->register(0x83F1C30, '', 'AnimCmds');
$map->register(0x83F1B38, '', 'AnimCmds');
$map->register(0x83EE8C8, '', 'AnimCmds');
$map->register(0x83EE890, '', 'AnimCmds');
$map->register(0x83D40AC, '', 'AnimCmds');
$map->register(0x83CD8B8, '', 'List', 5, '');
$map->register(0x83CD8CC, '', 'List', 5, '');

$map->register(0x83E7664, '', 'AffineAnimCmds');
$map->register(0x83E6FF0, '', 'AffineAnimCmds');;;
$map->register(0x86FC314, '', 'ascii');
$map->register(0x86FC318, '', 'ascii');
$map->register(0x86FC31C, '', 'ascii');
$map->register(0x86FC330, '', 'ascii');
$map->register(0x86FC338, '', 'ascii');
$map->register(0x86FC34C, '', 'ascii');
$map->register(0x86FC368, '', 'ascii');
$map->register(0x86FC36C, '', 'ascii');
$map->register(0x86FC370, '', 'ascii');
$map->register(0x86FC37C, '', 'ascii');
$map->register(0x86FC380, '', 'ascii');
$map->register(0x86FC4E8, '', 'ascii');
$map->register(0x86FC4E8, '', 'ascii');
$map->register(0x82345C0, '', 'ascii');
$map->register(0x82345D0, '', 'ascii');

$vramConfigs = [0x82345E8 => 2,
    0x8234690 => 1,
    0x8248320 => 4,
    0x826019C => 2,
    0x8261F1C => 4,
    0x826D1D4 => 4,
    0x826D320 => 4,
    0x83A73B8 => 1,
    0x83BFB74 => 4,
    0x83CC2D8 => 3,
    0x83CD888 => 4,
    0x83CEA50 => 4,
    0x83DF0C4 => 4,
    0x83E2290 => 4,
    0x83EE998 => 3,
    0x83F1A50 => 4,
    0x83F6C7C => 2,
    0x83FFA3C => 2,
    0x840BB80 => 2,
    0x840BB88 => 2,
    0x840BB90 => 4,
    0x840BBA0 => 2,
    0x840C228 => 3,
    0x840C5A4 => 3,
    0x8415A08 => 2,
    0x841EE64 => 1,
    0x843EBB0 => 1,
    0x843F8A0 => 4,
    0x8451EBC => 4,
    0x8452CF4 => 2,
    0x8453F6C => 2,
    0x8457194 => 1,
    0x8459980 => 2,
    0x8459EB8 => 3,
    0x845AA84 => 4,
    0x845FBF4 => 4,
    0x8462E58 => 3,
    0x8463134 => 3,
    0x8463EFC => 4,
    0x846434C => 3,
    0x8466B10 => 4,
    0x8466D60 => 4,
    0x846AEF8 => 1,
    0x846D8D4 => 4,
    0x846E3CC => 4,
    0x846FA74 => 2,
    0x847565C => 4,
    0x84792E0 => 4,
];
foreach ($vramConfigs as $offset => $c) {
    $map->register($offset, '', 'VRAMConfig', $c);
}

$windowTemplates = [
    0x825F7F4,
    0x82601B4,
    0x8261FC4,
    0x826D1CC,
    0x83A738C,
    0x83A73CC,
    0x83C68E4,
    0x83C6AB0,
    0x83CDA48,
    0x83CEB80,
    0x83DF0BC,
    0x83F5B3C,
    0x83FFA8C,
    0x8402248,
    0x8402250,
    0x8402258,
    0x840C234,
    0x8410E08,
    0x843EBA8,
    0x843F8D0,
    0x8451F54,
    0x8451F5C,
    0x8451F64,
    0x845216C,
    0x84521D4,
    0x84521DC,
    0x84521E4,
    0x84521EC,
    0x84521F4,
    0x84521FC,
    0x8452204,
    0x845220C,
    0x8452214,
    0x845311C,
    0x8453124,
    0x8456928,
    0x8456CD0,
    0x8456CFC,
    0x8456D04,
    0x8456D4C,
    0x8456D54,
    0x8456DF4,
    0x8456E34,
    0x8456E6C,
    0x8456F1C,
    0x845A108,
    0x845A110,
    0x845A118,
    0x8462EC0,
    0x8462EC8,
    0x8462ED0,
    0x8462ED8,
    0x84631E0,
    0x8464400,
    0x846487C,
    0x8466C38,
    0x8466D90,
    0x8466D98,
    0x8466DA0,
    0x8466DB8,
    0x8466DC0,
    0x8466DC8,
    0x846E2CC,
    0x846E3F0,
    0x84755E0,
    0x8475684,
    0x847569C,
    0x84756A4,
    0x847A218,
];
foreach ($windowTemplates as $offset) {
    $map->register($offset, '', 'WindowTemplate');
}

$windowTemplates = [
    0x83E22A0 => 6,
    0x841EE68 => 3,
    0x8453104 => 3,
    0x8453FD0 => 3,
    0x8453FE8 => 3,
    0x845661C => 3,
    0x845A120 => 12,
    0x8462E64 => 2,
    0x8462E74 => 4,
    0x8462E94 => 4,
    0x84631E8 => 2,
    0x8463F0C => 3,
    0x8463F24 => 3,
    0x8463F3C => 4,
    0x8463F5C => 4,
    0x8463F7C => 4,
    0x84643B8 => 4,
    0x84643D8 => 14,
    0x8467074 => 3,
    0x8468040 => 2,
    0x846E3F8 => 10,
    0x8475674 => 2,
    0x847568C => 2,
];
foreach ($windowTemplates as $offset => $c) {
    $map->register($offset, '', 'WindowTemplate', $c);
}


$rboxBars = [
    0x8234610 => 1,
    0x8234688 => 1,
    0x823468B => 1,
    0x83A73D4 => 1,
    0x83A73D8 => 1,
    0x83A73DC => 1,
    0x83CC360 => 1,
    0x83CC363 => 1,
    0x83CD8E0 => 1,
    0x83CD8E3 => 1,
    0x83E2428 => 2,
    0x83E2458 => 1,
    0x83EE9BC => 1,
    0x83F1A90 => 1,
    0x83F1A94 => 1,
    0x83F1A98 => 1,
    0x83F1CA8 => 1,
    0x840C23C => 1,
    0x8415A04 => 1,
    0x84530B8 => 4,
    0x8456618 => 1,
    0x8459FFC => 5,
    0x8463184 => 4,
    0x8463EF0 => 4,
    0x8463FA4 => 1,
    0x8463FA7 => 5,
    0x8467068 => 2,
    0x8468038 => 2,
    0x846D950 => 1,
    0x846E3DC => 1,
    0x846E3DC => 1,
    0x846E3DC => 1,
    0x846E3E5 => 1,
    0x846E3EB => 1,
    0x8478E3B => 1,
    0x847A22C => 1,
    0x8478E38 => 4,
];
foreach ($rboxBars as $offset => $c) {
    $map->register($offset, '', 'RboxBar', $c);
}

$map->register(0x8479D10, '', 'ascii');
$map->register(0x8479D24, '', 'ascii');
$map->register(0x8457174, '', 'ascii');
$map->register(0x8457178, '', 'ascii');
$map->register(0x8466F5C, '', 'ascii');
$map->register(0x8466FB8, '', 'ascii');
$map->register(0x8466FD4, '', 'ascii');
$map->register(0x8466FEC, '', 'ascii');
$map->register(0x8467000, '', 'ascii');
$map->register(0x8467044, '', 'ascii');
$map->register(0x8468C94, '', 'ascii');
$map->register(0x843EDE4, '', 'ascii');
$map->register(0x843EDF8, '', 'ascii');
$map->register(0x843EE10, '', 'ascii');
$map->register(0x843EE28, '', 'ascii');
$map->register(0x843EE47, '', 'ascii');
$map->register(0x843EE57, '', 'ascii');
$map->register(0x843EE60, '', 'ascii');
$map->register(0x843EE64, '', 'ascii');
$map->register(0x843EE6C, '', 'ascii');
$map->register(0x843EE78, '', 'ascii');
$map->register(0x843EE84, '', 'ascii');
$map->register(0x843EE90, '', 'ascii');
$map->register(0x843EE9C, '', 'ascii');
$map->register(0x843EEA8, '', 'ascii');
$map->register(0x843EEB0, '', 'ascii');
$map->register(0x843EEB8, '', 'ascii');

$map->register(0x8457138, '', 'ascii');
$map->register(0x8466F28, '', 'ascii');
$map->register(0x8466F80, '', 'ascii');
$map->register(0x8468C5C, '', 'ascii');
$map->register(0x8479CD8, '', 'ascii');
$map->register(0x81E9F14, '', 'ascii');
$map->register(0x81E9F68, '', 'ascii');
$map->register(0x81E9FA0, '', 'ascii');
$map->register(0x81E9FA4, '', 'ascii');
$map->register(0x081E9FB0, '', 'ascii');
$map->register(0x081E9FD8, '', 'ascii');
$map->register(0x81E9FEC, '', 'ascii');
$map->register(0x081EA018, '', 'ascii');
$map->register(0x8352F18, '', 'ascii');
$map->register(0x8352F4C, '', 'ascii');
$map->register(0x083A720C, '', 'ascii');
$map->register(0x083A7240, '', 'ascii');
$map->register(0x083A725C, '', 'ascii');
$map->register(0x083A7290, '', 'ascii');
$map->register(0x083F5EF0, '', 'ascii');
$map->register(0x083F5F24, '', 'ascii');
$map->register(0x0843ED94, '', 'ascii');
$map->register(0x843EDC4, '', 'ascii');
$map->register(0x0843EDD8, '', 'ascii');
$map->register(0x849EAF4, '', 'VoiceGroup');
$map->register(0x84A2C58, '', 'VoiceGroup');


$map->register(0x83F1A9C, '', 'List', 2, '');


$map->register(0x83FA658, '', 'objpals');
$map->register(0x83E17C0, '', 'objtiles', 2);
$map->register(0x83E17D0, '', 'objtiles', 2);

$map->register(0x83E17E0, '', 'List', 8, 'Text');

$map->register(0x83FF9A4, '', 'twoptr', 5, 'TextJP');


$map->register(0x83E00B0, '', 'Menu', 28);

$map->register(0x83A4204, '', 'MapObjectGraphicsInfo');
$map->register(0x83A45F4, '', 'MapObjectGraphicsInfo');

$map->register(0x83FF62C, '', 'SpriteTemplate');
$map->register(0x83BF498, '', 'SpriteTemplate');
$map->register(0x083BF4D4, '', 'SpriteTemplate');
$map->register(0x840C384, '', 'SpriteTemplate');
$map->register(0x83CBF88, '', 'SpriteTemplate');
$map->register(0x83CBFA0, '', 'SpriteTemplate');
$map->register(0x83CBFB8, '', 'SpriteTemplate');
$map->register(0x8231CA0, 'kDummySprite', 'UNK');


$map->register(0x840BCDC, '', 'SpriteTemplate');
$map->register(0x840BCBC, '', 'SpriteTemplate');
$map->register(0x840BD88, '', 'SpriteTemplate');

$map->register(0x840BDFC, '', 'SpriteTemplate');
$map->register(0x840BE8C, '', 'SpriteTemplate');
$map->register(0x840BEC4, '', 'SpriteTemplate');
$map->register(0x0840BDA8, '', 'SpriteTemplates', 2);

$map->register(0x840BE4C, '', 'SpriteTemplate');
$map->register(0x840BE8C, '', 'SpriteTemplate');
$map->register(0x81EA6B4, '', 'SpriteTemplate');
$map->register(0x8231D00, 'kDummySpriteTemplate', 'SpriteTemplate');
$map->register(0x82349BC, '', 'AnimCmds');
$map->register(0x8239FD4, 'kTrainerBackPicPaletteTable', 'objpals', 6);
$map->register(0x8239F74, '', 'List', 6, 'AnimCmds');

$map->register(0x822FC48, '', 'bin', 0x2000);

$map->register(0x82482E8, '', 'SpriteTemplates', 2);

$map->register(0x824EFF0, '', 'SpriteTemplate');
$map->register(0x8250A1C, '', 'SpriteTemplate');
$map->register(0x841F444, '', 'kFontInfos', 8);

$map->register(0x841F4A4, 'kMenuCursorDimensions', 'u8', 16, 2);


$map->register(0x8251CB8, 'gSpeciesToHoennPokedexNum', 'DexOrder');
$map->register(0x8251FEE, 'gSpeciesToNationalPokedexNum', 'DexOrder');
$map->register(0x8252324, 'gHoennToNationalOrder', 'DexMap');
$map->register(0x8250C04, 'kBattleMoves', 'BattleMoves');

$map->register(0x8261EE4, '', 'twoptr', 2, 'Text');


$map->register(0x83D4038, '', 'objpals', 6);
$map->register(0x83AE084, '', 'objtiles', 2);
$map->register(0x843F8F0, '', 'objtiles', 4);
$map->register(0x843F910, '', 'objpals', 5);
$map->register(0x843F938, '', 'objtiles', 4);
$map->register(0x83CEA60, '', 'objpals');
$map->register(0x83CEA68, '', 'objtiles');
$map->register(0x840BEDC, '', 'objtiles', 7);
$map->register(0x840BF14, '', 'objpals', 5);
$map->register(0x83FA588, '', 'twoptr', 1);
$map->register(0x81EA654, 'kGlyphWidthFuncs', 'twoptr', 7);
$map->register(0x824F028, '', 'AnimCmds');
$map->register(0x0824F044, '', 'AffineAnimCmds');

$map->register(0x845A424, '', 'AnimCmds', 2);

$map->register(0x8453F74, '', 'twoptr', 3);


$map->register(0x83FA77C, '', 'objpals');

$map->register(0x826CDD4, '', 'objtiles');
$map->register(0x826CDDC, '', 'objpals');
$map->register(0x826CE2C, '', 'objtiles');
$map->register(0x826CE34, '', 'objpals');
$map->register(0x826CE3C, '', 'objpals');


$map->register(0x826CF88, '', 'AffineAnimCmds');

$map->register(0x83D34B8, '', 'objpals', 2);

$map->register(0x83E23C0, '', 'objtiles');


$map->register(0x83E2440, '', 'List', 3, 'RboxBar');
$map->register(0x83E244C, '', 'List', 3, '');


$map->register(0x83E23BC, '', 'AnimCmds');

$map->register(0x83D3478, '', 'twoptr', 5);
$map->register(0x83D34A0, '', 'objtiles', 3);
$map->register(0x81EA68C, '', 'objtiles', 3);
$map->register(0x81EA6A4, '', 'objpals', 2);
$map->register(0x8231CA8, '', 'List', 1, 'AnimCmds');
$map->register(0x8231CB0, '', 'List', 1, 'AffineAnimCmds');
$map->register(0x8231CB4, '', 'List', 1, 'SpriteTemplate');
$map->register(0x8231CBC, '', 'List', 1, 'Fn');
$map->register(0x825F814, '', 'List', 1, '');
$map->register(0x825F818, '', 'List', 2, 'Fn');
$map->register(0x845A9AC, '', 'List', 20, 'Text');
$map->register(0x845B080, '', 'List', 6, 'Text');
$map->register(0x845B098, '', 'List', 370, 'Text');

$map->register(0x845A9FC, '', 'List', 10, '');


$map->register(0x82349CC, 'kMonFrontPicCoords', 'MonCoords', 440);
$map->register(0x8235E6C, 'kMonBackPicCoords', 'MonCoords', 440);
$map->register(0x823932C, 'kTrainerFrontPicCoords', 'MonCoords', 0x250 / 4);
$map->register(0x8239F8C, 'kTrainerBackPicCoords', 'MonCoords', 0x18 / 4);
$map->register(0x8239FA4, 'kTrainerBackPicTable', 'objtiles', 0x18 / 4);


$map->register(0x848144C, 'UNKTRAINERCARD', 'TrainerCard');
$map->register(0x8481C0C, 'UNKTRAINERCARD', 'TrainerCard');
$map->register(0x847B36C, 'UNKTRAINERCARD', 'TrainerCard');
$map->register(0x847FD0C, 'UNKTRAINERCARD', 'TrainerCard');


$map->register(0x81EEF00, 'kSmallLatinFontWidths', 'FontWidths');
$map->register(0x81FB100, 'kOtherLatinFontWidths', 'FontWidths');
$map->register(0x8207300, 'kLatinFontWidths', 'FontWidths');
$map->register(0x820F500, '', 'FontWidthsJP');
$map->register(0x8217618, 'kLatinMaleFontWidths', 'FontWidths');
$map->register(0x821F818, '', 'FontWidthsJP');
$map->register(0x8227930, 'kLatinFemaleFontWidths', 'FontWidths');
$map->register(0x822FB30, '', 'FontWidthsJP');


$map->register(0x826487C, '', '');
$map->register(0x826497C, '', '');

$map->register(0x84827B4, 'kTrainerCards_Single', 'List', 8, 'TrainerCard');
$map->register(0x84827D4, 'kTrainerCards_Double', 'List', 8, 'TrainerCard');
$map->register(0x84827F4, 'kTrainerCards_Knockout', 'List', 8, 'TrainerCard');
$map->register(0x8482814, 'kTrainerCards_Mixed', 'List', 8, 'TrainerCard');
$map->register(0x084886E8, '', 'List', 354, 'Text');


$map->register(0x83D40E0, '', 'AffineAnimCmds');
$map->register(0x083DF09C, '', 'twoptr', 3);


$map->register(0x845AABC, '', 'twoptr', 21);

$map->register(0x83E248C, '', 'List', 5, 'struct_7');


$map->register(0x83FA5CC, '', 'SpriteTemplate');

$map->register(0x083FA608, '', 'SpriteTemplates', 2);


$map->register(0x083AE06C, '', 'SpriteTemplate');

$map->register(0x843F8E0, '', 'List', 4, 'Text');
$map->register(0x8261ECC, '', 'List', 6, 'Text');
$map->register(0x8261EF4, '', 'List', 9, 'Text');
$map->register(0x83CDA20, '', 'List', 10, 'Text');

$map->register(0x83CDA70, '', 'List', 4, '');


$map->register(0x82D4DAC, '', 'Tileset');
$map->register(0x82D4DDC, '', 'Tileset');
$map->register(0x82D4E3C, '', 'Tileset');
$map->register(0x82D4FBC, '', 'Tileset');
$map->register(0x82D4FBC + 0x18, '', 'Tileset');

$map->register(0x83FF6D4, '', 'SpriteTemplate');
$map->register(0x83FF704, '', 'SpriteTemplate');
$map->register(0x8260290, '', 'SpriteTemplate');
$map->register(0x8260290, '', 'SpriteTemplate');
$map->register(0x82602C0, '', 'SpriteTemplate');
$map->register(0x826CE44, '', 'SpriteTemplate');
$map->register(0x83BF514, '', 'SpriteTemplate');

$map->register(0x843F968, '', 'SpriteTemplate');
$map->register(0x843F9B8, '', 'SpriteTemplate');
$map->register(0x843FA20, '', 'SpriteTemplate');
$map->register(0x843FA40, '', 'SpriteTemplate');
$map->register(0x843FA80, '', 'SpriteTemplate');
$map->register(0x843FA98, '', 'SpriteTemplate');
$map->register(0x843F284, '', 'SpriteTemplate');
$map->register(0x841EEF8, '', 'SpriteTemplate');
$map->register(0x083BF3F8, '', 'SpriteTemplates', 2);

$map->register(0x83E3474, '', 'SpriteTemplate');
$map->register(0x83E3568, '', 'SpriteTemplate');
$map->register(0x83E5CE4, '', 'SpriteTemplate');
$map->register(0x083E5CB8, '', 'SpriteTemplate');
$map->register(0x83E5F74, '', 'SpriteTemplate');
$map->register(0x83E5FAC, '', 'SpriteTemplate');
$map->register(0x83E62D0, '', 'SpriteTemplate');
$map->register(0x83E6C38, '', 'SpriteTemplate');
$map->register(0x83E6C50, '', 'SpriteTemplate');
$map->register(0x83E6DAC, '', 'SpriteTemplate');

$map->register(0x840BCFC, '', 'SpriteTemplate');
$map->register(0x83E7B70, '', 'SpriteTemplate');
$map->register(0x83E7878, '', 'SpriteTemplate');
$map->register(0x83E76F8, '', 'SpriteTemplate');
$map->register(0x83E668C, '', 'SpriteTemplate');
$map->register(0x83E7114, '', 'SpriteTemplate');
$map->register(0x83E65A4, '', 'SpriteTemplate');
$map->register(0x83D3728, '', 'SpriteTemplate');
$map->register(0x83D41FC, '', 'SpriteTemplate');


$map->register(0x86FC068, 'gUnknown_089A324C');
$map->register(0x86FC074, 'gUnknown_089A3258');
$map->register(0x84A329C, 'gMPlayTable');
$map->register(0x8489A08, 'gScaleTable');
$map->register(0x8489ABC, 'gFreqTable');
$map->register(0x8489C54, 'gXcmdTable', 'List', 14, 'Fn');
$map->register(0x8489B04, 'gCgbScaleTable');
$map->register(0x8489B88, 'gCgbFreqTable');
$map->register(0x8489BA0, 'gNoiseTable');
$map->register(0x8489968, 'gMPlayJumpTableTemplate', 'List', 36, 'Fn');
$map->register(0x8489BDC, 'gCgb3Vol');
$map->register(0x8489BEC, 'gClockTable');
$map->register(0x8489AEC, 'gPcmSamplesPerVBlankTable');
$map->register(0x84899F8, 'gDeltaEncodingTable');
$map->register(0x86FBF24, 'sSetupInfos');
$map->register(0x8489C20, 'gPokemonCrySongTemplate');

$map->register(0x83E3734, 'kBattleAnimSpriteTemplate_83E3734', 'SpriteTemplate');
$map->register(0x83E3764, 'kBattleAnimSpriteTemplate_83E3764', 'SpriteTemplate');
$map->register(0x83E398C, 'kBattleAnimSpriteTemplate_83E398C', 'SpriteTemplate');
$map->register(0x83E3CA0, 'kBattleAnimSpriteTemplate_83E3CA0', 'SpriteTemplate');
$map->register(0x83E3CB8, 'kBattleAnimSpriteTemplate_83E3CB8', 'SpriteTemplate');
$map->register(0x83E3CD0, 'kBattleAnimSpriteTemplate_83E3CD0', 'SpriteTemplate');
$map->register(0x83E5BA0, 'kBattleAnimSpriteTemplate_83E5BA0', 'SpriteTemplate');
$map->register(0x83E5F38, 'kBattleAnimSpriteTemplate_83E5F38', 'SpriteTemplate');
$map->register(0x83E60B8, 'kBattleAnimSpriteTemplate_83E60B8', 'SpriteTemplate');
$map->register(0x83E665C, 'kBattleAnimSpriteTemplate_83E665C', 'SpriteTemplate');
$map->register(0x83FF168, 'kBattleAnimSpriteTemplate_83FF168', 'SpriteTemplate');
$map->register(0x83FF26C, 'kBattleAnimSpriteTemplate_83FF26C', 'SpriteTemplate');
$map->register(0x83FF764, 'kBattleAnimSpriteTemplate_83FF764', 'SpriteTemplate');

$map->register(0x83FFB44, 'kBattleTowerLevel50Mons', 'BattleTowerMons');
$map->register(0x8400E04, 'kBattleTowerLevel100Mons', 'BattleTowerMons');

$map->register(0x8253AE4, 'kExperienceTable', 'ExperienceTable');
$map->register(0x86FC03C, 'gUnknown_089A3220');
$map->register(0x86FC0E8, '', 'List', 2, 'ascii');

$map->register(0x86FC384, '', 'List', 10, '');

$map->register(0x8414588, '', 'List', 13, '');

$map->register(0x83CD928, '', 'List', 2, 'RboxBar');
$map->register(0x83CD944, '', 'List', 2, 'Text');
$map->register(0x83D2B54, '', 'objtiles');


$map->register(0x83CEC38, '', 'AffineAnimCmds');
$map->register(0x83CDA90, '', 'List', 1, '');

$map->register(0x8231D18, 'kAnimFuncs', 'List', 2, 'Fn');
$map->register(0x8231D20, 'kAffineAnimFuncs', 'List', 2, 'Fn');
$map->register(0x8231D28, 'kAnimCmdFuncs', 'List', 4, 'Fn');
$map->register(0x8231D38, 'kAffineAnimCmdFuncs', 'List', 4, 'Fn');
$map->register(0x8231E70, '_expandPlaceholderFuncs', 'List', 14, 'Fn');
$map->register(0x8231EA8, '_extCtrlCodeLengths', 'u8', 25);
$map->register(0x825011C, '', 'List', 248, 'Fn');
$map->register(0x825089C, '', 'List', 57, 'Fn');
$map->register(0x8250A34, 'kOpponentBufferCommands', 'List', 57, 'Fn');
$map->register(0x8250B20, 'kLinkOpponentBufferCommands', 'List', 57, 'Fn');

$map->register(0x825D7B4, 'kLevelUpLearnsets', 'List', 412, 'LevelUpLearnset', function ($i) use ($species) {
    if ($i == 0) {
        $i = 1;
    }

    $s = $species[$i];
    $s = explode('_', $s);
    $s = array_map(function ($s) {
        return ucfirst(strtolower($s));
    }, $s);
    $s = implode($s);

    return 'kLevelUpLearnset_' . $s;
});
$map->register(0x839FDB0, '', 'List', 152, 'MapObjectGraphicsInfo');

$map->register(0x826D33C, '', 'List', 3, 'Fn');
$map->register(0x826D348, '', 'List', 11, 'Fn');
$map->register(0x826D374, '', 'List', 2, 'Fn');
$map->register(0x83ADF5C, '', 'List', 48, 'Fn');
$map->register(0x83BFB84, '', 'List', 6, 'Fn');
$map->register(0x83FA320, '', 'List', 18, 'Fn');
$map->register(0x839FBC8, '', 'List', 81, 'Fn');
$map->register(0x83A0010, '', 'List', 36, 'SpriteTemplate');

$map->register(0x834EB8C, '', 'List', 383, 'MapAttributes');

$map->register(0x824EE34, 'kBattleTerrainTable', 'BattleTerrainTable');


$map->register(0x823EAC8, 'kTrainers', 'Trainers');


$map->register(0x83A0010, '', 'List', 36, 'SpriteTemplate');

$map->register(0x83ECED4, '', 'EasyChatTable');


$map->register(0x83DB028, 'kItems', 'ItemList');

$map->register(0x83EEAC4, '', 'mail_maybe');
$map->register(0x83EEB68, '', 'mail_maybe');

$map->register(0x8252B48, 'kNatureStatTable', 'NatureStatTable');
$map->register(0x8252BC8, 'kTMHMLearnsets', 'TMHMLearnsets');

$map->register(0x83A72A8, '', 'twoptr', 13, '', '');
$map->register(0x83F3024, '', 'CuteSketch');

$map->register(0x8259754, 'kEvolutionTable', 'EvolutionTable');

$map->register(0x8254784, 'kBaseStats', 'BaseStats');

class LzData
{
    public $compressedSize;

    public function __construct($compressedSize)
    {
        $this->compressedSize = $compressedSize;
    }
}

function lz($compressedSize)
{
    return new LzData($compressedSize);
}

/** @var LzData[] $lzData */
$lzData = [
    0x8231EE4 => lz(0x4A5), // 0xE00 bytes
    0x823238C => lz(0x1EB), // 0x800 bytes
    0x826BA5C => lz(0xFD), // 0x800 bytes
    0x826BF7C => lz(0x68E), // 0x1800 bytes
    0x826C60C => lz(0x6F2), // 0x800 bytes
    0x83BF58C => lz(0x1C), // 0x80 bytes
    0x83BF5A8 => lz(0xA4), // 0x500 bytes
    0x83CC368 => lz(0x171), // 0x200 bytes
    0x83CC4DC => lz(0x214), // 0x4B0 bytes
    0x83CC6F0 => lz(0x1B6), // 0x4B0 bytes
    0x83CC8A8 => lz(0xDB), // 0x4B0 bytes
    0x83CC984 => lz(0x129), // 0x4B0 bytes
    0x83CCAB0 => lz(0x1F4), // 0x4B0 bytes
    0x83CCCA4 => lz(0x189), // 0x4B0 bytes
    0x83CCE30 => lz(0x95), // 0x4B0 bytes
    0x83CCEC8 => lz(0x95), // 0x4B0 bytes
    0x83CD5E8 => lz(0x29D), // 0x400 bytes
    0x83CE438 => lz(0x95), // 0x100 bytes
    0x83CE4D0 => lz(0x10C), // 0x800 bytes
    0x83CE5FC => lz(0xFB), // 0x500 bytes
    0x83EF3C4 => lz(0x8C), // 0x400 bytes
    0x83EF450 => lz(0x8D), // 0x400 bytes
    0x83EF4E0 => lz(0x42), // 0x100 bytes
    0x83EF524 => lz(0x76), // 0x80 bytes
    0x83EF59C => lz(0x80), // 0x80 bytes
    0x83EF61C => lz(0xD13), // 0x2800 bytes
    0x83F0330 => lz(0x24E), // 0x800 bytes
    0x83F0580 => lz(0x31C), // 0x1000 bytes
    0x83F089C => lz(0x25D), // 0x4B0 bytes
    0x83F0AFC => lz(0x10E), // 0x4B0 bytes
    0x83F0C0C => lz(0xE3), // 0x4B0 bytes
    0x83F0CF0 => lz(0x119), // 0x4B0 bytes
    0x83F0E0C => lz(0x10E), // 0x4B0 bytes
    0x83F0F1C => lz(0x168), // 0x4B0 bytes
    0x83F1084 => lz(0x10C), // 0x4B0 bytes
    0x83F1190 => lz(0x13A), // 0x4B0 bytes
    0x83F12CC => lz(0x11D), // 0x400 bytes
    0x83F13EC => lz(0x161), // 0x400 bytes
    0x83F1550 => lz(0xEE), // 0x400 bytes
    0x83F1640 => lz(0xF5), // 0x400 bytes
    0x83F1738 => lz(0xCC), // 0x400 bytes
    0x83F1804 => lz(0xD4), // 0x400 bytes
    0x83F18D8 => lz(0x2F), // 0x40 bytes
    0x83F1908 => lz(0x70), // 0x100 bytes
    0x83F1978 => lz(0x26), // 0xA0 bytes
    0x83F19A0 => lz(0xB0), // 0x4B0 bytes
    0x83F5864 => lz(0x1E0), // 0x800 bytes
    0x83F5A44 => lz(0xB1), // 0x400 bytes
    0x83F6CB0 => lz(0x6F5), // 0x1C00 bytes
    0x83F73A8 => lz(0x4E1), // 0x800 bytes
    0x83F788C => lz(0x4D3), // 0x800 bytes
    0x8402280 => lz(0x263), // 0x4E0 bytes
    0x84024E4 => lz(0x14A), // 0x800 bytes
    0x8402650 => lz(0x18), // 0x60 bytes
    0x8402668 => lz(0xA1), // 0x500 bytes
    0x840272C => lz(0x1CB), // 0x480 bytes
    0x8402D54 => lz(0x1292), // 0x31A0 bytes
    0x8403FE8 => lz(0x8E2), // 0x1000 bytes
    0x84048EC => lz(0x68F), // 0x1100 bytes
    0x8404F7C => lz(0x436), // 0x1000 bytes
    0x8405414 => lz(0x47C), // 0x880 bytes
    0x8405890 => lz(0x275), // 0x1000 bytes
    0x8405B28 => lz(0x1B2), // 0x220 bytes
    0x8405CDC => lz(0xC5), // 0x500 bytes
    0x8405DC4 => lz(0x685), // 0xE40 bytes
    0x840644C => lz(0x1E5), // 0x800 bytes
    0x8406654 => lz(0xB79), // 0x1540 bytes
    0x84071D0 => lz(0x25F), // 0x800 bytes
    0x8407470 => lz(0x5DF), // 0xAE0 bytes
    0x8407A50 => lz(0x14B), // 0x500 bytes
    0x8407B9C => lz(0x11FB), // 0x2B80 bytes
    0x8408D98 => lz(0x4D2), // 0x1000 bytes
    0x840C3BC => lz(0x1C9), // 0x3A0 bytes
    0x840C650 => lz(0x404), // 0x2000 bytes
    0x840CA54 => lz(0x135), // 0x400 bytes
    0x840CB8C => lz(0x69C), // 0xC80 bytes
    0x840D228 => lz(0x9E4), // 0x1380 bytes
    0x840E158 => lz(0x7AB), // 0xC80 bytes
    0x840E904 => lz(0x93B), // 0xF00 bytes
    0x840F240 => lz(0x702), // 0xC80 bytes
    0x840F944 => lz(0x852), // 0xF00 bytes
    0x8410198 => lz(0x41C), // 0xC80 bytes
    0x84105B4 => lz(0x548), // 0x1200 bytes
    0x8410E30 => lz(0xDC5), // 0x3000 bytes
    0x8411C18 => lz(0xD88), // 0x3000 bytes
    0x84129C0 => lz(0x958), // 0x3000 bytes
    0x8413338 => lz(0x51A), // 0x2000 bytes
    0x8413874 => lz(0x522), // 0x2000 bytes
    0x8413DB8 => lz(0x561), // 0x2000 bytes
    0x84147C0 => lz(0xD26), // 0x2000 bytes
    0x84154E8 => lz(0x46A), // 0x1000 bytes
    0x843F7AC => lz(0xC7), // 0x180 bytes
    0x8440124 => lz(0x14F), // 0x500 bytes
    0x8440274 => lz(0x136), // 0x400 bytes
    0x84403AC => lz(0x11B), // 0x400 bytes
    0x8440EF0 => lz(0x23A), // 0x600 bytes
    0x8443620 => lz(0x2ED), // 0xD80 bytes
    0x845967C => lz(0xBE), // 0x140 bytes
    0x845973C => lz(0x12C), // 0x500 bytes
    0x8459888 => lz(0x19), // 0x40 bytes
    0x84605E8 => lz(0x5BF), // 0x1400 bytes
    0x8460BA8 => lz(0xF9), // 0x438 bytes
    0x8460CA4 => lz(0x43), // 0x140 bytes
    0x8460CE8 => lz(0xAC), // 0x500 bytes
    0x8460F14 => lz(0x6E5), // 0x1800 bytes
    0x846163C => lz(0x696), // 0x1800 bytes
    0x8461D14 => lz(0x695), // 0x1800 bytes
    0x84623EC => lz(0x5E2), // 0x1800 bytes
    0x8463328 => lz(0x6E), // 0x100 bytes
    0x8463398 => lz(0x40), // 0x100 bytes
    0x84633D8 => lz(0x43), // 0x80 bytes
    0x846343C => lz(0x109), // 0x4A0 bytes
    0x8463740 => lz(0x12C), // 0x800 bytes
    0x846386C => lz(0x11D), // 0x800 bytes
    0x8463B20 => lz(0xF), // 0x20 bytes
    0x8463B64 => lz(0x24), // 0x40 bytes
    0x8463B88 => lz(0xF8), // 0x500 bytes
    0x8463C80 => lz(0x13C), // 0x800 bytes
    0x84659D0 => lz(0x804), // 0x1140 bytes
    0x84661D4 => lz(0x2E8), // 0x500 bytes
    0x846653C => lz(0x81), // 0x80 bytes
    0x8466620 => lz(0x375), // 0x840 bytes
    0x8466998 => lz(0x176), // 0x500 bytes
    0x8466D30 => lz(0x30), // 0x100 bytes
    0x846B7D4 => lz(0x22A), // 0x7E0 bytes
    0x846BA00 => lz(0x1AD), // 0x500 bytes
    0x846BBD0 => lz(0x94E), // 0x1000 bytes
    0x846C520 => lz(0x3B6), // 0x1000 bytes
    0x846C8F8 => lz(0xAAF), // 0x1540 bytes
    0x846D3A8 => lz(0x52A), // 0x2000 bytes
    0x846ED90 => lz(0x12D), // 0x280 bytes
    0x846EEC0 => lz(0x197), // 0x500 bytes
    0x846F058 => lz(0x228), // 0x800 bytes
    0x846F6D0 => lz(0x210), // 0x800 bytes
    0x846F8E0 => lz(0x193), // 0x800 bytes
    0x847220C => lz(0x1B1), // 0x480 bytes
    0x84723E0 => lz(0x88D), // 0x1000 bytes
    0x8472C70 => lz(0xDC9), // 0x1A00 bytes
    0x8473A3C => lz(0x93), // 0x180 bytes
    0x8473AD0 => lz(0x148), // 0x400 bytes
    0x8473C18 => lz(0x121C), // 0x3000 bytes
    0x8474E34 => lz(0x269), // 0x500 bytes
    0x84750A0 => lz(0x24D), // 0x500 bytes
    0x84752F0 => lz(0x249), // 0x500 bytes
    0x8475968 => lz(0x1B1), // 0x480 bytes
    0x8475B3C => lz(0x88D), // 0x1000 bytes
    0x84763CC => lz(0xDC9), // 0x1A00 bytes
    0x8477198 => lz(0x93), // 0x180 bytes
    0x847722C => lz(0x148), // 0x400 bytes
    0x8477374 => lz(0x121C), // 0x3000 bytes
    0x8478590 => lz(0x269), // 0x500 bytes
    0x84787FC => lz(0x24D), // 0x500 bytes
    0x8478A4C => lz(0x249), // 0x500 bytes

    0x840DC0C => lz(0x54A), // 0x800 bytes
];

foreach ($lzData as $offset => $lz) {
    $map->register($offset, '', 'lz', $lz->compressedSize);
}

function getFn($offset)
{
    global $container;

    if ($offset == 0) {
        return 'NULL';
    }

    $fn = $container['functionMap'][$offset - 1];
    return $fn->name;
}

$map->register(0x8479ED8, '', 'u8', 83 * 4, 4);
$map->register(0x847A024, '', 'u8', 80, 8);
$map->register(0x83F2EE0, '', 'u8', 108 * 3, 3);
$map->register(0x8261D08, '', 'u8', 0x138, 6);
$map->register(0x83FEB64, '', 'u8', 12 * 25, 12);
$map->register(0x8471F50, '', 'u8', 11 * 5 * 5, 11); // u8[5][5][11]

$map->register(0x845F83C, '', 'u8', 96, 6);
$map->register(0x845A180, '', 'u8', 70, 10);
$map->register(0x845A1C6, '', 'u8', 70, 10);
$map->register(0x845A20C, '', 'u8', 0x36, 18);
$map->register(0x845A242, '', 'u8', 0x36, 18);
$map->register(0x845A278, '', 'u8', 0x36, 18);

$map->register(0x83A65BC, '', 'List', 170, 'AnSteps');
$map->register(0x83C9CB8, 'kWildMonHeaders', 'WildMonHeaders');

$map->register(0x82390DC, '', 'List', 148, 'AnimCmds');
$map->register(0x83F1CAC, '', 'List', 109, 'Text');
$map->register(0x83E264C, '', 'List', 12, 'Text');
$map->register(0x8231CE4, 'kDummyOamData', 'OAM');
$map->register(0x8231CEC, 'kDummyAnim', 'AnimCmd');
$map->register(0x8231CF0, 'kDummySpriteAnimTable', 'AnimCmds');
$map->register(0x8231CF4, 'kDummyAffineAnim', 'AffineAnimCmd');
$map->register(0x8231CFC, 'kDummySpriteAffineAnimTable', 'AffineAnimCmds');
$map->register(0x8231D48, '', 's32', 24, 2);
$map->register(0x8231DA8, 'kOamDimensions', 'u8', 12 * 4, 4);


$map->register(0x84A32CC, 'gSongTable', 'SongList', 347);
$map->register(0x84556F8, 'kDecorations', 'Decorations', 121);


foreach ($usedData as $addr => $uages) {
    if ($addr > 0x8d00000) {
        continue;
    }

    $rom->setPosition($addr + 0x10);
    $rotscale = $rom->readUInt32();
    $fn = $rom->readUInt32();


    if ($rotscale == 0x8231CFC && isset($container['functionMap'][$fn - 1])) {
        $map->register($addr, '', 'SpriteTemplate');
        continue;
    }
}
$map->dump($rom, null, 0, 0);

foreach ($usedData as $addr => $suages) {
    $map->register($addr, '', 'UNK');
}

$usedData = json_decode(file_get_contents($container['basepath'] . '/data.json'), true);
foreach ($usedData as $addr => $uages) {
    if ($addr > 0x81D865F && $addr < 0x81D96AC) {
        $map->register($addr, '', 'MoveEffect');
    }
}


foreach ($usedData as $addr => $uages) {
    if ($addr > 0x8d00000) {
        continue;
    }

    $map->register($addr, '', null);
}
$map->register(0x847214C, '', 'UNK');
$map->register(0x84723C0, '', 'UNK');
$map->register(0x84723E0, '', 'UNK');
$map->register(0x8472C70, '', 'UNK');
$map->register(0x8473A3C, '', 'UNK');
$map->register(0x8473AD0, '', 'UNK');
$map->register(0x8473C18, '', 'UNK');
$map->register(0x8474E34, '', 'UNK');
$map->register(0x84750A0, '', 'UNK');
$map->register(0x84752F0, '', 'UNK');

$basePath = ROOT . '/out/' . $container['project'];
if (!file_exists($basePath)) {
    mkdir($basePath, 0777, true);
}

$script = fopen($basePath . '/script.s', 'w+');
$map->dump($rom, $script, 0x8160478, 0x81C68EA);
fclose($script);

$script = fopen($basePath . '-script_a.s', 'w+');
$map->dump($rom, $script, 0x81C68EA, 0x81D65A8);
fclose($script);

$script = fopen($basePath . '-script_b.s', 'w+');
$map->dump($rom, $script, 0x81D65A8, 0x8200000);
fclose($script);

$script = fopen($basePath . '-script_c.s', 'w+');
$map->dump($rom, $script, 0x81D96AC, 0x81D99B0);
fclose($script);

$script = fopen($basePath . '-script)d.s', 'w+');
$map->dump($rom, $script, 0x81D99B0, 0x81D9BF4);
fclose($script);

$script = fopen($basePath . '/10-data1.inc', 'w+');
$map->dump($rom, $script, 0x81E9F28, 0x83F3024);

$script = fopen($basePath . '/20-cutesketch.inc', 'w+');
$map->dump($rom, $script, 0x83F3024, 0x83F55A4);

$script = fopen($basePath . '/30-data1-b.inc', 'w+');
$map->dump($rom, $script, 0x83F55A4, 0x8444C35);

$script = fopen($basePath . '/pokedex_entries.inc', 'w+');
$map->dump($rom, $script, 0x8444C35, 0x844E850);
fclose($script);

$script = fopen($basePath . '/pokedex.inc', 'w+');
$map->dump($rom, $script, 0x844E850, 0x8451EBC);
fclose($script);

$script = fopen($basePath . '/data3.inc', 'w+');
$map->dump($rom, $script, 0x8451EBC, 0x86FBEA4);
fclose($script);

$script = fopen($basePath . '-data2b.s', 'w+');
$map->dump($rom, $script, 0x86FC068, 0x86FBEA4);
fclose($script);

$map->register(0x871A23C, '');

$script = fopen($basePath . '/libdata.s', 'w+');
$map->dump($rom, $script, 0x86FC2F0, 0x871A23C);
fclose($script);


$script = fopen($basePath . '-data3.s', 'w+');
$map->dump($rom, $script, 0x86FBEA4, 0x9000000);
fclose($script);
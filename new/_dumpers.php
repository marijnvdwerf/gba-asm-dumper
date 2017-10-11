<?php

use MarijnvdWerf\DisAsm\Dumper\RomMap as RomMap2;
use PhpBinaryReader\BinaryReader;

$map = new RomMap2();

$map->registerDumper('ascii', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $string = '';

    while (true) {
        $char = $rom->readString(1);

        if (ord($char) == 0) {
            break;
        }
        if ($char === "\n") {
            $char = '\n';
        }

        $string .= $char;
    }

    if (strpos($string, "C:/WORK/POKeFRLG/src/pm_lgfr_ose/source/") === 0) {
        $string = str_replace("C:/WORK/POKeFRLG/src/pm_lgfr_ose/source/", "", $string);

        fprintf($out, "    debug_path\n");
    }
    fprintf($out, "    .asciz \"%s\"\n", $string);
});


$map->registerDumper('asciilist', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];
    $strlen = $arguments[1];


    for ($i = 0; $i < $count; $i++) {
        $string = '';

        for ($n = 0; $n < $strlen; $n++) {
            $char = $rom->readString(1);
            if (ord($char) == 0) {
                $char = '\x00';
            }
            if ($char === "\n") {
                $char = '\n';
            }

            $string .= $char;
        }

        fprintf($out, "    .ascii \"%s\"\n", $string);
    }
});

$map->registerDumper('EnvTable', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    for ($i = 0; $i < 28; $i++) {
        fprintf($out, "    .byte %d, %d\n", $rom->readUInt8(), $rom->readUInt8());
        fprintf($out, "    .2byte 0x%04X\n", $rom->readUInt16());
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'LzGfx'));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'LzBin'));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'Pal', 0x40));
        fprintf($out, "\n");
    }

});

$map->registerDumper('Credits', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    for ($i = 0; $i < 43; $i++) {
        fprintf($out, "    .4byte %s\n", mkString($map, $rom->readUInt32()));
        fprintf($out, "    .4byte %s\n", mkString($map, $rom->readUInt32()));
        fprintf($out, "    .4byte %d\n", $rom->readUInt32());
        fprintf($out, "\n");
    }

});

$map->registerDumper('Doors', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    for ($i = 0; $i < 33; $i++) {
        $arg1 = $rom->readUInt16();
        $arg2 = $rom->readUInt8();
        $twoTiled = $rom->readUInt8();
        $tiles = $rom->readUInt32();
        $palettes = $rom->readUInt32();
        $tiles2 = $map->register($tiles, sprintf('DoorAnimTiles_%02d', $i), 'Gfx2', $i == 0 ? 0x1A0 : 0x320);
        $palettes2 = $map->register($palettes, sprintf('DoorAnimPalettes_%02d', $i), 'u8', 8);
        fprintf($out, "    .2byte 0x%04X\n", $arg1);
        fprintf($out, "    .byte %d\n", $arg2);
        fprintf($out, "    .byte %d @ two-tiled\n", $twoTiled);
        fprintf($out, "    .4byte %s\n", $tiles2);
        fprintf($out, "    .4byte %s\n", $palettes2);
        fprintf($out, "\n");
    }

});

$map->registerDumper('Berries', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    $sr = new StringReader();

    for ($i = 0; $i < 0x2B; $i++) {
        $pos = $rom->getPosition();
        $nam = $sr->readLines($rom)[0];
        $rom->setPosition($pos + 7);
        $firmness = $rom->readUInt8();
        $size = $rom->readUInt16();
        $minYield = $rom->readUInt8();
        $maxYield = $rom->readUInt8();
        $desc1 = $rom->readUInt32();
        $desc2 = $rom->readUInt32();
        $stage = $rom->readUInt8();
        $spicy = $rom->readUInt8();
        $dry = $rom->readUInt8();
        $sweet = $rom->readUInt8();
        $bitter = $rom->readUInt8();
        $sour = $rom->readUInt8();
        $smoothness = $rom->readUInt8();
        $rom->readBytes(1);

        fprintf($out, "    .string \"%s\", 7\n", $nam);
        fprintf($out, "    .byte %d\n", $firmness);
        fprintf($out, "    .2byte %d\n", $size);
        fprintf($out, "    .byte %d, %d\n", $minYield, $maxYield);
        fprintf($out, "    .4byte %s\n", $map->register($desc1, '', 'TextJP'));
        fprintf($out, "    .4byte %s\n", $map->register($desc2, '', 'TextJP'));
        fprintf($out, "    .byte %d\n", $stage);
        fprintf($out, "    .byte %d, %d, %d, %d, %d\n", $spicy, $dry, $sweet, $bitter, $sour);
        fprintf($out, "    .byte %d\n", $smoothness);
        fprintf($out, "    .byte 0 @ padding\n");
        fprintf($out, "\n");
    }
    /*
     * .string "CHERI$", 7
        .byte BERRY_FIRMNESS_SOFT
        .2byte 20 @ size (in millimeters)
        .byte 3 @ max yield
        .byte 2 @ min yield
        .4byte gBerryDescriptionPart1_Cheri
        .4byte gBerryDescriptionPart2_Cheri
        .byte 3 @ stage duration (in hours)
        .byte 10 @ spicy
        .byte 0 @ dry
        .byte 0 @ sweet
        .byte 0 @ bitter
        .byte 0 @ sour
        .byte 25 @ smoothness
        .byte 0 @ padding
     */

});

$map->registerDumper('MapGroups', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $ptrs = [];
    for ($i = 0; $i < 43; $i++) {
        $ptr = $rom->readUInt32();
        $ptrs[$i] = $ptr;

    }

    foreach ($ptrs as $i => $ptr) {
        fprintf($out, "    .4byte %s\n", $map->register($ptr, 'kMapGroup' . $i, 'MapGroup', $i));
    }

});

$map->registerDumper('MapGroup', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $i = $arguments[0];

    global $mapNames;
    $n = 0;

    while (true) {
        if (!isset($mapNames[$i . '.' . $n])) {
            break;
        }
        $name = $mapNames[$i . '.' . $n];;
        $addr = $rom->readUInt32();
        fprintf($out, "    .4byte %s\n", $map->register($addr, $name, 'MapHeader', $name));
        $n++;
    }
});

function dumpAnim($type, BinaryReader $rom, RomMap2 $map, $out, $arguments)
{

    if (isset($arguments[0])) {
        $count = $arguments[0];

        for ($i = 0; $i < $count; $i++) {
            $addr = $rom->readUInt32();
            fprintf($out, "    .4byte %s\n", $map->register($addr, '', $type));
        }

        return;
    }

    while (true) {
        $pos = $rom->getPosition();

        $addr = $rom->readUInt32();
        if ($addr < 0x8000000 || $addr > 0x8D00000) {
            $rom->setPosition($pos);
            return;
        }

        fprintf($out, "    .4byte %s\n", $map->register($addr, '', $type));

        if ($map->hasLabel($rom->getPosition())) {
            return;
        }
    }
}

$map->registerDumper('AnimCmd', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $pos = $rom->getPosition();

    /*
     *

        .macro obj_image_anim_frame pic_id, duration, flags = 0
        .2byte \pic_id
        .byte (\flags) | (\duration)
        .byte 0 @ padding
        .endm

        .macro obj_image_anim_loop count
        .2byte 0xfffd
        .byte \count
        .byte 0 @ padding
        .endm

        .macro obj_image_anim_jump target_index
        .2byte 0xfffe
        .byte \target_index
        .byte 0 @ padding
        .endm

        .macro obj_image_anim_end
        .2byte 0xffff
        .2byte 0 @ padding
        .endm
     */
    while (true) {
        $code = $rom->readUInt16();

        switch ($code) {
            default:
                $flagsDuration = $rom->readUInt8();

                $flags = [];
                if ($flagsDuration & (1 << 6)) {
                    $flags[] = 'OBJ_IMAGE_ANIM_H_FLIP';
                    $flagsDuration &= ~(1 << 6);
                }
                if ($flagsDuration & (1 << 7)) {
                    $flags[] = 'OBJ_IMAGE_ANIM_V_FLIP';
                    $flagsDuration &= ~(1 << 7);
                }
                $rom->readBytes(1);
                fprintf($out, "    obj_image_anim_frame %d, %d", $code, $flagsDuration);
                if (count($flags) !== 0) {
                    fprintf($out, ", %s", implode(' | ', $flags));
                }
                fwrite($out, "\n");
                break;


            case 0xFFFD:
                $count = $rom->readUInt8();
                $rom->readBytes(1);
                fprintf($out, "    obj_image_anim_loop %d\n", $count);
                break;

            case 0xFFFE:
                $targetIndex = $rom->readUInt8();
                $rom->readBytes(1);
                fprintf($out, "    obj_image_anim_jump %d\n", $targetIndex);
                return;

            case 0xFFFF:
                $rom->readBytes(2);
                fprintf($out, "    obj_image_anim_end\n");
                return;
        }
    }
});

$map->registerDumper('AffineAnimCmd', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $pos = $rom->getPosition();

    /*
     *

    	.macro obj_rot_scal_anim_frame delta_x_scale, delta_y_scale, delta_angle, duration
	.2byte \delta_x_scale
	.2byte \delta_y_scale
	.byte \delta_angle
	.byte \duration
	.2byte 0 @ padding
	.endm


     */
    while (true) {
        $code = $rom->readInt16();

        switch ($code) {
            default:
                $yScale = $rom->readInt16();
                $angle = $rom->readInt8();
                $duration = $rom->readInt8();
                $rom->readBytes(2);
                fprintf($out, "    obj_rot_scal_anim_frame %d, %d, %d, %d\n", $code, $yScale, $angle, $duration);
                break;


            case 0x7FFD:
                /*
                 *

	.macro obj_rot_scal_anim_loop count
	.2byte 0x7ffd
	.2byte \count
	.4byte 0 @ padding
	.endm
                 */
                $count = $rom->readUInt16();
                $rom->readBytes(4);
                fprintf($out, "    obj_rot_scal_anim_loop %d\n", $count);
                break;

            case 0x7FFE:
                /*
                 *
	.macro obj_rot_scal_anim_jump target_index
	.2byte 0x7ffe
	.2byte \target_index
	.4byte 0 @ padding
	.endm
                 */
                $targetIndex = $rom->readUInt16();
                $rom->readBytes(4);
                fprintf($out, "    obj_rot_scal_anim_jump %d\n", $targetIndex);
                return;

            case 0x7fff:
                /*
                 *
	.macro obj_rot_scal_anim_end unknown=0
	.2byte 0x7fff
	.2byte \unknown
	.fill 4 @ padding
	.endm
                 */
                $var = $rom->readUInt8();
                $rom->readBytes(4);
                fprintf($out, "    obj_rot_scal_anim_end");
                if ($var !== 0) {
                    fprintf($out, " %d", $var);
                }
                fwrite($out, "\n");
                return;
        }
    }
});


$map->registerUnboundedDumper('AnimCmds', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    return dumpAnim('AnimCmd', $rom, $map, $out, $arguments);
});


$map->registerDumper('DefaultFlags', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $mapNames;

    for ($i = 0; $i < 15; $i++) {
        $bank = $rom->readInt8();
        $map = $rom->readUInt8();
        fprintf($out, "    .byte %d, %d @ %s\n", $bank, $map, $mapNames[$bank . '.' . $map]);
        readByteArray($out, $rom, 2);
        readByteArray($out, $rom, 8);
        readByteArray($out, $rom, 8);
        readByteArray($out, $rom, 8);
        fwrite($out, "\n");
    }
});

$map->registerDumper('OAM', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    if (false) {
        fprintf($out, "    .2byte 0x%04X\n", $rom->readUInt16());
        fprintf($out, "    .2byte 0x%04X\n", $rom->readUInt16());
        fprintf($out, "    .2byte 0x%04X\n", $rom->readUInt16());
    } else {
        $a0 = $rom->readUInt16();
        $a1 = $rom->readUInt16();
        $a2 = $rom->readUInt16();
        $a3 = $rom->readUInt16();

        $y = $a0 & 0b11111111;
        $affineMode = ($a0 >> 8) & 0b11;
        $objMode = ($a0 >> 10) & 0b11;
        $mosaic = ($a0 >> 12) & 0b1;
        $bpp = ($a0 >> 13) & 0b1;
        $shape = ($a0 >> 14) & 0b11;

        $x = $a1 & 0b111111111;
        if ($affineMode) {
            $matrixNum = ($a1 >> 9) & 0b11111;
        } else {
            $hflip = ($a1 >> 12) & 0b1;
            $vflip = ($a1 >> 13) & 0b1;
        }
        $size = ($a1 >> 14) & 0b11;

        $tileNum = $a2 & 0b1111111111;
        $priority = ($a2 >> 10) & 0b11;
        $paletteNum = ($a2 >> 12) & 0b111;

        $sizes = [
            0 => [
                [8, 8],
                [16, 16],
                [32, 32],
                [64, 64],
            ],
            1 => [
                [16, 8],
                [32, 8],
                [32, 16],
                [64, 32],
            ],
            2 => [
                [8, 16],
                [8, 32],
                [16, 32],
                [32, 64],
            ]
        ];

        fprintf($out, "    oam_start\n");
        fprintf($out, "    oam_pos %d, %d\n", $x, $y);
        fprintf($out, "    oam_size %d, %d\n", $sizes[$shape][$size][0], $sizes[$shape][$size][1]);
        fprintf($out, "    oam_end\n");
    }
});

$map->registerUnboundedDumper('AffineAnimCmds', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    return dumpAnim('AffineAnimCmd', $rom, $map, $out, $arguments);
});

$map->registerDumper('BattleTerrainTable', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 20; $i++) {
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), 'kBattleTerrainTiles_' . $i, 'LzGfx'));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), 'kBattleTerrainTilemap_' . $i, 'LzBin'));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), 'kBattleTerrainAnimTiles_' . $i, 'LzGfx'));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), 'kBattleTerrainAnimTilemap_' . $i, 'LzBin'));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), 'kBattleTerrainPalette_' . $i, 'LzPal'));
        fprintf($out, "\n");
    }

});

$map->registerDumper('SongList', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < $arguments[0]; $i++) {
        $addr = $rom->readUInt32();
        $var = $rom->readUInt16();
        $var2 = $rom->readUInt16();
        fprintf($out, "    song %s, %d, %d\n", $map->register($addr, '', 'Song'), $var, $var2);
    }
});

function readFixedString(BinaryReader $br, StringReader $sr, $length)
{
    $pos = $br->getPosition();

    $lines = $sr->readLines($br);

    $br->setPosition($pos + $length);

    return $lines[0];
}

function readByteArray($out, BinaryReader $br, $count, $lineCount = 8)
{
    $data = [];

    for ($i = 0; $i < $count; $i++) {
        $data[] = sprintf('0x%02X', $br->readUInt8());
    }

    while (count($data) > 0) {
        $line = min(count($data), $lineCount);

        $linedata = array_slice($data, 0, $line);
        $data = array_slice($data, $line);

        fprintf($out, "    .byte %s\n", implode(', ', $linedata));
    }
}

function read2ByteArray($out, BinaryReader $br, $count)
{
    $data = [];

    for ($i = 0; $i < $count; $i++) {
        $data[] = sprintf('0x%04X', $br->readUInt16());
    }

    while (count($data) > 0) {
        $line = min(count($data), 8);

        $linedata = array_slice($data, 0, $line);
        $data = array_slice($data, $line);

        fprintf($out, "    .2byte %s\n", implode(', ', $linedata));
    }
}

$map->registerDumper('bin', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $rom->setPosition($rom->getPosition() + $arguments[0]);
    return null;
});

/**
 *
 * gBattleMoves:: @ 81FB12C
 * @ NONE
 * .byte EFFECT_HIT
 * .byte 0 @ power
 * .byte TYPE_NORMAL
 * .byte 0 @ accuracy
 * .byte 0 @ PP
 * .byte 0 @ secondary effect chance
 * .byte TARGET_SELECTED_POKEMON
 * .byte 0 @ priority
 * .4byte 0 @ misc. flags
 */

$map->registerDumper('BattleMoves', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    // TODO: read flags/constants

    global $moves;

    for ($i = 0; $i < count($moves); $i++) {
        fprintf($out, "    @ %s\n", $moves[$i]);

        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .byte %d @ power\n", $rom->readUInt8());
        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .byte %d @ accuracy\n", $rom->readUInt8());
        fprintf($out, "    .byte %d @ PP\n", $rom->readUInt8());
        fprintf($out, "    .byte %d @ secondary effect chance\n", $rom->readUInt8());
        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .byte %d @ priority\n", $rom->readUInt8());
        fprintf($out, "    .4byte %d @ misc. flags\n", $rom->readUInt32());
        fprintf($out, "\n");
    }
});

$map->registerDumper('DexOrder', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $species;

    for ($i = 0; $i < 411; $i++) {
        fprintf($out, "    .2byte %d @ %s\n", $rom->readUInt16(), $species[$i + 1]);
    }
});

$map->registerDumper('EggMoves', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $species, $moves;

    $out2 = false;
    while (true) {
        $move = $rom->readInt16();

        if ($move == -1) {
            fprintf($out, "\n");
            fprintf($out, "    .2byte -1\n");
            return;
        }

        if ($move >= 20000) {
            $s = $move - 20000;

            if ($out) {
                fprintf($out, "\n");
            }
            $out2 = true;
            fprintf($out, "    .egg_moves_begin SPECIES_%s\n", $species[$s]);
            continue;
        }

        fprintf($out, "    .2byte MOVE_%s\n", $moves[$move]);
    }
});

$map->registerDumper('Moves', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $moves;
    $count = $arguments[0];
    for ($i = 0; $i < $count; $i++) {
        $move = $rom->readUInt16();
        if (isset($moves[$move])) {
            fprintf($out, "    .2byte MOVE_%s\n", $moves[$move]);
        } else {
            fprintf($out, "    .2byte 0x%04X\n", $move);
        }
    }

});

$map->registerDumper('DexMap', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $species;

    for ($i = 0; $i < 411; $i++) {
        fprintf($out, "    .2byte %d @ %d\n", $rom->readUInt16(), $i + 1);
    }
});

$map->registerDumper('TrainerCard', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $sr = new StringReader();
    global $items, $moves, $species;

    fprintf($out, "    .byte %d @ Card Number\n", $rom->readUInt8());
    fprintf($out, "    .byte %d\n", $rom->readUInt8());
    fprintf($out, "    .byte %d @ Battle Type\n", $rom->readUInt8());
    fprintf($out, "    .byte %d @ Reward Item\n", $rom->readUInt8());

    for ($t = 0; $t < 3; $t++) {
        fprintf($out, "\n");
        fprintf($out, "    .string \"%s\", 11 @ Trainer Name\n", readFixedString($rom, $sr, 11));
        fprintf($out, "    .byte %d, %d, %d\n", $rom->readUInt8(), $rom->readUInt8(), $rom->readUInt8());

        for ($e = 0; $e < 3; $e++) {
            for ($i = 0; $i < 8; $i++) {
                fprintf($out, "    .2byte %d\n", $rom->readUInt16());
            }
        }
        fprintf($out, "    .byte %d, %d\n", $rom->readUInt8(), $rom->readUInt8());

        for ($p = 0; $p < 6; $p++) {
            fprintf($out, "\n");

            fprintf($out, "    .2byte SPECIES_%s\n", $species[$rom->readUInt16()]);

            $item = $rom->readUInt16();
            fprintf($out, "    .2byte ITEM_%s\n", $items[$item]);

            $ms = [];
            for ($n = 0; $n < 4; $n++) {
                $ms[] = $rom->readUInt16();
            }

            $ms = array_map(function ($m) use ($moves) {
                return 'MOVE_' . $moves[$m];
            }, $ms);
            fprintf($out, "    .2byte %s\n", implode(', ', $ms));

            readByteArray($out, $rom, 20);
            fprintf($out, "    .string \"%s\", 11 @ name\n", readFixedString($rom, $sr, 11));
            fprintf($out, "    .byte %d\n", $rom->readUInt8());

        }

    }

    fprintf($out, "\n");
    readByteArray($out, $rom, 4);

});

$map->registerDumper('Song', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    /*
         *
	.byte	8	@ NumTrks
	.byte	0	@ NumBlks
	.byte	bgm_kachi5_pri	@ Priority
	.byte	bgm_kachi5_rev	@ Reverb.

	.word	bgm_kachi5_grp
         */


    $tracks = $rom->readUInt8();
    fprintf($out, "    .byte %d @ NumTrks\n", $tracks);
    $blks = $rom->readUInt8();
    fprintf($out, "    .byte %d @ NumBlks\n", $blks);
    fprintf($out, "    .byte %d @ Priority\n", $rom->readUInt8());
    fprintf($out, "    .byte %d @ Reverb.\n", $rom->readUInt8());
    fprintf($out, "    .word %s\n", $map->register($rom->readUInt32(), '', 'VoiceGroup'));

    for ($i = 0; $i < $tracks; $i++) {
        fprintf($out, "    .word %s\n", $map->register($rom->readUInt32(), '', 'SongTrack'));
    }

});

$map->registerDumper('WindowTemplate', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;

    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    for ($n = 0; $n < $count; $n++) {
        $args = [];
        for ($i = 0; $i < 6; $i++) {
            $args[] = sprintf('0x%02X', $rom->readUInt8());
        }
        $args[] = sprintf('0x%04X', $rom->readUInt16());

        fprintf($out, "    window_template %s\n", implode(', ', $args));
    }
});

$map->registerDumper('WindowTemplateList', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    while (true) {
        $args = [];
        for ($i = 0; $i < 6; $i++) {
            $args[] = sprintf('0x%02X', $rom->readUInt8());
        }
        $args[] = sprintf('0x%04X', $rom->readUInt16());

        fprintf($out, "    window_template %s\n", implode(', ', $args));

        if ($args[0] == '0xFF') {
            break;
        }
    }
});

$map->registerDumper('RboxBar', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;

    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    for ($n = 0; $n < $count; $n++) {
        readByteArray($out, $rom, 3);
    }
});

$map->registerDumper('SongTrack', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    $lbl = $map->getLabel($rom->getPosition());
    if ($lbl !== 'SongTrack_86B7028') {
        // return false;
    }
    while (true) {
        $o = $rom->getPosition();
        $code = $rom->readUInt8();
        //  error_log(sprintf("%X: %02X", $o, $code ));


        if (($code >= 0x80 && $code <= (0x80 + 48)) ||
            ($code >= 0xce && $code <= 0xcf + 48)
        ) {
            $pos = $rom->getPosition();
            $note = $rom->readUInt8();
            if ($note > 127) {
                $rom->setPosition($pos);
                continue;
            }

            $pos = $rom->getPosition();
            $velocity = $rom->readUInt8();
            if ($velocity > 127) {
                $rom->setPosition($pos);
                continue;
            }
            continue;
        }

        switch ($code) {
            case 0xB2: // GOTO
            case 0xB3: // PATT
                $pos = $rom->getPosition();
                $addr = $rom->readUInt32();
                $map->register($addr, '', 'SongTrack');
                $map->register($pos, '', 'ptr');
                break;

            case 0xB1: // FINE
                // case 0xB4: // PEND
                return false;

            case 0xBB: // TEMPO
            case 0xBC: // KEYSHIFT
            case 0xBD: // VOICE
            case 0xBE: // VOL
            case 0xBF: // PAN
            case 0xC0: // BEND
            case 0xC1: // BENDR
            case 0xC2: // LFOS
            case 0xC4: // MOD
                $rom->readUInt8();
                break;

            case 0xCD: // XCMD
                $b = $rom->readBytes(4);
                //var_dump(sprintf("%X", $rom->getPosition()));
                assert(in_array(ord($b[0]), [0x08, 0x09]));
                if (!in_array(ord($b[2]), [0x08, 0x09])) {
                    $rom->setPosition($rom->getPosition() - 2);
                }
                break;

            default:
                break;

            case
            -1:
                error_log(sprintf("unknown code: %0X (%X)", $code, $rom->getPosition()));
                return false;
        }
    }
});

$map->registerUnboundedDumper('VoiceGroup', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    do {
        $pos = $rom->getPosition();
        $type = $rom->readUInt8();
        $bytes = [
            $type,
            $rom->readUInt8(),
            $rom->readUInt8(),
            $rom->readUInt8(),
        ];
        $addr = $rom->readUInt32();

        $done = false;
        switch ($type) {
            case 0x20:
                if ($bytes[1] == 60) {
                    assert($bytes[1] == 60);
                    assert($bytes[2] == 0);
                    assert($bytes[3] == 0);
                    $addr2 = $rom->readUInt32();
                    assert($addr2 == 0x00FF00FF);
                    fprintf($out, "    cry %s @ %X\n", $map->register($addr, '', ''), $pos);
                    $done = true;
                }
                break;

            case 0x30:
                if ($bytes[1] == 60) {
                    assert($bytes[1] == 60);
                    assert($bytes[2] == 0);
                    assert($bytes[3] == 0);
                    $addr2 = $rom->readUInt32();
                    assert($addr2 == 0x00FF00FF);
                    fprintf($out, "    cry2 %s @ %X\n", $map->register($addr, '', ''), $pos);
                    $done = true;
                }
                break;


            case 3:
            case 11:
                if ($bytes[1] == 60) {
                    assert($bytes[1] == 60);
                    assert($bytes[2] == 0);
                    assert($bytes[3] == 0);
                    /*
            .4byte \wave_samples_pointer
            .byte (\attack  & 0x7)
            .byte (\decay   & 0x7)
            .byte (\sustain & 0xF)
            .byte (\release & 0x7)
                    */

                    $attack = $rom->readUInt8();
                    $decay = $rom->readUInt8();
                    $sustain = $rom->readUInt8();
                    $release = $rom->readUInt8();

                    $fns = [
                        3 => 'voice_programmable_wave',
                        11 => 'voice_programmable_wave_alt'
                    ];
                    fprintf($out, "    %s %s, %d, %d, %d, %d\n", $fns[$type], $map->register($addr, '', 'ProgrammableWaveData'), $attack, $decay, $sustain, $release);
                    $done = true;
                }
                break;


            case 0:
            case 8:
            case 16:
                $bytes2 = [
                    $rom->readUInt8(),
                    $rom->readUInt8(),
                    $rom->readUInt8(),
                    $rom->readUInt8(),
                ];
                fprintf($out, "    .byte %s\n", implode(", ", $bytes));
                fprintf($out, "    .4byte %s\n", $map->register($addr, ''));
                fprintf($out, "    .byte %s\n", implode(", ", $bytes2));
                $done = true;
                break;


            case 0x40:
                assert($bytes[1] == 0);
                assert($bytes[2] == 0);
                assert($bytes[3] == 0);
                $addr2 = $rom->readUInt32();
                fprintf($out, "    voice_keysplit %s, %s\n", $map->register($addr, '', 'VoiceGroup'), $map->register($addr2, '', 'KeySplitTable'));
                $done = true;
                break;

            case 0x80:
                assert($bytes[1] == 0);
                assert($bytes[2] == 0);
                assert($bytes[3] == 0);
                $addr2 = $rom->readUInt32();
                assert($addr2 == 0);
                fprintf($out, "    voice_keysplit_all %s\n", $map->register($addr, '', 'VoiceGroup'));
                $done = true;
                break;

            /*
             *
.macro cry sample
.byte 0x20, 60, 0, 0
.4byte \sample
.byte 0xff, 0, 0xff, 0
.endm

             */

        }

        if ($done) {
            continue;
        }

        $bytes2 = [
            $rom->readUInt8(),
            $rom->readUInt8(),
            $rom->readUInt8(),
            $rom->readUInt8(),
        ];
        //error_log(sprintf("type: %02X", $type));
        fprintf($out, "    .byte %s\n", implode(", ", $bytes));
        fprintf($out, "    .4byte 0x%x\n", $addr);
        fprintf($out, "    .byte %s\n", implode(", ", $bytes2));
        fprintf($out, "\n");
    } while (!$map->hasLabel($rom->getPosition()));
});


$map->registerDumper('Trainers', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    $stringreader = new StringReader();

    for ($i = 0; $i < 743; $i++) {
        /*
         *
    @ NONE
        .byte 0 @ party type flags
        .byte TRAINER_CLASS_NAME_POKEMON_TRAINER_1
        .byte TRAINER_ENCOUNTER_MUSIC_MALE @ gender flag and encounter music
        .byte TRAINER_PIC_BRENDAN
        .string "$", 12
        .2byte ITEM_NONE, ITEM_NONE, ITEM_NONE, ITEM_NONE @ items
        .4byte FALSE @ is double battle
        .4byte 0x0 @ AI flags
        .4byte 0 @ party size
        .4byte NULL
         */

        $partyTypeFlags = $rom->readUInt8();
        fprintf($out, "    .byte 0x%X @ party type flags\n", $partyTypeFlags);
        fprintf($out, "    .byte 0x%X\n", $rom->readUInt8());
        fprintf($out, "    .byte 0x%X @ gender flag and encounter music\n", $rom->readUInt8());
        fprintf($out, "    .byte 0x%X\n", $rom->readUInt8());

        $pos = $rom->getPosition();

        $name = $stringreader->readLines($rom);
        fprintf($out, "    .string \"%s\", 12\n", $name[0]);


        $rom->setPosition($pos + 12);

        $items = [
            $rom->readUInt16(),
            $rom->readUInt16(),
            $rom->readUInt16(),
            $rom->readUInt16(),
        ];

        fprintf($out, "    .2byte %s @ items\n", implode(", ", $items));
        fprintf($out, "    .4byte %d @ is double battle\n", $rom->readUInt32() ? 'TRUE' : 'FALSE');
        fprintf($out, "    .4byte 0x%X @ AI flags\n", $rom->readUInt32());

        $partySize = $rom->readUInt32();
        $party = $rom->readUInt32();
        fprintf($out, "    .4byte %d @ party size\n", $partySize);
        fprintf($out, "    .4byte %s\n", $map->register($party, '', 'TrainerParty', $partySize, $partyTypeFlags));
        fprintf($out, "\n");
    }

});

$map->registerDumper('MapObjectGraphicsInfo', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    fprintf($out, "    .2byte 0x%04X @ tileTag\n", $rom->readUInt16());
    fprintf($out, "    .2byte 0x%04X @ paletteTag1\n", $rom->readUInt16());
    fprintf($out, "    .2byte 0x%04X @ paletteTag2\n", $rom->readUInt16());
    fprintf($out, "    .2byte %d @ size\n", $rom->readUInt16());
    fprintf($out, "    .2byte %d, %d @ size\n", $rom->readInt16(), $rom->readInt16());
    fprintf($out, "    .byte 0x%02X\n", $rom->readUInt8());
    fprintf($out, "    .byte 0x%02X @ tracks\n", $rom->readUInt8());
    fprintf($out, "    .2byte %d\n", $rom->readInt16());
    fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'OAM'));
    fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'MapObjectSubspriteTables'));
    fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'AnimCmds'));
    fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'GfxTable'));
    fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'AffineAnimCmds'));
});

$map->registerDumper('ptr', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    $typeA = 'Text';

    if (isset($arguments[1])) {
        $typeA = $arguments[1];
    }

    for ($i = 0; $i < $count; $i++) {
        $ptr1 = $rom->readUInt32();

        global $container;

        $name1 = null;
        if (isset($container['functionMap'][$ptr1 - 1])) {
            $name1 = $container['functionMap'][$ptr1 - 1]->name . '+1';
        }

        if ($ptr1 < 0x8000000) {
            $name1 = sprintf("0x%X", $ptr1);
        }

        if ($name1 == null) {
            $name1 = $map->register($ptr1, '', $typeA);
        }

        fprintf($out, "    .4byte %s\n", $name1);
    }
});

$map->registerDumper('twoptr', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    $typeA = 'Text';
    $typeB = 'Text';

    if (isset($arguments[1])) {
        $typeA = $arguments[1];
    }

    if (isset($arguments[2])) {
        $typeB = $arguments[2];
    }

    for ($i = 0; $i < $count; $i++) {
        $ptr1 = $rom->readUInt32();
        $ptr2 = $rom->readUInt32();

        global $container;

        $name1 = null;
        if (isset($container['functionMap'][$ptr1 - 1])) {
            $name1 = $container['functionMap'][$ptr1 - 1]->name . '+1';
        }

        $name2 = null;
        if (isset($container['functionMap'][$ptr2 - 1])) {
            $name2 = $container['functionMap'][$ptr2 - 1]->name . '+1';
        }

        if ($ptr1 < 0x8000000) {
            $name1 = sprintf("0x%X", $ptr1);
        }

        if ($ptr2 < 0x8000000) {
            $name2 = sprintf("0x%X", $ptr2);
        }

        if ($name1 == null) {
            $name1 = $map->register($ptr1, '', $typeA);
        }

        if ($name2 == null) {
            $name2 = $map->register($ptr2, '', $typeB);
        }

        fprintf($out, "    .4byte %s, %s\n", $name1, $name2);
    }
});

$map->registerDumper('light_level_transition_table', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    while (true) {
        $val = $rom->readUInt8();
        fprintf($out, "    .byte %d, %d, %d, %d\n", $val, $rom->readUInt8(), $rom->readUInt8(), $rom->readUInt8());
        fprintf($out, "    .4byte %s\n", getFn($rom->readUInt32()));
        fprintf($out, "    .4byte %s\n", getFn($rom->readUInt32()));

        if ($val == 0) {
            break;
        }
    }
});


$map->registerUnboundedDumper('GfxTable', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    while (true) {
        $pos = $rom->getPosition();

        $offset = $rom->readUInt32();
        $size = $rom->readUInt32();
        if ($offset < 0x8000000) {
            $rom->setPosition($pos);
            break;
        }

        fprintf($out, "    .4byte %s, 0x%X\n", $map->register($offset, '', 'GfxU', $size), $size);

        if ($map->hasLabel($rom->getPosition())) {
            break;
        }
    }
});

$map->registerDumper('npc_looping_info', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    while (true) {
        $offset = $rom->readUInt32();
        $a = $rom->readUInt8();
        $b = $rom->readUInt8();
        $c = $rom->readUInt8();
        $d = $rom->readUInt8();

        fprintf($out, "    .4byte %s\n", $map->register($offset, '', 'AnimCmds'));
        fprintf($out, "    .byte %d, %d, %d, %d\n", $a, $b, $c, $d);

        if ($offset == 0) {
            break;
        }
    }
});

$map->registerUnboundedDumper('MapObjectSubspriteTables', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    while (true) {
        $start = $rom->getPosition();
        $type = $rom->readUInt32();
        $offset = $rom->readUInt32();

        if ($type > 16) {
            $rom->setPosition($start);
            return;
        }

        fprintf($out, "    .4byte %d, %s\n", $type, $map->register($offset, '', 'MapObjectSubspriteTable', $type));

        if ($map->hasLabel($rom->getPosition())) {
            break;
        }
    }
});

/**
 *
 * struct Subsprite
 * {
 * u16 x;
 * u16 y;
 * u16 shape:2;
 * u16 size:2;
 * u16 tileOffset:10;
 * u16 priority:2;
 * };
 */
$map->registerDumper('MapObjectSubspriteTable', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];

    for ($i = 0; $i < $count; $i++) {
        readByteArray($out, $rom, 4);
    }
});

$map->registerDumper('TrainerParty', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];
    $partyTypeFlags = $arguments[1];

    if ($partyTypeFlags & ~0b11) {
        error_log('Unknown party flag');
    }

    global $species, $moves, $items;
    for ($i = 0; $i < $count; $i++) {
        /*
         *
	.2byte 0 @ IV (0-255)
	.2byte 16 @ level
	.2byte SPECIES_MAGNEMITE
	.2byte 0 @ padding
         */
        $pad = true;
        $iv = $rom->readUInt16();
        $level = $rom->readUInt16();
        $sp = $rom->readUInt16();
        fprintf($out, "    .2byte %d @ IV (0-255)\n", $iv);
        fprintf($out, "    .2byte %d @ level\n", $level);
        fprintf($out, "    .2byte SPECIES_%s\n", $species[$sp]);

        if ($partyTypeFlags & 1 << 1) {
            $pad = false;
            $item = $rom->readUInt16();
            fprintf($out, "    .2byte ITEM_%s\n", $items[$item]);
        }

        if ($partyTypeFlags & 1 << 0) {
            $ms = [];
            for ($n = 0; $n < 4; $n++) {
                $ms[] = $rom->readUInt16();
            }

            $ms = array_map(function ($m) use ($moves) {
                return 'MOVE_' . $moves[$m];
            }, $ms);
            fprintf($out, "    .2byte %s\n", implode(', ', $ms));
        }


        if ($pad) {
            $padding = $rom->readUInt16();
            fprintf($out, "    .2byte 0 @ padding\n");
        }

        fprintf($out, "\n");
    }
});

$map->registerDumper('Menus', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 65; $i++) {

        $addr = $rom->readUInt32();
        $count = $rom->readUInt32();
        fprintf($out, "    .4byte %s, %d\n", $map->register($addr, '', 'Menu', $count), $count);
    }
});

$map->registerDumper('Menu', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];
    for ($i = 0; $i < $count; $i++) {
        $addr = $rom->readUInt32();
        $fn = $rom->readUInt32();
        fprintf($out, "    .4byte %s, %d\n", $map->register($addr, '', 'Text'), $fn);
    }
});

$map->registerDumper('StringList', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];
    $size = $arguments[1];

    $stringReader = new StringReader();
    for ($i = 0; $i < $count; $i++) {
        $pos = $rom->getPosition();

        $string = $stringReader->readLines($rom)[0];
        while ($rom->getPosition() != $pos + $size) {
            $byte = $rom->readUInt8();
            if ($byte == 0xFF) {
                $string .= "$";
            }
        }

        fprintf($out, "    .string \"%s\", %d\n", $string, $size);
    }
});

$map->registerDumper('EasyChatTable', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 22; $i++) {
        $addr = $rom->readUInt32();
        $count1 = $rom->readUInt16();
        $count2 = $rom->readUInt16();
        fprintf($out, "    .4byte %s\n", $map->register($addr, '', 'EasyChatList', $count1));
        fprintf($out, "    .2byte %d, %d\n", $count1, $count2);
    }
});

$map->registerDumper('TrainerEyeTrainer', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 221; $i++) {
        $trainers = [];
        for ($n = 0; $n < 6; $n++) {
            $trainers[] = $rom->readUInt16();
        }
        $group = $rom->readUInt16();
        $map = $rom->readUInt16();

        fprintf($out, "    .2byte %s\n", implode(', ', $trainers));
        fprintf($out, "    .2byte %d, %d\n", $group, $map);
    }
});


$map->registerDumper('EasyChatList', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];

    $pos = $rom->getPosition();
    $test = $rom->readUInt32();
    $rom->setPosition($pos);

    if ($test > 0x8000000 && $test < 0x8D00000) {
        for ($i = 0; $i < $count; $i++) {
            $addr = $rom->readUInt32();
            $count1 = $rom->readUInt32();
            $count2 = $rom->readUInt32();
            fprintf($out, "    .4byte %s\n", $map->register($addr, '', 'Text'));
            fprintf($out, "    .4byte %d, %d\n", $count1, $count2);
        }
    } else {

        for ($i = 0; $i < $count; $i++) {
            $species = $rom->readUInt16();
            fprintf($out, "    .2byte %d\n", $species);
        }
    }
});


$map->registerDumper('BattleStringsTable', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 374; $i++) {
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'Text'));
    }

});

$map->registerDumper('ItemList', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {


    $stringreader = new StringReader();

    for ($i = 0; $i < 375; $i++) {
        /*
         * 	.string "????????$", 14
            .2byte ITEM_NONE
            .2byte 0 @ price
            .byte HOLD_EFFECT_NONE
            .byte 0
            .4byte gItemDescription_Dummy
            .byte 0
            .byte 0
            .byte POCKET_ITEMS
            .byte 4
            .4byte ItemUseOutOfBattle_CannotUse
            .4byte 0
            .4byte NULL
            .4byte 0
         */
        $pos = $rom->getPosition();
        $name = $stringreader->readLines($rom);
        $rom->setPosition($pos + 14);


        fprintf($out, "    .string \"%s\", 14\n", $name[0]);
        fprintf($out, "    .2byte %d\n", $rom->readUInt16());
        fprintf($out, "    .2byte %d @ price\n", $rom->readUInt16());
        fprintf($out, "    .byte %d\n", $rom->readUInt8()); // hold effect
        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), 'kItemDescription_' . $i, 'Text'));
        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .byte %d\n", $rom->readUInt8());

        $outOfBattleFn = 'NULL';
        $addr = $rom->readUInt32();
        if ($addr !== 0) {
            global $container;
            $fn = $container['functionMap'][$addr - 1];
            $outOfBattleFn = $fn->name;
        }
        fprintf($out, "    .4byte %s\n", $outOfBattleFn);
        fprintf($out, "    .4byte %d @ b\n", $rom->readUInt32());

        $battleFn = 'NULL';
        $addr = $rom->readUInt32();
        if ($addr !== 0) {
            global $container;
            $fn = $container['functionMap'][$addr - 1];
            $battleFn = $fn->name;
        }
        fprintf($out, "    .4byte %s\n", $battleFn);
        fprintf($out, "    .4byte %d @ d\n", $rom->readUInt32());
        fprintf($out, "\n");
    }

});

$map->registerDumper('SubspriteTable', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = array_shift($arguments);
    }

    for ($i = 0; $i < $count; $i++) {
        $c = $rom->readUInt32();
        $addr = $rom->readUInt32();
        fprintf($out, "    .4byte %d, %s\n", $c, $map->register($addr, '', 'Subsprite', $c));
    }
});

$map->registerDumper('Subsprite', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = array_shift($arguments);
    }

    readByteArray($out, $rom, $count * 4, 4);
});

$map->registerDumper('struct_6', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 4; $i++) {

        fprintf($out, "    .4byte %d, %d, %d, %d\n", $rom->readUInt32(), $rom->readUInt32(), $rom->readUInt32(), $rom->readUInt32());
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), ''));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), ''));
        fprintf($out, "    .4byte %d\n", $rom->readUInt32());
        fprintf($out, "\n");
    }
});

$map->registerDumper('struct_7', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    fprintf($out, "    .byte %d, %d, %d, %d, %d, %d, %d, %d\n", $rom->readUInt8(), $rom->readUInt8(), $rom->readUInt8(), $rom->readUInt8(), $rom->readUInt8(), $rom->readUInt8(), $rom->readUInt8(), $rom->readUInt8());
    fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), ''));
});

$map->registerDumper('AnSteps', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    while (true) {
        $start = $rom->getPosition();
        $addr = $rom->readUInt32();

        if ($addr < 0x8000000) {
            $rom->setPosition($start);
            break;
        }

        fprintf($out, "    .4byte %s\n", getFn($addr));

        if ($map->hasLabel($rom->getPosition())) {
            break;
        }
    }
});

$map->registerDumper('WildMonHeaders', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    global $mapNames;
    for ($i = 0; $i < 133; $i++) {
        /*
            map SafariZone_Northeast
            .2byte 0 @ padding
            .4byte SafariZone_Northeast_LandMonsInfo
            .4byte NULL
            .4byte SafariZone_Northeast_RockSmashMonsInfo
            .4byte NULL

            map SafariZone_Southwest
            .2byte 0 @ padding
            .4byte SafariZone_Southwest_LandMonsInfo
            .4byte SafariZone_Southwest_WaterMonsInfo
            .4byte NULL
            .4byte SafariZone_Southwest_FishingMonsInfo*/


        $g = $rom->readUInt8();
        $m = $rom->readUInt8();
        if ($g == 255) {
            $name = 'UNDEFINED';
        } else {
            $name = $mapNames[$g . '.' . $m];

        }
        $prefix = $name;
        if ($name == 'Map1_122') {
            $prefix = 'Map1_122' . chr($i - 26);
        }

        $p = $rom->readBytes(2);

        $land = $rom->readUInt32();
        $water = $rom->readUInt32();
        $rocksmash = $rom->readUInt32();
        $fishing = $rom->readUInt32();


        fprintf($out, "    map %s\n", $name);
        fprintf($out, "    .2byte 0 @ padding\n");
        fprintf($out, "    .4byte %s\n", $map->register($land, $prefix . '_LandMonsInfo', 'WildPokemonInfo'));
        fprintf($out, "    .4byte %s\n", $map->register($water, $prefix . '_WaterMonsInfo', 'WildPokemonInfo'));
        fprintf($out, "    .4byte %s\n", $map->register($rocksmash, $prefix . '_RockSmashMonsInfo', 'WildPokemonInfo'));
        fprintf($out, "    .4byte %s\n", $map->register($fishing, $prefix . '_FishingMonsInfo', 'WildPokemonInfo'));
        fprintf($out, "\n");
    }

});

$map->registerDumper('WildPokemonInfo', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $label = $map->getLabel($rom->getPosition());
    $label = preg_replace('/Info$/', '', $label);

    $count = $rom->readUInt32();
    fprintf($out, "    .4byte %d @ encounter rate\n", $count);
    fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), $label, 'WildPokemon'));

});

$map->registerDumper('WildPokemon', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    global $species;

    do {
        $min = $rom->readUInt8();
        $max = $rom->readUInt8();
        $s = $rom->readUInt16();
        fprintf($out, "    wild_mon %s, %d, %d\n", $species[$s], $min, $max);
    } while (!$map->hasLabel($rom->getPosition()));

});


$map->registerDumper('MailGraphics', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 12; $i++) {
        $a = $rom->readUInt32();
        $b = $rom->readUInt32();
        $c = $rom->readUInt32();

        $pa = $rom->readUInt16();
        $pb = $rom->readUInt16();
        $pc = $rom->readUInt16();
        $pd = $rom->readUInt16();


        fprintf($out, "    .4byte %s\n", $map->register($a, '', null));
        fprintf($out, "    .4byte %s\n", $map->register($b, '', null));
        fprintf($out, "    .4byte %s\n", $map->register($c, '', null));
        fprintf($out, "    .2byte 0x%04X\n", $pa);
        fprintf($out, "    .2byte 0x%04X\n", $pb);
        fprintf($out, "    .2byte 0x%04X\n", $pc);
        fprintf($out, "    .2byte 0x%04X\n", $pd);

        fwrite($out, "\n");
    }
});
/*
 *
gMailGraphicsTable:: @ 83E5634
	.4byte gMailPalette_Orange
	.4byte gMailTiles_Orange
	.4byte gMailTilemap_Orange
	.2byte 0x2C0
	.2byte 0
	.2byte 0x294A
	.2byte 0x6739
 */

$map->registerDumper('LevelUpLearnset', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $moves;

    while (true) {
        $val = $rom->readUInt16();


        if ($val == 0xFFFF) {
            fprintf($out, "    .2byte -1\n");
            return;
        }

        $level = $val >> 9;
        $move = $val & 0b111111111;

        fprintf($out, "    level_up_move %d, MOVE_%s\n", $level, $moves[$move]);
    }
});

$map->registerDumper('InGameTrade', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    $stringReader = new StringReader();

    global $species, $items;

    for ($i = 0; $i < 9; $i++) {
        $pos = $rom->getPosition();
        $mon = $stringReader->readLines($rom)[0];
        $rom->setPosition($pos + 12);
        $s = $rom->readUInt16();
        $ivs = [
            $rom->readUInt8(),
            $rom->readUInt8(),
            $rom->readUInt8(),
            $rom->readUInt8(),
            $rom->readUInt8(),
            $rom->readUInt8(),
        ];
        $ab = $rom->readUInt8();
        $rom->readBytes(3);
        $otid = $rom->readUInt32();

        $contest = [
            $rom->readUInt8(),
            $rom->readUInt8(),
            $rom->readUInt8(),
            $rom->readUInt8(),
            $rom->readUInt8(),
        ];
        $rom->readBytes(3);
        $personality = $rom->readUInt32();
        $heldItem = $rom->readUInt16();
        $mail = $rom->readInt8();

        $pos = $rom->getPosition();
        $trainer = $stringReader->readLines($rom)[0];
        $rom->setPosition($pos + 11);
        $gender = $rom->readInt8();
        $sheen = $rom->readInt8();
        $playerSpecies = $rom->readUInt16();
        $rom->readBytes(2);

        fprintf($out, "    @ %X\n", $pos);
        fprintf($out, "    .string \"%s\", 11 @ nickname\n", $mon);
        fwrite($out, "    .space 1\n");
        fprintf($out, "    .2byte SPECIES_%s @ NPC mon species\n", $species[$s]);
        fprintf($out, "    .byte %s @ IVs\n", implode(', ', $ivs));
        fprintf($out, "    .byte %s @ second ability\n", $ab == 1 ? 'TRUE' : 'FALSE');
        fwrite($out, "    .space 3\n");
        fprintf($out, "    .4byte %d @ OT ID\n", $otid);
        fprintf($out, "    .byte %s @ contest stats\n", implode(', ', $contest));
        fwrite($out, "    .space 3\n");
        fprintf($out, "    .4byte 0x%X @ personality value\n", $personality);

        fprintf($out, "    .2byte ITEM_%s @ held item\n", $items[$heldItem]);
        fprintf($out, "    .byte %d @ mail num\n", $mail);
        fprintf($out, "    .string \"%s\", 11 @ OT name\n", $trainer);
        fprintf($out, "    .byte %s @ @ OT gender\n", $gender == 1 ? 'FEMALE' : 'MALE');
        fprintf($out, "    .byte %d @ sheen\n", $sheen);
        fprintf($out, "    .2byte SPECIES_%s @ player mon species\n", $species[$playerSpecies]);
        fwrite($out, "    .space 2\n");

        fwrite($out, "\n");
    }
    /*
     *
**     .string "MAKIT$", 11 @ nickname
  **   .space 1
  **   .2byte SPECIES_MAKUHITA @ NPC mon species
 --  .byte 5 @ HP IV
 --  .byte 5 @ attack IV
 --  .byte 4 @ defense IV
 --  .byte 4 @ speed IV
 --  .byte 4 @ sp. attack IV
 --  .byte 4 @ sp. defense IV
 --    .byte TRUE @ second ability
   --  .space 3
   --  .4byte 49562 @ OT ID
  -- .byte 5 @ cool
  -- .byte 5 @ beauty
  -- .byte 5 @ cute
  -- .byte 5 @ smart
  -- .byte 30 @ tough
  --   .space 3
  --   .4byte 0x9C40 @ personality value
 --    .2byte ITEM_X_ATTACK @ held item
 --    .byte -1 @ mail num
 --    .string "ELYSSA$", 11 @ OT name
     .byte MALE @ OT gender
  --   .byte 10 @ sheen
  --   .2byte SPECIES_SLAKOTH @ player mon species
 --    .space 2
     */
});

$map->registerDumper('PokedexEntries', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    $stringReader = new StringReader();

    global $species;

    for ($i = 0; $i <= 386; $i++) {
        $pos = $rom->getPosition();
        $categoryName = $stringReader->readLines($rom)[0];
        $rom->setPosition($pos + 12);
        $height = $rom->readUInt16();
        $weight = $rom->readUInt16();
        $descriptionPage1 = $rom->readUInt32();
        $descriptionPage2 = $rom->readUInt32();
        $unused = $rom->readUInt16();
        $pokemonScale = $rom->readInt16();
        $pokemonOffset = $rom->readInt16();
        $trainerScale = $rom->readInt16();
        $trainerOffset = $rom->readInt16();
        $rom->readUInt16();

        $mon = sprintf("%03d", $i);

        $desc1 = $map->register($descriptionPage1, ''/*'kDexDescription_' . $mon . '_1'*/, 'Text');
        $desc2 = $map->register($descriptionPage2, ''/*'kDexDescription_' . $mon . '_2'*/, 'Text');

        /*
         * .string "UNBEKANNT$", 12
	pokedex_entry      Dummy,   0,    0, 256,   0,  256,  0
         */

        /*
         * .2byte \height @ in decimeters
	.2byte \weight @ in hectograms
	.4byte DexDescription_\pokemon_name\()_1
	.4byte DexDescription_\pokemon_name\()_2
	.2byte 0 @ unused
	.2byte \pokemon_scale
	.2byte \pokemon_offset
	.2byte \trainer_scale
	.2byte \trainer_offset
	.2byte 0 @ padding
         */
        fprintf($out, "    @ %X\n", $pos);
        fprintf($out, "    .string \"%s\", 12\n", $categoryName);
        // fprintf($out, "    pokedex_entry %s, %d, %d, %d, %d, %d, %d\n", $mon, $height, $weight, $pokemonScale, $pokemonOffset, $trainerScale, $trainerOffset);
        fprintf($out, "    .2byte %d @ in decimeters\n", $height, $weight, $pokemonScale, $pokemonOffset, $trainerScale, $trainerOffset);
        fprintf($out, "    .2byte %d @ in hectograms\n", $weight, $pokemonScale, $pokemonOffset, $trainerScale, $trainerOffset);
        fprintf($out, "    .4byte %s\n", $desc1);
        fprintf($out, "    .4byte %s\n", $desc2);
        fprintf($out, "    .2byte 0 @ unused\n");
        fprintf($out, "    .2byte %d\n", $pokemonScale, $pokemonOffset, $trainerScale, $trainerOffset);
        fprintf($out, "    .2byte %d\n", $pokemonOffset, $trainerScale, $trainerOffset);
        fprintf($out, "    .2byte %d\n", $trainerScale, $trainerOffset);
        fprintf($out, "    .2byte %d\n", $trainerOffset);
        fprintf($out, "    .2byte 0 @ padding\n\n");
    }

    //return false;
});

$map->registerDumper('unk1', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    $data = $rom->readUInt32();
    $fn = $rom->readUInt32();
    $fn2 = $rom->readUInt32();
    $count = $rom->readUInt16();
    $var2 = $rom->readUInt16();
    fprintf($out, "    .4byte %s\n", $map->register($data, '', 'unk2'));
    fprintf($out, "    .4byte %s\n", getFn($fn));
    fprintf($out, "    .4byte %s\n", getFn($fn2));
    fprintf($out, "    .2byte %d, %d\n", $count, $var2);
});

$map->registerUnboundedDumper('unk2', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    $count = 0;
    do {

        $str = $rom->readUInt32();
        $val = $rom->readInt32();
        fprintf($out, "    .4byte %s, %d\n", $map->register($str, '', 'Text'), $val);

        $count++;
    } while (!$map->hasLabel($rom->getPosition()));
    fprintf($out, "    @ count: %d\n", $count);
});


$map->registerDumper('Decorations', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    $stringreader = new StringReader();
    for ($i = 0; $i < $arguments[0]; $i++) {
        $type = $rom->readUInt8();
        $pos = $rom->getPosition();
        $name = $stringreader->readLines($rom)[0];
        $rom->setPosition($pos + 16);

        fprintf($out, "    .byte %d\n", $type);
        fprintf($out, "    .string \"%s\", 16\n", $name);
        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .2byte %d\n", $rom->readUInt16());
        $rom->readBytes(2);
        fprintf($out, "    .space 2\n");
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'Text'));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'Gfx'));
        fprintf($out, "\n");
    }
    /*
     *
	.byte DECOR_WAILMER_DOLL
	.string "WAILMER-PUPPE$", 16
	.byte 4
	.byte 5
	.byte 6
	.2byte 10000
	.space 2
	.4byte DecorDesc_WAILMER_DOLL
	.4byte DecorGfx_WAILMER_DOLL
     */
});
$map->registerDumper('FieldEffectScript', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    global $container;

    while (true) {
        $cmd = $rom->readUInt8();
        switch ($cmd) {
            case 0x01:
                $addr = $rom->readUInt32();
                fprintf($out, "    loadfadedpal 0x%X\n", $addr);
                break;

            case 0x02:
                $addr = $rom->readUInt32();
                fprintf($out, "    loadpal 0x%X\n", $addr);
                break;

            case 0x03:
                $addr = $rom->readUInt32();
                $fn = $container['functionMap'][$addr - 1];
                fprintf($out, "    callnative %s\n", $fn->name);
                break;

            case 0x04:
                fprintf($out, "    end\n");
                return;

            case 0x07:
                $pal = $rom->readUInt32();
                $addr = $rom->readUInt32();
                $fn = $container['functionMap'][$addr - 1];
                fprintf($out, "    loadfadedpal_callnative %s, %s\n", $map->register($pal, '', 'objpals'), $fn->name);
                break;

            default:
                var_dump($cmd);
                return;
        }
    }
});

$map->registerDumper('npc_palette', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    while (true) {
        $offset = $rom->readUInt32();
        $tag = $rom->readUInt16();
        $pad = $rom->readUInt16();

        fprintf($out, "    .4byte %s\n", $map->register($offset, ''));
        fprintf($out, "    .2byte 0x%04X\n", $tag);
        fprintf($out, "    .2byte 0\n");
        if ($offset == 0) {
            break;
        }
    }
});


$map->registerDumper('List', 'readList');
function readList(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $count = $arguments[0];
    $type = $arguments[1];
    $name = isset($arguments[2]) ? $arguments[2] : null;

    for ($i = 0; $i < $count; $i++) {
        if ($type == 'Fn') {
            global $container;

            $addr = $rom->readUInt32();
            if ($addr == 0) {
                fprintf($out, "    .4byte NULL\n");
            } else {
                $fn = $container['functionMap'][$addr - 1];
                fprintf($out, "    .4byte %s+1\n", $fn->name);
            }
            continue;
        }

        $addr = $rom->readUInt32();
        $addrName = '';
        if (is_null($name)) {
            $addrName = sprintf('%s_%X', $type, $addr);
        } else if (is_callable($name)) {
            $addrName = call_user_func($name, $i, $addr);
        }
        fprintf($out, "    .4byte %s\n", $map->register($addr, $addrName, $type));
    }
}

$map->registerDumper('MoveEffects', 'readMoveEffects');
function readMoveEffects(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    global $moveEffects;

    for ($i = 0; $i < 214; $i++) {
        $addr = $rom->readUInt32();
        fprintf($out, "    .4byte %s\n", $map->register($addr, 'MoveEffect_' . $moveEffects[$i], 'MoveEffect'));
    }
}


$map->registerDumper('MoveAnim', 'readMoveAnim');
function readMoveAnim(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{

    $todo = [];

    $fn = [];
    $sizes = [];

    $todo[] = $rom->getPosition();
    $maxPos = 0;

    $labels = [];

    $start = $rom->getPosition();

    while (count($todo) > 0) {
        $addr = array_shift($todo);
        if (isset($fn[$addr])) {
            continue;
        }

        if ($addr < $start) {
            continue;
        }

        $rom->setPosition($addr);
        $buffer = fopen('php://memory', 'w+');
        $lbs = [];
        readBattleAnimCommand($rom, $map, $buffer, $end, $lbs);
        $todo = array_merge($lbs, $todo);
        $labels = array_merge($lbs, $labels);
        rewind($buffer);

        $fn[$addr] = stream_get_contents($buffer);
        $sizes[$addr] = $rom->getPosition() - $addr;
        fclose($buffer);

        if (!$end) {
            $todo[] = $rom->getPosition();
        }
    }

    $labels = array_unique($labels);

    ksort($fn);
    $expectedAddr = $start;
    foreach ($fn as $addr => $value) {
        if ($expectedAddr !== $addr) {
            break;
        }
        if (in_array($addr, $labels)) {
            unset($labels[array_search($addr, $labels)]);
            fprintf($out, ".L%X:\n", $addr);
        }
        fwrite($out, $value);

        $expectedAddr += $sizes[$addr];
        $maxPos = $expectedAddr;
    }

    foreach ($labels as $label) {
        $map->register($label, sprintf('.L%X', $label), 'MoveAnim');
    }

    $rom->setPosition($maxPos);
}


function readBattleAnimCommand(BinaryReader $rom, RomMap2 $map, $out, &$ends, &$todo)
{
    $ends = true;

    $command = $rom->readUInt8();

    switch ($command) {
        case 0x00:
            $id = $rom->readUInt16();
            fprintf($out, "    loadsprite %d\n", $id);
            break;

        case 0x01:
            $id = $rom->readUInt16();
            fprintf($out, "    unloadsprite %d\n", $id);
            break;

        case 0x02:
            $addr = $rom->readUInt32();
            $name = $map->register($addr, sprintf('kBattleAnimSpriteTemplate_%X', $addr), 'SpriteTemplate');
            $priority = $rom->readUInt8();
            $count = $rom->readUInt8();
            $args = [];
            for ($i = 0; $i < $count; $i++) {
                $args[] = $rom->readInt16();
            }
            fprintf($out, "    sprite 0x%X, %d, %s\n", $addr, $priority, implode(", ", $args));
            break;

        case 0x03:
            $addr = $rom->readUInt32();
            $priority = $rom->readUInt8();
            $argCount = $rom->readUInt8();
            $args = [];
            for ($i = 0; $i < $argCount; $i++) {
                $args[] = $rom->readInt16();
            }
            global $container;
            $fn = $container['functionMap'][$addr - 1];
            fprintf($out, "    createtask %s, %d, %s\n", $fn->name, $priority, implode(", ", $args));
            break;

        case 0x04:
            $delay = $rom->readUInt8();
            fprintf($out, "    pause %d\n", $delay);
            break;

        case 0x05:
            fprintf($out, "    wait\n");
            break;

        case 0x08:
            fprintf($out, "    end\n");
            return;

        case 0x09:
            $id = $rom->readUInt16();
            fprintf($out, "    playse %d\n", $id);
            break;

        case 0x0A:
            $which = $rom->readUInt8();
            fprintf($out, "    monbg %d\n", $which);
            break;

        case 0x0B:
            $which = $rom->readUInt8();
            fprintf($out, "    clearmonbg %d\n", $which);
            break;

        case 0x0C:
            $val = $rom->readUInt16();
            fprintf($out, "    setalpha %d, %d\n", $val & 0b11111111, $val >> 8);
            break;

        case 0x0D:
            fprintf($out, "    blendoff\n");
            break;

        case 0x0E:
            $fn = $rom->readUInt32();
            fprintf($out, "    call %s\n", $map->register($fn, sprintf('MoveFn_%X', $fn), 'MoveAnim'));
            break;

        case 0x0F:
            fprintf($out, "    ret\n");
            return;

        case 0x10:
            $varnum = $rom->readUint8();
            $value = $rom->readInt16();
            fprintf($out, "    setvar %d, %d\n", $varnum, $value);
            break;

        case 0x11:
            $addr1 = $rom->readUInt32();
            $addr2 = $rom->readUInt32();
            $todo[] = $addr1;
            $todo[] = $addr2;
            fprintf($out, "    ifelse .L%X, .L%X\n", $addr1, $addr2);
            break;

        case 0x12:
            $cond = $rom->readUInt8();
            $addr = $rom->readUInt32();
            $todo[] = $addr;
            fprintf($out, "    jumpif %d, .L%X\n", $cond, $addr);
            break;

        case 0x13:
            $addr = $rom->readUInt32();

            if ($map->hasLabel($addr)) {
                $label = $map->getLabel($addr);
            } else {
                $todo[] = $addr;
                $label = sprintf(".L%X", $addr);
            }

            fprintf($out, "    jump %s\n", $label);
            return;

        case 0x14:
            $id = $rom->readUInt8();
            fprintf($out, "    fadetobg %d\n", $id);
            break;

        case 0x15:
            fprintf($out, "    restorebg\n");
            break;

        case 0x16:
            fprintf($out, "    waitbgfadeout\n");
            break;

        case 0x17:
            fprintf($out, "    waitbgfadein\n");
            break;

        case 0x18:
            $id = $rom->readUInt8();
            fprintf($out, "    changebg %d\n", $id);
            break;

        case 0x19:
            $id = $rom->readUInt16();
            $pan = $rom->readUInt8();
            fprintf($out, "    panse_19 %d, %d\n", $id, $pan);
            break;

        case 0x1A:
            $pan = $rom->readUInt8();
            fprintf($out, "    setpan %d\n", $pan);
            break;

        case 0x1B:
            $id = $rom->readUInt16();
            $panStart = $rom->readUInt8();
            $panEnd = $rom->readUInt8();
            $step = $rom->readUInt8();
            $delay = $rom->readUInt8();
            fprintf($out, "    panse_1B %d, %d, %d, %d, %d\n", $id, $panStart, $panEnd, $step, $delay);
            break;

        case 0x1C:
            $id = $rom->readUInt16();
            $pan = $rom->readUInt8();
            $delay = $rom->readUInt8();
            $count = $rom->readUInt8();
            fprintf($out, "    panse_1C %d, %d, %d, %d\n", $id, $pan, $delay, $count);
            break;

        case 0x1D:
            $id = $rom->readUInt16();
            $pan = $rom->readUInt8();
            $count = $rom->readUInt8();
            fprintf($out, "    panse_1D %d, %d, %d\n", $id, $pan, $count);
            break;

        case 0x1F:
            $addr = $rom->readUInt32();
            $argCount = $rom->readUInt8();
            $args = [];
            for ($i = 0; $i < $argCount; $i++) {
                $args[] = $rom->readUInt16();
            }
            global $container;
            $fn = $container['functionMap'][$addr - 1];
            fprintf($out, "    createtask_1F %s, %s\n", $fn->name, implode(", ", $args));
            break;

        case 0x20:
            fprintf($out, "    waitsound\n");
            break;

        case 0x21:
            $var = $rom->readUInt8();
            $value = $rom->readInt16();
            $addr = $rom->readUInt32();

            if ($map->hasLabel($addr)) {
                $label = $map->getLabel($addr);
            } else {
                $todo[] = $addr;
                $label = sprintf(".L%X", $addr);
            }
            fprintf($out, "    jumpvareq %d, %d, %s\n", $var, $value, $label);
            break;

        case 0x22:
            $a = $rom->readUInt8();
            fprintf($out, "    monbg_22 %d\n", $a);
            break;

        case 0x23:
            $a = $rom->readUInt8();
            fprintf($out, "    clearmonbg_23 %d\n", $a);
            break;

        case 0x24:
            $addr = $rom->readUInt32();
            $todo[] = $addr;
            fprintf($out, "    jumpunkcond .L%X\n", $addr);
            break;

        case 0x25:
            $a = $rom->readUInt8();
            $b = $rom->readUInt8();
            $c = $rom->readUInt8();
            fprintf($out, "    fadetobg_25 %d, %d, %d\n", $a, $b, $c);
            break;

        case 0x26:
            $id = $rom->readUInt16();
            $panStart = $rom->readUInt8();
            $panEnd = $rom->readUInt8();
            $step = $rom->readUInt8();
            $delay = $rom->readUInt8();
            fprintf($out, "    panse_26 %d, %d, %d, %d, %d\n", $id, $panStart, $panEnd, $step, $delay);
            break;

        case 0x27:
            $id = $rom->readUInt16();
            $panStart = $rom->readUInt8();
            $panEnd = $rom->readUInt8();
            $step = $rom->readUInt8();
            $delay = $rom->readUInt8();
            fprintf($out, "    panse_27 %d, %d, %d, %d, %d\n", $id, $panStart, $panEnd, $step, $delay);
            break;

        case 0x28:
            $unk = $rom->readUInt8();
            fprintf($out, "    monbgprio_28 %d\n", $unk);
            break;

        case 0x29:
            fprintf($out, "    monbgprio_29\n");
            break;

        case 0x2A:
            $unk = $rom->readUInt8();
            fprintf($out, "    monbgprio_2A %d\n", $unk);
            break;

        case 0x2B:
            $side = $rom->readUInt8();
            fprintf($out, "    invisible %d\n", $side);
            break;

        case 0x2C:
            $side = $rom->readUInt8();
            fprintf($out, "    visible %d\n", $side);
            break;

        case 0x2F:
            fprintf($out, "    stopsound\n");
            break;

        default:
            error_log(sprintf("unexpected opcode: %02X", $command));
            return;
    }

    $ends = false;
}


$map->registerDumper('MoveList', 'readMoveList');
function readMoveList(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $names = [];
    if (isset($arguments[0])) {
        $names = $arguments[0];
    }

    $i = 0;
    do {
        $addr = $rom->readUInt32();
        $name = sprintf('MoveAnim_%X', $addr);
        if (isset($names[$i])) {
            $name = 'Move_' . $names[$i];
        }
        fprintf($out, "    .4byte %s\n", $map->register($addr, $name, 'MoveAnim'));
        $i++;
    } while (!$map->hasLabel($rom->getPosition()));
}

$map->registerDumper('Braille', 'readBraille');
function readBraille(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $table = [
        0x01 => 'A',
        0x05 => 'B',
        0x03 => 'C',
        0x0B => 'D',
        0x09 => 'E',
        0x07 => 'F',
        0x0F => 'G',
        0x0D => 'H',
        0x06 => 'I',
        0x0E => 'J',
        0x11 => 'K',
        0x15 => 'L',
        0x13 => 'M',
        0x1B => 'N',
        0x19 => 'O',
        0x17 => 'P',
        0x1F => 'Q',
        0x1D => 'R',
        0x16 => 'S',
        0x1E => 'T',
        0x31 => 'U',
        0x35 => 'V',
        0x2E => 'W',
        0x33 => 'X',
        0x3B => 'Y',
        0x39 => 'Z',
        0x00 => ' ',
        0x04 => ',',
        0x2C => '.',
        0xFF => '$',
    ];

    $buffer = '';
    while (true) {
        $char = $rom->readUInt8();

        if (isset($table[$char])) {
            $buffer .= $table[$char];
        } else {
            $buffer .= sprintf('{%02X}', $char);
        }
        if ($char == 0xFF/* || strlen($buffer) > 2048*/) {
            break;
        }
    }


    fprintf($out, "    .braille \"%s\"\n", $buffer);
}

$map->registerDumper('MapObjectTemplates', 'readMapObjectTemplates');
function readMapObjectTemplates(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $m = $arguments[0];
    $count = $arguments[1];

    for ($i = 0; $i < $count; $i++) {

        $byte1 = $rom->readUint8();
        $word1 = $rom->readUInt16();
        $byte2 = $rom->readUint8();
        $byte3 = $rom->readUint8();
        $byte4 = $rom->readUint8();
        $byte5 = $rom->readUint8();
        $byte6 = $rom->readUint8();
        $byte7 = $rom->readUint8();
        $byte8 = $rom->readUint8();
        $byte9 = $rom->readUint8();
        $byte10 = $rom->readUint8();
        $byte11 = $rom->readUint8();
        $byte12 = $rom->readUint8();
        $byte13 = $rom->readUint8();
        $byte14 = $rom->readUint8();
        $script = $rom->readUInt32();
        $word2 = $rom->readUInt16();
        $byte15 = $rom->readUint8();
        $byte16 = $rom->readUint8();

        fprintf(
            $out,
            "    object_event %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %d, %d, %d\n",
            $byte1,
            $word1,
            $byte2, $byte3, $byte4, $byte5, $byte6, $byte7, $byte8, $byte9, $byte10, $byte11, $byte12, $byte13, $byte14,
            $map->register($script, sprintf('%s_EventScript_%x', $m, $script), 'EventScript'),
            $word2,
            $byte15, $byte16
        );
    }
}

$map->registerDumper('CoordEvents', 'readCoordEvents');
function readCoordEvents(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $m = $arguments[0];
    $count = $arguments[1];

    for ($i = 0; $i < $count; $i++) {

        $x = $rom->readInt16();
        $y = $rom->readInt16();
        $byte1 = $rom->readUInt8();
        $byte2 = $rom->readUInt8();
        $word1 = $rom->readUInt16();
        $word2 = $rom->readUInt16();
        $word3 = $rom->readUInt16();
        $script = $rom->readUInt32();

        fprintf(
            $out,
            "    coord_event %d, %d, %d, %d, %d, %d, %d, %s\n",
            $x, $y,
            $byte1, $byte2,
            $word1, $word2, $word3,
            $map->register($script, sprintf('%s_EventScript_%x', $m, $script), 'EventScript')
        );
    }
}

$map->registerDumper('Coords16', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    for ($i = 0; $i < $count; $i++) {
        fprintf($out, "    .2byte %d, %d\n", $rom->readInt16(), $rom->readInt16());
    }
});


$map->registerDumper('BgEvents', 'readBgEvents');
function readBgEvents(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $m = $arguments[0];
    $count = $arguments[1];

    for ($i = 0; $i < $count; $i++) {

        $x = $rom->readInt16();
        $y = $rom->readInt16();
        $byte = $rom->readInt8();
        $kind = $rom->readInt8();
        $word = $rom->readInt16();

        if ($kind < 5) {
            $other = $rom->readUInt32();

            fprintf(
                $out,
                "    bg_event %d, %d, %d, %d, %d, %s\n",
                $x, $y, $byte, $kind, $word,
                $map->register($other, sprintf('%s_EventScript_%x', $m, $other), 'EventScript')
            );
        } else {
            $other6 = $rom->readUInt16();
            $other7 = $rom->readUInt8();
            $other8 = $rom->readUInt8();

            fprintf(
                $out,
                "    bg_event %d, %d, %d, %d, %d, %d, %d, %d // FINDME\n",
                $x, $y, $byte, $kind, $word,
                $other6, $other7, $other8
            );
        }
    }
}


$map->registerUnboundedDumper('Movement', 'readMovement');
function readMovement(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    while (true) {
        $step = $rom->readUInt8();
        fprintf($out, "    .byte 0x%02X\n", $step);

        if ($step == 0xFE) {
            return;
        }

        if ($map->hasLabel($rom->getPosition())) {
            return;
        }
    }
}

$map->registerDumper('Pokemart', 'readPokemkart');
function readPokemkart(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    return;
    while (true) {
        $step = $rom->readUInt16();

        if ($step == 0) {
            return;
        }
    }
}

$map->registerDumper('Text', 'readText');
function readText(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $stringreader = new StringReader();
    $lines = $stringreader->readLines($rom, StringReader::LANGUAGE_ENGLISH);

    foreach ($lines as $buffer) {
        fprintf($out, "    .string \"%s\"\n", $buffer);
    }
}

$map->registerDumper('TextJP', 'readTextJP');
function readTextJP(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $stringreader = new StringReader();
    $lines = $stringreader->readLines($rom, StringReader::LANGUAGE_JAPANESE);

    foreach ($lines as $buffer) {
        fprintf($out, "    .string \"%s\"\n", $buffer);
    }
}


$map->registerDumper('MapScript2', 'readMapScript2');
function readMapScript2(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    while (true) {
        $tag = $rom->readUInt16();
        if ($tag == 0) {
            fprintf($out, "    .2byte 0\n");
            break;
        }

        $word = $rom->readUInt16();
        $addr = $rom->readUInt32();

        fprintf($out, "    map_script_2 0x%04X, %d, %s\n", $tag, $word, $map->register($addr, sprintf('EventScript_%X', $addr), 'EventScript'));
    }
}


$map->registerDumper('MapAttributes', 'readMapAttributes');
function readMapAttributes(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    if (isset($arguments[0])) {
        $label = $arguments[0];
    } else {
        $label = sprintf('Map_%X', $rom->getPosition());
    }

    $width = $rom->readUInt32();
    $height = $rom->readUInt32();
    $border = $rom->readUInt32();
    $blockData = $rom->readUInt32();
    $primaryTileset = $rom->readUInt32();
    $secondaryTileset = $rom->readUInt32();
    $borderWidth = $rom->readInt8();
    $borderHeight = $rom->readInt8();

    $pad = $rom->readUInt16();
    assert($pad == 0);

    if ($borderWidth == 0 && $borderHeight == 0) {
        $borderWidth = 2;
        $borderHeight = 2;
    }

    fprintf($out, "    .4byte %d, %d\n", $width, $height);
    fprintf($out, "    .4byte %s\n", $map->register($border, 'Border_' . $label, 'MapBlockdata', $borderWidth, $borderHeight));
    fprintf($out, "    .4byte %s\n", $map->register($blockData, 'MapBlockdata_' . $label, 'MapBlockdata', $width, $height));
    fprintf($out, "    .4byte %s\n", $map->register($primaryTileset, sprintf('Tileset_%X', $primaryTileset), 'Tileset'));
    fprintf($out, "    .4byte %s\n", $map->register($secondaryTileset, sprintf('Tileset_%X', $secondaryTileset), 'Tileset'));
    fprintf($out, "    .byte %d, %d\n", $borderWidth, $borderHeight);
    fprintf($out, "    .2byte 0\n");
}

$map->registerDumper('MapBlockdata', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $w = $arguments[0];
    $h = $arguments[1];
    $rom->setPosition($rom->getPosition() + ($w * $h) * 2);
});

$map->registerDumper('MapEvents', 'readMapEvents');
function readMapEvents(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $cObjects = $rom->readInt8();
    $cWarps = $rom->readInt8();
    $cEvents = $rom->readInt8();
    $cBgEvents = $rom->readInt8();

    $objects = $rom->readUInt32();
    $warps = $rom->readUInt32();
    $events = $rom->readUInt32();
    $bgEvents = $rom->readUInt32();

    fprintf(
        $out,
        "    map_events %s, %s, %s, %s\n",
        $map->register($objects, $arguments[0] . '_MapObjects', 'MapObjectTemplates', $arguments[0], $cObjects),
        $map->register($warps, $arguments[0] . '_MapWarps', 'WarpEvents', $arguments[0], $cWarps),
        $map->register($events, $arguments[0] . '_MapCoordEvents', 'CoordEvents', $arguments[0], $cEvents),
        $map->register($bgEvents, $arguments[0] . '_MapBGEvents', 'BgEvents', $arguments[0], $cBgEvents)
    );
}

$map->registerDumper('WarpEvents', 'readWarpEvents');
function readWarpEvents(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    global $mapNames;
    $count = $arguments[1];
    for ($i = 0; $i < $count; $i++) {
        $x = $rom->readUInt16();
        $y = $rom->readUInt16();
        $byte = $rom->readUInt8();
        $warp = $rom->readUInt8();

        $mapNo = $rom->readInt8();
        $bankNo = $rom->readInt8();

        if ($mapNo == 0x7F && $bankNo == 0x7F) {
            $map = 'NONE';
        } else {
            $map = $mapNames[$bankNo . '.' . $mapNo];
        }

        fprintf($out, "    warp_def %d, %d, %d, %d, %s\n", $x, $y, $byte, $warp, $map);
    }
}

$map->registerDumper('Tileset', 'readTileset');
function readTileset(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    /*
     *
gTileset_SecretBase:: @ 828721C
	.byte FALSE @ is compressed
	.byte FALSE @ is secondary tileset
	.2byte 0 @ padding
	.4byte gTilesetTiles_SecretBase
	.4byte gTilesetPalettes_SecretBase
	.4byte gMetatiles_SecretBasePrimary
	.4byte gMetatileAttributes_SecretBasePrimary
	.4byte NULL @ animation callback

	.align 2
     */

    $compressed = $rom->readUInt8();
    $secondary = $rom->readUInt8();
    $padding = $rom->readUInt16();
    assert($padding == 0);
    $tiles = $rom->readUInt32();
    $palettes = $rom->readUInt32();
    $meta = $rom->readUInt32();
    $animcallback = $rom->readUInt32();
    $metaattr = $rom->readUInt32();

    $animcb = 'NULL';

    if ($animcallback !== 0) {

        global $container;
        $fn = $container['functionMap'][$animcallback - 1];
        $animcb = $fn->name;
    }

    fprintf($out, "    .byte %d @ is compressed\n", $compressed);
    fprintf($out, "    .byte %d @ is secondary tileset\n", $secondary);
    fprintf($out, "    .2byte 0 @ padding\n");
    if ($compressed) {
        fprintf($out, "    .4byte %s\n", $map->register($tiles, sprintf('TilesetTiles_%X', $tiles), 'LzGfx'));
    } else {
        fprintf($out, "    .4byte %s\n", $map->register($tiles, '', 'TilesetTiles'));
    }
    fprintf($out, "    .4byte %s\n", $map->register($palettes, sprintf('TilesetPalettes_%X', $palettes), 'Pal', 0x200));
    fprintf($out, "    .4byte %s\n", $map->register($meta, '', 'Metatiles'));
    fprintf($out, "    .4byte %s\n", $animcb);
    fprintf($out, "    .4byte %s\n", $map->register($metaattr, '', 'MetatileAttributes'));

    //$rom->readBytes(0x18);
}

$map->registerUnboundedDumper('Metatiles', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 0;

    while (true) {
        $rom->readBytes(8);
        $count++;

        if ($map->hasLabel($rom->getPosition())) {
            break;
        }
    }
    fprintf($out, "    @ %d tiles\n", $count);
});

$map->registerUnboundedDumper('MetatileAttributes', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 0;

    while (true) {
        $rom->readUInt16();
        $count++;

        if ($map->hasLabel($rom->getPosition())) {
            break;
        }
    }

    fprintf($out, "    @ %d tiles\n", $count);
});


$map->registerDumper('EventScript', 'readEventScript');
function readEventScript(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    global $codes;
    $tmp = '';
    while (true) {
        $commandCode = $rom->readUInt8();

        if (!isset($codes[$commandCode])) {
            throw new Exception(sprintf('Unexpected opcode: %02X (0x%X)', $commandCode, $rom->getPosition()));
        }

        $cmd = $codes[$commandCode];

        $args = [];
        foreach ($cmd->param_types as $param) {
            switch ($param) {
                case 'TrainerbattleArgs':
                    $args[] = $type = $rom->readUInt8();
                    $args[] = $trainer = $rom->readUInt16();
                    $args[] = $word = $rom->readUInt16();
                    switch ($type) {
                        case 0:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            break;
                        case 1:
                        case 2:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkScript($map, $rom->readUInt32());
                            break;
                        case 3:
                            $args[] = mkString($map, $rom->readUInt32());
                            break;
                        case 4:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            break;
                        case 5:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            break;
                        case 6:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkScript($map, $rom->readUInt32());
                            break;
                        case 7:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            break;
                        case 8:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkScript($map, $rom->readUInt32());
                            break;
                        case 9:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            break;

                        default:
                            var_dump($type);
                        // die();
                    }
                    break;

                case 'byte':
                case 'Byte':
                    $args[] = $rom->readUInt8();
                    break;
                case 'word':
                case 'Word':
                    $args[] = $rom->readUInt16();
                    break;

                case 'long':
                    $args[] = $rom->readUInt32();
                    break;

                case 'MapId':
                    global $mapNames;
                    $bank = $rom->readInt8();
                    $mapNo = $rom->readInt8();

                    if ($mapNo == -1) {
                        $args[] = 'UNDEFINED';
                    } else {
                        $args[] = $mapNames[$bank . '.' . $mapNo];
                    }
                    break;

                case 'TextPointer':
                    $args[] = mkString($map, $rom->readUint32());
                    break;

                case 'EventScriptPointer':
                    $args[] = mkScript($map, $rom->readUint32());
                    break;

                case 'MovementPointer':
                    $offset = $rom->readUint32();
                    $args[] = $map->register($offset, sprintf('Movement_%X', $offset), 'Movement');
                    break;

                case 'PokemartPointer':
                    $offset = $rom->readUint32();
                    $args[] = $map->register($offset, 'Items_' . $offset, 'Pokemart');
                    break;

                case 'BraillePointer':
                    $offset = $rom->readUint32();
                    $args[] = $map->register($offset, 'Braille_' . $offset, 'Braille');
                    break;

                case 'Variable':
                    $args[] = sprintf('0x%04X', $rom->readUInt16());
                    break;
                case 'WordOrVariable':
                case 'Species':
                case 'Move':
                case 'Item':
                case 'Decoration':
                    $args[] = sprintf('0x%04X', $rom->readUInt16());
                    break;
                default:
                    error_log($param);
                    return;
            }
        }

        $tmp .= sprintf("    %s %s\n", $cmd->name, implode(", ", $args));
        fprintf($out, "    %s %s\n", $cmd->name, implode(", ", $args));


        if (isset($cmd->end)) {
            break;
        }
    }

}

function writeEventScript(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $o = $rom->getPosition();
    global $codes;
    $tmp = '';
    while (true) {
        $commandCode = $rom->readUInt8();

        if (!isset($codes[$commandCode])) {
            throw new Exception(sprintf('Unexpected opcode: %02X (0x%X, 0x%X)', $commandCode, $rom->getPosition(), $o));
        }

        $cmd = $codes[$commandCode];

        $args = [];
        foreach ($cmd->param_types as $param) {
            switch ($param) {
                case 'TrainerbattleArgs':
                    $args[] = $type = $rom->readUInt8();
                    $args[] = $trainer = $rom->readUInt16();
                    $args[] = $word = $rom->readUInt16();
                    switch ($type) {
                        case 0:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            break;
                        case 1:
                        case 2:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkScript($map, $rom->readUInt32());
                            break;
                        case 3:
                            $args[] = mkString($map, $rom->readUInt32());
                            break;
                        case 4:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            break;
                        case 5:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            break;
                        case 6:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkScript($map, $rom->readUInt32());
                            break;
                        case 7:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            break;
                        case 8:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkScript($map, $rom->readUInt32());
                            break;
                        case 9:
                            $args[] = mkString($map, $rom->readUInt32());
                            $args[] = mkString($map, $rom->readUInt32());
                            break;

                        default:
                            var_dump($type);
                        // die();
                    }
                    break;

                case 'byte':
                case 'Byte':
                    $args[] = $rom->readUInt8();
                    break;
                case 'word':
                case 'Word':
                    $args[] = $rom->readUInt16();
                    break;

                case 'long':
                    $args[] = $rom->readUInt32();
                    break;

                case 'MapId':
                    global $mapNames;
                    $bank = $rom->readInt8();
                    $mapNo = $rom->readInt8();

                    if ($mapNo == -1) {
                        $args[] = 'UNDEFINED';
                    } else {
                        $args[] = $mapNames[$bank . '.' . $mapNo];
                    }
                    break;

                case 'TextPointer':
                    $args[] = mkString($map, $rom->readUint32());
                    break;

                case 'EventScriptPointer':
                    $args[] = mkScript($map, $rom->readUint32());
                    break;

                case 'MovementPointer':
                    $offset = $rom->readUint32();
                    $args[] = $map->register($offset, sprintf('Movement_%X', $offset), 'Movement');
                    break;

                case 'PokemartPointer':
                    $offset = $rom->readUint32();
                    $args[] = $map->register($offset, 'Items_' . $offset, 'Pokemart');
                    break;

                case 'BraillePointer':
                    $offset = $rom->readUint32();
                    $args[] = $map->register($offset, 'Braille_' . $offset, 'Braille');
                    break;

                case 'Variable':
                    $args[] = sprintf('0x%04X', $rom->readUInt16());
                    break;
                case 'WordOrVariable':
                case 'Species':
                case 'Move':
                case 'Item':
                case 'Decoration':
                    $args[] = sprintf('0x%04X', $rom->readUInt16());
                    break;
                default:
                    error_log($param);
                    return;
            }
        }

        $tmp .= sprintf("    %s %s\n", $cmd->name, implode(", ", $args));
        fprintf($out, "    %s %s\n", $cmd->name, implode(", ", $args));


        if ($map->hasLabel($rom->getPosition())) {
            break;
        }


        if (isset($cmd->end)) {
            if ($cmd->name == 'jump') {
                $pos = $rom->getPosition();
                $next = $rom->readUInt8();
                $rom->setPosition($pos);
                if ($next == 0x02) {
                    continue;
                }
            }
            break;
        }
    }
}

$map->registerDumper('MapScripts', 'readMapScripts');
function readMapScripts(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $label = str_replace('_MapScripts', '_', $map->getLabel($rom->getPosition()));
    $n = 1;
    while (true) {
        $tag = $rom->readInt8();
        if ($tag == 0) {
            fprintf($out, "    .byte 0\n");
            break;
        }

        $addr = $rom->readUInt32();

        if (in_array($tag, array(1, 3, 5, 6, 7))) {
            $type = 'EventScript';
        } else {
            $type = 'MapScript2';
        }

        fprintf($out, "    map_script %d, %s\n", $tag, $map->register($addr, sprintf('%s_%d', $label . $type, $n), $type));
        $n++;
    }
}

$map->registerDumper('MapHeader', 'readMapHeader');
function readMapHeader(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $label = $arguments[0];

    $data = $rom->readUInt32();
    $events = $rom->readUInt32();
    $scripts = $rom->readUInt32();
    $connections = $rom->readUInt32();
    $music = $rom->readUInt16();
    $dataId = $rom->readUInt16();
    $name = $rom->readUInt8();
    $cave = $rom->readUInt8();
    $weather = $rom->readUInt8();
    $mapType = $rom->readUInt8();
    $filler_18 = $rom->readUInt8();
    $escapeRope = $rom->readUInt8();
    $flags = $rom->readUInt8();
    $battleType = $rom->readUInt8();


    $attr = $map->register($data, $label . '_MapAttributes', 'MapAttributes', $label);
    if ($attr != $label . '_MapAttributes') {
        //  error_log("Reuse of " . $attr);
    }

    fprintf($out, "    .4byte %s\n", $attr);
    fprintf($out, "    .4byte %s\n", $map->register($events, $label . '_MapEvents', 'MapEvents', $label));
    fprintf($out, "    .4byte %s\n", $map->register($scripts, $label . '_MapScripts', 'MapScripts', $label));
    fprintf($out, "    .4byte %s\n", $map->register($connections, $label . '_MapConnections', 'MapConnections', $label));
    fprintf($out, "    .2byte %d\n", $music);
    fprintf($out, "    .2byte %d\n", $dataId);
    fprintf($out, "    .byte %d\n", $name);
    fprintf($out, "    .byte %d\n", $cave);
    fprintf($out, "    .byte %d\n", $weather);
    fprintf($out, "    .byte %d\n", $mapType);
    fprintf($out, "    .byte %d\n", $filler_18);
    fprintf($out, "    .byte %d\n", $escapeRope);
    fprintf($out, "    .byte %d\n", $flags);
    fprintf($out, "    .byte %d\n", $battleType);
}

function readSpriteTemplate(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{

    /*
     *
	.macro spr_template tile_tag, pal_tag, oam, anims, images, affine_anims, callback
	.2byte \tile_tag
	.2byte \pal_tag
	.4byte \oam
	.4byte \anims
	.4byte \images
	.4byte \affine_anims
	.4byte \callback
	.endm
     */

    $tiletag = $rom->readUInt16();
    $paltag = $rom->readUInt16();

    $oam = $map->register($rom->readUInt32(), '', 'OAM');
    $anims = $map->register($rom->readUInt32(), '', 'AnimCmds');
    $images = $map->register($rom->readUInt32(), '', 'GfxTable');
    $affineAnims = $map->register($rom->readUInt32(), '', 'AffineAnimCmds');
    $callback = $rom->readUInt32();


    if ($callback === 0) {
        $callback = 'NULL';
    } else {

        global $container;

        $fn = $container['functionMap'][$callback - 1];
        $callback = $fn->name;
    }

    fprintf($out, "    spr_template %d, %d, %s, %s, %s, %s, %s\n", $tiletag, $paltag, $oam, $anims, $images, $affineAnims, $callback);
}

;
$map->registerDumper('SpriteTemplate', 'readSpriteTemplate');

$map->registerDumper('SpriteTemplates', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];

    for ($i = 0; $i < $count; $i++) {
        readSpriteTemplate($rom, $map, $out, $arguments);
    }
});


$map->registerDumper('Messages', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {


    for ($i = 0; $i < 10; $i++) {
        fprintf($out, "    .byte %d, %d, %d, %d\n", $rom->readUInt8(), $rom->readUInt8(), $rom->readUInt8(), $rom->readUInt8());
        fprintf($out, "    .4byte %s\n", mkString($map, $rom->readUInt32()));
        fprintf($out, "    .4byte %s\n", mkString($map, $rom->readUInt32()));
        fprintf($out, "    .4byte %s\n", mkString($map, $rom->readUInt32()));
        fprintf($out, "    .4byte %s\n", mkString($map, $rom->readUInt32()));
        fprintf($out, "    .4byte %s\n", mkString($map, $rom->readUInt32()));
    }
});

$map->registerDumper('Unk4', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $species;

    for ($i = 0; $i < 413; $i++) {
        $data = [];
        for ($n = 0; $n < 5; $n++) {
            $data[] = $rom->readUInt8();
        }
        $name = $species[$i + 1];
        if ($i == 411) {
            $name = 'UNOWN_EMARK';
        }
        if ($i == 412) {
            $name = 'UNOWN_QMARK';
        }
        if ($i == 200) {
            $name = 'UNOWN_A';
        }
        if ($i >= 251 && $i <= 275) {
            $name = $species[$i - 251 + 0x19d];
        }
        fprintf($out, "    .byte %s @ %s\n", implode(', ', $data), $name);
    }
});

$map->registerDumper('Unk5', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    // TODO: Trainer music?

    for ($i = 0; $i < 105; $i++) {
        readByteArray($out, $rom, 4);
    }
});

$map->registerDumper('TrainerMoney', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 105; $i++) {
        readByteArray($out, $rom, 4);
    }
});

$map->registerDumper('LocationDescription', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 19; $i++) {
        fprintf($out, "    .4byte %d\n", $rom->readUInt32());
        fprintf($out, "    .4byte %s\n", mkString($map, $rom->readUInt32()));
        fprintf($out, "    .4byte %s\n", mkString($map, $rom->readUInt32()));
    }
});
$map->registerDumper('VRAMConfig', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];

    for ($i = 0; $i < $count; $i++) {
        fprintf($out, "    .4byte 0x%X\n", $rom->readUInt32());
    }
});
$map->registerDumper('borg', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    $type = null;
    if (isset($arguments[1])) {
        $type = $arguments[1];
    }

    for ($i = 0; $i < $count; $i++) {
        fprintf($out, "    .4byte %s, 0x%X\n", $map->register($rom->readUInt32(), '', $type), $rom->readUInt32());
    }
});

$map->registerDumper('WordLists', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 27; $i++) {
        $suffix = (chr(ord('A') + $i - 1));
        if ($i == 0) {
            $suffix = 'UNK1';
        }
        $ptr = $rom->readUInt32();
        $count = $rom->readUInt32();
        $ptr = $map->register($ptr, 'WordList_' . $suffix, 'WordList', $count);
        fprintf($out, "    .4byte %s, %d\n", $ptr, $count);
    }
});
$map->registerDumper('WordList', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    global $species, $moves;

    for ($i = 0; $i < $count; $i++) {
        $var = $rom->readUInt16();
        if ($var == 0xFFFF) {
            $i++;
            $c = $rom->readUInt16();
            fprintf($out, "    ec_duplicates %d\n", $c);
            continue;
        }
        $group = $var >> 9;
        $word = $var & 0b111111111;

        switch ($group) {
            case 0:
                fprintf($out, "    ec_pokemon1 %s @ %04X\n", $species[$word], $var);
                break;
            case 0x15:
                fprintf($out, "    ec_pokemon2 %s @ %04X\n", $species[$word], $var);
                break;
            case 0x12:
                fprintf($out, "    ec_move1 %s @ %04X\n", $moves[$word], $var);
                break;
            case 0x13:
                fprintf($out, "    ec_move2 %s @ %04X\n", $moves[$word], $var);
                break;
            default:
                fprintf($out, "    .2byte (%d << 9) | %d @ %04X\n", $group, $word, $var);
                break;
        }
    }
});

$map->registerDumper('objtiles', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    for ($i = 0; $i < $count; $i++) {
        $ptr = $rom->readUInt32();
        $size = $rom->readUInt16();
        $tag = $rom->readUInt16();

        $ptr = $map->register($ptr, '', 'GfxU', $size);

        fprintf($out, "    obj_tiles %s, 0x%X, %d\n", $ptr, $size, $tag);
    }
});
$map->registerDumper('objpals', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    for ($i = 0; $i < $count; $i++) {
        $ptr = $rom->readUInt32();
        $tag = $rom->readUInt16();
        $pad = $rom->readUInt16();

        $ptr = $map->register($ptr, '', 'Pal', 0x20);

        fprintf($out, "    obj_pal %s, %d\n", $ptr, $tag);
    }
});
$map->registerDumper('struct_20', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    for ($i = 0; $i < $count; $i++) {
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), '', 'Text'));

        $bytes = [];
        for ($n = 0; $n < 8; $n++) {
            $bytes[] = $rom->readUInt8();
        }
        fprintf($out, "    .byte %s\n", implode(", ", $bytes));
    }
});

/**
 * @param BinaryReader $rom
 * @param RomMap2 $map
 * @param $out
 */
function readMEvent(BinaryReader $rom, RomMap2 $map, $out): void
{
    $cmd = $rom->readUInt32();
    $arg1 = $rom->readUInt32();
    $arg2 = $rom->readUInt32();
    if ($cmd == 3 || $cmd == 4) {
        $arg2 = $map->register($arg2, '', null);
        //  var_dump(sprintf("%d: %s", $cmd, $arg2));
    } else {
        $arg2 = $map->register($arg2, '', null);
        //  var_dump(sprintf("%d: %s", $cmd, $arg2));
    }
    fprintf($out, "    .4byte 0x%X, 0x%X, %s\n", $cmd, $arg1, $arg2);
}


$map->registerUnboundedDumper('mevent2', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    while (true) {
        readMEvent($rom, $map, $out);
        if ($map->hasLabel($rom->getPosition())) {
            break;
        }
    }
});

$map->registerDumper('mevent', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    for ($i = 0; $i < $count; $i++) {
        readMEvent($rom, $map, $out);
    }
});

$map->registerDumper('struct_19', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    for ($i = 0; $i < $count; $i++) {
        $bytes = [];
        for ($n = 0; $n < 4; $n++) {
            $bytes[] = $rom->readInt8();
        }

        $hwords = [
            $rom->readUInt16(),
            $rom->readUInt16(),
        ];
        fprintf($out, "    .byte %s\n", implode(", ", $bytes));
        fprintf($out, "    .2byte %s\n", implode(", ", $hwords));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), ''));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), ''));
    }
});

$map->registerDumper('bogo', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    for ($i = 0; $i < $count; $i++) {
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), ''));
        fprintf($out, "    .2byte 0x%04X, 0x%04X\n", $rom->readUInt16(), $rom->readUInt16());
    }
});

$map->registerDumper('MapConnections', 'readMapConnections');
function readMapConnections(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $label = $map->getLabel($rom->getPosition());

    $count = $rom->readUInt32();
    $list = $rom->readUInt32();
    fprintf($out, "    .4byte %d\n", $count);
    fprintf($out, "    .4byte %s\n", $map->register($list, $label . 'List', 'MapConnectionsList', $count));
}


$map->registerDumper('mail_maybe', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 12; $i++) {
        $bytes = [];
        for ($n = 0; $n < 8; $n++) {
            $bytes[] = $rom->readUInt8();
        }
        fprintf($out, "    .byte %s\n", implode(", ", $bytes));
        fprintf($out, "    .4byte %s\n", $map->register($rom->readUInt32(), ''));
    }
});

$map->registerDumper('fbox', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < $arguments[0]; $i++) {
        $offset = $rom->readUInt32();
        $bytes = [];
        for ($n = 0; $n < 8; $n++) {
            $bytes[] = $rom->readInt8();
        }
        fprintf($out, "    .4byte %s\n", getFn($offset));
        fprintf($out, "    .byte %s\n", implode(", ", $bytes));
    }
});

$map->registerDumper('NatureStatTable', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 25; $i++) {
        $bytes = [];
        for ($n = 0; $n < 5; $n++) {
            $bytes[] = $rom->readInt8();
        }
        fprintf($out, "    .byte %s\n", implode(", ", $bytes));
    }
});

$map->registerDumper('MoveTutorLearnsets', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 412; $i++) {
        $str = '';
        $val = $rom->readUInt16();
        while ($val > 0) {
            $str = ($val & 0b1) . $str;
            $val = $val >> 1;
        }
        fprintf($out, "    .2byte 0b%s\n", str_pad($str, 16, '0', STR_PAD_LEFT));
    }
});

$map->registerDumper('TMHMLearnsets', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 412; $i++) {
        $str = '';
        $val = $rom->readUInt64();
        while ($val > 0) {
            $str = ($val & 0b1) . $str;
            $val = $val >> 1;
        }
        fprintf($out, "    .8byte 0b%s\n", str_pad($str, 64, '0', STR_PAD_LEFT));
    }
});

$map->registerDumper('PokedexOrder', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $species;

    for ($i = 0; $i < $arguments[0]; $i++) {
        $s = $rom->readUInt16();
        fprintf($out, "    .2byte SPECIES_%s\n", $species[$s]);
    }
});

$map->registerDumper('ImgPalMap', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];

    for ($i = 0; $i < $count; $i++) {
        $img = $map->register($rom->readUInt32(), '', 'Gfx');
        $pal = $map->register($rom->readUInt32(), '', 'Pal');
        $map2 = $map->register($rom->readUInt32(), '', 'Map');
        fprintf($out, "    .4byte %s, %s, %s\n", $img, $pal, $map2);
    }
});

$map->registerDumper('ImgPal', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];

    for ($i = 0; $i < $count; $i++) {
        $img = $map->register($rom->readUInt32(), '', 'LzGfx');
        $pal = $map->register($rom->readUInt32(), '', 'Pal', 0x20);
        fprintf($out, "    .4byte %s, %s\n", $img, $pal);
    }
});

$map->registerDumper('ImgMapPal', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];

    for ($i = 0; $i < $count; $i++) {
        $img = $map->register($rom->readUInt32(), '', 'LzGfx');
        $map2 = $map->register($rom->readUInt32(), '', 'LzBin');
        $pal = $map->register($rom->readUInt32(), '', 'Pal', 0x20);
        fprintf($out, "    .4byte %s, %s, %s\n", $img, $map2, $pal);
    }
});

$map->registerDumper('ImgMapPal2', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];

    for ($i = 0; $i < $count; $i++) {
        $img = $map->register($rom->readUInt32(), '', 'LzGfx');
        $map2 = $map->register($rom->readUInt32(), '', 'LzBin');
        $pal = $map->register($rom->readUInt32(), '', 'Pal', 0x40);
        fprintf($out, "    .4byte %s, %s, %s\n", $img, $map2, $pal);
    }
});

$map->registerDumper('ImgMapPal3', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];

    for ($i = 0; $i < $count; $i++) {
        $var = $rom->readUInt32();
        $img = $map->register($rom->readUInt32(), '', 'LzGfx');
        $map2 = $map->register($rom->readUInt32(), '', 'LzBin');
        $pal = $map->register($rom->readUInt32(), '', 'Pal', 0x20);
        fprintf($out, "    .4byte 0x%04X, %s, %s, %s\n", $var, $img, $map2, $pal);
    }
});

$map->registerDumper('LzBinList', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < $arguments[0]; $i++) {
        $bin = $map->register($rom->readUInt32(), '', 'LzBin');
        fprintf($out, "    .4byte %s\n", $bin);
    }
});

$map->registerDumper('LzBin', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) use ($container) {
    /** @var \MarijnvdWerf\DisAsm\LZ\Decompressor $decomp */
    $decomp = $container['decompressor'];

    // TODO: incbin
    $decomp->decodeFile($rom);
});

$map->registerDumper('LzPal', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) use ($container) {
    /** @var \MarijnvdWerf\DisAsm\LZ\Decompressor $decomp */
    $decomp = $container['decompressor'];

    // TODO: incbin
    $decomp->decodeFile($rom);
});

$map->registerDumper('LzGfx', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) use ($container) {
    /** @var \MarijnvdWerf\DisAsm\LZ\Decompressor $decomp */
    $decomp = $container['decompressor'];

    // TODO: incbin
    $decomp->decodeFile($rom);
});

$map->registerDumper('GfxList', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {

    $count = $arguments[0];
    $size = $arguments[1];

    for ($i = 0; $i < $count; $i++) {
        $bin = $map->register($rom->readUInt32(), '', 'Gfx2', $size);
        fprintf($out, "    .4byte %s\n", $bin);
    }
});

$map->registerDumper('Gfx2', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    if (!is_numeric($arguments[0])) {
        var_dump(dechex($rom->getPosition()));
    }
    $rom->setPosition($rom->getPosition() + $arguments[0]);
    // TODO: incbin
    return null;
});

$map->registerDumper('Pal', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    if (!isset($arguments[0])) {
        error_log(sprintf('No palette size specfied for 0x%X', $rom->getPosition()));
        return null;
    }

    // TODO: incbin
    $rom->setPosition($rom->getPosition() + $arguments[0]);
    return null;
});

$map->registerDumper('GfxU', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) use ($container) {
    /** @var \MarijnvdWerf\DisAsm\LZ\Decompressor $decomp */
    $decomp = $container['decompressor'];
// TODO: incbin
    $pos = $rom->getPosition();

    try {
        $data = $decomp->decodeFile($rom);
        if (strlen($data) !== $arguments[0]) {
            error_log(sprintf('Error decompressing 0x%X', $pos));
        }
    } catch (Exception $e) {
        $rom->setPosition($rom->getPosition() + $arguments[0]);
    }

});


$map->registerDumper('EnemyMonElevation', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $species;

    for ($i = 0; $i < 412; $i++) {
        fprintf($out, "    .byte %d @ %s\n", $rom->readUInt8(), $species[$i]);
    }
});

$map->registerDumper('Unk6', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 51; $i++) {
        read2ByteArray($out, $rom, 4);
    }
});

$map->registerDumper('Unk7', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 100; $i++) {
        read2ByteArray($out, $rom, 2);
    }
});

$map->registerDumper('TypeEffectiveness', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $typeNames;

    while (true) {
        $a = $rom->readUInt8();
        $b = $rom->readUInt8();
        $val = $rom->readUInt8();


        $aStr = isset($typeNames[$a]) ? $typeNames[$a] : sprintf('0x%02X', $a);
        $bStr = isset($typeNames[$b]) ? $typeNames[$b] : sprintf('0x%02X', $b);

        fprintf($out, "    .byte %s, %s, %d\n", $aStr, $bStr, $val);

        if ($a == 0xFF) {
            break;
        }
    }
});

$map->registerDumper('lz', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $rom->readBytes($arguments[0]);
});

$map->registerDumper('WorldMap', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $rom->readBytes(0x294);
});

$map->registerDumper('CuteSketch', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 3200; $i++) {
        readByteArray($out, $rom, 3);
    }
});

$map->registerDumper('u8', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];
    $lineCount = 8;
    if (isset($arguments[1])) {
        $lineCount = $arguments[1];
    }

    readByteArray($out, $rom, $count, $lineCount);
});

$map->registerDumper('u16', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    read2ByteArray($out, $rom, $arguments[0]);
});

$map->registerDumper('SpindaSpots', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 4; $i++) {
        readByteArray($out, $rom, 2);
        for ($n = 0; $n < 17; $n++) {
            $str = '';
            $val = $rom->readUInt16();
            while ($val > 0) {
                $str = ($val & 0b1) . $str;
                $val = $val >> 1;
            }
            fprintf($out, "    .2byte 0b%s\n", str_pad($str, 16, '0', STR_PAD_LEFT));
        }

        fwrite($out, "\n");
    }
});


$map->registerDumper('unk10', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];
    for ($i = 0; $i < $count; $i++) {
        readByteArray($out, $rom, 2);
        read2ByteArray($out, $rom, 1);
    }
});


$map->registerDumper('unk11', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];
    for ($i = 0; $i < $count; $i++) {
        read2ByteArray($out, $rom, 1);
        readByteArray($out, $rom, 2);
        read2ByteArray($out, $rom, 1);
        readByteArray($out, $rom, 2);
    }
});


$map->registerDumper('unk12', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];
    for ($i = 0; $i < $count; $i++) {
        $window = $map->register($rom->readUInt32(), '', 'WindowTemplate');
        $gfx = $map->register($rom->readUInt32(), '', 'LzGfx');
        fprintf($out, "    .4byte %s, %s\n", $window, $gfx);
    }
});

$map->registerDumper('unk13', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = 1;
    if (isset($arguments[0])) {
        $count = $arguments[0];
    }

    for ($i = 0; $i < $count; $i++) {
        fprintf(
            $out,
            "    .4byte %d, %s, %s, %s\n",
            $rom->readUInt32(),
            $map->register($rom->readUInt32(), '', 'LzGfx'),
            $map->register($rom->readUInt32(), '', 'LzBin'),
            $map->register($rom->readUInt32(), '', 'Pal', 0x20)
        );
    }
});


$map->registerDumper('unk14', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    $count = $arguments[0];
    for ($i = 0; $i < $count; $i++) {
        $gfx = $map->register($rom->readUInt32(), '', 'Gfx2', 0x120);
        $pal = $map->register($rom->readUInt32(), '', 'Pal', 0x20);
        fprintf($out, "    .4byte %s, %s\n", $gfx, $pal);
    }
});

$dumpers2 = [
    's8' => ['readInt8', '%d', 'byte'],
    's16' => ['readInt16', '%d', '2byte'],
    's32' => ['readInt32', '%d', '4byte'],
    'u32' => ['readUInt32', '0x%08X', '4byte'],
];

foreach ($dumpers2 as $type => $attrs) {
    $method = $attrs[0];
    $format = $attrs[1];
    $size = $attrs[2];

    $map->registerDumper($type, function (BinaryReader $rom, RomMap2 $map, $out, $arguments) use ($method, $format, $size) {
        $count = $arguments[0];
        $lineCount = 8;
        if (isset($arguments[1])) {
            $lineCount = $arguments[1];
        }

        $data = [];

        for ($i = 0; $i < $count; $i++) {
            $data[] = sprintf($format, call_user_func([$rom, $method]));
        }

        while (count($data) > 0) {
            $line = min(count($data), $lineCount);

            $linedata = array_slice($data, 0, $line);
            $data = array_slice($data, $line);

            fprintf($out, "    .%s %s\n", $size, implode(', ', $linedata));
        }
    });
}

$map->registerDumper('KeypadIcon', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 13; $i++) {
        fprintf($out, "    .2byte 0x%0X\n", $rom->readUInt16());
        readByteArray($out, $rom, 2);
    }
});


$map->registerDumper('FontWidths', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    readByteArray($out, $rom, 512);
});

$map->registerDumper('FontWidthsJP', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    readByteArray($out, $rom, 280);
});

$map->registerDumper('CatchAreas', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 80; $i++) {
        readByteArray($out, $rom, 4);
    }
});

$map->registerDumper('RoamerLocations', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 26; $i++) {
        readByteArray($out, $rom, 7);
    }
});

$map->registerDumper('CreditsScript', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($i = 0; $i < 67; $i++) {
        $type = $rom->readUInt8();
        $arg1 = $rom->readUInt8();
        $arg2 = $rom->readUInt16();

        fprintf($out, "    .byte %d, %d\n", $type, $arg1);
        fprintf($out, "    .2byte %d\n", $arg2);
    }
});

$map->registerDumper('MonCoords', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    /*
     *

 struct MonCoords
 {
     // This would use a bitfield, but sub_8079F44
     // uses it as a u8 and casting won't match.
     u8 coords; // u8 x:4, y:4;
     u8 y_offset;
 };
     */

    for ($i = 0; $i < $arguments[0]; $i++) {
        $x = $rom->readUBits(4);
        $y = $rom->readUBits(4);
        $yOffset = $rom->readUInt8();
        $rom->readBytes(2);
        fprintf($out, "    coord %d, %d, %d\n", $x, $y, $yOffset);
    }
});

$map->registerDumper('ExperienceTable', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    for ($n = 0; $n < 8; $n++) {
        for ($i = 0; $i < 101; $i++) {
            fprintf($out, "    .4byte %d @ %d\n", $rom->readUInt32(), $i);
        }

        if ($n < 7) {
            fprintf($out, "\n");
        }
    }
});

$map->registerDumper('BattleTowerMons', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $species, $moves, $natures;
    for ($i = 0; $i < 300; $i++) {
        fprintf($out, "    .2byte SPECIES_%s\n", $species[$rom->readUInt16()]);
        fprintf($out, "    .byte %d @ item\n", $rom->readUInt8());
        fprintf($out, "    .byte 0x%02X @ team flags\n", $rom->readUInt8());

        $mvs = [];
        for ($n = 0; $n < 4; $n++) {
            $mvs[] = sprintf('MOVE_%s', $moves[$rom->readUInt16()]);
        }
        fprintf($out, "    .2byte %s\n", implode(', ', $mvs));

        fprintf($out, "    .byte %d\n", $rom->readUInt8());
        fprintf($out, "    .byte %d @ nature\n", $rom->readUInt8());
        $rom->readBytes(2);
        /*
         *
	.2byte SPECIES_LINOONE
	.byte BATTLE_TOWER_ITEM_RAWST_BERRY
	.byte 0x42 @ team flags
	.2byte MOVE_SLASH, MOVE_GROWL, MOVE_TAIL_WHIP, MOVE_SAND_ATTACK
	.byte F_EV_SPREAD_SPEED
	.byte NATURE_SERIOUS
	.2byte 0 @ padding
         */
    }
});


$typeNames = [
    0x00 => 'TYPE_NORMAL',
    0x01 => 'TYPE_FIGHTING',
    0x02 => 'TYPE_FLYING',
    0x03 => 'TYPE_POISON',
    0x04 => 'TYPE_GROUND',
    0x05 => 'TYPE_ROCK',
    0x06 => 'TYPE_BUG',
    0x07 => 'TYPE_GHOST',
    0x08 => 'TYPE_STEEL',
    0x09 => 'TYPE_MYSTERY',
    0x0a => 'TYPE_FIRE',
    0x0b => 'TYPE_WATER',
    0x0c => 'TYPE_GRASS',
    0x0d => 'TYPE_ELECTRIC',
    0x0e => 'TYPE_PSYCHIC',
    0x0f => 'TYPE_ICE',
    0x10 => 'TYPE_DRAGON',
    0x11 => 'TYPE_DARK',
];

$map->registerDumper('BaseStats', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $typeNames, $items, $abilities;

    $growths = [
        'GROWTH_MEDIUM_FAST',
        'GROWTH_ERRATIC',
        'GROWTH_FLUCTUATING',
        'GROWTH_MEDIUM_SLOW',
        'GROWTH_FAST',
        'GROWTH_SLOW'
    ];

    $colors = [
        'BODY_COLOR_RED',
        'BODY_COLOR_BLUE',
        'BODY_COLOR_YELLOW',
        'BODY_COLOR_GREEN',
        'BODY_COLOR_BLACK',
        'BODY_COLOR_BROWN',
        'BODY_COLOR_PURPLE',
        'BODY_COLOR_GRAY',
        'BODY_COLOR_WHITE',
        'BODY_COLOR_PINK',
    ];

    $eggGroups = [
        'EGG_GROUP_NONE',
        'EGG_GROUP_MONSTER',
        'EGG_GROUP_WATER_1',
        'EGG_GROUP_BUG',
        'EGG_GROUP_FLYING',
        'EGG_GROUP_FIELD',
        'EGG_GROUP_FAIRY',
        'EGG_GROUP_GRASS',
        'EGG_GROUP_HUMAN_LIKE',
        'EGG_GROUP_WATER_3',
        'EGG_GROUP_MINERAL',
        'EGG_GROUP_AMORPHOUS',
        'EGG_GROUP_WATER_2',
        'EGG_GROUP_DITTO',
        'EGG_GROUP_DRAGON',
        'EGG_GROUP_UNDISCOVERED',
    ];

    for ($i = 0; $i < 412; $i++) {
        /*
         * base_stats 45, 49, 49, 45, 65, 65
 -	.byte TYPE_GRASS
 -	.byte TYPE_POISON
 -	.byte 45
 -	.byte 64 @ base exp. yield
  	ev_yield 0, 0, 0, 0, 1, 0 TODO
 -	.2byte ITEM_NONE
 -	.2byte ITEM_NONE
 -	.byte 31 @ gender
 -	.byte 20 @ egg cycles
 -	.byte 70 @ base friendship
 -	.byte GROWTH_MEDIUM_SLOW
 - 	.byte EGG_GROUP_MONSTER
 - 	.byte EGG_GROUP_GRASS
 - 	.byte ABILITY_OVERGROW
 - 	.byte ABILITY_NONE
  	.byte 0 @ Safari Zone flee rate
  	.byte BODY_COLOR_GREEN
  	.2byte 0 @ padding
         */

        $stats = [];
        for ($n = 0; $n < 6; $n++) {
            $stats[] = $rom->readUInt8();
        }
        fprintf($out, "    base_stats %s\n", implode(', ', $stats));

        fprintf($out, "    .byte %s\n", $typeNames[$rom->readUInt8()]);
        fprintf($out, "    .byte %s\n", $typeNames[$rom->readUInt8()]);
        fprintf($out, "    .byte %d @ catch rate\n", $rom->readUInt8());
        fprintf($out, "    .byte %d @ base exp. yield\n", $rom->readUInt8());
        fprintf($out, "    ev_yield %d, %d, %d, %d, %d, %d\n", $rom->readBits(2), $rom->readBits(2), $rom->readBits(2), $rom->readBits(2), $rom->readBits(2), $rom->readBits(2));
        $rom->readBits(4); // ev_yield
        fprintf($out, "    .2byte %s\n", $items[$rom->readUInt16()]);
        fprintf($out, "    .2byte %s\n", $items[$rom->readUInt16()]);
        fprintf($out, "    .byte %s @ gender\n", $rom->readUInt8());
        fprintf($out, "    .byte %s @ egg cycles\n", $rom->readUInt8());
        fprintf($out, "    .byte %s @ base friendship\n", $rom->readUInt8());
        fprintf($out, "    .byte %s\n", $growths[$rom->readUInt8()]);
        fprintf($out, "    .byte %s\n", $eggGroups[$rom->readUInt8()]);
        fprintf($out, "    .byte %s\n", $eggGroups[$rom->readUInt8()]);
        fprintf($out, "    .byte %s\n", $abilities[$rom->readUInt8()]);
        fprintf($out, "    .byte %s\n", $abilities[$rom->readUInt8()]);
        fprintf($out, "    .byte %s @ Safari Zone flee rate\n", $rom->readUInt8());


        fprintf($out, "    .byte %2\$s%1\$s\n", $colors[$rom->readBits(7)], $rom->readBits(1) ? 'F_SUMMARY_SCREEN_FLIP_SPRITE | ' : '');

        fprintf($out, "    .2byte 0 @ padding\n");
        $rom->readBytes(2); // padding

        fprintf($out, "\n");
    }
});


$map->registerDumper('EvolutionTable', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) {
    global $species, $items;
    $mNames = [
        0x0001 => 'EVO_FRIENDSHIP',
        0x0002 => 'EVO_FRIENDSHIP_DAY',
        0x0003 => 'EVO_FRIENDSHIP_NIGHT',
        0x0004 => 'EVO_LEVEL',
        0x0005 => 'EVO_TRADE',
        0x0006 => 'EVO_TRADE_ITEM',
        0x0007 => 'EVO_ITEM',
        0x0008 => 'EVO_LEVEL_ATK_GT_DEF',
        0x0009 => 'EVO_LEVEL_ATK_EQ_DEF',
        0x000a => 'EVO_LEVEL_ATK_LT_DEF',
        0x000b => 'EVO_LEVEL_SILCOON',
        0x000c => 'EVO_LEVEL_CASCOON',
        0x000d => 'EVO_LEVEL_NINJASK',
        0x000e => 'EVO_LEVEL_SHEDINJA',
        0x000f => 'EVO_BEAUTY',
    ];

    for ($i = 0; $i < 412; $i++) {
        for ($p = 0; $p < 5; $p++) {
            $method = $rom->readUInt16();
            $parameter = $rom->readUInt16();
            $speciesNo = $rom->readUInt16();
            $padding = $rom->readUInt16();

            if ($method == 0) {
                break;
            }

            switch ($method) {
                default:
                    fprintf($out, "    evo_entry %s, %d, SPECIES_%s\n", $mNames[$method], $parameter, $species[$speciesNo]);
                    break;

                case 0x06: // EVO_TRADE_ITEM
                case 0x07: // EVO_ITEM
                    fprintf($out, "    evo_entry %s, ITEM_%s, SPECIES_%s\n", $mNames[$method], $items[$parameter], $species[$speciesNo]);
                    break;
            }
        }

        $fill = 5 - $p;
        if ($fill !== 0) {
            $rom->readBytes(8 * ($fill - 1));
            fprintf($out, "    empty_evo_entries %d\n", $fill);
        }

        fprintf($out, "\n");
    }
});

$map->registerDumper('MapConnectionsList', 'readMapConnectionsList');
function readMapConnectionsList(BinaryReader $rom, RomMap2 $map, $out, $arguments)
{
    $count = $arguments[0];

    $connections = [
        1 => 'down',
        2 => 'up',
        3 => 'left',
        4 => 'right',
        5 => 'dive',
        6 => 'emerge',
    ];

    for ($i = 0; $i < $count; $i++) {

        $direction = $rom->readUInt32();
        $offset = $rom->readInt32();
        $group = $rom->readUInt8();
        $map = $rom->readUInt8();
        $rom->readBytes(2);
        global $mapNames;
        fprintf($out, "    connection %s, %d, %s\n", $connections[$direction], $offset, $mapNames[$group . '.' . $map]);
    }

}

$map->registerDumper('ereader', function (BinaryReader $rom, RomMap2 $map, $out, $arguments) use ($species) {
global $species;
    while (true) {
        $cmd = $rom->readUInt8();
        switch ($cmd) {

            case 0x00:
                fprintf($out, "    nop\n");
                break;

            case 0x02:
                fprintf($out, "    end\n");
                return;

            case 0x03:
                fprintf($out, "    return\n");
                return;

            case 0x08:
                $out2 = $rom->readUInt8();
                fprintf($out, "    gotostd %d\n", $out2);
                return;

            case 0x09:
                $out2 = $rom->readUInt8();
                fprintf($out, "    callstd %d\n", $out2);
                break;

            case 0x0C:
                fprintf($out, "    gotoram\n");
                return;


            case 0x16:
                $destination = $rom->readUInt16();
                $value = $rom->readUInt16();
                fprintf($out, "    setvar 0x%04X, 0x%04X\n", $destination, $value);
                break;

            case 0x17:
                $destination = $rom->readUInt16();
                $value = $rom->readUInt16();
                fprintf($out, "    addvar 0x%04X, 0x%04X\n", $destination, $value);
                break;

            case 0x18:
                $destination = $rom->readUInt16();
                $value = $rom->readUInt16();
                fprintf($out, "    subvar 0x%04X, 0x%04X\n", $destination, $value);
                break;

            case 0x1A:
                $var = $rom->readUInt16();
                $value = $rom->readUInt16();
                fprintf($out, "    copyvarifnotzero 0x%04X, 0x%04X\n", $var, $value);
                break;

            case 0x21:
                $var = $rom->readUInt16();
                $value = $rom->readUInt16();
                fprintf($out, "    compare_var_to_value 0x%04X, 0x%04X\n", $var, $value);
                break;

            case 0x25:
                $var = $rom->readUInt16();
                fprintf($out, "    special 0x%04X\n", $var);
                break;

            case 0x26:
                $var = $rom->readUInt16();
                $value = $rom->readUInt16();
                fprintf($out, "    specialvar 0x%04X, %d\n", $var, $value);
                break;

            case 0x29:
                $var = $rom->readUInt16();
                fprintf($out, "    setflag 0x%04X\n", $var);
                break;

            case 0x2B:
                $var = $rom->readUInt16();
                fprintf($out, "    checkflag 0x%04X\n", $var);
                break;

            case 0x31:
                $fanfareNumber = $rom->readUInt16();
                fprintf($out, "    playfanfare 0x%04X\n", $fanfareNumber);
                break;

            case 0x32:
                fprintf($out, "    waitfanfare\n");
                break;

            case 0x46:
                $var = $rom->readUInt16();
                $value = $rom->readUInt16();
                fprintf($out, "    checkitemroom 0x%04X, 0x%04X\n", $var, $value);
                break;

            case 0x47:
                $index = $rom->readUInt16();
                $quantity = $rom->readUInt16();
                fprintf($out, "    checkitem 0x%04X, 0x%04X\n", $index, $quantity);
                break;

            case 0x5A:
                fprintf($out, "    faceplayer\n");
                break;

            case 0x66:
                fprintf($out, "    waitmsg\n");
                break;

            case 0x6A:
                fprintf($out, "    lock\n");
                break;

            case 0x6C:
                fprintf($out, "    release\n");
                break;

            case 0x6D:
                fprintf($out, "    waitkeypress\n");
                break;

            case 0x7A:
                $flagA = $rom->readUInt16();
                fprintf($out, "    giveegg SPECIES_%s\n", $species[$flagA]);
                break;

            case 0xCD:
                $flagA = $rom->readUInt16();
                fprintf($out, "    setobedience %d\n", $flagA);
                break;

            case 0x7b:
                global $moves;
                $var = $rom->readUInt8();
                $flagA = $rom->readUInt8();
                $value = $rom->readUInt16();
                fprintf($out, "    setpokemove %d, %d, MOVE_%s\n", $var, $flagA, $moves[$value]);
                break;

            case 0x80:
                $out2 = $rom->readUInt8();
                $item = $rom->readUInt16();
                fprintf($out, "    getitemname %d, 0x%04X\n", $out2, $item);
                break;

            case 0x83:
                $out2 = $rom->readUInt8();
                $input = $rom->readUInt16();
                fprintf($out, "    getnumberstring %d, 0x%04X\n", $out2, $input);
                break;


            case 0xB8:
                $ptr = $rom->readUInt32();
                $ptr = $map->register($ptr, '', 'ereader');
                fprintf($out, "    setvaddress %s\n", $ptr);
                break;

            case 0xBA:
                $ptr = $rom->readUInt32();
                $ptr = $map->register($ptr, '', 'ereader');
                fprintf($out, "    vcall %s\n", $ptr);
                break;

            case 0xBB:
                $arg = $rom->readUInt8();
                $ptr = $rom->readUInt32();
                $ptr = $map->register($ptr, '', 'ereader');
                fprintf($out, "    vcall_if %d, %s\n", $arg, $ptr);
                break;

            case 0xBD:
                $ptr = $rom->readUInt32();
                $ptr = $map->register($ptr, '', 'Text');
                fprintf($out, "    vmessage %s\n", $ptr);
                break;

            case 0xd2:
                $slot = $rom->readUInt16();
                $location = $rom->readUInt8();
                fprintf($out, "    setcatchlocale %d, %d\n", $slot, $location);
                break;

            default:
                fprintf($out, "    @ 0x%02X\n", $cmd);
                printf("    @ 0x%02X\n", $cmd);
                $rom->setPosition($rom->getPosition() - 1);
                return;
        }
    }
});


<?php


use PhpBinaryReader\BinaryReader;

class StringReader
{

    private $jTable =
        [
            0x00 => '　',
            0x01 => 'あ',
            0x02 => 'い',
            0x03 => 'う',
            0x04 => 'え',
            0x05 => 'お',
            0x06 => 'か',
            0x07 => 'き',
            0x08 => 'く',
            0x09 => 'け',
            0x0A => 'こ',
            0x0B => 'さ',
            0x0C => 'し',
            0x0D => 'す',
            0x0E => 'せ',
            0x0F => 'そ',
            0x10 => 'た',
            0x11 => 'ち',
            0x12 => 'つ',
            0x13 => 'て',
            0x14 => 'と',
            0x15 => 'な',
            0x16 => 'に',
            0x17 => 'ぬ',
            0x18 => 'ね',
            0x19 => 'の',
            0x1A => 'は',
            0x1B => 'ひ',
            0x1C => 'ふ',
            0x1D => 'へ',
            0x1E => 'ほ',
            0x1F => 'ま',
            0x20 => 'み',
            0x21 => 'む',
            0x22 => 'め',
            0x23 => 'も',
            0x24 => 'や',
            0x25 => 'ゆ',
            0x26 => 'よ',
            0x27 => 'ら',
            0x28 => 'り',
            0x29 => 'る',
            0x2A => 'れ',
            0x2B => 'ろ',
            0x2C => 'わ',
            0x2D => 'を',
            0x2E => 'ん',
            0x2F => 'ぁ',
            0x30 => 'ぃ',
            0x31 => 'ぅ',
            0x32 => 'ぇ',
            0x33 => 'ぉ',
            0x34 => 'ゃ',
            0x35 => 'ゅ',
            0x36 => 'ょ',
            0x37 => 'が',
            0x38 => 'ぎ',
            0x39 => 'ぐ',
            0x3A => 'げ',
            0x3B => 'ご',
            0x3C => 'ざ',
            0x3D => 'じ',
            0x3E => 'ず',
            0x3F => 'ぜ',
            0x40 => 'ぞ',
            0x41 => 'だ',
            0x42 => 'ぢ',
            0x43 => 'づ',
            0x44 => 'で',
            0x45 => 'ど',
            0x46 => 'ば',
            0x47 => 'び',
            0x48 => 'ぶ',
            0x49 => 'べ',
            0x4A => 'ぼ',
            0x4B => 'ぱ',
            0x4C => 'ぴ',
            0x4D => 'ぷ',
            0x4E => 'ぺ',
            0x4F => 'ぽ',
            0x50 => 'っ',
            0x51 => 'ア',
            0x52 => 'イ',
            0x53 => 'ウ',
            0x54 => 'エ',
            0x55 => 'オ',
            0x56 => 'カ',
            0x57 => 'キ',
            0x58 => 'ク',
            0x59 => 'ケ',
            0x5A => 'コ',
            0x5B => 'サ',
            0x5C => 'シ',
            0x5D => 'ス',
            0x5E => 'セ',
            0x5F => 'ソ',
            0x60 => 'タ',
            0x61 => 'チ',
            0x62 => 'ツ',
            0x63 => 'テ',
            0x64 => 'ト',
            0x65 => 'ナ',
            0x66 => 'ニ',
            0x67 => 'ヌ',
            0x68 => 'ネ',
            0x69 => 'ノ',
            0x6A => 'ハ',
            0x6B => 'ヒ',
            0x6C => 'フ',
            0x6D => 'ヘ',
            0x6E => 'ホ',
            0x6F => 'マ',
            0x70 => 'ミ',
            0x71 => 'ム',
            0x72 => 'メ',
            0x73 => 'モ',
            0x74 => 'ヤ',
            0x75 => 'ユ',
            0x76 => 'ヨ',
            0x77 => 'ラ',
            0x78 => 'リ',
            0x79 => 'ル',
            0x7A => 'レ',
            0x7B => 'ロ',
            0x7C => 'ワ',
            0x7D => 'ヲ',
            0x7E => 'ン',
            0x7F => 'ァ',
            0x80 => 'ィ',
            0x81 => 'ゥ',
            0x82 => 'ェ',
            0x83 => 'ォ',
            0x84 => 'ャ',
            0x85 => 'ュ',
            0x86 => 'ョ',
            0x87 => 'ガ',
            0x88 => 'ギ',
            0x89 => 'グ',
            0x8A => 'ゲ',
            0x8B => 'ゴ',
            0x8C => 'ザ',
            0x8D => 'ジ',
            0x8E => 'ズ',
            0x8F => 'ゼ',
            0x90 => 'ゾ',
            0x91 => 'ダ',
            0x92 => 'ヂ',
            0x93 => 'ヅ',
            0x94 => 'デ',
            0x95 => 'ド',
            0x96 => 'バ',
            0x97 => 'ビ',
            0x98 => 'ブ',
            0x99 => 'ベ',
            0x9A => 'ボ',
            0x9B => 'パ',
            0x9C => 'ピ',
            0x9D => 'プ',
            0x9E => 'ペ',
            0x9F => 'ポ',
            0xA0 => 'ッ',
            0xAB => '！',
            0xAC => '？',
            0xAD => '。',
            0xAE => 'ー',
            0xAF => '·',
            0xB0 => '‥',
            0xF0 => ':',
            0xFF => "$",

            0xFA => '\l',
            0xFB => '\p',
            0xFE => '\n',
        ];

    private $table = [
        0x00 => ' ',
        0x01 => 'À',
        0x02 => 'Á',
        0x03 => 'Â',
        0x04 => 'Ç',
        0x05 => 'È',
        0x06 => 'É',
        0x07 => 'Ê',
        0x08 => 'Ë',
        0x09 => 'Ì',
        0x0B => 'Î',
        0x0C => 'Ï',
        0x0D => 'Ò',
        0x0E => 'Ó',
        0x0F => 'Ô',
        0x10 => 'Œ',
        0x11 => 'Ù',
        0x12 => 'Ú',
        0x13 => 'Û',
        0x14 => 'Ñ',
        0x15 => 'ß',
        0x16 => 'à',
        0x17 => 'á',
        0x19 => 'ç',
        0x1A => 'è',
        0x1B => 'é',
        0x1C => 'ê',
        0x1D => 'ë',
        0x1E => 'ì',
        0x20 => 'î',
        0x21 => 'ï',
        0x22 => 'ò',
        0x23 => 'ó',
        0x24 => 'ô',
        0x25 => 'œ',
        0x26 => 'ù',
        0x27 => 'ú',
        0x28 => 'û',
        0x29 => 'ñ',
        0x2A => 'º',
        0x2B => 'ª',
        0x2C => '{SUPER_ER}',
        0x2D => '&',
        0x2E => '+',
        0x34 => '{LV}',
        0x35 => '=',
        0x51 => '¿',
        0x52 => '¡',
        0x53 => '{PK}',
        0x5A => 'Í',
        0x5B => '%',
        0x5C => '(',
        0x5D => ')',
        0x68 => 'â',
        0x6F => 'í',
        0x79 => '{UP_ARROW}',
        0x7A => '{DOWN_ARROW}',
        0x7B => '{LEFT_ARROW}',
        0x7C => '{RIGHT_ARROW}',
        0xA1 => '0',
        0xA2 => '1',
        0xA3 => '2',
        0xA4 => '3',
        0xA5 => '4',
        0xA6 => '5',
        0xA7 => '6',
        0xA8 => '7',
        0xA9 => '8',
        0xAA => '9',
        0xAB => '!',
        0xAC => '?',
        0xAD => '.',
        0xAE => '-',
        0xB0 => '…',
        0xB1 => '“',
        0xB2 => '”',
        0xB3 => '‘',
        0xB4 => '’',
        0xB5 => '♂',
        0xB6 => '♀',
        0xB7 => '¥',
        0xB8 => ',',
        0xB9 => '×',
        0xBA => '/',
        0xBB => 'A',
        0xBC => 'B',
        0xBD => 'C',
        0xBE => 'D',
        0xBF => 'E',
        0xC0 => 'F',
        0xC1 => 'G',
        0xC2 => 'H',
        0xC3 => 'I',
        0xC4 => 'J',
        0xC5 => 'K',
        0xC6 => 'L',
        0xC7 => 'M',
        0xC8 => 'N',
        0xC9 => 'O',
        0xCA => 'P',
        0xCB => 'Q',
        0xCC => 'R',
        0xCD => 'S',
        0xCE => 'T',
        0xCF => 'U',
        0xD0 => 'V',
        0xD1 => 'W',
        0xD2 => 'X',
        0xD3 => 'Y',
        0xD4 => 'Z',
        0xD5 => 'a',
        0xD6 => 'b',
        0xD7 => 'c',
        0xD8 => 'd',
        0xD9 => 'e',
        0xDA => 'f',
        0xDB => 'g',
        0xDC => 'h',
        0xDD => 'i',
        0xDE => 'j',
        0xDF => 'k',
        0xE0 => 'l',
        0xE1 => 'm',
        0xE2 => 'n',
        0xE3 => 'o',
        0xE4 => 'p',
        0xE5 => 'q',
        0xE6 => 'r',
        0xE7 => 's',
        0xE8 => 't',
        0xE9 => 'u',
        0xEA => 'v',
        0xEB => 'w',
        0xEC => 'x',
        0xED => 'y',
        0xEE => 'z',
        0xEF => '▶',
        0xF0 => ':',
        0xF1 => 'Ä',
        0xF2 => 'Ö',
        0xF3 => 'Ü',
        0xF4 => 'ä',
        0xF5 => 'ö',
        0xF6 => 'ü',
        0xFF => "$",

        0xFA => '\l',
        0xFB => '\p',
        0xFE => '\n',
    ];

    private $fdCodes = [
        0x01 => 'PLAYER',
        0x02 => 'STR_VAR_1',
        0x03 => 'STR_VAR_2',
        0x04 => 'STR_VAR_3',
        0x05 => 'KUN',
        0x06 => 'RIVAL',
        0x07 => 'VERSION',
        0x08 => 'EVIL_TEAM',
        0x09 => 'GOOD_TEAM',
        0x0A => 'EVIL_LEADER',
        0x0B => 'GOOD_LEADER',
        0x0C => 'EVIL_LEGENDARY',
        0x0D => 'GOOD_LEGENDARY',
    ];

    private $f8codes = [
        0x00 => 'A_BUTTON',
        0x01 => 'B_BUTTON',
        0x02 => 'L_BUTTON',
        0x03 => 'R_BUTTON',
        0x04 => 'START_BUTTON',
        0x05 => 'SELECT_BUTTON',

        0x06 => 'DPAD_UP',
        0x07 => 'DPAD_DOWN',
        0x08 => 'DPAD_LEFT',
        0x09 => 'DPAD_RIGHT',
        0x0A => 'DPAD_VERTICAL',
        0x0B => 'DPAD_HORIZONTAL',
        0x0C => 'DPAD',
    ];

    private $fcCodes = [
        0x00 => 'NAME_END',
        0x01 => 'COLOR',
        0x02 => 'HIGHLIGHT',
        0x03 => 'SHADOW',
        0x04 => 'COLOR_HIGHLIGHT_SHADOW',
        0x05 => 'PALETTE',
        0x06 => 'SIZE',
        0x07 => 'UNKNOWN_7',
        0x08 => 'PAUSE',
        0x09 => 'PAUSE_UNTIL_PRESS',
        0x0A => 'UNKNOWN_A',
        0x0B => 'PLAY_BGM',
        0x0C => 'ESCAPE',
        0x0D => 'SHIFT_TEXT',
        0x0E => 'UNKNOWN_E',
        0x0F => 'UNKNOWN_F',
        0x10 => 'PLAY_SE',
        0x11 => 'CLEAR',
        0x12 => 'SKIP',
        0x13 => 'CLEAR_TO',
        0x14 => 'UNKNOWN_14',
        0x15 => 'JPN',
        0x16 => 'ENG',
        0x17 => 'PAUSE_MUSIC',
        0x18 => 'RESUME_MUSIC',
    ];

    private $colors = [
        0x00 => 'TRANSPARENT',
        0x01 => 'DARK_GREY',
        0x02 => 'RED',
        0x03 => 'GREEN',
        0x04 => 'BLUE',
        0x05 => 'YELLOW',
        0x06 => 'CYAN',
        0x07 => 'MAGENTA',
        0x08 => 'LIGHT_GREY',
        0x09 => 'BLACK',
        0x0A => 'BLACK2', // duplicate of black?
        0x0B => 'SILVER',
        0x0C => 'WHITE',
        0x0D => 'SKY_BLUE',
        0x0E => 'LIGHT_BLUE',
        0x0F => 'WHITE2', // duplicate of white?
    ];

    private static function readCode(BinaryReader $br)
    {
        return $br->readUInt8();
    }

    const LANGUAGE_JAPANESE = 1;
    const LANGUAGE_ENGLISH = 2;

    public function readLines(BinaryReader $br, $language = self::LANGUAGE_ENGLISH)
    {
        $lines = [];

        $characterTable = $this->table;
        if ($language == self::LANGUAGE_JAPANESE) {
            $characterTable = [];
            foreach ($this->table as $ord => $char) {
                $characterTable[$ord] = $char;
            }
            foreach ($this->jTable as $ord => $char) {
                $characterTable[$ord] = $char;
            }
        }

        $currentLine = '';
        while (true) {
            $code = self::readCode($br);
            if ($code === false) {
                break;
            }

            if ($code == 0xFF) {
                $currentLine .= '$';
                break;
            }

            if ($code == 0xF8) {
                $position = $br->getPosition();
                $nextCode = self::readCode($br);
                if (isset($this->f8codes[$nextCode])) {
                    $currentLine .= '{BUTTON ' . $this->f8codes[$nextCode] . '}';
                    continue;
                }

                $br->setPosition($position);
            }

            if ($code == 0xFD) {
                $nextCode = self::readCode($br);
                if (isset($this->fdCodes[$nextCode])) {
                    $currentLine .= '{' . $this->fdCodes[$nextCode] . '}';
                } else {
                    $currentLine .= sprintf('{STRING %d}', $nextCode);
                }
                continue;
            }

            if ($code == 0x55) {
                $position = $br->getPosition();
                $a56 = self::readCode($br);
                $a57 = self::readCode($br);
                $a58 = self::readCode($br);
                $a59 = self::readCode($br);

                if ($a56 == 0x56 && $a57 == 0x57 && $a58 == 0x58 && $a59 == 0x59) {
                    $currentLine .= '{POKEBLOCK}';
                    continue;
                } else {
                    $br->setPosition($position);
                }
            }

            if ($code == 0x53) {
                $position = $br->getPosition();
                $next = self::readCode($br);

                if ($next == 0x54) {
                    $currentLine .= '{PKMN}';
                    continue;
                } else {
                    $br->setPosition($position);
                }
            }

            if ($code == 0xF9) {
                $symbolNames = [
                    0 => 'UP',
                    1 => 'DOWN',
                    2 => 'LEFT',
                    3 => 'RIGHT',
                    4 => 'DPAD',
                    5 => 'LV',
                    6 => 'PP',
                    7 => 'ID',
                    8 => 'NO',
                    9 => '_',
                    10 => 'NO_1',
                    11 => 'NO_2',
                    12 => 'NO_3',
                    13 => 'NO_4',
                    14 => 'NO_5',
                    15 => 'NO_6',
                    16 => 'NO_7',
                    17 => 'NO_8',
                    18 => 'NO_9',
                    19 => 'LB',
                    20 => 'RB',
                    21 => 'CIRCLE_DOT',
                    22 => 'TRIANGLE',
                    23 => 'CROSS',
                ];
                $next = self::readCode($br);
                if (!isset($symbolNames[$next])) {
                    $currentLine .= sprintf('{SYMBOL %d}', $next);
                } else {
                    $currentLine .= sprintf('{SYMBOL_%s}', $symbolNames[$next]);
                }
                continue;
            }

            if ($code == 0xFC) {
                $position = $br->getPosition();
                $next = self::readCode($br);

                $success = true;
                switch ($next) {
                    case 0x07:
                    case 0x0C:
                    case 0x0D:
                    case 0x0E:
                    case 0x0F:
                    case 0x12:
                    case 0x15:
                    case 0x16:
                    case 0x17:
                    case 0x18:
                        // unk
                    case 0x00:
                    case 0x09:
                    case 0x0A:
                        // fn()
                        $currentLine .= sprintf('{%s}', $this->fcCodes[$next]);
                        break;

                    case 0x01:
                    case 0x02:
                    case 0x03:
                        // fn(color)
                        $color = self::readCode($br);
                        $currentLine .= sprintf('{%s %s}', $this->fcCodes[$next], $this->colors[$color]);
                        break;

                    case 0x04:
                        // fn(color, color, color)
                        $colorA = self::readCode($br);
                        $colorB = self::readCode($br);
                        $colorC = self::readCode($br);
                        $currentLine .= sprintf('{%s %s %s %s}', $this->fcCodes[$next], $this->colors[$colorA], $this->colors[$colorB], $this->colors[$colorC]);
                        break;

                    case 0x05:
                    case 0x06:
                    case 0x08:
                    case 0x11:
                    case 0x13:
                    case 0x14: // UNKNOWN
                        // fn(int)
                        $parameter = self::readCode($br);
                        $currentLine .= sprintf('{%s %d}', $this->fcCodes[$next], $parameter);
                        break;

                    case 0x0B:
                    case 0x10:
                        // fn(int, int)
                        $parameterA = self::readCode($br);
                        $parameterB = self::readCode($br);
                        $currentLine .= sprintf('{%s 0x%02X 0x%02X}', $this->fcCodes[$next], $parameterA, $parameterB);
                        break;

                    default:
                        $success = false;
                        break;
                }

                if ($success) {
                    continue;
                }

                $br->setPosition($position);
            }

            if (isset($characterTable[$code])) {
                $currentLine .= $characterTable[$code];
            } else {
                $currentLine .= sprintf('{0x%02X}', $code);
            }

            if (in_array($code, [0xFA, 0xFB, 0xFE])) {
                $lines[] = $currentLine;
                $currentLine = '';
            }
        }

        $lines[] = $currentLine;

        return $lines;
    }
}

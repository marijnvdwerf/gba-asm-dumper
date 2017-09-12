<?php


$_GET['project'] = 'firered';
require 'new/_common.php';

$labels = file('/Users/Marijn/temp/fr-rodata.txt', FILE_IGNORE_NEW_LINES);
$diffs = file('/Users/Marijn/temp/changes-fr11.txt', FILE_IGNORE_NEW_LINES);

error_log("parsing diff");
$diffs2 = [];
foreach ($diffs as $line2) {
    $line = explode(' ', $line2);
    $diffs2[intval($line[0], 16) + 0x8000000] = $line2;
}

error_log("parsing labels");
$labels2 = [];
foreach ($labels as $line) {
    $line = explode(' ', $line);
    $labels2[intval($line[0], 16)] = $line[2];
}

ksort($labels2);

$labelKeys = array_keys($labels2);
$labelKeys[] = 0;
sort($labelKeys);

function getLabel($offset)
{
    global $labels2, $labelKeys;

    $index = NearestValue::array_numeric_sorted_nearest($labelKeys, $offset, 1);
    return $index;
}

class NearestValue
{
    private static $ARRAY_NEAREST_DEFAULT = 0;
    private static $ARRAY_NEAREST_LOWER = 1;
    private static $ARRAY_NEAREST_HIGHER = 2;

    /**
     * Finds nearest value in numeric array. Can be used in loops.
     * Array needs to be non-assocative and sorted.
     *
     * @param array $array
     * @param int $value
     * @param int $method ARRAY_NEAREST_DEFAULT|ARRAY_NEAREST_LOWER|ARRAY_NEAREST_HIGHER
     * @return int
     */
    public static function array_numeric_sorted_nearest($array, $value, $method = 0)
    {
        $count = count($array);
        if ($count == 0) {
            return null;
        }
        $div_step = 2;
        $index = ceil($count / $div_step);
        $best_index = null;
        $best_score = null;
        $direction = null;
        $indexes_checked = Array();
        while (true) {
            if (isset($indexes_checked[$index])) {
                break;
            }
            $curr_key = $array[$index] ?? null;
            if ($curr_key === null) {
                break;
            }
            $indexes_checked[$index] = true;
            // perfect match, nothing else to do
            if ($curr_key == $value) {
                return $curr_key;
            }
            $prev_key = $array[$index - 1] ?? null;
            $next_key = $array[$index + 1] ?? null;
            switch ($method) {
                default:
                case self::$ARRAY_NEAREST_DEFAULT:
                    $curr_score = abs($curr_key - $value);
                    $prev_score = $prev_key !== null ? abs($prev_key - $value) : null;
                    $next_score = $next_key !== null ? abs($next_key - $value) : null;
                    if ($prev_score === null) {
                        $direction = 1;
                    } else if ($next_score === null) {
                        break 2;
                    } else {
                        $direction = $next_score < $prev_score ? 1 : -1;
                    }
                    break;
                case self::$ARRAY_NEAREST_LOWER:
                    $curr_score = $curr_key - $value;
                    if ($curr_score > 0) {
                        $curr_score = null;
                    } else {
                        $curr_score = abs($curr_score);
                    }
                    if ($curr_score === null) {
                        $direction = -1;
                    } else {
                        $direction = 1;
                    }
                    break;
                case self::$ARRAY_NEAREST_HIGHER:
                    $curr_score = $curr_key - $value;
                    if ($curr_score < 0) {
                        $curr_score = null;
                    }
                    if ($curr_score === null) {
                        $direction = 1;
                    } else {
                        $direction = -1;
                    }
                    break;
            }
            if (($curr_score !== null) && ($curr_score < $best_score) || ($best_score === null)) {
                $best_index = $index;
                $best_score = $curr_score;
            }
            $div_step *= 2;
            $index += $direction * ceil($count / $div_step);
        }
        return $array[$best_index];
    }
}

error_log("sorting diff");
$labelDiffs = [];
foreach ($diffs2 as $addr => $diff) {
    $lbl = getLabel($addr);
    if ($lbl == 0) {
        continue;
    }

    if (!isset($labelDiffs[$lbl])) {
        $labelDiffs[$lbl] = [];
    }

    $labelDiffs[$lbl][] = $diff;
}


$map = [];
foreach ($labels2 as $addr => $label) {
    if (isset($labelDiffs[$addr])) {
        if (strpos($label, 'Song_') === 0) {
            // break;
        }
        $map[$addr] = $labelDiffs[$addr];
    }
}

//arsort($map);

$addresses = [];
$br = new \PhpBinaryReader\BinaryReader(fopen('projects/leafgreen/rom.gba', 'rb'));


foreach ($map as $lbl => $c) {
    printf("%4d %s\n", count($c), $labels2[$lbl]);
    //continue;

    $dataOrig = [];
    $dataNew = [];
    foreach ($c as $line) {
        $data = preg_split('/\s+/', $line);

        $offset = intval($data[0], 16);

        $orig = $data[1];
        $new = $data[3];
        while (strlen($orig) != 0) {
            $dataOrig[$offset] = hexdec(substr($orig, 0, 2));
            $dataNew[$offset] = hexdec(substr($new, 0, 2));

            $addresses[] = floor($offset / 4) * 4;

            $orig = substr($orig, 2);
            $new = substr($new, 2);
            $offset++;
        }
    }

    $min = floor(min(array_keys($dataOrig)) / 8) * 8;
    $max = ceil(max(array_keys($dataOrig)) / 8) * 8 + 7;

    if ($max - $min > 48) {
        continue;
    }

    var_dump(sprintf("%x", $min));

    $data = [];
    $br->setPosition($min);
    for ($i = $min; $i <= $max; $i++) {
        $data[$i] = $br->readUInt8();
    }

    for ($i = $min; $i <= $max; $i++) {
        if ($i % 8 === 0) {
            echo ' ';
        }
        if (!isset($dataOrig[$i])) {
            printf("\033[0;37m%02X\033[0m ", $data[$i]);
            continue;
        }

        printf("\033[0;30m%02X\033[0m ", $dataOrig[$i]);
    }
    echo "\n";

    for ($i = $min; $i <= $max; $i++) {
        if ($i % 8 === 0) {
            echo ' ';
        }

        if (!isset($dataNew[$i])) {
            printf("\033[0;37m%02X\033[0m ", $data[$i]);
            continue;
        }

        printf("\033[0;30m%02X\033[0m ", $dataNew[$i]);
    }


    echo "\n";

    echo "\n";
}


echo "Count: " . count($map) . "\n";

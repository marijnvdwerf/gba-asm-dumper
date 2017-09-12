<?php


require '_common.php';
use MarijnvdWerf\DisAsm\Output\Html\HtmlElement;

/** @var \MarijnvdWerf\DisAsm\RomMap $map */
$map = $container['map'];
$usedData = json_decode(file_get_contents($container['basepath'] . '/data.json'), true);

$values = $usedData;
$ewram = [];
$iwram = [];
$rodata = [];
ksort($values);

include 'html/head.php';

echo '<table>';
foreach ($values as $value => $count) {
    $block = floor($value / 0x1000000);

    $attrs = ['class' => 'blob-num'];
    $color = null;
    if ($block < 2 || $block > 8) {
        continue;
    }
    if ($block == 2) {
        $attrs['style'] = 'border-left: 2px solid #E91E63';
        $ewram[] = $value;
    } else if ($block == 3) {
        $attrs['style'] = 'border-left: 2px solid #673AB7';
        $iwram[] = $value;
    } else if ($block == 4) {
        $attrs['style'] = 'border-left: 2px solid #4CAF50';
    } else if ($block == 8) {
        if ($block < 0x8D00000) {
            $attrs['style'] = 'border-left: 2px solid #FFC107';
            $rodata[] = $value;
        } else {
            $attrs['style'] = 'border-left: 2px solid #00BCD4';
            $gfxdata[] = $value;
        }
    }
    printf('<tr>');

    echo(new HtmlElement('td', $attrs, sprintf('%08x;', $value)));
    printf('<td class="blob-code blob-code-inner">%d</td>', $count);
    printf('<td class="blob-code blob-code-inner">%s</td>', $map->getLabel($value));

    echo '</tr>';
}

echo '</table>';
?>
</body>

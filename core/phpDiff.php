<?php
require __DIR__ . '/../vendor/autoload.php';
use Jfcherng\Diff\DiffHelper;

function getDiffHTML($oldText, $newText) {
    return DiffHelper::calculate($oldText, $newText, 'Inline', [
        'detailLevel' => 'word',
        'outputTagAsString' => true,
        'resultForIdenticals' => '',
    ]);
}
?>
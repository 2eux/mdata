<?php
function xe($value) {
    // Pastikan null aman dan gunakan ENT_QUOTES agar karakter standar aman
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function cellStr($value, $style = '') {
    $styleAttr = $style ? ' ss:StyleID="' . $style . '"' : '';
    return '<Cell' . $styleAttr . '><Data ss:Type="String">' . xe($value) . '</Data></Cell>';
}
function cellNum($value, $style = '') {
    $styleAttr = $style ? ' ss:StyleID="' . $style . '"' : '';

    if ($value === '' || $value === null) {
        return '<Cell' . $styleAttr . '><Data ss:Type="String"></Data></Cell>';
    }

    return '<Cell' . $styleAttr . '>'
        . '<Data ss:Type="Number">' . xe($value) . '</Data>'
        . '</Cell>';
}

function cellEmpty($style = '') {
    $styleAttr = $style ? ' ss:StyleID="' . $style . '"' : '';

    return '<Cell' . $styleAttr . ' />';
}
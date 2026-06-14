<?php

function clicketBarcodeSvg(string $value, int $height = 62): string {
    $patterns = [
        '0'=>'nnnwwnwnn','1'=>'wnnwnnnnw','2'=>'nnwwnnnnw','3'=>'wnwwnnnnn','4'=>'nnnwwnnnw',
        '5'=>'wnnwwnnnn','6'=>'nnwwwnnnn','7'=>'nnnwnnwnw','8'=>'wnnwnnwnn','9'=>'nnwwnnwnn',
        'A'=>'wnnnnwnnw','B'=>'nnwnnwnnw','C'=>'wnwnnwnnn','D'=>'nnnnwwnnw','E'=>'wnnnwwnnn',
        'F'=>'nnwnwwnnn','G'=>'nnnnnwwnw','H'=>'wnnnnwwnn','I'=>'nnwnnwwnn','J'=>'nnnnwwwnn',
        'K'=>'wnnnnnnww','L'=>'nnwnnnnww','M'=>'wnwnnnnwn','N'=>'nnnnwnnww','O'=>'wnnnwnnwn',
        'P'=>'nnwnwnnwn','Q'=>'nnnnnnwww','R'=>'wnnnnnwwn','S'=>'nnwnnnwwn','T'=>'nnnnwnwwn',
        'U'=>'wwnnnnnnw','V'=>'nwwnnnnnw','W'=>'wwwnnnnnn','X'=>'nwnnwnnnw','Y'=>'wwnnwnnnn',
        'Z'=>'nwwnwnnnn','-'=>'nwnnnnwnw','.'=>'wwnnnnwnn',' '=>'nwwnnnwnn','*'=>'nwnnwnwnn',
    ];
    $encoded = '*' . strtoupper(preg_replace('/[^A-Z0-9 .-]/', '-', $value)) . '*';
    $narrow = 2;
    $wide = 5;
    $x = 10;
    $bars = '';

    foreach (str_split($encoded) as $character) {
        $pattern = $patterns[$character] ?? $patterns['-'];
        foreach (str_split($pattern) as $index => $widthType) {
            $width = $widthType === 'w' ? $wide : $narrow;
            if ($index % 2 === 0) {
                $bars .= '<rect x="' . $x . '" y="0" width="' . $width . '" height="' . $height . '" fill="#111"/>';
            }
            $x += $width;
        }
        $x += $narrow;
    }

    return '<svg class="voucher-barcode" viewBox="0 0 ' . ($x + 10) . ' ' . $height . '" role="img" aria-label="Validation barcode">' . $bars . '</svg>';
}


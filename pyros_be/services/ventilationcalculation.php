<?php

function calculateValues($data){
    $denom = ($data['blowRatio'] + $data['suckRatio'])/3600;
    $nom = $data['blowPower'] + $data['suckPower'];
    $specificVal = round($nom/$denom, 2);
    $sfp = match (true) {
                $specificVal < 500 => 1, $specificVal < 750 => 2, $specificVal < 1250 => 3,
                $specificVal < 2000 => 4, $specificVal < 3000 => 5, $specificVal < 4500 => 6,
                default => 7
            };

    return [
        'specificVal' => $nom/$denom,
        'spfCat' => 'SFP' . $sfp
    ];
}
<?php

const BUILDING_USAGE_TO_HMV = [
    "Iroda" => [5, 1],
    "Lakó/Szállásjellegű épület" => [25, 5],
    "Oktatási" => [7, 1.4],
    "Kereskedelmi" => [7, 1.4],
    "Üzem" => [3, 0.6],
    "Raktár" => [1, 0]
];

const HMV_TYPE_TO_MULTIPLIER = [
    "Elektromos bojler" => 1,
    "Hőszivattyús" => 0.33,
    "Közvetlen gáztüzelésű berendezés" => 1.15,
    "Fűtőművi távfűtés" => 1.15,
];

function calculateHMVQf(string $buildingType, bool $containment, bool $circulation, float $buildingSize, string $hmvType): float
{
    $specific = BUILDING_USAGE_TO_HMV[$buildingType][0] + ($containment ? 0.2 : 0) + ($circulation ? BUILDING_USAGE_TO_HMV[$buildingType][1] : 0);
    $result = $specific * $buildingSize * HMV_TYPE_TO_MULTIPLIER[$hmvType];
    return round($result, 2);
}
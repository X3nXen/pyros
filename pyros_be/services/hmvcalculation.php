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
    "Elektromos bojler" => [1, 0],
    "Hőszivattyús" => [0.33, 0],
    "Közvetlen gáztüzelésű berendezés" => [1.15, 0],
    "Fűtőművi távfűtés" => [1.15, 0.4],
];

function calculateHMVQf(string $buildingType, bool $containment, bool $circulation, float $buildingSize, string $hmvType): float
{
    $specific = BUILDING_USAGE_TO_HMV[$buildingType][0] + ($containment ? 0.2 : 0) + ($circulation ? BUILDING_USAGE_TO_HMV[$buildingType][1] : 0);
    $result = $specific * $buildingSize * HMV_TYPE_TO_MULTIPLIER[$hmvType][0];
    $result += calculateAdditionals($buildingSize, $hmvType, $circulation);
    return round($result, 2);
}

function calculateAdditionals(float $zoneSize, string $mode, bool $circulation): float
{
    $res = 0;
    $sizeAddition = match (true) {
        $zoneSize <= 100 => 1.140,
        $zoneSize <= 150 => 0.820,
        $zoneSize <= 200 => 0.660,
        $zoneSize <= 300 => 0.490,
        $zoneSize <= 500 => 0.340,
        $zoneSize <= 750 => 0.270,
        $zoneSize <= 1000 => 0.220,
        $zoneSize <= 1500 => 0.180,
        $zoneSize <= 2500 => 0.140,
        $zoneSize <= 5000 => 0.110,
        $zoneSize > 5000 => 0.100
    };
    $res += HMV_TYPE_TO_MULTIPLIER[$mode][1] * $zoneSize;
    if ($circulation) {
        $res += $sizeAddition;
    }
    return $res;
}
<?php

const SOLUTION_TO_VALUES = [
    "Normál izzólámpa – üvegburás/parabolatükrös" => [15, 0.5],
"Normál izzólámpa – opál burás" => [15, 0.3],
"Halogén izzólámpa – üvegburás/parabolatükrös" => [	20, 0.5],
"Halogén izzólámpa – opál burás" =>	[20, 0.3],
"Fénycső – üvegburás/parabolatükrös" =>	[75, 0.5],
"Fénycső – opál burás" =>	[75, 0.3],
"Kompakt fénycső – üvegburás/parabolatükrös" =>	[70, 0.5],
"Kompakt fénycső – opál burás" => [70,0.3],
"Higanylámpa – üvegburás/parabolatükrös" =>	[50, 0.5],
"Higanylámpa – opál burás" =>	[50,0.3],
"Fémhalogén lámpa – üvegburás/parabolatükrös" =>	[87,0.5],
"Fémhalogén lámpa – opál burás" =>	[87,0.3],
"LED – bármely lámpatest-változat" =>	[120,0.5]
];

const DIM_TO_VALUES = [
"Nem dimmelhető világítási rendszer" => 1,
"Dimmelhető halogén fényforrás" =>	0.9,
"Dimmelhető fénycső" =>	0.8,
"Dimmelhető LED" =>	0.7
];

const ZONE_USAGE_TO_VALUES = [
"Iroda / Irodaépület" => 	[500, 0.2, 2250, 250],
"Oktatási intézmény / Oktatási épület" =>	[500, 0.2, 1800, 200],
"Kórház / Kórház"	=> [500, 0.2, 3000, 2000],
"Hotel / Hotel" =>	[150,0,3000,2000],
"Étterem / Étterem" =>	[200, 0, 1250, 1250],
"Sportcsarnok / Sportközpont" =>	[300, 0.3,2000,2000],
"Kereskedelmi egység / Kereskedelmi egység" =>	[300,0,3000,2000],
"Üzem / Üzem"	 => [750,0,2500,1500],
"Múzeum / Kereskedelmi egység üzemideje" =>	[300,0,3000,2000],
"Könyvtár / Oktatási épület üzemideje" =>	[500,0,1800,200],
"Színház, auditórium / Étterem jellegű üzemidő" =>	[200,0,1250,1250],
"Konferenciaterem, Kiállító terem / Kereskedelmi egység üzemideje" =>	[500,0.5,3000,2000],
];

const REGULATION_TO_VALUES = [               //0 0.1 0.2 0.3  0.4   0.5 0.6  0.7  0.8  0.9  1
"Kézi be- és kikapcsolás"                   => [1, 1, 1, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0],
"Automatikus bekapcsolás/dimmelhető"        => [1, 0.975, 0.975, 0.95, 0.85, 0.65, 0.55, 0.45, 0.35, 0.25, 0],
"Automatikus be- és kikapcsolás"            => [1, 0.95, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0],
"Kézi bekapcsolás/dimmelhető"               => [1, 0.95, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0],
"Kézi bekapcsolás, automatikus kikapcsolás" =>	[1, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0.1, 0]
];

const NATURAL_LIGHT_TO_VALUES = [
"80% fölött" =>	0.35,
"40% - 80% között" => 0.45,
"40% alatt" =>	0.55,
"Nincs természetes világítás" => 1
];
function calculateValues($data){
    $fh = SOLUTION_TO_VALUES[$data['solution']][0];
    $nvil = SOLUTION_TO_VALUES[$data['solution']][1];

    $ffe = DIM_TO_VALUES[$data['dim']];
    $fkihaszn = ZONE_USAGE_TO_VALUES[$data['zoneUsage']][1];

    $regulationIndex = array_search($fkihaszn ,REGULATION_TO_VALUES[$data['regulation']]);
    $fszab = $regulationIndex / 10;

    $tnappal = ZONE_USAGE_TO_VALUES[$data['zoneUsage']][2];
    $fnappal = NATURAL_LIGHT_TO_VALUES[$data['naturalLight']];
    $tejjel = ZONE_USAGE_TO_VALUES[$data['zoneUsage']][3];
    $wvesz = $data['emergency'] ? 1 : 0;
    $wstandby = $data['standBy'] ? 1.5 : 0;

    $pj = ZONE_USAGE_TO_VALUES[$data['zoneUsage']][0] / ($fh * $nvil);

    $specific = ($pj * $ffe * $fszab * (($tnappal * $fnappal) + $tejjel) / 1000) + $wvesz + $wstandby;
    $sum = $specific * $data['size'];

    return [
        'specific' => round($specific, 2),
        'sum' => round($sum, 2)
    ];
}
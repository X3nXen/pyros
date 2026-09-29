<?php

const HEATER_TYPE_CONSTANT = [
    'Állandó hőmérsékletű gázkazán' => 1.24,
    'Alacsony hőmérsékletű gázkazán' => 1.24,
    'Kondenzációs gázkazán' => 1.01,
    'Gázégő' => 1.4,
    'Egyedi gázkonvektor' => 1.4,
    'Sugárzóernyő' => 1.4,
    'Elektromos üzemű hőszivattyú levegő hőforrással (vizes)' => 0.33,
    'Elektromos üzemű hőszivattyú levegő hőforrással (hűtőgázos)' => 0.33,
    'Elektromos üzemű hőszivattyú talajhő hőforrással' => 0.33,
    'Elektromos üzemű hőszivattyú víz hőforrással' => 0.33,
    'VRV/VRF' => null,
    'Split klíma' => null,
    'Elektromos üzemű kazán' => 1.11,
    'Egyedi elektromos fűtés',
    'Hőszivattyú' => 0.33,
    'Termoventilátor' => 1,
    'Rooftop' => 1,
    'Technológiai hűtés (hőszivattyú)' => null,
    'Technológiai hűtés (folyadékhűtő)' => null,
    'Technológiai hűtés' => null,
    'Folyadékhűtő' => null,
    'Lemezes hőcserélős leválasztás' => 1,
    'Csőköteges hőcserélős leválasztás' => 1,
    'Keverőszelepes leválasztás, 3 járatú' => 1,
    'Keverőszelepes leválasztás, 4 járatú' => 1,
    'Keverőszelepes leválasztás, kézi' => 1,
    'Hidraulikus váltós leválasztás' => 1,
    'Olajégő' => 1.8,
    'Olajkályha' => 1.8,
    'Olajkazán' => 1.8,
    'Szénkazán' => 1.85,
    'Kályha' => 1.8,
    'Faelgázosító kazán' => 1.2,
    'Fatüzelésű kazán' => 1.1,
    'Kandalló' => 1.8,
    'Kazán' => 1.8,
    'Egyéb' => 1,
];

const EMITTER_REGULATION_CONSTANT = [
    "Szabályozás helyiség szinten" => 0,
    "Időjáráskövető központi szabályozás" => 0.05,
    "Egyszerű központi szabályozás" => 0.1,
    "Szabályozatlan hőleadás" => 0.25
];

function calculateVirtualMeasurements(array $heaters, array $standings, array $buildingData): array
{
    // standingId => consumptionValue;
    $buildingRows = [];
    foreach ($buildingData as $buildingRow) {
        $jsonData = json_decode($buildingData['json_data'], true);
        $dataRow = ['qf' => $buildingRow['qf'], 'regulation' => $jsonData['emitterRegulation']];
        $buildingRows[(string) $buildingRow['id']] = $dataRow;
    }
    $grouped = [];
    foreach ($standings as $standingId) {
        foreach ($heaters as $heater) {
            if ($heater['standing'] != $standingId) {
                continue;
            }
            $calculated = 0;
            if (HEATER_TYPE_CONSTANT[$heater['heatingType']] == null) {
                continue;
            } else {
                foreach ($heater['servicedBuilding'] as $serviced) {
                    $calculated += HEATER_TYPE_CONSTANT[$heater['heatingType']] * $serviced['servicedSize'] * (1 + EMITTER_REGULATION_CONSTANT[$buildingRows[$serviced['buildingId']]['regulation']]) * $buildingRows[$serviced['buildingId']]['qf'];
                }
            }
            $grouped[$standingId] = ["total" => round($calculated, 2)];
        }
    }
    return $grouped;
}
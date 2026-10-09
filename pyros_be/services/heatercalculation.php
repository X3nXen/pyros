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

const HeaterDescriptions = [
    'NEW' => 0,
    'SERVICED' => -0.02,
    'UNRELIABLE' => -0.05,
    'OOO' => null
];

const ElectricCalcMode = [
    'UNKNOWN' => null,
    'Ismeretlen' => null,
    'On/Off működés, 1 hűtőkör' => [1.06, 0.95],
    'Többfokozatú működés, hűtőkörönként több kompresszor' => [1.15, 1.04],
    'Inverteres/fordulatszám szabályzott kompresszorok' => [1.33, 1.18],
];

const ElectricCalcInstallation = [
    'UNKNOWN' => null,
    'Ismeretlen' => null,
    'Gyári előírások betartásával, jól szellőző helyen' => 0,
    'Részben zavart légárammal' => -0.05,
    'Rosszul szellőző, zugos helyen' => -0.12,
];

const ElectricCalcSource = [
    'Ismeretlen' => null,
    'UNKNOWN' => null,
    'Levegő' => [0, -0.03],
    'Nedvesített levegő' => [0.05, 0],
    'Talajszonda' => [0.1, 0.04],
];

const ElectricCalcMedium = [
    'Ismeretlen' => null,
    'UNKNOWN' => null,
    'Levegő' => [0, 0],
    'Víz (normál üzemi tartomány)' => [-0.05, -0.05],
    'Víz (magas hőmérsékletű üzemi tartomány)' => [-0.03, -0.075],
];

const ElectricCalcRefrigerant = [
    'Ismeretlen' => null,
    'UNKNOWN' => null,
    'R410A' => [0, 0],
    'R32' => [0.01, 0.02],
    'R454B' => [0.005, 0.01],
    'R407C' => [-0.02, -0.02],
    'R22' => [-0.03, -0.04],
    'R134A (állandó sebesség)' => [0, 0],
    'R134a (VSD/centrifugás)' => [0.02, 0.01],
    'R1234ze' => [0.02, 0.01],
    'R290' => [0.01, 0.02],
];

const ElectricCalcUsage = [
    'Lakosság, csak 14 óra után' => 350,
    'Lakosság, egész nap' => 800,
    'Iroda, 8-16 között, munkanap' => 880,
    'Iroda, egész nap' => 2000,
    'Szállodai használat (vendégszobák)' => 1100,
];

function calculateVirtualMeasurements(array $heaters, array $standings, array $buildingData, string $purpose, array $pumps): array
{
    $buildingRows = [];
    foreach ($buildingData as $buildingRow) {
        $jsonData = json_decode($buildingData['json_data'], true);
        $dataRow = ['qf' => $buildingRow['qf'], 'regulation' => $jsonData['emitterRegulation']];
        $buildingRows[(string) $buildingRow['id']] = $dataRow;
    }
    $pumpType = null;
    if (!empty($pumps)) {
        $pumpType = "Állandó fordulatú";
        foreach ($pumps as $pump) {
            if ($pump['archetype'] == "Frekvenciaváltós" || $pump['archetype'] == "TYPE_C") {
                $pumpType = "Fordulatszám szabályozású";
                break;
            }
        }
    }
    $grouped = [];
    $effectiveSize = 0;
    foreach ($standings as $standingId) {
        foreach ($heaters as $heater) {
            if ($heater['standing'] != $standingId) {
                continue;
            }
            $calculated = 0;
            if ($purpose == 'HEAT' || $purpose == "BOTH") {
                if (HEATER_TYPE_CONSTANT[$heater['heatingType']] == null) {
                    $calculated += calculateFromSEERSCOP($heater);
                } else {
                    foreach ($heater['servicedBuilding'] as $serviced) {
                        $calculated += HEATER_TYPE_CONSTANT[$heater['heatingType']] * $serviced['servicedSize'] * (1 + EMITTER_REGULATION_CONSTANT[$buildingRows[$serviced['buildingId']]['regulation']]) * $buildingRows[$serviced['buildingId']]['qf'];
                        $effectiveSize += $serviced['servicedSize'];
                    }
                    $calculated += calculateAdditionalConsumption($heater, $effectiveSize, $pumpType);
                }
                $grouped[$standingId] = ["total" => round($calculated, 2)];
            }
            if ($purpose == 'COOL') {
                $calculated += calculateFromSEERSCOP($heater);
                $grouped[$standingId] = ["total" => round($calculated, 2)];
            }
        }
    }
    return $grouped;
}

function calculateFromSEERSCOP(array $heater)
{
    $res = 0;
    if (isset($heater['eer'])) {
        if ($heater['baseType'] !== 'Ismeretlen' && $heater['ambientMedium'] !== 'Ismeretlen' && $heater['heatTransfer'] !== 'Ismeretlen' && $heater['placementType'] !== 'Ismeretlen' && $heater['refrigerant'] !== 'Ismeretlen' && $heater['state'] !== 'Nem üzemel') {
            $seer = $heater['eer'] * max(
                1.03,
                min(
                    1.5,
                    ElectricCalcMode[$heater['baseType']][0] *
                    (1 + ElectricCalcMedium[$heater['ambientMedium']][0])
                ) *
                (1 + ElectricCalcSource[$heater['heatTransfer']][0]) *
                (1 + ElectricCalcInstallation[$heater['placementType']]) *
                (1 + ElectricCalcRefrigerant[$heater['refrigerant']][0]) *
                (1 + HeaterDescriptions[$heater['state']])
            );
            $coolingConsumption = round(ElectricCalcUsage[$heater['usage']] * ($heater['nominalOutput'] / round($seer, 2)), 2);
            $res += $coolingConsumption;
        }
    }
    if (isset($heater['cop'])) {
        if ($heater['baseType'] !== 'Ismeretlen' && $heater['ambientMedium'] !== 'Ismeretlen' && $heater['heatTransfer'] !== 'Ismeretlen' && $heater['placementType'] !== 'Ismeretlen' && $heater['refrigerant'] !== 'Ismeretlen' && $heater['state'] !== 'Nem üzemel') {
            $scop = $heater['cop'] * max(
                1.03,
                min(
                    1.5,
                    ElectricCalcMode[$heater['baseType']][1] *
                    (1 + ElectricCalcMedium[$heater['ambientMedium']][1])
                ) *
                (1 + ElectricCalcSource[$heater['heatTransfer']][1]) *
                (1 + ElectricCalcInstallation[$heater['placementType']]) *
                (1 + ElectricCalcRefrigerant[$heater['refrigerant']][1]) *
                (1 + HeaterDescriptions[$heater['state']])
            );
            $heatingConsumption = round(ElectricCalcUsage[$heater['usage']] * ($heater['nominalOutput'] / round($scop, 2)), 2);
            $res += $heatingConsumption;
        }
    }
    return $res;
}


function calculateAdditionalConsumption(array $heater, float $effectiveSize, string $pumpType)
{
    $additional = 0;
    $heaterSizeMatch = match (true) {
        $effectiveSize <= 100 => [0.79, 0, 0.19, 1.96],
        $effectiveSize <= 150 => [0.66, 0, 0.13, 1.84],
        $effectiveSize <= 200 => [0.58, 0, 0.1, 1.78],
        $effectiveSize <= 300 => [0.48, 0, 0.07, 1.71],
        $effectiveSize <= 500 => [0.38, 0, 0.04, 1.65],
        $effectiveSize <= 750 => [0.31, 0, 0.04, 1.65],
        $effectiveSize <= 1000 => [0.27, 0, 0.04, 1.65],
        $effectiveSize <= 1500 => [0.23, 0, 0.04, 1.65],
        $effectiveSize <= 2500 => [0.18, 0, 0.04, 1.65],
        $effectiveSize <= 5000 => [0.13, 0, 0.04, 1.65],
        default => [0.09, 0, 0.04, 1.65],
    };
    $pumpSizeMatch = [0, 0, 0, 0];
    if ($pumpType !== null) {
        $pumpSizeMatch = match (true) {
            $effectiveSize <= 100 => $pumpType == "Fordulatszám szabályozású" ? [1.69, 1.85, 1.98, 3.52] : [2.02, 2.22, 2.38, 4.22],
            $effectiveSize <= 150 => $pumpType == "Fordulatszám szabályozású" ? [1.12, 1.24, 1.35, 2.40] : [1.42, 1.56, 1.71, 3.03],
            $effectiveSize <= 200 => $pumpType == "Fordulatszám szabályozású" ? [0.86, 0.95, 1.06, 1.88] : [1.11, 1.24, 1.38, 2.44],
            $effectiveSize <= 300 => $pumpType == "Fordulatszám szabályozású" ? [0.61, 0.68, 0.78, 1.39] : [0.81, 0.91, 1.04, 1.85],
            $effectiveSize <= 500 => $pumpType == "Fordulatszám szabályozású" ? [0.42, 0.48, 0.57, 1.01] : [0.57, 0.65, 0.78, 1.38],
            $effectiveSize <= 750 => $pumpType == "Fordulatszám szabályozású" ? [0.33, 0.38, 0.47, 0.83] : [0.45, 0.52, 0.64, 1.14],
            $effectiveSize <= 1000 => $pumpType == "Fordulatszám szabályozású" ? [0.28, 0.33, 0.42, 0.74] : [0.39, 0.46, 0.58, 1.02],
            $effectiveSize <= 1500 => $pumpType == "Fordulatszám szabályozású" ? [0.23, 0.28, 0.37, 0.65] : [0.33, 0.39, 0.51, 0.90],
            $effectiveSize <= 2500 => $pumpType == "Fordulatszám szabályozású" ? [0.20, 0.24, 0.33, 0.58] : [0.28, 0.34, 0.46, 0.81],
            $effectiveSize <= 5000 => $pumpType == "Fordulatszám szabályozású" ? [0.17, 0.22, 0.30, 0.53] : [0.24, 0.30, 0.42, 0.74],
            default => $pumpType == "Fordulatszám szabályozású" ? [0.16, 0.20, 0.28, 0.50] : [0.22, 0.28, 0.40, 0.70],
        };
    }

    $heaterTypeMatch = match (true) {
        $heater['heatingType'] == "Állandó hőmérsékletű gázkazán" ||
        $heater['heatingType'] == 'Alacsony hőmérsékletű gázkazán' ||
        $heater['heatingType'] == 'Kondenzációs gázkazán' => $heaterSizeMatch[0],

        $heater['heatingType'] == 'Szénkazán' => $heaterSizeMatch[1],

        $heater['heatingType'] == 'Fatüzelésű kazán' ||
        $heater['heatingType'] == 'Faelgázosító kazán' => $heaterSizeMatch[2],

        $heater['heatingType'] == 'Kandalló' ||
        $heater['heatingType'] == 'Kazán' => $heaterSizeMatch[3],

        default => null
    };

    if ($heaterTypeMatch !== null) {
        $additional += $effectiveSize * $heaterTypeMatch;
        if ($heater['heatingSurface'] == "Szabad fűtőfelület") {
            $systemHeatMatch = match (true) {
                $heater["systemHeat"] == '20 K (90/70 °C)' => $pumpSizeMatch[0],
                $heater["systemHeat"] == '15 K (70/55 °C)' => $pumpSizeMatch[1],
                $heater["systemHeat"] == '10 K (55/45 °C)' => $pumpSizeMatch[2],
                $heater["systemHeat"] == '7 K (35/28 °C)' => 0
            };
            $additional += $systemHeatMatch * $effectiveSize;
        } else {
            $systemHeatMatch = match (true) {
                $heater["systemHeat"] == '20 K (90/70 °C)' => 0,
                $heater["systemHeat"] == '15 K (70/55 °C)' => 0,
                $heater["systemHeat"] == '10 K (55/45 °C)' => 0,
                $heater["systemHeat"] == '7 K (35/28 °C)' => $pumpSizeMatch[3]
            };
            $additional += $systemHeatMatch * $effectiveSize;
        }
    }


    return $additional;
}
<?php

const BUILDING_USAGES = [
        "Iroda" => [7, 5, 1],
        "Lakóépület" => [5, 25, 5],
        "Kereskedelmi" => [10, 7, 1.4],
        "Oktatási" => [12, 7, 1.4],
        "Üzem" => [3, 3, 0.6],
        "Raktár" => [1, 1, 0]
    ];

    
    const BUILDING_WALL_LAYER_PRESET = [
        "Kisméretű tégla fal, 30 cm" => 0.451,
        "Kisméretű tégla fal, 38 cm" => 0.566,
        "Kisméretű tégla fal, 51 cm" => 0.752,
        "B25 tégla fal, 25 cm" => 0.440,
        "B30 tégla fal, 30 cm" => 0.523,
        "B38 tégla fal, 38 cm" =>  0.656,
        "Soklyukú tégla fal, 30 cm" => 0.932,
        "Soklyukú tégla fal, 38 cm" => 1.174,
        "Soklyukú tégla fal, 45 cm" => 1.387,
        "Pórusbeton (Ytong), 30 cm" => 2.523,
        "Pórusbeton (Ytong), 38 cm" => 3.190,
        "Panel, vb., 15...20 cm" => 1.381,
        "Panel, vb., 20+ cm" => 2.170,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 8 cm" => 0.408,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 10 cm" => 0.331,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 12 cm" => 0.278,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 15 cm" => 0.225
    ];

    
    const BUILDING_CEILING_LAYER_PRESET = [
        "Fafödém zárt légréteggel" => 0.500,
        "Téglabetétes, gerendás–bordás vb födém" => 0.510,
        "Vb. gerenda béléstesttel" => 0.876,
        "Üreges vb födém" => 0.876,
        "Acél tartók trapézlemezzel" => 4.403,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 3 cm" => 1.001,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 4 cm"  => 0.778,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 5 cm"  => 0.637,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 6 cm"  => 0.539,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 8 cm"  => 0.412,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 10 cm"  => 0.333,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 12 cm"  => 0.280,
        "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 15 cm"  => 0.226
    ];

    
    const BUILDING_DOOR_AND_WINDOW_PRESET = [
        "Szimpla fa-fém" => [5.5, 1.1],
        "Kapcsolt gerébtokos fa" => [4.5, 1.1],
        "Egyesített szárnyú nyíló/bukó fém/fa kerettel, kétrétegű üveggel" => [3, 1.1],
        "Régi műanyagkeret kétrétegű üveggel" => [2.6, 0.8],
        "Új műanyagkeret kétrégetű üveggel" => [1.6, 0.5],
        "Új műanyagkeret két vagy háromrétegű üveggel, low-E bevonattal" => [1.15, 0.5]
    ];

    
    const BUILDING_TYPICAL_HEATING = [
        "Elektromos radiátor vagy elektromos hősugárzó" => [1, 2.5],
        "Elektromos kazán" => [1.11, 2, 5],
        "Elektromos hőtárolós kályha" => [1.03, 1, 8],
        "Fűtőművi távfűtés" => [1, 0.75],
        "Fatüzelésű cserépkályha" => [1.6, 0],
        "Kandalló, kályha (zárt, hagyományos)" => [1.8, 0],
        "Kandalló (nyitott, hagyományos)" => [4, 0],
        "Gázkonvektor, gázüzemű sugárzóernyő" => [1.4, 1],
        "Széntüzelésű kazán" => [1.85, 1],
        "Pellettüzelésű kazán" => [1.1, 0.2],
        "Faelgázosító kazán" => [1.2, 0.2],
        "Levegő-víz hőszivattyú (magas hőmérséklet)" => [0.33, 1.8],
        "Levegő-víz hőszivattyú (alacsony hőmérséklet)" => [0.25, 1.8],
        "Hagyományos gázkazán fűtött téren belül" => [1.24, 1],
        "Kondenzációs gázkazán fűtött téren belül" => [1.01, 1],
        "Hagyományos gázkazán fűtött téren kívül" => [1.33, 1],
        "Kondenzációs gázkazán fűtött téren kívül" => [1.04, 1]

    ];

    
    const BUILDING_WARM_WATER_CREATION = [
        "Nincs" => [0, 0],
        "Elektromos bojler" => [1, 2.5],
        "Közvetlen gáztüzelésű berendezés" => [1.15, 1],
        "Fűtőművi távfűtés" => [1.15, 0.75],
        "Hőszivattyús" => [0.33, 2.5]
    ];

    
    const BUILDING_HEATING_TYPICAL_REGULATION = [
        "Szabályozás helyiség szinten" => 1,
        "Időjáráskövető központi szabályozás" => 1.05,
        "Egyszerű központi szabályozás" => 1.1,
        "Szabályozatlan hőleadás" => 1.25
    ];

function calculateValues($data){
    $res = [];
    $res['outdoorTemp'] = getOutdoorDesignTemp($data['postal'])['outdoor_temp'];
        
    $res['volume'] = (float) $data["size"] * (float) $data["height"];
    $res['facade_area'] = ((int) $data["stories"] * (float) $data["height"] * (float) $data["floorSize"]) - (float) $data["doorWindowSize"];
    
    $res['u_wall'] = calculateWallU($data);
    $res['u_ceiling'] = calculateCeilingU($data);
    $res['u_windows'] = BUILDING_DOOR_AND_WINDOW_PRESET[$data["doorWindowType"]][0];
    $res['floor_bridge'] = ($data["floorInsulation"] === 1) ? 1.05 : 1.7;
    
    $tempDiff = (float) $data["insideHeat"] + abs($res['outdoorTemp']);

    $res['losses'] = [
        'windows' => $res['u_windows'] * (float) $data["doorWindowSize"] * $tempDiff,
        'wall' => $res['u_wall'] * 1.2 * $res['facade_area'] * $tempDiff,
        'ceiling' => ((float) $data["size"] / (int) $data["stories"]) * 1.2 * $res['u_ceiling'] * $tempDiff,
        'floor' => $tempDiff * (float) $data["floorSize"] * $res['floor_bridge']
    ];

    $res['total_transmission_loss'] = array_sum($res['losses']);
    
    $ventilationFactor = BUILDING_DOOR_AND_WINDOW_PRESET[$data["doorWindowType"]][1];
    $res['ventilation_loss'] = $res['volume'] * $ventilationFactor / 3600 * ((float) $data["insideHeat"] - 4) * 4800 * 0.35;

    $res['total_loss_watt'] = $res['total_transmission_loss'] + $res['ventilation_loss'];

        
    $usageData = BUILDING_USAGES[$data['usage']]; 

    $specificLossConstant = $res['total_loss_watt'] / $tempDiff;
    $res['specific_loss_constant'] = $specificLossConstant;
    $specificLoss = $res['total_loss_watt'] / ($res['volume'] * $tempDiff);
    $res['specific_loss_factor'] = $specificLoss;
    $runningFactor = ($data["running"] == "Folyamatos" ? 1 : 0.9);

    $res['q_f'] = (72 * $res['volume'] * ($specificLoss + 0.35 * $ventilationFactor * $runningFactor) - 4.4 * (float) $data["size"] * $usageData[0]) / (float) $data["size"];
    $res['q_hmv'] = $usageData[1];

        
    $heatType = BUILDING_TYPICAL_HEATING[$data['heatingType']];
    $regFactor = BUILDING_HEATING_TYPICAL_REGULATION[$data['regulationMode']];
    $hmvType = BUILDING_WARM_WATER_CREATION[$data['hmvCreation']];

    $res['final_energy_heating'] = $res['q_f'] * (float) $data["size"] * $heatType[0] * $regFactor;

    $circLoss = $data["hmvCirculation"] ? $usageData[2] : 0;
    $contLoss = $data["hmvContainment"] ? 0.2 : 0;

    $res['final_energy_hmv'] = ($res['q_hmv'] + $contLoss + $circLoss) * (float) $data["size"] * $hmvType[0];

    $res['total_final_energy'] = $res['final_energy_heating'] + $res['final_energy_hmv'];

    $res['primary_energy_specific'] = (($res['final_energy_heating'] * $heatType[1]) + ($res['final_energy_hmv'] * $hmvType[1])) / (float) $data["size"];
    return $res;
    }

function calculateWallU($data)
    {
        $baseR = BUILDING_WALL_LAYER_PRESET[$data["wallLayers"]];
        $insulationR = ($data["wallInsulationWidth"] != 0) ? ($data["wallInsulationWidth"] / 100 / 0.038) : 0;
        $u = 1 / ($baseR + $insulationR + (1 / 24) + (1 / 8));
        return $u > 2 ? 2 : $u;
    }

    function calculateCeilingU($data)
    {
        $baseR = BUILDING_CEILING_LAYER_PRESET[$data["ceilingLayers"]];
        $insulationR = ($data["ceilingInsulationWidth"] != 0) ? ($data["ceilingInsulationWidth"] / 100 / 0.038) : 0;
        $u = 1 / ($baseR + $insulationR + (1 / 24) + (1 / 10));
        return $u > 1.2 ? 1.2 : $u;
    }

function getOutdoorDesignTemp($postal)
    {
        $kulso_meretezi_hom = 0;
        try {
            $request_url = "https://api.zippopotam.us/hu/" . $postal;
            $response = file_get_contents($request_url);
            $api_response = json_decode($response, true);

            $city = $api_response["places"][0]["place name"] ?? "Nem található adatbázisban";
            $state = $api_response["places"][0]["state abbreviation"] ?? "";
            switch ($state) {
                case "SZ":
                case "BZ":
                case "GS":
                case "HB":
                case "HE":
                case "JN":
                case "NO":
                    $kulso_meretezi_hom = -15;
                    break;
                case "BK":
                case "BE":
                case "CS":
                case "FE":
                case "KE":
                case "BU":
                case "PE":
                case "VA":
                case "VE":
                case "ZA":
                    $kulso_meretezi_hom = -13;
                    break;
                case "SO":
                case "TO":
                case "BA":
                    $kulso_meretezi_hom = -11;
                    break;
            }
        } catch (\Exception $ex) {
            $kulso_meretezi_hom = -13;
        }
        return ['outdoor_temp' => $kulso_meretezi_hom, 'city' => $city];
    }
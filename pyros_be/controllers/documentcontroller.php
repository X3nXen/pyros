<?php

use PhpOffice\PhpWord\TemplateProcessor;
require_once __DIR__ . "/../database.php";
require_once __DIR__ . "/../services/AuditService.php";
require_once __DIR__ . "/../services/EnergyPriceService.php";
class DocumentController
{
    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            $this->handlePost();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'A kért HTTP metódus nem támogatott']);
        }
    }

    private function handlePost()
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Érvénytelen JSON formátum']);
            return;
        }

        $project_id = $data['project_id'] ?? null;
        $base64Image = $data['sankey_image'] ?? null;

        if (!$project_id) {
            http_response_code(400);
            echo json_encode(['error' => 'A project_id megadása kötelező']);
            return;
        }

        $sankeyImagePath = sys_get_temp_dir() . '/sankey_' . $project_id . '.png';
        $hasValidImage = false;

        if (!empty($base64Image)) {
            $imgData = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
            $decodedData = base64_decode($imgData);

            if ($decodedData !== false) {
                file_put_contents($sankeyImagePath, $decodedData);

                if (@getimagesize($sankeyImagePath) !== false) {
                    $hasValidImage = true;
                }
            }
        }
        try {
            $templateProcessor = new TemplateProcessor(__DIR__ . '/audit_template.docx');
            $db = Database::getConnection();

            // 1. Vállalkozás bemutatása
            $stmt = $db->prepare("SELECT json FROM variables WHERE project_id = :projectId");
            $stmt->execute([':projectId' => $project_id]);
            $jsonData = json_decode($stmt->fetchColumn() ?: '{}', true);
            $companyName = $jsonData['fullName'];

            $templateProcessor->setValue('company_name', $companyName ?? '');
            $templateProcessor->setValue('foundation_year', $jsonData['foundationYear'] ?? '');
            $templateProcessor->setValue('owner_percentage', !empty($jsonData['foreign']) ? 'magyar' : ($jsonData['percent'] ?? 0) . '%-ban külföldi');
            $templateProcessor->setValue('company_product', $jsonData['mainActivity'] ?? '');
            $templateProcessor->setValue('company_place', $jsonData['companyPlace'] ?? '');
            $templateProcessor->setValue('data_year', $jsonData['dataYear'] ?? '');
            $templateProcessor->setValue('employee_count', $jsonData['employeeCount'] ?? '');
            $templateProcessor->setValue('profit', $jsonData['income'] ?? '');

            $stmt = $db->prepare("
    SELECT id, name, measurement_type, sub_to, source, measurement, consumption, purpose 
    FROM standings 
    WHERE project_id = :projectId
");
            $stmt->execute([':projectId' => $project_id]);
            $standings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $carrierRows = AuditService::getEnergyCarrierSummaryRows($standings);

            if (!empty($carrierRows)) {
                $templateProcessor->cloneRow('carrier', count($carrierRows));

                foreach ($carrierRows as $index => $row) {
                    $i = $index + 1;

                    $templateProcessor->setValue("carrier#{$i}", $row['carrier_name']);
                    $templateProcessor->setValue("carrier_building#{$i}", $row['carrier_building']);
                    $templateProcessor->setValue("carrier_product#{$i}", $row['carrier_product']);
                    $templateProcessor->setValue("carrier_vehicle#{$i}", $row['carrier_vehicle']);
                    $templateProcessor->setValue("carrier_total#{$i}", $row['carrier_total']);
                }
            } else {
                $templateProcessor->setValue('carrier', 'Nincs adat');
                $templateProcessor->setValue('carrier_building', '-');
                $templateProcessor->setValue('carrier_product', '-');
                $templateProcessor->setValue('carrier_vehicle', '-');
                $templateProcessor->setValue('carrier_total', '-');
            }

            // Fogyasztások felosztása

            if (!empty($carrierRows)) {
                $templateProcessor->cloneRow('carrier2', count($carrierRows));

                foreach ($carrierRows as $index => $row) {
                    $i = $index + 1;
                    $templateProcessor->setValue("carrier2#{$i}", $row['carrier_name']);
                    $templateProcessor->setValue("carrier2_building#{$i}", $row['carrier_building']);
                    $templateProcessor->setValue("carrier2_product#{$i}", $row['carrier_product']);
                    $templateProcessor->setValue("carrier2_vehicle#{$i}", $row['carrier_vehicle']);
                }
            } else {
                $templateProcessor->setValue('carrier2', 'Nincs adat');
                $templateProcessor->setValue('carrier2_building', '-');
                $templateProcessor->setValue('carrier2_product', '-');
                $templateProcessor->setValue('carrier2_vehicle', '-');
            }

            if ($hasValidImage) {
                $templateProcessor->setImageValue('sankey_diagram', [
                    'path' => $sankeyImagePath,
                    'width' => 600,
                    'height' => 300,
                    'ratio' => true
                ]);
            } else {
                $templateProcessor->setValue('sankey_diagram', 'A diagram nem áll rendelkezésre.');
            }
            if (file_exists($sankeyImagePath)) {
                @unlink($sankeyImagePath);
            }

            //Pénzügyi kalkuláció
            $templateProcessor->setValue('bubor_rate', $jsonData['buborPercent']);
            $templateProcessor->setValue('bond_rate', $jsonData['bondPercent']);
            $templateProcessor->setValue('mnb_rate', $jsonData['mnbPercent']);
            $interest_rate = 0.3 * (((float) $jsonData['buborPercent']) / 100) + 0.5 * (((float) $jsonData['bondPercent']) / 100) + 0.2 * (((float) $jsonData['mnbPercent']) / 100);
            $interest_rate = round(($interest_rate + 0.03) * 100, 2);
            $templateProcessor->setValue('interest_rate', $interest_rate);

            //Költségek kalkulációja
            $templateProcessor->setValue('current_date', date('Y.m.d'));

            $stmt = $db->prepare("SELECT date_from, date_to FROM standings WHERE project_id=:projectId LIMIT 1");
            $stmt->execute([':projectId' => $project_id]);
            $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $dateFrom = new DateTime($dates[0]['date_from']);
            $dateTo = new DateTime($dates[0]['date_to']);

            $templateProcessor->setValue('audit_interval', $dateFrom->format('Y.m.d') . ' - ' . $dateTo->format('Y.m.d'));

            $marketData = EnergyPriceService::getMarketPrices();

            $eurHuf = $marketData['eur_huf_rate'];
            $gasEurMwh = $marketData['natural_gas_price'];
            $electricEurMwh = $marketData['electric_energy_price'];

            $gasConverted = ($gasEurMwh * $eurHuf) / 1000;
            $electricConverted = ($electricEurMwh * $eurHuf) / 1000;

            $gasSpecific = $gasConverted + 15.0;
            $electricSpecific = $electricConverted + 30.915;

            $templateProcessor->setValue('eur_huf_rate', number_format($eurHuf, 2, ',', ' '));
            $templateProcessor->setValue('natural_gas_price', number_format($gasEurMwh, 2, ',', ' '));
            $templateProcessor->setValue('electric_energy_price', number_format($electricEurMwh, 2, ',', ' '));

            $templateProcessor->setValue('natural_gas_converted', number_format($gasConverted, 2, ',', ' '));
            $templateProcessor->setValue('natural_gas_specific', number_format($gasSpecific, 2, ',', ' '));

            $templateProcessor->setValue('electric_converted', number_format($electricConverted, 2, ',', ' '));
            $templateProcessor->setValue('electric_energy_specific', number_format($electricSpecific, 3, ',', ' '));

            // 2. Telephelyek táblázat
            $stmt = $db->prepare("SELECT complex_json FROM complex WHERE project_id = :projectId");
            $stmt->execute([':projectId' => $project_id]);
            $complexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $templateProcessor->setValue('telephelyein', count($complexes) > 1 ? 'telephelyein' : 'telephelyén');

            $complexTable = new \PhpOffice\PhpWord\Element\Table([
                'borderSize' => 0,
                'borderColor' => 'FFFFFF',
                'afterSpacing' => 100,
            ]);

            foreach ($complexes as $index => $field) {
                $fieldJson = json_decode($field['complex_json'], true);
                if (!$fieldJson)
                    continue;

                $complexTable->addRow();
                $cell = $complexTable->addCell(9000, ['gridSpan' => 2]);
                $cell->addText(
                    $fieldJson['postal'] . ' ' . $fieldJson['city'] . ', ' . $fieldJson['address'] . ' (' . $fieldJson['name'] . ')',
                    ['bold' => true, 'size' => 11]
                );

                if (!empty($fieldJson['working']) && is_array($fieldJson['working'])) {
                    foreach ($fieldJson['working'] as $working) {
                        $complexTable->addRow();
                        $complexTable->addCell(4500)->addText('• Tevékenység: ' . $working['workType'], ['size' => 10]);
                        $complexTable->addCell(4500)->addText('Munkarend: ' . $working['workHours'], ['size' => 10, 'italic' => true]);
                    }
                }

                if ($index < count($complexes) - 1) {
                    $complexTable->addRow();
                    $complexTable->addCell(9000, ['gridSpan' => 2])->addText('');
                }
            }
            $templateProcessor->setComplexValue('complex_data', $complexTable);

            // 3. Fogyasztási táblázat
            $stmt = $db->prepare("SELECT c.id as complex_id, c.name, st.source, st.measurement, st.consumption 
                          FROM complex c 
                          JOIN standings_to_other s ON s.type='COMPLEX' AND s.reference=c.id 
                          JOIN standings st ON st.id=s.standing 
                          WHERE st.measurement_type='MAIN' AND c.project_id=:projectId");
            $stmt->execute([':projectId' => $project_id]);

            $groupedComplexData = AuditService::processMonthlyConsumption($stmt->fetchAll(PDO::FETCH_ASSOC));

            $mainTable = new \PhpOffice\PhpWord\Element\Table([
                'borderColor' => 'CCCCCC',
                'borderSize' => 4,
                'cellMarginTop' => 40,
                'cellMarginBottom' => 40,
                'cellMarginLeft' => 100,
                'cellMarginRight' => 100,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);

            $isFirstComplex = true;
            foreach ($groupedComplexData as $complexTitle => $groupedData) {
                if (!$isFirstComplex) {
                    $mainTable->addRow();
                    $breakCell = $mainTable->addCell(9000, ['gridSpan' => 3, 'borderSize' => 0]);
                    $breakCell->addText('<w:br w:type="page"/>');
                }
                $isFirstComplex = false;

                $mainTable->addRow(250, ['cantSplit' => true]);
                $mainTable->addCell(9000, ['gridSpan' => 3, 'bgColor' => 'D9D9D9', 'valign' => 'center'])
                    ->addText('Mérési pont / Telephely: ' . $complexTitle, ['bold' => true, 'size' => 10]);

                $mainTable->addRow(220, ['tblHeader' => true, 'cantSplit' => true]);
                $mainTable->addCell(3000, ['bgColor' => 'F2F2F2', 'valign' => 'center'])->addText('Hónap', ['bold' => true, 'size' => 9.5], ['alignment' => 'center']);
                $mainTable->addCell(3500, ['bgColor' => 'F2F2F2', 'valign' => 'center'])->addText('Energiahordozó', ['bold' => true, 'size' => 9.5], ['alignment' => 'center']);
                $mainTable->addCell(2500, ['bgColor' => 'F2F2F2', 'valign' => 'center'])->addText('Fogyasztás', ['bold' => true, 'size' => 9.5], ['alignment' => 'center']);

                ksort($groupedData);

                foreach ($groupedData as $sortKey => $monthData) {
                    $monthLabel = $monthData['label'];
                    $items = array_values($monthData['items']);

                    foreach ($items as $index => $item) {
                        $mainTable->addRow(200, ['cantSplit' => true]);

                        if ($index === 0) {
                            $mainTable->addCell(3000, ['vMerge' => 'restart', 'valign' => 'center'])
                                ->addText($monthLabel, ['bold' => true, 'size' => 9.5], ['alignment' => 'center']);
                        } else {
                            $mainTable->addCell(3000, ['vMerge' => 'continue']);
                        }

                        $mainTable->addCell(3500, ['valign' => 'center'])->addText($item['source'], ['size' => 9.5]);

                        $formattedValue = number_format($item['value'], 0, ',', ' ') . ' ' . $item['unit'];
                        $mainTable->addCell(2500, ['valign' => 'center'])->addText($formattedValue, ['size' => 9.5], ['alignment' => 'right']);
                    }
                }
            }
            $templateProcessor->setComplexValue('standings_data_by_complex', $mainTable);

            // 4. Mérő hierarchia
            $stmt = $db->prepare("SELECT id, name, measurement_type, sub_to, source, measurement, consumption 
                          FROM standings WHERE project_id = :projectId ORDER BY id ASC");
            $stmt->execute([':projectId' => $project_id]);
            $allStandings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $standingsById = [];
            $childrenByParent = [];
            $mainStandings = [];

            foreach ($allStandings as $standing) {
                $id = $standing['id'];
                $parentId = $standing['sub_to'];
                $standingsById[$id] = $standing;

                if (!empty($parentId)) {
                    $childrenByParent[$parentId][] = $id;
                } else {
                    $mainStandings[] = $id;
                }
            }

            $hierarchyTable = new \PhpOffice\PhpWord\Element\Table([
                'borderSize' => 0,
                'borderColor' => 'FFFFFF',
                'cellMarginLeft' => 40,
                'cellMarginRight' => 40,
                'cellMarginTop' => 20,
                'cellMarginBottom' => 20,
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
            ]);

            foreach ($mainStandings as $mainId) {
                AuditService::buildStandingTree($mainId, $standingsById, $childrenByParent, $hierarchyTable, 0);
            }

            $templateProcessor->setComplexValue('standing_hierarchy', $hierarchyTable);

            //Épületenergetikai értékelés

            $stmt = $db->prepare("SELECT b.name as building_name, b.qf, c.name as complex_name FROM buildings b join complex c on c.id = b.complex WHERE b.project_id=:projectId");
            $stmt->execute([
                ':projectId' => $project_id
            ]);
            $allBuildings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $buildingsTable = new \PhpOffice\PhpWord\Element\Table([
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
            AuditService::buildBuildingsTable($buildingsTable, $allBuildings);

            $templateProcessor->setComplexValue('building_listing', $buildingsTable);

            // - Fűtési rendszerek értékelése

            $stmt = $db->prepare("SELECT h.heaters, h.emitters, c.name FROM heating_systems h JOIN complex c ON h.complex=c.id WHERE h.project_id=:projectId AND (purpose='HEAT' OR purpose='BOTH')");
            $stmt->execute([
                ':projectId' => $project_id
            ]);
            $allHeating = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $heatingTable = new \PhpOffice\PhpWord\Element\Table([
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
            AuditService::buildHeatingTable($heatingTable, $allHeating);

            $templateProcessor->setComplexValue('heating_listing', $heatingTable);

            // - HMV rendszerek értékelése
            $hmvTable = new \PhpOffice\PhpWord\Element\Table([
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
            AuditService::buildHMVTable($hmvTable, $allHeating);
            $templateProcessor->setComplexValue("hmv_listing", $hmvTable);

            // - Világítási rendszerek értékelése

            $stmt = $db->prepare("SELECT l.name, l.specific_sum, s.consumption, s.source, c.name as complex_name FROM lighting_systems l join standings s on l.standing = s.id join complex c on c.id = l.complex where l.project_id =:projectId");
            $stmt->execute([":projectId" => $project_id]);
            $allLighting = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $lightingTable = new \PhpOffice\PhpWord\Element\Table([
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);

            AuditService::buildLightingTable($lightingTable, $allLighting);
            $templateProcessor->setComplexValue("lighting_listing", $lightingTable);

            // - Komforthűtés rendszerek értékelése

            $stmt = $db->prepare("SELECT h.heaters, c.name FROM heating_systems h JOIN complex c ON h.complex=c.id WHERE h.project_id=:projectId AND (purpose='COOL' OR purpose='BOTH')");
            $stmt->execute([':projectId' => $project_id]);
            $allCooling = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $coolingTable = new \PhpOffice\PhpWord\Element\Table([
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
            AuditService::buildCoolingTable($coolingTable, $allCooling);

            $templateProcessor->setComplexValue('cooling_listing', $coolingTable);

            // - Légkezelő rendszerek
            $stmt = $db->prepare("SELECT v.name, c.name as complex_name, b.name as building_name, v.sfp, v.category, v.json from ventilation_systems v join complex c on c.id=v.complex join buildings b on v.building = b.id WHERE v.project_id=:projectId");
            $stmt->execute([":projectId" => $project_id]);
            $allHvac = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $hvacTable = new \PhpOffice\PhpWord\Element\Table([
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
            AuditService::buildHVACTable($hvacTable, $allHvac);

            $templateProcessor->setComplexValue('hvac_listing', $hvacTable);

            //Szállítás értékelése
            $stmt = $db->prepare("SELECT 
                        v.name AS vehicle_name, 
                        c.name AS complex_name, 
                        v.usage_value, 
                        v.usage_value2, 
                        v.usage_metric,
                        v.vehicle_category,
                        v.motor_size,
                        v.hibrid,
                        v.fuel,
                        v.chargeable,
                        v.capacity,
                        s.consumption, 
                        s.measurement, 
                        s.source 
                      FROM vehicles v 
                      JOIN complex c ON v.complex_id = c.id 
                      LEFT JOIN standings s ON v.standing_id = s.id 
                      WHERE v.project_id = :projectId");
            $stmt->execute([':projectId' => $project_id]);
            $vehicleData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $vehiclesTable = new \PhpOffice\PhpWord\Element\Table([
                'borderSize' => 6,
                'borderColor' => '000000',
                'cellMarginLeft' => 80,
                'cellMarginRight' => 80,
                'cellMarginTop' => 60,
                'cellMarginBottom' => 60,
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);

            AuditService::buildVehiclesTable($vehiclesTable, $vehicleData);

            $templateProcessor->setComplexValue('vehicle_listing', $vehiclesTable);

            //Technológia értékelése
            $stmt = $db->prepare("SELECT t.id, t.name, t.json, c.name as complex_name, t.technology_type FROM technology t join complex c on t.complex = c.id WHERE t.project_id = :projectId ORDER BY id ASC");
            $stmt->execute([':projectId' => $project_id]);
            $technologies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($technologies)) {
                $templateProcessor->setValue('technology_title', '');
                $templateProcessor->setValue('technology_content', '');
            } else {
                $templateProcessor->setValue('technology_title', '10. Technológiai alrendszerek energetikai értékelése');
                $stmtMeters = $db->prepare("SELECT id, name FROM standings WHERE project_id = :projectId");
                $stmtMeters->execute([':projectId' => $project_id]);
                $meters = $stmtMeters->fetchAll(PDO::FETCH_KEY_PAIR);

                $groupedTechs = [
                    'COMPRESSED_AIR' => [],
                    'STEAM' => [],
                    'COOLING' => [],
                    'OTHER' => []
                ];

                foreach ($technologies as $tech) {
                    $type = $tech['technology_type'];
                    if (isset($groupedTechs[$type])) {
                        $groupedTechs[$type][] = $tech;
                    }
                }

                $mainTable = new \PhpOffice\PhpWord\Element\Table([
                    'borderSize' => 0,
                    'borderColor' => 'FFFFFF',
                    'cellMargin' => 0
                ]);

                $mainTable->addRow();
                $mainCell = $mainTable->addCell(9000);
                $mainCell->addText(
                    "A " . htmlspecialchars($companyName ?? 'GAZDÁLKODÓ SZERVEZET') . "-nál/nél az alábbi technológiai alrendszerek kerültek kialakításra:",
                    null,
                    ['spaceAfter' => 120]
                );
                $letterIndex = 'a';

                if (!empty($groupedTechs['COMPRESSED_AIR'])) {
                    $mainCell->addText($letterIndex . ") Sűrített levegős hálózat", ['bold' => true, 'size' => 11]);

                    foreach ($groupedTechs['COMPRESSED_AIR'] as $tech) {
                        $jsonData = json_decode($tech['json'], true) ?? [];

                        // Átadjuk a $complexes és $meters tömböket is!
                        AuditService::appendCompressedAirTableToCell($mainCell, $jsonData, $tech['name'], $tech['complex_name'], $meters);
                        $mainCell->addText("");
                    }

                    $letterIndex = chr(ord($letterIndex) + 1);
                }

                if (!empty($groupedTechs['STEAM'])) {
                    $mainCell->addText($letterIndex . ") Gőzrendszer", ['bold' => true, 'size' => 11]);

                    foreach ($groupedTechs['STEAM'] as $tech) {
                        $jsonData = json_decode($tech['json'], true) ?? [];
                        $complexName = $tech['complex_name'] ?? '';

                        AuditService::appendSteamTableToCell($mainCell, $jsonData, $tech['name'], $complexName, $meters);
                        $mainCell->addText("");
                    }

                    $letterIndex = chr(ord($letterIndex) + 1);
                }

                if (!empty($groupedTechs['COOLING'])) {
                    $mainCell->addText($letterIndex . ") Technológiai hűtés", ['bold' => true, 'size' => 11]);

                    foreach ($groupedTechs['COOLING'] as $tech) {
                        $jsonData = json_decode($tech['json'], true) ?? [];

                        AuditService::appendCoolingTableToCell($mainCell, $jsonData, $tech['name'], $meters);
                        $mainCell->addText(""); // Sorköz
                    }

                    $letterIndex = chr(ord($letterIndex) + 1);
                }

                if (!empty($groupedTechs['OTHER'])) {
                    $mainCell->addText($letterIndex . ") Egyéb technológiai hőhasználat", ['bold' => true, 'size' => 11]);

                    foreach ($groupedTechs['OTHER'] as $tech) {
                        $jsonData = json_decode($tech['json'], true) ?? [];
                        $complexName = $tech['complex_name'] ?? '';

                        AuditService::appendOtherTableToCell($mainCell, $jsonData, $tech['name'], $complexName, $meters);
                        $mainCell->addText("");
                    }

                    $letterIndex = chr(ord($letterIndex) + 1);
                }

                $templateProcessor->setComplexBlock('technology_content', $mainTable);
            }

            // 5. Letöltés és takarítás
            $tempFileName = 'dokumentacio_' . time() . '.docx';
            $tempPath = sys_get_temp_dir() . '/' . $tempFileName;
            $templateProcessor->saveAs($tempPath);

            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="generalt_dokumentacio.docx"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($tempPath));

            readfile($tempPath);
            unlink($tempPath);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            echo "Hiba történt a dokumentum generálása során: " . $e->getMessage();
        }
    }
}
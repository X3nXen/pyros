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
            $improveable_list = [
                'building' => [],
                'heaters' => [],
                'hmv' => [],
                'coolers' => [],
                'hvac' => [],
                'vehicle' => [],
                'technology' => []
            ];
            //1. Vállalkozás bemutatása
            $stmt = $db->prepare("SELECT json FROM variables WHERE project_id = :projectId");
            $stmt->execute([':projectId' => $project_id]);
            $jsonData = json_decode($stmt->fetchColumn() ?: '{}', true);

            // Biztonságos XML escape segédfüggvény a kód tisztaságához
            $xmlEscape = function ($val) {
                return htmlspecialchars($val ?? '', ENT_XML1, 'UTF-8');
            };

            $companyName = $jsonData['fullName'] ?? '';
            $ownerPercentageText = !empty($jsonData['foreign']) ? 'magyar' : ($jsonData['percent'] ?? 0) . '%-ban külföldi';
            $income = $jsonData['income'] ?? 0;
            $incomeInThousands = (float) $income / 1000;
            $formattedIncome = number_format($incomeInThousands, 0, ',', '.');
            $templateProcessor->setValue('company_name', $xmlEscape($companyName));
            $templateProcessor->setValue('foundation_year', $xmlEscape($jsonData['foundationYear'] ?? ''));
            $templateProcessor->setValue('owner_percentage', $xmlEscape($ownerPercentageText));
            $templateProcessor->setValue('company_product', $xmlEscape($jsonData['mainActivity'] ?? ''));
            $templateProcessor->setValue('company_place', $xmlEscape($jsonData['companyPlace'] ?? ''));
            $templateProcessor->setValue('data_year', $xmlEscape($jsonData['dataYear'] ?? ''));
            $templateProcessor->setValue('employee_count', $xmlEscape($jsonData['employeeCount'] ?? ''));
            $templateProcessor->setValue('profit', $xmlEscape($formattedIncome ?? '') . ' ');

            /*$stmt = $db->prepare("
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

                    $templateProcessor->setValue("carrier#{$i}", $xmlEscape($row['carrier_name'] ?? ''));
                    $templateProcessor->setValue("carrier_building#{$i}", $xmlEscape($row['carrier_building'] ?? ''));
                    $templateProcessor->setValue("carrier_product#{$i}", $xmlEscape($row['carrier_product'] ?? ''));
                    $templateProcessor->setValue("carrier_vehicle#{$i}", $xmlEscape($row['carrier_vehicle'] ?? ''));
                    $templateProcessor->setValue("carrier_total#{$i}", $xmlEscape($row['carrier_total'] ?? ''));
                }
            } else {
                $templateProcessor->setValue('carrier', $xmlEscape('Nincs adat'));
                $templateProcessor->setValue('carrier_building', $xmlEscape('-'));
                $templateProcessor->setValue('carrier_product', $xmlEscape('-'));
                $templateProcessor->setValue('carrier_vehicle', $xmlEscape('-'));
                $templateProcessor->setValue('carrier_total', $xmlEscape('-'));
            }

            // Fogyasztások felosztása

            if (!empty($carrierRows)) {
                $templateProcessor->cloneRow('carrier2', count($carrierRows));

                foreach ($carrierRows as $index => $row) {
                    $i = $index + 1;

                    $templateProcessor->setValue("carrier2#{$i}", $xmlEscape($row['carrier_name'] ?? ''));
                    $templateProcessor->setValue("carrier2_building#{$i}", $xmlEscape($row['carrier_building'] ?? ''));
                    $templateProcessor->setValue("carrier2_product#{$i}", $xmlEscape($row['carrier_product'] ?? ''));
                    $templateProcessor->setValue("carrier2_vehicle#{$i}", $xmlEscape($row['carrier_vehicle'] ?? ''));
                }
            } else {
                $templateProcessor->setValue('carrier2', $xmlEscape('Nincs adat'));
                $templateProcessor->setValue('carrier2_building', $xmlEscape('-'));
                $templateProcessor->setValue('carrier2_product', $xmlEscape('-'));
                $templateProcessor->setValue('carrier2_vehicle', $xmlEscape('-'));
            }

            if ($hasValidImage) {
                $templateProcessor->setImageValue('sankey_diagram', [
                    'path' => $sankeyImagePath,
                    'width' => 600,
                    'height' => 300,
                    'ratio' => true
                ]);
            } else {
                $templateProcessor->setValue('sankey_diagram', $xmlEscape('A diagram nem áll rendelkezésre.'));
            }
            if (file_exists($sankeyImagePath)) {
                @unlink($sankeyImagePath);
            }

            //Pénzügyi kalkuláció
            $bubor = $jsonData['buborPercent'] ?? 0;
            $bond = $jsonData['bondPercent'] ?? 0;
            $mnb = $jsonData['mnbPercent'] ?? 0;

            $templateProcessor->setValue('bubor_rate', $xmlEscape($bubor));
            $templateProcessor->setValue('bond_rate', $xmlEscape($bond));
            $templateProcessor->setValue('mnb_rate', $xmlEscape($mnb));

            $interest_rate = 0.3 * (((float) $bubor) / 100) + 0.5 * (((float) $bond) / 100) + 0.2 * (((float) $mnb) / 100);
            $interest_rate = round(($interest_rate + 0.03) * 100, 2);
            $templateProcessor->setValue('interest_rate', $xmlEscape($interest_rate));

            // Költségek kalkulációja
            $templateProcessor->setValue('current_date', date('Y.m.d'));

            $stmt = $db->prepare("SELECT date_from, date_to FROM standings WHERE project_id=:projectId LIMIT 1");
            $stmt->execute([':projectId' => $project_id]);
            $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($dates) && isset($dates[0]['date_from'], $dates[0]['date_to'])) {
                $dateFrom = new DateTime($dates[0]['date_from']);
                $dateTo = new DateTime($dates[0]['date_to']);
                $auditInterval = $dateFrom->format('Y.m.d') . ' - ' . $dateTo->format('Y.m.d');
            } else {
                $auditInterval = 'Nincs megadva';
            }

            $templateProcessor->setValue('audit_interval', $xmlEscape($auditInterval));

            $marketData = EnergyPriceService::getMarketPrices();

            $eurHuf = $marketData['eur_huf_rate'] ?? 0;
            $gasEurMwh = $marketData['natural_gas_price'] ?? 0;
            $electricEurMwh = $marketData['electric_energy_price'] ?? 0;

            $gasConverted = ($gasEurMwh * $eurHuf) / 1000;
            $electricConverted = ($electricEurMwh * $eurHuf) / 1000;

            $gasSpecific = $gasConverted + 15.0;
            $electricSpecific = $electricConverted + 30.915;

            $templateProcessor->setValue('eur_huf_rate', $xmlEscape(number_format($eurHuf, 2, ',', ' ')));
            $templateProcessor->setValue('natural_gas_price', $xmlEscape(number_format($gasEurMwh, 2, ',', ' ')));
            $templateProcessor->setValue('electric_energy_price', $xmlEscape(number_format($electricEurMwh, 2, ',', ' ')));

            $templateProcessor->setValue('natural_gas_converted', $xmlEscape(number_format($gasConverted, 2, ',', ' ')));
            $templateProcessor->setValue('natural_gas_specific', $xmlEscape(number_format($gasSpecific, 2, ',', ' ')));

            $templateProcessor->setValue('electric_converted', $xmlEscape(number_format($electricConverted, 2, ',', ' ')));
            $templateProcessor->setValue('electric_energy_specific', $xmlEscape(number_format($electricSpecific, 3, ',', ' ')));

            // 2. Telephelyek táblázat
            $stmt = $db->prepare("SELECT complex_json FROM complex WHERE project_id = :projectId");
            $stmt->execute([':projectId' => $project_id]);
            $complexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $templateProcessor->setValue('telephelyein', count($complexes) > 1 ? 'telephelyein' : 'telephelyén');

            if (!empty($complexes)) {
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

                    $addressText = $xmlEscape($fieldJson['postal'] ?? '') . ' ' .
                        $xmlEscape($fieldJson['city'] ?? '') . ', ' .
                        $xmlEscape($fieldJson['address'] ?? '') . ' (' .
                        $xmlEscape($fieldJson['name'] ?? '') . ')';

                    $cell->addText(
                        $addressText,
                        ['bold' => true, 'size' => 11]
                    );

                    if (!empty($fieldJson['working']) && is_array($fieldJson['working'])) {
                        foreach ($fieldJson['working'] as $working) {
                            $complexTable->addRow();

                            $workTypeSafe = $xmlEscape($working['workType'] ?? '');
                            $workHoursSafe = $xmlEscape($working['workHours'] ?? '');

                            $complexTable->addCell(4500)->addText('• Tevékenység: ' . $workTypeSafe, ['size' => 10]);
                            $complexTable->addCell(4500)->addText('Munkarend: ' . $workHoursSafe, ['size' => 10, 'italic' => true]);
                        }
                    }

                    if ($index < count($complexes) - 1) {
                        $complexTable->addRow();
                        $complexTable->addCell(9000, ['gridSpan' => 2])->addText('');
                    }
                }
                $templateProcessor->setComplexValue('complex_data', $complexTable);
            } else {
                $templateProcessor->setValue('complex_data', $xmlEscape('Nincs megadott telephely adat.'));
            }

            // 3. Fogyasztási táblázat
            $stmt = $db->prepare("SELECT c.id as complex_id, c.name, st.source, st.measurement, st.consumption 
                          FROM complex c 
                          JOIN standings_to_other s ON s.type='COMPLEX' AND s.reference=c.id 
                          JOIN standings st ON st.id=s.standing 
                          WHERE st.measurement_type='MAIN' AND c.project_id=:projectId");
            $stmt->execute([':projectId' => $project_id]);

            $groupedComplexData = AuditService::processMonthlyConsumption($stmt->fetchAll(PDO::FETCH_ASSOC));

            if (!empty($groupedComplexData)) {
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

                        $breakCell->addPageBreak();
                    }
                    $isFirstComplex = false;

                    $mainTable->addRow(250, ['cantSplit' => true]);
                    $mainTable->addCell(9000, ['gridSpan' => 3, 'bgColor' => 'D9D9D9', 'valign' => 'center'])
                        ->addText('Mérési pont / Telephely: ' . $xmlEscape($complexTitle), ['bold' => true, 'size' => 10]);

                    $mainTable->addRow(220, ['tblHeader' => true, 'cantSplit' => true]);
                    $mainTable->addCell(3000, ['bgColor' => 'F2F2F2', 'valign' => 'center'])->addText('Hónap', ['bold' => true, 'size' => 9.5], ['alignment' => 'center']);
                    $mainTable->addCell(3500, ['bgColor' => 'F2F2F2', 'valign' => 'center'])->addText('Energiahordozó', ['bold' => true, 'size' => 9.5], ['alignment' => 'center']);
                    $mainTable->addCell(2500, ['bgColor' => 'F2F2F2', 'valign' => 'center'])->addText('Fogyasztás', ['bold' => true, 'size' => 9.5], ['alignment' => 'center']);

                    ksort($groupedData);

                    foreach ($groupedData as $sortKey => $monthData) {
                        $monthLabel = $xmlEscape($monthData['label']);
                        $items = array_values($monthData['items']);

                        foreach ($items as $index => $item) {
                            $mainTable->addRow(200, ['cantSplit' => true]);

                            if ($index === 0) {
                                $mainTable->addCell(3000, ['vMerge' => 'restart', 'valign' => 'center'])
                                    ->addText($monthLabel, ['bold' => true, 'size' => 9.5], ['alignment' => 'center']);
                            } else {
                                $mainTable->addCell(3000, ['vMerge' => 'continue']);
                            }

                            $sourceSafe = $xmlEscape($item['source']);
                            $mainTable->addCell(3500, ['valign' => 'center'])->addText($sourceSafe, ['size' => 9.5]);

                            $formattedValue = number_format($item['value'], 0, ',', ' ') . ' ' . $xmlEscape($item['unit']);
                            $mainTable->addCell(2500, ['valign' => 'center'])->addText($formattedValue, ['size' => 9.5], ['alignment' => 'right']);
                        }
                    }
                }
                $templateProcessor->setComplexValue('standings_data_by_complex', $mainTable);
            } else {
                $templateProcessor->setValue('standings_data_by_complex', $xmlEscape('Nincs elérhető mérési adat telephelyenként.'));
            }

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

            if (!empty($mainStandings)) {
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
            } else {
                $templateProcessor->setValue('standing_hierarchy', $xmlEscape('Nincs elérhető mérési hierarchia adat.'));
            }

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
            AuditService::buildBuildingsTable($buildingsTable, $allBuildings, $improveable_list);

            $templateProcessor->setComplexValue('building_listing', $buildingsTable);

            // - Fűtési rendszerek értékelése
            $starterIndex = 2;

            // --- 1. FŰTÉSI RENDSZEREK ---
            $stmt = $db->prepare("SELECT h.heaters, h.emitters, c.name as complex_name FROM heating_systems h JOIN complex c ON h.complex=c.id WHERE h.project_id=:projectId AND (purpose='HEAT' OR purpose='BOTH')");
            $stmt->execute([':projectId' => $project_id]);
            $allHeating = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $heatingTable = new \PhpOffice\PhpWord\Element\Table([
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);

            if (empty($allHeating)) {
                $templateProcessor->setValue("subheading_building_heating", "");
                $templateProcessor->setValue("heating_intro", "");
                $templateProcessor->setValue("heating_subtext", "");
                $templateProcessor->setValue('heating_listing', "");
            } else {
                $templateProcessor->setValue("subheading_building_heating", "7." . $starterIndex . ". Épületek fűtése");
                AuditService::buildHeatingTable($heatingTable, $allHeating, $improveable_list);
                $heatingIntro = "A(z) " . $xmlEscape($companyName) . " az alábbi fűtési rendszerekkel rendelkezik:";
                $heatingSubtext = "A pontszám megállapításánál figyelembe vett szempontok: karbonintenzitás, elérhetőség, technológia korszerűsége, illetve a berendezés aktuális műszaki állapota.";

                $templateProcessor->setComplexValue('heating_listing', $heatingTable);
                $templateProcessor->setValue("heating_intro", $heatingIntro);
                $templateProcessor->setValue("heating_subtext", $heatingSubtext);
                $starterIndex++;
            }

            // --- 2. HMV RENDSZEREK (Javított biztonságos ellenőrzéssel) ---
            $emittersData = !empty($allHeating) ? json_decode($allHeating[0]['emitters'] ?? '[]', true) : [];

            if (empty($emittersData)) {
                $templateProcessor->setValue("subheading_building_hmv", "");
                $templateProcessor->setValue("hmv_intro", "");
                $templateProcessor->setValue("hmv_subtext", "");
                $templateProcessor->setValue('hmv_listing', "");
            } else {
                $templateProcessor->setValue("subheading_building_hmv", "7." . $starterIndex . ". Használati melegvíz készítés");

                $hmvTable = new \PhpOffice\PhpWord\Element\Table([
                    'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
                ]);
                $hmvIntro = "A(z) " . $xmlEscape($companyName) . " az alábbi használati melegvizes rendszerekkel rendelkezik:";
                $hmvSubtext = "A pontszám megállapításánál figyelembe vett szempontok: melegvíz készítés szabályozási előfeltételei: Időprogram és/vagy hőmérsékleti értékek.";
                AuditService::buildHMVTable($hmvTable, $allHeating, $improveable_list);

                $templateProcessor->setComplexValue("hmv_listing", $hmvTable);
                $templateProcessor->setValue("hmv_intro", $hmvIntro);
                $templateProcessor->setValue("hmv_subtext", $hmvSubtext);

                $starterIndex++;
            }

            // --- 3. VILÁGÍTÁSI RENDSZEREK ---
            $stmt = $db->prepare("SELECT l.name, l.specific_sum, s.consumption, s.source, c.name as complex_name, l.size, b.size as building_size, l.solution FROM lighting_systems l join standings s on l.standing = s.id join complex c on c.id = l.complex join buildings b on b.id=l.building where l.project_id =:projectId");
            $stmt->execute([":projectId" => $project_id]);
            $allLighting = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allLighting)) {
                $templateProcessor->setValue("subheading_building_lighting", "");
                $templateProcessor->setValue("lighting_intro", "");
                $templateProcessor->setValue("lighting_subtext", "");
                $templateProcessor->setValue('lighting_listing', "");
            } else {
                $templateProcessor->setValue("subheading_building_lighting", "7." . $starterIndex . ". Világítási rendszerek");
                $lightingTable = new \PhpOffice\PhpWord\Element\Table([
                    'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
                ]);
                $lightingIntro = "A(z) " . $xmlEscape($companyName) . " az alábbi világítási zónákkal és rendszerekkel rendelkezik:";
                $lightingSubtext = "A fajlagos érték kialakításánál az alábbi szempontok kerültek figyelembevételre: világítótest fajtája (Fénycső, Halogénizzó, LED, stb.), szabályozás módja (Kézi vagy Automatikus működtetés).";

                AuditService::buildLightingTable($lightingTable, $allLighting);
                $templateProcessor->setComplexValue("lighting_listing", $lightingTable);
                $templateProcessor->setValue("lighting_intro", $lightingIntro);
                $templateProcessor->setValue("lighting_subtext", $lightingSubtext);
                $starterIndex++;
            }

            // --- 4. KOMFORTHŰTÉS RENDSZEREK ---
            $stmt = $db->prepare("SELECT h.heaters, c.name FROM heating_systems h JOIN complex c ON h.complex=c.id WHERE h.project_id=:projectId AND (purpose='COOL' OR purpose='BOTH')");
            $stmt->execute([':projectId' => $project_id]);
            $allCooling = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allCooling)) {
                if (method_exists($templateProcessor, 'cloneBlock')) {
                    $templateProcessor->cloneBlock('cooling_blocking', 0, true, false);
                }
                $templateProcessor->setValue("subheading_building_cooling", "");
                $templateProcessor->setValue("cooling_intro", "");
                $templateProcessor->setValue("cooling_subtext", "");
                $templateProcessor->setValue('cooling_listing', "");
            } else {
                $templateProcessor->cloneBlock('cooling_blocking', 1, true, false);
                $templateProcessor->setValue("subheading_building_cooling", "7." . $starterIndex . ". Komforthűtési rendszerek");
                $coolingTable = new \PhpOffice\PhpWord\Element\Table([
                    'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
                ]);

                $coolingIntro = "A(z) " . $xmlEscape($companyName) . " az alábbi komforthűtési rendszerekkel rendelkezik:";
                $coolingSubtext = "A százalékos érték kialakításánál az alábbi szempontok kerültek figyelembevételre: alkalmazott hűtőközeg GWP értéke, berendezés működési módja (időjáráshoz alkalmazkodik vagy sem), berendezés műszaki állapota és az üzemelés körülményei.";

                AuditService::buildCoolingTable($coolingTable, $allCooling, $improveable_list);

                $templateProcessor->setValue("cooling_intro", $coolingIntro);
                $templateProcessor->setValue("cooling_subtext", $coolingSubtext);
                $templateProcessor->setComplexValue('cooling_listing', $coolingTable);
                $starterIndex++;
            }

            // --- 5. LÉGKEZELŐ RENDSZEREK ---
            $stmt = $db->prepare("SELECT v.name, c.name as complex_name, b.name as building_name, v.sfp, v.category, v.json from ventilation_systems v join complex c on c.id=v.complex join buildings b on v.building = b.id WHERE v.project_id=:projectId");
            $stmt->execute([":projectId" => $project_id]);
            $allHvac = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allHvac)) {
                if (method_exists($templateProcessor, 'cloneBlock')) {
                    $templateProcessor->cloneBlock('hvac_blocking', 0, true, false);
                }
                $templateProcessor->setValue("subheading_building_hvac", "");
                $templateProcessor->setValue("hvac_intro", "");
                $templateProcessor->setValue("hvac_subtext", "");
                $templateProcessor->setValue('hvac_listing', "");
            } else {
                $templateProcessor->cloneBlock('hvac_blocking', 1, true, false);
                $templateProcessor->setValue("subheading_building_hvac", "7." . $starterIndex . ". Légtechnikai rendszerek");
                $hvacTable = new \PhpOffice\PhpWord\Element\Table([
                    'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
                ]);

                $hvacIntro = "A(z) " . $xmlEscape($companyName) . " az alábbi légtechnikai rendszerekkel rendelkezik:";
                $hvacSubtext = "Értelmezés: az SFP érték, hővisszanyerési hatékonyság és a szigeteltségi állapot 90% érték alatt fejlesztendő.";

                AuditService::buildHVACTable($hvacTable, $allHvac, $improveable_list);

                $templateProcessor->setComplexValue('hvac_listing', $hvacTable);
                $templateProcessor->setValue("hvac_intro", $hvacIntro);
                $templateProcessor->setValue("hvac_subtext", $hvacSubtext);
                $starterIndex++;
            }

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
                        s.measurement_type,
                        s.measurement, 
                        s.source 
                      FROM vehicles v 
                      JOIN complex c ON v.complex_id = c.id 
                      LEFT JOIN standings s ON v.standing_id = s.id 
                      WHERE v.project_id = :projectId");
            $stmt->execute([':projectId' => $project_id]);
            $vehicleData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($vehicleData)) {
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

                AuditService::buildVehiclesTable($vehiclesTable, $vehicleData, $improveable_list);

                $templateProcessor->setComplexValue('vehicle_listing', $vehiclesTable);
            } else {
                $templateProcessor->setValue('vehicle_listing', $xmlEscape('Nincs megadott szállítási / jármű adat.'));
            }

            //Technológia értékelése
            $stmt = $db->prepare("SELECT t.id, t.name, t.json, c.name as complex_name, t.technology_type FROM technology t join complex c on t.complex = c.id WHERE t.project_id = :projectId ORDER BY id ASC");
            $stmt->execute([':projectId' => $project_id]);
            $technologies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($technologies)) {
                $templateProcessor->setValue('technology_title', '');
                $templateProcessor->setValue('technology_content', '');

                $templateProcessor->setValue("technology_offset_index", 9);
                $templateProcessor->setValue("technology_offset_2_index", 10);
                $templateProcessor->setValue("technology_offset_3_index", 11);

                if (method_exists($templateProcessor, 'deleteBlock')) {
                    @$templateProcessor->deleteBlock('block_technology');
                }
            } else {
                if (method_exists($templateProcessor, 'cloneBlock')) {
                    @$templateProcessor->cloneBlock('block_technology', 1, true, false);
                }

                $templateProcessor->setValue('technology_title', '9.Technológiai alrendszerek energetikai értékelése');

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

                $safeCompanyName = $xmlEscape($companyName ?? 'GAZDÁLKODÓ SZERVEZET');
                $mainCell->addText(
                    "A " . $safeCompanyName . "-nál/nél az alábbi technológiai alrendszerek kerültek kialakításra:",
                    null,
                    ['spaceAfter' => 120]
                );
                $letterIndex = 'a';

                if (!empty($groupedTechs['COMPRESSED_AIR'])) {
                    $mainCell->addText($letterIndex . ") Sűrített levegős hálózat", ['bold' => true, 'size' => 11]);

                    foreach ($groupedTechs['COMPRESSED_AIR'] as $tech) {
                        $jsonData = json_decode($tech['json'], true) ?? [];
                        AuditService::appendCompressedAirTableToCell($mainCell, $jsonData, $tech['name'], $tech['complex_name'], $meters, $improveable_list);
                        $mainCell->addText("");
                    }

                    $letterIndex = chr(ord($letterIndex) + 1);
                }

                if (!empty($groupedTechs['STEAM'])) {
                    $mainCell->addText($letterIndex . ") Gőzrendszer", ['bold' => true, 'size' => 11]);

                    foreach ($groupedTechs['STEAM'] as $tech) {
                        $jsonData = json_decode($tech['json'], true) ?? [];
                        $complexName = $tech['complex_name'] ?? '';

                        AuditService::appendSteamTableToCell($mainCell, $jsonData, $tech['name'], $complexName, $meters, $improveable_list);
                        $mainCell->addText("");
                    }

                    $letterIndex = chr(ord($letterIndex) + 1);
                }

                if (!empty($groupedTechs['COOLING'])) {
                    $mainCell->addText($letterIndex . ") Technológiai hűtés", ['bold' => true, 'size' => 11]);

                    foreach ($groupedTechs['COOLING'] as $tech) {
                        $jsonData = json_decode($tech['json'], true) ?? [];
                        AuditService::appendCoolingTableToCell($mainCell, $jsonData, $tech['name'], $meters, $improveable_list);
                        $mainCell->addText("");
                    }

                    $letterIndex = chr(ord($letterIndex) + 1);
                }

                if (!empty($groupedTechs['OTHER'])) {
                    $mainCell->addText($letterIndex . ") Egyéb technológiai hőhasználat", ['bold' => true, 'size' => 11]);

                    foreach ($groupedTechs['OTHER'] as $tech) {
                        $jsonData = json_decode($tech['json'], true) ?? [];
                        $complexName = $tech['complex_name'] ?? '';

                        AuditService::appendOtherTableToCell($mainCell, $jsonData, $tech['name'], $complexName, $meters, $improveable_list);
                        $mainCell->addText("");
                    }

                    $letterIndex = chr(ord($letterIndex) + 1);
                }

                $templateProcessor->setComplexBlock('technology_content', $mainTable);

                // Technológia offset indexek fejezeteknek
                $templateProcessor->setValue("technology_offset_index", 10);
                $templateProcessor->setValue("technology_offset_2_index", 11);
                $templateProcessor->setValue("technology_offset_3_index", 12);
            }

            // Energia teljesítmény mutató

            $stmt = $db->prepare("SELECT product_name, metric, is_primary, json FROM product WHERE project_id=:projectId");
            $stmt->execute([":projectId" => $project_id]);
            $allProduct = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($allProduct)) {
                $productTable = new \PhpOffice\PhpWord\Element\Table([
                    'borderSize' => 6,
                    'borderColor' => '000000',
                    'cellMargin' => 80
                ]);
                AuditService::buildProductTable($productTable, $allProduct, $auditInterval);
                $templateProcessor->setComplexValue("product_listing", $productTable);
            } else {
                $templateProcessor->setValue('product_listing', $xmlEscape('Nincs megadott termék adat.'));
            }

            $totalActivityEnergyKwH = 0.0;

            if (!empty($carrierRows)) {
                foreach ($carrierRows as $cRow) {
                    $cleanProductValue = str_replace([' ', ','], ['', '.'], $cRow['carrier_product'] ?? '0');
                    $totalActivityEnergyKwH += (float) $cleanProductValue;
                }
            }

            $primaryProduct = null;
            foreach ($allProduct as $prod) {
                if (!empty($prod['is_primary'])) {
                    $primaryProduct = $prod;
                    break;
                }
            }

            if (!$primaryProduct && !empty($allProduct)) {
                $primaryProduct = $allProduct[0];
            }

            if ($primaryProduct) {
                $rawProductName = $primaryProduct['product_name'] ?? '';
                $metric = $primaryProduct['metric'] ?? '';

                $productNameLabel = $rawProductName;
                if (!empty($metric)) {
                    $productNameLabel .= ' (' . $metric . ')';
                }

                $templateProcessor->setValue('product_name', $xmlEscape($productNameLabel));

                $jsonData = json_decode($primaryProduct['json'] ?? '{}', true);
                $primaryAmountSum = (float) ($jsonData['sum'] ?? 0.0);

                if ($primaryAmountSum > 0) {
                    $etmValue = $totalActivityEnergyKwH / $primaryAmountSum;
                    $formattedEtm = number_format($etmValue, 2, ',', ' ') . ' kWh/' . $metric;

                    $templateProcessor->setValue('ETM', $xmlEscape($formattedEtm));
                } else {
                    $templateProcessor->setValue('ETM', $xmlEscape('-'));
                }
            } else {
                $templateProcessor->setValue('product_name', $xmlEscape('főtermék'));
                $templateProcessor->setValue('ETM', $xmlEscape('-'));
            }

            $xmlEscape = function ($val) {
                return htmlspecialchars($val ?? '', ENT_XML1, 'UTF-8');
            };

            $formatItems = function (array $items) use ($xmlEscape): string {
                if (empty($items)) {
                    return 'Nem azonosítottunk fejlesztési lehetőséget.';
                }
                return implode(', ', array_map($xmlEscape, $items));
            };

            $templateProcessor->setValue('suggestion_a', $xmlEscape($formatItems($improveable_list['building'] ?? [])));

            $mepItems = array_merge(
                $improveable_list['hmv'] ?? [],
                $improveable_list['coolers'] ?? [],
                $improveable_list['hvac'] ?? []
            );
            $templateProcessor->setValue('suggestion_b', $xmlEscape($formatItems($mepItems)));
            $templateProcessor->setValue('suggestion_c', $xmlEscape($formatItems($improveable_list['technology'] ?? [])));
            $templateProcessor->setValue('suggestion_d', $xmlEscape($formatItems($improveable_list['vehicle'] ?? [])));
            $templateProcessor->setValue('suggestion_e', $xmlEscape($formatItems($improveable_list['heaters'] ?? [])));
            */

            // 5. Letöltés és takarítás
            $tempFileName = 'dokumentacio_' . time() . '.docx';
            $outputDir = __DIR__ . '/generated';
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            $tempPath = $outputDir . '/' . $tempFileName;
            $templateProcessor->saveAs($tempPath);

            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="generalt_dokumentacio.docx"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($tempPath));

            readfile($tempPath);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            echo "Hiba történt a dokumentum generálása során: " . $e->getMessage();
        }
    }
}
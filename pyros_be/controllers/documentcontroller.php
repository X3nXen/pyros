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
            //1-2. Vállalkozás bemutatása
            $stmt = $db->prepare("SELECT json FROM variables WHERE project_id = :projectId");
            $stmt->execute([':projectId' => $project_id]);
            $jsonData = json_decode($stmt->fetchColumn() ?: '{}', true);

            AuditService::createIntroductionChapter($jsonData, $templateProcessor);
            $companyName = $jsonData['fullName'];

            $stmt = $db->prepare("
    SELECT id, name, measurement_type, sub_to, source, measurement, consumption, purpose 
    FROM standings 
    WHERE project_id = :projectId
");
            $stmt->execute([':projectId' => $project_id]);
            $standings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //1. Vezetői összefoglaló táblázata és a sankey diagram

            $carrierRows = AuditService::calculateTotalConsumptionList($standings);

            AuditService::createConsumptionList($carrierRows, $templateProcessor, true);


            if ($hasValidImage) {
                $templateProcessor->setImageValue('sankey_diagram', [
                    'path' => $sankeyImagePath,
                    'width' => 600,
                    'height' => 300,
                    'ratio' => true
                ]);
            } else {
                $templateProcessor->setValue('sankey_diagram', AuditService::xmlEscape('A diagram nem áll rendelkezésre.'));
            }
            if (file_exists($sankeyImagePath)) {
                @unlink($sankeyImagePath);
            }

            // 2. Vállakozás bemutatása

            $stmt = $db->prepare("SELECT complex_json FROM complex WHERE project_id = :projectId");
            $stmt->execute([':projectId' => $project_id]);
            $complexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            AuditService::createComplexesTable($complexes, $templateProcessor);

            // 3. Fogyasztási adatok

            $stmt = $db->prepare("SELECT c.id as complex_id, c.name, st.source, st.measurement, st.consumption 
                                      FROM complex c 
                                      JOIN standings_to_other s ON s.type='COMPLEX' AND s.reference=c.id 
                                      JOIN standings st ON st.id=s.standing 
                                      WHERE st.measurement_type='MAIN' AND c.project_id=:projectId");
            $stmt->execute([':projectId' => $project_id]);
            $standingDataByComplex = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $groupedData = AuditService::processMonthlyConsumptionList($standingDataByComplex);

            AuditService::createStandingByComplexSection($groupedData, $templateProcessor);

            // 4. Mérési hálózatok

            $stmt = $db->prepare("SELECT id, name, measurement_type, sub_to, source, measurement, consumption 
                                      FROM standings WHERE project_id = :projectId ORDER BY id ASC");
            $stmt->execute([':projectId' => $project_id]);
            $allStandings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $hierarchy_data = AuditService::buildStandingHierarchy($allStandings);
            AuditService::createStandingHierarchySection($hierarchy_data, $templateProcessor);

            // 5. Költségek kalkulációja

            $stmt = $db->prepare("SELECT date_from, date_to FROM standings WHERE project_id=:projectId LIMIT 1");
            $stmt->execute([':projectId' => $project_id]);
            $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            AuditService::createCurrentPricesSection($dates, $templateProcessor);

            // 6. Pénzügyi környezet kalkuláció

            AuditService::createInvestmentSection($jsonData, $templateProcessor);

            // 7. Épületek energetikai értékelése
            // 7.1 - Épületfizikai értékelés

            $stmt = $db->prepare("SELECT b.name as building_name, b.qf, c.name as complex_name FROM buildings b join complex c on c.id = b.complex WHERE b.project_id=:projectId");
            $stmt->execute([
                ':projectId' => $project_id
            ]);
            $allBuildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $buildingData = AuditService::buildBuildingRows($allBuildings, $improveable_list);
            AuditService::createBuildingListingSection($buildingData, $templateProcessor);

            // 7.2? - Épületek fűtése
            $starterIndex = 2;
            $stmt = $db->prepare("SELECT h.heaters, h.emitters, c.name as complex_name FROM heating_systems h JOIN complex c ON h.complex=c.id WHERE h.project_id=:projectId AND (purpose='HEAT' OR purpose='BOTH')");
            $stmt->execute([':projectId' => $project_id]);
            $allHeating = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $heaterData = AuditService::buildHeatingListingRows($allHeating, $improveable_list);
            AuditService::createHeatingListingSection($heaterData, $templateProcessor, $starterIndex, $companyName);

            //7.3? - HMV készítés - later, when change is implemented regarding HMV systems
            $hmvData = [];
            AuditService::createHMVListingSection($hmvData, $templateProcessor, $starterIndex, $companyName);
            //7.4? - Világítási rendszerek

            $stmt = $db->prepare("SELECT l.name, l.specific_sum, s.consumption, s.source, c.name as complex_name, l.size, b.size as building_size, l.solution FROM lighting_systems l join standings s on l.standing = s.id join complex c on c.id = l.complex join buildings b on b.id=l.building where l.project_id =:projectId");
            $stmt->execute([":projectId" => $project_id]);
            $allLighting = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $lightingData = AuditService::buildLightingListingRows($allLighting, $improveable_list);
            AuditService::createLightingListingSection($lightingData, $templateProcessor, $starterIndex, $companyName);

            //7.5? - Hűtési rendszerek
            $stmt = $db->prepare("SELECT h.heaters, c.name FROM heating_systems h JOIN complex c ON h.complex=c.id WHERE h.project_id=:projectId AND (purpose='COOL' OR purpose='BOTH')");
            $stmt->execute([':projectId' => $project_id]);
            $allCooling = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $coolingData = AuditService::buildCoolingListingRows($allCooling, $improveable_list);
            AuditService::createCoolingListingSection($coolingData, $templateProcessor, $starterIndex, $companyName);

            //7.6? - Légkezelő rendszerek

            $stmt = $db->prepare("SELECT v.name, c.name as complex_name, b.name as building_name, v.sfp, v.category, v.json from ventilation_systems v join complex c on c.id=v.complex join buildings b on v.building = b.id WHERE v.project_id=:projectId");
            $stmt->execute([":projectId" => $project_id]);
            $allHvac = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $hvacData = AuditService::buildHVACListingRows($allHvac, $improveable_list);
            AuditService::createHVACListingSection($hvacData, $templateProcessor, $starterIndex, $companyName);

            // 8. - Szállítás értékelése
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

            $vehicleListing = AuditService::buildVehicleListingRows($vehicleData, $improveable_list);
            AuditService::createVehicleListingSection($vehicleListing, $templateProcessor);

            // 10. Technológia értékelése
            $sectionOffset = 9;
            $stmt = $db->prepare("SELECT t.id, t.name, t.json, c.name as complex_name, t.technology_type FROM technology t join complex c on t.complex = c.id WHERE t.project_id = :projectId ORDER BY id ASC");
            $stmt->execute([':projectId' => $project_id]);
            $technologies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            AuditService::createTechnologySection($technologies, $templateProcessor, $sectionOffset);

            //11. Fogyasztások felosztása

            AuditService::createConsumptionList($carrierRows, $templateProcessor, false);

            // 12?. Energia teljesítmény mutató meghatározása

            $stmt = $db->prepare("SELECT product_name, metric, is_primary, json FROM product WHERE project_id=:projectId");
            $stmt->execute([":projectId" => $project_id]);
            $allProduct = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $productListing = AuditService::buildProductListingRows($allProduct, $carrierRows);
            AuditService::createProductListingSection($productListing, $templateProcessor);

            // 13?. Javaslatok és források
            AuditService::createImproveableListingSection($improveable_list, $templateProcessor);
            /*

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
                            $hmvIntro = "A(z) " . AuditService::xmlEscape($companyName) . " az alábbi használati melegvizes rendszerekkel rendelkezik:";
                            $hmvSubtext = "A pontszám megállapításánál figyelembe vett szempontok: melegvíz készítés szabályozási előfeltételei: Időprogram és/vagy hőmérsékleti értékek.";
                            AuditService::buildHMVTable($hmvTable, $allHeating, $improveable_list);

                            $templateProcessor->setComplexValue("hmv_listing", $hmvTable);
                            $templateProcessor->setValue("hmv_intro", $hmvIntro);
                            $templateProcessor->setValue("hmv_subtext", $hmvSubtext);

                            $starterIndex++;
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

                            $safeCompanyName = AuditService::xmlEscape($companyName ?? 'GAZDÁLKODÓ SZERVEZET');
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
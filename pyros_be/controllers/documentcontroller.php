<?php

use PhpOffice\PhpWord\TemplateProcessor;
require_once __DIR__ . "/../database.php";
require_once __DIR__ . "/../services/AuditService.php";
class DocumentController
{
    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->handleGet();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'A kért HTTP metódus nem támogatott']);
        }
    }

    private function handleGet()
    {
        $project_id = $_GET['project_id'];
        try {
            $templateProcessor = new TemplateProcessor(__DIR__ . '/audit_template.docx');
            $db = Database::getConnection();

            // 1. Vállalkozás bemutatása
            $stmt = $db->prepare("SELECT json FROM variables WHERE project_id = :projectId");
            $stmt->execute([':projectId' => $project_id]);
            $jsonData = json_decode($stmt->fetchColumn() ?: '{}', true);

            $templateProcessor->setValue('company_name', $jsonData['fullName'] ?? '');
            $templateProcessor->setValue('foundation_year', $jsonData['foundationYear'] ?? '');
            $templateProcessor->setValue('owner_percentage', !empty($jsonData['foreign']) ? 'magyar' : ($jsonData['percent'] ?? 0) . '%-ban külföldi');
            $templateProcessor->setValue('company_product', $jsonData['mainActivity'] ?? '');
            $templateProcessor->setValue('company_place', $jsonData['companyPlace'] ?? '');
            $templateProcessor->setValue('data_year', $jsonData['dataYear'] ?? '');
            $templateProcessor->setValue('employee_count', $jsonData['employeeCount'] ?? '');
            $templateProcessor->setValue('profit', $jsonData['income'] ?? '');

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
            $stmt = $db->prepare("SELECT c.id as complex_id, c.name, c.pod_id, st.source, st.measurement, st.consumption 
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

            $stmt = $db->prepare("SELECT 
                        v.name AS vehicle_name, 
                        c.name AS complex_name, 
                        v.usage_value, 
                        v.usage_value2, 
                        v.usage_metric, 
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
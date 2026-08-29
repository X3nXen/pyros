<?php

use PhpOffice\PhpWord\TemplateProcessor;
require_once __DIR__ . "/../database.php";
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

            //Vállalkozás bemutatása

            $db = Database::getConnection();
            $sql = "SELECT * FROM variables WHERE project_id=:projectId";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':projectId' => $project_id
            ]);

            $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $jsonData = json_decode($rawData[0]['json'], true);

            $templateProcessor->setValue('company_name', $jsonData['fullName']);
            $templateProcessor->setValue('foundation_year', $jsonData['foundationYear']);
            $templateProcessor->setValue('owner_percentage', ((bool) $jsonData['foreign'] ? 'magyar' : $jsonData['percent'] . '%-ban külföldi'));
            $templateProcessor->setValue('company_product', $jsonData['mainActivity']);
            $templateProcessor->setValue('company_place', $jsonData['companyPlace']);

            $templateProcessor->setValue('data_year', $jsonData['dataYear']);
            $templateProcessor->setValue('employee_count', $jsonData['employeeCount']);
            $templateProcessor->setValue('profit', $jsonData['income']);

            //Telephelyek

            $sql = "SELECT id, complex_json FROM complex WHERE project_id=:projectId";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':projectId' => $project_id
            ]);
            $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $table = new \PhpOffice\PhpWord\Element\Table([
                'borderSize' => 0,
                'borderColor' => 'FFFFFF',
                'afterSpacing' => 100,
            ]);

            foreach ($rawData as $index => $field) {
                $field_json = json_decode($field['complex_json'], true);

                if (!$field_json) {
                    continue;
                }

                $table->addRow();
                $cell = $table->addCell(9000, ['gridSpan' => 2]);
                $cell->addText(
                    $field_json['postal'] . ' ' . $field_json['city'] . ', ' . $field_json['address'] . ' (' . $field_json['name'] . ')',
                    ['bold' => true, 'size' => 11]
                );

                if (!empty($field_json['working']) && is_array($field_json['working'])) {
                    foreach ($field_json['working'] as $working) {
                        $table->addRow();

                        $leftCell = $table->addCell(4500);
                        $leftCell->addText('• Tevékenység: ' . $working['workType'], ['size' => 10]);

                        $rightCell = $table->addCell(4500);
                        $rightCell->addText('Munkarend: ' . $working['workHours'], ['size' => 10, 'italic' => true]);
                    }
                }

                if ($index < count($rawData) - 1) {
                    $table->addRow();
                    $table->addCell(9000, ['gridSpan' => 2])->addText('');
                }
            }

            $templateProcessor->setComplexValue('complex_data', $table);

            //Fogyasztási adatok
            $sql = "select c.name, c.pod_id, st.source, st.measurement, st.consumption, st.date_from, st.date_TO from complex c join standings_to_other s on s.type='COMPLEX' and s.reference=c.id join standings st on st.id=s.standing WHERE st.measurement_type='MAIN' AND c.project_id=:projectId";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':projectId' => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT c.id as complex_id, c.name, c.pod_id, st.source, st.measurement, st.consumption, st.date_from, st.date_TO 
        FROM complex c 
        JOIN standings_to_other s ON s.type='COMPLEX' AND s.reference=c.id 
        JOIN standings st ON st.id=s.standing 
        WHERE st.measurement_type='MAIN' AND c.project_id=:projectId";
            $stmt = $db->prepare($sql);
            $stmt->execute([':projectId' => $project_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT c.id as complex_id, c.name, c.pod_id, st.source, st.measurement, st.consumption, st.date_from, st.date_TO 
        FROM complex c 
        JOIN standings_to_other s ON s.type='COMPLEX' AND s.reference=c.id 
        JOIN standings st ON st.id=s.standing 
        WHERE st.measurement_type='MAIN' AND c.project_id=:projectId";
            $stmt = $db->prepare($sql);
            $stmt->execute([':projectId' => $project_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $energySourcesMap = [
                'COAL' => 'Szén',
                'GASOLINE' => 'Gázolaj',
                'PETROL' => 'Benzin',
                'GAS' => 'Földgáz',
                'ELECTRICITY' => 'Elektromos áram',
                'REMOTE' => 'Távhő',
                'PAKURA' => 'Pakura',
                'PB' => 'PB Gáz',
                'PROPANE' => 'Propán',
                'LPG' => 'LPG',
                'WOOD' => 'Tűzifa',
                'SOLAR' => 'Napenergia'
            ];

            $energyMeasurementsMap = [
                'KWH' => 'kWh',
                'MJ' => 'MJ',
                'MCUBE' => 'm³',
                'GJ' => 'GJ',
                'MWH' => 'MWh'
            ];

            $monthMap = [
                'jan' => ['name' => 'január', 'num' => '01'],
                'feb' => ['name' => 'február', 'num' => '02'],
                'mar' => ['name' => 'március', 'num' => '03'],
                'apr' => ['name' => 'április', 'num' => '04'],
                'may' => ['name' => 'május', 'num' => '05'],
                'jun' => ['name' => 'június', 'num' => '06'],
                'jul' => ['name' => 'július', 'num' => '07'],
                'aug' => ['name' => 'augusztus', 'num' => '08'],
                'sep' => ['name' => 'szeptember', 'num' => '09'],
                'oct' => ['name' => 'október', 'num' => '10'],
                'nov' => ['name' => 'november', 'num' => '11'],
                'dec' => ['name' => 'december', 'num' => '12'],
            ];

            $complexesData = [];

            foreach ($data as $row) {
                if (!empty($row['pod_id']) && !empty($row['name'])) {
                    $complexLabel = $row['pod_id'] . ' / ' . $row['name'];
                } else {
                    $complexLabel = !empty($row['name']) ? $row['name'] : $row['pod_id'];
                }

                $rawSource = $row['source'];
                $rawUnit = $row['measurement'] ?? '';

                $sourceLabel = $energySourcesMap[$rawSource] ?? $rawSource;
                $unitLabel = $energyMeasurementsMap[$rawUnit] ?? $rawUnit;

                $consumptionJson = json_decode($row['consumption'], true);

                if (is_array($consumptionJson)) {
                    foreach ($consumptionJson as $year => $months) {
                        if (!is_array($months))
                            continue;

                        foreach ($months as $monthKey => $value) {
                            if ($value !== null && isset($monthMap[$monthKey])) {
                                $sortKey = $year . '-' . $monthMap[$monthKey]['num'];
                                $displayLabel = $year . '. ' . $monthMap[$monthKey]['name'];

                                $finalValue = ($rawSource === 'SOLAR') ? -abs($value) : $value;

                                $complexesData[$complexLabel][$sortKey]['label'] = $displayLabel;

                                if (!isset($complexesData[$complexLabel][$sortKey]['items'][$rawSource])) {
                                    $complexesData[$complexLabel][$sortKey]['items'][$rawSource] = [
                                        'source' => $sourceLabel,
                                        'value' => 0,
                                        'unit' => $unitLabel
                                    ];
                                }

                                $complexesData[$complexLabel][$sortKey]['items'][$rawSource]['value'] += $finalValue;
                            }
                        }
                    }
                }
            }

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

            foreach ($complexesData as $complexTitle => $groupedData) {
                if (!$isFirstComplex) {
                    $mainTable->addRow();
                    $breakCell = $mainTable->addCell(9000, ['gridSpan' => 3, 'borderSize' => 0]);
                    // XML oldaltörés beszúrása
                    $breakCell->addText('<w:br w:type="page"/>');
                }
                $isFirstComplex = false;

                $mainTable->addRow(250, ['cantSplit' => true]);
                $headerCell = $mainTable->addCell(9000, [
                    'gridSpan' => 3,
                    'bgColor' => 'D9D9D9',
                    'valign' => 'center'
                ]);
                $headerCell->addText('Mérési pont / Telephely: ' . $complexTitle, ['bold' => true, 'size' => 10]);

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
                            $mainTable->addCell(3000, [
                                'vMerge' => 'restart',
                                'valign' => 'center'
                            ])->addText($monthLabel, ['bold' => true, 'size' => 9.5], ['alignment' => 'center']);
                        } else {
                            $mainTable->addCell(3000, [
                                'vMerge' => 'continue'
                            ]);
                        }

                        $mainTable->addCell(3500, ['valign' => 'center'])->addText($item['source'], ['size' => 9.5]);

                        $formattedValue = number_format($item['value'], 0, ',', ' ') . ' ' . $item['unit'];
                        $mainTable->addCell(2500, ['valign' => 'center'])->addText($formattedValue, ['size' => 9.5], ['alignment' => 'right']);
                    }
                }
            }

            $templateProcessor->setComplexValue('standings_data_by_complex', $mainTable);

            //mentés
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
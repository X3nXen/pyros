<?php
class AuditService
{
    // Szótárak központosítása statikus tömbökként
    public static array $energySources = [
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

    public static array $energyMeasurements = [
        'KWH' => 'kWh',
        'MJ' => 'MJ',
        'MCUBE' => 'm³',
        'GJ' => 'GJ',
        'MWH' => 'MWh'
    ];

    public static array $months = [
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

    /**
     * Összesített havi fogyasztási adatok feldolgozása telephelyek szerint
     */
    public static function processMonthlyConsumption(array $rawData): array
    {
        $complexesData = [];

        foreach ($rawData as $row) {
            $complexLabel = (!empty($row['name']) ? $row['name'] : "Telephely");

            $rawSource = $row['source'];
            $rawUnit = $row['measurement'] ?? '';

            $sourceLabel = self::$energySources[$rawSource] ?? $rawSource;
            $unitLabel = self::$energyMeasurements[$rawUnit] ?? $rawUnit;

            $consumptionJson = json_decode($row['consumption'], true);

            if (!is_array($consumptionJson)) {
                continue;
            }

            foreach ($consumptionJson as $year => $months) {
                if (!is_array($months))
                    continue;

                foreach ($months as $monthKey => $value) {
                    if ($value !== null && isset(self::$months[$monthKey])) {
                        $sortKey = $year . '-' . self::$months[$monthKey]['num'];
                        $displayLabel = $year . '. ' . self::$months[$monthKey]['name'];

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

        return $complexesData;
    }

    public static function calculateTotalConsumption(?string $consumptionJson, string $source = ''): float
    {
        if (empty($consumptionJson)) {
            return 0;
        }

        $data = json_decode($consumptionJson, true);
        if (!is_array($data)) {
            return 0;
        }

        if (isset($data['total']) && is_numeric($data['total'])) {
            $total = (float) $data['total'];
            return ($source === 'SOLAR') ? -abs($total) : $total;
        }

        $total = 0;
        foreach ($data as $year => $months) {
            if (is_numeric($months)) {
                $total += (float) $months;
                continue;
            }

            if (!is_array($months)) {
                continue;
            }

            foreach ($months as $val) {
                if ($val !== null && is_numeric($val)) {
                    $total += (float) $val;
                }
            }
        }

        return ($source === 'SOLAR') ? -abs($total) : $total;
    }

    public static function getEnergyCarrierSummaryRows(array $standings): array
    {
        $carriers = [];

        foreach ($standings as $row) {
            $rawSource = strtoupper(trim($row['source'] ?? ''));
            if (empty($rawSource)) {
                continue;
            }

            $carrierName = self::$energySources[$rawSource] ?? $rawSource;

            if (!isset($carriers[$carrierName])) {
                $carriers[$carrierName] = [
                    'raw_source' => $rawSource,
                    'total' => 0.0,
                    'building' => 0.0,
                    'service' => 0.0,
                    'carry' => 0.0
                ];
            }

            $consumptionValue = self::calculateTotalConsumption($row['consumption'] ?? '', $rawSource);

            // 1. Teljes fogyasztás gyűjtése (Főmérők vagy önálló mérési pontok)
            if (in_array($row['measurement_type'] ?? '', ['MAIN', 'VIRTUAL']) && empty($row['sub_to'])) {
                $carriers[$carrierName]['total'] += $consumptionValue;
            }

            // 2. Kategóriák szerinti gyűjtés
            $purpose = strtoupper(trim($row['purpose'] ?? ''));
            if ($purpose === 'BUILDING') {
                $carriers[$carrierName]['building'] += $consumptionValue;
            } elseif ($purpose === 'SERVICE') {
                $carriers[$carrierName]['service'] += $consumptionValue;
            } elseif (in_array($purpose, ['CARRY', 'TRANSPORT', 'VEHICLE', 'SZALLITAS'])) {
                $carriers[$carrierName]['carry'] += $consumptionValue;
            }
        }

        $carrierRows = [];

        foreach ($carriers as $name => $data) {
            $building = $data['building'];
            $carry = $data['carry'];
            $total = $data['total'];

            // Biztonsági korrekció: ha a részösszegek meghaladják a total-t (pl. sub-metering miatt), 
            // a total felveszi a részösszegek max értékét.
            $total = max($total, $building + $carry + $data['service']);

            // Tevékenység (Product) = ami megmarad az Épület és Szállítás levonása után
            $product = max(0.0, $total - $building - $carry);

            $carrierRows[] = [
                'carrier_name' => $name,
                'carrier_building' => number_format($building, 0, ',', ' ') . ' kWh',
                'carrier_product' => number_format($product, 0, ',', ' ') . ' kWh',
                'carrier_vehicle' => number_format($carry, 0, ',', ' ') . ' kWh',
                'carrier_total' => number_format($total, 0, ',', ' ') . ' kWh',
            ];
        }

        return $carrierRows;
    }

    public static function buildStandingTree(
        int $standingId,
        array &$standingsById,
        array &$childrenByParent,
        \PhpOffice\PhpWord\Element\Table &$table,
        int $level = 0
    ): void {
        if (!isset($standingsById[$standingId]))
            return;

        $standing = $standingsById[$standingId];
        $totalConsumption = self::calculateTotalConsumption($standing['consumption'], $standing['source']);
        $unitLabel = self::$energyMeasurements[$standing['measurement']] ?? $standing['measurement'];
        $formattedValue = number_format($totalConsumption, 0, ',', ' ') . ' ' . $unitLabel;

        $table->addRow(null, ['cantSplit' => true]);

        $fontStyle = [
            'bold' => ($level === 0),
            'size' => ($level === 0) ? 10 : 9.5
        ];

        $cellOptions = ['valign' => 'top'];

        $nameColWidth = 2000;
        $deadCellColWidth = 3000;
        $valueColWidth = 3000;

        $nameCell = $table->addCell($nameColWidth, $cellOptions);

        if ($level === 0) {
            $nameParagraphStyle = [
                'alignment' => 'left',
                'spaceAfter' => 20,
                'spaceBefore' => 20
            ];
            $nameCell->addText($standing['name'], $fontStyle, $nameParagraphStyle);
        } else {
            $nameParagraphStyle = [
                'alignment' => 'right',
                'rightIndent' => 200,
                'spaceAfter' => 20,
                'spaceBefore' => 20
            ];
            $nameCell->addText('• ' . $standing['name'], $fontStyle, $nameParagraphStyle);
        }

        $deadCell = $table->addCell($deadCellColWidth, $cellOptions);

        $valueCell = $table->addCell($valueColWidth, $cellOptions);
        $valueParagraphStyle = [
            'alignment' => 'left',
            'spaceAfter' => 20,
            'spaceBefore' => 20
        ];
        $valueCell->addText('Fogyasztás: ' . $formattedValue, $fontStyle, $valueParagraphStyle);

        if (isset($childrenByParent[$standingId])) {
            foreach ($childrenByParent[$standingId] as $childId) {
                self::buildStandingTree($childId, $standingsById, $childrenByParent, $table, $level + 1);
            }
        }
    }

    public static function buildBuildingsTable(\PhpOffice\PhpWord\Element\Table &$table, array &$buildings)
    {
        $colWidths = [
            'building' => 2500,
            'complex' => 2300,
            'qf' => 2200,
            'status' => 2000
        ];

        // Stílusok a fejléchez
        $headerRowStyle = [
            'tblHeader' => true,
            'cantSplit' => true
        ];
        $headerCellStyle = [
            'bgColor' => 'A6A6A6',
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $headerFontStyle = [
            'bold' => true,
            'size' => 10,
            'name' => 'Calibri'
        ];
        $headerParagraphStyle = [
            'alignment' => 'center',
            'spaceBefore' => 60,
            'spaceAfter' => 60
        ];

        $table->addRow(600, $headerRowStyle);

        $headerCell1 = $table->addCell($colWidths['building'], $headerCellStyle);
        $headerCell1->addText("Épület\nmegnevezése", $headerFontStyle, $headerParagraphStyle);

        $headerCell2 = $table->addCell($colWidths['complex'], $headerCellStyle);
        $headerCell2->addText("Telephely", $headerFontStyle, $headerParagraphStyle);

        $headerCell3 = $table->addCell($colWidths['qf'], $headerCellStyle);
        $headerCell3->addText("Kalkulált fajlagos\nenergiafelhasználás", $headerFontStyle, $headerParagraphStyle);

        $headerCell4 = $table->addCell($colWidths['status'], $headerCellStyle);
        $headerCell4->addText("Besorolás", $headerFontStyle, $headerParagraphStyle);

        $dataCellStyle = [
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $dataFontStyle = [
            'size' => 9.5,
            'name' => 'Calibri'
        ];
        $dataParagraphStyleLeft = [
            'alignment' => 'left',
            'spaceBefore' => 40,
            'spaceAfter' => 40
        ];
        $dataParagraphStyleCenter = [
            'alignment' => 'center',
            'spaceBefore' => 40,
            'spaceAfter' => 40
        ];

        foreach ($buildings as $b) {
            $table->addRow(null, ['cantSplit' => true]);
            $qfValue = is_numeric($b['qf']) ? (float) $b['qf'] : 0;
            $statusText = ($qfValue > 150) ? 'Fejlesztendő' : 'Megfelelő';

            $cell1 = $table->addCell($colWidths['building'], $dataCellStyle);
            $cell1->addText($b['building_name'] ?? '', $dataFontStyle, $dataParagraphStyleLeft);

            $cell2 = $table->addCell($colWidths['complex'], $dataCellStyle);
            $cell2->addText($b['complex_name'] ?? '', $dataFontStyle, $dataParagraphStyleLeft);

            $cell3 = $table->addCell($colWidths['qf'], $dataCellStyle);
            $formattedQf = number_format($qfValue, 2, ',', ' ') . ' kWh/m²a';
            $cell3->addText($formattedQf, $dataFontStyle, $dataParagraphStyleCenter);

            $cell4 = $table->addCell($colWidths['status'], $dataCellStyle);
            $cell4->addText($statusText, $dataFontStyle, $dataParagraphStyleCenter);
        }
    }

    public static function buildHeatingTable(\PhpOffice\PhpWord\Element\Table &$table, array &$heating_systems)
    {
        $colWidths = [
            'complex' => 1500,
            'name' => 1500,
            'type' => 3000,
            'points' => 1000,
            'status' => 2000
        ];

        $headerRowStyle = [
            'tblHeader' => true,
            'cantSplit' => true
        ];

        $headerCellStyle = [
            'bgColor' => 'A6A6A6',
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $headerFontStyle = [
            'bold' => true,
            'size' => 10,
            'name' => 'Calibri'
        ];
        $headerParagraphStyle = [
            'alignment' => 'center',
            'spaceBefore' => 60,
            'spaceAfter' => 60
        ];

        $table->addRow(600, $headerRowStyle);
        $header1 = $table->addCell($colWidths['complex'], $headerCellStyle);
        $header1->addText("Telephely", $headerFontStyle, $headerParagraphStyle);
        $header2 = $table->addCell($colWidths['name'], $headerCellStyle);
        $header2->addText("Hőtermelő\nmegnevezése", $headerFontStyle, $headerParagraphStyle);
        $header3 = $table->addCell($colWidths['type'], $headerCellStyle);
        $header3->addText("Hőtermelő\ntípusa", $headerFontStyle, $headerParagraphStyle);
        $header4 = $table->addCell($colWidths['points'], $headerCellStyle);
        $header4->addText("Kalkulált\npontszám", $headerFontStyle, $headerParagraphStyle);
        $header5 = $table->addCell($colWidths['status'], $headerCellStyle);
        $header5->addText("Besorolás", $headerFontStyle, $headerParagraphStyle);

        $dataCellStyle = [
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $dataFontStyle = [
            'size' => 9.5,
            'name' => 'Calibri'
        ];
        $dataParagraphStyleCenter = [
            'alignment' => 'center',
            'spaceBefore' => 40,
            'spaceAfter' => 40
        ];

        foreach ($heating_systems as $h) {
            $heaters = json_decode($h['heaters'], true);
            foreach ($heaters as $index => $heater) {
                $table->addRow(null, ['cantSplit' => true]);

                if ($index === 0) {
                    $complexCellStyle = array_merge($dataCellStyle, ['vMerge' => 'restart']);
                    $complexCell = $table->addCell($colWidths['complex'], $complexCellStyle);
                    $complexCell->addText($h['name'], $dataFontStyle, $dataParagraphStyleCenter);
                } else {
                    $complexCellStyle = array_merge($dataCellStyle, ['vMerge' => 'continue']);
                    $complexCell = $table->addCell($colWidths['complex'], $complexCellStyle);
                }

                $heaterNameCell = $table->addCell($colWidths['name'], $dataCellStyle);
                $heaterNameCell->addText($heater['name'], $dataFontStyle, $dataParagraphStyleCenter);

                $heaterTypeCell = $table->addCell($colWidths['type'], $dataCellStyle);
                $heaterTypeCell->addText($heater["heatingType"], $dataFontStyle, $dataParagraphStyleCenter);

                $heaterPoints = self::calculateHeaterPoints($heater);
                $heaterPointCell = $table->addCell($colWidths['points'], $dataCellStyle);
                $heaterPointCell->addText($heaterPoints['points'], $dataFontStyle, $dataParagraphStyleCenter);

                $heaterStatusCell = $table->addCell($colWidths['status'], $dataCellStyle);
                $heaterStatusCell->addText($heaterPoints['status'], $dataFontStyle, $dataParagraphStyleCenter);
            }
        }
    }

    public static function buildHMVTable(PhpOffice\PhpWord\Element\Table &$table, array &$heating_systems): void
    {
        $colWidths = [
            'complex' => 1500,
            'name' => 1500,
            'points' => 1500,
            'status' => 1500,
            'etc' => 3000
        ];

        $headerRowStyle = [
            'tblHeader' => true,
            'cantSplit' => true
        ];

        $headerCellStyle = [
            'bgColor' => 'A6A6A6',
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $headerFontStyle = [
            'bold' => true,
            'size' => 10,
            'name' => 'Calibri'
        ];
        $headerParagraphStyle = [
            'alignment' => 'center',
            'spaceBefore' => 60,
            'spaceAfter' => 60
        ];

        $table->addRow(600, $headerRowStyle);
        $header1 = $table->addCell($colWidths['complex'], $headerCellStyle);
        $header1->addText("Telephely", $headerFontStyle, $headerParagraphStyle);
        $header2 = $table->addCell($colWidths['name'], $headerCellStyle);
        $header2->addText("HMV rendszer\nmegnevezése", $headerFontStyle, $headerParagraphStyle);
        $header3 = $table->addCell($colWidths['points'], $headerCellStyle);
        $header3->addText("Szabályozási\nmegfelelőség", $headerFontStyle, $headerParagraphStyle);
        $header4 = $table->addCell($colWidths['status'], $headerCellStyle);
        $header4->addText("Besorolás", $headerFontStyle, $headerParagraphStyle);
        $header5 = $table->addCell($colWidths['etc'], $headerCellStyle);
        $header5->addText("Megjegyzés", $headerFontStyle, $headerParagraphStyle);

        $dataCellStyle = [
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $dataFontStyle = [
            'size' => 9.5,
            'name' => 'Calibri'
        ];
        $dataParagraphStyleCenter = [
            'alignment' => 'center',
            'spaceBefore' => 40,
            'spaceAfter' => 40
        ];

        foreach ($heating_systems as $h) {
            $heaters = json_decode($h['emitters'], true);
            foreach ($heaters as $index => $emitter) {
                if ($emitter['type'] !== 'HMV') {
                    continue;
                }
                $table->addRow(null, ['cantSplit' => true]);

                if ($index === 0) {
                    $complexCellStyle = array_merge($dataCellStyle, ['vMerge' => 'restart']);
                    $complexCell = $table->addCell($colWidths['complex'], $complexCellStyle);
                    $complexCell->addText($h['name'], $dataFontStyle, $dataParagraphStyleCenter);
                } else {
                    $complexCellStyle = array_merge($dataCellStyle, ['vMerge' => 'continue']);
                    $complexCell = $table->addCell($colWidths['complex'], $complexCellStyle);
                }

                $heaterNameCell = $table->addCell($colWidths['name'], $dataCellStyle);
                $heaterNameCell->addText($emitter['name'], $dataFontStyle, $dataParagraphStyleCenter);

                $hmvPoints = self::HMV_REGULATION_VALUES[$emitter['hmvRegulation']];
                $heaterTypeCell = $table->addCell($colWidths['points'], $dataCellStyle);
                $heaterTypeCell->addText($hmvPoints . "%", $dataFontStyle, $dataParagraphStyleCenter);

                $status = $hmvPoints === 100 ? "Megfelelő" : "Fejlesztendő";
                $etc = "Cirkuláció és szabályozás optimalizálása, ahol a pontszám alacsony";

                $heaterPointCell = $table->addCell($colWidths['status'], $dataCellStyle);
                $heaterPointCell->addText($status, $dataFontStyle, $dataParagraphStyleCenter);

                $heaterStatusCell = $table->addCell($colWidths['etc'], $dataCellStyle);
                $heaterStatusCell->addText($etc, $dataFontStyle, $dataParagraphStyleCenter);
            }
        }
    }

    public static function buildLightingTable(PhpOffice\PhpWord\Element\Table &$table, array &$lighting_systems): void
    {
        $colWidths = [
            'complex' => 1500,
            'zone' => 1500,
            'specific' => 1750,
            'annual' => 1750,
            'status' => 2500
        ];

        $headerRowStyle = [
            'tblHeader' => true,
            'cantSplit' => true
        ];

        $headerCellStyle = [
            'bgColor' => 'A6A6A6',
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $headerFontStyle = [
            'bold' => true,
            'size' => 10,
            'name' => 'Calibri'
        ];
        $headerParagraphStyle = [
            'alignment' => 'center',
            'spaceBefore' => 60,
            'spaceAfter' => 60
        ];

        $table->addRow(600, $headerRowStyle);
        $header1 = $table->addCell($colWidths['complex'], $headerCellStyle);
        $header1->addText("Telephely", $headerFontStyle, $headerParagraphStyle);
        $header2 = $table->addCell($colWidths['zone'], $headerCellStyle);
        $header2->addText("Zóna", $headerFontStyle, $headerParagraphStyle);
        $header3 = $table->addCell($colWidths['specific'], $headerCellStyle);
        $header3->addText("Kalkulált fajlagos fogyasztás", $headerFontStyle, $headerParagraphStyle);
        $header4 = $table->addCell($colWidths['annual'], $headerCellStyle);
        $header4->addText("Éves fogyasztás", $headerFontStyle, $headerParagraphStyle);
        $header5 = $table->addCell($colWidths['status'], $headerCellStyle);
        $header5->addText("Besorolás", $headerFontStyle, $headerParagraphStyle);

        $dataCellStyle = [
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $dataFontStyle = [
            'size' => 9.5,
            'name' => 'Calibri'
        ];
        $dataParagraphStyleCenter = [
            'alignment' => 'center',
            'spaceBefore' => 40,
            'spaceAfter' => 40
        ];

        foreach ($lighting_systems as $index => $system) {
            $table->addRow(null, ['cantSplit' => true]);
            $complexCell = $table->addCell($colWidths['complex'], $dataCellStyle);
            $complexCell->addText($system['complex_name'], $dataFontStyle, $dataParagraphStyleCenter);

            $zoneCell = $table->addCell($colWidths['zone'], $dataCellStyle);
            $zoneCell->addText($system['name'], $dataFontStyle, $dataParagraphStyleCenter);

            $specificCell = $table->addCell($colWidths['specific'], $dataCellStyle);
            $textRun = $specificCell->addTextRun($dataParagraphStyleCenter);

            $textRun->addText($system['specific_sum'] . " kWh/m", $dataFontStyle);

            $superScriptStyle = array_merge($dataFontStyle, ['superScript' => true]);
            $textRun->addText("2", $superScriptStyle);

            $textRun->addText("a", $dataFontStyle);

            $consumption = self::calculateTotalConsumption($system['consumption'], $system['source']);
            $annualCell = $table->addCell($colWidths['annual'], $dataCellStyle);
            $annualCell->addText($consumption . "kWh", $dataFontStyle, $dataParagraphStyleCenter);

            $statusCell = $table->addCell($colWidths['status'], $dataCellStyle);
            $statusCell->addText('Megfelelő/Fejlesztendő', $dataFontStyle, $dataParagraphStyleCenter);
        }
    }

    public static function buildCoolingTable(PhpOffice\PhpWord\Element\Table &$table, array &$cooling_systems): void
    {
        $colWidths = [
            'complex' => 1500,
            'name' => 1500,
            'type' => 2100,
            'base_points' => 1300,
            'regulation_points' => 1300,
            'status' => 1300
        ];

        $headerRowStyle = [
            'tblHeader' => true,
            'cantSplit' => true
        ];

        $headerCellStyle = [
            'bgColor' => 'A6A6A6',
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $headerFontStyle = [
            'bold' => true,
            'size' => 10,
            'name' => 'Calibri'
        ];
        $headerParagraphStyle = [
            'alignment' => 'center',
            'spaceBefore' => 60,
            'spaceAfter' => 60
        ];

        $table->addRow(600, $headerRowStyle);
        $header1 = $table->addCell($colWidths['complex'], $headerCellStyle);
        $header1->addText("Telephely", $headerFontStyle, $headerParagraphStyle);
        $header2 = $table->addCell($colWidths['name'], $headerCellStyle);
        $header2->addText("Berendezés\nmegnevezése", $headerFontStyle, $headerParagraphStyle);
        $header3 = $table->addCell($colWidths['type'], $headerCellStyle);
        $header3->addText("Berendezés\ntípusa", $headerFontStyle, $headerParagraphStyle);
        $header4 = $table->addCell($colWidths['base_points'], $headerCellStyle);
        $header4->addText("Alap\npontszám\n(33)", $headerFontStyle, $headerParagraphStyle);
        $header5 = $table->addCell($colWidths['regulation_points'], $headerCellStyle);
        $header5->addText("Szabályozás\n(10)", $headerFontStyle, $headerParagraphStyle);
        $header6 = $table->addCell($colWidths['status'], $headerCellStyle);
        $header6->addText("Összesített\n(43)", $headerFontStyle, $headerParagraphStyle);

        $dataCellStyle = [
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $dataFontStyle = [
            'size' => 9.5,
            'name' => 'Calibri'
        ];
        $dataParagraphStyleCenter = [
            'alignment' => 'center',
            'spaceBefore' => 40,
            'spaceAfter' => 40
        ];

        foreach ($cooling_systems as $h) {
            $coolers = json_decode($h['heaters'], true);
            foreach ($coolers as $index => $cooler) {
                if (!is_numeric(array_search($cooler['heatingType'], self::COOLER_TYPES))) {
                    continue;
                }
                $table->addRow(null, ['cantSplit' => true]);

                if ($index === 0) {
                    $complexCellStyle = array_merge($dataCellStyle, ['vMerge' => 'restart']);
                    $complexCell = $table->addCell($colWidths['complex'], $complexCellStyle);
                    $complexCell->addText($h['name'], $dataFontStyle, $dataParagraphStyleCenter);
                } else {
                    $complexCellStyle = array_merge($dataCellStyle, ['vMerge' => 'continue']);
                    $complexCell = $table->addCell($colWidths['complex'], $complexCellStyle);
                }

                $coolerNameCell = $table->addCell($colWidths['name'], $dataCellStyle);
                $coolerNameCell->addText($cooler['name'], $dataFontStyle, $dataParagraphStyleCenter);

                $coolerTypeCell = $table->addCell($colWidths['type'], $dataCellStyle);
                $coolerTypeCell->addText($cooler["heatingType"], $dataFontStyle, $dataParagraphStyleCenter);

                $coolerPoints = self::calculateCoolerPoints($cooler);
                $coolerBasePointCell = $table->addCell($colWidths['base_points'], $dataCellStyle);
                $coolerBasePointCell->addText($coolerPoints['base'] . "%", $dataFontStyle, $dataParagraphStyleCenter);

                $coolerRegulationCell = $table->addCell($colWidths['regulation_points'], $dataCellStyle);
                $coolerRegulationCell->addText($coolerPoints['regulation'] . "%", $dataFontStyle, $dataParagraphStyleCenter);

                $coolerCombinedCell = $table->addCell($colWidths['status'], $dataCellStyle);
                $coolerCombinedCell->addText($coolerPoints['combined'] . "%", $dataFontStyle, $dataParagraphStyleCenter);
            }
        }
    }

    public static function buildHVACTable(PhpOffice\PhpWord\Element\Table &$table, array &$hvacSystems): void
    {
        $colWidths = [
            'complex' => 1125,
            'building' => 1125,
            'name' => 1125,
            'sfp' => 1125,
            'sfp_points' => 1125,
            'heat' => 1125,
            "heat_points" => 1125,
            "insulation_points" => 1125
        ];

        $headerRowStyle = [
            'tblHeader' => true,
            'cantSplit' => true
        ];

        $headerCellStyle = [
            'bgColor' => 'A6A6A6',
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $headerFontStyle = [
            'bold' => true,
            'size' => 10,
            'name' => 'Calibri'
        ];
        $headerParagraphStyle = [
            'alignment' => 'center',
            'spaceBefore' => 60,
            'spaceAfter' => 60
        ];

        $table->addRow(600, $headerRowStyle);
        $header1 = $table->addCell($colWidths['complex'], $headerCellStyle);
        $header1->addText("Telephely", $headerFontStyle, $headerParagraphStyle);
        $header2 = $table->addCell($colWidths['building'], $headerCellStyle);
        $header2->addText("Hely", $headerFontStyle, $headerParagraphStyle);
        $header3 = $table->addCell($colWidths['name'], $headerCellStyle);
        $header3->addText("Rendszer", $headerFontStyle, $headerParagraphStyle);
        $header4 = $table->addCell($colWidths['sfp'], $headerCellStyle);
        $header4->addText("SFP\n(W/m3/s)", $headerFontStyle, $headerParagraphStyle);
        $header5 = $table->addCell($colWidths['sfp_points'], $headerCellStyle);
        $header5->addText("SFP\nmegfelelőség", $headerFontStyle, $headerParagraphStyle);
        $header6 = $table->addCell($colWidths['heat'], $headerCellStyle);
        $header6->addText("Hővisszanyerés\ntípusa", $headerFontStyle, $headerParagraphStyle);
        $header6 = $table->addCell($colWidths['heat_points'], $headerCellStyle);
        $header6->addText("Hővisszanyerés\nmegfelelőség", $headerFontStyle, $headerParagraphStyle);
        $header6 = $table->addCell($colWidths['insulation_points'], $headerCellStyle);
        $header6->addText("Szigetelés\nmegfelelőség", $headerFontStyle, $headerParagraphStyle);

        $dataCellStyle = [
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $dataFontStyle = [
            'size' => 9.5,
            'name' => 'Calibri'
        ];
        $dataParagraphStyleCenter = [
            'alignment' => 'center',
            'spaceBefore' => 40,
            'spaceAfter' => 40
        ];

        foreach ($hvacSystems as $system) {
            $systemDetails = json_decode($system['json'], true);
            $table->addRow(null, ['cantSplit' => true]);

            $complexCell = $table->addCell($colWidths['complex'], $dataCellStyle);
            $complexCell->addText($system['complex_name'], $dataFontStyle, $dataParagraphStyleCenter);
            $buildingCell = $table->addCell($colWidths['building'], $dataCellStyle);
            $buildingCell->addText($system['building_name'], $dataFontStyle, $dataParagraphStyleCenter);
            $nameCell = $table->addCell($colWidths['name'], $dataCellStyle);
            $nameCell->addText($system['name'], $dataFontStyle, $dataParagraphStyleCenter);

            $calculated = self::calculateVentilationGoodness($systemDetails, (float) $system['sfp']);

            $sfpCell = $table->addCell($colWidths['sfp'], $dataCellStyle);
            $sfpCell->addText($system['sfp'] . "", $dataFontStyle, $dataParagraphStyleCenter);

            $sfpGoodnessCell = $table->addCell($colWidths['sfp_points'], $dataCellStyle);
            $sfpGoodnessCell->addText($calculated['sfp_goodness'] . "%", $dataFontStyle, $dataParagraphStyleCenter);

            $retrieverCell = $table->addCell($colWidths['heat'], $dataCellStyle);
            $retrieverCell->addText($systemDetails['retriever'], $dataFontStyle, $dataParagraphStyleCenter);

            $retrieverGoodnessCell = $table->addCell($colWidths['heat_points'], $dataCellStyle);
            $retrieverGoodnessCell->addText($calculated['retriever_goodness'] . "%", $dataFontStyle, $dataParagraphStyleCenter);

            $insulationGoodnessCell = $table->addCell($colWidths['insulation_points'], $dataCellStyle);
            $insulationGoodnessCell->addText($calculated['insulation_goodness'] . "%", $dataFontStyle, $dataParagraphStyleCenter);
        }
    }

    public static function convertToKwh(float $value, string $unit): float
    {
        return
            match (strtoupper($unit)) {
                'MWH' => $value * 1000,
                'MJ' => $value / 3.6,
                'GJ' => $value * 277.777778,
                'MCUBE' => $value * 9.5,
                'KWH' => $value,
                default => $value,
            };
    }

    public static function buildVehiclesTable(\PhpOffice\PhpWord\Element\Table &$table, array &$vehicles): void
    {
        $colWidths = [
            'vehicle' => 2500,
            'complex' => 2300,
            'qf' => 2200,
            'status' => 2000
        ];

        $headerRowStyle = [
            'tblHeader' => true,
            'cantSplit' => true
        ];
        $headerCellStyle = [
            'bgColor' => 'A6A6A6',
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $headerFontStyle = [
            'bold' => true,
            'size' => 10,
            'name' => 'Calibri'
        ];
        $headerParagraphStyle = [
            'alignment' => 'center',
            'spaceBefore' => 60,
            'spaceAfter' => 60
        ];

        $table->addRow(600, $headerRowStyle);

        $cell1 = $table->addCell($colWidths['vehicle'], $headerCellStyle);
        $cell1->addText("Jármű\nmegnevezése", $headerFontStyle, $headerParagraphStyle);

        $cell2 = $table->addCell($colWidths['complex'], $headerCellStyle);
        $cell2->addText("Telephely", $headerFontStyle, $headerParagraphStyle);

        $cell3 = $table->addCell($colWidths['qf'], $headerCellStyle);
        $cell3->addText("Kalkulált fajlagos\nenergiafelhasználás", $headerFontStyle, $headerParagraphStyle);

        $cell4 = $table->addCell($colWidths['status'], $headerCellStyle);
        $cell4->addText("Besorolás", $headerFontStyle, $headerParagraphStyle);

        $dataCellStyle = [
            'valign' => 'center',
            'borderSize' => 6,
            'borderColor' => '000000'
        ];
        $dataFontStyle = [
            'size' => 9.5,
            'name' => 'Calibri'
        ];
        $dataParagraphStyleLeft = [
            'alignment' => 'left',
            'spaceBefore' => 40,
            'spaceAfter' => 40
        ];
        $dataParagraphStyleCenter = [
            'alignment' => 'center',
            'spaceBefore' => 40,
            'spaceAfter' => 40
        ];

        foreach ($vehicles as $v) {
            $table->addRow(null, ['cantSplit' => true]);

            $calc = self::calculateVehicleQf($v);

            $c1 = $table->addCell($colWidths['vehicle'], $dataCellStyle);
            $c1->addText($v['vehicle_name'] ?? $v['name'] ?? '', $dataFontStyle, $dataParagraphStyleLeft);

            $c2 = $table->addCell($colWidths['complex'], $dataCellStyle);
            $c2->addText($v['complex_name'] ?? '', $dataFontStyle, $dataParagraphStyleLeft);

            $c3 = $table->addCell($colWidths['qf'], $dataCellStyle);
            $formattedQf = number_format($calc['qf'], 2, ',', ' ') . ' ' . $calc['unit'];
            $c3->addText($formattedQf, $dataFontStyle, $dataParagraphStyleCenter);

            $c4 = $table->addCell($colWidths['status'], $dataCellStyle);
            $c4->addText($calc['status'], $dataFontStyle, $dataParagraphStyleCenter);
        }
    }

    public static function calculateVehicleQf(array $vehicle): array
    {
        $category = mb_strtolower(trim($vehicle['vehicle_category'] ?? ''));
        $usageMetric = mb_strtolower(trim($vehicle['usage_metric'] ?? $vehicle['usageMetric'] ?? ''));

        if (str_contains($category, 'anyagmozgat') || str_contains($category, 'targonc')) {
            $rawConsumption = $vehicle['consumption'] ?? null;
            $totalConsumption = self::calculateTotalConsumption($rawConsumption, $vehicle['source'] ?? '');

            $fuel = $vehicle['fuel'] ?? 'Elektromos áram';
            $unit = $vehicle['measurement'] ?? 'KWH';
            $totalKwh = self::convertFuelToKwh($totalConsumption, $fuel, $unit);

            $usage1 = (float) ($vehicle['usage_value'] ?? $vehicle['usageValue'] ?? 0);
            $usage2 = (float) ($vehicle['usage_value2'] ?? $vehicle['usageValue2'] ?? 0);
            $capacity = (float) ($vehicle['capacity'] ?? 0);

            if ($usageMetric === 'tkm') {
                $divisor = $usage1;
                $unitLabel = 'kWh/tkm';
                $threshold = 0.247;
            } elseif ($usageMetric === 'km') {
                $weight = $usage2 > 0 ? $usage2 : ($capacity > 0 ? $capacity : 1.0);
                $divisor = $usage1 * $weight;
                $unitLabel = 'kWh/tkm';
                $threshold = 0.247;
            } else {
                $divisor = $usage1;
                $unitLabel = 'kWh/h';
                $threshold = self::getForkliftThresholdKwhPerHour($capacity, $fuel);
            }

            $qf = ($divisor > 0) ? ($totalKwh / $divisor) : 0;
            $status = ($threshold > 0 && $qf > $threshold) ? 'Fejlesztendő' : 'Megfelelő';

            return [
                'qf' => $qf,
                'unit' => $unitLabel,
                'status' => $status
            ];
        } else if (str_contains($category, 'áruszállít') || str_contains($category, 'aruszallit') || str_contains($category, 'teher')) {
            $rawConsumption = $vehicle['consumption'] ?? null;
            $totalConsumption = self::calculateTotalConsumption($rawConsumption, $vehicle['source'] ?? '');

            $fuel = $vehicle['fuel'] ?? 'Gázolaj';
            $unit = $vehicle['measurement'] ?? 'L';
            $totalKwh = self::convertFuelToKwh($totalConsumption, $fuel, $unit);

            $usage1 = (float) ($vehicle['usage_value'] ?? $vehicle['usageValue'] ?? 0);
            $usage2 = (float) ($vehicle['usage_value2'] ?? $vehicle['usageValue2'] ?? 0);
            $capacity = (float) ($vehicle['capacity'] ?? 0);

            $weightInTons = $usage2 > 0 ? $usage2 : ($capacity > 0 ? $capacity : 1.0);

            if ($usageMetric === 'tkm') {
                $divisor = $usage1;
                $unitLabel = 'kWh/tkm';
                $threshold = 0.247;
            } elseif ($usageMetric === 'km') {
                $divisor = $usage1;
                $unitLabel = 'kWh/km';
                $threshold = 0.247 * $weightInTons;
            } elseif ($usageMetric === 'üzemóra' || $usageMetric === 'h') {
                $divisor = $usage1;
                $unitLabel = 'kWh/h';
                $threshold = 0.247 * $weightInTons * 40.0;
            } else {
                $divisor = $usage1;
                $unitLabel = 'kWh/tkm';
                $threshold = 0.247;
            }

            $qf = ($divisor > 0) ? ($totalKwh / $divisor) : 0;
            $status = ($threshold > 0 && $qf > $threshold) ? 'Fejlesztendő' : 'Megfelelő';

            return [
                'qf' => $qf,
                'unit' => $unitLabel,
                'status' => $status
            ];
        } else {

            $rawConsumption = $vehicle['consumption'] ?? null;
            $totalConsumption = self::calculateTotalConsumption($rawConsumption, $vehicle['source'] ?? '');

            $fuel = $vehicle['fuel'] ?? 'Benzin';
            $unit = $vehicle['measurement'] ?? 'L';
            $totalKwh = self::convertFuelToKwh($totalConsumption, $fuel, $unit);

            $usageKm = (float) ($vehicle['usage_value'] ?? $vehicle['usageValue'] ?? 0);
            $qf = ($usageKm > 0) ? ($totalKwh / $usageKm) : 0;

            $motorSize = (int) ($vehicle['motor_size'] ?? $vehicle['motorSize'] ?? 0);
            $isHybrid = !empty($vehicle['hibrid']);
            $isChargeable = !empty($vehicle['chargeable']);

            $threshold = self::getPassengerCarThresholdKwhPerKm($fuel, $motorSize, $isHybrid, $isChargeable);

            $status = ($threshold > 0 && $qf > $threshold) ? 'Fejlesztendő' : 'Megfelelő';

            return [
                'qf' => $qf,
                'unit' => 'kWh/km',
                'status' => $status
            ];
        }
    }

    private static function getPassengerCarThresholdKwhPerKm(string $fuel, int $motorSize, bool $isHybrid, bool $isChargeable): float
    {
        $fuelClean = mb_strtolower(trim($fuel));

        if (str_contains($fuelClean, 'elektromos') && !$isHybrid) {
            return 0.20;
        }

        $l100km = 0.0;

        if (str_contains($fuelClean, 'benzin')) {
            if ($motorSize <= 1000) {
                $l100km = 7.6;
            } elseif ($motorSize <= 1500) {
                $l100km = 8.6;
            } elseif ($motorSize <= 2000) {
                $l100km = 9.5;
            } elseif ($motorSize <= 3000) {
                $l100km = 11.4;
            } else {
                $l100km = 13.3;
            }
        } elseif (str_contains($fuelClean, 'dízel') || str_contains($fuelClean, 'dizel') || str_contains($fuelClean, 'gázolaj')) {
            if ($motorSize <= 1500) {
                $l100km = 5.7;
            } elseif ($motorSize <= 2000) {
                $l100km = 6.7;
            } elseif ($motorSize <= 3000) {
                $l100km = 7.6;
            } else {
                $l100km = 9.5;
            }
        } elseif (str_contains($fuelClean, 'lpg') || str_contains($fuelClean, 'pb') || str_contains($fuelClean, 'gáz')) {
            $baseBenzin = 8.6;
            if ($motorSize <= 1000)
                $baseBenzin = 7.6;
            elseif ($motorSize <= 1500)
                $baseBenzin = 8.6;
            elseif ($motorSize <= 2000)
                $baseBenzin = 9.5;
            elseif ($motorSize <= 3000)
                $baseBenzin = 11.4;
            elseif ($motorSize > 3000)
                $baseBenzin = 13.3;

            $l100km = $baseBenzin * 1.2;
        }

        $kwhPerKm = 0.0;

        if (str_contains($fuelClean, 'dízel') || str_contains($fuelClean, 'dizel') || str_contains($fuelClean, 'gázolaj')) {
            $kwhPerKm = ($l100km / 100) * 11.4;
        } else {
            $kwhPerKm = ($l100km / 100) * 9.2;
        }

        if ($isHybrid && $isChargeable) {
            $kwhPerKm *= 0.7;
        }

        return $kwhPerKm;
    }

    public static function convertFuelToKwh(float $value, string $fuel, string $unit): float
    {
        $fuelClean = mb_strtolower(trim($fuel));
        $unitClean = strtoupper(trim($unit));

        if (str_contains($fuelClean, 'elektromos') || str_contains($fuelClean, 'eletromos')) {
            return self::convertToKwh($value, $unitClean);
        }

        if (str_contains($fuelClean, 'dízel') || str_contains($fuelClean, 'dizel') || str_contains($fuelClean, 'gázolaj')) {
            return $value * 11.4;
        }

        if (str_contains($fuelClean, 'benzin')) {
            return $value * 9.2;
        }

        if (str_contains($fuelClean, 'lpg') || str_contains($fuelClean, 'pb')) {
            if ($unitClean === 'L' || $unitClean === 'LITER') {
                return $value * 6.69;
            }
            return $value * 12.82;
        }

        if (str_contains($fuelClean, 'propán') || str_contains($fuelClean, 'propan')) {
            return $value * 12.86;
        }

        return self::convertToKwh($value, $unitClean);
    }

    private static function getForkliftThresholdKwhPerHour(float $capacity, string $fuel): float
    {
        $fuelClean = mb_strtolower(trim($fuel));

        $matrix = [
            '1.5' => ['dízel' => 2.19, 'lpg' => 2.30, 'elektr' => 4.47],
            '2.0' => ['dízel' => 2.57, 'lpg' => 2.70, 'elektr' => 4.81],
            '2.5' => ['dízel' => 2.97, 'lpg' => 3.26, 'elektr' => 7.16],
            '3.0' => ['dízel' => 3.23, 'lpg' => 3.55, 'elektr' => 7.56],
            '3.5' => ['dízel' => 3.90, 'lpg' => 3.79, 'elektr' => 8.33],
            '4.0' => ['dízel' => 4.44, 'lpg' => 4.29, 'elektr' => 8.70],
            '4.5' => ['dízel' => 4.91, 'lpg' => 4.63, 'elektr' => 9.30],
            '5.0' => ['dízel' => 5.95, 'lpg' => 4.94, 'elektr' => 10.38],
            '5.5' => ['dízel' => 6.97, 'lpg' => 5.31, 'elektr' => 12.80],
            '6.0' => ['dízel' => 7.48, 'lpg' => 7.09, 'elektr' => 12.20],
            '6.5' => ['dízel' => 0.00, 'lpg' => 0.00, 'elektr' => 12.20],
            '7.0' => ['dízel' => 8.80, 'lpg' => 7.83, 'elektr' => 13.40],
            '7.5' => ['dízel' => 8.90, 'lpg' => 0.00, 'elektr' => 0.00],
            '8.0' => ['dízel' => 9.72, 'lpg' => 9.93, 'elektr' => 0.00],
            '9.0' => ['dízel' => 11.11, 'lpg' => 10.70, 'elektr' => 0.00],
            '10.0' => ['dízel' => 13.00, 'lpg' => 0.00, 'elektr' => 0.00],
        ];

        if ($capacity <= 0) {
            return 0.0;
        }

        $closestCap = '1.5';
        $minDiff = null;

        foreach ($matrix as $capKey => $values) {
            $diff = abs($capacity - (float) $capKey);
            if ($minDiff === null || $diff < $minDiff) {
                $minDiff = $diff;
                $closestCap = (string) $capKey;
            }
        }

        $row = $matrix[$closestCap];

        if (str_contains($fuelClean, 'elektromos') || str_contains($fuelClean, 'eletromos')) {
            return $row['elektr'];
        }

        if (str_contains($fuelClean, 'dízel') || str_contains($fuelClean, 'dizel') || str_contains($fuelClean, 'gázolaj')) {
            return $row['dízel'] * 11.4;
        }

        if (str_contains($fuelClean, 'lpg') || str_contains($fuelClean, 'pb') || str_contains($fuelClean, 'propán')) {
            return $row['lpg'] * 12.82;
        }

        return 0.0;
    }

    public static function appendCompressedAirTableToCell(\PhpOffice\PhpWord\Element\Cell &$cell, array $data, string $defaultName = '', string $complexName = '', array $meters = []): void
    {
        $machines = $data['machines'] ?? [];
        $machineCount = count($machines);
        $colSpan = max(1, $machineCount);

        $labelWidth = 2400;
        $valWidth = 1300;
        $boldFont = ['bold' => true, 'size' => 8.5, 'name' => 'Calibri'];
        $font = ['size' => 8.5, 'name' => 'Calibri'];
        $headerStyle = ['bgColor' => 'D9D9D9'];
        $centerPara = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 10, 'spaceAfter' => 10];
        $leftPara = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceBefore' => 10, 'spaceAfter' => 10];
        $table = $cell->addTable([
            'borderColor' => '000000',
            'borderSize' => 4,
            'cellMarginTop' => 30,
            'cellMarginBottom' => 30,
            'cellMarginLeft' => 50,
            'cellMarginRight' => 50,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT,
            'width' => 100 * 50
        ]);

        $addSpannedRow = function ($label, $value, $isHeader = false) use (&$table, $labelWidth, $valWidth, $colSpan, $font, $boldFont, $leftPara, $centerPara, $headerStyle) {
            $table->addRow();
            $cellStyle = $isHeader ? $headerStyle : [];

            $table->addCell($labelWidth, $cellStyle)->addText($label, $boldFont, $leftPara);

            $valCell = $table->addCell($valWidth * $colSpan, array_merge(['gridSpan' => $colSpan], $cellStyle));
            $valCell->addText($value, $isHeader ? $boldFont : $font, $centerPara);
        };

        $addSpannedRow('Rendszer megnevezése', $data['name'] ?? $defaultName, true);

        $addSpannedRow('Telephely', $complexName, true);

        $addSpannedRow('Hálózati nyomás (bar)', isset($data['pressure']) ? $data['pressure'] . ' bar' : '');

        $table->addRow();
        $table->addCell($labelWidth)->addText('Kompresszor típusa', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText($m['compressorType'] ?? '', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Mennyisége (db)', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText(($m['amount'] ?? 1) . ' db', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Névleges teljesítmény (kW/db)', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText(($m['nominalOutput'] ?? '') . ' kW', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Működési mód', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText($m['mode'] ?? 'ON-OFF / Fr.váltós', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Hozzá tartozó almérés', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $meterId = $m['meter_id'] ?? $m['standing'] ?? null;
            $meterName = ($meterId && isset($meters[$meterId])) ? $meters[$meterId] : 'Nincs';
            $table->addCell($valWidth)->addText($meterName, $font, $centerPara);
        }

        $hasRoomForImprovement = false;

        $table->addRow();
        $table->addCell($labelWidth)->addText('Van lehetőség a nyomás csökkentésére?', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $isYes = !empty($m['pressureReduction']);
            $table->addCell($valWidth)->addText($isYes ? 'IGEN' : 'NEM', $font, $centerPara);

            if (!$isYes) {
                $hasRoomForImprovement = true;
            }
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Van lehetőség hálózati optimalizációra?', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $isYes = !empty($m['systemOptimalization']);
            $table->addCell($valWidth)->addText($isYes ? 'IGEN' : 'NEM', $font, $centerPara);

            if (!$isYes) {
                $hasRoomForImprovement = true;
            }
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Van hulladékhő hasznosítás?', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $wasteVal = trim($m['wasteUse'] ?? 'NINCS, LEHETŐSÉG SINCS');
            $table->addCell($valWidth)->addText($wasteVal, $font, $centerPara);

            if (str_contains(strtoupper($wasteVal), 'VAN RÁ LEHETŐSÉG')) {
                $hasRoomForImprovement = true;
            }
        }

        $calculatedStatus = $hasRoomForImprovement ? 'Fejlesztendő' : 'Megfelelő';

        $addSpannedRow('A technológiai alrendszer', $calculatedStatus, true);
    }

    public static function appendSteamTableToCell(\PhpOffice\PhpWord\Element\Cell &$cell, array $data, string $defaultName = '', string $complexName = '', array $meters = []): void
    {
        $machines = $data['machines'] ?? [];
        $machineCount = count($machines);
        $colSpan = max(1, $machineCount);

        $labelWidth = 2400;
        $valWidth = 1300;

        $boldFont = ['bold' => true, 'size' => 8.5, 'name' => 'Calibri'];
        $font = ['size' => 8.5, 'name' => 'Calibri'];
        $headerStyle = ['bgColor' => 'D9D9D9'];

        $centerPara = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 10, 'spaceAfter' => 10];
        $leftPara = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceBefore' => 10, 'spaceAfter' => 10];

        $table = $cell->addTable([
            'borderColor' => '000000',
            'borderSize' => 4,
            'cellMarginTop' => 30,
            'cellMarginBottom' => 30,
            'cellMarginLeft' => 50,
            'cellMarginRight' => 50,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT,
            'width' => 100 * 50
        ]);

        $addSpannedRow = function ($label, $value, $isHeader = false) use (&$table, $labelWidth, $valWidth, $colSpan, $font, $boldFont, $leftPara, $centerPara, $headerStyle) {
            $table->addRow();
            $cellStyle = $isHeader ? $headerStyle : [];

            $table->addCell($labelWidth, $cellStyle)->addText($label, $boldFont, $leftPara);

            $valCell = $table->addCell($valWidth * $colSpan, array_merge(['gridSpan' => $colSpan], $cellStyle));
            $valCell->addText($value, $isHeader ? $boldFont : $font, $centerPara);
        };

        $addSpannedRow('Rendszer megnevezése', $data['name'] ?? $defaultName, true);

        $addSpannedRow('Telephely', $complexName ?: '-', true);

        $addSpannedRow('Hálózati nyomás (bar)', isset($data['pressure']) ? $data['pressure'] . ' bar' : '');

        $table->addRow();
        $table->addCell($labelWidth)->addText('Gőzfejlesztő típusa', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText($m['type'] ?? '', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Mennyisége (db)', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText(($m['amount'] ?? 1) . ' db', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Névleges teljesítmény (kW/db)', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText(($m['nominalOutput'] ?? '') . ' kW', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Működési mód', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText($m['mode'] ?? 'nagy vízterű / gyorsgőzfejlesztő', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Hozzá tartozó almérés', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $meterId = $m['standing'] ?? $m['meter_id'] ?? null;
            $meterName = ($meterId && isset($meters[$meterId])) ? $meters[$meterId] : 'Nincs';
            $table->addCell($valWidth)->addText($meterName, $font, $centerPara);
        }

        $hasRoomForImprovement = false;

        $table->addRow();
        $table->addCell($labelWidth)->addText('Van füstgáz hasznosítás?', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $smokeVal = trim($m['smokeUse'] ?? 'NINCS, LEHETŐSÉG SINCS');
            $table->addCell($valWidth)->addText($smokeVal, $font, $centerPara);

            if (str_contains(strtoupper($smokeVal), 'VAN RÁ LEHETŐSÉG')) {
                $hasRoomForImprovement = true;
            }
        }

        $calculatedStatus = $hasRoomForImprovement ? 'Fejlesztendő' : 'Megfelelő';
        $addSpannedRow('A technológiai alrendszer', $calculatedStatus, true);
    }

    public static function appendCoolingTableToCell(\PhpOffice\PhpWord\Element\Cell &$cell, array $data, string $defaultName = '', array $meters = []): void
    {
        $machines = $data['machines'] ?? [];
        $machineCount = count($machines);
        $colSpan = max(1, $machineCount);

        $labelWidth = 2400;
        $valWidth = 1300;

        $boldFont = ['bold' => true, 'size' => 8.5, 'name' => 'Calibri'];
        $font = ['size' => 8.5, 'name' => 'Calibri'];
        $headerStyle = ['bgColor' => 'D9D9D9'];

        $centerPara = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 10, 'spaceAfter' => 10];
        $leftPara = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceBefore' => 10, 'spaceAfter' => 10];

        $table = $cell->addTable([
            'borderColor' => '000000',
            'borderSize' => 4,
            'cellMarginTop' => 30,
            'cellMarginBottom' => 30,
            'cellMarginLeft' => 50,
            'cellMarginRight' => 50,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT,
            'width' => 100 * 50
        ]);

        $addSpannedRow = function ($label, $value, $isHeader = false) use (&$table, $labelWidth, $valWidth, $colSpan, $font, $boldFont, $leftPara, $centerPara, $headerStyle) {
            $table->addRow();
            $cellStyle = $isHeader ? $headerStyle : [];

            $table->addCell($labelWidth, $cellStyle)->addText($label, $boldFont, $leftPara);

            $valCell = $table->addCell($valWidth * $colSpan, array_merge(['gridSpan' => $colSpan], $cellStyle));
            $valCell->addText($value, $isHeader ? $boldFont : $font, $centerPara);
        };

        $addSpannedRow('Rendszer megnevezése', $data['name'] ?? $defaultName, true);

        $table->addRow();
        $table->addCell($labelWidth)->addText('Hűtőberendezés típusa', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText($m['type'] ?? $m['chillerType'] ?? '', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Mennyisége (db)', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText(($m['amount'] ?? 1) . ' db', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Névleges teljesítmény (kW/db)', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText(($m['nominalOutput'] ?? '') . ' kW', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Működési mód', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText($m['mode'] ?? 'Direkt elpárolgás / szabadhűtés', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Hozzá tartozó almérés', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $meterId = $m['standing'] ?? $m['meter_id'] ?? null;
            $meterName = ($meterId && isset($meters[$meterId])) ? $meters[$meterId] : 'Nincs';
            $table->addCell($valWidth)->addText($meterName, $font, $centerPara);
        }

        $hasRoomForImprovement = false;
        $table->addRow();
        $table->addCell($labelWidth)->addText('Van hulladékhő hasznosítás?', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $wasteVal = trim($m['wasteUse'] ?? $m['heatRecovery'] ?? 'NINCS, LEHETŐSÉG SINCS');
            $table->addCell($valWidth)->addText($wasteVal, $font, $centerPara);

            if (str_contains(strtoupper($wasteVal), 'VAN RÁ LEHETŐSÉG')) {
                $hasRoomForImprovement = true;
            }
        }

        $calculatedStatus = $hasRoomForImprovement ? 'Fejlesztendő' : 'Megfelelő';
        $addSpannedRow('A technológiai alrendszer', $calculatedStatus, true);
    }

    public static function appendOtherTableToCell(\PhpOffice\PhpWord\Element\Cell &$cell, array $data, string $defaultName = '', string $complexName = '', array $meters = []): void
    {
        $machines = $data['machines'] ?? [];
        $machineCount = count($machines);
        $colSpan = max(1, $machineCount);

        $labelWidth = 2400;
        $valWidth = 1300;

        $boldFont = ['bold' => true, 'size' => 8.5, 'name' => 'Calibri'];
        $font = ['size' => 8.5, 'name' => 'Calibri'];
        $headerStyle = ['bgColor' => 'D9D9D9'];

        $centerPara = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 10, 'spaceAfter' => 10];
        $leftPara = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceBefore' => 10, 'spaceAfter' => 10];
        $table = $cell->addTable([
            'borderColor' => '000000',
            'borderSize' => 4,
            'cellMarginTop' => 30,
            'cellMarginBottom' => 30,
            'cellMarginLeft' => 50,
            'cellMarginRight' => 50,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT,
            'width' => 100 * 50
        ]);

        $addSpannedRow = function ($label, $value, $isHeader = false) use (&$table, $labelWidth, $valWidth, $colSpan, $font, $boldFont, $leftPara, $centerPara, $headerStyle) {
            $table->addRow();
            $cellStyle = $isHeader ? $headerStyle : [];

            $table->addCell($labelWidth, $cellStyle)->addText($label, $boldFont, $leftPara);

            $valCell = $table->addCell($valWidth * $colSpan, array_merge(['gridSpan' => $colSpan], $cellStyle));
            $valCell->addText($value, $isHeader ? $boldFont : $font, $centerPara);
        };

        $addSpannedRow('Rendszer megnevezése', $data['name'] ?? $defaultName, true);
        $addSpannedRow('Telephely', $complexName ?: '-', true);
        $table->addRow();
        $table->addCell($labelWidth)->addText('Technológiai berendezés típusa', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText($m['type'] ?? '', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Mennyisége (db)', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText(($m['amount'] ?? 1) . ' db', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Névleges hőteljesítmény (kW/db)', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $table->addCell($valWidth)->addText(($m['nominalOutput'] ?? '') . ' kW', $font, $centerPara);
        }

        $table->addRow();
        $table->addCell($labelWidth)->addText('Hozzá tartozó almérés', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $meterId = $m['standing'] ?? $m['meter_id'] ?? null;
            $meterName = ($meterId && isset($meters[$meterId])) ? $meters[$meterId] : 'Nincs';
            $table->addCell($valWidth)->addText($meterName, $font, $centerPara);
        }

        $hasRoomForImprovement = false;

        $table->addRow();
        $table->addCell($labelWidth)->addText('Van hulladékhő hasznosítás?', $boldFont, $leftPara);
        foreach ($machines as $m) {
            $wasteVal = trim($m['wasteUse'] ?? 'NINCS, LEHETŐSÉG SINCS');
            $table->addCell($valWidth)->addText($wasteVal, $font, $centerPara);

            if (str_contains(strtoupper($wasteVal), 'VAN RÁ LEHETŐSÉG')) {
                $hasRoomForImprovement = true;
            }
        }

        $calculatedStatus = $hasRoomForImprovement ? 'Fejlesztendő' : 'Megfelelő';
        $addSpannedRow('A technológiai alrendszer', $calculatedStatus, true);
    }

    public static function calculateVehicleTotal($vehicleData): array
    {
        $type = $vehicleData['category'] ?? '';
        $fuel = $vehicleData['fuel'] ?? '';
        $capacity = floatval($vehicleData['capacity'] ?? 0);
        $annualHours = floatval($vehicleData['usageValue'] ?? 24 * 365);

        $hourlyNorm = 0.0;

        if ($type === 'Anyagmozgató') {
            if ($fuel === 'Elektromos áram' || $fuel === 'Elektromos') {
                $hourlyNorm = self::getElectricForkliftNorm($capacity);
            }
        }

        $annualConsumption = $hourlyNorm * $annualHours;

        return [
            'total' => round($annualConsumption, 2),
        ];
    }

    private static function getElectricForkliftNorm(float $capacity): float
    {
        $normTable = [
            1.5 => 4.47,
            2.0 => 4.81,
            2.5 => 7.16,
            3.0 => 7.56,
            3.5 => 8.33,
            4.0 => 8.70,
            4.5 => 9.30,
            5.0 => 10.38,
            5.5 => 12.80,
            6.0 => 12.20,
            6.5 => 12.20,
            7.0 => 13.40,
        ];

        if ($capacity <= 0) {
            return 0.0;
        }

        if (isset($normTable[(string) $capacity])) {
            return $normTable[(string) $capacity];
        }
        $closestCapacity = null;
        $minDiff = null;

        foreach ($normTable as $capKey => $normValue) {
            $diff = abs($capacity - $capKey);
            if ($minDiff === null || $diff < $minDiff) {
                $minDiff = $diff;
                $closestCapacity = $capKey;
            }
        }

        return $normTable[$closestCapacity] ?? 0.0;
    }

    public static function calculateHeaterPoints(array $heater): array
    {
        $points = 100 * self::CARRIER_VALUES[$heater['carrier']] * self::REGULATION_VALUES[$heater['regulation']] * self::STATE_VALUES[$heater['state']];
        return [
            "points" => round($points, 2),
            "status" => ($points > 60.0 ? "Megfelelő" : "Fejlesztendő")
        ];
    }

    public const CARRIER_VALUES = [
        "H hőszivattyús elektromos áram" => 1.0,
        "Biogáz" => 0.9,
        "Távfűtés" => 0.8,
        "Biomassza" => 0.75,
        "Pellet" => 0.72,
        "Csúcson kívüli elektromos áram" => 0.68,
        "Elektromos áram" => 0.65,
        "Földgáz" => 0.58,
        "Tűzifa" => 0.52,
        "PB-gáz" => 0.45,
        "Tüzelőolaj" => 0.3,
        "Szén" => 0.15,
        "Egyéb" => 0.5
    ];

    public const REGULATION_VALUES = [
        "Időjárásfüggő szabályozás" => 1.0,
        "Központi értékről történő szabályozás" => 0.85,
        "Fix értéktartás" => 0.7
    ];

    public const STATE_VALUES = [
        "NEW" => 1.0,
        "SERVICED" => 0.9,
        "UNRELIABLE" => 0.5,
        "OOO" => 0.0
    ];

    public const HMV_REGULATION_VALUES = [
        "Nincs" => 0.0,
        "Hőmérsékletre" => 33.3,
        "Időprogramra" => 66.7,
        "Hőmérsékletre és időprogramra" => 100
    ];

    public const COOLER_TYPES = [
        'Elektromos üzemű hőszivattyú levegő hőforrással (vizes)',
        'Elektromos üzemű hőszivattyú levegő hőforrással (hűtőgázos)',
        'Elektromos üzemű hőszivattyú talajhő hőforrással',
        'Elektromos üzemű hőszivattyú víz hőforrással',
        'VRV/VRF',
        'Split klíma',
        'Technológiai hűtés (hőszivattyú)',
        'Technológiai hűtés (folyadékhűtő)',
        'Technológiai hűtés',
        'Folyadékhűtő',
        'Hőszivattyú'
    ];

    public static function calculateCoolerPoints(array $cooler)
    {
        $energy_efficiency_multiplier = self::HEATER_ELECTRIC_CALC_MODE[$cooler['baseType']] *
            (1 + self::HEATER_ELECTRIC_CALC_INSTALLATION[$cooler['placementType']]) *
            (1 + self::HEATER_ELECTRIC_CALC_SOURCE[$cooler['ambientMedium']]) *
            (1 + self::HEATER_ELECTRIC_CALC_MEDIUM[$cooler['heatTransfer']]) *
            (1 + self::HEATER_ELECTRIC_CALC_REFRIGERANT[$cooler['refrigerant']]) *
            (1 + self::HEATER_DESCRIPTIONS[$cooler['state']]);
        $multiplier = max([1.03, min([1.5, $energy_efficiency_multiplier])]);
        $scop_cop_ratio = round(($multiplier / 1) * 100, 2);
        $base_points = ($scop_cop_ratio >= 120.8 ? 33 :
            ($scop_cop_ratio >= 112.7 ? 30 :
                ($scop_cop_ratio >= 104.5 ? 25 :
                    ($scop_cop_ratio >= 96.4 ? 20 :
                        ($scop_cop_ratio >= 88.2 ? 15 :
                            ($scop_cop_ratio >= 80.0 ? 10 :
                                ($scop_cop_ratio >= 71.9 ? 5 : 0)))))));
        $regulation_points = match ($cooler['regulation']) {
            'Fix értéktartás' => 0,
            'Központi értékről történő szabályozás' => 5,
            default => 10
        };
        $combined = $base_points + $regulation_points;
        return [
            "base" => round(($base_points / 33) * 100, 2),
            "regulation" => round(($regulation_points / 10) * 100, 2),
            "combined" => round(($combined / 43) * 100, 2),
        ];
    }

    public const HEATER_DESCRIPTIONS = [
        "NEW" => 0,
        "SERVICED" => -0.02,
        "UNRELIABLE" => -0.05,
        "OOO" => -0.05
    ];

    public const HEATER_ELECTRIC_CALC_MODE = [
        "Ismeretlen" => 0,
        "On/Off működés, 1 hűtőkör" => 0.95,
        "Többfokozatú működés, hűtőkörönként több kompresszor" => 1.04,
        "Inverteres/fordulatszám szabályzott kompresszorok" => 1.18,
    ];

    public const HEATER_ELECTRIC_CALC_INSTALLATION = [
        "Ismeretlen" => 0,
        "Gyári előírások betartásával, jól szellőző helyen" => 0,
        "Részben zavart légárammal" => -0.05,
        "Rosszul szellőző, zugos helyen" => -0.12,
    ];

    public const HEATER_ELECTRIC_CALC_SOURCE = [
        "Ismeretlen" => 0,
        "Levegő" => -0.03,
        "Nedvesített levegő" => 0,
        "Talajszonda" => 0.04,
    ];

    public const HEATER_ELECTRIC_CALC_MEDIUM = [
        "Ismeretlen" => 0,
        "Levegő" => 0,
        "Víz (normál üzemi tartomány)" => -0.05,
        "Víz (magas hőmérsékletű üzemi tartomány)" => -0.075,
    ];

    public const HEATER_ELECTRIC_CALC_REFRIGERANT = [
        "Ismeretlen" => 0,
        "R410A" => 0,
        "R32" => 0.02,
        "R454B" => 0.01,
        "R407C" => -0.02,
        "R22" => -0.04,
        "R134a (állandó sebesség)" => 0,
        "R134a (VSD/centrifugás)" => 0.01,
        "R1234ze" => 0.01,
        "R290" => 0.02,
    ];

    public static function calculateVentilationGoodness(array $hvacSystem, float $sfp): array
    {
        $sfp_points = match (true) {
            $sfp < 500 => 15, $sfp < 750 => 13, $sfp < 1250 => 11,
            $sfp < 2000 => 9, $sfp < 3000 => 7, $sfp < 4500 => 5,
            default => 0
        };
        $sfp_goodness = round(($sfp_points / 15) * 100, 2);
        $ins_thick = $hvacSystem['insulationWidth'] ?? 0;
        $ins_point = match (true) {
            $ins_thick < 10 => 0, $ins_thick < 20 => 1, $ins_thick < 40 => 2,
            default => 3
        };
        $insulation_goodness = round(($ins_point / 3) * 100);
        $hv_type = $hvacSystem['retriever'] ?? "Nincs";
        $hv_year = (int) ($hvacSystem['retrieverYear'] ?? 0);
        $hv_idx = ($hv_year < 2016) ? 0 : 1;
        $hv_point = self::RETRIEVER_POINTS[$hv_type][$hv_idx] ?? 0;
        $retriever_goodness = round(($hv_point / 10) * 100, 2);
        return [
            "sfp_goodness" => $sfp_goodness,
            "retriever_goodness" => $retriever_goodness,
            "insulation_goodness" => $insulation_goodness
        ];
    }

    public const RETRIEVER_POINTS = [
        "Keresztáramú" => [4, 6],
        "Forgódobos" => [6, 10],
        "Közvetítő közeges" => [2, 6],
        "Hőcsöves" => [0, 0],
        "Keverőkamra" => [0, 0],
        "Egyéb" => [0, 0],
        "Nincs" => [0, 0]
    ];
}
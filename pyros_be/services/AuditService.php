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
            $complexLabel = (!empty($row['pod_id']) && !empty($row['name']))
                ? $row['pod_id'] . ' / ' . $row['name']
                : (!empty($row['name']) ? $row['name'] : $row['pod_id']);

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

    /**
     * A teljes fogyasztás összegzése egyetlen számmá a mérő JSON-jából (a hierarchiafához)
     */
    public static function calculateTotalConsumption(?string $consumptionJson, string $source = ''): float
    {
        if (empty($consumptionJson))
            return 0;

        $data = json_decode($consumptionJson, true);
        if (!is_array($data))
            return 0;

        $total = 0;
        foreach ($data as $year => $months) {
            if (!is_array($months))
                continue;
            foreach ($months as $val) {
                if ($val !== null && is_numeric($val)) {
                    $total += $val;
                }
            }
        }

        return ($source === 'SOLAR') ? -abs($total) : $total;
    }

    /**
     * Mérő hierarchia fa felépítése rekurzívan egy TextRun elembe
     */
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

        // 1. OSZLOP: MÉRŐ NEVE
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

        // 2. OSZLOP: DEADSPACE
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

    /**
     * Jármű fajlagos fogyasztásának és mértékegységének kiszámítása
     */
    public static function calculateVehicleQf(array $vehicle): array
    {
        // 1. A DB-ből jövő 'consumption' JSON sztring
        $rawConsumption = $vehicle['consumption'] ?? null;

        // 2. Összes éves fogyasztás kiszámítása
        $totalConsumption = self::calculateTotalConsumption($rawConsumption, $vehicle['source'] ?? '');

        // 3. Átszámítás kWh-ra
        $unit = $vehicle['measurement'] ?? 'KWH';
        $totalKwh = self::convertToKwh($totalConsumption, $unit);

        // 4. Használati mutatók lekérése - kezeli a camelCase és snake_case kulcsokat is!
        $metric = $vehicle['usage_metric'] ?? $vehicle['usageMetric'] ?? 'km';
        $usage1 = (float) ($vehicle['usage_value'] ?? $vehicle['usageValue'] ?? 0);
        $usage2 = (float) ($vehicle['usage_value2'] ?? $vehicle['usageValue2'] ?? 0);

        $divisor = 0;
        $unitLabel = 'kWh/km';
        $threshold = 0.8;

        if (strcasecmp($metric, 'tkm') === 0) {
            $divisor = $usage1 * $usage2; // km * tonna
            $unitLabel = 'kWh/tkm';
            $threshold = 0.15;
        } elseif (strcasecmp($metric, 'Üzemóra') === 0 || strcasecmp($metric, 'h') === 0) {
            $divisor = $usage1;
            $unitLabel = 'kWh/h';
            $threshold = 15.0;
        } else { // km
            $divisor = $usage1;
            $unitLabel = 'kWh/km';
            $threshold = 0.8;
        }

        // 5. Qf kiszámítása
        $qf = ($divisor > 0) ? ($totalKwh / $divisor) : 0;
        $status = ($qf > $threshold) ? 'Fejlesztendő' : 'Megfelelő';

        return [
            'qf' => $qf,
            'unit' => $unitLabel,
            'status' => $status
        ];
    }

    /**
     * Jármű értékelési táblázat építése Word-höz
     */
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

        // Fejléc sor
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

        // Adatsorok generálása
        foreach ($vehicles as $v) {
            $table->addRow(null, ['cantSplit' => true]);

            // Fajlagos érték kiszámítása
            $calc = self::calculateVehicleQf($v);

            // Jármű neve
            $c1 = $table->addCell($colWidths['vehicle'], $dataCellStyle);
            $c1->addText($v['vehicle_name'] ?? '', $dataFontStyle, $dataParagraphStyleLeft);

            // Telephely neve
            $c2 = $table->addCell($colWidths['complex'], $dataCellStyle);
            $c2->addText($v['complex_name'] ?? '', $dataFontStyle, $dataParagraphStyleLeft);

            // Kalkulált Fajlagos érték + dinamikus mértékegység (kWh/km, kWh/h, kWh/tkm)
            $c3 = $table->addCell($colWidths['qf'], $dataCellStyle);
            $formattedQf = number_format($calc['qf'], 2, ',', ' ') . ' ' . $calc['unit'];
            $c3->addText($formattedQf, $dataFontStyle, $dataParagraphStyleCenter);

            // Besorolás
            $c4 = $table->addCell($colWidths['status'], $dataCellStyle);
            $c4->addText($calc['status'], $dataFontStyle, $dataParagraphStyleCenter);
        }
    }
}
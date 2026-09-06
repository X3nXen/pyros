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

    public static function getEnergyCarrierSummaryRows(array $standings): array
    {
        $carriers = [];

        foreach ($standings as $row) {
            $rawSource = strtoupper(trim($row['source'] ?? ''));
            if (empty($rawSource))
                continue;

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

            if (in_array($row['measurement_type'], ['MAIN', 'VIRTUAL']) && empty($row['sub_to'])) {
                $carriers[$carrierName]['total'] += $consumptionValue;
            }

            if (!empty($row['purpose'])) {
                if ($row['purpose'] === 'BUILDING') {
                    $carriers[$carrierName]['building'] += $consumptionValue;
                } elseif ($row['purpose'] === 'SERVICE') {
                    $carriers[$carrierName]['service'] += $consumptionValue;
                } elseif ($row['purpose'] === 'CARRY') {
                    $carriers[$carrierName]['carry'] += $consumptionValue;
                }
            }
        }

        $carrierRows = [];

        foreach ($carriers as $name => $data) {
            $total = $data['total'];
            $building = $data['building'];
            $carry = $data['carry'];
            $rawSource = $data['raw_source'];
            $product = 0.0;

            if (in_array($rawSource, ['GAS', 'REMOTE', 'COAL', 'PAKURA', 'WOOD', 'PB', 'PROPANE', 'LPG'])) {
                $carry = 0.0;
                $product = max(0.0, $total - $building);
            } elseif (in_array($rawSource, ['GASOLINE', 'PETROL'])) {
                $building = 0.0;
                $product = max(0.0, $total - $carry);
            } else {
                $product = max(0.0, $total - $building - $carry);
            }

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

    public static function generateSankeyDiagram(array $carrierRows, string $outputPath): bool
    {
        $links = [];

        $buildingNode = 'Épület';
        $productNode = 'Tevékenység';
        $vehicleNode = 'Szállítás';

        // Szigorú érték-tisztító segédfüggvény
        $parseValue = function ($val) {
            if (is_numeric($val))
                return (float) $val;
            if (empty($val))
                return 0.0;
            $clean = preg_replace('/[^\d\,\.]/', '', str_replace(['&nbsp;', "\xC2\xA0", ' ', 'kWh'], '', $val));
            $clean = str_replace(',', '.', $clean);
            return (float) $clean;
        };

        foreach ($carrierRows as $row) {
            $source = trim(strip_tags($row['carrier_name'] ?? 'Energia'));

            $bVal = $parseValue($row['carrier_building'] ?? 0);
            $pVal = $parseValue($row['carrier_product'] ?? 0);
            $vVal = $parseValue($row['carrier_vehicle'] ?? 0);

            if ($bVal > 0) {
                $links[] = ['from' => $source, 'to' => $buildingNode, 'flow' => $bVal];
            }
            if ($pVal > 0) {
                $links[] = ['from' => $source, 'to' => $productNode, 'flow' => $pVal];
            }
            if ($vVal > 0) {
                $links[] = ['from' => $source, 'to' => $vehicleNode, 'flow' => $vVal];
            }
        }

        if (empty($links)) {
            return false;
        }

        // Chart.js v2/v3 stabil Sankey struktúra
        $chartConfig = [
            'type' => 'sankey',
            'data' => [
                'datasets' => [
                    [
                        'data' => $links,
                        'colorFrom' => '#0055A5',
                        'colorTo' => '#28A745',
                        'colorMode' => 'gradient'
                    ]
                ]
            ]
        ];

        $postData = json_encode([
            'width' => 600,
            'height' => 300,
            'format' => 'png',
            'chart' => $chartConfig
        ]);

        $ch = curl_init('https://quickchart.io/chart');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $imageContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Csak akkor mentjük el, ha 200 OK választ kaptunk
        if ($httpCode === 200 && !empty($imageContent)) {
            file_put_contents($outputPath, $imageContent);

            // EXTRA VALIDÁCIÓ: Megnézzük, hogy a fájl valóban érvényes KÉP-e (nem JSON hibaüzenet)
            $imageInfo = @getimagesize($outputPath);
            if ($imageInfo !== false) {
                return true; // Érvényes kép!
            }
        }

        // Ha nem kép jött vissza, töröljük a hibás fájlt
        if (file_exists($outputPath)) {
            @unlink($outputPath);
        }

        return false;
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
}
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
}
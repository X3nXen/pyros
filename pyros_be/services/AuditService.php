<?php

use PhpOffice\PhpWord\TemplateProcessor;
class AuditService
{
    public static function xmlEscape($val)
    {
        return htmlspecialchars($val ?? '', ENT_XML1, 'UTF-8');
    }
    public const EnergySources = [
        'COAL' => 'Szén',
        'GASOLINE' => 'Gázolaj',
        'PETROL' => 'Benzin',
        'GAS' => 'Földgáz',
        'ELECTRICITY' => 'Elektromos áram',
        'REMOTE' => 'Távhő',
        'PAKURA' => 'Petrolkoksz',
        'PB' => 'PB Gáz',
        'PROPANE' => 'Propán',
        'LPG' => 'LPG',
        'WOOD' => 'Tűzifa',
        'SOLAR' => 'Napenergia'
    ];

    public const EnergyMeasurements = [
        'KWH' => 'kWh',
        'MJ' => 'MJ',
        'MCUBE' => 'm³',
        'GJ' => 'GJ',
        'MWH' => 'MWh',
        'L' => 'l',
        'KG' => 'kg',
        'T' => 'tonna'
    ];

    public const MonthAbbrreviationToFull = [
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
        "R134A (állandó sebesség)" => 0,
        "R134A (VSD/centrifugás)" => 0.01,
        "R1234ze" => 0.01,
        "R290" => 0.02,
    ];
    public const RETRIEVER_POINTS = [
        "Keresztáramú" => [4, 6],
        "Forgódobos" => [6, 10],
        "Közvetítő közeges" => [2, 6],
        "Hőcsöves" => [0, 0],
        "Keverőkamra" => [0, 0],
        "Egyéb" => [0, 0],
        "Nincs" => [0, 0]
    ];

    public static function processMonthlyConsumptionList(array $rawData)
    {
        /*
        $grouped = [
            telephely_neve => [
                év1.hónap1 => [
                    source1 => [
                        metric: metric
                        data: data
                    ] 
                ]
            ]
        ]
        */

        $grouped = [];
        foreach ($rawData as $row) {
            $complexName = (!empty($row['name']) ? $row['name'] : 'Telephely');
            $rawSource = self::EnergySources[$row['source']];
            $rawUnit = self::EnergyMeasurements[$row['measurement']];

            if (!isset($grouped[$complexName])) {
                $grouped[$complexName] = [];
            }
            $consumptionJson = json_decode($row['consumption'], true);
            foreach ($consumptionJson as $year => $months) {
                if (!is_array($months)) {
                    continue;
                }
                foreach ($months as $month => $value) {
                    if ($value !== null) {
                        $monthName = self::MonthAbbrreviationToFull[$month]['name'];
                        if (!isset($grouped[$complexName][$year . '.' . $monthName])) {
                            $grouped[$complexName][$year . '.' . $monthName] = [];
                        }
                        if (!isset($grouped[$complexName][$year . '.' . $monthName][$rawSource])) {
                            $grouped[$complexName][$year . '.' . $monthName][$rawSource] = ["metric" => $rawUnit, "total" => 0.0];
                        }
                        $grouped[$complexName][$year . '.' . $monthName][$rawSource]['total'] += $value;
                    }
                }
            }
            $monthOrder = [
                'január' => 1,
                'február' => 2,
                'március' => 3,
                'április' => 4,
                'május' => 5,
                'június' => 6,
                'július' => 7,
                'augusztus' => 8,
                'szeptember' => 9,
                'október' => 10,
                'november' => 11,
                'december' => 12
            ];
            foreach ($grouped as $complexName => &$monthsData) {
                uksort($monthsData, function ($a, $b) use ($monthOrder) {
                    $posA = strpos($a, '.');
                    $yearA = substr($a, 0, $posA);
                    $monthNameA = mb_strtolower(substr($a, $posA + 1));

                    $posB = strpos($b, '.');
                    $yearB = substr($b, 0, $posB);
                    $monthNameB = mb_strtolower(substr($b, $posB + 1));

                    if ($yearA !== $yearB) {
                        return $yearA <=> $yearB;
                    }

                    $indexA = $monthOrder[$monthNameA] ?? 0;
                    $indexB = $monthOrder[$monthNameB] ?? 0;

                    return $indexA <=> $indexB;
                });
            }
            unset($monthsData);
        }
        return $grouped;
    }

    public static function calculateTotalConsumption(string $consumptionJson): float
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
            return $total;
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

        return $total;
    }

    /**
     * A nyers adat parsingja nekünk érdekes/használható formában
     * @param array $standings (A mérések összes adatja)
     * @return array (A várt struktúra: [
            source => [
                total => 0,
                metric => metric,
                subs => [
                    BUILDING => 0,
                    CARRY => 0,
                    SERVICE => 0
                    SUM => 0
                ]
            ]
        ])
     */
    public static function calculateTotalConsumptionList(array $standings): array
    {
        /*$grouped = [
            "<source>" => [
                "total" => 0,
                "metric" => '<metric>',
                "subs" => [
                    'BUILDING' => 0,
                    'CARRY' => 0,
                    'SERVICE' => 0
                    'SUM' => 0
                ]
            ]
        ];*/
        $grouped = [];
        foreach ($standings as $row) {
            $source = strtoupper(trim($row['source']));
            if (empty($source)) {
                continue;
            }
            if (!isset($grouped[$source])) {
                $grouped[$source] = [
                    "total" => 0.0,
                    "metric" => $row['measurement'],
                    "subs" => ['BUILDING' => 0.0, 'CARRY' => 0.0, 'SERVICE' => 0.0, 'SUM' => 0.0],
                ];
            }
            if ($row['measurement_type'] == 'MAIN') {
                $grouped[$source]['total'] += self::calculateTotalConsumption($row['consumption']);
                $grouped[$source]['metric'] = $row['measurement'];
            } else {
                $total = self::calculateTotalConsumption($row['consumption']);
                $grouped[$source]['subs'][$row['purpose']] += $total;
                $grouped[$source]['subs']['SUM'] += $total;
            }
        }
        //Adat átszervezése
        foreach ($grouped as $source => &$source_data) {
            if ($source == 'GAS' || $source == 'REMOTE' || $source == 'COAL' || $source == 'PAKURA' || $source == 'WOOD' || $source == 'SOLAR') {
                $carryTotal = $source_data['subs']['CARRY'];
                $source_data['subs']['CARRY'] = 0.0;
                $source_data['subs']['SUM'] = ($source_data['subs']['SUM'] - $carryTotal) > 0 ? $source_data['subs']['SUM'] - $carryTotal : 0;
                $source_data['subs']['SERVICE'] = $source_data['total'] - $source_data['subs']['BUILDING'];
            } else if ($source == 'PETROL' || $source == 'GASOLINE') {
                $buildingTotal = $source_data['subs']['BUILDING'];
                $source_data['subs']['BUILDING'] = 0.0;
                $source_data['subs']['SUM'] = ($source_data['subs']['SUM'] - $buildingTotal) > 0 ? $source_data['subs']['SUM'] - $buildingTotal : 0;
                $source_data['subs']['SERVICE'] = $source_data['total'] - $source_data['subs']['CARRY'];
            } else {
                $source_data['subs']['SERVICE'] = $source_data['total'] - ($source_data['subs']['CARRY'] + $source_data['subs']['BUILDING']);
            }
        }
        return $grouped;
    }

    /**
     * Vezetői összefoglaló adatai, illetve annak egész dokumentumot érintő beillesztései
     * @param array $data (A feldolgozandó adatok, json parse-olás után asszociatív tömbben) 
     * @param TemplateProcessor $templateProcessor (A PHPWord TemplateProcessora, ami lehetővé teszi a beillesztést)
     * @return void
     */
    public static function createIntroductionChapter(array $data, TemplateProcessor $templateProcessor): void
    {
        $companyName = $data['fullName'] ?? '';
        $ownerPercentageText = $data['foreign'] ? ($data['percent'] ?? 0) . '%-ban külföldi' : 'magyar';
        $income = $data['income'] ?? 0;
        $incomeInThousands = (float) $income / 1000;
        $formattedIncome = number_format($incomeInThousands, 0, ',', '.');
        $templateProcessor->setValue('company_name', AuditService::xmlEscape($companyName));
        $templateProcessor->setValue('foundation_year', AuditService::xmlEscape($data['foundationYear'] ?? ''));
        $templateProcessor->setValue('owner_percentage', AuditService::xmlEscape($ownerPercentageText));
        $templateProcessor->setValue('company_product', AuditService::xmlEscape($data['mainActivity'] ?? ''));
        $templateProcessor->setValue('company_place', AuditService::xmlEscape($data['companyPlace'] ?? ''));
        $templateProcessor->setValue('data_year', AuditService::xmlEscape($data['dataYear'] ?? ''));
        $templateProcessor->setValue('employee_count', AuditService::xmlEscape($data['employeeCount'] ?? ''));
        $templateProcessor->setValue('profit', AuditService::xmlEscape($formattedIncome ?? '') . ' ');
    }

    /**
     * A Fogyasztások felosztása táblázat feltöltése adatokkal
     * @param array $data (A szervezett fogyasztási adatok)
     * @param TemplateProcessor $templateProcessor (A PHPWordból származó TemplateProcessor)
     * @param bool $needTotal (A 2 fejezet különbsége, hogy van vagy nincs Összesen oszlop)
     * @return void
     */
    public static function createConsumptionList(array $data, TemplateProcessor $templateProcessor, bool $needTotal): void
    {
        if (!empty($data)) {
            $rowVariablePrefix = $needTotal ? "carrier" : "carrier2";
            $templateProcessor->cloneRow($rowVariablePrefix, count($data));
            $i = 1;
            foreach ($data as $index => $row) {
                $norm_metric = self::EnergyMeasurements[$row['metric']];
                $norm_carrier_name = self::xmlEscape(self::EnergySources[$index]);
                $norm_building = self::xmlEscape(number_format($row['subs']['BUILDING'], 2, ',', ' ') . ' ' . $norm_metric);
                $norm_service = self::xmlEscape(number_format($row['subs']['SERVICE'], 2, ',', ' ') . ' ' . $norm_metric);
                $norm_carry = self::xmlEscape(number_format($row['subs']['CARRY'], 2, ',', ' ') . ' ' . $norm_metric);
                $norm_total = self::xmlEscape(number_format($row['total'], 2, ',', ' ') . ' ' . $norm_metric);

                $templateProcessor->setValue($rowVariablePrefix . "#{$i}", $norm_carrier_name);
                $templateProcessor->setValue($rowVariablePrefix . "_building#{$i}", $norm_building);
                $templateProcessor->setValue($rowVariablePrefix . "_product#{$i}", $norm_service);
                $templateProcessor->setValue($rowVariablePrefix . "_vehicle#{$i}", $norm_carry);
                if ($needTotal) {
                    $templateProcessor->setValue($rowVariablePrefix . "_total#{$i}", $norm_total);
                }
                $i++;
            }
        } else {
            $templateProcessor->setValue('carrier', self::xmlEscape('Nincs adat'));
            $templateProcessor->setValue('carrier_building', self::xmlEscape('-'));
            $templateProcessor->setValue('carrier_product', self::xmlEscape('-'));
            $templateProcessor->setValue('carrier_vehicle', self::xmlEscape('-'));
            $templateProcessor->setValue('carrier_total', self::xmlEscape('-'));
        }
    }

    public static function createCurrentPricesSection(array $data, TemplateProcessor $templateProcessor)
    {
        if (!empty($data) && isset($data[0]['date_from'], $data[0]['date_to'])) {
            $dateFrom = new DateTime($data[0]['date_from']);
            $dateTo = new DateTime($data[0]['date_to']);
            $auditInterval = $dateFrom->format('Y.m.d') . ' - ' . $dateTo->format('Y.m.d');
        } else {
            $auditInterval = 'Nincs megadva';
        }

        $templateProcessor->setValue('audit_interval', self::xmlEscape($auditInterval));

        $marketData = EnergyPriceService::getMarketPrices();

        $eurHuf = $marketData['eur_huf_rate'] ?? 0;
        $gasEurMwh = $marketData['natural_gas_price'] ?? 0;
        $electricEurMwh = $marketData['electric_energy_price'] ?? 0;

        $gasConverted = ($gasEurMwh * $eurHuf) / 1000;
        $electricConverted = ($electricEurMwh * $eurHuf) / 1000;

        $gasSpecific = $gasConverted + 15.0;
        $electricSpecific = $electricConverted + 30.915;

        $templateProcessor->setValue('eur_huf_rate', self::xmlEscape(number_format($eurHuf, 2, ',', ' ')));
        $templateProcessor->setValue('natural_gas_price', self::xmlEscape(number_format($gasEurMwh, 2, ',', ' ')));
        $templateProcessor->setValue('electric_energy_price', self::xmlEscape(number_format($electricEurMwh, 2, ',', ' ')));

        $templateProcessor->setValue('natural_gas_converted', self::xmlEscape(number_format($gasConverted, 2, ',', ' ')));
        $templateProcessor->setValue('natural_gas_specific', self::xmlEscape(number_format($gasSpecific, 2, ',', ' ')));

        $templateProcessor->setValue('electric_converted', self::xmlEscape(number_format($electricConverted, 2, ',', ' ')));
        $templateProcessor->setValue('electric_energy_specific', self::xmlEscape(number_format($electricSpecific, 3, ',', ' ')));
    }

    public static function createInvestmentSection(array $data, TemplateProcessor $templateProcessor)
    {
        $bubor = $data['buborPercent'] ?? 0;
        $bond = $data['bondPercent'] ?? 0;
        $mnb = $data['mnbPercent'] ?? 0;

        $templateProcessor->setValue('bubor_rate', self::xmlEscape($bubor));
        $templateProcessor->setValue('bond_rate', self::xmlEscape($bond));
        $templateProcessor->setValue('mnb_rate', self::xmlEscape($mnb));

        $interest_rate = 0.3 * (((float) $bubor) / 100) + 0.5 * (((float) $bond) / 100) + 0.2 * (((float) $mnb) / 100);
        $interest_rate = round(($interest_rate + 0.03) * 100, 2);
        $templateProcessor->setValue('interest_rate', self::xmlEscape($interest_rate));
        $templateProcessor->setValue('current_date', date('Y.m.d'));
    }

    public static function createComplexesTable(array $data, TemplateProcessor $templateProcessor)
    {
        $templateProcessor->setValue('telephelyein', count($data) > 1 ? 'telephelyein' : 'telephelyén');

        if (!empty($data)) {
            $complex_index = 1;
            $templateProcessor->cloneBlock('block_complex', count($data), true, true);
            foreach ($data as $field) {
                $fieldJson = json_decode($field['complex_json'], true);
                if (!$fieldJson) {
                    continue;
                }

                $norm_name = self::xmlEscape(
                    ($fieldJson['postal'] ?? '') . ' ' .
                    ($fieldJson['city'] ?? '') . ', ' .
                    ($fieldJson['address'] ?? '') . '; ' .
                    ($fieldJson['name'] ?? '')
                );
                $replacements = [];
                foreach ($fieldJson['working'] as $working) {
                    $replacements[] = ["complex_function#{$complex_index}" => self::xmlEscape("Tevékenység: " . $working['workType']), "complex_shift#{$complex_index}" => self::xmlEscape("Munkarend: " . $working['workHours'])];
                }
                $templateProcessor->setValue("complex_name#{$complex_index}", $norm_name);
                $templateProcessor->cloneRowAndSetValues("function_shift_row#{$complex_index}", $replacements);

                $complex_index++;
            }
        } else {
            $templateProcessor->deleteBlock('block_complex');
        }
    }

    public static function createStandingByComplexSection(array $data, TemplateProcessor $templateProcessor)
    {
        /*
        $grouped = [
            telephely_neve => [
                év1.hónap1 => [
                    source1 => [
                        metric: metric
                        data: data
                    ] 
                ]
            ]
        ]
        */
        if (!empty($data)) {
            $complex_index = 1;
            $templateProcessor->cloneBlock('block_consumption', count($data), true, true);
            foreach ($data as $complex => $dateRow) {
                $replacements = [];
                $norm_complex_name = self::xmlEscape($complex);
                foreach ($dateRow as $month => $sources) {
                    foreach ($sources as $source => $value) {
                        $replacements[] = [
                            "consumption_month#{$complex_index}" => self::xmlEscape($month),
                            "consumption_carrier#{$complex_index}" => self::xmlEscape($source),
                            "consumption_amount#{$complex_index}" => self::xmlEscape(number_format($value['total'], 2, '.', ',') . ' ' . $value['metric'])
                        ];
                    }
                }
                $templateProcessor->setValue("consumption_complex_name#{$complex_index}", $norm_complex_name);
                $templateProcessor->cloneRowAndSetValues("consumption_month_row#{$complex_index}", $replacements);
                $complex_index++;
            }
        } else {
            $templateProcessor->deleteBlock('block_consumption');
        }
    }

    public static function buildStandingHierarchy(array $data)
    {
        /* 
            $hierarchy = [
                ["mainName" => name, "subName" => name, "total" => total]
            ]
        */
        $hierarchy = [];
        foreach ($data as $standingRow) {
            if ($standingRow['measurement_type'] !== 'MAIN') {
                continue;
            }
            $mainId = $standingRow['id'];
            $mainName = self::xmlEscape($standingRow['name']);
            $unitName = self::xmlEscape(self::EnergyMeasurements[$standingRow['measurement']]);
            $total = number_format(self::calculateTotalConsumption($standingRow['consumption']), 2, '.', ',');
            $hierarchy[] = ["network_mainstanding" => $mainName, "network_subto" => "-", "network_consumption" => $total . " " . $unitName];
            foreach ($data as $subStandings) {
                if ($subStandings['measurement_type'] === 'MAIN' || $subStandings['sub_to'] !== $mainId) {
                    continue;
                }
                $subName = self::xmlEscape($subStandings['name']);
                $subTotal = number_format(self::calculateTotalConsumption($subStandings['consumption']), 2, '.', ',');
                $hierarchy[] = ["network_mainstanding" => $mainName, "network_subto" => $subName, "network_consumption" => $subTotal . " " . $unitName];
            }
        }
        return $hierarchy;
    }

    public static function createStandingHierarchySection(array $data, TemplateProcessor $templateProcessor)
    {
        $templateProcessor->cloneRowAndSetValues("network_standing_row", $data);
    }

    public static function buildBuildingRows(array $data, array &$improveable_list)
    {
        /*

        $grouped = [
            ["building_listing_name" => name, "building_listing_complex" => complex, "building_listing_qf" => qf, "building_listing_status" => status]
        ]
        */
        $grouped = [];
        foreach ($data as $buildingRow) {
            $buildingName = self::xmlEscape($buildingRow['building_name']);
            $complexName = self::xmlEscape($buildingRow['complex_name']);
            $qf = self::xmlEscape(number_format((float) $buildingRow['qf'], 2, ".", ",") . " kWh/m²a");
            $status = '';
            if ((float) $buildingRow['qf'] > 150) {
                if (!isset($improveable_list['building'])) {
                    $improveable_list['building'] = [];
                }
                $improveable_list['building'][] = $buildingName;
                $status = self::xmlEscape("Fejlesztendő");
            } else {
                $status = self::xmlEscape("Megfelelő");
            }
            $grouped[] = ["building_listing_name" => $buildingName, "building_listing_complex" => $complexName, "building_listing_qf" => $qf, "building_listing_status" => $status];
        }
        usort($grouped, function ($a, $b) {
            return strcasecmp($a['building_listing_complex'], $b['building_listing_complex']);
        });
        return $grouped;
    }
    public static function createBuildingListingSection(array $data, TemplateProcessor $templateProcessor)
    {
        $templateProcessor->cloneRowAndSetValues("building_listing_row", $data);
    }

    public static function calculateHeaterPoints(array $heater): array
    {
        $points = 100 * self::CARRIER_VALUES[$heater['carrier']] * self::REGULATION_VALUES[$heater['regulation']] * self::STATE_VALUES[$heater['state']];
        return [
            "points" => round($points, 2),
            "status" => ($points > 60.0 ? "Megfelelő" : "Fejlesztendő")
        ];
    }

    public static function buildHeatingListingRows(array $data, array &$improveable_list)
    {
        /*
        $grouped = [
            ['heating_listing_complex' => complex, 'heating_listing_name' => name, 'heating_listing_type' => type, 'heating_listing_points' => points, 'heating_listing_status'=>status]
        ]
        */
        if (empty($data) || !is_array($data)) {
            return [];
        }
        $grouped = [];
        foreach ($data as $heatingRow) {
            $heaters = json_decode($heatingRow['heaters'], true);
            if (!is_array($heaters))
                continue;
            $complex_name = self::xmlEscape($heatingRow['complex_name']);
            foreach ($heaters as $index => $heater) {
                $heaterName = self::xmlEscape($heater['name']);
                $heaterType = self::xmlEscape($heater['heatingType']);
                $heaterCalculated = self::calculateHeaterPoints($heater);
                if ($heaterCalculated['status'] == 'Fejlesztendő') {
                    if (!isset($improveable_list['heaters'])) {
                        $improveable_list['heaters'] = [];
                    }
                    $improveable_list['heaters'][] = $heaterName;
                }
                $heaterPoints = self::xmlEscape($heaterCalculated['points']);
                $heaterStatus = self::xmlEscape($heaterCalculated['status']);
                $grouped[] = ['heating_listing_complex#1' => $complex_name, 'heating_listing_name#1' => $heaterName, 'heating_listing_type#1' => $heaterType, 'heating_listing_points#1' => $heaterPoints, 'heating_listing_status#1' => $heaterStatus];
            }
        }
        usort($grouped, function ($a, $b) {
            return strcasecmp($a['heater_listing_complex#1'], $b['heater_listing_complex#1']);
        });
        return $grouped;
    }

    public static function createHeatingListingSection(array $data, TemplateProcessor $templateProcessor, int &$sectionIndex, string $companyName)
    {
        if (empty($data)) {
            $templateProcessor->setValue("subheading_building_heating", "");
            $templateProcessor->setValue("heating_intro", "");
            $templateProcessor->setValue("heating_subtext", "");
            $templateProcessor->cloneBlock("block_heating", 0, true, true);
        } else {
            $subheading_text = self::xmlEscape("7." . $sectionIndex . ". Épületek fűtése");
            $heatingIntro = self::xmlEscape("A(z) " . $companyName . " az alábbi fűtési rendszerekkel rendelkezik:");
            $heatingSubtext = self::xmlEscape("A pontszám megállapításánál figyelembe vett szempontok: karbonintenzitás, elérhetőség, technológia korszerűsége, illetve a berendezés aktuális műszaki állapota.");

            $templateProcessor->setValue("subheading_building_heating", $subheading_text);
            $templateProcessor->setValue("heating_intro", $heatingIntro);
            $templateProcessor->setValue("heating_subtext", $heatingSubtext);

            $templateProcessor->cloneBlock("block_heating", 1, true, true);
            $templateProcessor->cloneRowAndSetValues("heating_listing_row#1", $data);
            $sectionIndex++;
        }
    }

    public static function createHMVListingSection(array $data, TemplateProcessor $templateProcessor, int &$sectionIndex, string $companyName)
    {
        if (empty($data)) {
            $templateProcessor->setValue("subheading_building_hmv", "");
            $templateProcessor->setValue("hmv_intro", "");
            $templateProcessor->setValue("hmv_subtext", "");
            $templateProcessor->cloneBlock("block_hmv", 0, true, true);
        }
    }

    public static function buildLightingListingRows(array $data, array &$improveable_list)
    {
        /*
        $grouped = [
        lighting_listing_complex#1 => complex,
        lighting_listing_zone#1 => zone,
        lighting_listing_qf#1 => qf, 
        lighting_listing_annual#1 => annual, 
        lighting_listing_status => status
        ]
        */

        $grouped = [];
        foreach ($data as $index => $rowData) {
            $complexName = self::xmlEscape($rowData['complex_name']);
            $zoneName = self::xmlEscape($rowData['name']);
            $specific_sum = self::xmlEscape(number_format($rowData['specific_sum'], 2, '.', ',') . 'kWh/m');
            $annual_sum = self::xmlEscape(number_format(self::calculateTotalConsumption($rowData['consumption']), 2, '.', ',') . 'kWh');
            $status = "";
            if ($rowData['solution'] === "LED – bármely lámpatest-változat" && ((float) $rowData['building_size']) * 0.8 <= $rowData['size']) {
                $status = self::xmlEscape('Megfelelő');
            } else {
                $status = self::xmlEscape('Fejlesztendő');
                $improveable_list['lighting'][] = self::xmlEscape($zoneName . ' világítási rendszer');
            }
            $grouped[] = [
                "lighting_listing_complex#1" => $complexName,
                "lighting_listing_zone#1" => $zoneName,
                "lighting_listing_qf#1" => $specific_sum,
                "lighting_listing_annual#1" => $annual_sum,
                "lighting_listing_status#1" => $status
            ];
        }
        usort($grouped, function ($a, $b) {
            return strcasecmp($a['lighting_listing_complex#1'], $b['lighting_listing_complex#1']);
        });
        return $grouped;
    }

    public static function createLightingListingSection(array $data, TemplateProcessor $templateProcessor, int &$sectionIndex, string $companyName)
    {
        if (empty($data)) {
            $templateProcessor->setValue("subheading_building_lighting", "");
            $templateProcessor->setValue("lighting_intro", "");
            $templateProcessor->setValue("lighting_subtext", "");
            $templateProcessor->cloneBlock("block_lighting", 0, true, true);
        } else {
            $subheading_text = self::xmlEscape("7." . $sectionIndex . ". Világítási rendszerek");
            $lighting_intro = self::xmlEscape("A(z) " . $companyName . " az alábbi világítási zónákkal és rendszerekkel rendelkezik:");
            $lighting_subtext = self::xmlEscape("A fajlagos érték kialakításánál az alábbi szempontok kerültek figyelembevételre: világítótest fajtája (Fénycső, Halogénizzó, LED, stb.), szabályozás módja (Kézi vagy Automatikus működtetés).");

            $templateProcessor->setValue("subheading_building_lighting", $subheading_text);
            $templateProcessor->setValue("lighting_intro", $lighting_intro);
            $templateProcessor->setValue("lighting_subtext", $lighting_subtext);

            $templateProcessor->cloneBlock("block_lighting", 1, true, true);
            $templateProcessor->cloneRowAndSetValues("lighting_listing_row#1", $data);
            $sectionIndex++;
        }
    }

    public static function calculateCoolerPoints(array $cooler)
    {
        $energy_efficiency_multiplier = self::HEATER_ELECTRIC_CALC_MODE[$cooler['baseType']] *
            (1 + self::HEATER_ELECTRIC_CALC_INSTALLATION[$cooler['placementType']]) *
            (1 + self::HEATER_ELECTRIC_CALC_MEDIUM[$cooler['ambientMedium']]) *
            (1 + self::HEATER_ELECTRIC_CALC_SOURCE[$cooler['heatTransfer']]) *
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

    public static function buildCoolingListingRows(array $data, array &$improveable_list)
    {
        /*
        grouped = [
            [
                "cooling_listing_complex#1" => complex, 
                "cooling_listing_name#1" => name, 
                "cooling_listing_type#1" => type
                "cooling_listing_base#1" => base
                "cooling_listing_reg#1" => reg
                "cooling_listing_combined#1"=>combined
            ]
        ]
        */
        $grouped = [];
        foreach ($data as $rowData) {
            $coolers = json_decode($rowData['heaters'], true);
            $complexName = self::xmlEscape($rowData['name']);

            if (!is_array($coolers)) {
                continue;
            }

            foreach ($coolers as $cooler) {
                $coolerName = self::xmlEscape($cooler['name']);
                $coolerType = self::xmlEscape($cooler['heatingType']);
                $coolerPoints = self::calculateCoolerPoints($cooler);
                if ($coolerPoints['combined'] < 75) {
                    if (!isset($improveable_list['coolers'])) {
                        $improveable_list['coolers'] = [];
                    }
                    $improveable_list['coolers'][] = $coolerName;
                }
                $coolerBase = self::xmlEscape(number_format($coolerPoints['base'], 2, '.', ',') . " %");
                $coolerReg = self::xmlEscape(number_format($coolerPoints['regulation'], 2, '.', ',') . " %");
                $coolerComb = self::xmlEscape(number_format($coolerPoints['combined'], 2, '.', ',') . " %");
                $grouped[] = [
                    "cooling_listing_complex#1" => $complexName,
                    "cooling_listing_name#1" => $coolerName,
                    "cooling_listing_type#1" => $coolerType,
                    "cooling_listing_base#1" => $coolerBase,
                    "cooling_listing_reg#1" => $coolerReg,
                    "cooling_listing_combined#1" => $coolerComb
                ];
            }
        }
        usort($grouped, function ($a, $b) {
            return strcasecmp($a['cooler_listing_complex#1'], $b['cooler_listing_complex#1']);
        });
        return $grouped;
    }

    public static function createCoolingListingSection(array $data, TemplateProcessor $templateProcessor, int &$sectionIndex, string $companyName)
    {
        if (empty($data)) {
            $templateProcessor->setValue("subheading_building_cooling", "");
            $templateProcessor->setValue("cooling_intro", "");
            $templateProcessor->setValue("cooling_subtext", "");
            $templateProcessor->cloneBlock("block_cooling", 0, true, true);
        } else {
            $subheading_text = self::xmlEscape("7." . $sectionIndex . ". Komforthűtési rendszerek");
            $cooling_intro = self::xmlEscape("A(z) " . $companyName . " az alábbi komforthűtési rendszerekkel rendelkezik:");
            $cooling_subtext = self::xmlEscape("A százalékos érték kialakításánál az alábbi szempontok kerültek figyelembevételre: alkalmazott hűtőközeg GWP értéke, berendezés működési módja (időjáráshoz alkalmazkodik vagy sem), berendezés műszaki állapota és az üzemelés körülményei.");

            $templateProcessor->setValue("subheading_building_cooling", $subheading_text);
            $templateProcessor->setValue("cooling_intro", $cooling_intro);
            $templateProcessor->setValue("cooling_subtext", $cooling_subtext);

            $templateProcessor->cloneBlock("block_cooling", 1, true, true);
            $templateProcessor->cloneRowAndSetValues("cooling_listing_row#1", $data);
            $sectionIndex++;
        }
    }

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
    public static function buildHVACListingRows(array $data, array &$improveable_list)
    {
        /*
        grouped = [
            "hvac_listing_complex#1" => complex,
            "hvac_listing_name#1" => name,
            "hvac_listing_sfp#1" => sfp,
            "hvac_listing_sfp_points#1" => sfp_points,
            "hvac_listing_heat#1" => heat,
            "hvac_listing_heat_points#1" => heat_points,
            "hvac_listing_insulation#1" => insulation_points 
        ]
        */
        $grouped = [];
        foreach ($data as $rowData) {
            $details = json_decode($rowData['json'], true);
            $complexName = self::xmlEscape($rowData['complex_name']);
            $systemName = self::xmlEscape($rowData['name']);
            $sfpRaw = $rowData['sfp'] ?? 0;

            $calculated = self::calculateVentilationGoodness($details, (float) $sfpRaw);

            $avgGoodness = ($calculated["sfp_goodness"] + $calculated['retriever_goodness'] + $calculated['insulation_goodness']) / 3;
            if ($avgGoodness < 75) {
                if (!isset($improveable_list['hvac'])) {
                    $improveable_list['hvac'] = [];
                }
                $improveable_list['hvac'][] = $systemName;
            }

            $sfp = self::xmlEscape(number_format($sfpRaw, 2, '.', ',') . " W/m3/s");
            $sfp_points = self::xmlEscape($calculated['sfp_goodness'] . "%");
            $retriever = self::xmlEscape($details['retriever']);
            $retriever_points = self::xmlEscape($calculated['retriever_goodness'] . "%");
            $insulation_points = self::xmlEscape($calculated['insulation_goodness'] . "%");
            $grouped[] = [
                "hvac_listing_complex#1" => $complexName,
                "hvac_listing_name#1" => $systemName,
                "hvac_listing_sfp#1" => $sfp,
                "hvac_listing_sfp_points#1" => $sfp_points,
                "hvac_listing_heat#1" => $retriever,
                "hvac_listing_heat_points#1" => $retriever_points,
                "hvac_listing_insulation#1" => $insulation_points
            ];
        }
        usort($grouped, function ($a, $b) {
            return strcasecmp($a['hvac_listing_complex#1'], $b['hvac_listing_complex#1']);
        });
        return $grouped;
    }

    public static function createHVACListingSection(array $data, TemplateProcessor $templateProcessor, int &$sectionIndex, string $companyName)
    {
        if (empty($data)) {
            $templateProcessor->setValue("subheading_building_hvac", "");
            $templateProcessor->setValue("hvac_intro", "");
            $templateProcessor->setValue("hvac_subtext", "");
            $templateProcessor->cloneBlock("block_hvac", 0, true, true);
        } else {
            $subheading_text = self::xmlEscape("7." . $sectionIndex . ". Komforthűtési rendszerek");
            $hvac_intro = self::xmlEscape("A(z) " . $companyName . " az alábbi légtechnikai rendszerekkel rendelkezik:");
            $hvac_subtext = self::xmlEscape("Értelmezés: az SFP érték, hővisszanyerési hatékonyság és a szigeteltségi állapot 90% érték alatt fejlesztendő.");

            $templateProcessor->setValue("subheading_building_hvac", $subheading_text);
            $templateProcessor->setValue("hvac_intro", $hvac_intro);
            $templateProcessor->setValue("hvac_subtext", $hvac_subtext);

            $templateProcessor->cloneBlock("block_hvac", 1, true, true);
            $templateProcessor->cloneRowAndSetValues("hvac_listing_row#1", $data);
            $sectionIndex++;
        }
    }

    public static function buildVehicleListingRows(array $data, array &$improveable_list)
    {
        /*
        $grouped = [
            "vehicles_listing_name" => name,
            "vehicles_listing_complex" => complex,
            "vehicles_listing_qf" => qf,
            "vehicles_listing_status" => status
        ]
        */
        $grouped = [];
        foreach ($data as $rowData) {
            $vehicleName = self::xmlEscape($rowData['vehicle_name']);
            $complexName = self::xmlEscape($rowData['complex_name']);
            $calculated = self::calculateVehicleQf($rowData);
            $qf = self::xmlEscape(number_format($calculated['qf'], 2, '.', ',') . $calculated['unit']);
            $status = self::xmlEscape($calculated['status']);
            if ($calculated['status'] === "Fejlesztendő") {
                if (!isset($improveable_list['vehicle'])) {
                    $improveable_list['vehicle'] = [];
                }
                $improveable_list['vehicle'][] = $vehicleName;
            }
            $grouped[] = [
                "vehicles_listing_name" => $vehicleName,
                "vehicles_listing_complex" => $complexName,
                "vehicles_listing_qf" => $qf,
                "vehicles_listing_status" => $status
            ];
        }
        usort($grouped, function ($a, $b) {
            return strcasecmp($a['vehicles_listing_complex'], $b['vehicles_listing_complex']);
        });
        return $grouped;
    }

    public static function createVehicleListingSection(array $data, TemplateProcessor $templateProcessor)
    {
        $templateProcessor->cloneRowAndSetValues("vehicles_listing_row", $data);
    }

    public static function getStandingIdsOfTechnology(array $data)
    {
        $standingIds = [];
        foreach ($data as $rowData) {
            $details = json_decode($rowData['json'], true);
            if (!is_array($details)) {
                continue;
            }
            foreach ($details['machines'] as $machine) {
                $standingIds[] = $machine['standing'];
            }
        }
        return $standingIds;
    }

    public static function buildTechnologyListingRows(array $data, array &$improveable_list, array $standingIdToName)
    {
        /*
        grouped = [
            "COMPRESSED" => [
                ["compressed_air_name#1" => name,
                "compressed_air_complex#1" => complex,
                "compressed_air_pressure#1" => pressure,
                "compressor_data" => [
                        ["compressor_row_title#1" => "Kompresszor típusa", "compressed_air_compressor_data#1" => type],
                        ["compressor_row_title#1" => "Mennyisége (db)", "compressed_air_compressor_data#1" => amount],
                        ["compressor_row_title#1" => "Névleges teljesítmény (kW/db)", "compressed_air_compressor_data#1" => nominal],
                        ["compressor_row_title#1" => "Működési mód", "compressed_air_compressor_data#1" => mode],
                        ["compressor_row_title#1" => "Hozzá tartozó almérés", "compressed_air_compressor_data#1" => standingId],
                ],
                "compressed_air_pressure_reduction#1" => canPressureReduc,
                "compressed_air_optimalisation#1" => canOptimise,
                "compressed_air_retriever#1" => haveRetrieve,
                "compressed_air_status#1" => status]
            ],
            "STEAM" => [
                "steam_name#1" => name,
                "steam_complex#1" => complex,
                "steam_pressure#1" => pressure,
                "steam_machine_data" => [
                        ["steam_row_title#1" => "Gőzfejlesztő típusa", "steam_machine_data#1" => type],
                        ["steam_row_title#1" => "Mennyisége (db)", "steam_machine_data#1" => amount],
                        ["steam_row_title#1" => "Névleges teljesítmény (kW/db)", "steam_machine_data#1" => nominal],
                        ["steam_row_title#1" => "Működési mód", "steam_machine_data#1" => mode],
                        ["steam_row_title#1" => "Hozzá tartozó almérés", "steam_machine_data#1" => standingId],
                    ]
                ],
                "steam_retriever#1" => haveRetrieve,
                "steam_status#1" => status,
            ],
            "COOLING" => [
                "tech_cooling_name#1" => name,
                "tech_cooler_data" => [
                        ["tech_cooling_row_title#1" => "Hűtőberendezés típusa", "tech_cooling_data#1" => type],
                        ["tech_cooling_row_title#1" => "Mennyisége (db)", "tech_cooling_data#1" => amount],
                        ["tech_cooling_row_title#1" => "Névleges teljesítmény (kW/db)", "tech_cooling_data#1" => nominal],
                        ["tech_cooling_row_title#1" => "Működési mód", "tech_cooling_data#1" => mode],
                        ["tech_cooling_row_title#1" => "Hozzá tartozó almérés", "tech_cooling_data#1" => standingId],
                ],
                "tech_cooling_retriever#1" => haveRetriever,
                "tech_cooling_status#1" => status

            ],
            "OTHER" => [
                "other_name#1" => name,
                "other_complex#1" => complex,
                "other_machine_data" => [
                        ["other_row_title#1" => "Technológiai berendezés típusa", "other_row_data#1" => type],
                        ["other_row_title#1" => "Mennyisége (db)", "other_row_data#1" => amount],
                        ["other_row_title#1" => "Névleges hőteljesítmény (kW/db)", "other_row_data#1" => nominal],
                        ["other_row_title#1" => "Hozzá tartozó almérés", "other_row_data#1" => standingId],
                ],
                "other_retriever#1" => retriever
            ],
            "standingIds" => [
                'id' => name
            ]
        ]
        */
        $grouped = ["COMPRESSED" => [], "STEAM" => [], "COOLING" => [], "OTHER" => [], "STANDINGS" => []];
        $compressed_block_index = 1;
        $steam_block_index = 1;
        $tech_cooling_index = 1;
        $other_block_index = 1;
        foreach ($data as $rowData) {
            $details = json_decode($rowData['json'], true);
            if (!is_array($details)) {
                continue;
            }
            if ($rowData["technology_type"] == "COMPRESSED_AIR") {
                $status = "Megfelelő";
                if ($details['pressureReduction'] || $details['systemOptimalization'] || ($details['wasteUse'] === "Nincs, van rá lehetőség")) {
                    $status = "Fejlesztendő";
                    if (!isset($improveable_list['technology'])) {
                        $improveable_list['technology'] = [];
                    }
                    $improveable_list['technology'][] = $rowData['name'];
                }
                $compressed_data = [
                    "compressed_air_name" => self::xmlEscape($rowData['name']),
                    "compressed_air_complex" => self::xmlEscape($rowData['complex_name']),
                    "compressed_air_pressure" => self::xmlEscape($details['pressure']),
                    "compressor_data" => [],
                    "compressed_air_pressure_reduction" => self::xmlEscape($details['pressureReduction'] ? "Igen" : "Nem"),
                    "compressed_air_optimalisation" => self::xmlEscape($details['systemOptimalization'] ? "Igen" : "Nem"),
                    "compressed_air_retriever" => self::xmlEscape($details['wasteUse']),
                    "compressed_air_status" => self::xmlEscape($status)
                ];
                foreach ($details['machines'] as $machine) {
                    $compressed_data['compressor_data'][] = ["compressor_row_title#{$compressed_block_index}" => self::xmlEscape("Kompresszor típusa"), "compressed_air_compressor_data#{$compressed_block_index}" => self::xmlEscape($machine['compressorType'])];
                    $compressed_data['compressor_data'][] = ["compressor_row_title#{$compressed_block_index}" => self::xmlEscape("Mennyisége (db)"), "compressed_air_compressor_data#{$compressed_block_index}" => self::xmlEscape($machine['amount'])];
                    $compressed_data['compressor_data'][] = ["compressor_row_title#{$compressed_block_index}" => self::xmlEscape("Névleges teljesítmény (kW/db)"), "compressed_air_compressor_data#{$compressed_block_index}" => self::xmlEscape($machine['nominalOutput'])];
                    $compressed_data['compressor_data'][] = ["compressor_row_title#{$compressed_block_index}" => self::xmlEscape("Működési mód"), "compressed_air_compressor_data#{$compressed_block_index}" => self::xmlEscape($machine["mode"])];
                    $compressed_data['compressor_data'][] = ["compressor_row_title#{$compressed_block_index}" => self::xmlEscape("Hozzá tartozó almérés"), "compressed_air_compressor_data#{$compressed_block_index}" => self::xmlEscape($standingIdToName[$machine['standing']])];
                    $grouped['STANDINGS'][] = $machine['standing'];
                }
                $compressed_block_index++;
                $grouped['COMPRESSED'][] = $compressed_data;
            }
            if ($rowData['technology_type'] == "STEAM") {
                $status = "Megfelelő";
                if ($details['smokeUse'] == "Nincs, van rá lehetőség") {
                    $status = "Fejlesztendő";
                    if (!isset($improveable_list['technology'])) {
                        $improveable_list['technology'] = [];
                    }
                    $improveable_list['technology'][] = $rowData['name'];
                }
                $steam_data = [
                    "steam_name" => self::xmlEscape($rowData['name']),
                    "steam_complex" => self::xmlEscape($rowData['complex_name']),
                    "steam_pressure" => self::xmlEscape($details['pressure']),
                    "steam_machine_data" => [],
                    "steam_retriever" => self::xmlEscape($details['smokeUse']),
                    "steam_status" => self::xmlEscape($status),
                ];
                foreach ($details['machines'] as $machine) {
                    $steam_data['steam_machine_data'][] = ["steam_row_title#{$steam_block_index}" => self::xmlEscape("Gőzfejlesztő típusa"), "steam_machine_data#{$steam_block_index}" => self::xmlEscape($machine['type'])];
                    $steam_data['steam_machine_data'][] = ["steam_row_title#{$steam_block_index}" => self::xmlEscape("Mennyisége (db)"), "steam_machine_data#{$steam_block_index}" => self::xmlEscape($machine['amount'])];
                    $steam_data['steam_machine_data'][] = ["steam_row_title#{$steam_block_index}" => self::xmlEscape("Névleges teljesítmény (kW/db)"), "steam_machine_data#{$steam_block_index}" => self::xmlEscape($machine['nominalOutput'])];
                    $steam_data['steam_machine_data'][] = ["steam_row_title#{$steam_block_index}" => self::xmlEscape("Működési mód"), "steam_machine_data#{$steam_block_index}" => self::xmlEscape($machine['mode'])];
                    $steam_data['steam_machine_data'][] = ["steam_row_title#{$steam_block_index}" => self::xmlEscape("Hozzá tartozó almérés"), "steam_machine_data#{$steam_block_index}" => self::xmlEscape($standingIdToName[$machine['standing']])];
                    $grouped['STANDINGS'][] = $machine['standing'];
                }
                $steam_block_index++;
                $grouped['STEAM'][] = $steam_data;
            }
            if ($rowData['technology_type'] == "COOLING") {
                $status = "Megfelelő";
                if ($details['wasteUse'] === "Nincs, van rá lehetőség") {
                    $status = "Fejlesztendő";
                    if (!isset($improveable_list['technology'])) {
                        $improveable_list['technology'] = [];
                    }
                    $improveable_list['technology'][] = $rowData['name'];
                }
                $cooling_data = [
                    "tech_cooling_name" => self::xmlEscape($rowData['name']),
                    "tech_cooler_data" => [],
                    "tech_cooling_retriever" => self::xmlEscape($details['wasteUse']),
                    "tech_cooling_status" => self::xmlEscape($status)
                ];
                foreach ($details['machines'] as $machine) {
                    $cooling_data['tech_cooler_data'][] = ["tech_cooling_row_title#{$tech_cooling_index}" => self::xmlEscape("Hűtőberendezés típusa"), "tech_cooling_row_data#{$tech_cooling_index}" => self::xmlEscape($machine['type'])];
                    $cooling_data['tech_cooler_data'][] = ["tech_cooling_row_title#{$tech_cooling_index}" => self::xmlEscape("Mennyisége (db)"), "tech_cooling_row_data#{$tech_cooling_index}" => self::xmlEscape($machine['amount'])];
                    $cooling_data['tech_cooler_data'][] = ["tech_cooling_row_title#{$tech_cooling_index}" => self::xmlEscape("Névleges teljesítmény (kW/db)"), "tech_cooling_row_data#{$tech_cooling_index}" => self::xmlEscape($machine['nominalOutput'])];
                    $cooling_data['tech_cooler_data'][] = ["tech_cooling_row_title#{$tech_cooling_index}" => self::xmlEscape("Működési mód"), "tech_cooling_row_data#{$tech_cooling_index}" => self::xmlEscape($machine['mode'])];
                    $cooling_data['tech_cooler_data'][] = ["tech_cooling_row_title#{$tech_cooling_index}" => self::xmlEscape("Hozzá tartozó almérés"), "tech_cooling_row_data#{$tech_cooling_index}" => self::xmlEscape($standingIdToName[$machine['standing']])];
                    $grouped['STANDINGS'][] = $machine['standing'];
                }
                $tech_cooling_index++;
                $grouped['COOLING'][] = $cooling_data;
            }
            if ($rowData['technology_type'] == "OTHER") {
                $status = "Megfelelő";
                if ($details['wasteUse'] == "Nincs, van rá lehetőség") {
                    $status = "Fejlesztendő";
                    if (!isset($improveable_list['technology'])) {
                        $improveable_list['technology'] = [];
                    }
                    $improveable_list['technology'][] = $rowData['name'];
                }
                $other_data = [
                    "other_name" => self::xmlEscape($rowData['name']),
                    "other_complex" => self::xmlEscape($rowData['complex_name']),
                    "other_machine_data" => [],
                    "other_retriever" => self::xmlEscape($details['wasteUse']),
                    "other_status" => self::xmlEscape($status)
                ];
                foreach ($details['machines'] as $machine) {
                    $other_data["other_machine_data"][] = ["other_row_title#{$other_block_index}" => self::xmlEscape("Technológiai berendezés típusa"), "other_row_data#{$other_block_index}" => self::xmlEscape($machine['type'])];
                    $other_data["other_machine_data"][] = ["other_row_title#{$other_block_index}" => self::xmlEscape("Mennyisége (db)"), "other_row_data#{$other_block_index}" => self::xmlEscape($machine['amount'])];
                    $other_data["other_machine_data"][] = ["other_row_title#{$other_block_index}" => self::xmlEscape("Névleges hőteljesítmény (kW/db)"), "other_row_data#{$other_block_index}" => self::xmlEscape($machine['nominalOutput'])];
                    $other_data["other_machine_data"][] = ["other_row_title#{$other_block_index}" => self::xmlEscape("Hozzá tartozó almérés"), "other_row_data#{$other_block_index}" => self::xmlEscape($standingIdToName[$machine['standing']])];
                    $grouped['STANDINGS'][] = $machine['standing'];
                }
                $other_block_index++;
                $grouped['OTHER'][] = $other_data;
            }
        }
        $grouped['STANDINGS'] = array_unique($grouped['STANDINGS']);
        return $grouped;
    }

    public static function createTechnologySection(array $data, TemplateProcessor $templateProcessor, string $company_name)
    {
        if (empty($data['COMPRESSED']) && empty($data['STEAM']) && empty($data['COOLING']) && empty($data['OTHER'])) {
            $templateProcessor->setValue("technology_title", "");
            $templateProcessor->setValue("technology_intro", "");

            $templateProcessor->setValue("compressed_air_sub", "");
            $templateProcessor->setValue("steam_sub", "");
            $templateProcessor->setValue("tech_cooling_sub", "");
            $templateProcessor->setValue("other_sub", "");

            $templateProcessor->cloneBlock("block_compressed_air", 0, true, true);
            $templateProcessor->cloneBlock("block_steam", 0, true, true);
            $templateProcessor->cloneBlock("block_tech_cooling", 0, true, true);
            $templateProcessor->cloneBlock("block_other", 0, true, true);

            $templateProcessor->setValue("technology_offset_index", 9);
            $templateProcessor->setValue("technology_offset_2_index", 10);
            $templateProcessor->setValue("technology_offset_3_index", 11);
        } else {
            $templateProcessor->setValue("technology_title", "9. Technológiai alrendszerek energetikai értékelése");
            $templateProcessor->setValue("technology_intro", self::xmlEscape("A" . $company_name . "-nál/nél az alábbi technológiai alrendszerek kerültek kialakításra:"));
            $templateProcessor->setValue("technology_offset_index", 10);
            $templateProcessor->setValue("technology_offset_2_index", 11);
            $templateProcessor->setValue("technology_offset_3_index", 12);


            $section = 'a)';
            if (!empty($data['COMPRESSED'])) {
                self::createCompressedSubsection($data['COMPRESSED'], $templateProcessor, $section);
            } else {
                $templateProcessor->setValue("compressed_air_sub", "");
                $templateProcessor->cloneBlock("block_compressed_air", 0, true, true);
            }
            if (!empty($data['STEAM'])) {
                self::createSteamSubsection($data['STEAM'], $templateProcessor, $section);
            } else {
                $templateProcessor->setValue("steam_sub", "");
                $templateProcessor->cloneBlock("block_steam", 0, true, true);
            }
            if (!empty($data['COOLING'])) {
                self::createTechCoolingSubsection($data['COOLING'], $templateProcessor, $section);
            } else {
                $templateProcessor->setValue("tech_cooling_sub", "");
                $templateProcessor->cloneBlock("block_tech_cooling", 0, true, true);
            }
            if (!empty($data['OTHER'])) {
                self::createOtherSubsection($data['OTHER'], $templateProcessor, $section);
            } else {
                $templateProcessor->setValue("other_sub", "");
                $templateProcessor->cloneBlock("block_other", 0, true, true);
            }
        }
    }

    public static function createCompressedSubsection(array $data, TemplateProcessor $templateProcessor, string &$section)
    {
        $templateProcessor->setValue("compressed_air_sub", self::xmlEscape($section . " Sűrített levegős hálózat"));
        $templateProcessor->cloneBlock("block_compressed_air", count($data), true, true);
        $blockCount = 1;
        foreach ($data as $system) {
            $templateProcessor->cloneRowAndSetValues("compressor_row_title#{$blockCount}", $system['compressor_data']);
            $templateProcessor->setValue("compressed_air_name#{$blockCount}", $system['compressed_air_name']);
            $templateProcessor->setValue("compressed_air_complex#{$blockCount}", $system['compressed_air_complex']);
            $templateProcessor->setValue("compressed_air_pressure#{$blockCount}", $system['compressed_air_pressure']);
            $templateProcessor->setValue("compressed_air_pressure_reduction#{$blockCount}", $system['compressed_air_pressure_reduction']);
            $templateProcessor->setValue("compressed_air_optimalisation#{$blockCount}", $system['compressed_air_optimalisation']);
            $templateProcessor->setValue("compressed_air_retriever#{$blockCount}", $system['compressed_air_retriever']);
            $templateProcessor->setValue("compressed_air_status#{$blockCount}", $system['compressed_air_status']);
            $blockCount++;
        }
        $section = "b)";
    }

    public static function createSteamSubsection(array $data, TemplateProcessor $templateProcessor, string &$section)
    {
        $templateProcessor->setValue("steam_sub", self::xmlEscape($section . " Gőzrendszer"));
        $templateProcessor->cloneBlock("block_steam", count($data), true, true);
        $blockCount = 1;
        foreach ($data as $system) {
            $templateProcessor->cloneRowAndSetValues("steam_row_title#{$blockCount}", $system['steam_machine_data']);
            $templateProcessor->setValue("steam_name#{$blockCount}", $system['steam_name']);
            $templateProcessor->setValue("steam_complex#{$blockCount}", $system['steam_complex']);
            $templateProcessor->setValue("steam_pressure#{$blockCount}", $system['steam_pressure']);
            $templateProcessor->setValue("steam_retriever#{$blockCount}", $system['steam_retriever']);
            $templateProcessor->setValue("steam_status#{$blockCount}", $system['steam_status']);
            $blockCount++;
        }
        $section = "c)";
    }

    public static function createTechCoolingSubsection(array $data, TemplateProcessor $templateProcessor, string &$section)
    {
        $templateProcessor->setValue("tech_cooling_sub", self::xmlEscape($section . " Technológiai hűtés"));
        $templateProcessor->cloneBlock("block_tech_cooling", count($data), true, true);
        $blockCount = 1;
        foreach ($data as $system) {
            $templateProcessor->cloneRowAndSetValues("tech_cooling_row_title#{$blockCount}", $system['tech_cooler_data']);
            $templateProcessor->setValue("tech_cooling_name#{$blockCount}", $system['tech_cooling_name']);
            $templateProcessor->setValue("tech_cooling_retriever#{$blockCount}", $system['tech_cooling_retriever']);
            $templateProcessor->setValue("tech_cooling_status#{$blockCount}", $system['tech_cooling_status']);
            $blockCount++;
        }
        $section = "d)";
    }

    public static function createOtherSubsection(array $data, TemplateProcessor $templateProcessor, string &$section)
    {
        $templateProcessor->setValue("other_sub", self::xmlEscape($section . " Egyéb technológiai hőhasználat"));
        $templateProcessor->cloneBlock("block_other", count($data), true, true);
        $blockCount = 1;
        foreach ($data as $system) {
            $templateProcessor->cloneRowAndSetValues("other_row_title#{$blockCount}", $system['other_machine_data']);
            $templateProcessor->setValue("other_name#{$blockCount}", $system['other_name']);
            $templateProcessor->setValue("other_complex#{$blockCount}", $system['other_complex']);
            $templateProcessor->setValue("other_retriever#{$blockCount}", $system['other_retriever']);
            $templateProcessor->setValue("other_status#{$blockCount}", $system['other_status']);
            $blockCount++;
        }
    }

    public static function buildProductListingRows(array $data, array $carrierRows)
    {
        /*
        grouped = [
            "rows" => [
                ["product_listing_name" => name, "product_listing_amount" => amount]
            ],
            "calculated" => calculated
        ]
        */
        $grouped = ["rows" => [], "primary" => null];
        $primaryProduct = null;
        foreach ($data as $rowData) {
            $products = json_decode($rowData['json'], true);
            if ($rowData['is_primary'] && $primaryProduct === null) {
                $primaryProduct = ['data' => $rowData, 'sum' => $products['sum']];
            }
            $productName = self::xmlEscape($rowData['product_name']);
            $sum = $products['sum'];
            $formattedSum = self::xmlEscape(number_format($sum, 2, ".", ",") . " " . $rowData['metric']);
            $grouped['rows'][] = ["product_listing_name" => $productName, "product_listing_amount" => $formattedSum];
        }
        $totalServicePower = 0.0;
        /*$grouped = [
            "<source>" => [
                "total" => 0,
                "metric" => '<metric>',
                "subs" => [
                    'BUILDING' => 0,
                    'CARRY' => 0,
                    'SERVICE' => 0
                    'SUM' => 0
                ]
            ]
        ];
        */
        foreach ($carrierRows as $source) {
            $totalServicePower += (float) $source['subs']['SERVICE'];
        }
        if (!$primaryProduct || $primaryProduct == null) {
            $primaryProduct = ['data' => $data[0], 'sum' => json_decode($data[0]['json'], true)['sum']];
        }
        if ($totalServicePower == 0.0) {
            $totalServicePower = 1.0;
        }
        $primaryName = self::xmlEscape($primaryProduct['data']['product_name'] . " (" . $primaryProduct['data']['metric'] . ")");
        $etm = self::xmlEscape(number_format($totalServicePower / (float) $primaryProduct['sum'], 2, ".", ",") . " kWh/" . $primaryProduct['data']['metric']);

        $primaryData = ["primary_name" => $primaryName, 'etm' => $etm];
        $grouped['primary'] = $primaryData;
        usort($grouped['rows'], function ($a, $b) {
            return strcasecmp($a['product_listing_name'], $b['product_listing_name']);
        });
        return $grouped;

    }

    public static function createProductListingSection(array $data, TemplateProcessor $templateProcessor)
    {
        $templateProcessor->cloneRowAndSetValues("product_listing_row", $data['rows']);
        $templateProcessor->setValue('product_name', $data['primary']['primary_name']);
        $templateProcessor->setValue('ETM', $data['primary']['etm']);
    }

    public static function createImproveableListingSection(array $data, TemplateProcessor $templateProcessor)
    {
        /*
        $improveable_list = [
                'building' => [],
                'heaters' => [],
                'hmv' => [],
                'coolers' => [],
                'hvac' => [],
                'vehicle' => [],
                'technology' => []
            ];*/
        $buildingMachines = array_merge($data['heaters'], $data['hmv']);
        $heatersCoolers = array_merge($data['heaters'], $data['coolers'], $data['hvac']);
        $suggestionA = self::xmlEscape(implode(',', $data['building']));
        $suggestionB = self::xmlEscape(implode(',', $buildingMachines));
        $suggestionC = self::xmlEscape(implode(',', $data['technology']));
        $suggestionD = self::xmlEscape(implode(',', $data['vehicle']));
        $suggestionE = self::xmlEscape(implode(',', $heatersCoolers));

        $templateProcessor->setValue("suggestion_a", $suggestionA);
        $templateProcessor->setValue("suggestion_b", $suggestionB);
        $templateProcessor->setValue("suggestion_c", $suggestionC);
        $templateProcessor->setValue("suggestion_d", $suggestionD);
        $templateProcessor->setValue("suggestion_e", $suggestionE);

    }
    public static function buildHMVTable(PhpOffice\PhpWord\Element\Table &$table, array &$heating_systems, &$improveable_list): void
    {
        $xmlEscape = function ($val) {
            return htmlspecialchars($val ?? '', ENT_XML1, 'UTF-8');
        };

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
        $dataParagraphStyleCenter = [
            'alignment' => 'center',
            'spaceBefore' => 40,
            'spaceAfter' => 40
        ];

        foreach ($heating_systems as $h) {
            $heaters = json_decode($h['emitters'], true);
            if (!is_array($heaters)) {
                continue;
            }

            $complexNameRaw = $h['complex_name'] ?? '';

            $hmvHeaters = array_filter($heaters, function ($emitter) {
                return isset($emitter['type']) && $emitter['type'] === 'HMV';
            });

            if (empty($hmvHeaters)) {
                continue;
            }

            $rowIndex = 0;
            foreach ($hmvHeaters as $emitter) {
                $table->addRow(null, ['cantSplit' => true]);

                if ($rowIndex === 0) {
                    $complexCellStyle = array_merge($dataCellStyle, ['vMerge' => 'restart']);
                    $complexCell = $table->addCell($colWidths['complex'], $complexCellStyle);
                    $complexCell->addText($xmlEscape($complexNameRaw), null, $dataParagraphStyleCenter);
                } else {
                    $complexCellStyle = array_merge($dataCellStyle, ['vMerge' => 'continue']);
                    $complexCell = $table->addCell($colWidths['complex'], $complexCellStyle);
                }

                $emitterNameRaw = $emitter['name'] ?? '';
                $heaterNameCell = $table->addCell($colWidths['name'], $dataCellStyle);
                $heaterNameCell->addText($xmlEscape($emitterNameRaw), null, $dataParagraphStyleCenter);

                $hmvPoints = self::HMV_REGULATION_VALUES[$emitter['hmvRegulation'] ?? ''] ?? 0;
                $heaterTypeCell = $table->addCell($colWidths['points'], $dataCellStyle);
                $heaterTypeCell->addText($xmlEscape($hmvPoints . "%"), null, $dataParagraphStyleCenter);

                $status = $hmvPoints === 100 ? "Megfelelő" : "Fejlesztendő";
                $etc = "Cirkuláció és szabályozás optimalizálása, ahol a pontszám alacsony";
                if ($hmvPoints < 100) {
                    if (!isset($improveable_list['hmv'])) {
                        $improveable_list['hmv'] = [];
                    }
                    $improveable_list['hmv'][] = $complexNameRaw;
                }

                $heaterPointCell = $table->addCell($colWidths['status'], $dataCellStyle);
                $heaterPointCell->addText($xmlEscape($status), null, $dataParagraphStyleCenter);

                $heaterStatusCell = $table->addCell($colWidths['etc'], $dataCellStyle);
                $heaterStatusCell->addText($xmlEscape($etc), null, $dataParagraphStyleCenter);

                $rowIndex++;
            }
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

    public static function calculateVehicleQf(array $vehicle): array
    {
        $category = mb_strtolower(trim($vehicle['vehicle_category'] ?? ''));
        $usageMetric = mb_strtolower(trim($vehicle['usage_metric'] ?? $vehicle['usageMetric'] ?? ''));

        if (str_contains($category, 'anyagmozgat') || str_contains($category, 'targonc')) {
            $rawConsumption = $vehicle['consumption'] ?? null;
            $totalConsumption = self::calculateTotalConsumption($rawConsumption);

            $fuel = $vehicle['fuel'] ?? 'Elektromos áram';
            $unit = $vehicle['measurement'] ?? 'KWH';
            $totalKwh = self::convertFuelToKwh($totalConsumption, $fuel, $unit);

            $usage1 = (float) ($vehicle['usage_value'] ?? $vehicle['usageValue'] ?? 0);
            $usage2 = (float) ($vehicle['usage_value2'] ?? $vehicle['usageValue2'] ?? 0);
            $capacity = (float) ($vehicle['capacity'] ?? 0);

            if ($usageMetric === 'tkm') {
                $weight = $usage2 > 0 ? $usage2 : ($capacity > 0 ? $capacity : 1.0);
                $divisor = $usage1 * $weight;
                $unitLabel = 'kWh/tkm';
                $threshold = 0.247;
            } elseif ($usageMetric === 'km') {
                $divisor = $usage1;
                $unitLabel = 'kWh/km';
                $threshold = 0.247;
            } else {
                $divisor = $usage1;
                $unitLabel = 'kWh/h';
                $threshold = self::getForkliftThresholdKwhPerHour($capacity, $fuel);
            }

            if ($fuel == "Elektromos áram" && $vehicle['measurement_type'] == "VIRTUAL") {
                return [
                    'qf' => $threshold,
                    'unit' => $unitLabel,
                    'status' => "Megfelelő"
                ];
            } else {
                $qf = ($divisor > 0) ? ($totalKwh / $divisor) : 0;
                $status = ($threshold > 0 && $qf > $threshold) ? 'Fejlesztendő' : 'Megfelelő';

                return [
                    'qf' => $qf,
                    'unit' => $unitLabel,
                    'status' => $status
                ];
            }
        } else if (str_contains($category, 'áruszállít') || str_contains($category, 'aruszallit') || str_contains($category, 'teher')) {
            $rawConsumption = $vehicle['consumption'] ?? null;
            $totalConsumption = self::calculateTotalConsumption($rawConsumption);

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
            $totalConsumption = self::calculateTotalConsumption($rawConsumption);

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
            //'1.5'
            'A' => ['dízel' => 2.19, 'lpg' => 2.30, 'elektr' => 4.47],
            //'2.0'
            'B' => ['dízel' => 2.57, 'lpg' => 2.70, 'elektr' => 4.81],
            //2.5
            'C' => ['dízel' => 2.97, 'lpg' => 3.26, 'elektr' => 7.16],
            //'3.0'
            'D' => ['dízel' => 3.23, 'lpg' => 3.55, 'elektr' => 7.56],
            //'3.5'
            'E' => ['dízel' => 3.90, 'lpg' => 3.79, 'elektr' => 8.33],
            //'4.0'
            'F' => ['dízel' => 4.44, 'lpg' => 4.29, 'elektr' => 8.70],
            //'4.5'
            'G' => ['dízel' => 4.91, 'lpg' => 4.63, 'elektr' => 9.30],
            //'5.0'
            'H' => ['dízel' => 5.95, 'lpg' => 4.94, 'elektr' => 10.38],
            //'5.5'
            'I' => ['dízel' => 6.97, 'lpg' => 5.31, 'elektr' => 12.80],
            //'6.0'
            'J' => ['dízel' => 7.48, 'lpg' => 7.09, 'elektr' => 12.20],
            //'6.5'
            'K' => ['dízel' => 0.00, 'lpg' => 0.00, 'elektr' => 12.20],
            //'7.0'
            'L' => ['dízel' => 8.80, 'lpg' => 7.83, 'elektr' => 13.40],
            //'7.5'
            'M' => ['dízel' => 8.90, 'lpg' => 0.00, 'elektr' => 0.00],
            //'8.0'
            'N' => ['dízel' => 9.72, 'lpg' => 9.93, 'elektr' => 0.00],
            //'9.0'
            'O' => ['dízel' => 11.11, 'lpg' => 10.70, 'elektr' => 0.00],
            //'10.0'
            'P' => ['dízel' => 13.00, 'lpg' => 0.00, 'elektr' => 0.00],
        ];

        if ($capacity <= 0) {
            return 0.0;
        }

        $row = match (true) {
            $capacity >= 1.5 && $capacity < 2.0 => $matrix['A'],
            $capacity < 2.5 => $matrix['B'],
            $capacity < 3 => $matrix['C'],
            $capacity < 3.5 => $matrix['D'],
            $capacity < 4.0 => $matrix['E'],
            $capacity < 4.5 => $matrix['F'],
            $capacity < 5.0 => $matrix['G'],
            $capacity < 5.5 => $matrix['H'],
            $capacity < 6.0 => $matrix['I'],
            $capacity < 6.5 => $matrix['J'],
            $capacity < 7.0 => $matrix['K'],
            $capacity < 7.5 => $matrix['L'],
            $capacity < 8.0 => $matrix['M'],
            $capacity < 9.0 => $matrix['N'],
            $capacity < 10.0 => $matrix['O'],
            default => $matrix['P']
        };

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
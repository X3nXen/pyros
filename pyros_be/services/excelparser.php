<?php
use PhpOffice\PhpSpreadsheet\IOFactory;

function parseStandingsExcel(string $filePath, string $startDateStr, string $endDateStr): array
{
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();

    $start = new DateTime($startDateStr);
    $end = new DateTime($endDateStr);

    $monthKeys = [
        1 => 'jan',
        2 => 'feb',
        3 => 'mar',
        4 => 'apr',
        5 => 'may',
        6 => 'jun',
        7 => 'jul',
        8 => 'aug',
        9 => 'sep',
        10 => 'oct',
        11 => 'nov',
        12 => 'dec'
    ];

    $result = [];
    $currentRow = 3;

    $current = (clone $start)->modify('first day of this month');
    $endMonth = (clone $end)->modify('first day of this month');

    while ($current <= $endMonth) {
        $year = $current->format('Y');
        $monthNum = (int) $current->format('n');
        $monthKey = $monthKeys[$monthNum];

        if (!isset($result[$year])) {
            $result[$year] = array_fill_keys(array_values($monthKeys), null);
        }

        $rawVal = $sheet->getCell('C' . $currentRow)->getCalculatedValue();

        $result[$year][$monthKey] = ($rawVal !== null && $rawVal !== '') ? (float) $rawVal : null;

        $currentRow++;
        $current->modify('+1 month');
    }

    return $result;
}

function parseProductExcel(string $filePath): array
{
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();

    $rawProductName = $sheet->getCell('C2')->getValue();
    $productName = (is_string($rawProductName) || is_numeric($rawProductName)) && trim((string) $rawProductName) !== ''
        ? trim((string) $rawProductName)
        : 'Termék';

    $productionData = [];
    $totalSum = 0.0;
    $currentRow = 3;

    while (true) {
        $dateVal = $sheet->getCell('A' . $currentRow)->getFormattedValue();

        if ($dateVal === null || trim((string) $dateVal) === '') {
            break;
        }

        $dateKey = trim((string) $dateVal);
        $rawVal = $sheet->getCell('B' . $currentRow)->getCalculatedValue();

        $numericVal = ($rawVal !== null && $rawVal !== '') ? (float) $rawVal : 0.0;

        $productionData[$dateKey] = $numericVal;
        $totalSum += $numericVal;

        $currentRow++;
    }

    $productionData['sum'] = $totalSum;

    return [
        'product_name' => $productName,
        'json' => $productionData
    ];
}
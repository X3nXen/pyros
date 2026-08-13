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

    // 1. C2 cella kiolvasása terméknévként
    $rawProductName = $sheet->getCell('C2')->getValue();
    $productName = (is_string($rawProductName) || is_numeric($rawProductName)) && trim((string) $rawProductName) !== ''
        ? trim((string) $rawProductName)
        : 'Termék';

    $productionData = [];
    $currentRow = 3; // B3 és C3 celláktól indul a kiolvasás

    // B3-tól lefelé addig olvasunk, amíg találunk évet a B oszlopban
    while (true) {
        $yearVal = $sheet->getCell('B' . $currentRow)->getValue();

        // Ha a B oszlop sorában nincs több adat (évet jelölő érték), megállunk
        if ($yearVal === null || trim((string) $yearVal) === '') {
            break;
        }

        $year = trim((string) $yearVal);
        $rawVal = $sheet->getCell('C' . $currentRow)->getCalculatedValue();

        // Ha van megadott mennyiség, float-ként eltároljuk
        $productionData[$year] = ($rawVal !== null && $rawVal !== '') ? (float) $rawVal : null;

        $currentRow++;
    }

    return [
        'product_name' => $productName,
        'json' => $productionData
    ];
}
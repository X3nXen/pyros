<?php


class EnergyPriceService
{
    public static function getMarketPrices(): array
    {
        $eurHuf = self::getEurHufRate();
        $ttfGas = self::getTtfGasPrice();
        $electricity = self::getEuropeanElectricityPrice();

        return [
            'eur_huf_rate' => $eurHuf,
            'natural_gas_price' => $ttfGas,
            'electric_energy_price' => $electricity,
        ];
    }

    private static function getEurHufRate(): float
    {
        $url = 'https://open.er-api.com/v6/latest/EUR';
        $response = self::makeHttpRequest($url);

        if ($response && isset($response['rates']['HUF'])) {
            return (float) $response['rates']['HUF'];
        }

        return 400.00;
    }


    private static function getTtfGasPrice(): float
    {
        $url = 'https://query1.finance.yahoo.com/v8/finance/chart/TTF=F?interval=1d&range=1d';
        $response = self::makeHttpRequest($url);

        if (isset($response['chart']['result'][0]['meta']['regularMarketPrice'])) {
            return (float) $response['chart']['result'][0]['meta']['regularMarketPrice'];
        }

        return 35.00;
    }

    private static function getEuropeanElectricityPrice(): float
    {
        $url = 'https://api.energy-charts.info/price?bzn=DE-LU';
        $response = self::makeHttpRequest($url);

        if (isset($response['price']) && is_array($response['price'])) {
            $prices = array_filter($response['price'], fn($v) => $v !== null);
            if (!empty($prices)) {
                return (float) end($prices);
            }
        }

        return 80.00;
    }

    private static function makeHttpRequest(string $url): ?array
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'header' => "User-Agent: Mozilla/5.0\r\n"
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];

        $context = stream_context_create($options);
        $output = @file_get_contents($url, false, $context);

        if ($output !== false) {
            return json_decode($output, true);
        }

        return null;
    }
}
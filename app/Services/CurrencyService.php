<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    protected $table = 'money_currency';
    protected $exchange_rates = [];

    public function __construct()
    {
        $this->loadRates();
    }

    protected function loadRates()
    {
        try {
            $rates = DB::table($this->table)->get();
            foreach ($rates as $rate) {
                $this->exchange_rates[$rate->currency] = $rate->rate;
            }
        } catch (\Exception $e) {
            // Default rates from your existing project
            $this->exchange_rates = [
                'USD' => '0.9748', 'JPY' => '141.01', 'GBP' => '0.88300', 'EUR' => '1.02',
                'AUD' => '1.5076', 'CAD' => '1.3401', 'NZD' => '1.7177', 'SGD' => '1.4001', 'SEK' => '10.8993'
            ];
        }
    }

    public function convert($amount = 1, $from = "USD", $to = "EUR", $decimals = 2)
    {
        if (!isset($this->exchange_rates[$from]) || !isset($this->exchange_rates[$to])) {
            return number_format($amount, $decimals, '.', '');
        }

        $result = ($amount / $this->exchange_rates[$from]) * $this->exchange_rates[$to];
        return number_format($result, $decimals, '.', '');
    }

    public function getSymbol($currencyId)
    {
        $symbols = [
            2 => '&pound;', // GBP
            3 => '&#36;',   // USD
            4 => '&#36;',   // CAD
            5 => '&#36;',   // AUD
            6 => '&#36;',   // NZD
            7 => '',        // SEK
            8 => '&#36;',   // SGD
            9 => '&yen;',   // JPY
        ];

        return $symbols[$currencyId] ?? '&euro;';
    }

    public function getCurrencyCode($currencyId)
    {
        $codes = [
            2 => 'GBP', 3 => 'USD', 4 => 'CAD', 5 => 'AUD',
            6 => 'NZD', 7 => 'SEK', 8 => 'SGD', 9 => 'JPY'
        ];
        return $codes[$currencyId] ?? 'EUR';
    }
}

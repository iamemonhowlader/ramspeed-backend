<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ShippingService
{
    /**
     * Calculate Akis Express shipping for Cyprus orders.
     * Replicated from shipping_types.php lines 462-493
     */
    public function calculateAkis($weight, $isDoorToDoor = false)
    {
        $akis = DB::table('akis')->where('id', 1)->first();
        $shPrice = $akis->price;

        if ($weight > $akis->kg) {
            $roundWeight = ceil($weight);
            $initialWeight = ceil($akis->kg);
            $extraWeight = $roundWeight - $initialWeight;
            
            $addedPrice = DB::table('akis_added_price')->where('id', 1)->value('price');
            $shPrice += ($extraWeight * $addedPrice);
        }

        if ($isDoorToDoor) {
            $shPrice += 1.68; // Pay on delivery fee
        }

        // Add VAT
        $vat = DB::table('vat')->where('id', 2)->value('vat');
        $vatMultiplier = 1 + ($vat / 100);
        
        return round($shPrice * $vatMultiplier, 2);
    }

    /**
     * Fetch real-time rates from SendFromChina (SFC) XML API.
     * Replicated from legacy shipping logic.
     */
    public function calculateSFC($weight, $countryCode, $isWholesaler = false)
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>
        <getrates>
            <weight>{$weight}</weight>
            <country>{$countryCode}</country>
            <type>1</type>
        </getrates>";

        try {
            $response = Http::withHeaders(['Content-Type' => 'text/xml'])
                ->send('POST', 'http://www.sendfromchina.com/api/getrates', [
                    'body' => $xml
                ]);

            if ($response->successful()) {
                // Parse XML and return rate
                $data = simplexml_load_string($response->body());
                return (float) $data->rate;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}

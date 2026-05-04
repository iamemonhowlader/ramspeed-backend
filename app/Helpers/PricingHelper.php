<?php

namespace App\Helpers;

use App\Models\Vat;

class PricingHelper
{
    /**
     * Find the original value before a percentage was added.
     * Replicates amountBeforePercentageAdded() from your existing project.
     */
    public static function amountBeforePercentageAdded($percent, $amount, $precision = 2)
    {
        $dec = 1;
        if ($percent < 900) $dec = 9;
        if ($percent < 800) $dec = 8;
        if ($percent < 700) $dec = 7;
        if ($percent < 600) $dec = 6;
        if ($percent < 500) $dec = 5;
        if ($percent < 400) $dec = 4;
        if ($percent < 300) $dec = 3;
        if ($percent < 200) $dec = 2;
        if ($percent < 100) $dec = 1;

        if ($percent > 99) {
            $percent = substr($percent, 1, 2);
        }

        $divisor = (float) ($dec . '.' . $percent);
        return round($amount / $divisor, $precision);
    }

    /**
     * Replicates getWholesalerPrice() logic.
     */
    public static function getWholesalerPrice($price, $profitPercentage, $vatRate = null)
    {
        $profit = ($price * $profitPercentage) / 100;
        $total = $price + $profit;

        if ($vatRate === null) {
            // Replicate default behavior of getting vat ID 2
            $vatData = Vat::find(2);
            $vatRate = $vatData ? (1 + ($vatData->vat / 100)) : 1.19; // Default 19% if not found
        }

        return $total * $vatRate;
    }

    public static function addPercentage($amount, $percent, $precision = 2)
    {
        $res = ($percent / 100) * $amount;
        return round($amount + $res, $precision);
    }
}

<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Delivery;

class OrderService
{
    /**
     * Calculate the final order amount.
     *
     * @param  array  $data  Contains 'subtotal', 'weight', 'destination_latitude', 'destination_longitude', and optional 'products'
     * @return array  Breakdown of the calculation.
     */
    public function calculateFinalAmount(array $data)
    {
        $subtotal = $data['subtotal'];
        $taxRate = $this->calculateTax($subtotal);
        $discountRate = $this->calculateDiscount($data, $subtotal);

        $deliveryData = $this->calculateDeliveryFee(
            $data['destination_latitude'],
            $data['destination_longitude'],
            $data['weight']
        );
        $deliveryFee = $deliveryData['fee'];
        $deliveryDistance = $deliveryData['distance'];

        $taxAmount = $subtotal * $taxRate;
        $discountAmount = $subtotal * $discountRate;
        $finalAmount = ($subtotal + $taxAmount + $deliveryFee) - $discountAmount;

        return [
            'subtotal'           => $subtotal,
            'tax_rate'           => round($taxRate * 100, 2),
            'tax_amount'         => round($taxAmount, 2),
            'discount_rate'      => round($discountRate * 100, 2),
            'discount_amount'    => round($discountAmount, 2),
            'delivery_fee'       => round($deliveryFee, 2),
            'delivery_distance'  => round($deliveryDistance, 2),
            'final_amount'       => round($finalAmount, 2)
        ];
    }

    /**
     * Determine the tax rate based on the order subtotal.
     */
    protected function calculateTax($subtotal)
    {
        if ($subtotal >= 100 && $subtotal < 500) {
            return 0.05;
        } elseif ($subtotal >= 500 && $subtotal < 1000) {
            return 0.08;
        } elseif ($subtotal >= 1000) {
            return 0.12;
        }
        return 0;
    }

    /**
     * Calculate discount rate by checking discount rules from the database.
     */
    protected function calculateDiscount(array $data, $subtotal): float
    {
        $discount = 0;
        $discountRules = Discount::all();

        foreach ($discountRules as $rule) {
            if ($rule->type === 'product_combo' && !empty($data['products'])) {
                $requiredProducts = json_decode($rule->required_product);
                if (count(array_intersect($requiredProducts, $data['products'])) > 0) {
                    $discount += $rule->rate;
                }
            }
            if ($rule->type === 'order_total' && $subtotal > $rule->threshold) {
                $discount += $rule->rate;
            }
        }
        return $discount;
    }


    /**
     * Calculate delivery fee based on distance from warehouse and weight.
     *
     * @param float $destLat Destination latitude.
     * @param float $destLng Destination longitude.
     * @param float $weight  Order weight.
     * @return array ['fee' => float, 'distance' => float] with fee and computed distance in kilometers.
     */
    protected function calculateDeliveryFee($destLat, $destLng, $weight)
    {
        // Retrieve the delivery fee rule (assuming a single rule exists).
        $rule = Delivery::first();
        if (!$rule) {
            return ['fee' => 0, 'distance' => 0];
        }

        // Calculate distance using the Haversine formula.
        $distance = $this->haversineDistance(
            $rule->warehouse_lat,
            $rule->warehouse_lng,
            $destLat,
            $destLng
        );

        // Fee = base fee + (distance * cost per km).
        $fee = $rule->base_fee + ($distance * $rule->cost_per_km);

        // Optionally, add a weight-based surcharge (e.g., $0.50 per kg above 5 kg).
        if ($weight > 5) {
            $fee += ($weight - 5) * 0.50;
        }
        return ['fee' => $fee, 'distance' => $distance];
    }

    /**
     * Calculate the distance between two coordinates using the Haversine formula.
     *
     * @return float Distance in kilometers.
     */
    protected function haversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

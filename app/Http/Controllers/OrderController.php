<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Calculate the final payable amount for an order.
     *
     * Expected JSON payload:
     * {
     *   "subtotal": 250.00,
     *   "weight": 10,
     *   "location": "urban",
     *   "products": ["Laptop", "Headphones"]
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateFinalAmount(Request $request)
    {
        /**
         * Calculate the final payable amount for an order.
         *
         * Expected JSON payload:
         * {
         *   "subtotal": 250.00,
         *   "weight": 10,
         *   "destination_latitude": 40.730610,
         *   "destination_longitude": -73.935242,
         *   "products": ["Laptop", "Headphones"]
         * }
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $validatedData = $request->validate([
                'subtotal'  => 'required|numeric|min:0',
                'weight'    => 'required|numeric|min:0',
                'destination_latitude' => 'required_with:destination_longitude|numeric',
                'destination_longitude' => 'required_with:destination_latitude|numeric',
                'products'  => 'sometimes|array'
            ]);

            // Create a unique cache key based on the input data.
            $cacheKey = 'order_calc_' . md5(json_encode($validatedData));

            // Retrieve result from cache if available, else calculate and store for 60 seconds.
            $result = Cache::remember($cacheKey, 60, function () use ($validatedData) {
                $orderService = new OrderService();
                return $orderService->calculateFinalAmount($validatedData);
            });

            // Log the calculation for auditing and debugging purposes.
            Log::info('Order calculation performed', $validatedData);

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Invalid input data', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error calculating order amount', ['exception' => $e]);
            return response()->json(['error' => 'An error occurred while calculating the order amount'], 500);
        }
    }
}

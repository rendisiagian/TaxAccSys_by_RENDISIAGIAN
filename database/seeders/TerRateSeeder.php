<?php

namespace Database\Seeders;

use App\Models\TerRate;
use Illuminate\Database\Seeder;

class TerRateSeeder extends Seeder
{
    public function run(): void
    {
        // Sample TER rates based on PP 58/2023
        // In a real app, this would contain the full 40+ brackets per category.
        
        $rates = [
            // Category A (TK/0, TK/1, K/0)
            ['category' => 'A', 'min' => 0, 'max' => 5400000, 'rate' => 0],
            ['category' => 'A', 'min' => 5400000, 'max' => 5650000, 'rate' => 0.25],
            ['category' => 'A', 'min' => 5650000, 'max' => 5950000, 'rate' => 0.5],
            ['category' => 'A', 'min' => 5950000, 'max' => 6300000, 'rate' => 0.75],
            ['category' => 'A', 'min' => 6300000, 'max' => 6750000, 'rate' => 1],
            ['category' => 'A', 'min' => 6750000, 'max' => 7500000, 'rate' => 1.25],
            ['category' => 'A', 'min' => 7500000, 'max' => 8550000, 'rate' => 1.5],
            ['category' => 'A', 'min' => 8550000, 'max' => 9650000, 'rate' => 1.75],
            ['category' => 'A', 'min' => 9650000, 'max' => 10050000, 'rate' => 2],
            ['category' => 'A', 'min' => 10050000, 'max' => 10350000, 'rate' => 2.25],
            ['category' => 'A', 'min' => 10350000, 'max' => 10700000, 'rate' => 2.5],
            ['category' => 'A', 'min' => 10700000, 'max' => 11050000, 'rate' => 3],
            // Catch all for A
            ['category' => 'A', 'min' => 11050000, 'max' => null, 'rate' => 4],

            // Category B (TK/2, TK/3, K/1, K/2)
            ['category' => 'B', 'min' => 0, 'max' => 6200000, 'rate' => 0],
            ['category' => 'B', 'min' => 6200000, 'max' => 6500000, 'rate' => 0.25],
            ['category' => 'B', 'min' => 6500000, 'max' => 6850000, 'rate' => 0.5],
            ['category' => 'B', 'min' => 6850000, 'max' => 7300000, 'rate' => 0.75],
            ['category' => 'B', 'min' => 7300000, 'max' => 9200000, 'rate' => 1],
            ['category' => 'B', 'min' => 9200000, 'max' => 10750000, 'rate' => 1.5],
            // Catch all for B
            ['category' => 'B', 'min' => 10750000, 'max' => null, 'rate' => 2],

            // Category C (K/3)
            ['category' => 'C', 'min' => 0, 'max' => 6600000, 'rate' => 0],
            ['category' => 'C', 'min' => 6600000, 'max' => 6950000, 'rate' => 0.25],
            ['category' => 'C', 'min' => 6950000, 'max' => 7350000, 'rate' => 0.5],
            ['category' => 'C', 'min' => 7350000, 'max' => 7800000, 'rate' => 0.75],
            ['category' => 'C', 'min' => 7800000, 'max' => 8850000, 'rate' => 1],
            // Catch all for C
            ['category' => 'C', 'min' => 8850000, 'max' => null, 'rate' => 1.5],
        ];

        foreach ($rates as $r) {
            TerRate::create([
                'category' => $r['category'],
                'min_bruto' => $r['min'],
                'max_bruto' => $r['max'],
                'rate_percentage' => $r['rate']
            ]);
        }
    }
}

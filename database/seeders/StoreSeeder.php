<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'name' => 'Aroma Palace - Grand Indonesia',
                'address' => 'Jl. M.H. Thamrin No.1, Jakarta Pusat',
                'phone' => '021-23580001',
                'lng' => 106.8207,
                'lat' => -6.1953,
            ],
            [
                'name' => 'Aroma Palace - Senayan City',
                'address' => 'Jl. Asia Afrika No.19, Jakarta Pusat',
                'phone' => '021-72781000',
                'lng' => 106.7974,
                'lat' => -6.2274,
            ],
            [
                'name' => 'Aroma Palace - Paris Van Java Bandung',
                'address' => 'Jl. Sukajadi No.131-139, Bandung',
                'phone' => '022-82063000',
                'lng' => 107.5959,
                'lat' => -6.8893,
            ],
        ];

        foreach ($stores as $item) {
            Store::create([
                'name' => $item['name'],
                'address' => $item['address'],
                'phone' => $item['phone'],
                // SetSRID 4326 dengan format POINT(Longitude Latitude)
                'location' => DB::raw("ST_SetSRID(ST_MakePoint({$item['lng']}, {$item['lat']}), 4326)"),
            ]);
        }
    }
}
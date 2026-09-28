<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StoreService
{
    /**
     * Ambil daftar toko (dengan pencarian radius PostGIS jika koordinat ada)
     */
    public function getStores(?float $lat = null, ?float $lng = null, int $radius = 50000, bool $pickupOnly = false, ?string $city = null): Collection
    {
        if ($lat && $lng) {
            $query = Store::nearby($lat, $lng, $radius);
        } else {
            $query = Store::withCoordinates()->orderBy('name', 'asc');
        }

        if ($pickupOnly) {
            $query->where('is_pickup_available', true);
        }

        if ($city) {
            $query->where('city', 'ilike', "%{$city}%");
        }

        return $query->get();
    }

    /**
     * Dapatkan daftar kota unik tempat gerai toko berada
     */
    public function getCities(): array
    {
        return Store::whereNotNull('city')->distinct()->pluck('city')->sort()->values()->toArray();
    }

    /**
     * Detail toko tertentu
     */
    public function getStoreDetail(int $id): Store
    {
        return Store::withCoordinates()->findOrFail($id);
    }

    /**
     * Admin: Simpan atau Update data toko
     */
    public function saveStore(array $data, ?int $id = null): Store
    {
        $lat = (float) ($data['latitude'] ?? $data['lat'] ?? 0);
        $lng = (float) ($data['longitude'] ?? $data['lng'] ?? 0);

        $payload = [
            'name' => $data['name'],
            'city' => $data['city'] ?? null,
            'address' => $data['address'],
            'phone' => $data['phone'] ?? null,
            'operating_hours' => $data['operating_hours'] ?? '10:00 - 22:00',
            'open_time' => $data['open_time'] ?? '10:00',
            'close_time' => $data['close_time'] ?? '22:00',
            'image_url' => $data['image_url'] ?? null,
            'is_pickup_available' => !empty($data['is_pickup_available']),
            'location' => DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)"),
        ];

        if ($id) {
            $store = Store::findOrFail($id);
            $store->update($payload);
            return $this->getStoreDetail($id);
        }

        $store = Store::create($payload);
        return $this->getStoreDetail($store->id);
    }
}


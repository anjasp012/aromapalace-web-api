<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'address',
        'phone',
        'operating_hours',
        'open_time',
        'close_time',
        'image_url',
        'is_pickup_available',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'is_pickup_available' => 'boolean',
        ];
    }

    /**
     * Scope query untuk mencari toko terdekat menggunakan PostGIS
     * $lng = longitude, $lat = latitude, $radiusInMeters = radius (default 50.000 meter / 50 km)
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, int $radiusInMeters = 50000): Builder
    {
        return $query
            ->select('id', 'name', 'city', 'address', 'phone', 'operating_hours', 'open_time', 'close_time', 'image_url', 'is_pickup_available')
            ->selectRaw('ST_Y(location::geometry) as latitude')
            ->selectRaw('ST_X(location::geometry) as longitude')
            ->selectRaw(
                'ROUND(ST_Distance(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography)) as distance_meters',
                [$lng, $lat]
            )
            ->whereRaw(
                'ST_DWithin(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)',
                [$lng, $lat, $radiusInMeters]
            )
            ->orderBy('distance_meters', 'asc');
    }

    /**
     * Scope untuk mengambil koordinat lat/lng untuk semua toko
     */
    public function scopeWithCoordinates(Builder $query): Builder
    {
        return $query
            ->select('id', 'name', 'city', 'address', 'phone', 'operating_hours', 'open_time', 'close_time', 'image_url', 'is_pickup_available')
            ->selectRaw('ST_Y(location::geometry) as latitude')
            ->selectRaw('ST_X(location::geometry) as longitude');
    }
}

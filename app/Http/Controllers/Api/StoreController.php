<?php

namespace App\Http\Controllers\Api;

use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends BaseApiController
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'radius' => 'nullable|numeric|min:1',
            'city' => 'nullable|string',
            'pickup_only' => 'nullable|boolean',
        ]);

        $lat = $request->query('lat') ? (float) $request->query('lat') : null;
        $lng = $request->query('lng') ? (float) $request->query('lng') : null;
        $radius = (int) ($request->query('radius', 50000));
        $city = $request->query('city');
        $pickupOnly = $request->boolean('pickup_only');

        $stores = $this->storeService->getStores($lat, $lng, $radius, $pickupOnly, $city);

        return $this->sendResponse($stores, 'Daftar lokasi toko Aroma Palace.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $store = $this->storeService->getStoreDetail($id);
            return $this->sendResponse($store, 'Detail informasi toko.');
        } catch (\Exception $e) {
            return $this->sendError('Toko tidak ditemukan.', [], 404);
        }
    }
}
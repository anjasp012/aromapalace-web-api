<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(
        protected StoreService $storeService,
        protected ProductService $productService
    ) {}

    public function index(Request $request): View
    {
        $lat = $request->query('lat') ? (float) $request->query('lat') : null;
        $lng = $request->query('lng') ? (float) $request->query('lng') : null;
        $city = $request->query('city');

        $allStores = $this->storeService->getStores(null, null, 50000, false, null);

        $storeId = $request->query('store');
        if ($storeId) {
            $matchingStore = $allStores->firstWhere('id', (int) $storeId);
            if ($matchingStore && empty($city)) {
                $city = $matchingStore->city;
            }
        }

        $stores = $this->storeService->getStores($lat, $lng, 50000, false, $city);
        $cities = $this->storeService->getCities();
        $discountProducts = $this->productService->getDiscountProducts(20);

        return view('web.stores.index', compact('stores', 'allStores', 'city', 'cities', 'discountProducts'));
    }
}


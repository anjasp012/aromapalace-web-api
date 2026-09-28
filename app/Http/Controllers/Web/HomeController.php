<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected HomeService $homeService
    ) {}

    public function index(): View
    {
        $data = $this->homeService->getHomeData();

        return view('web.home', [
            'banners' => $data['banners'],
            'categories' => $data['categories'],
            'recommendedProducts' => $data['recommended_products'],
            'popularProducts' => $data['popular_products'],
            'discountProducts' => $data['discount_products'],
            'brands' => $data['featured_brands'],
            'exclusivePromos' => $data['exclusive_promos'],
            'latestArticles' => $data['latest_articles'] ?? collect(),
        ]);
    }
}

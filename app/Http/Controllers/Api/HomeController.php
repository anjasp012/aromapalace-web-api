<?php

namespace App\Http\Controllers\Api;

use App\Services\HomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends BaseApiController
{
    public function __construct(
        protected HomeService $homeService
    ) {}

    /**
     * Payload lengkap untuk Home screen (Mobile & Web)
     */
    public function index(Request $request): JsonResponse
    {
        $homeData = $this->homeService->getHomeData();

        return $this->sendResponse($homeData, 'Data Beranda Aroma Palace.');
    }
}

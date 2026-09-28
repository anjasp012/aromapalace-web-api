<?php

namespace App\Http\Controllers\Api;

use App\Models\Promotion;
use App\Services\PromotionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends BaseApiController
{
    public function __construct(
        protected PromotionService $promotionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $promotions = $this->promotionService->getActivePromotions();
        return $this->sendResponse($promotions, 'Daftar promo yang sedang berlangsung.');
    }

    public function vouchers(Request $request): JsonResponse
    {
        $vouchers = $this->promotionService->getActiveVouchers();
        return $this->sendResponse($vouchers, 'Daftar voucher belanja.');
    }

    public function exclusiveOffers(Request $request): JsonResponse
    {
        $offers = $this->promotionService->getExclusiveOffers();
        return $this->sendResponse($offers, 'Penawaran eksklusif member.');
    }

    public function show(string $code): JsonResponse
    {
        $promotion = Promotion::where('code', strtoupper($code))->first();
        if (!$promotion) {
            return $this->sendError('Promo atau voucher tidak ditemukan.', [], 404);
        }
        return $this->sendResponse($promotion, 'Detail syarat dan ketentuan promo.');
    }

    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $result = $this->promotionService->validateCode($request->code, (float) $request->amount);
            return $this->sendResponse($result, 'Kode promo valid dan dapat digunakan.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }
}

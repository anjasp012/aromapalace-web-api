<?php

namespace App\Http\Controllers\Api;

use App\Services\MembershipService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipController extends BaseApiController
{
    public function __construct(
        protected MembershipService $membershipService
    ) {}

    public function status(Request $request): JsonResponse
    {
        $status = $this->membershipService->getStatus($request->user());
        return $this->sendResponse($status, 'Status membership dan loyalty points.');
    }

    public function pointHistory(Request $request): JsonResponse
    {
        $histories = $this->membershipService->getPointHistory($request->user(), (int) $request->input('per_page', 15));
        return $this->sendResponse($histories, 'Riwayat loyalty points.');
    }

    public function rewards(): JsonResponse
    {
        $rewards = $this->membershipService->getAvailableRewards();
        return $this->sendResponse($rewards, 'Daftar rewards loyalty points.');
    }

    public function redeemReward(Request $request, int $rewardId): JsonResponse
    {
        try {
            $result = $this->membershipService->redeemReward($request->user(), $rewardId);
            return $this->sendResponse($result, 'Reward berhasil ditukarkan! Voucher diskon telah dibuat.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }
}

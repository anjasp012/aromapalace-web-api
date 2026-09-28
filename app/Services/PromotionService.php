<?php

namespace App\Services;

use App\Models\Promotion;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class PromotionService
{
    /**
     * Seluruh promo yang sedang berlangsung
     */
    public function getActivePromotions(): Collection
    {
        return Promotion::where('is_active', true)
            ->where('end_date', '>=', now())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Voucher aktif yang dapat digunakan
     */
    public function getActiveVouchers(): Collection
    {
        return Promotion::where('is_active', true)
            ->where('end_date', '>=', now())
            ->where('is_exclusive', false)
            ->get();
    }

    /**
     * Exclusive offers untuk member
     */
    public function getExclusiveOffers(): Collection
    {
        return Promotion::where('is_active', true)
            ->where('end_date', '>=', now())
            ->where('is_exclusive', true)
            ->get();
    }

    /**
     * Validasi kode promo terhadap nominal belanja
     */
    public function validateCode(string $code, float $amount): array
    {
        $promo = Promotion::where('code', strtoupper($code))->where('is_active', true)->first();

        if (!$promo) {
            throw new Exception('Kode promo tidak valid atau tidak ditemukan.');
        }

        $now = now();
        if ($now->lt($promo->start_date) || $now->gt($promo->end_date)) {
            throw new Exception('Masa aktif promo ini telah berakhir.');
        }

        if ($promo->quota > 0 && $promo->used_count >= $promo->quota) {
            throw new Exception('Kuota pemakaian promo ini telah habis.');
        }

        if ($amount < $promo->min_purchase) {
            throw new Exception('Minimum belanja untuk promo ini adalah Rp ' . number_format($promo->min_purchase, 0, ',', '.'));
        }

        $discount = $promo->calculateDiscount($amount);

        return [
            'is_valid' => true,
            'code' => $promo->code,
            'title' => $promo->title,
            'discount_type' => $promo->discount_type,
            'discount_value' => $promo->discount_value,
            'calculated_discount' => $discount,
            'final_amount' => max(0, $amount - $discount),
            'terms_conditions' => $promo->terms_conditions,
        ];
    }
}


<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Notification;
use App\Models\PointHistory;
use App\Models\Promotion;
use App\Models\Reward;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipService
{
    /**
     * Ambil status membership dan detail poin user
     */
    public function getStatus(User $user): array
    {
        $membership = Membership::firstOrCreate(
            ['user_id' => $user->id],
            ['tier' => 'Member', 'points' => 0, 'total_spent' => 0, 'joined_at' => now()]
        );

        $benefits = [
            'Cashback Loyalty Points untuk setiap transaksi (1 poin / Rp 10.000)',
            'Tukarkan poin dengan voucher potongan harga & produk eksklusif',
            'Penawaran spesial & promo khusus member terdaftar',
            'Hadiah kejutan di bulan ulang tahun Anda',
            'Layanan konsultasi wewangian dengan Fragrance Advisor',
        ];

        return [
            'is_member' => true,
            'status_label' => 'Member Aktif',
            'points' => $membership->points,
            'total_spent' => $membership->total_spent,
            'joined_at' => $membership->joined_at ? $membership->joined_at->format('d M Y') : now()->format('d M Y'),
            'benefits' => $benefits,
            // Fallback backward compatibility keys:
            'tier' => 'Member',
            'my_benefits' => $benefits,
            'next_tier' => ['next_tier' => null, 'progress_percent' => 100, 'remaining_spent' => 0],
        ];
    }

    /**
     * Riwayat poin loyalty
     */
    public function getPointHistory(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return PointHistory::where('user_id', $user->id)->latest()->paginate($perPage);
    }

    /**
     * Daftar reward yang dapat ditukar
     */
    public function getAvailableRewards(): Collection
    {
        return Reward::where('is_active', true)->orderBy('points_required', 'asc')->get();
    }

    /**
     * Tukar poin dengan reward / voucher
     */
    public function redeemReward(User $user, int $rewardId): array
    {
        $reward = Reward::findOrFail($rewardId);
        $membership = Membership::where('user_id', $user->id)->firstOrFail();

        if ($membership->points < $reward->points_required) {
            throw new Exception("Poin Anda ({$membership->points}) tidak mencukupi untuk reward ini ({$reward->points_required} poin).");
        }

        return DB::transaction(function () use ($user, $membership, $reward) {
            $membership->decrement('points', $reward->points_required);

            $voucherCode = ($reward->promo_prefix ?? 'RWD-') . strtoupper(Str::random(6));

            Promotion::create([
                'code' => $voucherCode,
                'title' => "Hadiah Penukaran Poin: {$reward->title}",
                'description' => "Voucher diskon senilai Rp " . number_format($reward->discount_amount, 0, ',', '.') . " dari penukaran poin.",
                'discount_type' => 'fixed',
                'discount_value' => $reward->discount_amount,
                'min_purchase' => $reward->discount_amount,
                'quota' => 1,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addDays(30),
                'terms_conditions' => 'Berlaku 30 hari sejak penukaran untuk 1x transaksi.',
                'is_exclusive' => true,
                'is_active' => true,
            ]);

            PointHistory::create([
                'user_id' => $user->id,
                'type' => 'redeemed',
                'points' => -$reward->points_required,
                'balance_after' => $membership->points,
                'reference_type' => 'reward',
                'reference_id' => $reward->id,
                'description' => "Penukaran {$reward->title} (Kode: {$voucherCode})",
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'reward',
                'title' => 'Reward Berhasil Ditukarkan!',
                'message' => "Selamat! Anda berhasil menukarkan reward '{$reward->title}'. Gunakan voucher: {$voucherCode}",
                'data' => [
                    'reward_id' => $reward->id,
                    'voucher_code' => $voucherCode,
                    'discount_amount' => $reward->discount_amount,
                ],
            ]);

            return [
                'voucher_code' => $voucherCode,
                'reward' => $reward,
                'remaining_points' => $membership->points,
            ];
        });
    }
}


<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAddress;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class AddressService
{
    /**
     * Dapatkan daftar alamat user dengan alamat utama di paling atas
     */
    public function getUserAddresses(User $user): Collection
    {
        return $user->addresses()
            ->orderBy('is_primary', 'desc')
            ->latest()
            ->get();
    }

    /**
     * Dapatkan detail alamat milik user
     */
    public function getAddressDetail(User $user, int $addressId): ?UserAddress
    {
        return $user->addresses()->find($addressId);
    }

    /**
     * Tambah alamat baru untuk user
     */
    public function createAddress(User $user, array $data): UserAddress
    {
        $isFirst = $user->addresses()->count() === 0;
        $isPrimary = $isFirst || !empty($data['is_primary']);

        if ($isPrimary) {
            $user->addresses()->update(['is_primary' => false]);
        }

        $data['user_id'] = $user->id;
        $data['is_primary'] = $isPrimary;

        return UserAddress::create($data);
    }

    /**
     * Update alamat user
     */
    public function updateAddress(User $user, int $addressId, array $data): UserAddress
    {
        $address = $user->addresses()->find($addressId);
        if (!$address) {
            throw new Exception('Alamat tidak ditemukan.', 404);
        }

        if (!empty($data['is_primary'])) {
            $user->addresses()->where('id', '!=', $addressId)->update(['is_primary' => false]);
        }

        $address->update($data);

        return $address->fresh();
    }

    /**
     * Hapus alamat user
     */
    public function deleteAddress(User $user, int $addressId): bool
    {
        $address = $user->addresses()->find($addressId);
        if (!$address) {
            throw new Exception('Alamat tidak ditemukan.', 404);
        }

        $wasPrimary = $address->is_primary;
        $deleted = $address->delete();

        // Jika alamat utama dihapus, jadikan alamat berikutnya sebagai utama
        if ($wasPrimary) {
            $nextAddress = $user->addresses()->latest()->first();
            if ($nextAddress) {
                $nextAddress->update(['is_primary' => true]);
            }
        }

        return $deleted;
    }

    /**
     * Jadikan alamat tertentu sebagai alamat utama
     */
    public function setPrimaryAddress(User $user, int $addressId): UserAddress
    {
        $address = $user->addresses()->find($addressId);
        if (!$address) {
            throw new Exception('Alamat tidak ditemukan.', 404);
        }

        $user->addresses()->update(['is_primary' => false]);
        $address->update(['is_primary' => true]);

        return $address->fresh();
    }
}


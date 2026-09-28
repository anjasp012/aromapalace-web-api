<?php

namespace App\Http\Controllers\Api;

use App\Services\AddressService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends BaseApiController
{
    public function __construct(
        protected AddressService $addressService
    ) {}

    /**
     * Daftar alamat pengiriman milik user
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = $this->addressService->getUserAddresses($request->user());

        return $this->sendResponse($addresses, 'Daftar alamat pengiriman.');
    }

    /**
     * Tambah alamat baru
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'full_address' => 'required|string',
            'province' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_primary' => 'nullable|boolean',
        ]);

        $address = $this->addressService->createAddress($request->user(), $validated);

        return $this->sendResponse($address, 'Alamat pengiriman berhasil ditambahkan.', 201);
    }

    /**
     * Detail alamat
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $address = $this->addressService->getAddressDetail($request->user(), $id);

        if (!$address) {
            return $this->sendError('Alamat tidak ditemukan.', [], 404);
        }

        return $this->sendResponse($address, 'Detail alamat pengiriman.');
    }

    /**
     * Update alamat
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'sometimes|required|string|max:100',
            'phone_number' => 'sometimes|required|string|max:20',
            'full_address' => 'sometimes|required|string',
            'province' => 'nullable|string|max:100',
            'city' => 'sometimes|required|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_primary' => 'nullable|boolean',
        ]);

        try {
            $address = $this->addressService->updateAddress($request->user(), $id, $validated);
            return $this->sendResponse($address, 'Alamat berhasil diperbarui.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 404);
        }
    }

    /**
     * Set alamat sebagai alamat utama (primary)
     */
    public function setPrimary(Request $request, int $id): JsonResponse
    {
        try {
            $address = $this->addressService->setPrimaryAddress($request->user(), $id);
            return $this->sendResponse($address, 'Alamat utama berhasil diatur.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 404);
        }
    }

    /**
     * Hapus alamat
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->addressService->deleteAddress($request->user(), $id);
            return $this->sendResponse(null, 'Alamat berhasil dihapus.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 404);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    public function index(): View
    {
        $stores = Store::withCoordinates()->latest()->paginate(15);
        return view('admin.stores.index', compact('stores'));
    }

    public function create(): View
    {
        return view('admin.stores.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:30',
            'operating_hours' => 'nullable|string|max:100',
            'image_url' => 'nullable|url',
            'is_pickup_available' => 'nullable|boolean',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $store = $this->storeService->saveStore($validated);

        return redirect()->route('admin.stores.index')->with('success', "Toko '{$store->name}' berhasil ditambahkan.");
    }

    public function edit(int $id): View
    {
        $store = $this->storeService->getStoreDetail($id);
        return view('admin.stores.edit', compact('store'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:30',
            'operating_hours' => 'nullable|string|max:100',
            'image_url' => 'nullable|url',
            'is_pickup_available' => 'nullable|boolean',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $store = $this->storeService->saveStore($validated, $id);

        return redirect()->route('admin.stores.index')->with('success', "Informasi toko '{$store->name}' berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $store = Store::findOrFail($id);
        $name = $store->name;
        $store->delete();

        return redirect()->route('admin.stores.index')->with('success', "Toko '{$name}' telah dihapus.");
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        $promotions = Promotion::latest()->paginate(15);
        return view('admin.promotions.index', compact('promotions'));
    }

    public function create(): View
    {
        return view('admin.promotions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promotions,code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:1',
            'min_purchase' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'quota' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'terms_conditions' => 'nullable|string',
            'is_exclusive' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_exclusive'] = !empty($validated['is_exclusive']);
        $validated['is_active'] = true;

        Promotion::create($validated);

        return redirect()->route('admin.promotions.index')->with('success', "Kode voucher '{$validated['code']}' berhasil dibuat.");
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $promo = Promotion::findOrFail($id);
        $promo->update(['is_active' => !$promo->is_active]);

        $status = $promo->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Promo '{$promo->code}' berhasil {$status}.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $promo = Promotion::findOrFail($id);
        $code = $promo->code;
        $promo->delete();

        return redirect()->route('admin.promotions.index')->with('success', "Voucher '{$code}' telah dihapus.");
    }
}


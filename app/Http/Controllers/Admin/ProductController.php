<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['brand', 'category', 'variants', 'images']);

        if ($search = $request->query('search')) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($catId = $request->query('category_id')) {
            $query->where('category_id', $catId);
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'short_description' => 'nullable|string',
            'description' => 'required|string',
            'how_to_use' => 'nullable|string',
            'ingredients' => 'nullable|string',
            'image_url' => 'required|url',
            'is_featured' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'variant_name' => 'nullable|array',
            'variant_price' => 'nullable|array',
            'variant_stock' => 'nullable|array',
        ]);

        $slug = Str::slug($validated['name']);
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(4);
        }

        $isDiscount = !empty($validated['discount_price']) && $validated['discount_price'] < $validated['base_price'];
        $discountPercent = 0;
        if ($isDiscount) {
            $discountPercent = (int) round((($validated['base_price'] - $validated['discount_price']) / $validated['base_price']) * 100);
        }

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'brand_id' => $validated['brand_id'],
            'category_id' => $validated['category_id'],
            'base_price' => $validated['base_price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'discount_percent' => $discountPercent,
            'stock' => $validated['stock'],
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'],
            'how_to_use' => $validated['how_to_use'] ?? null,
            'ingredients' => $validated['ingredients'] ?? null,
            'is_discount' => $isDiscount,
            'is_featured' => !empty($validated['is_featured']),
            'is_popular' => !empty($validated['is_popular']),
            'is_active' => true,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => $validated['image_url'],
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        // Simpan varian jika ada
        if (!empty($validated['variant_name'])) {
            foreach ($validated['variant_name'] as $i => $vName) {
                if (!empty($vName)) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $vName,
                        'sku' => 'SKU-' . strtoupper(Str::random(6)),
                        'price' => $validated['variant_price'][$i] ?? $validated['base_price'],
                        'stock' => $validated['variant_stock'][$i] ?? 10,
                        'attribute_name' => 'Size',
                        'attribute_value' => $vName,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', "Produk '{$product->name}' berhasil ditambahkan.");
    }

    public function edit(int $id): View
    {
        $product = Product::with(['images', 'variants'])->findOrFail($id);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'short_description' => 'nullable|string',
            'description' => 'required|string',
            'how_to_use' => 'nullable|string',
            'ingredients' => 'nullable|string',
            'image_url' => 'nullable|url',
            'is_featured' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $isDiscount = !empty($validated['discount_price']) && $validated['discount_price'] < $validated['base_price'];
        $discountPercent = 0;
        if ($isDiscount) {
            $discountPercent = (int) round((($validated['base_price'] - $validated['discount_price']) / $validated['base_price']) * 100);
        }

        $product->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'category_id' => $validated['category_id'],
            'base_price' => $validated['base_price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'discount_percent' => $discountPercent,
            'stock' => $validated['stock'],
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'],
            'how_to_use' => $validated['how_to_use'] ?? null,
            'ingredients' => $validated['ingredients'] ?? null,
            'is_discount' => $isDiscount,
            'is_featured' => !empty($validated['is_featured']),
            'is_popular' => !empty($validated['is_popular']),
            'is_active' => $request->has('is_active'),
        ]);

        if (!empty($validated['image_url'])) {
            $product->images()->updateOrCreate(
                ['is_primary' => true],
                ['image_url' => $validated['image_url']]
            );
        }

        return redirect()->route('admin.products.index')->with('success', "Produk '{$product->name}' berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', "Produk '{$name}' telah dihapus.");
    }
}


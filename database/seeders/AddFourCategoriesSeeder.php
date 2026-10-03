<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class AddFourCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $newCategories = [
            [
                'name' => 'Lilin & Diffuser',
                'slug' => 'lilin-aromaterapi-diffuser',
                'icon_url' => 'https://cdn-icons-png.flaticon.com/512/3163/3163235.png',
                'image_url' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=600&q=80',
                'description' => 'Lilin aromaterapi beraroma mewah dan reed diffuser untuk kenyamanan suasana ruangan.',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Oud & Oriental',
                'slug' => 'oud-oriental',
                'icon_url' => 'https://cdn-icons-png.flaticon.com/512/3163/3163240.png',
                'image_url' => 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=600&q=80',
                'description' => 'Koleksi aroma kayu gaharu (oud), amber, dan rempah eksotis khas wewangian oriental.',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Bath & Body Care',
                'slug' => 'bath-and-body-care',
                'icon_url' => 'https://cdn-icons-png.flaticon.com/512/3163/3163245.png',
                'image_url' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=600&q=80',
                'description' => 'Sabun mandi mewah, body wash, dan body lotion dengan wewangian tahan lama.',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Gift Sets & Hampers',
                'slug' => 'gift-sets-and-hampers',
                'icon_url' => 'https://cdn-icons-png.flaticon.com/512/3163/3163250.png',
                'image_url' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80',
                'description' => 'Paket bingkisan wewangian istimewa dalam kemasan box eksklusif.',
                'is_active' => true,
                'sort_order' => 9,
            ],
        ];

        $brandJoMalone = Brand::where('slug', 'jo-malone-london')->first() ?? Brand::first();
        $brandTomFord = Brand::where('slug', 'tom-ford')->first() ?? Brand::first();
        $brandMargiela = Brand::where('slug', 'maison-margiela')->first() ?? Brand::first();

        foreach ($newCategories as $catData) {
            $cat = Category::updateOrCreate(['slug' => $catData['slug']], $catData);

            if ($cat->products()->count() === 0) {
                if ($cat->slug === 'lilin-aromaterapi-diffuser') {
                    $prod = Product::updateOrCreate(
                        ['slug' => 'english-pear-luxury-scented-candle'],
                        [
                            'name' => 'English Pear Luxury Scented Candle',
                            'brand_id' => $brandJoMalone?->id,
                            'category_id' => $cat->id,
                            'short_description' => 'Lilin aromaterapi mewah beraroma pir segar dan freesia putih.',
                            'description' => 'Menghadirkan keharuman signature English Pear & Freesia ke dalam ruangan Anda dengan daya bakar hingga 45 jam.',
                            'how_to_use' => 'Bakar lilin minimal 2 jam pada penggunaan pertama hingga permukaan meleleh merata.',
                            'base_price' => 1250000,
                            'discount_price' => 1100000,
                            'discount_percent' => 12,
                            'stock' => 25,
                            'rating_avg' => 4.90,
                            'reviews_count' => 34,
                            'is_featured' => true,
                            'is_active' => true,
                        ]
                    );
                    ProductImage::updateOrCreate(
                        ['product_id' => $prod->id, 'is_primary' => true],
                        ['image_url' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=800&q=80', 'sort_order' => 0]
                    );
                } elseif ($cat->slug === 'oud-oriental') {
                    $prod = Product::updateOrCreate(
                        ['slug' => 'royal-oud-wood-parfum'],
                        [
                            'name' => 'Royal Oud Wood Parfum Intense',
                            'brand_id' => $brandTomFord?->id,
                            'category_id' => $cat->id,
                            'short_description' => 'Wewangian oriental mewah perpaduan kayu gaharu langka, amber, dan kapulaga.',
                            'description' => 'Salah satu wewangian paling berharga dengan aroma smoky oud, rosewood, dan tonka bean yang intens dan memesona.',
                            'how_to_use' => 'Semprotkan pada titik nadi hangat leher dan pergelangan tangan.',
                            'base_price' => 3850000,
                            'discount_price' => null,
                            'discount_percent' => 0,
                            'stock' => 20,
                            'rating_avg' => 4.95,
                            'reviews_count' => 48,
                            'is_featured' => true,
                            'is_active' => true,
                        ]
                    );
                    ProductImage::updateOrCreate(
                        ['product_id' => $prod->id, 'is_primary' => true],
                        ['image_url' => 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=800&q=80', 'sort_order' => 0]
                    );
                } elseif ($cat->slug === 'bath-and-body-care') {
                    $prod = Product::updateOrCreate(
                        ['slug' => 'silk-velvet-body-wash-lotion'],
                        [
                            'name' => 'Silk Velvet Body Cleanser & Lotion Duo',
                            'brand_id' => $brandMargiela?->id,
                            'category_id' => $cat->id,
                            'short_description' => 'Paket perawatan tubuh mewah bernutrisi tinggi dengan keharuman tahan lama.',
                            'description' => 'Formula lembut yang membersihkan sekaligus melembapkan kulit secara mendalam dengan aroma floral powdery yang lembut.',
                            'how_to_use' => 'Gunakan saat mandi, lanjutkan dengan body lotion ke seluruh tubuh.',
                            'base_price' => 1450000,
                            'discount_price' => 1290000,
                            'discount_percent' => 11,
                            'stock' => 30,
                            'rating_avg' => 4.88,
                            'reviews_count' => 29,
                            'is_featured' => false,
                            'is_active' => true,
                        ]
                    );
                    ProductImage::updateOrCreate(
                        ['product_id' => $prod->id, 'is_primary' => true],
                        ['image_url' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=800&q=80', 'sort_order' => 0]
                    );
                } elseif ($cat->slug === 'gift-sets-and-hampers') {
                    $prod = Product::updateOrCreate(
                        ['slug' => 'palace-imperial-discovery-hamper'],
                        [
                            'name' => 'Palace Imperial Discovery Hamper',
                            'brand_id' => $brandJoMalone?->id,
                            'category_id' => $cat->id,
                            'short_description' => 'Set bingkisan mewah berisi parfum 50ml, travel spray, dan mini candle.',
                            'description' => 'Pilihan hadiah paling prestisius dikemas dalam kotak velvet burgundy berpita emas eksklusif Aroma Palace.',
                            'how_to_use' => 'Ideal sebagai bingkisan hari istimewa, ulang tahun, dan perayaan spesial.',
                            'base_price' => 3200000,
                            'discount_price' => 2890000,
                            'discount_percent' => 10,
                            'stock' => 15,
                            'rating_avg' => 5.00,
                            'reviews_count' => 19,
                            'is_featured' => true,
                            'is_active' => true,
                        ]
                    );
                    ProductImage::updateOrCreate(
                        ['product_id' => $prod->id, 'is_primary' => true],
                        ['image_url' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80', 'sort_order' => 0]
                    );
                }
            }
        }
    }
}

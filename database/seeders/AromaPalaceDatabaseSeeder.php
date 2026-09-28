<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\BeautyArticle;
use App\Models\BeautyTopic;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Membership;
use App\Models\Notification;
use App\Models\PointHistory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\Reward;
use App\Models\Store;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AromaPalaceDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Demo User & Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@aromapalace.com'],
            [
                'name' => 'Administrator Aroma Palace',
                'role' => 'admin',
                'phone' => '081100001111',
                'password' => Hash::make('password123'),
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=300&q=80',
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'user@aromapalace.com'],
            [
                'name' => 'Anjas Aroma',
                'role' => 'customer',
                'phone' => '081234567890',
                'password' => Hash::make('password123'),
                'birthdate' => '1998-05-15',
                'gender' => 'male',
                'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=300&q=80',
            ]
        );

        // User Primary Address
        UserAddress::firstOrCreate(
            ['user_id' => $user->id, 'is_primary' => true],
            [
                'label' => 'Rumah',
                'recipient_name' => 'Anjas Aroma',
                'phone_number' => '081234567890',
                'full_address' => 'Jl. Senopati Raya No. 45, Kebayoran Baru',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Selatan',
                'district' => 'Kebayoran Baru',
                'postal_code' => '12190',
                'notes' => 'Pagar hitam, samping mini market',
                'latitude' => -6.2341,
                'longitude' => 106.8092,
            ]
        );

        // Membership & Initial Points
        $membership = Membership::firstOrCreate(
            ['user_id' => $user->id],
            [
                'tier' => 'Gold',
                'points' => 350,
                'total_spent' => 2850000,
                'joined_at' => now()->subMonths(3),
            ]
        );

        PointHistory::firstOrCreate(
            ['user_id' => $user->id, 'description' => 'Bonus Pendaftaran Member Baru'],
            [
                'type' => 'bonus',
                'points' => 50,
                'balance_after' => 50,
                'reference_type' => 'signup',
            ]
        );

        // 2. Stores (with PostGIS Geometry Points)
        $storesData = [
            [
                'name' => 'Aroma Palace - Grand Indonesia',
                'city' => 'Jakarta Pusat',
                'address' => 'East Mall Lantai 1, Jl. M.H. Thamrin No.1, Jakarta Pusat',
                'phone' => '021-23580001',
                'operating_hours' => '10:00 - 22:00',
                'image_url' => 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&w=600&q=80',
                'is_pickup_available' => true,
                'lng' => 106.8207,
                'lat' => -6.1953,
            ],
            [
                'name' => 'Aroma Palace - Senayan City',
                'city' => 'Jakarta Pusat',
                'address' => 'Ground Floor Unit 12, Jl. Asia Afrika No.19, Jakarta Pusat',
                'phone' => '021-72781000',
                'operating_hours' => '10:00 - 22:00',
                'image_url' => 'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?auto=format&fit=crop&w=600&q=80',
                'is_pickup_available' => true,
                'lng' => 106.7974,
                'lat' => -6.2274,
            ],
            [
                'name' => 'Aroma Palace - Paris Van Java Bandung',
                'city' => 'Bandung',
                'address' => 'Resort Level No. C-05, Jl. Sukajadi No.131-139, Bandung',
                'phone' => '022-82063000',
                'operating_hours' => '10:00 - 22:00',
                'image_url' => 'https://images.unsplash.com/photo-1541123437800-1bb1317badc2?auto=format&fit=crop&w=600&q=80',
                'is_pickup_available' => true,
                'lng' => 107.5959,
                'lat' => -6.8893,
            ],
            [
                'name' => 'Aroma Palace - Tunjungan Plaza Surabaya',
                'city' => 'Surabaya',
                'address' => 'TP 4 Lantai 2, Jl. Embong Malang No.7-21, Surabaya',
                'phone' => '031-5311000',
                'operating_hours' => '10:00 - 22:00',
                'image_url' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=600&q=80',
                'is_pickup_available' => true,
                'lng' => 112.7388,
                'lat' => -7.2618,
            ],
            [
                'name' => 'Aroma Palace - Seminyak Village Bali',
                'city' => 'Bali',
                'address' => 'Lantai 1, Jl. Kayu Jati No.8, Seminyak, Kuta, Badung, Bali',
                'phone' => '0361-738000',
                'operating_hours' => '10:00 - 23:00',
                'image_url' => 'https://images.unsplash.com/photo-1582037928769-181f2644ecb7?auto=format&fit=crop&w=600&q=80',
                'is_pickup_available' => true,
                'lng' => 115.1558,
                'lat' => -8.6836,
            ],
        ];

        foreach ($storesData as $item) {
            Store::updateOrCreate(
                ['name' => $item['name']],
                [
                    'city' => $item['city'],
                    'address' => $item['address'],
                    'phone' => $item['phone'],
                    'operating_hours' => $item['operating_hours'],
                    'image_url' => $item['image_url'],
                    'is_pickup_available' => $item['is_pickup_available'],
                    'location' => DB::raw("ST_SetSRID(ST_MakePoint({$item['lng']}, {$item['lat']}), 4326)"),
                ]
            );
        }

        // 3. Categories
        $categories = [
            [
                'name' => 'Parfum Wanita',
                'slug' => 'parfum-wanita',
                'icon_url' => 'https://cdn-icons-png.flaticon.com/512/3163/3163195.png',
                'image_url' => 'https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?auto=format&fit=crop&w=400&q=80',
                'description' => 'Aroma floral, sweet, fruity, dan powdery yang anggun dan memikat.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Parfum Pria',
                'slug' => 'parfum-pria',
                'icon_url' => 'https://cdn-icons-png.flaticon.com/512/3163/3163200.png',
                'image_url' => 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&w=400&q=80',
                'description' => 'Wewangian woody, spicy, fresh citrus, dan leather yang karismatik.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Unisex Fragrance',
                'slug' => 'unisex-fragrance',
                'icon_url' => 'https://cdn-icons-png.flaticon.com/512/3163/3163212.png',
                'image_url' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=400&q=80',
                'description' => 'Aroma versatil yang cocok dan seimbang untuk siapa saja.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Discovery Sets & Vials',
                'slug' => 'discovery-sets',
                'icon_url' => 'https://cdn-icons-png.flaticon.com/512/3163/3163220.png',
                'image_url' => 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=400&q=80',
                'description' => 'Miniatur dan sampler set untuk menjelajahi signature scent Anda.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Body & Hair Mist',
                'slug' => 'body-and-hair-mist',
                'icon_url' => 'https://cdn-icons-png.flaticon.com/512/3163/3163231.png',
                'image_url' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=400&q=80',
                'description' => 'Wewangian ringan menyegarkan untuk rambut dan seluruh tubuh.',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 4. Brands
        $brands = [
            [
                'name' => 'Maison Margiela',
                'slug' => 'maison-margiela',
                'logo_url' => 'https://images.unsplash.com/photo-1615397349754-cfa2066a298e?auto=format&fit=crop&w=200&q=80',
                'is_featured' => true,
                'description' => 'Brand haute couture legendaris dengan lini wewangian Replica.',
            ],
            [
                'name' => 'Jo Malone London',
                'slug' => 'jo-malone-london',
                'logo_url' => 'https://images.unsplash.com/photo-1583445013765-46c20c4a6772?auto=format&fit=crop&w=200&q=80',
                'is_featured' => true,
                'description' => 'Cita rasa khas wewangian Inggris yang elegan dan timeless.',
            ],
            [
                'name' => 'Tom Ford',
                'slug' => 'tom-ford',
                'logo_url' => 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=200&q=80',
                'is_featured' => true,
                'description' => 'Koleksi Private Blend yang mewah, berani, dan tak tertandingi.',
            ],
            [
                'name' => 'HMNS Perfume',
                'slug' => 'hmns-perfume',
                'logo_url' => 'https://images.unsplash.com/photo-1528740561666-dc2479dc08ab?auto=format&fit=crop&w=200&q=80',
                'is_featured' => true,
                'description' => 'Artisanal perfume lokal terbaik dengan longevity dan sillage istimewa.',
            ],
            [
                'name' => 'SAFF & Co.',
                'slug' => 'saff-and-co',
                'logo_url' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=200&q=80',
                'is_featured' => true,
                'description' => 'Extrait de parfum berdaya tahan tinggi dengan karakter bold.',
            ],
        ];

        foreach ($brands as $b) {
            Brand::updateOrCreate(['slug' => $b['slug']], $b);
        }

        // 5. Products
        $catUnisex = Category::where('slug', 'unisex-fragrance')->first();
        $catPria = Category::where('slug', 'parfum-pria')->first();
        $catWanita = Category::where('slug', 'parfum-wanita')->first();
        $catDiscovery = Category::where('slug', 'discovery-sets')->first();

        $brandMargiela = Brand::where('slug', 'maison-margiela')->first();
        $brandJoMalone = Brand::where('slug', 'jo-malone-london')->first();
        $brandTomFord = Brand::where('slug', 'tom-ford')->first();
        $brandHmns = Brand::where('slug', 'hmns-perfume')->first();
        $brandSaff = Brand::where('slug', 'saff-and-co')->first();

        $productsData = [
            [
                'name' => 'Replica Jazz Club Eau de Toilette',
                'slug' => 'replica-jazz-club-edt',
                'brand_id' => $brandMargiela?->id,
                'category_id' => $catUnisex?->id,
                'short_description' => 'Aroma tembakau hangat, rum, dan vanilla yang membangkitkan suasana jazz club di Brooklyn.',
                'description' => 'Replica Jazz Club menghidupkan kembali malam yang tak terlupakan di klub jazz legendaris Brooklyn. Dengan perpaduan hangat daun tembakau, absolute rum, dan bourbon vanilla, parfum ini memberikan sentuhan sensual dan elegan sepanjang hari.',
                'how_to_use' => 'Semprotkan pada titik nadi (leher, pergelangan tangan, lipatan siku) dari jarak 15 cm.',
                'ingredients' => 'Alcohol, Fragrance, Water, Ethylhexyl Methoxycinnamate, Coumarin, Limonene.',
                'base_price' => 2450000,
                'discount_price' => 2200000,
                'discount_percent' => 10,
                'stock' => 45,
                'rating_avg' => 4.90,
                'reviews_count' => 128,
                'is_featured' => true,
                'is_popular' => true,
                'is_discount' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=800&q=80',
                ],
                'variants' => [
                    ['name' => '30 ml Travel Size', 'sku' => 'MM-JC-30', 'price' => 1150000, 'discount_price' => 1050000, 'stock' => 20, 'attribute_name' => 'Size', 'attribute_value' => '30ml'],
                    ['name' => '100 ml Full Bottle', 'sku' => 'MM-JC-100', 'price' => 2450000, 'discount_price' => 2200000, 'stock' => 25, 'attribute_name' => 'Size', 'attribute_value' => '100ml'],
                ],
            ],
            [
                'name' => 'English Pear & Freesia Cologne',
                'slug' => 'english-pear-and-freesia',
                'brand_id' => $brandJoMalone?->id,
                'category_id' => $catWanita?->id,
                'short_description' => 'Kesegaran buah pir King William berpadu dengan keharuman bunga freesia putih yang lembut.',
                'description' => 'Cologne ikonik Jo Malone London yang menggambarkan keindahan musim gugur di Inggris. Kesegaran pir matang yang renyah dibalut dengan buket freesia putih yang lembut, dan disempurnakan oleh amber, nilam, serta kayu.',
                'how_to_use' => 'Semprotkan secara bebas pada pergelangan tangan dan leher. Cocok dikombinasikan dengan Wood Sage & Sea Salt.',
                'ingredients' => 'Alcohol Denat., Water\Aqua\Eau, Fragrance (Parfum), Geraniol, Hexyl Cinnamal.',
                'base_price' => 2600000,
                'discount_price' => null,
                'discount_percent' => 0,
                'stock' => 38,
                'rating_avg' => 4.85,
                'reviews_count' => 96,
                'is_featured' => true,
                'is_popular' => true,
                'is_discount' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=800&q=80',
                ],
                'variants' => [
                    ['name' => '50 ml', 'sku' => 'JM-EP-50', 'price' => 1750000, 'discount_price' => null, 'stock' => 18, 'attribute_name' => 'Size', 'attribute_value' => '50ml'],
                    ['name' => '100 ml', 'sku' => 'JM-EP-100', 'price' => 2600000, 'discount_price' => null, 'stock' => 20, 'attribute_name' => 'Size', 'attribute_value' => '100ml'],
                ],
            ],
            [
                'name' => 'Oud Wood Eau de Parfum',
                'slug' => 'tom-ford-oud-wood',
                'brand_id' => $brandTomFord?->id,
                'category_id' => $catPria?->id,
                'short_description' => 'Salah satu wewangian oud paling legendaris dengan sentuhan rosewood, cardamom, dan amber.',
                'description' => 'Asap kayu oud langka yang menyatu dengan rempah cardamom dan rosewood. Menampilkan aroma hangat dan berwibawa khas seorang gentlemen yang percaya diri.',
                'how_to_use' => 'Semprotkan 2-3 semprotan pada pakaian dan area leher.',
                'ingredients' => 'Rare Oud Wood, Sandalwood, Chinese Pepper, Rosewood, Tonka Bean, Vanilla.',
                'base_price' => 4300000,
                'discount_price' => 3870000,
                'discount_percent' => 10,
                'stock' => 15,
                'rating_avg' => 4.95,
                'reviews_count' => 84,
                'is_featured' => true,
                'is_popular' => true,
                'is_discount' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&w=800&q=80',
                ],
                'variants' => [
                    ['name' => '50 ml', 'sku' => 'TF-OW-50', 'price' => 4300000, 'discount_price' => 3870000, 'stock' => 15, 'attribute_name' => 'Size', 'attribute_value' => '50ml'],
                ],
            ],
            [
                'name' => 'HMNS Farhampton Extrait de Parfum',
                'slug' => 'hmns-farhampton',
                'brand_id' => $brandHmns?->id,
                'category_id' => $catPria?->id,
                'short_description' => 'Aroma maskulin berkarakter hangat dengan konsentrasi murni extrait de parfum.',
                'description' => 'Farhampton memiliki konsentrasi pure parfum oil tertinggi dengan longevity hingga 10-12 jam. Top notes bergamot dan ripe fruits berpadu dengan lavender, orange blossom, amber, dan tonka beans.',
                'how_to_use' => 'Semprotkan pada titik denyut nadi untuk proyeksi maksimal.',
                'ingredients' => 'Extrait de parfum grade fragrance oil, ethanol.',
                'base_price' => 395000,
                'discount_price' => 355000,
                'discount_percent' => 10,
                'stock' => 120,
                'rating_avg' => 4.88,
                'reviews_count' => 312,
                'is_featured' => true,
                'is_popular' => true,
                'is_discount' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=800&q=80',
                ],
                'variants' => [
                    ['name' => '100 ml', 'sku' => 'HMNS-FH-100', 'price' => 395000, 'discount_price' => 355000, 'stock' => 120, 'attribute_name' => 'Size', 'attribute_value' => '100ml'],
                ],
            ],
            [
                'name' => 'SAFF & Co. S.O.T.B. Extrait de Parfum',
                'slug' => 'saff-and-co-sotb',
                'brand_id' => $brandSaff?->id,
                'category_id' => $catWanita?->id,
                'short_description' => 'Nuansa tropis manis dari mandarin, floral, vanilla, dan musk yang memikat.',
                'description' => 'Sex On The Beach (S.O.T.B.) memberikan vibrasi liburan pantai yang hangat dan ceria. Wangi manis tropis yang tahan lebih dari 8 jam.',
                'how_to_use' => 'Semprotkan dari jarak 10-15 cm di pakaian atau kulit.',
                'ingredients' => 'Mandarin, Galbanum, Ylang, Tuberose, Jasmine, Orange Flower, Vanilla, Tonka Bean, Musk.',
                'base_price' => 249000,
                'discount_price' => null,
                'discount_percent' => 0,
                'stock' => 80,
                'rating_avg' => 4.78,
                'reviews_count' => 210,
                'is_featured' => false,
                'is_popular' => true,
                'is_discount' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?auto=format&fit=crop&w=800&q=80',
                ],
                'variants' => [
                    ['name' => '30 ml', 'sku' => 'SAFF-SOTB-30', 'price' => 249000, 'discount_price' => null, 'stock' => 80, 'attribute_name' => 'Size', 'attribute_value' => '30ml'],
                ],
            ],
            [
                'name' => 'Aroma Palace Luxury Discovery Set (5 x 5ml)',
                'slug' => 'aroma-palace-luxury-discovery-set',
                'brand_id' => $brandMargiela?->id,
                'category_id' => $catDiscovery?->id,
                'short_description' => '5 vial mewah parfum niche pilihan untuk menemukan signature scent Anda.',
                'description' => 'Koleksi eksklusif berisi 5 varian terpopuler berukuran 5ml vial atomizer: Jazz Club, English Pear, Oud Wood, Farhampton, dan S.O.T.B. Dilengkapi box gift mewah.',
                'how_to_use' => 'Gunakan satu aroma setiap hari untuk menemukan karakter wangi favorit Anda.',
                'ingredients' => '5 Miniature glass atomizers 5ml.',
                'base_price' => 450000,
                'discount_price' => 380000,
                'discount_percent' => 15,
                'stock' => 50,
                'rating_avg' => 4.92,
                'reviews_count' => 64,
                'is_featured' => true,
                'is_popular' => false,
                'is_discount' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=800&q=80',
                ],
                'variants' => [
                    ['name' => 'Box Set 5 x 5ml', 'sku' => 'AP-DISC-5X5', 'price' => 450000, 'discount_price' => 380000, 'stock' => 50, 'attribute_name' => 'Set', 'attribute_value' => '5x5ml'],
                ],
            ],
        ];

        foreach ($productsData as $prod) {
            $images = $prod['images'];
            $variants = $prod['variants'];
            unset($prod['images'], $prod['variants']);

            $product = Product::updateOrCreate(['slug' => $prod['slug']], $prod);

            // Images
            foreach ($images as $index => $imgUrl) {
                ProductImage::firstOrCreate(
                    ['product_id' => $product->id, 'image_url' => $imgUrl],
                    ['is_primary' => ($index === 0), 'sort_order' => $index]
                );
            }

            // Variants
            foreach ($variants as $var) {
                $var['product_id'] = $product->id;
                ProductVariant::updateOrCreate(['sku' => $var['sku']], $var);
            }

            // Demo Reviews
            ProductReview::firstOrCreate(
                ['product_id' => $product->id, 'user_id' => $user->id],
                [
                    'rating' => 5,
                    'comment' => 'Wanginya sangat mewah dan tahan lebih dari 8 jam. Pengiriman juga sangat rapi dan cepat!',
                    'is_verified_purchase' => true,
                    'is_approved' => true,
                ]
            );
        }

        // 6. Banners (Home Screen Sliders)
        $banners = [
            [
                'title' => 'The Art of Fragrance',
                'subtitle' => 'Jelajahi koleksi parfum mewah dunia dengan penawaran spesial.',
                'image_url' => 'https://images.unsplash.com/photo-1615397349754-cfa2066a298e?auto=format&fit=crop&w=1200&q=80',
                'mobile_image_url' => 'https://images.unsplash.com/photo-1615397349754-cfa2066a298e?auto=format&fit=crop&w=600&q=80',
                'link_type' => 'category',
                'link_target' => 'unisex-fragrance',
                'sort_order' => 1,
            ],
            [
                'title' => 'Flash Sale Diskon hingga 20%',
                'subtitle' => 'Gunakan kode promo AROMA20 untuk potongan harga eksklusif.',
                'image_url' => 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=1200&q=80',
                'mobile_image_url' => 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=600&q=80',
                'link_type' => 'promo',
                'link_target' => 'AROMA20',
                'sort_order' => 2,
            ],
            [
                'title' => 'Store Pickup Sekarang Tersedia',
                'subtitle' => 'Pesan online di aplikasi, ambil langsung di gerai Grand Indonesia & Senayan City.',
                'image_url' => 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&w=1200&q=80',
                'mobile_image_url' => 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&w=600&q=80',
                'link_type' => 'stores',
                'link_target' => 'stores',
                'sort_order' => 3,
            ],
        ];

        foreach ($banners as $ban) {
            Banner::updateOrCreate(['title' => $ban['title']], $ban);
        }

        // 7. Promotions & Vouchers
        $promos = [
            [
                'code' => 'AROMA20',
                'title' => 'Diskon 20% Koleksi Pilihan',
                'description' => 'Diskon 20% maksimal potongan Rp 150.000 untuk minimal belanja Rp 500.000.',
                'banner_image' => 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=600&q=80',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'min_purchase' => 500000,
                'max_discount' => 150000,
                'quota' => 200,
                'used_count' => 12,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
                'terms_conditions' => 'Berlaku untuk seluruh produk kecuali Discovery Set. 1 kali pemakaian per user.',
                'is_exclusive' => false,
            ],
            [
                'code' => 'WELCOME50',
                'title' => 'Voucher Pengguna Baru Rp 50.000',
                'description' => 'Potongan langsung Rp 50.000 untuk transaksi pertama.',
                'banner_image' => 'https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?auto=format&fit=crop&w=600&q=80',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_purchase' => 250000,
                'max_discount' => 50000,
                'quota' => 500,
                'used_count' => 45,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(60),
                'terms_conditions' => 'Khusus akun baru dengan minimal belanja Rp 250.000.',
                'is_exclusive' => false,
            ],
            [
                'code' => 'MEMBERVIP',
                'title' => 'Exclusive Gold & Platinum Member Rp 100.000',
                'description' => 'Potongan spesial Rp 100.000 untuk member Gold dan Platinum.',
                'banner_image' => 'https://images.unsplash.com/photo-1615397349754-cfa2066a298e?auto=format&fit=crop&w=600&q=80',
                'discount_type' => 'fixed',
                'discount_value' => 100000,
                'min_purchase' => 1000000,
                'max_discount' => 100000,
                'quota' => 100,
                'used_count' => 8,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(30),
                'terms_conditions' => 'Eksklusif member Gold/Platinum dengan minimal transaksi Rp 1.000.000.',
                'is_exclusive' => true,
            ],
        ];

        foreach ($promos as $pro) {
            Promotion::updateOrCreate(['code' => $pro['code']], $pro);
        }

        // 8. Rewards Catalog
        $rewards = [
            [
                'title' => 'Voucher Belanja Rp 50.000',
                'description' => 'Tukarkan 200 poin untuk voucher potongan belanja Rp 50.000 tanpa minimal transaksi.',
                'image_url' => 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=300&q=80',
                'points_required' => 200,
                'reward_type' => 'voucher_discount',
                'discount_amount' => 50000,
                'promo_prefix' => 'RWD50-',
            ],
            [
                'title' => 'Voucher Belanja Rp 100.000',
                'description' => 'Tukarkan 350 poin untuk voucher potongan belanja Rp 100.000.',
                'image_url' => 'https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?auto=format&fit=crop&w=300&q=80',
                'points_required' => 350,
                'reward_type' => 'voucher_discount',
                'discount_amount' => 100000,
                'promo_prefix' => 'RWD100-',
            ],
            [
                'title' => 'Gratis 1 Fragrance Discovery Vial 5ml',
                'description' => 'Tukarkan 150 poin untuk mendapatkan vial 5ml varian pilihan saat pengambilan di toko.',
                'image_url' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=300&q=80',
                'points_required' => 150,
                'reward_type' => 'merchandise',
                'discount_amount' => 0,
                'promo_prefix' => 'VIAL-',
            ],
        ];

        foreach ($rewards as $rew) {
            Reward::updateOrCreate(['title' => $rew['title']], $rew);
        }

        // 9. Beauty Content (Topics & Articles)
        $topicPerfumeGuide = BeautyTopic::updateOrCreate(
            ['slug' => 'fragrance-guide'],
            ['name' => 'Fragrance Guide', 'description' => 'Panduan lengkap memahami notes, konsentrasi, dan memilih parfum yang tepat.', 'sort_order' => 1]
        );

        $topicTrends = BeautyTopic::updateOrCreate(
            ['slug' => 'trends-and-insights'],
            ['name' => 'Trends & Insights', 'description' => 'Tren wewangian terbaru dan ulasan bahan-bahan aromatik terkini.', 'sort_order' => 2]
        );

        $firstProduct = Product::where('slug', 'replica-jazz-club-edt')->first();
        $secondProduct = Product::where('slug', 'english-pear-and-freesia')->first();
        $relatedIds = array_filter([$firstProduct?->id, $secondProduct?->id]);

        $articles = [
            [
                'topic_id' => $topicPerfumeGuide->id,
                'title' => 'Rahasia Agar Aroma Parfum Tahan Seharian',
                'slug' => 'rahasia-agar-aroma-parfum-tahan-seharian',
                'summary' => 'Pelajari teknik layering dan titik denyut nadi terbaik agar parfum Anda tetap tercium hingga malam hari.',
                'content' => "Banyak orang mengeluh aroma parfum mereka cepat pudar dalam beberapa jam. Padahal, ketahanan parfum (longevity) tidak hanya bergantung pada konsentrasi alkohol dan minyak wangi, melainkan juga pada kondisi kulit dan cara pengaplikasian.\n\n1. Lembapkan Kulit Sebelum Menyemprot: Kulit yang kering menyerap minyak wangi lebih cepat sehingga aroma tidak bertahan lama. Gunakan lotion tanpa aroma (unscented moisturizer) pada titik nadi sebelum menyemprotkan parfum.\n\n2. Jangan Menggosok Pergelangan Tangan: Menggosok pergelangan tangan akan merusak molekul top notes dan mempercepat penguapan aroma.\n\n3. Titik Semprot yang Ideal: Semprotkan pada titik nadi hangat seperti leher samping, belakang telinga, pergelangan tangan bagian dalam, dan lipatan siku.",
                'cover_image' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=800&q=80',
                'reading_time_minutes' => 4,
                'is_trending' => true,
                'published_at' => now()->subDays(2),
                'related_product_ids' => $relatedIds,
            ],
            [
                'topic_id' => $topicTrends->id,
                'title' => 'Tren Wewangian 2026: Kebangkitan Aroma Woody & Gourmand Elegan',
                'slug' => 'tren-wewangian-2026-woody-gourmand',
                'summary' => 'Eksplorasi perpaduan aroma kayu hangat dengan sentuhan manis vanilla bourbon dan rempah eksotis.',
                'content' => "Tahun 2026 ditandai dengan pergeseran preferensi pecinta wewangian ke arah aroma yang memberikan kenyamanan (comfort scents). Kombinasi aroma kayu oud, cedarwood, dan vetiver yang dipadukan dengan vanilla manis atau rum menghasilkan aroma yang hangat, menenangkan, sekaligus misterius dan memikat.",
                'cover_image' => 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=800&q=80',
                'reading_time_minutes' => 5,
                'is_trending' => true,
                'published_at' => now()->subDays(5),
                'related_product_ids' => $relatedIds,
            ],
        ];

        foreach ($articles as $art) {
            BeautyArticle::updateOrCreate(['slug' => $art['slug']], $art);
        }

        // 10. Sample Notifications
        Notification::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Voucher Eksklusif Menanti Anda'],
            [
                'type' => 'promo',
                'message' => 'Gunakan kode AROMA20 untuk diskon 20% pesanan Anda hari ini!',
                'data' => ['code' => 'AROMA20'],
                'read_at' => null,
            ]
        );
    }
}


<?php

namespace Database\Seeders;

use App\Enums\ProductKind;
use App\Enums\ProductType;
use App\Models\Article;
use App\Models\Category;
use App\Models\NewsItem;
use App\Models\Offer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Seed the marketplace catalog.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Phones', 'name_ar' => 'هواتف', 'slug' => 'phones', 'image' => 'images/categories/phone.jpg', 'blurb' => 'Flagships and everyday 5G', 'blurb_ar' => 'هواتف رائدة ويومية بشبكة 5G', 'sort' => 1],
            ['name' => 'Computers', 'name_ar' => 'حواسيب', 'slug' => 'computers', 'blurb' => 'Laptops for study and work', 'blurb_ar' => 'حواسيب محمولة للدراسة والعمل', 'sort' => 2],
            ['name' => 'Audio', 'name_ar' => 'صوتيات', 'slug' => 'audio', 'blurb' => 'Headphones, watches, speakers', 'blurb_ar' => 'سماعات وساعات ومكبرات صوت', 'sort' => 3],
            ['name' => 'Fashion', 'name_ar' => 'أزياء', 'slug' => 'fashion', 'blurb' => 'Cairo heat, sorted', 'blurb_ar' => 'مناسبة لحر القاهرة', 'sort' => 4],
            ['name' => 'Home', 'name_ar' => 'منزل', 'slug' => 'home', 'blurb' => 'Kitchen and cotton', 'blurb_ar' => 'مطبخ وقطن', 'sort' => 5],
            ['name' => 'Beauty', 'name_ar' => 'عناية', 'slug' => 'beauty',  'blurb' => 'Skin and scent', 'blurb_ar' => 'بشرة وعطر', 'sort' => 6],
            ['name' => 'Pantry', 'name_ar' => 'مؤن', 'slug' => 'pantry', 'blurb' => 'Dates, oil, gifts', 'blurb_ar' => 'بلح وزيت وهدايا', 'sort' => 7],
            ['name' => 'Sports', 'name_ar' => 'رياضة', 'slug' => 'sports', 'blurb' => 'Move, commute, train', 'blurb_ar' => 'حركة وتنقّل وتمرين', 'sort' => 8],
            ['name' => 'Books', 'name_ar' => 'كتب', 'slug' => 'books',  'blurb' => 'Stories and study', 'blurb_ar' => 'قصص ودراسة', 'sort' => 9],
            ['name' => 'Courses', 'name_ar' => 'دورات', 'slug' => 'courses','blurb' => 'Learn at your pace', 'blurb_ar' => 'تعلّم وفق وتيرتك', 'sort' => 10],
        ];

        $ids = [];

        foreach ($categories as $category) {
            $ids[$category['slug']] = Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $this->translations($category, ['name', 'blurb']),
            )->id;
        }

        $products = [
            [
                'category' => 'phones',
                'name' => 'Samsung Galaxy A56 5G',
                'slug' => 'samsung-galaxy-a56',
                'brand' => 'Samsung',
                'price' => 18499,
                'compare_price' => 20999,
                'rating' => 4.6,
                'reviews_count' => 1284,
                'stock' => 32,
                'badge' => 'express',
                'image' => 'images/catalog/phone.jpg',
                'is_featured' => true,
                'is_deal' => true,
                'description' => 'A bright 6.7-inch phone with a 50MP camera and a battery that gets through a Cairo workday plus the commute home. Official warranty, dual SIM, and 5G on local networks.',
                'highlights' => ['6.7" Super AMOLED', '50MP main camera', '5,000 mAh battery', 'Official warranty in Egypt'],
            ],
            [
                'category' => 'phones',
                'name' => 'Apple iPhone 16',
                'slug' => 'apple-iphone-16',
                'brand' => 'Apple',
                'price' => 52990,
                'compare_price' => 55990,
                'rating' => 4.8,
                'reviews_count' => 864,
                'stock' => 14,
                'badge' => 'bestseller',
                'image' => 'images/catalog/iphone.jpg',
                'is_featured' => true,
                'is_deal' => false,
                'description' => 'The everyday iPhone, ready for FaceTime with family abroad and a full camera roll from Alexandria weekends. Ships unlocked for Egyptian carriers.',
                'highlights' => ['A18 chip', 'All-day battery', 'USB-C', 'Unlocked, dual SIM ready'],
            ],
            [
                'category' => 'phones',
                'name' => 'Xiaomi Redmi Note 14',
                'slug' => 'xiaomi-redmi-note-14',
                'brand' => 'Xiaomi',
                'price' => 8999,
                'compare_price' => 10499,
                'rating' => 4.4,
                'reviews_count' => 2104,
                'stock' => 48,
                'badge' => 'deal',
                'image' => 'images/catalog/redmi.jpg',
                'is_featured' => false,
                'is_deal' => true,
                'description' => 'A large screen and a patient battery for students and first smartphones. Fast charging and a case-friendly body.',
                'highlights' => ['120Hz display', 'Fast charging', 'Dual SIM', 'Expandable storage'],
            ],
            [
                'category' => 'computers',
                'name' => 'Lenovo IdeaPad Slim 14',
                'slug' => 'lenovo-ideapad-slim-14',
                'brand' => 'Lenovo',
                'price' => 24990,
                'compare_price' => 27490,
                'rating' => 4.5,
                'reviews_count' => 412,
                'stock' => 11,
                'badge' => 'express',
                'image' => 'images/catalog/laptop.jpg',
                'is_featured' => true,
                'is_deal' => true,
                'description' => 'Light enough for the metro, quick enough for lectures, sheets, and a few browser tabs too many. Includes a year of local service.',
                'highlights' => ['14" full HD', '16GB memory', '512GB SSD', 'Backlit keyboard'],
            ],
            [
                'category' => 'computers',
                'name' => 'Apple MacBook Air 13',
                'slug' => 'apple-macbook-air-13',
                'brand' => 'Apple',
                'price' => 54990,
                'compare_price' => null,
                'rating' => 4.9,
                'reviews_count' => 306,
                'stock' => 7,
                'badge' => 'express',
                'image' => 'images/catalog/macbook.jpg',
                'is_featured' => true,
                'is_deal' => false,
                'description' => 'Silent, thin, and happy on a café table in Zamalek for a full afternoon. Apple silicon with all-day battery.',
                'highlights' => ['M3 chip', '13.6" Liquid Retina', '18-hour battery', 'Fanless design'],
            ],
            [
                'category' => 'audio',
                'name' => 'Sony WH-CH720N Headphones',
                'slug' => 'sony-wh-ch720n',
                'brand' => 'Sony',
                'price' => 4299,
                'compare_price' => 5499,
                'rating' => 4.7,
                'reviews_count' => 980,
                'stock' => 26,
                'badge' => 'deal',
                'image' => 'images/catalog/headphones.jpg',
                'is_featured' => true,
                'is_deal' => true,
                'description' => 'Noise cancelling for the Ring Road and overnight buses. Lightweight, with 35 hours on a charge.',
                'highlights' => ['Noise cancelling', '35-hour battery', 'Multipoint Bluetooth', 'Fold-flat case'],
            ],
            [
                'category' => 'audio',
                'name' => 'mourymore_store Studio Watch',
                'slug' => 'mourymore_store-studio-watch',
                'brand' => 'mourymore_store',
                'price' => 1890,
                'compare_price' => 2490,
                'rating' => 4.3,
                'reviews_count' => 640,
                'stock' => 40,
                'badge' => 'bestseller',
                'image' => 'images/catalog/watch.jpg',
                'is_featured' => false,
                'is_deal' => true,
                'description' => 'Steps, sleep, and prayer-time friendly alerts on a clean round face. Water resistant for a walk along the corniche.',
                'highlights' => ['Heart rate', '5-day battery', 'Arabic and English', 'Water resistant'],
            ],
            [
                'category' => 'audio',
                'name' => 'JBL Flip Essential Speaker',
                'slug' => 'jbl-flip-essential',
                'brand' => 'JBL',
                'price' => 2799,
                'compare_price' => 3299,
                'rating' => 4.6,
                'reviews_count' => 733,
                'stock' => 19,
                'badge' => 'express',
                'image' => 'images/catalog/speaker.jpg',
                'is_featured' => false,
                'is_deal' => false,
                'description' => 'A compact speaker for rooftops and Friday gatherings. Bold sound, a strap, and dust resistance.',
                'highlights' => ['12-hour playtime', 'PartyBoost', 'IPX7', 'Portable strap'],
            ],
            [
                'category' => 'fashion',
                'name' => 'Heavyweight Cotton Tee',
                'slug' => 'heavyweight-cotton-tee',
                'brand' => 'Nile & Co',
                'price' => 449,
                'compare_price' => 590,
                'rating' => 4.5,
                'reviews_count' => 1520,
                'stock' => 80,
                'badge' => 'bestseller',
                'image' => 'images/catalog/linen.jpg',
                'is_featured' => true,
                'is_deal' => true,
                'description' => 'Egyptian cotton jersey with a heavier hand that keeps its shape after washing. Cut for warm evenings, not air-conditioned offices only.',
                'highlights' => ['100% Egyptian cotton', 'Pre-shrunk', 'Sizes S–XXL', 'Made for the heat'],
            ],
            [
                'category' => 'fashion',
                'name' => 'Court Leather Sneakers',
                'slug' => 'court-leather-sneakers',
                'brand' => 'Nile & Co',
                'price' => 1290,
                'compare_price' => 1690,
                'rating' => 4.4,
                'reviews_count' => 488,
                'stock' => 22,
                'badge' => 'express',
                'image' => 'images/catalog/sneakers.jpg',
                'is_featured' => false,
                'is_deal' => true,
                'description' => 'Clean leather sneakers that work with linen and jeans. Cushioned enough for a day between shops in Zamalek.',
                'highlights' => ['Leather upper', 'Cushioned footbed', 'Sizes 39–45', 'Rubber sole'],
            ],
            [
                'category' => 'fashion',
                'name' => 'Cairo Linen Set',
                'slug' => 'cairo-linen-set',
                'brand' => 'Nile & Co',
                'price' => 1890,
                'compare_price' => null,
                'rating' => 4.7,
                'reviews_count' => 266,
                'stock' => 15,
                'badge' => 'new',
                'image' => 'images/catalog/linen.jpg',
                'is_featured' => true,
                'is_deal' => false,
                'description' => 'A breathable linen shirt and trouser set in sand. The summer uniform from the north coast back to the city.',
                'highlights' => ['European flax', 'Relaxed fit', 'Sand colorway', 'Machine wash cold'],
            ],
            [
                'category' => 'home',
                'name' => 'Family Air Fryer 5.5L',
                'slug' => 'family-air-fryer-55',
                'brand' => 'Fresh Home',
                'price' => 3490,
                'compare_price' => 4190,
                'rating' => 4.6,
                'reviews_count' => 1102,
                'stock' => 18,
                'badge' => 'deal',
                'image' => 'images/catalog/olive.jpg',
                'is_featured' => true,
                'is_deal' => true,
                'description' => 'Enough room for a family tray of potatoes or chicken without heating the whole kitchen. Simple dials, easy basket.',
                'highlights' => ['5.5 litre basket', '60-minute timer', 'Non-stick', 'Recipe card in Arabic and English'],
            ],
            [
                'category' => 'home',
                'name' => 'Morning Espresso Maker',
                'slug' => 'morning-espresso-maker',
                'brand' => 'Fresh Home',
                'price' => 2190,
                'compare_price' => 2690,
                'rating' => 4.2,
                'reviews_count' => 390,
                'stock' => 0,
                'badge' => 'express',
                'image' => 'images/catalog/coffee.jpg',
                'is_featured' => false,
                'is_deal' => false,
                'description' => 'A compact machine for a proper cup before the commute. Milk frother included. Currently waiting on the next Cairo shipment.',
                'highlights' => ['15-bar pump', 'Milk frother', 'Compact footprint', '1-year service'],
            ],
            [
                'category' => 'home',
                'name' => 'Giza Cotton Sheet Set',
                'slug' => 'giza-cotton-sheet-set',
                'brand' => 'Nile & Co',
                'price' => 1590,
                'compare_price' => 1990,
                'rating' => 4.8,
                'reviews_count' => 754,
                'stock' => 27,
                'badge' => 'bestseller',
                'image' => 'images/catalog/sheets.jpg',
                'is_featured' => true,
                'is_deal' => true,
                'description' => 'Percale sheets woven from long-staple Egyptian cotton. Cool to the touch, in a stone color that hides city dust a little longer.',
                'highlights' => ['Long-staple cotton', 'King and queen', 'Percale weave', 'OEKO-TEX dyes'],
            ],
            [
                'category' => 'beauty',
                'name' => 'Nile Mist Skincare Set',
                'slug' => 'nile-mist-skincare-set',
                'brand' => 'Nile Mist',
                'price' => 690,
                'compare_price' => 860,
                'rating' => 4.5,
                'reviews_count' => 512,
                'stock' => 36,
                'badge' => 'deal',
                'image' => 'images/catalog/skincare.jpg',
                'is_featured' => false,
                'is_deal' => true,
                'description' => 'Cleanser, serum, and a light moisturizer formulated for dry air-conditioning and hard water. Unscented, everyday routine.',
                'highlights' => ['Three-step set', 'Fragrance free', 'For combination skin', 'Made for hard water'],
            ],
            [
                'category' => 'beauty',
                'name' => 'Amber Souk Eau de Parfum',
                'slug' => 'amber-souk-eau-de-parfum',
                'brand' => 'Nile Mist',
                'price' => 890,
                'compare_price' => null,
                'rating' => 4.7,
                'reviews_count' => 298,
                'stock' => 21,
                'badge' => 'new',
                'image' => 'images/catalog/perfume.jpg',
                'is_featured' => true,
                'is_deal' => false,
                'description' => 'Amber, bitter orange, and a soft wood base. A 50ml bottle that feels like evening in Khan el-Khalili without shouting.',
                'highlights' => ['50ml', 'Amber and orange', 'Long wear', 'Gift box'],
            ],
            [
                'category' => 'pantry',
                'name' => 'Siwa Dates Gift Box',
                'slug' => 'siwa-dates-gift-box',
                'brand' => 'Wahat',
                'price' => 320,
                'compare_price' => 390,
                'rating' => 4.9,
                'reviews_count' => 860,
                'stock' => 60,
                'badge' => 'bestseller',
                'image' => 'images/catalog/dates.jpg',
                'is_featured' => true,
                'is_deal' => true,
                'description' => 'Soft Siwa dates, packed in a box you can take to dinner. Naturally sweet, no syrup added.',
                'highlights' => ['500g', 'Siwa oasis', 'No added syrup', 'Gift-ready box'],
            ],
            [
                'category' => 'pantry',
                'name' => 'Extra Virgin Olive Oil 750ml',
                'slug' => 'extra-virgin-olive-oil',
                'brand' => 'Wahat',
                'price' => 245,
                'compare_price' => null,
                'rating' => 4.6,
                'reviews_count' => 430,
                'stock' => 54,
                'badge' => 'express',
                'image' => 'images/catalog/olive.jpg',
                'is_featured' => false,
                'is_deal' => false,
                'description' => 'A peppery early-harvest oil for salads, ful, and finishing a pan. Dark glass bottle, 750ml.',
                'highlights' => ['750ml', 'Cold extracted', 'Dark glass', 'Harvest dated'],
            ],
            [
                'category' => 'sports',
                'name' => 'Match Football Size 5',
                'slug' => 'match-football-size-5',
                'brand' => 'mourymore_store Sport',
                'price' => 390,
                'compare_price' => 480,
                'rating' => 4.4,
                'reviews_count' => 670,
                'stock' => 44,
                'badge' => 'deal',
                'image' => 'images/catalog/football.jpg',
                'is_featured' => false,
                'is_deal' => true,
                'description' => 'A size 5 ball with a durable outer for street pitches and club training. Ships inflated-ready, with a needle in the box.',
                'highlights' => ['Size 5', 'Machine stitched', 'Street-tough cover', 'Pump needle included'],
            ],
            [
                'category' => 'sports',
                'name' => 'Commute Daypack 20L',
                'slug' => 'commute-daypack-20l',
                'brand' => 'mourymore_store Sport',
                'price' => 790,
                'compare_price' => 990,
                'rating' => 4.5,
                'reviews_count' => 355,
                'stock' => 28,
                'badge' => 'express',
                'image' => 'images/catalog/backpack.jpg',
                'is_featured' => true,
                'is_deal' => false,
                'description' => 'A 20-litre pack with a padded laptop sleeve and a water-bottle pocket. Sits close so it works on a crowded metro.',
                'highlights' => ['Fits 15" laptop', '20 litres', 'Padded straps', 'Water-resistant'],
            ],
        ];

        $arabic = [
            'samsung-galaxy-a56' => [
                'name_ar' => 'سامسونج جالاكسي A56 5G',
                'description_ar' => 'شاشة 6.7 بوصة واضحة، وكاميرا 50 ميغابكسل، وبطارية تكفي يوم عمل في القاهرة والعودة إلى المنزل. ضمان رسمي، وشريحتان، وشبكة 5G على الشبكات المحلية.',
                'highlights_ar' => ['شاشة سوبر أموليد 6.7 بوصة', 'كاميرا 50 ميغابكسل', 'بطارية 5000 مللي أمبير', 'ضمان رسمي في مصر'],
            ],
            'apple-iphone-16' => [
                'name_ar' => 'آيفون 16',
                'description_ar' => 'آيفون للاستخدام اليومي، جاهز لمكالمات العائلة وللتصوير في الإسكندرية. يصل مفتوحًا لشبكات مصر.',
                'highlights_ar' => ['شريحة A18', 'بطارية تدوم طوال اليوم', 'USB-C', 'مفتوح ويدعم شريحتين'],
            ],
            'xiaomi-redmi-note-14' => [
                'name_ar' => 'شاومي ريدمي نوت 14',
                'description_ar' => 'شاشة كبيرة وبطارية مناسبة للطلبة ولأول هاتف. شحن سريع وجسم مريح مع الغلاف.',
                'highlights_ar' => ['شاشة 120 هرتز', 'شحن سريع', 'شريحتان', 'ذاكرة قابلة للتوسيع'],
            ],
            'lenovo-ideapad-slim-14' => [
                'name_ar' => 'لينوفو آيديا باد سليم 14',
                'description_ar' => 'خفيف في المترو، وسريع للمحاضرات والجداول وتبويبات المتصفح الكثيرة. مع سنة خدمة محلية.',
                'highlights_ar' => ['شاشة 14 بوصة فل إتش دي', 'ذاكرة 16 جيجابايت', 'قرص 512 جيجابايت', 'لوحة مفاتيح مضيئة'],
            ],
            'apple-macbook-air-13' => [
                'name_ar' => 'ماك بوك إير 13',
                'description_ar' => 'هادئ ورفيع، مناسب لعمل بعد الظهر في مقهى بالزمالك. معالج آبل وبطارية تدوم طوال اليوم.',
                'highlights_ar' => ['شريحة M3', 'شاشة 13.6 ليكويد ريتينا', 'بطارية 18 ساعة', 'بدون مروحة'],
            ],
            'sony-wh-ch720n' => [
                'name_ar' => 'سماعة سوني WH-CH720N',
                'description_ar' => 'عزل للضوضاء مناسب للطريق الدائري ورحلات الليل. خفيفة، و35 ساعة من التشغيل.',
                'highlights_ar' => ['عزل للضوضاء', 'بطارية 35 ساعة', 'بلوتوث لأكثر من جهاز', 'تُطوى في الحقيبة'],
            ],
            'mourymore_store-studio-watch' => [
                'name_ar' => 'ساعة mourymore_store ستوديو',
                'description_ar' => 'خطوات ونوم وتنبيهات تناسب أوقات الصلاة على واجهة نظيفة. مقاومة للماء في نزهة الكورنيش.',
                'highlights_ar' => ['نبض القلب', 'بطارية 5 أيام', 'عربي وإنجليزي', 'مقاومة للماء'],
            ],
            'jbl-flip-essential' => [
                'name_ar' => 'مكبر صوت JBL فليب',
                'description_ar' => 'مكبر صوت صغير للطاولات ولقاءات الجمعة. صوت قوي وحزام ومقاومة للغبار.',
                'highlights_ar' => ['تشغيل 12 ساعة', 'PartyBoost', 'IPX7', 'حزام حمل'],
            ],
            'heavyweight-cotton-tee' => [
                'name_ar' => 'تيشيرت قطن ثقيل',
                'description_ar' => 'جيرسي من القطن المصري، قوامه أثقل وشكله ثابت بعد الغسيل. قصة مناسبة لليل الدافئ، لا للمكاتب المكيفة فحسب.',
                'highlights_ar' => ['قطن مصري 100%', 'لا ينكمش', 'مقاسات من S إلى XXL', 'مناسب للحر'],
            ],
            'court-leather-sneakers' => [
                'name_ar' => 'حذاء رياضي جلدي',
                'description_ar' => 'جلد نظيف يتناسب مع الكتان والجينز. مبطّن بما يكفي ليوم بين محال الزمالك.',
                'highlights_ar' => ['وجه جلد', 'نعل داخلي مبطّن', 'مقاسات 39–45', 'نعل مطاط'],
            ],
            'cairo-linen-set' => [
                'name_ar' => 'طقم كتان القاهرة',
                'description_ar' => 'قميص وبنطلون كتان خفيف بلون رملي. زي صيفي من الساحل إلى المدينة.',
                'highlights_ar' => ['كتان', 'قصة مريحة', 'لون رملي', 'غسيل بارد'],
            ],
            'family-air-fryer-55' => [
                'name_ar' => 'قلاية هوائية 5.5 لتر',
                'description_ar' => 'تكفي صينية بطاطس أو دجاج للعائلة دون تسخين المطبخ كله. أزرار بسيطة وسلة سهلة الاستخدام.',
                'highlights_ar' => ['سلة 5.5 لتر', 'مؤقت 60 دقيقة', 'طبقة غير لاصقة', 'بطاقة وصفات بالعربية والإنجليزية'],
            ],
            'morning-espresso-maker' => [
                'name_ar' => 'ماكينة إسبريسو الصباح',
                'description_ar' => 'ماكينة صغيرة لفنجان جيد قبل التنقّل. معها مضرب لبن. الشحنة التالية من القاهرة ما زالت في الطريق.',
                'highlights_ar' => ['مضخة 15 بار', 'مضرب لبن', 'حجم صغير', 'خدمة سنة'],
            ],
            'giza-cotton-sheet-set' => [
                'name_ar' => 'طقم ملايات قطن جيزة',
                'description_ar' => 'ملايات بيركال من قطن مصري طويل التيلة. باردة على الجسم، ولون حجري يخفف أثر غبار المدينة.',
                'highlights_ar' => ['قطن طويل التيلة', 'كينج وكوين', 'نسج بيركال', 'أصباغ OEKO-TEX'],
            ],
            'nile-mist-skincare-set' => [
                'name_ar' => 'مجموعة نايل ميست للبشرة',
                'description_ar' => 'غسول ومصل ومرطب خفيف لهواء التكييف والماء العسر. بلا عطر، روتين يومي.',
                'highlights_ar' => ['ثلاث خطوات', 'بلا عطر', 'للبشرة المختلطة', 'مناسبة للماء العسر'],
            ],
            'amber-souk-eau-de-parfum' => [
                'name_ar' => 'عنبر السوق',
                'description_ar' => 'عنبر وبرتقال مرّ وقاعدة خشبية هادئة. زجاجة 50 مل تذكّر بمساء خان الخليلي دون أن تعلو نبرتها.',
                'highlights_ar' => ['50 مل', 'عنبر وبرتقال', 'ثبات طويل', 'علبة هدية'],
            ],
            'siwa-dates-gift-box' => [
                'name_ar' => 'علبة بلح سيوة',
                'description_ar' => 'بلح سيوة طري في علبة يمكنك اصطحابها إلى العشاء. حلاوة طبيعية بلا شراب زائد.',
                'highlights_ar' => ['500 جم', 'واحة سيوة', 'بلا شراب', 'علبة هدية'],
            ],
            'extra-virgin-olive-oil' => [
                'name_ar' => 'زيت زيتون بكر ممتاز 750 مل',
                'description_ar' => 'زيت أول الموسم فيه حدة خفيفة، للسلطة والفول ونهاية الطهي. زجاجة غامقة 750 مل.',
                'highlights_ar' => ['750 مل', 'عصر على البارد', 'زجاج غامق', 'تاريخ الحصاد'],
            ],
            'match-football-size-5' => [
                'name_ar' => 'كرة قدم مقاس 5',
                'description_ar' => 'كرة مقاس 5 بغطاء يتحمل الملاعب والتمرين. تصل جاهزة للنفخ ومعها إبرة.',
                'highlights_ar' => ['مقاس 5', 'خياطة آلية', 'غطاء يتحمل الشارع', 'إبرة نفخ'],
            ],
            'commute-daypack-20l' => [
                'name_ar' => 'حقيبة يومية 20 لتر',
                'description_ar' => 'حقيبة 20 لتر بجيب محمول مبطّن وجيب لزجاجة الماء. قريبة من الجسم لتناسب زحام المترو.',
                'highlights_ar' => ['تناسب حاسوبًا محمولًا 15 بوصة', '20 لتر', 'أشرطة مبطّنة', 'مقاومة للماء'],
            ],
        ];

        foreach ($products as $product) {
            $category = $product['category'];
            unset($product['category']);
            $product['category_id'] = $ids[$category];
            $product = array_merge($product, $arabic[$product['slug']]);
            $product = $this->translations($product, ['name', 'description', 'highlights']);

            if (is_string($product['brand'] ?? null)) {
                $product['brand'] = ['en' => $product['brand'], 'ar' => $product['brand']];
            }

            $product['kind'] = ProductKind::Product;

            Product::query()->updateOrCreate(
                ['slug' => $product['slug']],
                $product,
            );
        }

        $book = Product::query()->updateOrCreate(
            ['slug' => 'nile-stories'],
            $this->translations([
                'category_id' => $ids['books'],
                'type' => ProductType::Physical,
                'kind' => ProductKind::Book,
                'name' => 'Nile Stories',
                'name_ar' => 'حكايات النيل',
                'brand' => 'mourymore_store',
                'description' => 'Short stories set between Cairo balconies and a slow train south. A paperback you can pack for the ride.',
                'description_ar' => 'قصص قصيرة بين شرفات القاهرة وقطار هادئ نحو الجنوب. كتاب ورقي يصلح للطريق.',
                'highlights' => ['Paperback', '240 pages', 'Arabic and English notes'],
                'highlights_ar' => ['غلاف ورقي', '240 صفحة', 'هوامش بالعربي والإنجليزي'],
                'price' => 220,
                'compare_price' => 280,
                'rating' => 4.7,
                'reviews_count' => 86,
                'stock' => 40,
                'badge' => null,
                'image' => 'images/departments/books.jpg',
                'is_featured' => true,
                'is_deal' => false,
            ], ['name', 'brand', 'description', 'highlights']),
        );

        $book->bookDetail()->updateOrCreate(
            ['product_id' => $book->id],
            $this->translations([
                'author' => 'Hana Farid',
                'author_ar' => 'هنا فريد',
                'pages' => 240,
                'file_path' => null,
            ], ['author']),
        );

        $course = Product::query()->updateOrCreate(
            ['slug' => 'home-kitchen-basics'],
            $this->translations([
                'category_id' => $ids['courses'],
                'type' => ProductType::Digital,
                'kind' => ProductKind::Course,
                'name' => 'Home Kitchen Basics',
                'name_ar' => 'أساسيات مطبخ البيت',
                'brand' => 'mourymore_store',
                'description' => 'A short course on weeknight cooking with a Cairo pantry. Watch the lessons whenever you want.',
                'description_ar' => 'دورة قصيرة في طبخ أيام الأسبوع بمؤن المنزل في القاهرة. تُتاح الدروس في أي وقت.',
                'highlights' => ['6 lessons', 'Watch anytime', 'No shipping'],
                'highlights_ar' => ['6 دروس', 'المشاهدة في أي وقت', 'دون شحن'],
                'price' => 350,
                'compare_price' => null,
                'rating' => 4.8,
                'reviews_count' => 42,
                'stock' => 0,
                'badge' => null,
                'image' => 'images/catalog/olive.jpg',
                'is_featured' => true,
                'is_deal' => false,
            ], ['name', 'brand', 'description', 'highlights']),
        );

        Product::query()->updateOrCreate(
            ['slug' => 'cairo-balcony-recipes'],
            $this->translations([
                'category_id' => $ids['books'],
                'type' => ProductType::Physical,
                'kind' => ProductKind::Book,
                'name' => 'Cairo Balcony Recipes',
                'name_ar' => 'وصفات بلكونة القاهرة',
                'brand' => 'mourymore_store',
                'description' => 'A small cookbook for a two-burner kitchen: lentils, rice, and the salad that stands in for dinner.',
                'description_ar' => 'كتاب صغير لمطبخ بموقدين: عدس وأرز وسلطة تكفي عشاءً.',                'highlights' => ['Paperback', '160 pages', 'Weeknight recipes'],
                'highlights_ar' => ['غلاف ورقي', '160 صفحة', 'وصفات أيام الأسبوع'],
                'price' => 180,
                'compare_price' => 220,
                'rating' => 4.6,
                'reviews_count' => 54,
                'stock' => 25,
                'badge' => null,
                'image' => 'images/departments/books.jpg',
                'is_featured' => false,
                'is_deal' => true,
            ], ['name', 'brand', 'description', 'highlights']),
        );

        Product::query()->updateOrCreate(
            ['slug' => 'phone-photos-course'],
            $this->translations([
                'category_id' => $ids['courses'],
                'type' => ProductType::Digital,
                'kind' => ProductKind::Course,
                'name' => 'Phone Photos for Selling',
                'name_ar' => 'تصوير الهاتف للبيع',
                'brand' => 'mourymore_store',
                'description' => 'Short lessons on photographing a product with the phone you already have, in daylight by a window.',
                'description_ar' => 'دروس قصيرة لتصوير المنتج بالهاتف، في ضوء النافذة.',
                'highlights' => ['5 lessons', 'Watch anytime', 'No shipping'],
                'highlights_ar' => ['5 دروس', 'المشاهدة في أي وقت', 'دون شحن'],
                'price' => 250,
                'compare_price' => null,
                'rating' => 4.5,
                'reviews_count' => 31,
                'stock' => 0,
                'badge' => null,
                'image' => 'images/catalog/phone.jpg',
                'is_featured' => false,
                'is_deal' => false,
            ], ['name', 'brand', 'description', 'highlights']),
        );

        $reading = Product::query()->updateOrCreate(
            ['slug' => 'evening-reading'],
            $this->translations([
                'category_id' => $ids['books'],
                'type' => ProductType::Physical,
                'kind' => ProductKind::Book,
                'name' => 'An Evening of Reading',
                'name_ar' => 'سهرة قراءة',
                'brand' => 'mourymore_store',
                'description' => 'A short paperback for the hour after dinner. Printed, and shipped from Cairo.',
                'description_ar' => 'كتاب ورقي قصير لساعة ما بعد العشاء. يُطبع ويُشحن من القاهرة.',
                'highlights' => ['Paperback', '128 pages', 'Ships from Cairo'],
                'highlights_ar' => ['غلاف ورقي', '128 صفحة', 'يُشحن من القاهرة'],
                'price' => 150,
                'compare_price' => 190,
                'rating' => 4.4,
                'reviews_count' => 28,
                'stock' => 30,
                'badge' => null,
                'image' => 'images/catalog/coffee.jpg',
                'is_featured' => false,
                'is_deal' => true,
            ], ['name', 'brand', 'description', 'highlights']),
        );

        $reading->bookDetail()->updateOrCreate(
            ['product_id' => $reading->id],
            $this->translations([
                'author' => 'Hana Farid',
                'author_ar' => 'هنا فريد',
                'pages' => 128,
                'file_path' => null,
            ], ['author']),
        );

        $styling = Product::query()->updateOrCreate(
            ['slug' => 'living-room-course'],
            $this->translations([
                'category_id' => $ids['courses'],
                'type' => ProductType::Digital,
                'kind' => ProductKind::Course,
                'name' => 'A Living Room That Works',
                'name_ar' => 'غرفة معيشة عملية',
                'brand' => 'mourymore_store',
                'description' => 'Short lessons on arranging a Cairo apartment living room with the furniture you already own.',
                'description_ar' => 'دروس قصيرة لترتيب غرفة المعيشة في شقة بالقاهرة بالأثاث المتوفر لديك.',
                'highlights' => ['4 lessons', 'Watch anytime', 'No shipping'],
                'highlights_ar' => ['4 دروس', 'المشاهدة في أي وقت', 'دون شحن'],
                'price' => 290,
                'compare_price' => null,
                'rating' => 4.6,
                'reviews_count' => 19,
                'stock' => 0,
                'badge' => null,
                'image' => 'images/catalog/sheets.jpg',
                'is_featured' => false,
                'is_deal' => false,
            ], ['name', 'brand', 'description', 'highlights']),
        );

        $styling->lessons()->delete();

        foreach ([
            ['title' => 'Measure the room first', 'title_ar' => 'قِس الغرفة أولًا', 'duration_minutes' => 8, 'is_preview' => true],
            ['title' => 'A sofa and a path', 'title_ar' => 'أريكة وممر', 'duration_minutes' => 14, 'is_preview' => false],
            ['title' => 'Light after sunset', 'title_ar' => 'الإضاءة بعد الغروب', 'duration_minutes' => 11, 'is_preview' => false],
            ['title' => 'What to leave out', 'title_ar' => 'ما يُستبعد من الغرفة', 'duration_minutes' => 9, 'is_preview' => false],
        ] as $index => $lesson) {
            $styling->lessons()->create([
                ...$this->translations($lesson, ['title']),
                'sort' => $index + 1,
            ]);
        }

        $course->lessons()->delete();

        foreach ([
            ['title' => 'A pantry that lasts the week', 'title_ar' => 'مؤن تكفي الأسبوع', 'duration_minutes' => 12, 'is_preview' => true],
            ['title' => 'One pan for two people', 'title_ar' => 'مقلاة واحدة لشخصين', 'duration_minutes' => 18, 'is_preview' => false],
            ['title' => 'Rice, lentils, and leftovers', 'title_ar' => 'أرز وعدس وبقايا الطعام', 'duration_minutes' => 16, 'is_preview' => false],
            ['title' => 'A salad that is actually dinner', 'title_ar' => 'سلطة تكفي عشاءً', 'duration_minutes' => 11, 'is_preview' => false],
            ['title' => 'Breakfast before the commute', 'title_ar' => 'فطور قبل التنقّل', 'duration_minutes' => 9, 'is_preview' => false],
            ['title' => 'Packing lunch that survives the bag', 'title_ar' => 'وجبة غداء تتحمّل الحقيبة', 'duration_minutes' => 14, 'is_preview' => false],
        ] as $index => $lesson) {
            $course->lessons()->create([
                ...$this->translations($lesson, ['title']),
                'sort' => $index + 1,
            ]);
        }

        Offer::query()->updateOrCreate(
            ['code' => strtoupper((string) config('store.promo_code', 'MOURYMORE_STORE10'))],
            $this->translations([
                'name' => 'Welcome 10%',
                'name_ar' => 'ترحيب 10٪',
                'type' => 'percent',
                'value' => ((float) config('store.promo_rate', 0.10)) * 100,
                'min_subtotal' => null,
                'max_uses' => null,
                'is_active' => true,
                'sort' => 1,
            ], ['name']),
        );

        PaymentMethod::query()->updateOrCreate(['code' => 'cod'], $this->translations([
            'name' => 'Cash payment',
            'name_ar' => 'الدفع نقدًا',
            'hint' => 'Pay the courier when the order arrives.',
            'hint_ar' => 'ادفع للمندوب عند وصول الطلب.',
            'collects_on_delivery' => true,
            'is_active' => false,
            'sort' => 99,
        ], ['name', 'hint']));

        PaymentMethod::query()->updateOrCreate(['code' => 'paymob'], $this->translations([
            'name' => 'Paymob',
            'name_ar' => 'باي موب',
            'hint' => 'Card or wallet. Stock is held until the payment succeeds.',
            'hint_ar' => 'بطاقة أو محفظة. تُحجز الكمية حتى ينجح الدفع.',
            'collects_on_delivery' => false,
            'is_active' => true,
            'sort' => 1,
        ], ['name', 'hint']));

        ShippingMethod::query()->updateOrCreate(['code' => 'greater_cairo'], $this->translations([
            'name' => 'Greater Cairo',
            'name_ar' => 'القاهرة الكبرى',
            'eta' => 'Same day or next day',
            'eta_ar' => 'اليوم نفسه أو اليوم التالي',
            'fee' => 45,
            'free_over' => 1500,
            'governorates' => config('store.greater_cairo'),
            'is_active' => true,
            'sort' => 1,
        ], ['name', 'eta']));

        ShippingMethod::query()->updateOrCreate(['code' => 'delta'], $this->translations([
            'name' => 'Delta and Alexandria',
            'name_ar' => 'الدلتا والإسكندرية',
            'eta' => '2–3 days',
            'eta_ar' => 'من يومين لثلاثة',
            'fee' => 65,
            'free_over' => null,
            'governorates' => config('store.delta'),
            'is_active' => true,
            'sort' => 2,
        ], ['name', 'eta']));

        ShippingMethod::query()->updateOrCreate(['code' => 'other'], $this->translations([
            'name' => 'Rest of Egypt',
            'name_ar' => 'باقي المحافظات',
            'eta' => '3–5 days',
            'eta_ar' => 'من 3 إلى 5 أيام',
            'fee' => 90,
            'free_over' => null,
            'governorates' => [],
            'is_active' => true,
            'sort' => 3,
        ], ['name', 'eta']));

        $this->seedContent();

        $this->call(PageSeeder::class);
        $this->seedSiteSettings();

        $this->command?->call('site-content:sync');
    }

    private function seedSiteSettings(): void
    {
        if (SiteSetting::query()->exists()) {
            return;
        }

        SiteSetting::query()->create(SiteSetting::defaultAttributes());
    }

    private function seedContent(): void
    {
        Article::query()->where('slug', 'cash-on-delivery-egypt')->delete();

        Article::query()->updateOrCreate(['slug' => 'paying-at-mourymore-store'], $this->translations([
            'title' => 'How to pay at mourymore_store',
            'title_ar' => 'طرق الدفع في mourymore_store',
            'excerpt' => 'InstaPay, Vodafone Cash, Fawry, and cards. Here is what happens after you place the order.',
            'excerpt_ar' => 'إنستاباي وفودافون كاش وفوري والبطاقات. هذا ما يحدث بعد تأكيد الطلب.',
            'body' => "At checkout, choose InstaPay, Vodafone Cash, Fawry, or a bank card. We hold the item while the payment is confirmed, then hand it to the courier for your governorate.\n\nYou pay through the method you picked. Card numbers are never stored on mourymore_store.\n\nCairo and Giza orders placed before 2:00 PM can go out the same day. Other governorates follow the delivery window shown at checkout.",
            'body_ar' => "عند إتمام الشراء اختر إنستاباي أو فودافون كاش أو فوري أو البطاقة البنكية. تُحجز الكمية حتى يتأكد الدفع، ثم يُسلَّم الطلب إلى المندوب بحسب محافظتك.\n\nتدفع بالطريقة التي اخترتها. لا تُحفظ أرقام البطاقات على mourymore_store.\n\nطلبات القاهرة والجيزة المسجَّلة قبل الساعة الثانية ظهرًا يمكن أن تخرج في اليوم نفسه. باقي المحافظات تتبع مدة التوصيل الظاهرة عند الدفع.",
            'meta_title' => 'Payment methods at mourymore_store',
            'meta_title_ar' => 'طرق الدفع في mourymore_store',
            'meta_description' => 'How InstaPay, wallets, Fawry, and cards work at mourymore_store after checkout.',
            'meta_description_ar' => 'كيف تعمل إنستاباي والمحافظ وفوري والبطاقات في mourymore_store بعد تأكيد الطلب.',
            'is_published' => true,
            'published_at' => now(),
        ], ['title', 'excerpt', 'body', 'meta_title', 'meta_description']));

        NewsItem::query()->updateOrCreate(['slug' => 'greater-cairo-same-day'], $this->translations([
            'title' => 'Same-day delivery is open in Greater Cairo',
            'title_ar' => 'التوصيل في نفس اليوم متاح في القاهرة الكبرى',
            'excerpt' => 'Order before 2:00 PM in Cairo or Giza and the courier can still make it today.',
            'excerpt_ar' => 'اطلب قبل الساعة الثانية ظهرًا في القاهرة أو الجيزة، ويمكن أن يصل المندوب في اليوم نفسه.',
            'body' => 'Greater Cairo orders placed before 2:00 PM stay on the same-day lane. After that, they leave the next morning. Delivery time at checkout is the promise for your governorate.',
            'body_ar' => 'تبقى طلبات القاهرة الكبرى المسجَّلة قبل الساعة الثانية ظهرًا على مسار اليوم نفسه. بعد ذلك تخرج في صباح اليوم التالي. مدة التوصيل الظاهرة عند الدفع هي المدة المعتمدة لمحافظتك.',
            'is_published' => true,
            'published_at' => now(),
        ], ['title', 'excerpt', 'body']));

        Testimonial::query()->updateOrCreate(['name' => 'Nour Hassan'], $this->translations([
            'name' => 'Nour Hassan',
            'role' => 'Dokki',
            'role_ar' => 'الدقي',
            'quote' => 'The courier called before he arrived, and the invoice matched the site. No back and forth.',
            'quote_ar' => 'اتصل المندوب قبل الوصول، والفاتورة مطابقة للموقع. دون تعقيد.',
            'rating' => 5,
            'sort' => 1,
            'is_published' => true,
        ], ['role', 'quote']));

        Testimonial::query()->updateOrCreate(['name' => 'Karim Adel'], $this->translations([
            'name' => 'Karim Adel',
            'role' => 'Alexandria',
            'role_ar' => 'الإسكندرية',
            'quote' => 'The book arrived in two days, packed flat, and the invoice matched the site.',
            'quote_ar' => 'وصل الكتاب خلال يومين، والتغليف سليم، والفاتورة مطابقة لما هو ظاهر في الموقع.',
            'rating' => 5,
            'sort' => 2,
            'is_published' => true,
        ], ['role', 'quote']));

        Testimonial::query()->updateOrCreate(['name' => 'Sara Magdy'], $this->translations([
            'name' => 'Sara Magdy',
            'role' => 'Nasr City',
            'role_ar' => 'مدينة نصر',
            'quote' => 'The book arrived the next day, and the invoice matched the price on the page.',
            'quote_ar' => 'وصل الكتاب في اليوم التالي، والفاتورة مطابقة للسعر الظاهر في الصفحة.',
            'rating' => 5,
            'sort' => 3,
            'is_published' => true,
        ], ['role', 'quote']));

        Testimonial::query()->updateOrCreate(['name' => 'Youssef Nabil'], $this->translations([
            'name' => 'Youssef Nabil',
            'role' => 'Giza',
            'role_ar' => 'الجيزة',
            'quote' => 'I paid with InstaPay, and the course opened as soon as the payment was confirmed.',
            'quote_ar' => 'دفعت عبر إنستاباي، وفُتحت الدورة فور تأكيد الدفع.',
            'rating' => 4,
            'sort' => 4,
            'is_published' => true,
        ], ['role', 'quote']));
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private function translations(array $row, array $fields): array
    {
        foreach ($fields as $field) {
            $english = $row[$field] ?? null;
            $arabic = $row[$field . '_ar'] ?? null;
            $translated = [];

            if ($english !== null && $english !== '') {
                $translated['en'] = $english;
            }

            if ($arabic !== null && $arabic !== '') {
                $translated['ar'] = $arabic;
            }

            $row[$field] = $translated;
            unset($row[$field . '_ar']);
        }

        return $row;
    }
}

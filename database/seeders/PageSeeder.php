<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'sort' => 1,
                'title' => 'Privacy Policy',
                'title_ar' => 'سياسة الخصوصية',
                'body' => <<<'TXT'
We collect only what we need to process your orders: name, phone, delivery address, email, and payment status from our providers.

We do not sell your personal data. Payment card details are handled by our payment partners and are not stored on mourymore_store servers.

You may request access, correction, or deletion of your account data by contacting support. We keep order records as required for accounting and delivery disputes.
TXT,
                'body_ar' => <<<'TXT'
نجمع فقط ما نحتاجه لتنفيذ طلباتك: الاسم، ورقم الهاتف، وعنوان التوصيل، والبريد الإلكتروني، وحالة الدفع من مزوّدي الخدمة.

لا نبيع بياناتك الشخصية. تفاصيل البطاقات تُعالج عبر شركاء الدفع ولا تُخزَّن على خوادم mourymore_store.

يمكنك طلب الاطلاع على بيانات حسابك أو تصحيحها أو حذفها عبر التواصل مع الدعم. نحتفظ بسجلات الطلبات وفق متطلبات المحاسبة ونزاعات التوصيل.
TXT,
            ],
            [
                'slug' => 'terms-of-service',
                'sort' => 2,
                'title' => 'Terms of Service',
                'title_ar' => 'الشروط والأحكام',
                'body' => <<<'TXT'
By placing an order on mourymore_store you agree to these terms. Prices are in Egyptian pounds and include applicable taxes where stated.

Digital products (courses and digital books) become available after successful payment. Physical items ship according to the delivery method selected at checkout.

We may refuse or cancel an order if stock is unavailable, payment fails, or the order looks fraudulent. Continued use of the site means you accept updates to these terms.
TXT,
                'body_ar' => <<<'TXT'
بإتمام طلب على mourymore_store فإنك توافق على هذه الشروط. الأسعار بالجنيه المصري وتشمل الضرائب حيث يُذكر ذلك.

المنتجات الرقمية (الدورات والكتب الرقمية) تُتاح بعد نجاح الدفع. المنتجات المادية تُشحن وفق طريقة التوصيل المختارة عند الدفع.

قد نرفض أو نلغي طلبًا عند نفاد المخزون أو فشل الدفع أو الاشتباه في احتيال. استمرار استخدام الموقع يعني قبول أي تحديثات لهذه الشروط.
TXT,
            ],
            [
                'slug' => 'return-policy',
                'sort' => 3,
                'title' => 'Return & Refund Policy',
                'title_ar' => 'سياسة الاسترجاع والاسترداد',
                'body' => <<<'TXT'
Physical products sold as mourymore_store Express may be returned within 14 days of delivery if unused and in original condition. We arrange pickup inside Greater Cairo when eligible.

Digital books and courses are non-returnable once access has been granted, except where required by Egyptian consumer law for a proven defect.

Refunds for approved returns are issued to the original payment method within 7–14 business days after we receive and inspect the item.
TXT,
                'body_ar' => <<<'TXT'
يمكن إرجاع المنتجات المادية المباعة عبر mourymore_store Express خلال 14 يومًا من التوصيل إذا كانت غير مستخدمة وبحالتها الأصلية. نرتّب الاستلام داخل القاهرة الكبرى عند الاستحقاق.

الكتب الرقمية والدورات غير قابلة للإرجاع بعد منح الوصول، إلا بما يفرضه قانون حماية المستهلك المصري عند ثبوت عيب.

تُرد المبالغ للطلبات المقبولة إلى وسيلة الدفع الأصلية خلال 7–14 يوم عمل بعد استلام المنتج وفحصه.
TXT,
            ],
            [
                'slug' => 'shipping-policy',
                'sort' => 4,
                'title' => 'Shipping Policy',
                'title_ar' => 'سياسة الشحن',
                'body' => <<<'TXT'
We deliver to all 27 Egyptian governorates. Greater Cairo orders placed before 2:00 PM can leave the same day when stock allows.

Shipping fees and free-shipping thresholds depend on the method shown at checkout. Delivery times are estimates and may vary during peak periods or remote areas.

You are responsible for providing a reachable phone number and accurate address. Failed delivery attempts may delay your order.
TXT,
                'body_ar' => <<<'TXT'
نوصّل إلى جميع محافظات مصر الـ 27. طلبات القاهرة الكبرى قبل الساعة 2:00 م يمكن أن تغادر في نفس اليوم عند توفر المخزون.

رسوم الشحن وحدّ التوصيل المجاني يعتمدان على الطريقة الظاهرة عند الدفع. مدد التوصيل تقديرية وقد تتغير في المواسم أو المناطق البعيدة.

أنت مسؤول عن رقم هاتف يمكن الوصول إليه وعنوان دقيق. محاولات التوصيل الفاشلة قد تؤخر طلبك.
TXT,
            ],
            [
                'slug' => 'about',
                'sort' => 5,
                'title' => 'About us',
                'title_ar' => 'من نحن',
                'body' => <<<'TXT'
mourymore_store is a marketplace built for how Egypt shops: clear EGP prices, wallet and card payments, printed books, courses that open after payment, and fast lanes across Greater Cairo.

We focus on practical products and transparent delivery — from Dokki to Upper Egypt — with support that answers in Arabic and English.
TXT,
                'body_ar' => <<<'TXT'
mourymore_store سوق مبني لطريقة تسوّق مصر: أسعار واضحة بالجنيه، ودفع بالمحافظ والبطاقات، وكتب مطبوعة، ودورات تُفتح بعد الدفع، ومسارات سريعة عبر القاهرة الكبرى.

نركّز على منتجات عملية وتوصيل واضح — من الدقي إلى الصعيد — مع دعم يرد بالعربية والإنجليزية.
TXT,
            ],
        ];

        foreach ($pages as $page) {
            Page::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => ['en' => $page['title'], 'ar' => $page['title_ar']],
                    'body' => ['en' => $page['body'], 'ar' => $page['body_ar']],
                    'meta_title' => ['en' => $page['title'], 'ar' => $page['title_ar']],
                    'is_published' => true,
                    'show_in_footer' => true,
                    'sort' => $page['sort'],
                    'published_at' => now(),
                ],
            );
        }
    }
}

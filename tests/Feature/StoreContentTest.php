<?php

use App\Enums\ProductKind;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Livewire\Store\SubscribeForm;
use App\Models\Article;
use App\Models\NewsItem;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('published articles are public pages with their own description', function () {
    Article::query()->create([
        'slug' => 'paying-at-mourymore-store',
        'title' => ['en' => 'How to pay', 'ar' => 'طرق الدفع'],
        'excerpt' => ['en' => 'InstaPay and cards.', 'ar' => 'إنستاباي والبطاقات.'],
        'body' => ['en' => 'Choose a wallet or card.', 'ar' => 'اختر محفظة أو بطاقة.'],
        'meta_title' => ['en' => 'Payments in Egypt', 'ar' => 'الدفع في مصر'],
        'meta_description' => ['en' => 'How payments work.', 'ar' => 'كيف يعمل الدفع.'],
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('articles'))
        ->assertOk()
        ->assertSee('How to pay');

    $this->get(route('articles.show', 'paying-at-mourymore-store'))
        ->assertOk()
        ->assertSee('Payments in Egypt', false)
        ->assertSee('How payments work.', false);
});

test('unpublished articles stay off the site', function () {
    Article::query()->create([
        'slug' => 'draft-guide',
        'title' => ['en' => 'Draft', 'ar' => 'مسودة'],
        'excerpt' => ['en' => 'Not ready.', 'ar' => 'لسه.'],
        'body' => ['en' => 'Hidden.', 'ar' => 'مخفي.'],
        'is_published' => false,
    ]);

    $this->get(route('articles.show', 'draft-guide'))->assertNotFound();
});

test('news and testimonials show on the storefront', function () {
    NewsItem::query()->create([
        'slug' => 'same-day',
        'title' => ['en' => 'Same-day lane', 'ar' => 'توصيل اليوم'],
        'excerpt' => ['en' => 'Order before 2.', 'ar' => 'اطلب قبل 2.'],
        'body' => ['en' => 'Cairo and Giza.', 'ar' => 'القاهرة والجيزة.'],
        'is_published' => true,
        'published_at' => now(),
    ]);

    Testimonial::query()->create([
        'name' => 'Nour Hassan',
        'role' => ['en' => 'Dokki', 'ar' => 'الدقي'],
        'quote' => ['en' => 'Arrived on time.', 'ar' => 'وصل في معادّه.'],
        'rating' => 5,
        'sort' => 1,
        'is_published' => true,
    ]);

    $this->get(route('news.index'))->assertOk()->assertSee('Same-day lane');
    $this->get(route('home'))->assertOk()->assertSee('Arrived on time.');
});

test('the footer form saves a newsletter subscription', function () {
    Livewire::test(SubscribeForm::class)
        ->set('email', 'reader@example.com')
        ->call('subscribe')
        ->assertHasNoErrors();

    expect(Subscription::query()->where('email', 'reader@example.com')->exists())->toBeTrue();
});

test('an admin sees articles news testimonials and subscriptions in the sidebar', function () {
    $guard = config('auth.defaults.guard', 'web');
    $role = Role::create(['name' => 'admin', 'guard_name' => $guard]);

    foreach (['Article', 'NewsItem', 'Testimonial', 'Subscription'] as $model) {
        $role->givePermissionTo(Permission::findOrCreate('ViewAny:'.$model, $guard));
    }

    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user);

    expect(ArticleResource::canViewAny())->toBeTrue()
        ->and(NewsResource::canViewAny())->toBeTrue()
        ->and(TestimonialResource::canViewAny())->toBeTrue()
        ->and(SubscriptionResource::canViewAny())->toBeTrue();
});

test('the sitemap lists published articles', function () {
    Article::query()->create([
        'slug' => 'paying-at-mourymore-store',
        'title' => ['en' => 'How to pay', 'ar' => 'طرق الدفع'],
        'excerpt' => ['en' => 'InstaPay and cards.', 'ar' => 'إنستاباي والبطاقات.'],
        'body' => ['en' => 'Choose a wallet or card.', 'ar' => 'اختر محفظة أو بطاقة.'],
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('articles.show', 'paying-at-mourymore-store'), false);
});

test('the shop splits books and courses from other products', function () {
    $fields = [
        'name' => 'Item',
        'brand' => 'mourymore_store',
        'description' => 'A store item.',
        'highlights' => ['One'],
        'price' => 100,
        'rating' => 4,
        'reviews_count' => 1,
        'stock' => 3,
        'image' => 'images/catalog/phone.jpg',
    ];

    Product::query()->create([...$fields, 'slug' => 'plain-phone', 'kind' => ProductKind::Product, 'name' => 'Plain Phone']);
    Product::query()->create([...$fields, 'slug' => 'nile-stories', 'kind' => ProductKind::Book, 'name' => 'Nile Stories']);
    Product::query()->create([...$fields, 'slug' => 'kitchen-course', 'kind' => ProductKind::Course, 'name' => 'Kitchen Course']);

    $this->get(route('shop'))
        ->assertOk()
        ->assertSee('Plain Phone')
        ->assertDontSee('Nile Stories')
        ->assertDontSee('Kitchen Course');

    $this->get(route('shop', ['department' => 'books']))
        ->assertOk()
        ->assertSee('Nile Stories')
        ->assertDontSee('Plain Phone');

    $this->get(route('shop', ['department' => 'courses']))
        ->assertOk()
        ->assertSee('Kitchen Course')
        ->assertDontSee('Plain Phone');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('moury_more_logo.png')
        ->assertSee('moury_more_cover.jpg');
});

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->string('currency_code', 8)->default('EGP')->after('cover');
            $table->string('currency_symbol_en', 16)->default('EGP')->after('currency_code');
            $table->string('currency_symbol_ar', 16)->default('ج.م')->after('currency_symbol_en');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('currency_symbol_ar');
            $table->boolean('prices_include_tax')->default(true)->after('tax_percent');
            $table->decimal('free_shipping_over', 10, 2)->nullable()->after('prices_include_tax');
            $table->string('support_email')->nullable()->after('free_shipping_over');
            $table->string('support_phone')->nullable()->after('support_email');
            $table->string('support_whatsapp')->nullable()->after('support_phone');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('tax', 10, 2)->default(0)->after('discount');
            $table->string('currency', 8)->default('EGP')->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'currency_code',
                'currency_symbol_en',
                'currency_symbol_ar',
                'tax_percent',
                'prices_include_tax',
                'free_shipping_over',
                'support_email',
                'support_phone',
                'support_whatsapp',
            ]);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['tax', 'currency']);
        });
    }
};

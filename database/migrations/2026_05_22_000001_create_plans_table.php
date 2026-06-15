<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tables.plans'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug',191)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('interval', ['monthly', 'yearly'])->default('monthly');
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->unsignedTinyInteger('trial_days')->default(0);
            $table->boolean('is_popular')->default(false);
            $table->boolean('status')->default(true);
            $table->string('button_text')->default('Get Started');
            $table->string('badge_text')->nullable();
            $table->string('theme_color')->default('#2563eb');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tables.plans'));
    }
};

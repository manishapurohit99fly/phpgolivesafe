<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tables.site_settings'), function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 191)->unique();
            $table->longText('setting_value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tables.site_settings'));
    }
};

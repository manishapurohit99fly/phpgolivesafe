<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tables.checklist_items'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained(config('tables.checklist_categories'))
                ->cascadeOnDelete();
            $table->string('checklist_item', 500);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tables.checklist_items'));
    }
};

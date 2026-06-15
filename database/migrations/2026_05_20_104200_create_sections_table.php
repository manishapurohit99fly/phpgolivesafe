<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('tables.sections'), function (Blueprint $table) {
             $table->id();

            $table->foreignId('page_id')
                ->constrained(config('tables.cms_pages'))
                ->cascadeOnDelete();
            $table->string('key');
            $table->json('content')->nullable();
            $table->integer('sort_order')
                ->default(0);
            $table->boolean('status')
                ->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.sections'));
    }
};

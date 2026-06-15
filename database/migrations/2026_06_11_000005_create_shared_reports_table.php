<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tables.shared_reports'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained(config('tables.projects'))
                ->cascadeOnDelete();
            $table->string('unique_token', 64)->unique();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tables.shared_reports'));
    }
};

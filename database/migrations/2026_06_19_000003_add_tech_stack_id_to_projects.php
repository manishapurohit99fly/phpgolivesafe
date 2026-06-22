<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('tables.projects'), function (Blueprint $table) {
            $table->foreignId('tech_stack_id')
                ->nullable()
                ->after('project_url')
                ->constrained(config('tables.tech_stacks'))
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(config('tables.projects'), function (Blueprint $table) {
            $table->dropForeign(['tech_stack_id']);
            $table->dropColumn('tech_stack_id');
        });
    }
};

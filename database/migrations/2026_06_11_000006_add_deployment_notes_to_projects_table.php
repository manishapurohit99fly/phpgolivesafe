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
            $table->text('deployment_notes')->nullable()->after('project_url');
        });
    }

    public function down(): void
    {
        Schema::table(config('tables.projects'), function (Blueprint $table) {
            $table->dropColumn('deployment_notes');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('tables.shared_reports'), function (Blueprint $table) {
            $table->unsignedBigInteger('assessment_id')->nullable()->after('project_id');
        });
    }

    public function down(): void
    {
        Schema::table(config('tables.shared_reports'), function (Blueprint $table) {
            $table->dropColumn('assessment_id');
        });
    }
};

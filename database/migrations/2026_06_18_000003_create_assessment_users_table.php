<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tables.assessment_users'), function (Blueprint $table) {
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['assessment_id', 'user_id']);

            $table->foreign('assessment_id')
                  ->references('id')
                  ->on(config('tables.assessments'))
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tables.assessment_users'));
    }
};

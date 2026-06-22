<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tables.assessment_checklists'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('checklist_item_id');
            $table->tinyInteger('is_checked')->default(0);
            $table->unsignedBigInteger('checked_by')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'checklist_item_id']);

            $table->foreign('assessment_id')
                  ->references('id')
                  ->on(config('tables.assessments'))
                  ->onDelete('cascade');

            $table->foreign('checklist_item_id')
                  ->references('id')
                  ->on(config('tables.checklist_items'))
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tables.assessment_checklists'));
    }
};

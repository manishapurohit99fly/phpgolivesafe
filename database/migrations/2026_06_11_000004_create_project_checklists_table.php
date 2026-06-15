<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tables.project_checklists'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained(config('tables.projects'))
                ->cascadeOnDelete();
            $table->foreignId('checklist_item_id')
                ->constrained(config('tables.checklist_items'))
                ->cascadeOnDelete();
            $table->tinyInteger('is_checked')->default(0)->comment('0 = Unchecked, 1 = Checked');
            $table->text('remarks')->nullable();
            $table->foreignId('checked_by')
                ->nullable()
                ->constrained(config('tables.users'))
                ->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(['project_id', 'checklist_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tables.project_checklists'));
    }
};

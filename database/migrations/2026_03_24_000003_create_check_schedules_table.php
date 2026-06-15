<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tables.check_schedules'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('check_name');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'annually']);
            $table->date('next_due_date');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('site_id')
                ->references('id')
                ->on(config('tables.sites'))
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->index(['site_id']);
            $table->index(['next_due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tables.check_schedules'));
    }
};

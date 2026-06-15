<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tables.check_logs'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->unsignedBigInteger('schedule_id');
            $table->timestamp('checked_at');
            $table->enum('status', ['passed', 'failed', 'pending'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('site_id')
                ->references('id')
                ->on(config('tables.sites'))
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('schedule_id')
                ->references('id')
                ->on(config('tables.check_schedules'))
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->index(['site_id']);
            $table->index(['schedule_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tables.check_logs'));
    }
};

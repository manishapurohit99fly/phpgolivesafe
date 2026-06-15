<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('tables.sections'), function (Blueprint $table) {

            $table->dropForeign(['page_id']);
            $table->dropColumn('page_id');

            /* Add polymorphic relation */
            $table->string('content_type')->nullable()->after('id');
            $table->unsignedBigInteger('content_id')->nullable()->after('content_type');
            
            /*  template relation */
            $table->unsignedBigInteger('section_template_id')->nullable()->after('content_id');
        });
    }

    public function down(): void
    {
        Schema::table(config('tables.sections'), function (Blueprint $table) {

            $table->dropColumn([
                'content_type',
                'content_id',
                'section_template_id',
            ]);

            $table->foreignId('page_id')
                ->constrained(config('tables.cms_pages'))
                ->cascadeOnDelete();
        });
    }
};
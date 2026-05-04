<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('survey_categories', 'kategori');
        Schema::rename('surveys', 'survey');
        Schema::rename('survey_submissions', 'jawaban_responden');

        Schema::table('survey', function (Blueprint $table) {
            $table->renameColumn('survey_category_id', 'kategori_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey', function (Blueprint $table) {
            $table->renameColumn('kategori_id', 'survey_category_id');
        });

        Schema::rename('jawaban_responden', 'survey_submissions');
        Schema::rename('survey', 'surveys');
        Schema::rename('kategori', 'survey_categories');
    }
};

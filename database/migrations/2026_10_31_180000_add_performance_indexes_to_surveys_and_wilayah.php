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
        Schema::table('master_wilayah', function (Blueprint $table) {
            $table->index(['nmkec', 'nmdesa'], 'idx_master_wilayah_nmkec_nmdesa');
            $table->index(['kdkec', 'kddesa'], 'idx_master_wilayah_kdkec_kddesa');
        });

        Schema::table('jawaban_responden', function (Blueprint $table) {
            $table->index(['survey_id', 'user_id'], 'idx_jawaban_survey_user');
            $table->index(['survey_id', 'submitted_at'], 'idx_jawaban_survey_submitted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_wilayah', function (Blueprint $table) {
            $table->dropIndex('idx_master_wilayah_nmkec_nmdesa');
            $table->dropIndex('idx_master_wilayah_kdkec_kddesa');
        });

        Schema::table('jawaban_responden', function (Blueprint $table) {
            $table->dropIndex('idx_jawaban_survey_user');
            $table->dropIndex('idx_jawaban_survey_submitted');
        });
    }
};

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
        Schema::table('survey', function (Blueprint $table) {
            $table->boolean('is_quiz')->default(false)->after('mode');
        });

        Schema::table('jawaban_responden', function (Blueprint $table) {
            $table->decimal('score', 8, 2)->nullable()->after('payload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jawaban_responden', function (Blueprint $table) {
            $table->dropColumn('score');
        });

        Schema::table('survey', function (Blueprint $table) {
            $table->dropColumn('is_quiz');
        });
    }
};
